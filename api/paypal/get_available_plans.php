<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../db/db_config.php';

$result = $conn->query("SELECT id, name, type, price, currency, duration_days, paypal_plan_id, description FROM payment_plans WHERE is_active = 1 ORDER BY price ASC");
$plans = [];
while ($row = $result->fetch_assoc()) {
    $plans[] = $row;
}

echo json_encode(['status' => 'success', 'plans' => $plans]);
$conn->close();
?>
