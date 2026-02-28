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
        t.id,
        t.user_id,
        t.email,
        t.subject,
        t.message,
        t.status,
        t.created_at,
        u.first_name,
        u.last_name
    FROM
        support_tickets t
    LEFT JOIN
        users u ON t.user_id = u.id
    ORDER BY
        t.created_at DESC
";

$result = $conn->query($query);
$tickets = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $tickets[] = $row;
    }
}

echo json_encode(['status' => 'success', 'tickets' => $tickets]);
$conn->close();
?>
