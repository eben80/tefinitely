<?php
require_once 'api/auth_check.php';
checkAccess(true, true); // Requires active subscription and admin role
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <base href="/">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="icon" href="img/favicon/favicon.ico" sizes="any">
    <link rel="icon" href="img/favicon/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="img/favicon/apple-touch-icon.png">
    <link rel="manifest" href="img/favicon/site.webmanifest">
    <link rel="stylesheet" href="css/toast.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .container { max-width: 1200px; }
        h1, h2 { color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; background: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        th, td { padding: 0.8rem; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #007bff; color: white; }
        tr:hover { background-color: #f1f1f1; }
        select { padding: 0.3rem; font-size: 0.9rem; }
        .status-active { color: green; font-weight: bold; }
        .status-inactive { color: red; font-weight: bold; }
        .table-container { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .header-with-button { display: flex; justify-content: space-between; align-items: center; }
        .action-btn { background-color: #28a745; color: white; padding: 0.5rem 1rem; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem; }
        .action-btn:hover { background-color: #218838; }
        .delete-user-btn { background-color: #dc3545; color: white; }
        .delete-user-btn:hover { background-color: #c82333; }
        .edit-user-btn { background-color: #007bff; color: white; }
        .edit-user-btn:hover { background-color: #0069d9; }
        td button { padding: 0.3rem 0.6rem; border: none; border-radius: 4px; cursor: pointer; margin-right: 0.3rem; }
        .modal { display: none; position: fixed; z-index: 1001; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4); }
        .modal-content { background-color: #fefefe; margin: 15% auto; padding: 20px; border: 1px solid #888; width: 80%; max-width: 500px; border-radius: 8px; }
        .close-btn { color: #aaa; float: right; font-size: 28px; font-weight: bold; }
        .close-btn:hover, .close-btn:focus { color: black; text-decoration: none; cursor: pointer; }
        .modal input { width: 100%; padding: 0.5rem; font-size: 1rem; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; margin-bottom: 1rem; }
        .modal button { width: 100%; }
        .modal hr { margin: 1.5rem 0; }
    </style>
</head>
<body>
    <header>
        <a href="index.html"><img src="img/top_logo_light.png" alt="TEFinitely Logo" class="logo" style="display: block; margin-left: auto; margin-right: auto;"></a>
    </header>
    <div id="toast-container"></div>
    <nav class="main-nav" id="user-status" style="display: none;">
        <div class="nav-links">
            <a href="oral_expression.php">Oral Expression</a>
            <a href="training.php">Phased Training</a>
            <a href="profile.php">Profile</a>
            <a id="admin-link" href="admin.php" style="display: none;">Admin Portal</a>
        </div>
        <div class="nav-user">
            <span id="first-name-display"></span>
            <button id="logoutBtn">Logout</button>
        </div>
    </nav>
    <div class="container">
        <div id="admin-dashboard" style="display: none;">
            <h1>Admin Dashboard</h1>
            <div id="admin-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
                <div style="background: white; padding: 1rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                    <h3 style="margin: 0; color: #666; font-size: 0.9rem;">Total OpenAI Calls (24h)</h3>
                    <p id="stat-calls-24h" style="margin: 0.5rem 0 0; font-size: 1.5rem; font-weight: bold; color: #007bff;">0</p>
                </div>
                <div style="background: white; padding: 1rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                    <h3 style="margin: 0; color: #666; font-size: 0.9rem;">Total OpenAI Calls (7d)</h3>
                    <p id="stat-calls-7d" style="margin: 0.5rem 0 0; font-size: 1.5rem; font-weight: bold; color: #007bff;">0</p>
                </div>
                <div style="background: white; padding: 1rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                    <h3 style="margin: 0; color: #666; font-size: 0.9rem;">Total OpenAI Calls (Life)</h3>
                    <p id="stat-calls-life" style="margin: 0.5rem 0 0; font-size: 1.5rem; font-weight: bold; color: #007bff;">0</p>
                </div>
            </div>
            <div style="display: flex; gap: 1rem; margin-bottom: 2rem; border-bottom: 1px solid #ddd;">
                <button class="tab-btn active" data-tab="user-management" style="padding: 0.5rem 1rem; border: none; background: none; cursor: pointer; border-bottom: 2px solid #007bff; font-weight: bold;">User Management</button>
                <button class="tab-btn" data-tab="audit-logs" style="padding: 0.5rem 1rem; border: none; background: none; cursor: pointer; font-weight: bold;">Audit Logs</button>
                <button class="tab-btn" data-tab="login-history" style="padding: 0.5rem 1rem; border: none; background: none; cursor: pointer; font-weight: bold;">Login History</button>
            </div>

            <div id="user-management-tab" class="tab-content">
            <div class="header-with-button">
                <h2>User Management</h2>
                <button id="add-user-btn" class="action-btn">Add New User</button>
            </div>
            <div id="filter-controls" style="display: flex; gap: 1rem; margin-bottom: 1rem; flex-wrap: wrap; align-items: flex-end;">
                <div style="flex: 1; min-width: 200px;">
                    <label for="search-input" style="display: block; font-size: 0.9rem; margin-bottom: 0.2rem;">Search Name or Email</label>
                    <input type="text" id="search-input" placeholder="Search..." style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                </div>
                <div>
                    <label for="filter-role" style="display: block; font-size: 0.9rem; margin-bottom: 0.2rem;">Role</label>
                    <select id="filter-role" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                        <option value="all">All Roles</option>
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div>
                    <label for="filter-status" style="display: block; font-size: 0.9rem; margin-bottom: 0.2rem;">Status</label>
                    <select id="filter-status" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                        <option value="all">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <button id="reset-filters-btn" class="action-btn" style="background-color: #6c757d;">Reset</button>
            </div>
            <div class="table-container">
                <table id="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Sub Status</th>
                            <th>Sub End Date</th>
                            <th>OpenAI Calls (1h/24h/7d/30d/Life)</th>
                            <th>Created At</th>
                            <th>Manage Sub</th>
                            <th>Actions</th>
                            <th>Support</th>
                        </tr>
                </thead>
                <tbody id="users-table-body">
                    <!-- User rows will be inserted here by JavaScript -->
                </tbody>
                </table>
            </div>
            </div> <!-- End user-management-tab -->

            <div id="audit-logs-tab" class="tab-content" style="display: none;">
                <h2>Admin Audit Logs</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Admin</th>
                                <th>Action</th>
                                <th>Target User</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody id="audit-logs-table-body">
                            <!-- Logs will be inserted here -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="login-history-tab" class="tab-content" style="display: none;">
                <h2>Login History</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Email</th>
                                <th>IP Address</th>
                                <th>Status</th>
                                <th>User Info</th>
                                <th>User Agent</th>
                            </tr>
                        </thead>
                        <tbody id="login-history-table-body">
                            <!-- History will be inserted here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div id="auth-error" style="display: none; text-align: center; margin-top: 5rem;">
            <h2>Access Denied</h2>
            <p>You must be an administrator to view this page. <a href="login.html">Login as Admin</a></p>
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
            <p>© 2025 TEFinitely.ca | All Rights Reserved</p>
        </div>
    </footer>

    <!-- Modal for editing user -->
    <div id="edit-user-modal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h2>Edit User: <span id="modal-user-name"></span></h2>
            <form id="edit-email-form">
                <label for="modal-email">Email Address</label>
                <input type="email" id="modal-email" required>
                <button type="submit">Update Email</button>
            </form>
            <hr>
            <form id="edit-password-form">
                <label for="modal-password">New Password (leave blank if no change)</label>
                <input type="text" id="modal-password">
                <button type="submit">Update Password</button>
            </form>
            <hr>
            <form id="edit-subscription-form">
                <label for="modal-sub-start">Subscription Start (YYYY-MM-DD)</label>
                <input type="text" id="modal-sub-start" placeholder="YYYY-MM-DD HH:MM:SS">
                <label for="modal-sub-end">Subscription End (YYYY-MM-DD)</label>
                <input type="text" id="modal-sub-end" placeholder="YYYY-MM-DD HH:MM:SS">
                <button type="submit">Update Subscription Dates</button>
            </form>
        </div>
    </div>

    <!-- Modal for viewing OpenAI calls -->
    <div id="openai-calls-modal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h2>OpenAI Call History: <span id="modal-openai-user-name"></span></h2>
            <div style="margin-bottom: 1rem; display: flex; gap: 1rem; align-items: center;">
                <label for="openai-timeframe">Timeframe:</label>
                <select id="openai-timeframe">
                    <option value="1h">Last Hour</option>
                    <option value="24h">Last 24 Hours</option>
                    <option value="7d">Last 7 Days</option>
                    <option value="30d">Last 30 Days</option>
                    <option value="lifetime" selected>Lifetime</option>
                </select>
                <button id="export-csv-btn" class="action-btn" style="background-color: #17a2b8;">Export to CSV</button>
            </div>
            <div class="table-container" style="max-height: 400px; overflow-y: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>OpenAI ID</th>
                            <th>Model</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody id="openai-calls-table-body">
                        <!-- Call logs will be inserted here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal for sending email -->
    <div id="send-email-modal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h2>Send Email to <span id="email-modal-user-name"></span></h2>
            <form id="send-email-form">
                <input type="hidden" id="email-user-id">
                <label for="email-subject">Subject</label>
                <input type="text" id="email-subject" required placeholder="Subject">
                <label for="email-message">Message</label>
                <textarea id="email-message" required style="width: 100%; height: 150px; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; margin-bottom: 1rem; font-family: inherit;"></textarea>
                <button type="submit" class="action-btn">Send Email</button>
            </form>
        </div>
    </div>

    <!-- Modal for adding a new user -->
    <div id="add-user-modal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h2>Add New User</h2>
            <form id="add-user-form">
                <label for="add-first-name">First Name</label>
                <input type="text" id="add-first-name" required>
                <label for="add-last-name">Last Name</label>
                <input type="text" id="add-last-name" required>
                <label for="add-email">Email Address</label>
                <input type="email" id="add-email" required>
                <label for="add-password">Password</label>
                <input type="password" id="add-password" required>
                <label for="add-role">Role</label>
                <select id="add-role">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
                <button type="button" id="create-user-btn">Create User</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="js/toast.js"></script>
    <script src="js/admin.js"></script>
    <script>
        flatpickr("#modal-sub-start", {
            enableTime: true,
            dateFormat: "Y-m-d H:i:S",
        });
        flatpickr("#modal-sub-end", {
            enableTime: true,
            dateFormat: "Y-m-d H:i:S",
        });
    </script>
</body>
</html>
