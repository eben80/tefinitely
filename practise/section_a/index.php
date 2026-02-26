<?php
require_once '../../api/auth_check.php';
checkAccess();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<base href="/">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Oral Expression - Section A</title>
<link rel="icon" href="img/favicon/favicon.ico" sizes="any">
<link rel="icon" href="img/favicon/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="img/favicon/apple-touch-icon.png">
<link rel="manifest" href="img/favicon/site.webmanifest">
<link rel="stylesheet" href="css/toast.css">
<link rel="stylesheet" href="css/main.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
<style>
    /* Reset some main.css styles for this app-like page */
    body {
        background: #f5f0ea;
        display: flex;
        flex-direction: column;
        height: 100vh;
        height: 100dvh; /* Dynamic viewport height for mobile */
        margin: 0;
        padding: 0;
        overflow: hidden; /* Prevent body scroll */
    }

    header {
        flex: 0 0 auto;
    }

    .main-nav {
        flex: 0 0 auto;
        margin: 0 0 10px 0 !important;
        position: relative !important;
        width: 100% !important;
        background: #f5f0ea !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05) !important;
        visibility: visible !important;
        display: flex !important; /* Force display even if auth.js hasn't run, though auth.js will set it too */
    }

    .main-nav .nav-content {
        display: flex !important;
        width: 100%;
        padding: 0 2rem;
    }

    @media (max-width: 768px) {
        .main-nav .nav-content {
            display: none !important;
            flex-direction: column;
            position: absolute;
            top: 100%;
            left: 0;
            background: #f5f0ea;
            z-index: 1000;
            width: 100%;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .main-nav .nav-content.is-open {
            display: flex !important;
        }

        /* Adjustments for smaller screens */
        #app-container {
            padding: 0 5px !important;
        }

        #main-content {
            padding: 10px !important;
            border-radius: 0 !important;
        }

        #instruction-display {
            padding: 8px 10px !important;
            font-size: 0.85rem !important;
            margin-bottom: 5px !important;
        }

        #instruction-display h1 {
            font-size: 1.1rem !important;
            margin: 0 0 5px 0 !important;
        }

        #instruction-display p {
            margin: 0 !important;
        }

        #advertisement-poster {
            padding: 15px !important;
            font-size: 1rem !important;
            min-height: 100px !important;
            margin-bottom: 5px !important;
        }

        #chat {
            padding: 10px !important;
        }

        .message {
            max-width: 90% !important;
            padding: 10px 12px !important;
            font-size: 0.95rem !important;
        }

        #input-area {
            padding: 8px !important;
            gap: 5px !important;
        }

        #user-input {
            padding: 8px 12px !important;
            min-width: 0 !important;
        }

        #send-btn, #next-btn, #speak-btn {
            padding: 8px 10px !important;
            font-size: 0.8rem !important;
            flex-shrink: 0 !important;
            white-space: nowrap !important;
        }

        #setup-container {
            padding: 20px !important;
            margin: auto 10px !important;
        }

        header .logo {
            max-height: 30px !important;
            margin: 5px auto !important;
        }

        .main-nav {
            margin-bottom: 5px !important;
        }

        #advertisement-poster {
            min-height: 80px !important;
            max-height: 120px !important;
            overflow-y: auto !important;
            padding: 10px !important;
        }
    }

    #app-container {
        flex: 1;
        display: flex;
        flex-direction: column;
        max-width: 1000px;
        margin: 0 auto;
        width: 100%;
        padding: 0 15px;
        overflow: hidden; /* Container doesn't scroll */
    }

    #main-content {
        background: white;
        border-radius: 8px 8px 0 0;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        padding: 15px;
        margin-bottom: 0;
        display: none; /* Shown after session starts */
        flex-direction: column;
        flex: 1;
        overflow: hidden;
    }

    #instruction-display {
        background: #e7f3ff;
        padding: 10px 15px;
        border-left: 6px solid #007bff;
        margin-bottom: 10px;
        border-radius: 4px;
        font-weight: 500;
        color: #004085;
        flex: 0 0 auto;
        font-size: 0.95rem;
    }

    #advertisement-poster {
        background: #fff;
        border: 2px solid #333;
        padding: 25px;
        margin-bottom: 10px;
        box-shadow: 4px 4px 0px rgba(0,0,0,0.2);
        font-family: 'Georgia', serif;
        white-space: pre-wrap;
        line-height: 1.4;
        position: relative;
        flex: 0 0 auto;
        font-size: 1.1rem;
        min-height: 150px; /* Ensure it has enough space */
    }

    #advertisement-poster::before {
        content: "ANNONCE";
        position: absolute;
        top: 0;
        right: 0;
        background: #333;
        color: white;
        padding: 2px 10px;
        font-size: 0.7rem;
        font-family: sans-serif;
        font-weight: bold;
    }

    #chat-container {
        display: flex;
        flex-direction: column;
        flex: 1; /* Take remaining space */
        border: 1px solid #dee2e6;
        border-radius: 8px;
        overflow: hidden;
        background: #fff;
        min-height: 150px;
        position: relative;
    }

    #question-counter {
        background: rgba(255, 255, 255, 0.9);
        border-bottom: 1px solid #eee;
        padding: 5px 15px;
        font-size: 0.85rem;
        font-weight: bold;
        color: #666;
        display: flex;
        justify-content: space-between;
        align-items: center;
        z-index: 10;
    }

    #question-counter .count-badge {
        background: #007bff;
        color: white;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 0.8rem;
    }

    #question-counter .count-badge.target-reached {
        background: #28a745;
    }

    #timer-container {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    #timer-display {
        font-family: monospace;
        font-size: 1rem;
        color: #dc3545;
    }

    #chat {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
        background: #fdfdfd;
    }

    .message {
        margin-bottom: 15px;
        max-width: 85%;
        padding: 12px 16px;
        border-radius: 18px;
        line-height: 1.5;
        position: relative;
    }

    .user {
        align-self: flex-end;
        background: #007bff;
        color: white;
        border-bottom-right-radius: 4px;
        margin-left: auto;
    }

    .assistant {
        align-self: flex-start;
        background: #f5f0ea;
        color: #333;
        border-bottom-left-radius: 4px;
    }

    .suggestion {
        align-self: center;
        background: #fff3cd;
        color: #856404;
        font-style: italic;
        font-size: 0.9rem;
        border: 1px solid #ffeeba;
        border-radius: 8px;
        padding: 8px 12px;
        margin: 10px 0;
    }

    .tts-btn {
        background: none;
        border: none;
        cursor: pointer;
        font-size: 1.2rem;
        margin-left: 8px;
        vertical-align: middle;
        padding: 0;
        opacity: 0.6;
        transition: opacity 0.2s;
    }

    .tts-btn:hover {
        opacity: 1;
    }

    #input-area {
        padding: 15px;
        background: white;
        border-top: 1px solid #dee2e6;
        display: flex;
        gap: 10px;
    }

    #user-input {
        flex: 1;
        padding: 10px 15px;
        border: 1px solid #ced4da;
        border-radius: 25px;
        outline: none;
    }

    #user-input:focus {
        border-color: #007bff;
    }

    #send-btn, #speak-btn, #next-btn {
        border: none;
        border-radius: 25px;
        padding: 8px 20px;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.2s;
    }

    #send-btn {
        background: #007bff;
        color: white;
    }

    #send-btn:hover {
        background: #0056b3;
    }

    #next-btn {
        background: #28a745;
        color: white;
    }

    #next-btn:hover {
        background: #218838;
    }

    #speak-btn {
        background: #6c757d;
        color: white;
    }

    #speak-btn:hover {
        background: #5a6268;
    }

    #hint-btn {
        background: #6c757d;
        color: white;
        border: none;
        border-radius: 25px;
        padding: 8px 15px;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.2s;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    #hint-btn:hover {
        background: #e0a800;
    }

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 2000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: transparent;
        pointer-events: none;
    }

    .modal-content {
        pointer-events: auto;
        background-color: #fefefe;
        position: absolute;
        top: 50px;
        left: 50%;
        transform: translateX(-50%);
        padding: 0;
        border: 1px solid #888;
        width: 90%;
        max-width: 500px;
        border-radius: 12px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.3);
        max-height: 60vh;
        display: flex;
        flex-direction: column;
    }

    .modal-header {
        padding: 15px 20px;
        background: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        cursor: move;
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-shrink: 0;
    }

    .modal-header h2 {
        margin: 0;
        font-size: 1.2rem;
    }

    .modal-body {
        padding: 20px;
        overflow-y: auto;
        flex: 1;
    }

    .close {
        color: #aaa;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        line-height: 1;
    }

    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
    }

    #hints-list {
        list-style-type: none;
        padding: 0;
    }

    #hints-list li {
        padding: 10px;
        border-bottom: 1px solid #eee;
        cursor: pointer;
        transition: background 0.2s;
    }

    #hints-list li:hover {
        background-color: #f8f9fa;
    }

    #setup-container {
        text-align: center;
        padding: 40px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        margin: auto; /* Center vertically in app-container */
        width: 100%;
        max-width: 500px;
    }

    .form-group {
        margin-bottom: 20px;
        text-align: left;
        max-width: 400px;
        margin-left: auto;
        margin-right: auto;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
    }

    .form-control {
        width: 100%;
        padding: 10px;
        border-radius: 5px;
        border: 1px solid #ced4da;
    }

    #start-btn {
        padding: 12px 40px;
        font-size: 1.1rem;
        background: #28a745;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-weight: bold;
    }

    #start-btn:hover {
        background: #218838;
    }
