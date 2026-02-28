<?php
// api/paypal/get_client_token.php
header('Content-Type: application/json; charset=utf-8');
require_once 'paypal_helper.php';

$result = get_paypal_client_token();

if (isset($result['token'])) {
    echo json_encode(['status' => 'success', 'client_token' => $result['token']]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to generate PayPal client token.', 'details' => $result['error'] ?? 'Unknown error']);
}
?>
