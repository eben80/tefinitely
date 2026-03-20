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
    <title>Dashboard - Voila!</title>
    <link rel="stylesheet" href="<?php echo asset_v('voila/style.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="nav">
            <h1>Voila!</h1>
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
                                <th>Next Check</th>
                                <th>Status</th>
                                <th style="text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($monitors as $monitor):
                                $next_check = 'N/A';
                                $remaining_seconds = 0;
                                if (!$monitor['is_paused']) {
                                    $base_time = $monitor['last_checked'] ? strtotime($monitor['last_checked']) : strtotime($monitor['created_at']);
                                    $next_check_time = $base_time + ($monitor['interval_minutes'] * 60);
                                    $remaining_seconds = max(0, $next_check_time - time());
                                    $next_check = date('H:i:s', $next_check_time);
                                }
                            ?>
                                <tr class="monitor-row"
                                    data-id="<?php echo $monitor['id']; ?>"
                                    data-remaining="<?php echo $remaining_seconds; ?>"
                                    data-interval="<?php echo $monitor['interval_minutes'] * 60; ?>"
                                    data-paused="<?php echo $monitor['is_paused']; ?>">
                                    <td data-label="URL" style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        <a href="<?php echo htmlspecialchars($monitor['url']); ?>" target="_blank" style="color: var(--text-color); text-decoration: none;"><?php echo htmlspecialchars($monitor['url']); ?></a>
                                    </td>
                                    <td data-label="Interval"><?php echo htmlspecialchars($monitor['interval_minutes']); ?> min</td>
                                    <td data-label="Next Check" style="font-size: 0.9rem; color: var(--muted-text);">
                                        <div style="margin-bottom: 5px;"><?php echo $next_check; ?></div>
                                        <div class="progress-container" style="width: 100%; height: 6px; background-color: #eee; border-radius: 3px; overflow: hidden;">
                                            <div class="progress-bar" style="width: 0%; height: 100%; background-color: var(--primary-color); transition: width 1s linear;"></div>
                                        </div>
                                    </td>
                                    <td data-label="Status">
                                        <?php if ($monitor['is_paused']): ?>
                                            <span class="badge" style="background-color: var(--error-color); color: #fff;">Paused</span>
                                        <?php elseif ($monitor['last_hash']): ?>
                                            <span class="badge badge-active">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-pending">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: right;">
                                        <button class="btn toggle-pause-btn"
                                                data-id="<?php echo $monitor['id']; ?>"
                                                data-paused="<?php echo $monitor['is_paused']; ?>"
                                                style="padding: 0.3rem 0.6rem; font-size: 0.8rem; background-color: var(--border-color); color: var(--text-color);">
                                            <i class="fas <?php echo $monitor['is_paused'] ? 'fa-play' : 'fa-pause'; ?>"></i>
                                            <?php echo $monitor['is_paused'] ? 'Resume' : 'Pause'; ?>
                                        </button>
                                        <button class="btn btn-danger delete-btn"
                                                data-id="<?php echo $monitor['id']; ?>"
                                                style="padding: 0.3rem 0.6rem; font-size: 0.8rem;">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const csrfToken = '<?php echo $_SESSION['csrf_token']; ?>';
            const rows = document.querySelectorAll('.monitor-row');

            function updateCountdowns() {
                let anyFinished = false;

                document.querySelectorAll('.monitor-row').forEach(row => {
                    const isPaused = row.dataset.paused === '1';
                    if (isPaused) return;

                    let remaining = parseInt(row.dataset.remaining);
                    const interval = parseInt(row.dataset.interval);
                    const progressBar = row.querySelector('.progress-bar');

                    if (remaining > 0) {
                        remaining--;
                        row.dataset.remaining = remaining;

                        const percent = ((interval - remaining) / interval) * 100;
                        progressBar.style.width = percent + '%';
                    } else {
                        anyFinished = true;
                        progressBar.style.width = '100%';
                    }
                });

                if (anyFinished) {
                    // Avoid multiple reloads by stopping countdowns
                    clearInterval(countdownInterval);
                    setTimeout(() => location.reload(), 2000);
                }
            }

            const countdownInterval = setInterval(updateCountdowns, 1000);
            updateCountdowns(); // Initial run

            // AJAX for Pause/Resume
            document.querySelectorAll('.toggle-pause-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const isPaused = parseInt(this.dataset.paused);
                    const newPaused = isPaused ? 0 : 1;

                    const formData = new FormData();
                    formData.append('id', id);
                    formData.append('is_paused', newPaused);
                    formData.append('csrf_token', csrfToken);

                    fetch('toggle_pause.php', {
                        method: 'POST',
                        body: formData
                    }).then(() => location.reload());
                });
            });

            // AJAX for Delete
            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    if (!confirm('Are you sure you want to delete this monitor?')) return;

                    const id = this.dataset.id;
                    const formData = new FormData();
                    formData.append('id', id);
                    formData.append('csrf_token', csrfToken);

                    fetch('delete_monitor.php', {
                        method: 'POST',
                        body: formData
                    }).then(() => location.reload());
                });
            });
        });
    </script>
</body>
</html>