</style>
</head>
<body>

<header>
    <a href="index.html"><img src="img/top_logo_light.png" alt="TEFinitely Logo" class="logo" style="max-height: 60px; margin: 10px auto; display: block;"></a>
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

<div id="app-container">
    <div id="setup-container">
        <h2 id="page-title">Conversation Practice</h2>
        <div class="form-group">
            <label id="lang-label">Langue:</label>
            <select id="language" class="form-control">
                <option value="fr" selected>Français</option>
                <option value="en">English</option>
            </select>
        </div>
        <div class="form-group">
            <label id="level-label">Niveau:</label>
            <select id="level" class="form-control"></select>
        </div>
        <div class="form-group">
            <label id="speed-label">Vitesse de parole:</label>
            <div style="display: flex; align-items: center; gap: 10px;">
                <input type="range" id="speech-speed" min="0.5" max="1.2" value="1" step="0.05" style="flex: 1;">
                <span id="speed-value">1.0</span>
            </div>
        </div>
        <button id="start-btn">Démarrer</button>
    </div>

    <div id="main-content">
        <div id="instruction-display"></div>
        <div id="advertisement-poster"></div>

        <div id="chat-container">
            <div id="question-counter">
                <div>
                    <span id="counter-label">Questions posées :</span>
                    <span class="count-badge" id="question-count">0</span>
                </div>
                <div id="timer-container">
                    <span id="timer-label">Temps restant :</span>
                    <span id="timer-display">05:00</span>
                </div>
            </div>
            <div id="chat" style="display: flex; flex-direction: column;"></div>

            <div id="input-area">
                <input type="text" id="user-input" placeholder="Tapez votre phrase ici..." />
                <button id="hint-btn" title="Besoin d'aide ?">💡</button>
                <button id="send-btn">Envoyer</button>
                <button id="next-btn" title="Annonce suivante">Suivant</button>
                <button id="speak-btn">🎤 Parler</button>
            </div>
        </div>
    </div>
