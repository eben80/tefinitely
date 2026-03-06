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
<title>Test de Niveau - Expression Orale Française</title>
<link rel="icon" href="img/favicon/favicon.ico" sizes="any">
<link rel="icon" href="img/favicon/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="img/favicon/apple-touch-icon.png">
<link rel="manifest" href="img/favicon/site.webmanifest">
<link rel="stylesheet" href="css/toast.css">
<link rel="stylesheet" href="css/main.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
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

<div id="main-content" class="level-test-container">
    <div id="loading-screen" class="test-loading-screen">
        <div id="loading-spinner">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Chargement...</span>
            </div>
            <p style="margin-top: 1rem;">Chargement de vos questions personnalisées...</p>
        </div>
        <div id="loading-error" style="display: none;">
            <p style="color: #dc3545; font-weight: bold;">Erreur lors du chargement des questions.</p>
            <p>Cela peut arriver si la connexion est lente ou si le pool de questions est vide.</p>
            <button onclick="fetchQuestions()" class="btn-primary" style="margin-top: 1rem;">Réessayer</button>
            <a href="practise/french_level_test/index.php" class="btn-secondary" style="margin-top: 1rem; display: block;">Retour</a>
        </div>
    </div>

    <div id="test-container" style="display: none;">
        <div id="progress-bar" class="test-progress-bar"><div id="progress-inner" class="test-progress-inner"></div></div>
        <div id="questions-wrapper"></div>
        <div id="controls" class="test-controls">
            <span id="question-index">1 / 20</span>
            <button id="next-btn" class="btn-primary" disabled>Suivant</button>
        </div>
    </div>

    <div id="result-screen" class="level-result-container">
        <h2>Votre niveau estimé en expression orale :</h2>
        <div class="level-badge" id="level-result">--</div>
        <p id="level-description"></p>
        <p style="margin-top: 2rem;">
            <strong>Score :</strong> <span id="score-display">0</span> / 20
        </p>
        <button onclick="location.reload()" class="btn-primary" style="margin-top: 2rem;">Recommencer le test</button>
        <a href="oral_expression.php" class="btn-secondary" style="margin-top: 1rem; display: block;">Retourner à l'entraînement</a>
    </div>
</div>

<script src="js/toast.js"></script>
<script src="js/nav.js"></script>
<script>
let allQuestionsPool = [];
let adaptiveQuestions = [];
let currentQuestionIndex = 0;
let userAnswers = {};

const levelsOrder = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];
let currentDifficultyIndex = 1; // Start at A2

const loadingScreen = document.getElementById('loading-screen');
const testContainer = document.getElementById('test-container');
const questionsWrapper = document.getElementById('questions-wrapper');
const nextBtn = document.getElementById('next-btn');
const questionIndexDisplay = document.getElementById('question-index');
const progressInner = document.getElementById('progress-inner');
const resultScreen = document.getElementById('result-screen');
const levelResult = document.getElementById('level-result');
const levelDescription = document.getElementById('level-description');
const scoreDisplay = document.getElementById('score-display');

async function fetchQuestions() {
    document.getElementById('loading-spinner').style.display = 'block';
    document.getElementById('loading-error').style.display = 'none';

    try {
        const response = await fetch('api/level_test/get_questions.php?type=oral');
        if (!response.ok) throw new Error('Network response was not ok');
        const data = await response.json();
        if (data.status === 'success') {
            allQuestionsPool = data.questions;
            startAdaptiveTest();
        } else {
            throw new Error(data.message || 'API error');
        }
    } catch (error) {
        console.error('Error fetching questions:', error);
        document.getElementById('loading-spinner').style.display = 'none';
        document.getElementById('loading-error').style.display = 'block';
        showToast('Erreur lors du chargement des questions.', 'error');
    }
}

function startAdaptiveTest() {
    currentQuestionIndex = 0;
    adaptiveQuestions = [];
    userAnswers = {};
    currentDifficultyIndex = 1;
    pickNextQuestion();
    loadingScreen.style.display = 'none';
    testContainer.style.display = 'block';
}

function pickNextQuestion() {
    const levelLabel = levelsOrder[currentDifficultyIndex];
    const usedIds = adaptiveQuestions.map(q => q.id);
    const available = allQuestionsPool.filter(q => q.level === levelLabel && !usedIds.includes(q.id));

    let nextQ;
    if (available.length > 0) {
        nextQ = available[Math.floor(Math.random() * available.length)];
    } else {
        const unused = allQuestionsPool.filter(q => !usedIds.includes(q.id));
        if (unused.length === 0) { showResults(); return; }
        unused.sort((a, b) => {
            const distA = Math.abs(levelsOrder.indexOf(a.level) - currentDifficultyIndex);
            const distB = Math.abs(levelsOrder.indexOf(b.level) - currentDifficultyIndex);
            return distA - distB;
        });
        nextQ = unused[0];
    }

    adaptiveQuestions.push(nextQ);
    renderCurrentQuestion();
    updateUI();
}

