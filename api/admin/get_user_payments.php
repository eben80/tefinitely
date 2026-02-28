<?php
require_once '../auth_check.php';
checkAccess(true, true); // Admin only

header('Content-Type: application/json');

if (!isset($_GET['user_id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'User ID is required']);
    exit;
}

$user_id = $_GET['user_id'];
require_once '../../db/db_config.php';

try {
    $stmt = $conn->prepare("
        SELECT p.*
        FROM subscription_payments p
        JOIN subscriptions s ON p.subscription_id = s.id
        WHERE s.user_id = ?
        ORDER BY p.payment_date DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $payments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    echo json_encode([
        'status' => 'success',
        'payments' => $payments
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
