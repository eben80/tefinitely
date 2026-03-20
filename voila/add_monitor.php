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
        $subject = "Monitor Added - Voila!";
        $body_html = "
        <div style='font-family: sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #eee; border-radius: 8px; overflow: hidden;'>
            <div style='background: #007bff; padding: 20px; text-align: center;'>
                <h1 style='color: white; margin: 0;'>Voila!</h1>
            </div>
            <div style='padding: 30px; line-height: 1.6; color: #333;'>
                <h2 style='color: #28a745;'>Monitor Added Successfully!</h2>
                <p>You have successfully added a new URL to monitor for visible changes:</p>
                <div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                    <p style='margin: 5px 0;'><strong>URL:</strong> <a href='{$url}' style='color: #007bff;'>{$url}</a></p>
                    <p style='margin: 5px 0;'><strong>Interval:</strong> {$interval} minutes</p>
                </div>
                <p>Voila! will notify you immediately whenever visible text changes are detected on this page.</p>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='https://tefinitely.com/voila/index.php' style='background: #007bff; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;'>View Dashboard</a>
                </div>
            </div>
            <div style='background: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #6c757d;'>
                &copy; " . date('Y') . " Voila! URL Monitoring Service
            </div>
        </div>";
        $body_text = "Monitor Added Successfully!\n\nYou have successfully added a new URL to monitor:\nURL: {$url}\nInterval: {$interval} minutes\n\nWe will notify you when visible changes are detected.\n\nDashboard: https://tefinitely.com/voila/index.php";

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
    <title>Add Monitor - Voila!</title>
    <link rel="stylesheet" href="<?php echo asset_v('voila/style.css'); ?>">
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
