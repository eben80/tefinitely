<?php
require_once 'api/auth_check.php';
checkAccess();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<base href="/">
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>TEF Canada Training - Dashboard</title>
<link rel="icon" href="img/favicon/favicon.ico" sizes="any">
<link rel="icon" href="img/favicon/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="img/favicon/apple-touch-icon.png">
<link rel="manifest" href="img/favicon/site.webmanifest">
<link rel="stylesheet" href="css/toast.css">
<link rel="stylesheet" href="css/main.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
<style>
  .logo {
    width: 100%;
    max-width: 500px;
    margin: 1rem 0 2rem 0;
  }
  body {
    font-family: Arial, sans-serif;
    margin: 2rem;
    background: #f5f0ea;
  }
  h1, h2 {
    color: #333;
  }
  .section-card {
    background: #fff;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
    max-width: 800px;
    margin-left: auto;
    margin-right: auto;
  }
  .section-card h2 {
    margin-top: 0;
    color: #004d99;
  }
  .section-card p {
    line-height: 1.6;
  }
  .section-card a {
    display: inline-block;
    margin-top: 1rem;
    padding: 0.8rem 1.5rem;
    background-color: #004d99;
    color: #fff;
    text-decoration: none;
    border-radius: 5px;
    font-weight: bold;
    transition: background-color 0.3s;
  }
  .section-card a:hover {
    background-color: #003366;
  }
</style>
</head>
<body>
<header>
    <a href="index.html"><img src="img/top_logo_light.png" alt="TEFinitely Logo" class="logo"></a>
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

<div id="main-content">
    <h1 style="text-align: center;">Welcome to Your Dashboard</h1>

    <div class="section-card">
        <h2>Oral Expression Practice</h2>
        <p>Master essential phrases for the TEF Canada exam. Practice pronunciation and build a strong foundation for success in Section A, or engage in realistic dialogues in Section B.</p>
        <a href="oral_expression.php">Go to Oral Expression</a>
    </div>

    <div class="section-card">
        <h2>Phased Training</h2>
        <p>Follow our unique, phased training system to go from hesitant to fluent. Each phase builds on the last, targeting specific skills you'll need for the exam.</p>
        <a href="training.php">Go to Phased Training</a>
    </div>

    <div class="section-card">
        <h2>Your Profile</h2>
        <p>Track your progress, view your performance statistics, and manage your account details. You can also restart the guided tour from your profile page.</p>
        <a href="profile.php">Go to Profile</a>
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
</div>
<script src="js/toast.js"></script>
<script src="js/auth.js"></script>
<script src="js/nav.js"></script>
</body>
</html>
