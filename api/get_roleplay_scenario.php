<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['subscription_status']) || $_SESSION['subscription_status'] !== 'active') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Access denied.']);
    exit;
}

require_once '../db/db_config.php';

$scenario_id = $_GET['id'] ?? 0;

if ($scenario_id == 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Scenario ID is required.']);
    exit;
}

$stmt = $conn->prepare("SELECT name, description, cloze_script_json FROM roleplay_scenarios WHERE id = ?");
$stmt->bind_param("i", $scenario_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $scenario = $result->fetch_assoc();
    // The JSON is already a string, but let's decode and re-encode to ensure it's well-formed.
    // This also makes it easy to manipulate on the server in the future.
    $scenario['cloze_script_json'] = json_decode($scenario['cloze_script_json']);
    echo json_encode($scenario, JSON_UNESCAPED_UNICODE);
} else {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Scenario not found.']);
}

$stmt->close();
$conn->close();
?>
