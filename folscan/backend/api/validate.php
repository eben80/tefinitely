<?php
require 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed. Use POST.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$license_key = $input['license_key'] ?? '';

if (empty($license_key)) {
    echo json_encode(['status' => 'error', 'message' => 'License key missing']);
    exit;
}

$stmt = $pdo->prepare("SELECT status FROM users WHERE license_key = ?");
$stmt->execute([$license_key]);
$user = $stmt->fetch();

if ($user && $user['status'] === 'active') {
    echo json_encode(['status' => 'active']);
} else {
    echo json_encode(['status' => 'inactive']);
}
?>
