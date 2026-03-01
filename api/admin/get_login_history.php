<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../../db/db_config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Administrator access required.']);
    exit;
}

$search = $_GET['search'] ?? '';

$query = "
    SELECT
        l.id,
        l.email,
        l.ip_address,
        l.status,
        l.user_agent,
        l.created_at,
        u.first_name,
        u.last_name
    FROM
        login_history l
    LEFT JOIN
        users u ON l.user_id = u.id
";

$params = [];
$types = "";

if ($search) {
    $query .= " WHERE l.email LIKE ? OR l.ip_address LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? ";
    $searchTerm = "%$search%";
    $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
    $types = "ssss";
}

$query .= " ORDER BY l.created_at DESC LIMIT 200";

$stmt = $conn->prepare($query);
if ($search) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$history = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $history[] = $row;
    }
}

echo json_encode(['status' => 'success', 'history' => $history]);
$conn->close();
?>
