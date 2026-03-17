<?php
require_once 'api/auth_check.php';
checkAccess(false); // Does not require active subscription to view profile
?>
<!DOCTYPE html>
<html lang="en">
<head>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-NDEYBJ5FQB"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-NDEYBJ5FQB');
</script>

    <base href="/">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="icon" href="img/favicon/favicon.ico" sizes="any">
    <link rel="icon" href="img/favicon/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="img/favicon/apple-touch-icon.png">
    <link rel="manifest" href="img/favicon/site.webmanifest">
    <?php require_once 'api/version_helper.php'; ?>
    <link rel="stylesheet" href="<?= asset_v('css/toast.css') ?>">
    <link rel="stylesheet" href="<?= asset_v('css/main.css') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <script src="https://applepay.cdn-apple.com/jsapi/v1/apple-pay-sdk.js"></script>
    <script src="<?= asset_v('js/paypal-util.js') ?>"></script>
    <script>
        loadPayPalSDK();
    </script>
    <style>
        .container { max-width: 1000px; padding: 1.5rem; }
        h1, h2 { color: #333; }
        h2 { font-size: 1.4rem; margin-bottom: 0.75rem; }
        h3 { font-size: 1.2rem; margin-bottom: 0.5rem; }
        .subscription-prompt {
            max-width: 600px;
            margin: 2rem auto;
            background: #fff;
            padding: 1.5rem 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
        }
        .subscription-prompt h2 {
            font-size: 1.6rem;
            color: #004d99;
            margin-bottom: 0.75rem;
        }
        .subscription-prompt .lead {
            font-size: 1rem;
            color: #666;
            margin-bottom: 1.5rem;
        }
        .subscription-prompt .benefits-list {
            list-style-type: none;
            padding: 0;
            margin: 0 auto 1.5rem auto;
            text-align: left;
            max-width: 350px;
        }
        .subscription-prompt .benefits-list li {
            margin-bottom: 0.75rem;
            font-size: 1rem;
            display: flex;
            align-items: center;
        }
        .subscription-prompt .benefits-list li::before {
            content: '✓';
            color: #28a745;
            font-size: 1.3rem;
            margin-right: 0.75rem;
        }
        #paypal-button-container {
            max-width: 300px;
            margin: 1rem auto 0 auto;
        }
        .profile-info, .form-section { margin-bottom: 2rem; }
        .profile-info p { font-size: 1rem; word-wrap: break-word; margin-bottom: 0.5rem; }
        .profile-info strong { color: #0056b3; }
        label { display: block; margin-bottom: 0.4rem; font-weight: bold; font-size: 0.95rem; }
        input, textarea { width: 100%; padding: 0.4rem; font-size: 0.95rem; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; margin-bottom: 0.75rem; }
        button { padding: 0.6rem 1.2rem; font-size: 0.95rem; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background-color: #0056b3; }
        .form-toggle-links { margin-top: 0.75rem; }
        .form-toggle-links a { text-decoration: none; color: #007bff; margin-right: 1rem; font-size: 0.9rem; }
        table { width: 100%; border-collapse: collapse; margin-top: 0.75rem; font-size: 0.9rem; }
        th, td { border: 1px solid #ddd; padding: 0.6rem; text-align: left; }
        th { background-color: #f8f9fa; color: #333; }
        .table-container { overflow-x: auto; -webkit-overflow-scrolling: touch; }

        @media (max-width: 640px) {
            body { margin: 0; }
            .container { padding: 1rem; }
            .subscription-prompt { padding: 1.5rem; margin: 2rem auto; }
            button { width: 100%; margin-bottom: 0.5rem; }
            .form-toggle-links { display: flex; flex-direction: column; gap: 0.5rem; }
            .form-toggle-links a { margin-right: 0; }
        }
    </style>
</head>
<body>
    <header>
        <a href="index.php"><img src="img/top_logo_light.png" alt="TEFinitely Logo" class="logo"></a>
    </header>
    <div id="toast-container"></div>
    <nav class="main-nav" id="user-status">
    <button class="hamburger-menu" id="hamburger-menu" aria-label="Toggle menu" aria-expanded="false">
        <i class="bi bi-list"></i>
    </button>
    <div class="nav-content" id="nav-content">
        <div class="nav-links">
            <a href="profile.php">Dashboard</a>
            <div class="dropdown">
                <a href="oral_expression.php" class="dropbtn">Oral Expression</a>
                <div class="dropdown-content">
                    <div class="sub-dropdown">
                        <a href="javascript:void(0)" class="sub-dropbtn">TEF Canada <i class="bi bi-chevron-right"></i></a>
                        <div class="sub-dropdown-content">
                            <a href="oral_expression_section_a.php">Flashcards</a>
                            <a href="practise/tef_canada/section_a/index.php">Section A Practice</a>
                            <a href="practise/tef_canada/section_b/index.php">Section B Practice</a>
                        </div>
                    </div>
                    <div class="sub-dropdown">
                        <a href="javascript:void(0)" class="sub-dropbtn">CELPIP <i class="bi bi-chevron-right"></i></a>
                        <div class="sub-dropdown-content">
                            <a href="practise/celpip/section_a/index.php">Section A Practice</a>
                            <a href="practise/celpip/section_b/index.php">Section B Practice</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="dropdown">
                <a href="javascript:void(0)" class="dropbtn">Level Test</a>
                <div class="dropdown-content">
                    <a href="practise/french_level_test/index.php">French Level Test</a>
                </div>
            </div>
            <a href="training.php">Phased Training</a>
            <a href="support.php" class="nav-help-icon" title="Contact Support"><i class="bi bi-question-circle"></i></a>
            <a id="admin-link" href="admin.php" style="display: none;">Admin Portal</a>
        </div>
        <div class="nav-user">
            <span id="first-name-display"></span>
            <button id="logoutBtn" style="display: none;">Logout</button>
            <a href="login.php" id="nav-login-btn" class="btn-login">Login</a>
        </div>
        </div>
    </nav>

    <div class="container" id="page-container">
        <div id="subscription-prompt" class="subscription-prompt" style="display: none;">
            <h2>Unlock Your Full Potential</h2>
            <p class="lead">Your subscription is inactive. Subscribe now to get full access to all our training features.</p>
            <ul class="benefits-list">
                <li>Access all training modules</li>
                <li>Practice with unlimited phrases</li>
                <li>Track your progress</li>
                <li>Cancel anytime</li>
            </ul>
            <div id="paypal-button-container"></div>
        </div>

        <div class="dashboard-grid">
            <!-- Account Summary Card -->
            <div class="summary-card clickable" id="toggle-account-details">
                <div class="card-info">
                    <span class="card-label">Account Info</span>
                    <span class="card-value"><span id="summary-name">Loading...</span></span>
                    <span class="card-subtext" id="summary-sub-status">Status: Loading...</span>
                </div>
                <div class="card-icon"><i class="bi bi-person-circle"></i></div>
            </div>

            <!-- Flashcard Summary Card -->
            <div class="summary-card clickable" id="toggle-flashcard">
                <div class="card-info">
                    <span class="card-label">Flashcard Progress</span>
                    <span class="card-value" id="summary-flashcard-count">0 Phrases</span>
                    <span class="card-subtext" id="summary-flashcard-avg">Avg: 0%</span>
                </div>
                <div class="card-icon"><i class="bi bi-card-text"></i></div>
            </div>

            <!-- Shadowing Summary Card -->
            <div class="summary-card clickable" id="toggle-shadowing">
                <div class="card-info">
                    <span class="card-label">Shadowing Stats</span>
                    <span class="card-value" id="summary-shadowing-coverage">0% Coverage</span>
                    <span class="card-subtext" id="summary-shadowing-avg">Avg Score: 0%</span>
                </div>
                <div class="card-icon"><i class="bi bi-mic"></i></div>
            </div>
        </div>

        <!-- Collapsible Account Details -->
        <div class="collapsible-wrapper" id="account-details-collapsible">
            <div class="collapsible-content">
                <div id="profile-details" class="profile-info" style="border-bottom: none; padding-bottom: 0;">
                    <h2>Account Information</h2>
                    <p><strong>Name:</strong> <span id="first-name"></span> <span id="last-name"></span></p>
                    <p><strong>Email:</strong> <span id="email"></span></p>
                    <p><strong>Subscription Status:</strong> <span id="sub-status"></span></p>
                    <p><strong>Subscription Ends:</strong> <span id="sub-end-date"></span></p>
                    <div class="form-toggle-links">
                        <a href="javascript:void(0)" id="toggle-subscription-prompt" style="font-weight: bold; color: #28a745;">Manage Subscription</a>
                        <a href="#" id="toggle-details-form">Update User Details</a>
                        <a href="#" id="toggle-password-form">Change Password</a>
                        <a href="#" id="restart-tour">Restart Guided Tour</a>
                    </div>
                </div>

                <div id="update-details-section" class="form-section" style="display: none; margin-top: 1.5rem;">
                    <h2>Update User Details</h2>
                    <form id="updateDetailsForm">
                        <label for="new-first-name">First Name</label>
                        <input type="text" id="new-first-name" required>
                        <label for="new-last-name">Last Name</label>
                        <input type="text" id="new-last-name" required>
                        <label for="new-email">Email Address</label>
                        <input type="email" id="new-email" required>
                        <button type="submit">Update Details</button>
                    </form>
                </div>

                <div id="change-password-section" class="form-section" style="display: none; margin-top: 1.5rem;">
                    <h2>Change Password</h2>
                    <form id="updatePasswordForm">
                        <label for="current-password">Current Password</label>
                        <input type="password" id="current-password" required>
                        <label for="new-password">New Password</label>
                        <input type="password" id="new-password" required>
                        <button type="submit">Update Password</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Collapsible Flashcard Details -->
        <div class="collapsible-wrapper" id="flashcard-collapsible">
            <div class="collapsible-content">
                <h2>Flashcard Progress</h2>
                <div id="progress-container" class="table-container"></div>
                <div style="margin-top: 1.5rem;">
                    <button id="reset-stats-btn" style="background-color: #dc3545; padding: 0.5rem 1rem; font-size: 0.85rem;">Reset Flashcard Stats</button>
                </div>
            </div>
        </div>

        <!-- Collapsible Shadowing Details -->
        <div class="collapsible-wrapper" id="shadowing-collapsible">
            <div class="collapsible-content">
                <h2>Phase 1: Shadowing Performance</h2>
                <div id="dialogue-progress-container" class="table-container">
                    <!-- Dialogue progress will be loaded here -->
                </div>
                <div style="margin-top: 1.5rem;">
                    <button id="reset-shadowing-btn" style="background-color: #dc3545; padding: 0.5rem 1rem; font-size: 0.85rem;">Reset Shadowing Stats</button>
                </div>
            </div>
        </div>

        <div class="form-section" id="level-test-history-section">
            <h2>French Level Test</h2>
            <div id="latest-level-container">
                <!-- Latest result card will be injected here -->
            </div>

            <div class="collapsible-wrapper" id="history-collapsible">
                <div class="collapsible-header" id="toggle-history">
                    <h3>Full Test History</h3>
                    <i class="bi bi-chevron-down collapsible-icon"></i>
                </div>
                <div class="collapsible-content">
                    <div id="level-history-container" class="table-container">
                        <p>Loading test history...</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <footer class="landing-footer">
        <div class="footer-grid">
            <div class="footer-column">
                <img src="img/top_logo_light.png" alt="TEFinitely Logo" class="footer-logo">
                <p>Your guide to succeeding on the TEF Canada exam.</p>
            </div>
            <div class="footer-column">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="profile.php">Dashboard</a></li><li><a href="oral_expression.php">Oral Expression</a></li>
                    <li><a href="training.php">Phased Training</a></li>

                </ul>
            </div>
            <div class="footer-column">
                <h4>Legal</h4>
                <ul>
                    <li><a href="terms.php">Terms of Service</a></li>
                    <li><a href="privacy.php">Privacy Policy</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h4>Contact</h4>
                <p><a href="mailto:tefinitely@gmail.com">Email Us</a></p>
                <p><a href="javascript:void(0)" onclick="window.showBrowserSupportPopup()">Supported OS / Browsers</a></p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© 2026 tefinitely.com | All Rights Reserved</p>
        </div>
    </footer>

    <script src="<?= asset_v('js/toast.js') ?>"></script>
    <script src="<?= asset_v('js/browser-support.js') ?>"></script>
    <script src="<?= asset_v('js/auth.js') ?>"></script>
    <script src="<?= asset_v('js/nav.js') ?>"></script>
    <script src="<?= asset_v('js/profile.js') ?>"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('restart-tour').addEventListener('click', async () => {
            try {
                const response = await fetch('api/profile/update_user_details.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        tour_completed: false,
                        tour_section_a_completed: false,
                        tour_section_b_completed: false
                    })
                });
                const data = await response.json();
                if (data.status === 'success') {
                    showToast('Guided tour has been reset. Redirecting...', 'success');
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 1000);
                } else {
                    showToast('Failed to reset the guided tour. Please try again.', 'error');
                }
            } catch (error) {
                showToast('An error occurred. Please try again later.', 'error');
            }
        });

        function formatName(name) {
            return name.replace(/_/g, ' ').replace(/\b\w/g, char => char.toUpperCase());
        }

        async function fetchProgress() {
            const response = await fetch('api/profile/get_progress.php');
            const data = await response.json();

            if (data.status === 'success') {
                const progressContainer = document.getElementById('progress-container');
                if (data.progress.length === 0) {
                    progressContainer.innerHTML = '<p>No progress yet. Start practicing!</p>';
                    return;
                }

                let totalPhrasesCovered = 0;
                let totalAverageQuality = 0;
                let topicsWithProgress = 0;

                let html = '<table>';
                html += '<tr><th>Theme</th><th>Phrases Covered</th><th>Average Matching Quality</th></tr>';
                data.progress.forEach(item => {
                    html += `<tr><td>${formatName(item.theme)}</td><td>${item.phrases_covered} out of ${item.total_phrases}</td><td>${(item.average_matching_quality * 100).toFixed(2)}%</td></tr>`;

                    totalPhrasesCovered += item.phrases_covered;
                    if (item.phrases_covered > 0) {
                        totalAverageQuality += item.average_matching_quality;
                        topicsWithProgress++;
                    }
                });
                html += '</table>';
                progressContainer.innerHTML = html;

                // Update summary card
                document.getElementById('summary-flashcard-count').textContent = `${totalPhrasesCovered} Phrases`;
                const avgQuality = topicsWithProgress > 0 ? (totalAverageQuality / topicsWithProgress * 100).toFixed(0) : 0;
                document.getElementById('summary-flashcard-avg').textContent = `Avg Quality: ${avgQuality}%`;
            }
        }

        fetchProgress();

        async function fetchDialogueProgress() {
            const response = await fetch('api/profile/get_dialogue_progress.php');
            const data = await response.json();

            if (data.status === 'success') {
                const progressContainer = document.getElementById('dialogue-progress-container');
                if (data.progress.length === 0) {
                    progressContainer.innerHTML = '<p>No progress yet for Phase 1. Start practicing!</p>';
                    return;
                }

                let totalCoverage = 0;
                let totalScore = 0;
                let dialogueCount = data.progress.length;
                let attemptedDialoguesCount = 0;

                let html = '<table>';
                html += '<tr><th>Dialogue</th><th>Coverage</th><th>Average Score</th></tr>';
                data.progress.forEach(item => {
                    const coveragePercent = (item.coverage * 100).toFixed(0);
                    const scorePercent = (item.average_score * 100).toFixed(2);
                    html += `<tr>
                                <td>${item.dialogue_name}</td>
                                <td>${coveragePercent}% (${item.attempted_lines}/${item.total_lines} lines)</td>
                                <td>${scorePercent}%</td>
                             </tr>`;

                    totalCoverage += item.coverage;
                    if (item.attempted_lines > 0) {
                        totalScore += item.average_score;
                        attemptedDialoguesCount++;
                    }
                });
                html += '</table>';
                progressContainer.innerHTML = html;

                // Update summary card
                const avgCoverage = dialogueCount > 0 ? (totalCoverage / dialogueCount * 100).toFixed(0) : 0;
                const avgScore = attemptedDialoguesCount > 0 ? (totalScore / attemptedDialoguesCount * 100).toFixed(0) : 0;
                document.getElementById('summary-shadowing-coverage').textContent = `${avgCoverage}% Coverage`;
                document.getElementById('summary-shadowing-avg').textContent = `Avg Score: ${avgScore}%`;
            }
        }

        fetchDialogueProgress();

        async function fetchLevelHistory() {
            try {
                const response = await fetch('api/level_test/get_history.php');
                const data = await response.json();
                const container = document.getElementById('level-history-container');
                const latestContainer = document.getElementById('latest-level-container');

                if (data.status === 'success') {
                    if (data.history.length === 0) {
                        container.innerHTML = '<p>No test history yet. Take your first level test to see your progress!</p>';
                        latestContainer.innerHTML = '<p>No results yet.</p>';
                        return;
                    }

                    const sortedHistory = data.history.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
                    const latest = sortedHistory[0];
                    const latestDate = new Date(latest.created_at).toLocaleDateString();

                    latestContainer.innerHTML = `
                        <div class="latest-level-card">
                            <div class="level-info">
                                <span class="level-label">Latest Estimated Level</span>
                                <span class="level-value">${latest.estimated_level}</span>
                                <span class="level-date">Assessed on ${latestDate}</span>
                            </div>
                            <div class="level-score">
                                <strong>Score:</strong> ${latest.score} / 20
                            </div>
                        </div>
                    `;

                    let html = '<table>';
                    html += '<tr><th>Date</th><th>Score</th><th>Estimated Level</th></tr>';
                    sortedHistory.forEach(item => {
                        const date = new Date(item.created_at).toLocaleDateString();
                        html += `<tr>
                                    <td>${date}</td>
                                    <td>${item.score} / 20</td>
                                    <td><strong>${item.estimated_level}</strong></td>
                                 </tr>`;
                    });
                    html += '</table>';
                    container.innerHTML = html;
                } else {
                    container.innerHTML = '<p>Failed to load level history.</p>';
                }
            } catch (error) {
                console.error('Error fetching level history:', error);
            }
        }

        fetchLevelHistory();

        document.getElementById('reset-stats-btn').addEventListener('click', async () => {
            if (confirm('Are you sure you want to reset your flashcard stats? This action cannot be undone.')) {
                try {
                    const response = await fetch('api/profile/reset_progress.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' }
                    });
                    const data = await response.json();
                    if (data.status === 'success') {
                        showToast('Your flashcard stats have been reset.', 'success');
                        fetchProgress();
                    } else {
                        showToast(data.message || 'Failed to reset stats.', 'error');
                    }
                } catch (error) {
                    showToast('An error occurred while resetting your stats.', 'error');
                }
            }
        });

        document.getElementById('reset-shadowing-btn').addEventListener('click', async () => {
            if (confirm('Are you sure you want to reset your shadowing stats? This action cannot be undone.')) {
                try {
                    const response = await fetch('api/profile/reset_dialogue_progress.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' }
                    });
                    const data = await response.json();
                    if (data.status === 'success') {
                        showToast('Your shadowing stats have been reset.', 'success');
                        fetchDialogueProgress();
                    } else {
                        showToast(data.message || 'Failed to reset stats.', 'error');
                    }
                } catch (error) {
                    showToast('An error occurred while resetting your stats.', 'error');
                }
            }
        });

        // Collapsible Logic
        const toggles = [
            { btn: 'toggle-account-details', wrapper: 'account-details-collapsible' },
            { btn: 'toggle-history', wrapper: 'history-collapsible' },
            { btn: 'toggle-flashcard', wrapper: 'flashcard-collapsible' },
            { btn: 'toggle-shadowing', wrapper: 'shadowing-collapsible' }
        ];

        toggles.forEach(t => {
            const btn = document.getElementById(t.btn);
            const wrapper = document.getElementById(t.wrapper);
            if (btn && wrapper) {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    // Close others
                    toggles.forEach(other => {
                        if (other.wrapper !== t.wrapper) {
                            document.getElementById(other.wrapper).classList.remove('is-open');
                        }
                    });
                    wrapper.classList.toggle('is-open');
                });
            }
        });

        const toggleSubBtn = document.getElementById('toggle-subscription-prompt');
        if (toggleSubBtn) {
            toggleSubBtn.addEventListener('click', () => {
                const prompt = document.getElementById('subscription-prompt');
                if (prompt) {
                    prompt.style.display = prompt.style.display === 'none' ? 'block' : 'none';
                    if (prompt.style.display === 'block') {
                        prompt.scrollIntoView({ behavior: 'smooth' });
                    }
                }
            });
        }

        });
    </script>
<script src="<?= asset_v('js/cookie-banner.js') ?>"></script>
</body>
</html>
