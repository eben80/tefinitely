document.addEventListener('DOMContentLoaded', () => {
    const testView = document.getElementById('test-view');
    let currentQuestion = null;
    let currentSEM = 1.0;

    async function initTest() {
        try {
            const response = await fetch('api/practise/cat_test/start_session.php', { method: 'POST' });
            const data = await response.json();
            if (data.status === 'success') {
                loadNextQuestion();
            } else {
                showError(data.message);
            }
        } catch (e) {
            showError('Failed to initialize test.');
        }
    }

    async function loadNextQuestion() {
        testView.innerHTML = '<div class="spinner"></div><p style="text-align: center;">Selecting optimal question...</p>';
        try {
            const response = await fetch('api/practise/cat_test/get_next_question.php');
            const data = await response.json();

            if (data.finished) {
                showResults(data);
                return;
            }

            renderQuestion(data.question, data.total_answered);
        } catch (e) {
            showError('Failed to load next question.');
        }
    }

    function renderQuestion(q, count) {
        currentQuestion = q;
        testView.innerHTML = `
            <div class="question-header">
                <span style="font-weight: bold; color: #007bff;">${q.competency} Assessment</span>
                <span style="color: #888;">Question ${count + 1}</span>
            </div>
            <div class="stem">${q.stem}</div>
            <div class="options-grid">
                <button class="option-btn" data-ans="A"><span class="option-label">A</span> ${q.option_a}</button>
                <button class="option-btn" data-ans="B"><span class="option-label">B</span> ${q.option_b}</button>
                <button class="option-btn" data-ans="C"><span class="option-label">C</span> ${q.option_c}</button>
                <button class="option-btn" data-ans="D"><span class="option-label">D</span> ${q.option_d}</button>
            </div>
            <div class="progress-container">
                <div class="progress-bar-bg"><div class="progress-bar-fill" style="width: ${Math.min(100, (count/20)*100)}%"></div></div>
                <div class="stats">
                    <span>Confidence: ${Math.max(0, Math.min(100, 100 - (currentSEM * 100))).toFixed(0)}%</span>
                    <span style="margin-left: auto;">Targeting: ${q.cefr_target}</span>
                </div>
            </div>
        `;

        document.querySelectorAll('.option-btn').forEach(btn => {
            btn.addEventListener('click', () => submitAnswer(btn.dataset.ans));
        });
    }

    async function submitAnswer(ans) {
        // Disable buttons
        document.querySelectorAll('.option-btn').forEach(btn => btn.style.pointerEvents = 'none');

        try {
            const response = await fetch('api/practise/cat_test/submit_answer.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    question_id: currentQuestion.id,
                    answer: ans
                })
            });
            const data = await response.json();

            if (data.finished) {
                showResults(data);
            } else {
                currentSEM = data.new_sem;
                loadNextQuestion();
            }
        } catch (e) {
            showError('Failed to submit answer.');
        }
    }

    function showResults(data) {
        // Map theta to level for immediate display if not provided
        let level = 'Calculating...';
        if (data.new_theta !== undefined) {
             const theta = data.new_theta;
             if (theta >= 3.0) level = 'C2';
             else if (theta >= 2.0) level = 'C1';
             else if (theta >= 1.0) level = 'B2';
             else if (theta >= 0.0) level = 'B1';
             else if (theta >= -1.0) level = 'A2';
             else level = 'A1';
        }

        testView.innerHTML = `
            <div class="result-card">
                <i class="bi bi-check-circle-fill" style="font-size: 4rem; color: #28a745;"></i>
                <h2>Assessment Complete</h2>
                <p>Your estimated French level is:</p>
                <div class="result-level">${level}</div>
                <p style="color: #666; margin-bottom: 2rem;">Your result has been saved to your profile.</p>
                <a href="profile.php" class="btn-start" style="text-decoration: none;">View Dashboard</a>
            </div>
        `;
    }

    function showError(msg) {
        testView.innerHTML = `
            <div style="text-align: center; padding: 2rem;">
                <i class="bi bi-exclamation-triangle" style="font-size: 3rem; color: #dc3545;"></i>
                <h3 style="margin-top: 1rem;">Oops!</h3>
                <p>${msg}</p>
                <a href="practise/french_level_test/index.php" class="btn-start" style="text-decoration: none; background: #6c757d;">Go Back</a>
            </div>
        `;
    }

    initTest();
});
