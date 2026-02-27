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
<title>TEF Canada Training - Oral Expression Section A</title>
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
  .container {
    padding: 1rem;
  }
  body {
    font-family: Arial, sans-serif;
    margin: 0; /* Removed margin to prevent overflow */
    background: #f5f0ea;
  }
  h1 {
    margin-bottom: 1rem;
    color: #333;
  }
  label {
    font-weight: bold;
  }
  select, button {
    margin: 0.5rem 0 1rem 0;
    padding: 0.4rem 0.7rem;
    font-size: 1rem;
  }
  #phraseBox {
    background: white;
    padding: 1rem 1.5rem;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgb(0 0 0 / 0.1);
    max-width: 600px;
    width: 100%;
    box-sizing: border-box;
    margin-left: auto;
    margin-right: auto;
    margin-bottom: 2rem;
  }
  #phraseBox p {
    margin: 0.3rem 0;
  }
  #phraseFrench {
    font-size: 1.4rem;
    font-weight: bold;
    color: #004d99;
  }
  #phraseEnglish {
    font-size: 1.1rem;
    color: #666;
  }
  #controls {
    margin-bottom: 2rem;
  }
  #recordingSection {
    margin-top: 1.5rem;
    max-width: 600px;
    width: 100%;
    box-sizing: border-box;
    margin-left: auto;
    margin-right: auto;
    background: #fff;
    padding: 1rem 1.5rem;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgb(0 0 0 / 0.1);
  }
  #recordingSection button {
    padding: 0.5rem 1rem;
    margin-right: 1rem;
    font-size: 1rem;
  }
  #userAudio {
    margin-top: 1rem;
    width: 100%;
    outline:.
  }
  #recordingResult {
    margin-top: 0.8rem;
    font-weight: 600;
  }
  #navButtons button {
    padding: 0.4rem 1rem;
    margin-right: 0.7rem;
  }
  /* --- Flashcard Styles --- */
  .flashcard {
      background-color: transparent;
      width: 100%;
      max-width: 600px;
      height: 200px;
      perspective: 1000px;
      margin: auto;
      margin-bottom: 1rem;
  }
  .flashcard-inner {
      position: relative;
      width: 100%;
      height: 100%;
      text-align: center;
      transition: transform 0.6s;
      transform-style: preserve-3d;
      box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
  }
  .flashcard.is-flipped .flashcard-inner {
      transform: rotateY(180deg);
  }
  .flashcard-front, .flashcard-back {
      position: absolute;
      width: 100%;
      height: 100%;
      -webkit-backface-visibility: hidden;
      backface-visibility: hidden;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 8px;
      background-color: #fff;
      padding: 1rem;
      box-sizing: border-box;
  }
  .flashcard-back {
      transform: rotateY(180deg);
  }

  /* --- Slide Animation for Next/Prev --- */
  @keyframes slide-out-left {
      from { transform: translateX(0); opacity: 1; }
      to { transform: translateX(-100px); opacity: 0; }
  }
  @keyframes slide-out-right {
      from { transform: translateX(0); opacity: 1; }
      to { transform: translateX(100px); opacity: 0; }
  }
  .slide-out-left {
      animation: slide-out-left 0.3s forwards;
  }
  .slide-out-right {
      animation: slide-out-right 0.3s forwards;
  }
  /* --- Responsive Design --- */
  @media (max-width: 640px) {
    .logo {
        margin: 0.5rem 0 1rem 0; /* Reduced vertical margins */
    }
    .main-nav {
        margin: 0 0 1rem 0; /* Reduced vertical margins and remove side margins */
        padding: 0.5rem 0; /* Reduced padding, removed horizontal */
    }
    #topicSelect {
        width: 100%;
        box-sizing: border-box; /* Include padding and border in the element's total width */
    }
    body {
      margin: 1rem;
    }
    h1 {
      font-size: 1.5rem;
    }
    #phraseBox, #recordingSection {
      padding: 1rem;
      margin-bottom: 1rem;
    }
    .flashcard {
        height: 180px; /* Adjust height for smaller screens */
    }
    #phraseFrench {
      font-size: 1.2rem;
    }
    #phraseEnglish {
      font-size: 1rem;
    }
    #navButtons {
        text-align: center;
    }
    #navButtons button {
      padding: 0.6rem;
      margin: 0.2rem;
      font-size: 0.9rem;
    }
    #main-content {
        padding: 0;
    }
  }
</style>
</head>
<body>
<div class="container">
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
    <h1>Oral Expression - Section A</h1>

    <div id="controls">
      <label for="topicSelect">Select Topic:</label><br />
      <select id="topicSelect">
          <option value="">--Please choose a topic--</option>
          <!-- Options will be dynamically populated -->
      </select>
      <span id="topic-progress-display" style="margin-left: 1rem; font-weight: bold; font-size: 0.9rem;"></span>
      <br />
      <label for="speechRate">Speech Speed:</label><br />
      <input type="range" id="speechRate" min="0.5" max="1.2" value="1.0" step="0.1" />
      <span id="rateDisplay">1.0</span>x
      <br />
    </div>

    <div id="phraseBox" style="display:none;">
        <div class="flashcard">
            <div class="flashcard-inner">
                <div class="flashcard-front">
                    <p id="phraseEnglish"></p>
                </div>
                <div class="flashcard-back">
                    <p id="phraseFrench"></p>
                </div>
            </div>
        </div>
        <div id="navButtons">
            <button id="firstCardBtn">&lt;&lt;</button>
            <button id="prevPhraseBtn" disabled>Previous</button>
            <button id="flipCardBtn">Flip</button>
            <button id="playPhraseBtn">Play Phrase</button>
            <button id="nextPhraseBtn" disabled>Next</button>
        </div>
        <div id="pagination-display" style="text-align: center; margin-top: 1rem; font-weight: bold;">
            <span id="current-card"></span> / <span id="total-cards"></span>
        </div>
    </div>

    <div id="recordingSection" style="display:none;">
      <button id="startRecordBtn">Speak</button>
      <audio id="userAudio" controls style="display:none;"></audio>
      <p id="recordingResult"></p>
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
<script src="js/oral_expression_section_a.js"></script>
</div>
</body>
</html>
