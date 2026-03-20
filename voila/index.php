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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - URL Monitor</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="nav">
            <h1>URL Monitor</h1>
            <div>
                <span style="margin-right: 1rem; color: var(--muted-text);">Welcome, <strong><?php echo htmlspecialchars($_SESSION['email']); ?></strong></span>
                <a href="logout.php" class="btn btn-danger" style="padding: 0.4rem 0.8rem; font-size: 0.9rem;">Logout</a>
            </div>
        </div>

        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 style="margin: 0;">Your Monitors</h2>
                <a href="add_monitor.php" class="btn btn-primary">Add New Monitor</a>
            </div>

            <?php if (empty($monitors)): ?>
                <p style="text-align: center; color: var(--muted-text); margin: 3rem 0;">You don't have any monitors yet. Click "Add New Monitor" to get started!</p>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>URL</th>
                                <th>Interval</th>
                                <th>Last Checked</th>
                                <th>Status</th>
                                <th style="text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($monitors as $monitor): ?>
                                <tr>
                                    <td style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        <a href="<?php echo htmlspecialchars($monitor['url']); ?>" target="_blank" style="color: var(--text-color); text-decoration: none;"><?php echo htmlspecialchars($monitor['url']); ?></a>
                                    </td>
                                    <td><?php echo htmlspecialchars($monitor['interval_minutes']); ?> min</td>
                                    <td style="font-size: 0.9rem; color: var(--muted-text);"><?php echo htmlspecialchars($monitor['last_checked'] ?: 'Never'); ?></td>
                                    <td>
                                        <?php if ($monitor['last_hash']): ?>
                                            <span class="badge badge-active">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-pending">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: right;">
                                        <form action="delete_monitor.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                            <input type="hidden" name="id" value="<?php echo $monitor['id']; ?>">
                                            <button type="submit" class="btn btn-danger" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;" onclick="return confirm('Are you sure you want to delete this monitor?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
