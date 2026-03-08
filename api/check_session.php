<?php
require_once 'session_init.php';
init_session();
header('Content-Type: application/json; charset=utf-8');
require_once '../db/db_config.php'; // For the debug_log function

// debug_log('--- check_session.php ---');
// debug_log($_SESSION);

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT u.role, u.subscription_status, u.tour_completed, u.tour_section_a_completed, u.tour_section_b_completed, u.last_topic, u.last_card_index, s.subscription_start_date, s.subscription_end_date, s.paypal_subscription_id FROM users u LEFT JOIN subscriptions s ON u.id = s.user_id WHERE u.id = ? ORDER BY s.subscription_end_date DESC LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_details = $result->fetch_assoc();

    if (!$user_details) {
        $user_details = [];
        $subscription_status = 'inactive';
    } else {
        if ($user_details['role'] === 'admin') {
            $subscription_status = 'active';
        } else {
            $now = new DateTime();
            $start_date = $user_details['subscription_start_date'] ? new DateTime($user_details['subscription_start_date']) : null;
            $end_date = $user_details['subscription_end_date'] ? new DateTime($user_details['subscription_end_date']) : null;

            if ($start_date && $end_date && $now >= $start_date && $now <= $end_date) {
                $subscription_status = 'active';
            } else {
                $subscription_status = 'inactive';
                // If DB says active but it's actually expired, sync the status
                if ($user_details['subscription_status'] === 'active') {
                    $update_stmt = $conn->prepare("UPDATE users SET subscription_status = 'inactive' WHERE id = ?");
                    $update_stmt->bind_param("i", $user_id);
                    $update_stmt->execute();
                    $update_stmt->close();
                }
            }
        }
    }

    // User is logged in
    http_response_code(200);

    // Calculate trial days left if it's a trial
    $trial_days_left = null;
    if ($subscription_status === 'active' && ($user_details['paypal_subscription_id'] ?? '') === 'trial') {
        $now = new DateTime();
        $end_date = new DateTime($user_details['subscription_end_date']);
        $seconds_left = $end_date->getTimestamp() - $now->getTimestamp();
        $trial_days_left = max(0, (int)ceil($seconds_left / 86400));
    }

    // Prepare user data for the response
    $response_user = $user_details; // Start with all details from DB
    $response_user['trial_days_left'] = $trial_days_left;
    $response_user['user_id'] = $_SESSION['user_id'];
    $response_user['first_name'] = $_SESSION['first_name'];
    $response_user['google_picture'] = $_SESSION['google_picture'] ?? null;
    // Overwrite subscription_status with the one we just calculated
    $response_user['subscription_status'] = $subscription_status;
    // The 'role' from $user_details is from the DB, which is more current than session.

    echo json_encode([
        'status' => 'success',
        'loggedIn' => true,
        'user' => $response_user
    ]);
} else {
    // User is not logged in
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'loggedIn' => false,
        'message' => 'User is not authenticated.'
    ]);
}
?>
