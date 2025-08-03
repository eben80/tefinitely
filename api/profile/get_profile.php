<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../../db/db_config.php';

// Security Check: User must be logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to view your profile.']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Fetch user's basic info
    $stmt_user = $conn->prepare("SELECT id, first_name, last_name, email, role, subscription_status FROM users WHERE id = ?");
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();

    if ($result_user->num_rows !== 1) {
        throw new Exception("User not found.", 404);
    }
    $profile_data = $result_user->fetch_assoc();

    // Fetch user's latest subscription details
    $stmt_sub = $conn->prepare("SELECT subscription_start_date, subscription_end_date FROM subscriptions WHERE user_id = ? ORDER BY subscription_end_date DESC LIMIT 1");
    $stmt_sub->bind_param("i", $user_id);
    $stmt_sub->execute();
    $result_sub = $stmt_sub->get_result();

    if ($result_sub->num_rows === 1) {
        $subscription_data = $result_sub->fetch_assoc();
        $profile_data['subscription_start_date'] = $subscription_data['subscription_start_date'];
        $profile_data['subscription_end_date'] = $subscription_data['subscription_end_date'];
    } else {
        $profile_data['subscription_start_date'] = null;
        $profile_data['subscription_end_date'] = null;
    }

    http_response_code(200);
    echo json_encode(['status' => 'success', 'profile' => $profile_data]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?>