</div>

<!-- Hints Modal -->
<div id="hints-modal" class="modal">
    <div class="modal-content" id="modal-content">
        <div class="modal-header" id="modal-header">
            <h2 id="modal-title">Idées de questions</h2>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body" id="modal-body">
            <ul id="hints-list"></ul>
            <div id="hints-loading" style="display: none; text-align: center; padding: 20px;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
                <p>Génération des questions...</p>
            </div>
        </div>
    </div>
</div>


<script src="js/toast.js"></script>
<script src="js/auth.js"></script>
<script src="js/nav.js"></script>
<script>
const chatContainer = document.getElementById('chat');
const instructionDisplay = document.getElementById('instruction-display');
const advertisementPoster = document.getElementById('advertisement-poster');
const inputField = document.getElementById('user-input');
const sendButton = document.getElementById('send-btn');
const nextButton = document.getElementById('next-btn');
const startButton = document.getElementById('start-btn');
const levelSelector = document.getElementById('level');
const speakButton = document.getElementById('speak-btn');
const hintButton = document.getElementById('hint-btn');
const languageSelector = document.getElementById('language');
const speedSlider = document.getElementById('speech-speed');
const speedValue = document.getElementById('speed-value');
const mainContent = document.getElementById('main-content');
const setupContainer = document.getElementById('setup-container');

