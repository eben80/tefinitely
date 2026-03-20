<?php
require_once 'config.php';
require_once __DIR__ . '/../db/db_config.php';
require_once __DIR__ . '/../api/services/EmailService.php';

if (is_logged_in()) {
    redirect('index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];

    if ($email && $password) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
            $stmt->execute([$email, $hashed_password]);

            // Send welcome email
            $subject = "Welcome to Voila!";
            $body_html = "
            <div style='font-family: sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #eee; border-radius: 8px; overflow: hidden;'>
                <div style='background: #007bff; padding: 20px; text-align: center;'>
                    <h1 style='color: white; margin: 0;'>Voila!</h1>
                </div>
                <div style='padding: 30px; line-height: 1.6; color: #333;'>
                    <h2>Welcome to Voila!</h2>
                    <p>Thank you for registering. You're all set to start monitoring your favorite web pages for visible changes.</p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='https://tefinitely.com/voila/login.php' style='background: #007bff; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Login to your Dashboard</a>
                    </div>
                    <p>If you have any questions, feel free to reply to this email.</p>
                </div>
                <div style='background: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #6c757d;'>
                    &copy; " . date('Y') . " Voila! URL Monitoring Service
                </div>
            </div>";
            $body_text = "Welcome to Voila!\n\nThank you for registering. You can now start monitoring URLs for visible changes.\n\nLogin here to get started: https://tefinitely.com/voila/login.php";

            sendEmail($email, $subject, $body_html, $body_text);

            $success = "Registration successful! You can now <a href='login.php'>login</a>.";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = "Email already registered.";
            } else {
                $error = "Error: " . $e->getMessage();
            }
        }
    } else {
        $error = "Please provide a valid email and password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Voila!</title>
    <link rel="stylesheet" href="<?php echo asset_v('voila/style.css'); ?>">
</head>
<body>
    <div class="container" style="max-width: 400px; margin-top: 5rem;">
        <div class="card">
            <h2 style="text-align: center; margin-bottom: 1.5rem;">Register</h2>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required autofocus>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Register</button>
            </form>
            <p style="text-align: center; margin-top: 1.5rem; color: var(--muted-text);">
                Already have an account? <a href="login.php">Login here</a>.
            </p>
        </div>
    </div>
</body>
</html>
