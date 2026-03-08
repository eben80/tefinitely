<?php
require_once '../session_init.php';
init_session();
header('Content-Type: application/json; charset=utf-8');
require_once '../../db/db_config.php';
require_once 'audit_logger.php';

// Security Check: Ensure user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Administrator access required.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['trial_enabled']) || !isset($data['trial_days'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required settings.']);
    exit;
}

$trial_enabled = $data['trial_enabled'] ? '1' : '0';
$trial_days = (int)$data['trial_days'];

if ($trial_days < 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Trial days cannot be negative.']);
    exit;
}

$conn->begin_transaction();
try {
    $stmt = $conn->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'trial_enabled'");
    $stmt->bind_param("s", $trial_enabled);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'trial_days'");
    $stmt->bind_param("s", $trial_days);
    $stmt->execute();
    $stmt->close();

    $details = "Trial Enabled: " . ($trial_enabled === '1' ? 'YES' : 'NO') . ", Trial Days: $trial_days";
    logAdminAction($conn, $_SESSION['user_id'], 'update_promotion_settings', null, $details);

    $conn->commit();
    echo json_encode(['status' => 'success', 'message' => 'Promotion settings updated successfully.']);
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to update settings.']);
}

$conn->close();
?>
