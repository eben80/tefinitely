<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../../db/db_config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Administrator access required.']);
    exit;
}

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
    ORDER BY
        l.created_at DESC
    LIMIT 200
";

$result = $conn->query($query);
$history = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $history[] = $row;
    }
}

echo json_encode(['status' => 'success', 'history' => $history]);
$conn->close();
?>
