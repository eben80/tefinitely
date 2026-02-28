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
        l.action,
        l.details,
        l.created_at,
        l.target_id,
        a.first_name as admin_first_name,
        a.last_name as admin_last_name,
        u.first_name as target_first_name,
        u.last_name as target_last_name
    FROM
        admin_audit_logs l
    JOIN
        users a ON l.admin_id = a.id
    LEFT JOIN
        users u ON l.target_id = u.id
    ORDER BY
        l.created_at DESC
    LIMIT 100
";

$result = $conn->query($query);
$logs = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }
}

echo json_encode(['status' => 'success', 'logs' => $logs]);
$conn->close();
?>