function setActionButtonsDisabled(disabled) {
    sendButton.disabled = disabled;
    speakButton.disabled = disabled;
    nextButton.disabled = disabled;
    hintButton.disabled = disabled;
    if (startButton) startButton.disabled = disabled;
}

// Counter and Timer elements
let questionCount = 0;
const questionCountDisplay = document.getElementById('question-count');
const counterLabel = document.getElementById('counter-label');

let timerInterval = null;
let timeLeft = 300; // 5 minutes
const timerDisplay = document.getElementById('timer-display');
const timerLabel = document.getElementById('timer-label');

let currentLevel = 'A2';
let currentLanguage = 'fr';

// Modal elements
const hintsModal = document.getElementById('hints-modal');
const modalContent = document.getElementById('modal-content');
const hintsList = document.getElementById('hints-list');
const hintsLoading = document.getElementById('hints-loading');
const closeModal = document.querySelector('.close');
const modalTitle = document.getElementById('modal-title');

// iOS detection
const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
let ttsUnlocked = false;

// Interface elements
const pageTitle = document.getElementById('page-title');
const langLabel = document.getElementById('lang-label');
const levelLabel = document.getElementById('level-label');
const speedLabel = document.getElementById('speed-label');

// -------------------- Translations --------------------
const translations = {
    fr: {
        pageTitle: 'Expression Orale - Section A',
        langLabel: 'Langue:',
        levelLabel: 'Niveau:',
        startBtn: 'Démarrer',
        speedLabel: 'Vitesse de parole:',
        userPlaceholder: 'Tapez votre phrase ici...',
        sendBtn: 'Envoyer',
        nextBtn: 'Suivant',
        nextBtnTitle: 'Annonce suivante',
        speakBtn: '🎤 Parler',
        hintBtnTitle: "Besoin d'aide ?",
        modalTitle: 'Idées de questions',
        loadingHints: 'Génération des questions...',
        counterLabel: 'Questions posées :',
        timerLabel: 'Temps restant :',
        levels: {
            A1: 'A1 - Débutant',
            A2: 'A2 - Élémentaire',
            B1: 'B1 - Intermédiaire',
            B2: 'B2 - Intermédiaire supérieur',
            C1: 'C1 - Avancé',
            C2: 'C2 - Maîtrise'
        }
    },
    en: {
        pageTitle: 'Oral Expression - Section A',
        langLabel: 'Language:',
        levelLabel: 'Level:',
        startBtn: 'Start',
        speedLabel: 'Speech speed:',
        userPlaceholder: 'Type your sentence here...',
        sendBtn: 'Send',
        nextBtn: 'Next',
        nextBtnTitle: 'Next Scenario',
        speakBtn: '🎤 Speak',
        hintBtnTitle: "Need help?",
        modalTitle: 'Question Ideas',
        loadingHints: 'Generating questions...',
        counterLabel: 'Questions asked:',
        timerLabel: 'Time remaining:',
        levels: {
            A1: 'A1 - Beginner',
            A2: 'A2 - Elementary',
            B1: 'B1 - Intermediate',
            B2: 'B2 - Upper Intermediate',
            C1: 'C1 - Advanced',
            C2: 'C2 - Proficient'
        }
    }
};

