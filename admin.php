<?php
require_once 'api/auth_check.php';
checkAccess(true, true); // Admin only
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
        .container { max-width: 95%; margin: 0 auto; }
        h1, h2 { color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; background: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        th, td { padding: 0.8rem; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #007bff; color: white; }
        tr:hover { background-color: #f1f1f1; }
        select, input[type="text"], input[type="email"], input[type="search"] { padding: 0.5rem; font-size: 0.9rem; border: 1px solid #ccc; border-radius: 4px; }
        .status-active { color: green; font-weight: bold; }
        .status-inactive { color: red; font-weight: bold; }
        .status-open { color: #dc3545; font-weight: bold; }
        .status-in-progress { color: #ffc107; font-weight: bold; }
        .status-resolved { color: #28a745; font-weight: bold; }
        .table-container { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .header-with-button { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 1rem; }
        .action-btn { background-color: #28a745; color: white; padding: 0.5rem 1rem; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem; }
        .action-btn:hover { background-color: #218838; }
        .delete-user-btn { background-color: #dc3545; color: white; }
        .delete-user-btn:hover { background-color: #c82333; }
        .edit-user-btn { background-color: #007bff; color: white; }
        .edit-user-btn:hover { background-color: #0069d9; }
        td button { padding: 0.3rem 0.6rem; border: none; border-radius: 4px; cursor: pointer; margin-right: 0.3rem; }
        .modal { display: none; position: fixed; z-index: 2001; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4); }
        .modal-content { background-color: #fefefe; margin: 10% auto; padding: 20px; border: 1px solid #888; width: 80%; max-width: 600px; border-radius: 8px; }
        .close-btn { color: #aaa; float: right; font-size: 28px; font-weight: bold; }
        .close-btn:hover, .close-btn:focus { color: black; text-decoration: none; cursor: pointer; }
        .modal input, .modal textarea, .modal select { width: 100%; padding: 0.5rem; font-size: 1rem; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; margin-bottom: 1rem; }
        .modal button { width: 100%; padding: 0.7rem; }
        .modal hr { margin: 1.5rem 0; }

        .stat-card { background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); text-align: center; }
        .stat-card h3 { margin: 0; color: #666; font-size: 1rem; }
        .stat-card p { margin: 0.5rem 0 0; font-size: 2rem; font-weight: bold; color: #007bff; }

        /* Tabs Styles */
        .tabs { display: flex; border-bottom: 2px solid #ddd; margin-bottom: 1rem; }
        .tab-link { padding: 0.7rem 1.5rem; cursor: pointer; border: none; background: none; font-size: 1rem; font-weight: bold; color: #555; }
        .tab-link.active { color: #007bff; border-bottom: 3px solid #007bff; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }

        /* Filter bar */
        .filter-bar { display: flex; gap: 1rem; margin-bottom: 1rem; flex-wrap: wrap; align-items: center; background: #f8f9fa; padding: 1rem; border-radius: 8px; }
        .filter-group { display: flex; align-items: center; gap: 0.5rem; }
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

            <div class="tabs">
                <button class="tab-link active" data-tab="user-management">User Management</button>
                <button class="tab-link" data-tab="payment-settings">Payment Settings</button>
                <button class="tab-link" data-tab="financial-overview">Financial Overview</button>
                <button class="tab-link" data-tab="audit-logs">Audit Logs</button>
                <button class="tab-link" data-tab="login-history">Login History</button>
                <button class="tab-link" data-tab="support-tickets">Support Tickets</button>
            </div>

            <div id="user-management-tab" class="tab-content active">
                <div class="header-with-button">
                    <h2>User Management</h2>
                    <div style="display: flex; gap: 0.5rem;">
                        <button id="add-user-btn" class="action-btn">Add New User</button>
                    </div>
                </div>

                <div style="margin-bottom: 1rem; display: flex; gap: 0.5rem; align-items: center; background: #e9ecef; padding: 1rem; border-radius: 8px;">
                    <strong>Bulk Actions:</strong>
                    <button id="bulk-email-btn" class="action-btn" style="background-color: #17a2b8;">Email Selected</button>
                    <button id="bulk-activate-btn" class="action-btn" style="background-color: #28a745;">Activate Selected</button>
                    <button id="bulk-deactivate-btn" class="action-btn" style="background-color: #ffc107; color: #333;">Deactivate Selected</button>
                    <button id="bulk-delete-btn" class="action-btn" style="background-color: #dc3545;">Delete Selected</button>
                </div>

                <div class="filter-bar">
                    <div class="filter-group">
                        <label for="search-input">Search:</label>
                        <input type="search" id="search-input" placeholder="Name or Email...">
                    </div>
                    <div class="filter-group">
                        <label for="filter-role">Role:</label>
                        <select id="filter-role">
                            <option value="all">All Roles</option>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="filter-status">Status:</label>
                        <select id="filter-status">
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
                                <th><input type="checkbox" id="select-all-users"></th>
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
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody id="users-table-body">
                            <!-- User rows will be inserted here by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="audit-logs-tab" class="tab-content">
                <h2>Audit Logs</h2>
                <div class="table-container">
                    <table id="audit-logs-table">
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
                            <!-- Audit logs will be inserted here -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="login-history-tab" class="tab-content">
                <h2>Login History</h2>
                <div class="table-container">
                    <table id="login-history-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Email</th>
                                <th>IP Address</th>
                                <th>Status</th>
                                <th>User</th>
                                <th>User Agent</th>
                            </tr>
                        </thead>
                        <tbody id="login-history-table-body">
                            <!-- Login history will be inserted here -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="payment-settings-tab" class="tab-content">
                <div class="header-with-button">
                    <h2>Payment Settings (Plans & One-Time)</h2>
                    <button id="add-plan-btn" class="action-btn">Add New Plan</button>
                </div>
                <div class="table-container">
                    <table id="plans-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Price</th>
                                <th>Currency</th>
                                <th>Duration (Days)</th>
                                <th>PayPal Plan ID</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="plans-table-body">
                            <!-- Plans will be inserted here -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="financial-overview-tab" class="tab-content">
                <h2>Financial Overview</h2>
                <div id="financial-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                    <div class="stat-card">
                        <h3>Active Subscribers</h3>
                        <p id="stat-active-subscribers">0</p>
                    </div>
                    <div class="stat-card">
                        <h3>Monthly Recurring Revenue (MRR)</h3>
                        <p id="stat-mrr">$0.00</p>
                    </div>
                </div>

                <h3>Revenue Growth (Last 6 Months)</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody id="revenue-trends-body">
                            <!-- Revenue data -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="support-tickets-tab" class="tab-content">
                <h2>Support Tickets</h2>
                <div class="table-container">
                    <table id="support-tickets-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>User</th>
                                <th>Email</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="support-tickets-table-body">
                            <!-- Tickets will be inserted here -->
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
                <p><a href="mailto:tefinitely@gmail.com">Email Us</a></p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© 2026 tefinitely.com | All Rights Reserved</p>
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
                <button type="button" id="create-user-btn" class="action-btn">Create User</button>
            </form>
        </div>
    </div>

    <!-- Modal for sending email -->
    <div id="send-email-modal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h2>Send Email to: <span id="email-modal-user-name"></span></h2>
            <form id="send-email-form">
                <input type="hidden" id="email-user-id">
                <input type="hidden" id="email-bulk-ids">
                <label for="email-subject">Subject</label>
                <input type="text" id="email-subject" required>
                <label for="email-message">Message</label>
                <textarea id="email-message" rows="10" required></textarea>
                <button type="submit" class="action-btn">Send Email</button>
            </form>
        </div>
    </div>

    <!-- Modal for adding/editing payment plan -->
    <div id="plan-modal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h2 id="plan-modal-title">Add New Payment Plan</h2>
            <form id="plan-form">
                <input type="hidden" id="plan-id">
                <label for="plan-name">Plan Name</label>
                <input type="text" id="plan-name" required placeholder="e.g. Monthly Premium">

                <label for="plan-type">Type</label>
                <select id="plan-type" required>
                    <option value="subscription">Subscription (Recurring)</option>
                    <option value="one-time">One-Time Payment</option>
                </select>

                <label for="plan-price">Price</label>
                <input type="number" id="plan-price" step="0.01" required>

                <label for="plan-currency">Currency</label>
                <input type="text" id="plan-currency" value="CAD" required>

                <div id="duration-field" style="display: none;">
                    <label for="plan-duration">Duration (Days)</label>
                    <input type="number" id="plan-duration" placeholder="e.g. 30">
                </div>

                <div id="paypal-plan-field">
                    <label for="plan-paypal-id">PayPal Plan ID (for subscriptions)</label>
                    <input type="text" id="plan-paypal-id" placeholder="P-XXXXXXXXXXXXXXXXXXXX">
                </div>

                <label for="plan-description">Description</label>
                <textarea id="plan-description" rows="3"></textarea>

                <label for="plan-active">Active</label>
                <select id="plan-active">
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>

                <button type="submit" class="action-btn" id="save-plan-btn">Save Plan</button>
            </form>
        </div>
    </div>

    <!-- Modal for viewing payment history -->
    <div id="payment-history-modal" class="modal">
        <div class="modal-content" style="max-width: 800px;">
            <span class="close-btn">&times;</span>
            <h2>Payment History: <span id="modal-payment-user-name"></span></h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>PayPal Trans ID</th>
                            <th>Amount</th>
                            <th>Currency</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody id="payment-history-table-body">
                        <!-- Payments will be inserted here -->
                    </tbody>
                </table>
            </div>
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
