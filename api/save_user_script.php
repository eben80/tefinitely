<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once '../db/db_config.php';

// Ensure user is authenticated
if (!isset($_SESSION['user_id']) || !isset($_SESSION['subscription_status']) || $_SESSION['subscription_status'] !== 'active') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Access denied.']);
    exit;
}

// Get the user ID from the session
$user_id = $_SESSION['user_id'];

// Get the raw POST data
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

$script_name = $data['script_name'] ?? '';
$script_content = $data['script_content'] ?? '';

if (empty($script_name) || empty($script_content)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Script name and content cannot be empty.']);
    exit;
}

try {
    $stmt = $conn->prepare("INSERT INTO user_scripts (user_id, script_name, script_content) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $script_name, $script_content);

    if ($stmt->execute()) {
        http_response_code(201); // Created
        echo json_encode(['status' => 'success', 'message' => 'Script saved successfully.', 'script_id' => $conn->insert_id]);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to save script.']);
    }
    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    // Check for duplicate entry error (code 1062)
    if ($conn->errno === 1062) {
        echo json_encode(['status' => 'error', 'message' => 'A script with this name already exists. Please choose a different name.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'An unexpected error occurred: ' . $e->getMessage()]);
    }
}

$conn->close();
?>
