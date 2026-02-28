<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once '../../db/db_config.php';
require_once '../../db/paypal_config.php';

// --- Security Check: User must be logged in ---
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to make a payment.']);
    exit;
}

// --- 1. Get PayPal Access Token ---
function get_paypal_access_token($conn) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, PAYPAL_API_BASE_URL . '/v1/oauth2/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
    curl_setopt($ch, CURLOPT_USERPWD, PAYPAL_CLIENT_ID . ':' . PAYPAL_CLIENT_SECRET);
    $headers = ['Accept: application/json', 'Accept-Language: en_US'];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        // Handle cURL error
        return null;
    }
    curl_close($ch);
    $json = json_decode($result);
    return $json->access_token ?? null;
}

// --- 2. Create PayPal Order ---
function create_paypal_order($access_token) {
    $ch = curl_init();
    $order_data = [
        'intent' => 'CAPTURE',
        'purchase_units' => [[
            'amount' => [
                'currency_code' => 'CAD',
                'value' => '5.00' // Subscription price
            ],
            'description' => '1 Month Subscription to TEF Practice'
        ]],
        'application_context' => [
            'return_url' => 'http://localhost/capture_payment.php', // Placeholder
            'cancel_url' => 'http://localhost/cancel_payment.php', // Placeholder
            'brand_name' => 'TEF Practice Platform',
            'user_action' => 'PAY_NOW',
        ],
    ];

    curl_setopt($ch, CURLOPT_URL, PAYPAL_API_BASE_URL . '/v2/checkout/orders');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($order_data));
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
$access_token = get_paypal_access_token($conn);
if (!$access_token) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to get PayPal access token.']);
    exit;
}

$order = create_paypal_order($access_token);
if (isset($order->id)) {
    http_response_code(200);
    echo json_encode(['status' => 'success', 'orderID' => $order->id]);
} else {
    http_response_code(500);
    // Forward the error from PayPal if available
    echo json_encode(['status' => 'error', 'message' => 'Failed to create PayPal order.', 'details' => $order]);
}

$conn->close();
?>
