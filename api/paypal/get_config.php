<?php
// api/paypal/get_config.php
header('Content-Type: application/json; charset=utf-8');
require_once '../../db/paypal_config.php';

// This endpoint provides the necessary configuration to the frontend JavaScript.
// It only exposes non-sensitive information.

$config = [
    'client_id' => PAYPAL_CLIENT_ID,
    'plan_id'   => PAYPAL_PLAN_ID
];

echo json_encode($config);
?>
