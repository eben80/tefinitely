<?php
require_once __DIR__ . '/../../db/db_config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

$tour_completed = isset($data['tour_completed']) ? ($data['tour_completed'] ? 1 : 0) : null;
$last_topic = isset($data['last_topic']) ? $data['last_topic'] : null;
$last_card_index = isset($data['last_card_index']) ? $data['last_card_index'] : null;
$first_name = isset($data['first_name']) ? trim($data['first_name']) : null;
$last_name = isset($data['last_name']) ? trim($data['last_name']) : null;

$sql = "UPDATE users SET ";
$params = [];
$types = "";

if ($tour_completed !== null) {
    $sql .= "tour_completed = ?, ";
    $params[] = $tour_completed;
    $types .= "i";
}

if ($first_name !== null) {
    $sql .= "first_name = ?, ";
    $params[] = $first_name;
    $types .= "s";
}

if ($last_name !== null) {
    $sql .= "last_name = ?, ";
    $params[] = $last_name;
    $types .= "s";
}

if ($last_topic !== null) {
    $sql .= "last_topic = ?, ";
    $params[] = $last_topic;
    $types .= "s";
}

if ($last_card_index !== null) {
    $sql .= "last_card_index = ?, ";
    $params[] = $last_card_index;
    $types .= "i";
}

$sql = rtrim($sql, ', ');
$sql .= " WHERE id = ?";
$params[] = $user_id;
$types .= "i";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'User details updated successfully']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to update user details']);
}

$stmt->close();
$conn->close();
?>