// -------------------- UI update --------------------
function updateInterface(lang) {
    const t = translations[lang];
    pageTitle.textContent = t.pageTitle;
    langLabel.textContent = t.langLabel;
    levelLabel.textContent = t.levelLabel;
    startButton.textContent = t.startBtn;
    speedLabel.textContent = t.speedLabel;
    inputField.placeholder = t.userPlaceholder;
    sendButton.textContent = t.sendBtn;
    nextButton.textContent = t.nextBtn;
    nextButton.title = t.nextBtnTitle;
    speakButton.textContent = t.speakBtn;
    hintButton.title = t.hintBtnTitle;
    modalTitle.textContent = t.modalTitle;
    hintsLoading.querySelector('p').textContent = t.loadingHints;
    counterLabel.textContent = t.counterLabel;
    timerLabel.textContent = t.timerLabel;

    levelSelector.innerHTML = '';
    for (const [key, label] of Object.entries(t.levels)) {
        const opt = document.createElement('option');
        opt.value = key;
        opt.textContent = label;
        if (key === 'A2') opt.selected = true;
        levelSelector.appendChild(opt);
    }
}

languageSelector.addEventListener('change', () => {
    updateInterface(languageSelector.value);
});

updateInterface(languageSelector.value);

// -------------------- Speech queue --------------------
let speaking = false;
const speechQueue = [];

let speechRate = parseFloat(localStorage.getItem('speechRate')) || parseFloat(speedSlider.value);
speedSlider.value = speechRate;
speedValue.textContent = speechRate.toFixed(2);

speedSlider.addEventListener('input', () => {
    speechRate = parseFloat(speedSlider.value);
    speedValue.textContent = speechRate.toFixed(2);
    localStorage.setItem('speechRate', speechRate);
});

// -------------------- TTS --------------------
let voices = [];

function loadVoices() {
    voices = speechSynthesis.getVoices();
}

speechSynthesis.onvoiceschanged = loadVoices;
setTimeout(loadVoices, 500);

function unlockTTS() {
    if (!isIOS || ttsUnlocked || !('speechSynthesis' in window)) return;
    const u = new SpeechSynthesisUtterance(' ');
    u.volume = 0;
    speechSynthesis.speak(u);
    ttsUnlocked = true;
}

function speak(text) {
    if (!('speechSynthesis' in window) || !text) return;

    if (isIOS) {
        unlockTTS();
        speechSynthesis.cancel();
    }

    speechQueue.push(text);
    if (speaking) return;

    function next() {
        if (speechQueue.length === 0) { speaking = false; return; }

        speaking = true;
        const utter = new SpeechSynthesisUtterance(speechQueue.shift());

        if (languageSelector.value === 'fr') {
            utter.lang = 'fr-FR';
            utter.voice = voices.find(v => v.lang.startsWith('fr')) || null;
        } else {
            utter.lang = 'en-US';
            utter.voice =
                voices.find(v => v.name === 'Google US English') ||
                voices.find(v => v.lang.startsWith('en')) ||
                null;
        }

        utter.rate = speechRate;
        utter.onend = next;
        speechSynthesis.speak(utter);
    }

    next();
}

// -------------------- Append message --------------------
function appendMessage(role, text) {
    const div = document.createElement('div');
    div.className = `message ${role}`;

    const textSpan = document.createElement('span');
    textSpan.innerHTML = text;
    div.appendChild(textSpan);

    if (role === 'assistant') {
        const ttsBtn = document.createElement('button');
        ttsBtn.className = 'tts-btn';
        ttsBtn.innerHTML = '🔊';
        ttsBtn.title = 'Écouter';
        ttsBtn.addEventListener('click', () => {
            unlockTTS();
            speak(text);
        });
        div.appendChild(ttsBtn);
    }

    chatContainer.appendChild(div);
    chatContainer.scrollTop = chatContainer.scrollHeight;
}

