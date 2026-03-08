<?php
require_once __DIR__ . '/../../db/db_config.php';
require_once '../session_init.php';
init_session();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

$sql = "DELETE FROM dialogue_progress WHERE user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Your shadowing stats have been reset.']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to reset shadowing stats.']);
}

$stmt->close();
$conn->close();
?>
