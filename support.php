<?php
require_once 'api/auth_check.php';
checkAccess(false);
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
    <title>Contact Support - TEFinitely</title>
    <meta name="description" content="Get help with your TEF Canada or CELPIP preparation. Contact TEFinitely support for any questions or issues.">
    <link rel="canonical" href="https://tefinitely.com/support.php">
    <link rel="icon" href="img/favicon/favicon.ico" sizes="any">
    <link rel="icon" href="img/favicon/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="img/favicon/apple-touch-icon.png">
    <link rel="manifest" href="img/favicon/site.webmanifest">
    <link rel="stylesheet" href="css/toast.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <style>
        .support-container {
            max-width: 600px;
            margin: 2rem auto;
            background: #fff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .support-container h1 {
            color: #004d99;
            margin-bottom: 1rem;
            font-size: 1.8rem;
            text-align: center;
        }
        .support-container p {
            color: #666;
            margin-bottom: 1.5rem;
            line-height: 1.5;
            text-align: center;
        }
        label { display: block; margin-bottom: 0.5rem; font-weight: bold; }
        input, textarea {
            width: 100%;
            padding: 0.75rem;
            margin-bottom: 1.25rem;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-family: inherit;
        }
        button {
            width: 100%;
            padding: 0.75rem;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: bold;
            transition: background-color 0.2s;
        }
        button:hover {
            background-color: #0056b3;
        }
        @media (max-width: 640px) {
            .support-container {
                margin: 1rem;
                padding: 1.5rem;
            }
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
                <button id="logoutBtn">Logout</button>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="support-container">
            <h1>Contact Support</h1>
            <p>Have a question or issue? Send us a message and we'll get back to you as soon as possible.</p>
            <form id="contact-support-form">
                <label for="support-subject">Subject</label>
                <input type="text" id="support-subject" required placeholder="What do you need help with?">
                <label for="support-message">Message</label>
                <textarea id="support-message" rows="6" required placeholder="Please provide as much detail as possible..."></textarea>
                <button type="submit">Submit Request</button>
            </form>
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
                    <li><a href="profile.php">Dashboard</a></li>
                    <li><a href="oral_expression.php">Oral Expression</a></li>
                    <li><a href="training.php">Phased Training</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h4>Legal</h4>
                <ul>
                    <li><a href="terms.html">Terms of Service</a></li>
                    <li><a href="privacy.html">Privacy Policy</a></li>
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

    <script src="js/toast.js"></script>
    <script src="js/browser-support.js"></script>
    <script src="js/auth.js"></script>
    <script src="js/nav.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('contact-support-form');
            form.addEventListener('submit', async (e) => {
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
                        form.reset();
                    } else {
                        showToast(data.message || 'Failed to send message.', 'error');
                    }
                } catch (error) {
                    showToast('An error occurred. Please try again later.', 'error');
                }
            });
        });
    </script>
    <script src="js/cookie-banner.js"></script>
</body>
</html>
