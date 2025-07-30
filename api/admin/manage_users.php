<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../../db/db_config.php';

// Security Check: Ensure user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403); // Forbidden
    echo json_encode(['status' => 'error', 'message' => 'Administrator access required.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Fetch all users and their latest subscription end date
    $query = "
        SELECT
            u.id,
            u.username,
            u.email,
            u.role,
            u.subscription_status,
            u.created_at,
            MAX(s.subscription_end_date) AS subscription_end_date
        FROM
            users u
        LEFT JOIN
            subscriptions s ON u.id = s.user_id
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
            $stmt = $conn->prepare("UPDATE users SET subscription_status = ? WHERE id = ?");
            $stmt->bind_param("si", $status, $user_id);
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'User subscription updated successfully.']);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to update user subscription.']);
            }
            $stmt->close();
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
            if (!isset($data['username']) || !isset($data['email']) || !isset($data['password']) || !isset($data['role'])) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
                exit;
            }
            $username = $data['username'];
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

            // Check if username or email already exists
            $stmt_check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt_check->bind_param("ss", $username, $email);
            $stmt_check->execute();
            if ($stmt_check->get_result()->num_rows > 0) {
                http_response_code(409); // Conflict
                echo json_encode(['status' => 'error', 'message' => 'Username or email already exists.']);
                exit;
            }

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, subscription_status) VALUES (?, ?, ?, ?, 'inactive')");
            $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);
            if ($stmt->execute()) {
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
            $start_date = $data['start_date'];
            $end_date = $data['end_date'];

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
                $stmt_insert = $conn->prepare("INSERT INTO subscriptions (user_id, paypal_transaction_id, subscription_start_date, subscription_end_date) VALUES (?, 'manual_admin', ?, ?)");
                $stmt_insert->bind_param("iss", $user_id, $start_date, $end_date);
                $stmt_insert->execute();
            }
            echo json_encode(['status' => 'success', 'message' => 'Subscription dates updated successfully.']);
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
