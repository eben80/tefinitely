<?php
require_once '../../db/db_config.php';
require_once '../auth_check.php';
checkAccess(true, true); // Admin only

header('Content-Type: application/json');

try {
    // 1. Total Active Subscribers
    $stmt = $conn->prepare("SELECT COUNT(*) as active_count FROM users WHERE subscription_status = 'active'");
    $stmt->execute();
    $active_count = $stmt->get_result()->fetch_assoc()['active_count'];

    // 2. MRR (Revenue in the last 30 days - Simplified to USD for this dashboard)
    $stmt = $conn->prepare("SELECT SUM(amount) as mrr FROM subscription_payments WHERE currency = 'USD' AND payment_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $stmt->execute();
    $mrr = $stmt->get_result()->fetch_assoc()['mrr'] ?? 0;

    // 3. Revenue Trends (Last 6 months - Simplified to USD)
    $stmt = $conn->prepare("
        SELECT DATE_FORMAT(payment_date, '%Y-%m') as month, SUM(amount) as revenue
        FROM subscription_payments
        WHERE currency = 'USD'
        GROUP BY month
        ORDER BY month DESC
        LIMIT 6
    ");
    $stmt->execute();
    $trends = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    echo json_encode([
        'status' => 'success',
        'stats' => [
            'active_subscribers' => (int)$active_count,
            'mrr' => (float)$mrr,
            'trends' => $trends
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
