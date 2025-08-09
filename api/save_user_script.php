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

// Check if a script with the same name already exists for this user
$stmt = $conn->prepare("SELECT id FROM user_scripts WHERE user_id = ? AND script_name = ?");
$stmt->bind_param("is", $user_id, $script_name);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    http_response_code(409); // Conflict
    echo json_encode(['status' => 'error', 'message' => 'A script with this name already exists. Please choose a different name.']);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// Insert the new script
try {
    $stmt = $conn->prepare("INSERT INTO user_scripts (user_id, script_name, script_content) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $script_name, $script_content);

    if ($stmt->execute()) {
        http_response_code(201); // Created
        echo json_encode(['status' => 'success', 'message' => 'Script saved successfully.', 'script_id' => $conn->insert_id]);
    } else {
        throw new Exception("Failed to execute statement.");
    }
    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to save script: ' . $e->getMessage()]);
}

$conn->close();
?>
