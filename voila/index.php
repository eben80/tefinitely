<?php
require_once 'config.php';

if (!is_logged_in()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM monitors WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$monitors = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - URL Monitor</title>
</head>
<body>
    <h1>URL Monitor Dashboard</h1>
    <p>Welcome, <?php echo htmlspecialchars($_SESSION['email']); ?>! <a href="logout.php">Logout</a></p>

    <h2>Your Monitors</h2>
    <a href="add_monitor.php">Add New Monitor</a>
    <table border="1">
        <thead>
            <tr>
                <th>URL</th>
                <th>Interval (min)</th>
                <th>Last Checked</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($monitors as $monitor): ?>
                <tr>
                    <td><?php echo htmlspecialchars($monitor['url']); ?></td>
                    <td><?php echo htmlspecialchars($monitor['interval_minutes']); ?></td>
                    <td><?php echo htmlspecialchars($monitor['last_checked'] ?: 'Never'); ?></td>
                    <td><?php echo $monitor['last_hash'] ? 'Active' : 'Pending'; ?></td>
                    <td>
                        <form action="delete_monitor.php" method="POST" style="display:inline;">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="id" value="<?php echo $monitor['id']; ?>">
                            <button type="submit" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
