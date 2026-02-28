<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once '../../db/db_config.php';
require_once '../../db/paypal_config.php';

// --- Security Check: User must be logged in ---
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to capture a payment.']);
    exit;
}

// Get the posted data
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['orderID'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing orderID.']);
    exit;
}
$orderID = $data['orderID'];
$user_id = $_SESSION['user_id'];

// --- 1. Get PayPal Access Token ---
// (This function would be identical to the one in create_payment.php. For brevity, it's assumed to be available)
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

// --- 2. Capture PayPal Order ---
function capture_paypal_order($access_token, $orderID) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, PAYPAL_API_BASE_URL . '/v2/checkout/orders/' . $orderID . '/capture');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    // No body needed for capture request
    curl_setopt($ch, CURLOPT_POSTFIELDS, '{}');
    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $access_token
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($ch);
    curl_close($ch);
    return json_decode($result);
}

// --- Main Execution ---
$access_token = get_paypal_access_token();
if (!$access_token) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to get PayPal access token.']);
    exit;
}

$capture_details = capture_paypal_order($access_token, $orderID);

if (isset($capture_details->status) && $capture_details->status === 'COMPLETED') {
    // --- Payment is successful, now update our database ---
    $transaction_id = $capture_details->id; // Or $capture_details->purchase_units[0]->payments->captures[0]->id for more specificity
    $start_date = date('Y-m-d H:i:s');
    $end_date = date('Y-m-d H:i:s', strtotime('+30 days'));

    // Use a transaction to ensure both updates succeed or fail together
    $conn->begin_transaction();
    try {
        // 1. Update users table
        $stmt_user = $conn->prepare("UPDATE users SET subscription_status = 'active' WHERE id = ?");
        $stmt_user->bind_param("i", $user_id);
        $stmt_user->execute();

        // 2. Insert into subscriptions table
        $stmt_sub = $conn->prepare("INSERT INTO subscriptions (user_id, paypal_subscription_id, subscription_start_date, subscription_end_date, status) VALUES (?, ?, ?, ?, 'active') ON DUPLICATE KEY UPDATE paypal_subscription_id = VALUES(paypal_subscription_id), subscription_start_date = VALUES(subscription_start_date), subscription_end_date = VALUES(subscription_end_date), status = 'active'");
        $stmt_sub->bind_param("isss", $user_id, $transaction_id, $start_date, $end_date);
        $stmt_sub->execute();
        $internal_sub_id = ($stmt_sub->insert_id) ? $stmt_sub->insert_id : null;
        if (!$internal_sub_id) {
            $stmt_get_id = $conn->prepare("SELECT id FROM subscriptions WHERE user_id = ?");
            $stmt_get_id->bind_param("i", $user_id);
            $stmt_get_id->execute();
            $internal_sub_id = $stmt_get_id->get_result()->fetch_assoc()['id'];
        }

        // 3. Log the payment to subscription_payments
        $amount = 5.00; // Hardcoded default for one-time
        $currency = "CAD";
        if (isset($capture_details->purchase_units[0]->payments->captures[0]->amount)) {
            $amount = $capture_details->purchase_units[0]->payments->captures[0]->amount->value;
            $currency = $capture_details->purchase_units[0]->payments->captures[0]->amount->currency_code;
        }

        $stmt_pay = $conn->prepare("INSERT INTO subscription_payments (subscription_id, paypal_transaction_id, amount, currency, payment_date) VALUES (?, ?, ?, ?, NOW())");
        $stmt_pay->bind_param("isds", $internal_sub_id, $transaction_id, $amount, $currency);
        $stmt_pay->execute();

        // Commit the transaction
        $conn->commit();

        // Also update the session
        $_SESSION['subscription_status'] = 'active';

        // Send confirmation email
        require_once __DIR__ . '/../services/EmailService.php';
        $stmt_email = $conn->prepare("SELECT email, first_name FROM users WHERE id = ?");
        $stmt_email->bind_param("i", $user_id);
        $stmt_email->execute();
        $result = $stmt_email->get_result();
        $user = $result->fetch_assoc();
        $stmt_email->close();

        if ($user) {
            $subject = "Your Subscription is Active!";
            $body_html = "<h1>Hi {$user['first_name']},</h1><p>Your payment was successful and your subscription is now active. It will be valid until {$end_date}.</p>";
            $body_text = "Hi {$user['first_name']}! Your payment was successful and your subscription is now active. It will be valid until {$end_date}.";
            sendEmail($user['email'], $subject, $body_html, $body_text);
        }

        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'Payment successful and subscription activated.']);

    } catch (mysqli_sql_exception $exception) {
        $conn->rollback();
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database update failed after successful payment. Please contact support.', 'details' => $exception->getMessage()]);
    }
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to capture PayPal payment.', 'details' => $capture_details]);
}

$conn->close();
?>