// -------------------- Start session --------------------
async function startSession(level, language) {
    setActionButtonsDisabled(true);

    currentLevel = level;
    currentLanguage = language;
    setupRecognition(language);
    unlockTTS();

    // Reset Counter
    questionCount = 0;
    questionCountDisplay.textContent = questionCount;
    questionCountDisplay.classList.remove('target-reached');

    // Reset Timer
    if (timerInterval) clearInterval(timerInterval);
    timerInterval = null;
    timeLeft = 300;
    updateTimerDisplay();

    try {
        console.log('Starting session...');
        const res = await fetch('practise/section_a/api/start_session.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'level=' + encodeURIComponent(level) + '&language=' + encodeURIComponent(language)
        });
        const data = await res.json();
        console.log('Session data received:', data);

        chatContainer.innerHTML = '';
        instructionDisplay.innerHTML = '';
        advertisementPoster.innerHTML = '';
        instructionDisplay.style.display = 'none';
        advertisementPoster.style.display = 'none';

        if (data.instruction) {
            instructionDisplay.innerHTML = data.instruction;
            instructionDisplay.style.display = 'block';
        }
        if (data.advertisement) {
            advertisementPoster.innerHTML = data.advertisement;
            advertisementPoster.style.display = 'block';
        }
        if (data.assistant) {
            appendMessage('assistant', data.assistant);
            speak(data.assistant);
        }

        setupContainer.style.display = 'none';
        mainContent.style.display = 'flex';
        console.log('Session started successfully');
    } catch (error) {
        console.error('Error starting session:', error);
    } finally {
        setActionButtonsDisabled(false);
    }
}

// -------------------- Timer --------------------
function updateTimerDisplay() {
    const minutes = Math.floor(timeLeft / 60);
    const seconds = timeLeft % 60;
    timerDisplay.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
}

function startTimer() {
    if (timerInterval) return;
    updateTimerDisplay();
    timerInterval = setInterval(() => {
        timeLeft--;
        if (timeLeft <= 0) {
            timeLeft = 0;
            clearInterval(timerInterval);
            timerInterval = null;
        }
        updateTimerDisplay();
    }, 1000);
}

// -------------------- Send user message --------------------
async function sendMessage() {
    const text = inputField.value.trim();
    if (!text) return;
    appendMessage('user', text);
    inputField.value = '';

    questionCount++;
    questionCountDisplay.textContent = questionCount;
    if (questionCount >= 10) {
        questionCountDisplay.classList.add('target-reached');
    }

    if (questionCount === 1) {
        startTimer();
    }

    setActionButtonsDisabled(true);
    unlockTTS();

    try {
        const res = await fetch('practise/section_a/api/continue_session.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'text=' + encodeURIComponent(text) + '&language=' + encodeURIComponent(languageSelector.value)
        });

        const data = await res.json();

        if (data.suggestion) appendMessage('suggestion', `<em>${data.suggestion}</em>`);
        if (data.assistant) {
            appendMessage('assistant', data.assistant);
            speak(data.assistant);
        }
    } catch (error) {
        console.error('Error in sendMessage:', error);
    } finally {
        setActionButtonsDisabled(false);
    }
}

// -------------------- Hints --------------------
async function showHints() {
    setActionButtonsDisabled(true);

    hintsModal.style.display = "block";
    hintsList.innerHTML = "";
    hintsLoading.style.display = "block";

    try {
        const res = await fetch('practise/section_a/api/get_hints.php');
        const data = await res.json();
        hintsLoading.style.display = "none";

        if (data.hints && Array.isArray(data.hints)) {
            data.hints.forEach(hint => {
                const li = document.createElement('li');
                li.textContent = hint;
                li.addEventListener('click', () => {
                    inputField.value = hint;
                    // hintsModal.style.display = "none"; // Keep open as per user request
                    inputField.focus();
                });
                hintsList.appendChild(li);
            });
        } else {
            const li = document.createElement('li');
            li.textContent = "Erreur lors de la génération des indices.";
            hintsList.appendChild(li);
        }
    } catch (error) {
        hintsLoading.style.display = "none";
        console.error('Error fetching hints:', error);
    } finally {
        setActionButtonsDisabled(false);
    }
}

