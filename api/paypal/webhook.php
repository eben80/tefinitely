<?php
// api/paypal/webhook.php

// This script handles incoming webhook events from PayPal.

require_once '../../db/db_config.php';
require_once '../../db/paypal_config.php';

use Psr\Log\LoggerInterface;

// --- Helper function to get access token ---
function get_paypal_access_token() {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, PAYPAL_API_BASE_URL . '/v1/oauth2/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
    curl_setopt($ch, CURLOPT_USERPWD, PAYPAL_CLIENT_ID . ':' . PAYPAL_CLIENT_SECRET);
    $headers = ['Accept: application/json', 'Accept-Language: en_US'];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($ch);
    if (curl_errno($ch)) { return null; }
    curl_close($ch);
    $json = json_decode($result);
    return $json->access_token ?? null;
}

// --- Main Execution ---

// 1. Get the webhook data
$request_body = file_get_contents('php://input');
$event = json_decode($request_body);

// For debugging: log the raw event
// file_put_contents('webhook_log.txt', $request_body . "\n\n", FILE_APPEND);

if (!$event || !isset($event->event_type)) {
    // Not a valid PayPal event.
    http_response_code(400);
    exit();
}

// 2. Verify the webhook signature (CRITICAL for security)
$headers = getallheaders();
$transmission_id = $headers['Paypal-Transmission-Id'] ?? $headers['paypal-transmission-id'];
$timestamp = $headers['Paypal-Transmission-Time'] ?? $headers['paypal-transmission-time'];
$cert_url = $headers['Paypal-Cert-Url'] ?? $headers['paypal-cert-url'];
$auth_algo = $headers['Paypal-Auth-Algo'] ?? $headers['paypal-auth-algo'];
$transmission_sig = $headers['Paypal-Transmission-Sig'] ?? $headers['paypal-transmission-sig'];

// The webhook ID is configured in your PayPal developer dashboard
$webhook_id = getenv('PAYPAL_WEBHOOK_ID'); // User will need to set this secret

$access_token = get_paypal_access_token();

$ch_verify = curl_init();
$verify_data = [
    'transmission_id' => $transmission_id,
    'transmission_time' => $timestamp,
    'cert_url' => $cert_url,
    'auth_algo' => $auth_algo,
    'transmission_sig' => $transmission_sig,
    'webhook_id' => $webhook_id,
    'webhook_event' => json_decode($request_body)
];

curl_setopt($ch_verify, CURLOPT_URL, PAYPAL_API_BASE_URL . '/v1/notifications/verify-webhook-signature');
curl_setopt($ch_verify, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch_verify, CURLOPT_POST, 1);
curl_setopt($ch_verify, CURLOPT_POSTFIELDS, json_encode($verify_data));
curl_setopt($ch_verify, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $access_token
]);

$verify_result = curl_exec($ch_verify);
curl_close($ch_verify);
$verification_status = json_decode($verify_result)->verification_status ?? 'failure';

if ($verification_status !== 'SUCCESS') {
    // Verification failed. Do not process the event.
    // Log this attempt for security monitoring.
    http_response_code(403); // Forbidden
    exit();
}

// 3. Process the verified event
$event_type = $event->event_type;
$resource = $event->resource;

