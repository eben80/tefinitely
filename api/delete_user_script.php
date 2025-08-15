<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Check if the user is authenticated
if (!isset($_SESSION['user_id'])) {
    http_response_code(403); // Forbidden
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to delete a script.']);
    exit;
}

require_once '../db/db_config.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

// Get the posted data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['script_id'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Script ID is required.']);
    exit;
}

$script_id = $data['script_id'];
$user_id = $_SESSION['user_id'];

// Verify that the user owns the script before deleting
$stmt = $conn->prepare("DELETE FROM user_scripts WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $script_id, $user_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        http_response_code(200); // OK
        echo json_encode(['status' => 'success', 'message' => 'Script deleted successfully.']);
    } else {
        http_response_code(404); // Not Found
        echo json_encode(['status' => 'error', 'message' => 'Script not found or you do not have permission to delete it.']);
    }
} else {
    http_response_code(500); // Internal Server Error
    echo json_encode(['status' => 'error', 'message' => 'Failed to delete script.']);
}

$stmt->close();
$conn->close();
?>