function renderCurrentQuestion() {
    const q = adaptiveQuestions[currentQuestionIndex];
    questionsWrapper.innerHTML = '';

    const div = document.createElement('div');
    div.className = 'question-card active';
    div.id = `q-${currentQuestionIndex}`;

    const h3 = document.createElement('h3');
    h3.textContent = `Question ${currentQuestionIndex + 1}`;
    div.appendChild(h3);

    const p = document.createElement('p');
    p.style.fontSize = '1.2rem';
    p.style.marginTop = '1rem';
    p.textContent = q.question;
    div.appendChild(p);

    const optionsDiv = document.createElement('div');
    optionsDiv.className = 'test-options';

    Object.entries(q.options).forEach(([key, text]) => {
        const btn = document.createElement('button');
        btn.className = 'option-btn';
        if (userAnswers[currentQuestionIndex] === key) btn.classList.add('selected');

        const strong = document.createElement('strong');
        strong.textContent = `${key}: `;
        btn.appendChild(strong);
        btn.appendChild(document.createTextNode(text));

        btn.addEventListener('click', () => {
            document.querySelectorAll('.option-btn').forEach(b => b.classList.remove('selected'));
            btn.classList.add('selected');
            userAnswers[currentQuestionIndex] = key;
            nextBtn.disabled = false;
        });

        optionsDiv.appendChild(btn);
    });

    div.appendChild(optionsDiv);
    questionsWrapper.appendChild(div);
}

function updateUI() {
    questionIndexDisplay.textContent = `${currentQuestionIndex + 1} / 20`;
    progressInner.style.width = `${((currentQuestionIndex + 1) / 20) * 100}%`;
    nextBtn.textContent = currentQuestionIndex === 19 ? 'Finish' : 'Next';
    nextBtn.disabled = !userAnswers[currentQuestionIndex];
}

nextBtn.addEventListener('click', () => {
    const currentQ = adaptiveQuestions[currentQuestionIndex];
    const isCorrect = userAnswers[currentQuestionIndex] === currentQ.correct;

    if (isCorrect) {
        if (currentDifficultyIndex < levelsOrder.length - 1) currentDifficultyIndex++;
    } else {
        if (currentDifficultyIndex > 0) currentDifficultyIndex--;
    }

    if (currentQuestionIndex < 19) {
        currentQuestionIndex++;
        if (adaptiveQuestions[currentQuestionIndex]) {
            renderCurrentQuestion();
            updateUI();
        } else {
            pickNextQuestion();
        }
    } else {
        showResults();
    }
} );

function showResults() {
    testContainer.style.display = 'none';
    resultScreen.style.display = 'block';

    let score = 0;
    let levelSum = 0;
    let correctCountInLastHalf = 0;

    adaptiveQuestions.forEach((q, index) => {
        const isCorrect = userAnswers[index] === q.correct;
        if (isCorrect) {
            score++;
            if (index >= 10) {
                levelSum += levelsOrder.indexOf(q.level);
                correctCountInLastHalf++;
            }
        }
    });

    let finalDifficultyIndex = 0;
    if (correctCountInLastHalf > 0) {
        finalDifficultyIndex = Math.round(levelSum / correctCountInLastHalf);
    } else {
        const lastQLevel = levelsOrder.indexOf(adaptiveQuestions[adaptiveQuestions.length-1].level);
        finalDifficultyIndex = Math.max(0, lastQLevel - 1);
    }

    if (score < 5) finalDifficultyIndex = Math.min(finalDifficultyIndex, 1);
    if (score < 3) finalDifficultyIndex = 0;
    if (score > 17 && finalDifficultyIndex < 4) finalDifficultyIndex = 4;

    scoreDisplay.textContent = score;
    let estimatedLevel = levelsOrder[finalDifficultyIndex];
    levelResult.textContent = estimatedLevel;

    // Save result to backend
    fetch('api/level_test/save_result.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            score: score,
            estimated_level: estimatedLevel,
            test_type: 'oral'
        })
    }).catch(err => console.error('Failed to save test result:', err));

    const descriptions = {
        'A1': 'Vous pouvez comprendre des expressions très simples et répondre à des besoins concrets.',
        'A2': 'Vous pouvez communiquer dans des tâches simples et habituelles.',
        'B1': 'Vous pouvez vous exprimer de manière simple et cohérente sur des sujets familiers.',
        'B2': 'Vous pouvez communiquer avec un degré de spontanéité et d\'aisance.',
        'C1': 'Vous pouvez vous exprimer spontanément et très couramment sans trop apparemment chercher vos mots.',
        'C2': 'Vous pouvez vous exprimer spontanément, très couramment et de façon précise.'
    };

    levelDescription.textContent = descriptions[estimatedLevel];
}

document.addEventListener('DOMContentLoaded', fetchQuestions);

// User status check
fetch('api/check_session.php')
    .then(r => r.json())
    .then(data => {
        if (data.loggedIn && data.user.subscription_status === 'active') {
            document.getElementById('user-status').style.display = 'flex';
            document.getElementById('first-name-display').textContent = `Welcome, ${data.user.first_name}`;
            if (data.user.role === 'admin') document.getElementById('admin-link').style.display = 'inline';
        } else {
            window.location.href = 'login.html';
        }
    });

document.getElementById('logoutBtn').addEventListener('click', () => {
    fetch('api/logout.php').then(() => window.location.href = 'login.html');
});
</script>
</body>
</html>
