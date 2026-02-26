<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkAccess($requireSubscription = true, $requireAdmin = false) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login.html');
        exit;
    }

    require_once __DIR__ . '/../db/db_config.php';

    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT u.role, u.subscription_status, s.subscription_start_date, s.subscription_end_date FROM users u LEFT JOIN subscriptions s ON u.id = s.user_id WHERE u.id = ? ORDER BY s.subscription_end_date DESC LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_details = $result->fetch_assoc();

    if (!$user_details) {
        $subscription_status = 'inactive';
        $role = 'user';
    } else {
        $role = $user_details['role'];
        if ($role === 'admin') {
            $subscription_status = 'active';
        } elseif ($user_details['subscription_status'] === 'active') {
            $subscription_status = 'active';
        } else {
            $now = new DateTime();
            $start_date = $user_details['subscription_start_date'] ? new DateTime($user_details['subscription_start_date']) : null;
            $end_date = $user_details['subscription_end_date'] ? new DateTime($user_details['subscription_end_date']) : null;

            if ($start_date && $end_date && $now >= $start_date && $now <= $end_date) {
                $subscription_status = 'active';
            } else {
                $subscription_status = 'inactive';
            }
        }
    }

    if ($requireAdmin && $role !== 'admin') {
        header('Location: /logged_in.php');
        exit;
    }

    if ($requireSubscription && $subscription_status !== 'active') {
        header('Location: /index.html');
        exit;
    }

    return $user_details;
}
?>
