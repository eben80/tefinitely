<?php
require_once 'config.php';
require_once __DIR__ . '/../db/db_config.php';
require_once __DIR__ . '/../api/services/EmailService.php';

if (!is_logged_in()) {
    redirect('login.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $url = filter_input(INPUT_POST, 'url', FILTER_VALIDATE_URL);
    $interval = (int) $_POST['interval_minutes'];

    if ($url && $interval > 0) {
        $stmt = $pdo->prepare("INSERT INTO monitors (user_id, url, interval_minutes) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $url, $interval]);

        // Send email notification
        $email = $_SESSION['email'];
        $subject = "New URL Monitor Added";
        $body_html = "<h1>Monitor Added</h1>
                      <p>You have successfully added a new URL to monitor:</p>
                      <p><strong>URL:</strong> <a href='{$url}'>{$url}</a></p>
                      <p><strong>Interval:</strong> {$interval} minutes</p>
                      <p>We will notify you when visible changes are detected.</p>";
        $body_text = "Monitor Added\n\nYou have successfully added a new URL to monitor:\nURL: {$url}\nInterval: {$interval} minutes\n\nWe will notify you when visible changes are detected.";

        sendEmail($email, $subject, $body_html, $body_text);

        redirect('index.php');
    } else {
        $error = "Please provide a valid URL and interval.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Monitor - URL Monitor</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container" style="max-width: 500px; margin-top: 5rem;">
        <div class="card">
            <h2 style="margin-bottom: 1.5rem;">Add New Monitor</h2>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="form-group">
                    <label>URL to monitor</label>
                    <input type="url" name="url" class="form-control" required placeholder="https://example.com" autofocus>
                    <small style="color: var(--muted-text);">Ensure the URL starts with http:// or https://</small>
                </div>
                <div class="form-group">
                    <label>Check interval (minutes)</label>
                    <input type="number" name="interval_minutes" class="form-control" value="60" min="1" required>
                </div>
                <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Add Monitor</button>
                    <a href="index.php" class="btn" style="background-color: var(--border-color); color: var(--text-color); flex: 1;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
