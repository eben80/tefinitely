<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'session_init.php';
require_once '../db/db_config.php';

// --- Main Logic ---
try {
    // Only allow POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.', 405);
    }

    // Get the posted data
    $data = json_decode(file_get_contents('php://input'), true);

    // Basic validation
    if (!$data || !isset($data['email']) || !isset($data['password'])) {
        throw new Exception('Missing email or password.', 400);
    }

    $email = trim($data['email']);
    $password = trim($data['password']);
    $remember = isset($data['remember']) ? (bool)$data['remember'] : false;
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

    require_once 'services/GeoService.php';
    $country_code = GeoService::getCountryCode($ip_address);

    if (empty($email) || empty($password)) {
        throw new Exception('Email and password are required.', 400);
    }

    // Check for lockout
    $stmt_lockout = $conn->prepare("SELECT COUNT(*) as failed_count FROM login_history WHERE email = ? AND status = 'failed' AND created_at >= NOW() - INTERVAL 15 MINUTE");
    $stmt_lockout->bind_param("s", $email);
    $stmt_lockout->execute();
    $lockout_result = $stmt_lockout->get_result()->fetch_assoc();
    if ($lockout_result['failed_count'] >= 5) {
        throw new Exception('Too many failed attempts. Please try again in 15 minutes.', 429);
    }

    // Fetch user from the database
    $stmt = $conn->prepare("SELECT id, first_name, password, role, subscription_status, email_verified FROM users WHERE email = ?");
    if (!$stmt) {
        throw new Exception("Database prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Check if email is verified (admins can bypass)
        if ($user['email_verified'] == 0 && $user['role'] !== 'admin') {
            // Log failed attempt due to unverified email
            $stmt_log = $conn->prepare("INSERT INTO login_history (user_id, email, ip_address, country_code, status, user_agent) VALUES (?, ?, ?, ?, 'failed', ?)");
            $stmt_log->bind_param("issss", $user['id'], $email, $ip_address, $country_code, $user_agent);
            $stmt_log->execute();
            $stmt_log->close();

            throw new Exception('Please verify your email address before logging in.', 403);
        }

        // Verify password
        if (password_verify($password, $user['password'])) {
            // Password is correct, log success and start the session
            $stmt_log = $conn->prepare("INSERT INTO login_history (user_id, email, ip_address, country_code, status, user_agent) VALUES (?, ?, ?, ?, 'success', ?)");
            $stmt_log->bind_param("issss", $user['id'], $email, $ip_address, $country_code, $user_agent);
            $stmt_log->execute();
            $stmt_log->close();

            init_session($remember);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['subscription_status'] = $user['subscription_status'];

            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'message' => 'Login successful.',
                'user' => [
                    'first_name' => $user['first_name'],
                    'role' => $user['role'],
                    'subscription_status' => $user['subscription_status']
                ]
            ]);
        } else {
            // Invalid password, log failure
            $stmt_log = $conn->prepare("INSERT INTO login_history (user_id, email, ip_address, country_code, status, user_agent) VALUES (?, ?, ?, ?, 'failed', ?)");
            $stmt_log->bind_param("issss", $user['id'], $email, $ip_address, $country_code, $user_agent);
            $stmt_log->execute();
            $stmt_log->close();

            throw new Exception('Invalid email or password.', 401);
        }
    } else {
        // User not found, log failure
        $stmt_log = $conn->prepare("INSERT INTO login_history (user_id, email, ip_address, country_code, status, user_agent) VALUES (NULL, ?, ?, ?, 'failed', ?)");
        $stmt_log->bind_param("ssss", $email, $ip_address, $country_code, $user_agent);
        $stmt_log->execute();
        $stmt_log->close();

        throw new Exception('Invalid email or password.', 401);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    // Log the actual error
    // debug_log("Login Error: " . $e->getMessage());

    // Send a generic, valid JSON error response
    $http_code = ($e->getCode() > 0) ? $e->getCode() : 500;
    http_response_code($http_code);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage() // Or a generic message like 'An internal error occurred.'
    ]);
}
?>
