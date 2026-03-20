<?php
require_once 'config.php';

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
        redirect('index.php');
    } else {
        $error = "Please provide a valid URL and interval.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Monitor - URL Monitor</title>
</head>
<body>
    <h2>Add New Monitor</h2>
    <?php if ($error): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <label>URL to monitor:</label><br>
        <input type="url" name="url" required placeholder="https://example.com"><br>
        <label>Interval (minutes):</label><br>
        <input type="number" name="interval_minutes" value="60" min="1" required><br>
        <button type="submit">Add Monitor</button>
    </form>
    <p><a href="index.php">Back to Dashboard</a></p>
</body>
</html>
