<?php
require 'db.php';

$secret = 'your_lemon_squeezy_webhook_secret';
$payload = file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_X_SIGNATURE'] ?? '';

if ($sig_header !== hash_hmac('sha256', $payload, $secret)) {
    http_response_code(401);
    echo "Invalid signature";
    exit;
}

$data = json_decode($payload, true);
$event = $data['meta']['event_name'] ?? '';

// Lemon Squeezy provides license keys in 'order_created' or 'license_key_created' events
// Or they are attached to the order. For subscriptions, we usually care about the subscription status.

if ($event === 'order_created') {
    $email = $data['data']['attributes']['user_email'];
    $license_key = $data['data']['attributes']['first_order_item']['license_keys'][0]['key'] ?? ''; // Example path
    // Note: Actual LS payload structure may vary, usually license keys are in relationships or separate events

    // Better: Handle 'license_key_created' event if enabled in LS
}

if ($event === 'subscription_created' || $event === 'subscription_updated') {
    $email = $data['data']['attributes']['user_email'];
    $status = $data['data']['attributes']['status'];
    $sub_id = $data['data']['id'];
    $db_status = ($status === 'active' || $status === 'on_trial') ? 'active' : 'inactive';

    $stmt = $pdo->prepare("INSERT INTO users (email, status, subscription_id)
                           VALUES (?, ?, ?)
                           ON DUPLICATE KEY UPDATE status = ?, subscription_id = ?");
    $stmt->execute([$email, $db_status, $sub_id, $db_status, $sub_id]);
}

if ($event === 'license_key_created') {
    $license_key = $data['data']['attributes']['key'];
    $email = $data['data']['attributes']['user_email'];

    $stmt = $pdo->prepare("UPDATE users SET license_key = ? WHERE email = ?");
    $stmt->execute([$license_key, $email]);
}

http_response_code(200);
echo "OK";
?>
