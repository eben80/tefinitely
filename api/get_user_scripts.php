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

$user_id = $_SESSION['user_id'];
$script_id = $_GET['id'] ?? null;

if ($script_id) {
    // --- Fetch a single script ---
    $stmt = $conn->prepare("SELECT id, script_name, script_content FROM user_scripts WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $script_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($script = $result->fetch_assoc()) {
        echo json_encode($script, JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Script not found or you do not have permission to access it.']);
    }
    $stmt->close();

} else {
    // --- Fetch the list of all scripts for the user ---
    $stmt = $conn->prepare("SELECT id, script_name FROM user_scripts WHERE user_id = ? ORDER BY script_name ASC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $scripts = [];
    while ($row = $result->fetch_assoc()) {
        $scripts[] = $row;
    }
    echo json_encode($scripts, JSON_UNESCAPED_UNICODE);
    $stmt->close();
}

$conn->close();
?>
