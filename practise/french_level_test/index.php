<?php
require_once '../../api/auth_check.php';
$user_details = checkAccess();
require_once '../../db/db_config.php';

$user_id = $_SESSION['user_id'];
$is_admin = $user_details['role'] === 'admin';

// Check eligibility
$can_take_test = false;
$wait_message = "";

if ($is_admin) {
    $can_take_test = true;
} else {
    $stmt = $conn->prepare("SELECT next_test_allowed_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $now = new DateTime();
    $next_allowed = $row['next_test_allowed_at'] ? new DateTime($row['next_test_allowed_at']) : null;

    if (!$next_allowed || $now >= $next_allowed) {
        $can_take_test = true;
    } else {
        $interval = $now->diff($next_allowed);
        $wait_message = "You can take the test again in " . $interval->format('%d days, %h hours, and %i minutes') . ".";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<base href="/">
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>French Level Test - Introduction</title>
<link rel="icon" href="img/favicon/favicon.ico" sizes="any">
<link rel="icon" href="img/favicon/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="img/favicon/apple-touch-icon.png">
<link rel="manifest" href="img/favicon/site.webmanifest">
<link rel="stylesheet" href="css/toast.css">
<link rel="stylesheet" href="css/main.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
<style>
    .intro-card {
        background: #fff;
        padding: 2.5rem;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        max-width: 700px;
        margin: 4rem auto;
        line-height: 1.6;
    }
    .intro-card h1 {
        color: #004d99;
        text-align: center;
        margin-bottom: 1.5rem;
    }
    .test-features {
        margin: 2rem 0;
        padding-left: 1.5rem;
    }
    .test-features li {
        margin-bottom: 0.8rem;
    }
    .alert-info {
        background: #e7f3ff;
        border-left: 5px solid #007bff;
        padding: 1rem;
        margin: 2rem 0;
        border-radius: 4px;
    }
    .btn-container {
        text-align: center;
        margin-top: 2rem;
    }
    .btn-start {
        display: inline-block;
        background: #28a745;
        color: white;
        padding: 1rem 2.5rem;
        border-radius: 5px;
        font-weight: bold;
        font-size: 1.2rem;
        text-decoration: none;
        transition: background 0.3s;
    }
    .btn-start:hover {
        background: #218838;
    }
    .btn-disabled {
        background: #ccc;
        cursor: not-allowed;
    }
    .wait-message {
        color: #dc3545;
        font-weight: bold;
        margin-top: 1rem;
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
                <a href="javascript:void(0)" class="dropbtn">Test</a>
                <div class="dropdown-content">
                    <a href="practise/french_level_test/index.php">French Level Test</a>
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

<div class="container">
    <div class="intro-card">
        <h1>French Vocabulary Level Test</h1>
        <p>Welcome to our adaptive French proficiency assessment. This test is designed to accurately estimate your current CEFR level (from A1 to C2) through a series of vocabulary challenges.</p>

        <ul class="test-features">
            <li><strong>Adaptive Difficulty:</strong> The test dynamically adjusts to your performance. If you answer correctly, the questions get harder; if you struggle, they get easier.</li>
            <li><strong>20 Questions:</strong> A focused session of 20 multiple-choice questions.</li>
            <li><strong>Anti-Cognate Focus:</strong> We specifically exclude words similar to English, ensuring we measure your actual French knowledge.</li>
            <li><strong>Progress Tracking:</strong> Your results will be saved to your profile so you can track your improvement over time.</li>
        </ul>

        <div class="alert-info">
            <p><i class="bi bi-info-circle-fill"></i> <strong>Important Note:</strong> To ensure accurate tracking and fair use of our AI resources, you can only take this test <strong>once every 7 days</strong>.</p>
        </div>

        <div class="btn-container">
            <?php if ($can_take_test): ?>
                <a href="practise/french_level_test/vocabulary.php" class="btn-start">Confirm & Start Test</a>
            <?php else: ?>
                <button class="btn-start btn-disabled" disabled>Start Test</button>
                <p class="wait-message"><?php echo $wait_message; ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="js/toast.js"></script>
<script src="js/auth.js"></script>
<script src="js/nav.js"></script>
<script src="js/cookie-banner.js"></script>
</body>
</html>
