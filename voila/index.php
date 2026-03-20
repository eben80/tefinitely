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
                                $remaining_seconds = 0;
                                $last_checked_utc = $monitor['last_checked'] ? $monitor['last_checked'] : null;
                                $created_at_utc = $monitor['created_at'];

                                if (!$monitor['is_paused']) {
                                    $base_time = $last_checked_utc ? strtotime($last_checked_utc) : strtotime($created_at_utc);
                                    $next_check_time = $base_time + ($monitor['interval_minutes'] * 60);
                                    $remaining_seconds = max(0, $next_check_time - time());
                                }
                            ?>
                                <tr class="monitor-row"
                                    data-id="<?php echo $monitor['id']; ?>"
                                    data-remaining="<?php echo $remaining_seconds; ?>"
                                    data-interval="<?php echo $monitor['interval_minutes'] * 60; ?>"
                                    data-paused="<?php echo $monitor['is_paused']; ?>"
                                    data-last-checked="<?php echo $last_checked_utc; ?>"
                                    data-last-changed="<?php echo $monitor['last_changed']; ?>"
                                    data-created-at="<?php echo $created_at_utc; ?>">
                                    <td data-label="URL" style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        <a href="<?php echo htmlspecialchars($monitor['url']); ?>" target="_blank" style="color: var(--text-color); text-decoration: none;"><?php echo htmlspecialchars($monitor['url']); ?></a>
                                    </td>
                                    <td data-label="Interval">
                                        <div style="display: flex; align-items: center; gap: 5px;">
                                            <input type="number"
                                                   class="form-control interval-input"
                                                   value="<?php echo htmlspecialchars($monitor['interval_minutes']); ?>"
                                                   min="1" step="1"
                                                   data-id="<?php echo $monitor['id']; ?>"
                                                   style="width: 70px; padding: 0.2rem 0.4rem; font-size: 0.9rem; margin: 0;">
                                            <span style="font-size: 0.85rem; color: var(--muted-text);">min</span>
                                            <button class="btn save-interval-btn"
                                                    data-id="<?php echo $monitor['id']; ?>"
                                                    title="Save Interval"
                                                    style="padding: 0.2rem 0.4rem; background: none; color: var(--primary-color); border: 1px solid var(--primary-color); font-size: 0.8rem; display: none;">
                                                <i class="fas fa-save"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td data-label="Next Check" style="font-size: 0.9rem; color: var(--muted-text);">
                                        <div class="next-check-display" style="margin-bottom: 5px;">N/A</div>
                                        <div class="progress-container" style="width: 100%; height: 6px; background-color: #eee; border-radius: 3px; overflow: hidden;">
                                            <div class="progress-bar" style="width: 0%; height: 100%; background-color: var(--primary-color); transition: width 1s linear;"></div>
                                        </div>
                                        <div class="last-checked-display" style="font-size: 0.75rem; margin-top: 5px;"></div>
                                        <div class="last-changed-display" style="font-size: 0.75rem; margin-top: 2px; color: var(--success-color); font-weight: 600;"></div>
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

            const timeFormatter = new Intl.DateTimeFormat(undefined, {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false
            });

            const dateTimeFormatter = new Intl.DateTimeFormat(undefined, {
                dateStyle: 'short',
                timeStyle: 'short'
            });

            function updateCountdowns() {
                let anyFinished = false;
                const now = Math.floor(Date.now() / 1000);

                document.querySelectorAll('.monitor-row').forEach(row => {
                    const isPaused = row.dataset.paused === '1';
                    const lastCheckedUtc = row.dataset.lastChecked;
                    const lastChangedUtc = row.dataset.lastChanged;
                    const createdAtUtc = row.dataset.createdAt;
                    const intervalSeconds = parseInt(row.dataset.interval);

                    const nextCheckDisplay = row.querySelector('.next-check-display');
                    const lastCheckedDisplay = row.querySelector('.last-checked-display');
                    const lastChangedDisplay = row.querySelector('.last-changed-display');
                    const progressBar = row.querySelector('.progress-bar');

                    // Update Last Checked display (Localized)
                    if (lastCheckedUtc && lastCheckedUtc !== '') {
                        const lastCheckedDate = new Date(lastCheckedUtc + ' UTC');
                        lastCheckedDisplay.textContent = 'Last check: ' + dateTimeFormatter.format(lastCheckedDate);
                    } else {
                        lastCheckedDisplay.textContent = 'Never checked';
                    }

                    // Update Last Changed display (Localized)
                    if (lastChangedUtc && lastChangedUtc !== '') {
                        const lastChangedDate = new Date(lastChangedUtc + ' UTC');
                        lastChangedDisplay.textContent = 'Last change: ' + dateTimeFormatter.format(lastChangedDate);
                    } else {
                        lastChangedDisplay.textContent = '';
                    }

                    if (isPaused) {
                        nextCheckDisplay.textContent = 'Paused';
                        progressBar.style.width = '0%';
                        return;
                    }

                    const baseTime = (lastCheckedUtc && lastCheckedUtc !== '') ? new Date(lastCheckedUtc + ' UTC') : new Date(createdAtUtc + ' UTC');
                    const nextCheckTimestamp = Math.floor(baseTime.getTime() / 1000) + intervalSeconds;
                    let remaining = nextCheckTimestamp - now;

                    if (remaining > 0) {
                        nextCheckDisplay.textContent = timeFormatter.format(new Date(nextCheckTimestamp * 1000));
                        const percent = ((intervalSeconds - remaining) / intervalSeconds) * 100;
                        progressBar.style.width = percent + '%';
                    } else {
                        nextCheckDisplay.textContent = 'Checking...';
                        progressBar.style.width = '100%';
                        anyFinished = true;
                    }
                });

                if (anyFinished) {
                    fetchMonitorData();
                }
            }

            let isFetching = false;
            function fetchMonitorData() {
                if (isFetching) return;
                isFetching = true;

                fetch('api_get_monitors.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            data.monitors.forEach(monitor => {
                                const row = document.querySelector(`.monitor-row[data-id="${monitor.id}"]`);
                                if (row) {
                                    row.dataset.lastChecked = monitor.last_checked || '';
                                    row.dataset.lastChanged = monitor.last_changed || '';
                                    row.dataset.paused = monitor.is_paused;
                                    row.dataset.interval = monitor.interval_minutes * 60;
                                }
                            });
                        }
                    })
                    .catch(err => console.error('Fetch error:', err))
                    .finally(() => {
                        setTimeout(() => { isFetching = false; }, 5000); // Polling throttle
                    });
            }

            const countdownInterval = setInterval(updateCountdowns, 1000);
            updateCountdowns(); // Initial run

            // Handle interval input changes
            document.querySelectorAll('.interval-input').forEach(input => {
                input.addEventListener('input', function() {
                    const row = this.closest('.monitor-row');
                    const saveBtn = row.querySelector('.save-interval-btn');
                    const originalValue = this.defaultValue;

                    if (this.value != originalValue && parseInt(this.value) > 0) {
                        saveBtn.style.display = 'inline-block';
                    } else {
                        saveBtn.style.display = 'none';
                    }
                });
            });

            // AJAX for saving interval
            document.querySelectorAll('.save-interval-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const row = this.closest('.monitor-row');
                    const input = row.querySelector('.interval-input');
                    const newInterval = parseInt(input.value);

                    if (!newInterval || newInterval <= 0) {
                        alert('Interval must be a positive integer.');
                        return;
                    }

                    const formData = new FormData();
                    formData.append('id', id);
                    formData.append('interval_minutes', newInterval);
                    formData.append('csrf_token', csrfToken);

                    fetch('update_interval.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            input.defaultValue = newInterval;
                            this.style.display = 'none';
                            // Update row data to reset countdown logic
                            row.dataset.interval = newInterval * 60;
                            // Optionally recalculate remaining time, or just reload to be safe
                            // But let's try updating data-remaining to be a full interval
                            row.dataset.remaining = newInterval * 60;
                        } else {
                            alert('Error: ' + data.message);
                        }
                    });
                });
            });

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
