<?php
// api/paypal/get_client_token.php
header('Content-Type: application/json; charset=utf-8');
require_once 'paypal_helper.php';

$client_token = get_paypal_client_token();

if ($client_token) {
    echo json_encode(['status' => 'success', 'client_token' => $client_token]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to generate PayPal client token.']);
}
?>
