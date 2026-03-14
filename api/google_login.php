<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'session_init.php';
require_once '../db/db_config.php';
require_once '../vendor/autoload.php';

// Check for Google Client ID in config, but since I can't see it, I'll assume it's defined or needs to be.
// For the purpose of this task, I'll use a placeholder if not found.
$google_client_id = defined('GOOGLE_CLIENT_ID') ? GOOGLE_CLIENT_ID : '1032401011225-pcjeocvpdigthv15u1qu1hmv8p61cuc0.apps.googleusercontent.com';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.', 405);
    }

    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || !isset($data['credential'])) {
        throw new Exception('Missing Google credential.', 400);
    }

    $id_token = $data['credential'];

    $client = new Google_Client(['client_id' => $google_client_id]);
    $payload = $client->verifyIdToken($id_token);

    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

    require_once 'services/GeoService.php';
    $country_code = GeoService::getCountryCode($ip_address);

    if ($payload) {
        $google_id = $payload['sub'];
        $email = $payload['email'];
        $first_name = $payload['given_name'] ?? 'User';
        $last_name = $payload['family_name'] ?? '';
        $picture = $payload['picture'] ?? null;

        // Check if user exists by email
        $stmt = $conn->prepare("SELECT id, first_name, role, subscription_status FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            // Existing user, log them in
            $user = $result->fetch_assoc();

            // Mark email as verified if it wasn't already (since Google verified it)
            $update_stmt = $conn->prepare("UPDATE users SET email_verified = 1 WHERE id = ?");
            $update_stmt->bind_param("i", $user['id']);
            $update_stmt->execute();
            $update_stmt->close();

            // Log successful login
            $stmt_log = $conn->prepare("INSERT INTO login_history (user_id, email, ip_address, country_code, status, user_agent) VALUES (?, ?, ?, ?, 'success', ?)");
            $stmt_log->bind_param("issss", $user['id'], $email, $ip_address, $country_code, $user_agent);
            $stmt_log->execute();
            $stmt_log->close();

        } else {
            // New user, register them
            $hashed_password = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT); // Random password
            $email_verified = 1;

            // Check for trial settings
            $trial_enabled = false;
            $trial_days = 0;
            $settings_res = $conn->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('trial_enabled', 'trial_days')");
            if ($settings_res) {
                while ($row = $settings_res->fetch_assoc()) {
                    if ($row['setting_key'] === 'trial_enabled') $trial_enabled = $row['setting_value'] === '1';
                    if ($row['setting_key'] === 'trial_days') $trial_days = (int)$row['setting_value'];
                }
            }

            $trial_used = $trial_enabled ? 1 : 0;
            $subscription_status = ($trial_enabled && $trial_days > 0) ? 'active' : 'inactive';

            $insert_stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, email_verified, subscription_status, trial_used) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("ssssisi", $first_name, $last_name, $email, $hashed_password, $email_verified, $subscription_status, $trial_used);

            if (!$insert_stmt->execute()) {
                throw new Exception('Failed to register user via Google.');
            }

            $user_id = $insert_stmt->insert_id;
            $insert_stmt->close();

            if ($trial_enabled && $trial_days > 0) {
                $start_date = date('Y-m-d H:i:s');
                $end_date = date('Y-m-d H:i:s', strtotime("+$trial_days days"));
                $sub_stmt = $conn->prepare("INSERT INTO subscriptions (user_id, paypal_subscription_id, subscription_start_date, subscription_end_date, status) VALUES (?, 'trial', ?, ?, 'active')");
                $sub_stmt->bind_param("iss", $user_id, $start_date, $end_date);
                $sub_stmt->execute();
                $sub_stmt->close();

                // Log the trial grant in audit logs (System automated)
                require_once __DIR__ . '/admin/audit_logger.php';
                logAdminAction($conn, 1, 'grant_trial', $user_id, "Automated $trial_days-day trial granted via Google Login.");
            }

            $user = [
                'id' => $user_id,
                'first_name' => $first_name,
                'role' => 'user',
                'subscription_status' => $subscription_status
            ];

            // Log registration as a successful login
            $stmt_log = $conn->prepare("INSERT INTO login_history (user_id, email, ip_address, country_code, status, user_agent) VALUES (?, ?, ?, ?, 'success', ?)");
            $stmt_log->bind_param("issss", $user['id'], $email, $ip_address, $country_code, $user_agent);
            $stmt_log->execute();
            $stmt_log->close();

            // Send notification to support for new Google registration
            require_once __DIR__ . '/services/EmailService.php';
            $os = getOS($user_agent);

            $support_subject = "New User Registered (Google): {$first_name} {$last_name}";
            $support_body_html = "<h1>New Google Registration</h1>
                                  <p>A new user has registered via Google on Tefinitely:</p>
                                  <ul>
                                      <li><strong>Name:</strong> {$first_name} {$last_name}</li>
                                      <li><strong>Email:</strong> {$email}</li>
                                      <li><strong>IP Address:</strong> {$ip_address}</li>
                                      <li><strong>Country Code:</strong> " . ($country_code ?: 'Unknown') . "</li>
                                      <li><strong>Operating System:</strong> {$os}</li>
                                      <li><strong>User Agent:</strong> {$user_agent}</li>
                                      <li><strong>Date:</strong> " . date('Y-m-d H:i:s') . "</li>
                                  </ul>";
            $support_body_text = "New Google Registration\n\nA new user has registered via Google on Tefinitely:\nName: {$first_name} {$last_name}\nEmail: {$email}\nIP Address: {$ip_address}\nCountry Code: " . ($country_code ?: 'Unknown') . "\nOperating System: {$os}\nUser Agent: {$user_agent}\nDate: " . date('Y-m-d H:i:s');

            sendEmail(SUPPORT_NOTIFICATION_EMAIL, $support_subject, $support_body_html, $support_body_text);
        }

        // Start session
        init_session(true); // Default to remember me for Google login
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['subscription_status'] = $user['subscription_status'];
        $_SESSION['google_picture'] = $picture;

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
        // Log failed Google login attempt if we have an email (but verifyIdToken failed, we might not have it reliably)
        // If we can't verify the token, we shouldn't trust any data in it.
        throw new Exception('Invalid ID token.', 401);
    }

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
