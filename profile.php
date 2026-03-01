<?php
require_once 'api/auth_check.php';
checkAccess(false); // Does not require active subscription to view profile
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <base href="/">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="icon" href="img/favicon/favicon.ico" sizes="any">
    <link rel="icon" href="img/favicon/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="img/favicon/apple-touch-icon.png">
    <link rel="manifest" href="img/favicon/site.webmanifest">
    <link rel="stylesheet" href="css/toast.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <script src="js/paypal-util.js"></script>
    <script>
        loadPayPalSDK();
    </script>
    <style>
        .container { max-width: 900px; }
        h1, h2 { color: #333; }
        .subscription-prompt {
            max-width: 600px;
            margin: 4rem auto;
            background: #fff;
            padding: 2rem 2.5rem;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
        }
        .subscription-prompt h2 {
            font-size: 1.8rem;
            color: #004d99;
            margin-bottom: 1rem;
        }
        .subscription-prompt .lead {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 2rem;
        }
        .subscription-prompt .benefits-list {
            list-style-type: none;
            padding: 0;
            margin: 0 auto 2rem auto;
            text-align: left;
            max-width: 350px;
        }
        .subscription-prompt .benefits-list li {
            margin-bottom: 1rem;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
        }
        .subscription-prompt .benefits-list li::before {
            content: '✓';
            color: #28a745;
            font-size: 1.5rem;
            margin-right: 1rem;
        }
        #paypal-button-container {
            max-width: 300px;
            margin: 1rem auto 0 auto;
        }
        .profile-info, .form-section { margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 1px solid #ddd; }
        .profile-info p { font-size: 1.1rem; word-wrap: break-word; }
        .profile-info strong { color: #0056b3; }
        label { display: block; margin-bottom: 0.5rem; font-weight: bold; }
        input, textarea { width: 100%; padding: 0.5rem; font-size: 1rem; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; margin-bottom: 1rem; }
        button { padding: 0.7rem 1.5rem; font-size: 1rem; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background-color: #0056b3; }
        .form-toggle-links { margin-top: 1rem; }
        .form-toggle-links a { text-decoration: none; color: #007bff; margin-right: 1rem; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { border: 1px solid #ddd; padding: 0.75rem; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <header>
        <a href="index.html"><img src="img/top_logo_light.png" alt="TEFinitely Logo" class="logo" style="display: block; margin-left: auto; margin-right: auto;"></a>
    </header>
    <div id="toast-container"></div>
    <nav class="main-nav" id="user-status" style="display: none;">
    <button class="hamburger-menu" id="hamburger-menu" aria-label="Toggle menu" aria-expanded="false">
        <i class="bi bi-list"></i>
    </button>
    <div class="nav-content" id="nav-content">
        <div class="nav-links">
            <div class="dropdown">
                <a href="oral_expression.php" class="dropbtn">Oral Expression</a>
                <div class="dropdown-content">
                    <a href="oral_expression_section_a.php">Flashcards</a>
                    <a href="practise/section_a/index.php">Section A Practice</a>
                    <a href="practise/section_b/index.php">Section B Practice</a>
                </div>
                </div>
            <a href="training.php">Phased Training</a>
            <a href="profile.php">Profile</a>
            <a id="admin-link" href="admin.php" style="display: none;">Admin Portal</a>
        </div>
        <div class="nav-user">
            <span id="first-name-display"></span>
            <button id="logoutBtn">Logout</button>
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

        <div id="profile-details" class="profile-info">
            <h2>Account Information</h2>
            <p><strong>Name:</strong> <span id="first-name"></span> <span id="last-name"></span></p>
            <p><strong>Email:</strong> <span id="email"></span></p>
            <p><strong>Subscription Status:</strong> <span id="sub-status"></span></p>
            <p><strong>Subscription Ends:</strong> <span id="sub-end-date"></span></p>
            <div class="form-toggle-links">
                <a href="#" id="toggle-details-form">Update User Details</a>
                <a href="#" id="toggle-password-form">Change Password</a>
                <a href="#" id="restart-tour">Restart Guided Tour</a>
            </div>
        </div>

        <div id="update-details-section" class="form-section" style="display: none;">
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

        <div id="change-password-section" class="form-section" style="display: none;">
            <h2>Change Password</h2>
            <form id="updatePasswordForm">
                <label for="current-password">Current Password</label>
                <input type="password" id="current-password" required>
                <label for="new-password">New Password</label>
                <input type="password" id="new-password" required>
                <button type="submit">Update Password</button>
            </form>
        </div>

        <div class="form-section">
            <h2>Contact Support</h2>
            <p>Have a question or issue? Send us a message and we'll get back to you as soon as possible.</p>
            <form id="contact-support-form">
                <label for="support-subject">Subject</label>
                <input type="text" id="support-subject" required>
                <label for="support-message">Message</label>
                <textarea id="support-message" rows="5" required></textarea>
                <button type="submit">Submit Request</button>
            </form>
        </div>

        <div class="form-section">
            <h2>Your Progress</h2>
            <h3>Flashcard Progress</h3>
            <div id="progress-container"></div>
            <div style="margin-top: 1rem; margin-bottom: 2rem;">
                <button id="reset-stats-btn" style="background-color: #dc3545;">Reset Flashcard Stats</button>
            </div>

            <h3>Phase 1: Shadowing Performance</h3>
            <div id="dialogue-progress-container">
                <!-- Dialogue progress will be loaded here -->
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
                    <li><a href="oral_expression.php">Oral Expression</a></li>
                    <li><a href="training.php">Phased Training</a></li>
                    <li><a href="profile.php">Profile</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h4>Contact</h4>
                <p>Email us at<br><a href="mailto:support@tefinitely.com">support@tefinitely.com</a></p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© 2026 tefinitely.com | All Rights Reserved</p>
        </div>
    </footer>

    <script src="js/toast.js"></script>
    <script src="js/auth.js"></script>
    <script src="js/nav.js"></script>
    <script src="js/profile.js"></script>
    <script>
        document.getElementById('restart-tour').addEventListener('click', async () => {
            try {
                const response = await fetch('api/profile/update_user_details.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ tour_completed: false })
                });
                const data = await response.json();
                if (data.status === 'success') {
                    showToast('Guided tour has been reset. Redirecting...', 'success');
                    setTimeout(() => {
                        window.location.href = 'logged_in.php';
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

                let html = '<table>';
                html += '<tr><th>Theme</th><th>Section</th><th>Phrases Covered</th><th>Average Matching Quality</th></tr>';
                data.progress.forEach(item => {
                    html += `<tr><td>${formatName(item.theme)}</td><td>${formatName(item.section)}</td><td>${item.phrases_covered} out of ${item.total_phrases}</td><td>${(item.average_matching_quality * 100).toFixed(2)}%</td></tr>`;
                });
                html += '</table>';
                progressContainer.innerHTML = html;
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
                });
                html += '</table>';
                progressContainer.innerHTML = html;
            }
        }

        fetchDialogueProgress();

        document.getElementById('reset-stats-btn').addEventListener('click', async () => {
            if (confirm('Are you sure you want to reset your stats? This action cannot be undone.')) {
                try {
                    const response = await fetch('api/profile/reset_progress.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' }
                    });
                    const data = await response.json();
                    if (data.status === 'success') {
                        showToast('Your stats have been reset.', 'success');
                        fetchProgress();
                    } else {
                        showToast(data.message || 'Failed to reset stats.', 'error');
                    }
                } catch (error) {
                    showToast('An error occurred while resetting your stats.', 'error');
                }
            }
        });

        // Support Contact Form Logic
        document.getElementById('contact-support-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const subject = document.getElementById('support-subject').value;
            const message = document.getElementById('support-message').value;

            try {
                const response = await fetch('api/support/create_ticket.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ subject, message })
                });
                const data = await response.json();
                if (data.status === 'success') {
                    showToast('Your message has been sent. We will get back to you soon!', 'success');
                    document.getElementById('contact-support-form').reset();
                } else {
                    showToast(data.message || 'Failed to send message.', 'error');
                }
            } catch (error) {
                showToast('An error occurred. Please try again later.', 'error');
            }
        });
    </script>
</body>
</html>
