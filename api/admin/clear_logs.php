<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../../db/db_config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Administrator access required.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$type = $data['type'] ?? '';

if (!in_array($type, ['login_history', 'audit_logs', 'financial'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid clear type specified.']);
    exit;
}

try {
    switch ($type) {
        case 'login_history':
            $conn->query("DELETE FROM login_history");
            $message = "Login history cleared successfully.";
            $action = "Clear Login History";
            break;
        case 'audit_logs':
            // We clear audit logs but maybe we should keep the "Clear Audit Logs" action itself?
            // Usually, "Clear" means truncate.
            $conn->query("DELETE FROM admin_audit_logs");
            $message = "Audit logs cleared successfully.";
            $action = "Clear Audit Logs";
            break;
        case 'financial':
            // Resetting financial data means clearing payment records.
            // We do NOT clear subscriptions or plans, just the payment history.
            $conn->query("DELETE FROM subscription_payments");
            $message = "Financial data (payment records) reset successfully.";
            $action = "Reset Financial Data";
            break;
    }

    // Log this clearing action to the audit log (if it wasn't just cleared or if we re-insert it)
    require_once 'audit_logger.php';
    logAdminAction($conn, $_SESSION['user_id'], $action, null, "All records deleted.");

    echo json_encode(['status' => 'success', 'message' => $message]);

} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database operation failed.', 'details' => $e->getMessage()]);
}

$conn->close();
?>
