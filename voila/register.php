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
            $subject = "Welcome to URL Monitor";
            $body_html = "<h1>Welcome to URL Monitor!</h1>
                          <p>Thank you for registering. You can now start monitoring URLs for visible changes.</p>
                          <p><a href='https://tefinitely.com/voila/login.php'>Login here</a> to get started.</p>";
            $body_text = "Welcome to URL Monitor!\n\nThank you for registering. You can now start monitoring URLs for visible changes.\n\nLogin here to get started: https://tefinitely.com/voila/login.php";

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
    <title>Register - URL Monitor</title>
    <link rel="stylesheet" href="style.css">
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
