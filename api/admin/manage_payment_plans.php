<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../../db/db_config.php';
require_once 'audit_logger.php';

// Security Check: Ensure user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Administrator access required.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $result = $conn->query("SELECT * FROM payment_plans ORDER BY created_at DESC");
    $plans = [];
    while ($row = $result->fetch_assoc()) {
        $plans[] = $row;
    }
    echo json_encode(['status' => 'success', 'plans' => $plans]);
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';

    switch ($action) {
        case 'add_plan':
            if (!isset($data['name'], $data['type'], $data['price'])) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
                exit;
            }
            $name = $data['name'];
            $type = $data['type'];
            $price = $data['price'];
            $currency = $data['currency'] ?? 'CAD';
            $duration_days = !empty($data['duration_days']) ? (int)$data['duration_days'] : null;
            $paypal_plan_id = !empty($data['paypal_plan_id']) ? $data['paypal_plan_id'] : null;
            $description = $data['description'] ?? '';

            $stmt = $conn->prepare("INSERT INTO payment_plans (name, type, price, currency, duration_days, paypal_plan_id, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdsiss", $name, $type, $price, $currency, $duration_days, $paypal_plan_id, $description);
            if ($stmt->execute()) {
                logAdminAction($conn, $_SESSION['user_id'], 'add_payment_plan', $conn->insert_id, "Name: $name, Type: $type");
                echo json_encode(['status' => 'success', 'message' => 'Payment plan added successfully.']);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to add payment plan.']);
            }
            break;

        case 'update_plan':
            if (!isset($data['id'], $data['name'], $data['type'], $data['price'])) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
                exit;
            }
            $id = $data['id'];
            $name = $data['name'];
            $type = $data['type'];
            $price = $data['price'];
            $currency = $data['currency'] ?? 'CAD';
            $duration_days = !empty($data['duration_days']) ? (int)$data['duration_days'] : null;
            $paypal_plan_id = !empty($data['paypal_plan_id']) ? $data['paypal_plan_id'] : null;
            $description = $data['description'] ?? '';
            $is_active = isset($data['is_active']) ? (int)$data['is_active'] : 1;

            $stmt = $conn->prepare("UPDATE payment_plans SET name = ?, type = ?, price = ?, currency = ?, duration_days = ?, paypal_plan_id = ?, description = ?, is_active = ? WHERE id = ?");
            $stmt->bind_param("ssdsissii", $name, $type, $price, $currency, $duration_days, $paypal_plan_id, $description, $is_active, $id);
            if ($stmt->execute()) {
                logAdminAction($conn, $_SESSION['user_id'], 'update_payment_plan', $id, "Name: $name");
                echo json_encode(['status' => 'success', 'message' => 'Payment plan updated successfully.']);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to update payment plan.']);
            }
            break;

        case 'delete_plan':
            if (!isset($data['id'])) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Missing plan ID.']);
                exit;
            }
            $id = $data['id'];
            $stmt = $conn->prepare("DELETE FROM payment_plans WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                logAdminAction($conn, $_SESSION['user_id'], 'delete_payment_plan', $id);
                echo json_encode(['status' => 'success', 'message' => 'Payment plan deleted successfully.']);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete payment plan.']);
            }
            break;

        default:
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
            break;
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
}
$conn->close();
?>