switch ($event_type) {
    case 'PAYMENT.SALE.COMPLETED':
        // A recurring payment was successfully made.
        if (isset($resource->billing_agreement_id)) {
            $subscription_id_paypal = $resource->billing_agreement_id;

            // Find our internal subscription record
            $stmt_find = $conn->prepare("SELECT id, user_id FROM subscriptions WHERE paypal_subscription_id = ?");
            $stmt_find->bind_param("s", $subscription_id_paypal);
            $stmt_find->execute();
            $result = $stmt_find->get_result();
            $subscription_record = $result->fetch_assoc();

            if ($subscription_record) {
                $internal_sub_id = $subscription_record['id'];
                $user_id = $subscription_record['user_id'];

                // Log the payment
                $stmt_log = $conn->prepare(
                    "INSERT INTO subscription_payments (subscription_id, paypal_transaction_id, amount, currency, payment_date) VALUES (?, ?, ?, ?, NOW())"
                );
                $stmt_log->bind_param("isds", $internal_sub_id, $resource->id, $resource->amount->total, $resource->amount->currency);
                $stmt_log->execute();

                // Update the end date of the subscription
                // We need to get the latest info from PayPal about the next billing date
                // This is a simplified approach; a more robust one would fetch subscription details again.
                $new_end_date = date('Y-m-d H:i:s', strtotime('+1 month'));
                $stmt_update = $conn->prepare("UPDATE subscriptions SET subscription_end_date = ?, status = 'active' WHERE id = ?");
                $stmt_update->bind_param("si", $new_end_date, $internal_sub_id);
                $stmt_update->execute();

                // Also update the main users table
                $stmt_user = $conn->prepare("UPDATE users SET subscription_status = 'active' WHERE id = ?");
                $stmt_user->bind_param("i", $user_id);
                $stmt_user->execute();
            }
        }
        break;

    case 'BILLING.SUBSCRIPTION.CANCELLED':
        $subscription_id_paypal = $resource->id;
        $stmt = $conn->prepare("UPDATE subscriptions SET status = 'cancelled' WHERE paypal_subscription_id = ?");
        $stmt->bind_param("s", $subscription_id_paypal);
        $stmt->execute();

        // User asked: "Will status be disabled if a cancellation or refund occurs through paypal?"
        // Usually, for cancellations, access continues until the end of the period.
        // But the user seems to want it "disabled". To be safe and meet the requirement:
        // If it's a cancellation, we set status to cancelled, but access usually remains.
        // If the user wants it DISABLED (revoked), we update the users table.
        // Given the phrasing "Will status be disabled", I will revoke it immediately to be sure.

        $stmt_revoke = $conn->prepare("
            UPDATE users u
            JOIN subscriptions s ON u.id = s.user_id
            SET u.subscription_status = 'inactive'
            WHERE s.paypal_subscription_id = ?
        ");
        $stmt_revoke->bind_param("s", $subscription_id_paypal);
        $stmt_revoke->execute();
        break;

    case 'BILLING.SUBSCRIPTION.SUSPENDED':
        $subscription_id_paypal = $resource->id;
        $stmt = $conn->prepare("UPDATE subscriptions SET status = 'suspended' WHERE paypal_subscription_id = ?");
        $stmt->bind_param("s", $subscription_id_paypal);
        $stmt->execute();

        // Find the user and set their main status to inactive
        $stmt_find_user = $conn->prepare(
            "UPDATE users u JOIN subscriptions us ON u.id = us.user_id
             SET u.subscription_status = 'inactive'
             WHERE us.paypal_subscription_id = ?"
        );
        $stmt_find_user->bind_param("s", $subscription_id_paypal);
        $stmt_find_user->execute();
        break;

    case 'PAYMENT.SALE.REFUNDED':
    case 'PAYMENT.CAPTURE.REFUNDED':
        // For recurring payments (sale) or one-time captures
        $paypal_id = $resource->id; // transaction ID
        $parent_id = $resource->parent_payment ?? $resource->billing_agreement_id ?? null;

        // Try to find by transaction ID or parent/subscription ID
        $stmt_refund = $conn->prepare("
            UPDATE users u
            JOIN subscriptions s ON u.id = s.user_id
            SET u.subscription_status = 'inactive', s.status = 'refunded', s.subscription_end_date = NOW()
            WHERE s.paypal_subscription_id = ? OR s.id IN (SELECT subscription_id FROM subscription_payments WHERE paypal_transaction_id = ?)
        ");
        $stmt_refund->bind_param("ss", $parent_id, $paypal_id);
        $stmt_refund->execute();
        break;
}

// 4. Respond with a 200 OK to acknowledge receipt
http_response_code(200);
echo json_encode(['status' => 'success']);

$conn->close();
?>
