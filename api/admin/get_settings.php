<?php
require_once '../session_init.php';
init_session();
header('Content-Type: application/json; charset=utf-8');
require_once '../../db/db_config.php';

// Security Check: Ensure user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Administrator access required.']);
    exit;
}

$result = $conn->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('trial_enabled', 'trial_days')");
$settings = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}

// Fallback to defaults if settings are not found in the DB (for now)
if (!isset($settings['trial_enabled'])) $settings['trial_enabled'] = '1';
if (!isset($settings['trial_days'])) $settings['trial_days'] = '3';

echo json_encode(['status' => 'success', 'settings' => $settings]);
$conn->close();
?>
