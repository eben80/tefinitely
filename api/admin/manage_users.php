<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../../db/db_config.php';
require_once 'audit_logger.php';

// Security Check: Ensure user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403); // Forbidden
    echo json_encode(['status' => 'error', 'message' => 'Administrator access required.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Optimized query using LEFT JOINs on pre-aggregated subqueries
    $query = "
        SELECT
            u.id,
            u.first_name,
            u.last_name,
            u.email,
            u.role,
            u.subscription_status,
            u.created_at,
            MAX(s.subscription_end_date) AS subscription_end_date,
            COALESCE(cl.calls_1h, 0) as calls_1h,
            COALESCE(cl.calls_24h, 0) as calls_24h,
            COALESCE(cl.calls_7d, 0) as calls_7d,
            COALESCE(cl.calls_30d, 0) as calls_30d,
            COALESCE(cl.calls_lifetime, 0) as calls_lifetime
        FROM
            users u
        LEFT JOIN
            subscriptions s ON u.id = s.user_id
        LEFT JOIN (
            SELECT
                user_id,
                COUNT(CASE WHEN created_at >= NOW() - INTERVAL 1 HOUR THEN 1 END) as calls_1h,
                COUNT(CASE WHEN created_at >= NOW() - INTERVAL 24 HOUR THEN 1 END) as calls_24h,
                COUNT(CASE WHEN created_at >= NOW() - INTERVAL 7 DAY THEN 1 END) as calls_7d,
                COUNT(CASE WHEN created_at >= NOW() - INTERVAL 30 DAY THEN 1 END) as calls_30d,
                COUNT(*) as calls_lifetime
            FROM openai_calls_log
            GROUP BY user_id
        ) cl ON u.id = cl.user_id
        GROUP BY
            u.id
        ORDER BY
            u.id ASC
    ";
    $result = $conn->query($query);
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    http_response_code(200);
    echo json_encode(['status' => 'success', 'users' => $users]);
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';

    switch ($action) {
        case 'update_subscription':
            // --- Update Subscription Status ---
            if (!isset($data['user_id']) || !isset($data['subscription_status'])) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Missing user_id or subscription_status.']);
                exit;
            }
            $user_id = $data['user_id'];
            $status = $data['subscription_status'];
            if (!in_array($status, ['active', 'inactive'])) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Invalid status value.']);
                exit;
            }

            $conn->begin_transaction();
            try {
                // Update the user's status
                $stmt_user = $conn->prepare("UPDATE users SET subscription_status = ? WHERE id = ?");
                $stmt_user->bind_param("si", $status, $user_id);
                $stmt_user->execute();
                $stmt_user->close();

                // Delete any existing subscription records for the user
                $stmt_sub = $conn->prepare("DELETE FROM subscriptions WHERE user_id = ?");
                $stmt_sub->bind_param("i", $user_id);
                $stmt_sub->execute();
                $stmt_sub->close();

                logAdminAction($conn, $_SESSION['user_id'], 'update_subscription', $user_id, "Set status to $status");

                $conn->commit();
                echo json_encode(['status' => 'success', 'message' => 'User subscription updated successfully.']);
            } catch (Exception $e) {
                $conn->rollback();
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to update user subscription.']);
            }
            break;

        case 'delete_user':
            // --- Delete User ---
            if (!isset($data['user_id'])) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'User ID not specified.']);
                exit;
            }
            $user_id = $data['user_id'];

            // Prevent admin from deleting themselves
            if ($user_id == $_SESSION['user_id']) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'You cannot delete your own account.']);
                exit;
            }

            // We should also delete related records (e.g., in subscriptions) for good database hygiene.
            // For simplicity here, we'll just delete from the users table. A real-world app would use transactions.
            $stmt_delete_subs = $conn->prepare("DELETE FROM subscriptions WHERE user_id = ?");
            $stmt_delete_subs->bind_param("i", $user_id);
            $stmt_delete_subs->execute();


            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    logAdminAction($conn, $_SESSION['user_id'], 'delete_user', $user_id);
                    echo json_encode(['status' => 'success', 'message' => 'User deleted successfully.']);
                } else {
                    http_response_code(404);
                    echo json_encode(['status' => 'error', 'message' => 'User not found or already deleted.']);
                }
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete user.']);
            }
            $stmt->close();
            break;

        case 'add_user':
            // --- Add New User ---
            if (!isset($data['first_name']) || !isset($data['last_name']) || !isset($data['email']) || !isset($data['password']) || !isset($data['role'])) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
                exit;
            }
            $first_name = $data['first_name'];
            $last_name = $data['last_name'];
            $email = $data['email'];
            $password = $data['password'];
            $role = $data['role'];

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Invalid email format.']);
                exit;
            }
            if (strlen($password) < 8) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Password must be at least 8 characters long.']);
                exit;
            }
            if (!in_array($role, ['user', 'admin'])) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Invalid role specified.']);
                exit;
            }

            // Check if email already exists
            $stmt_check = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt_check->bind_param("s", $email);
            $stmt_check->execute();
            if ($stmt_check->get_result()->num_rows > 0) {
                http_response_code(409); // Conflict
                echo json_encode(['status' => 'error', 'message' => 'Email already exists.']);
                exit;
            }

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, role, subscription_status) VALUES (?, ?, ?, ?, ?, 'inactive')");
            $stmt->bind_param("sssss", $first_name, $last_name, $email, $hashed_password, $role);
            if ($stmt->execute()) {
                $new_user_id = $conn->insert_id;
                logAdminAction($conn, $_SESSION['user_id'], 'add_user', $new_user_id, "Email: $email, Role: $role");
                // Send welcome email
                require_once __DIR__ . '/../services/EmailService.php';
                $subject = "Welcome to Tefinitely!";
                $body_html = "<h1>Welcome, {$first_name}!</h1><p>An administrator has created an account for you. Your password is: {$password}</p><p>We recommend changing your password after you log in.</p>";
                $body_text = "Welcome, {$first_name}!\nAn administrator has created an account for you. Your password is: {$password}\nWe recommend changing your password after you log in.";
                sendEmail($email, $subject, $body_html, $body_text);

                echo json_encode(['status' => 'success', 'message' => 'User added successfully.']);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to add user.']);
            }
            $stmt->close();
            break;

        case 'update_email':
            // --- Update Email ---
            if (!isset($data['user_id']) || !isset($data['email'])) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Missing user_id or email.']);
                exit;
            }
            $user_id = $data['user_id'];
            $email = $data['email'];
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Invalid email format.']);
                exit;
            }
            $stmt = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
            $stmt->bind_param("si", $email, $user_id);
            if ($stmt->execute()) {
                logAdminAction($conn, $_SESSION['user_id'], 'update_email', $user_id, "New email: $email");
                echo json_encode(['status' => 'success', 'message' => 'User email updated successfully.']);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to update user email.']);
            }
            $stmt->close();
            break;

        case 'update_password':
            // --- Update Password ---
            if (!isset($data['user_id']) || !isset($data['password'])) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Missing user_id or password.']);
                exit;
            }
            $user_id = $data['user_id'];
            $password = $data['password'];
            if (strlen($password) < 8) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Password must be at least 8 characters long.']);
                exit;
            }
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $user_id);
            if ($stmt->execute()) {
                logAdminAction($conn, $_SESSION['user_id'], 'update_password', $user_id);
                echo json_encode(['status' => 'success', 'message' => 'User password updated successfully.']);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to update user password.']);
            }
            $stmt->close();
            break;

        case 'update_subscription_dates':
            if (!isset($data['user_id']) || !isset($data['start_date']) || !isset($data['end_date'])) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Missing user_id or date fields.']);
                exit;
            }
            $user_id = $data['user_id'];
            $start_date = !empty($data['start_date']) ? $data['start_date'] : null;
            $end_date = !empty($data['end_date']) ? $data['end_date'] : null;

            // Find the latest subscription to update it.
            // If none exists, create one.
            $stmt_find = $conn->prepare("SELECT id FROM subscriptions WHERE user_id = ? ORDER BY subscription_end_date DESC LIMIT 1");
            $stmt_find->bind_param("i", $user_id);
            $stmt_find->execute();
            $result = $stmt_find->get_result();

            if ($result->num_rows > 0) {
                // Update existing subscription
                $sub = $result->fetch_assoc();
                $sub_id = $sub['id'];
                $stmt_update = $conn->prepare("UPDATE subscriptions SET subscription_start_date = ?, subscription_end_date = ? WHERE id = ?");
                $stmt_update->bind_param("ssi", $start_date, $end_date, $sub_id);
                $stmt_update->execute();
            } else {
                // Insert a new subscription record
                $stmt_insert = $conn->prepare("INSERT INTO subscriptions (user_id, paypal_subscription_id, subscription_start_date, subscription_end_date) VALUES (?, 'manual_admin', ?, ?)");
                $stmt_insert->bind_param("iss", $user_id, $start_date, $end_date);
                $stmt_insert->execute();
            }

            // Calculate and update user's status based on dates
            $now = new DateTime();
            $start_date_obj = new DateTime($start_date);
            $end_date_obj = new DateTime($end_date);
            $new_status = ($now >= $start_date_obj && $now <= $end_date_obj) ? 'active' : 'inactive';

            $stmt_status = $conn->prepare("UPDATE users SET subscription_status = ? WHERE id = ?");
            $stmt_status->bind_param("si", $new_status, $user_id);
            $stmt_status->execute();

            logAdminAction($conn, $_SESSION['user_id'], 'update_subscription_dates', $user_id, "Start: $start_date, End: $end_date, New status: $new_status");

            echo json_encode(['status' => 'success', 'message' => 'Subscription dates and status updated successfully.']);
            break;

        case 'bulk_delete':
            if (!isset($data['user_ids']) || !is_array($data['user_ids'])) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Missing or invalid user_ids.']);
                exit;
            }
            $user_ids = array_filter($data['user_ids'], function($id) {
                return $id != $_SESSION['user_id'];
            });
            if (empty($user_ids)) {
                echo json_encode(['status' => 'success', 'message' => 'No valid users to delete.']);
                exit;
            }
            $ids_str = implode(',', array_map('intval', $user_ids));
            $conn->query("DELETE FROM subscriptions WHERE user_id IN ($ids_str)");
            $conn->query("DELETE FROM users WHERE id IN ($ids_str)");
            logAdminAction($conn, $_SESSION['user_id'], 'bulk_delete', null, "Deleted users: $ids_str");
            echo json_encode(['status' => 'success', 'message' => 'Users deleted successfully.']);
            break;

        case 'bulk_status_update':
            if (!isset($data['user_ids']) || !is_array($data['user_ids']) || !isset($data['subscription_status'])) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Missing user_ids or status.']);
                exit;
            }
            $status = $data['subscription_status'];
            if (!in_array($status, ['active', 'inactive'])) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Invalid status value.']);
                exit;
            }
            $user_ids = $data['user_ids'];
            if (empty($user_ids)) {
                echo json_encode(['status' => 'success', 'message' => 'No users to update.']);
                exit;
            }
            $ids_str = implode(',', array_map('intval', $user_ids));

            $conn->begin_transaction();
            try {
                $conn->query("UPDATE users SET subscription_status = '$status' WHERE id IN ($ids_str)");
                if ($status === 'inactive') {
                    $conn->query("DELETE FROM subscriptions WHERE user_id IN ($ids_str)");
                }
                logAdminAction($conn, $_SESSION['user_id'], 'bulk_status_update', null, "Updated users: $ids_str to $status");
                $conn->commit();
                echo json_encode(['status' => 'success', 'message' => 'Users status updated successfully.']);
            } catch (Exception $e) {
                $conn->rollback();
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to update users status.']);
            }
            break;

        case 'bulk_email':
            if (!isset($data['user_ids']) || !is_array($data['user_ids']) || !isset($data['subject']) || !isset($data['message'])) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Missing user_ids, subject, or message.']);
                exit;
            }
            require_once __DIR__ . '/../services/EmailService.php';
            $user_ids = $data['user_ids'];
            if (empty($user_ids)) {
                echo json_encode(['status' => 'success', 'message' => 'No recipients selected.']);
                exit;
            }
            $ids_str = implode(',', array_map('intval', $user_ids));
            $result = $conn->query("SELECT email, first_name FROM users WHERE id IN ($ids_str)");

            $subject = $data['subject'];
            $message = $data['message'];
            $body_html = nl2br(htmlspecialchars($message));
            $body_text = $message;

            $sent_count = 0;
            while ($row = $result->fetch_assoc()) {
                if (sendEmail($row['email'], $subject, $body_html, $body_text)) {
                    $sent_count++;
                }
            }
            logAdminAction($conn, $_SESSION['user_id'], 'bulk_email', null, "Sent email to $sent_count users. Subject: $subject");
            echo json_encode(['status' => 'success', 'message' => "Email sent to $sent_count users."]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid action specified.']);
            break;
    }
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

$conn->close();
?>