closeModal.onclick = () => hintsModal.style.display = "none";
window.onclick = (event) => {
    if (event.target == hintsModal) hintsModal.style.display = "none";
};

// -------------------- Draggable --------------------
function makeDraggable(elmnt, header) {
    let pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;

    header.onmousedown = dragMouseDown;
    header.addEventListener('touchstart', dragTouchStart, {passive: false});

    function dragMouseDown(e) {
        e = e || window.event;
        if (e.target.className === 'close') return;

        // Handle transform before starting drag
        prepareElement();

        e.preventDefault();
        pos3 = e.clientX;
        pos4 = e.clientY;
        document.onmouseup = closeDragElement;
        document.onmousemove = elementDrag;
        elmnt.style.zIndex = "2100";
    }

    function dragTouchStart(e) {
        if (e.target.className === 'close') return;

        prepareElement();

        let touch = e.touches[0];
        pos3 = touch.clientX;
        pos4 = touch.clientY;
        document.addEventListener('touchend', closeDragTouch);
        document.addEventListener('touchmove', elementTouchMove, {passive: false});
        elmnt.style.zIndex = "2100";
    }

    function prepareElement() {
        if (elmnt.style.transform !== "none" && elmnt.style.transform !== "") {
            const rect = elmnt.getBoundingClientRect();
            elmnt.style.transform = "none";
            elmnt.style.left = rect.left + "px";
            elmnt.style.top = rect.top + "px";
            elmnt.style.margin = "0"; // Ensure no margins interfere
        }
    }

    function elementDrag(e) {
        e = e || window.event;
        e.preventDefault();
        pos1 = pos3 - e.clientX;
        pos2 = pos4 - e.clientY;
        pos3 = e.clientX;
        pos4 = e.clientY;
        moveElement();
    }

    function elementTouchMove(e) {
        e.preventDefault();
        let touch = e.touches[0];
        pos1 = pos3 - touch.clientX;
        pos2 = pos4 - touch.clientY;
        pos3 = touch.clientX;
        pos4 = touch.clientY;
        moveElement();
    }

    function moveElement() {
        elmnt.style.transform = "none";
        elmnt.style.top = (elmnt.offsetTop - pos2) + "px";
        elmnt.style.left = (elmnt.offsetLeft - pos1) + "px";
    }

    function closeDragElement() {
        document.onmouseup = null;
        document.onmousemove = null;
    }

    function closeDragTouch() {
        document.removeEventListener('touchend', closeDragTouch);
        document.removeEventListener('touchmove', elementTouchMove);
    }
}

makeDraggable(modalContent, document.getElementById('modal-header'));

// -------------------- Events --------------------
sendButton.addEventListener('click', () => { unlockTTS(); sendMessage(); });
nextButton.addEventListener('click', () => startSession(currentLevel, currentLanguage));
inputField.addEventListener('keypress', e => { if(e.key==='Enter') sendMessage(); });
startButton.addEventListener('click', () => startSession(levelSelector.value, languageSelector.value));
hintButton.addEventListener('click', showHints);

// -------------------- Speech recognition --------------------
let recognition;
function setupRecognition(language) {
    if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        recognition = new SpeechRecognition();
        recognition.lang = language === 'fr' ? 'fr-FR' : 'en-US';
        recognition.interimResults = false;
        recognition.maxAlternatives = 1;

        recognition.onresult = function(event) {
            inputField.value = event.results[0][0].transcript;
            sendMessage();
        };
    }
}

speakButton.addEventListener('click', () => {
    unlockTTS();
    if (recognition) recognition.start();
});
</script>

</body>
</html>
