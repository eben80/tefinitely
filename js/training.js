document.addEventListener('DOMContentLoaded', () => {
    // This script should only run on the training page for logged-in, active users.
    // main.js will handle the redirection if the user is not logged in.

    // Tab navigation
    const navButtons = document.querySelectorAll('.training-nav button');
    const modules = document.querySelectorAll('.training-module');

    navButtons.forEach(button => {
        button.addEventListener('click', () => {
            navButtons.forEach(btn => btn.classList.remove('active'));
            modules.forEach(module => module.classList.remove('active'));
            button.classList.add('active');
            const phase = button.id.replace('-btn', '');
            const module = document.getElementById(`${phase}-module`);
            if(module) module.classList.add('active');
        });
    });

    // --- Global Controls ---
    const speechRateInput = document.getElementById('speechRate');
    const rateDisplay = document.getElementById('rateDisplay');
    let speechRate = 1.0;

    function loadSpeechRate() {
        const savedRate = localStorage.getItem('savedSpeechRate');
        if (savedRate) {
            speechRate = parseFloat(savedRate);
            speechRateInput.value = savedRate;
            rateDisplay.textContent = parseFloat(savedRate).toFixed(1);
        }
    }

    if(speechRateInput) {
        speechRateInput.addEventListener('input', () => {
            speechRate = parseFloat(speechRateInput.value);
            rateDisplay.textContent = speechRate.toFixed(1);
            localStorage.setItem('savedSpeechRate', speechRate);
        });
        loadSpeechRate();
    }

    let recognition;
    let dialogueProgress = [];

    async function fetchDialogueProgress() {
        try {
            const response = await fetch('api/profile/get_dialogue_progress.php');
            const data = await response.json();
            if (data.status === 'success') {
                dialogueProgress = data.progress;
            }
        } catch (error) {
            console.error('Failed to fetch dialogue progress:', error);
        }
    }

    function speakFrench(text, onEndCallback) {
        const utterance = new SpeechSynthesisUtterance(text);
        if (onEndCallback) utterance.onend = onEndCallback;

        const setVoiceAndSpeak = () => {
            const voices = speechSynthesis.getVoices();
            let frenchVoice = voices.find(v => v.name === 'Thomas' && v.lang === 'fr-FR') || voices.find(v => v.lang === 'fr-FR');
            if (frenchVoice) utterance.voice = frenchVoice;
            utterance.lang = 'fr-FR';
            utterance.rate = speechRate;
            speechSynthesis.speak(utterance);
        };

        if (speechSynthesis.getVoices().length > 0) setVoiceAndSpeak();
        else speechSynthesis.onvoiceschanged = setVoiceAndSpeak;
    }

    function checkPronunciation(userTranscript, targetText, resultElement, dialogueId, lineId) {
        const target = targetText.toLowerCase().replace(/[.,!?;]/g, '');
        const userWords = userTranscript.toLowerCase().replace(/[.,!?;]/g, '').split(/\s+/);
        const targetWords = target.split(/\s+/);

        let matchCount = 0;
        targetWords.forEach(w => { if (userWords.includes(w)) matchCount++; });

        const ratio = targetWords.length > 0 ? matchCount / targetWords.length : 0;
        const score = (ratio * 100).toFixed(2);

        resultElement.textContent = ` (Score: ${score}%)`;
        resultElement.style.color = ratio > 0.6 ? 'green' : 'red';

        fetch('api/profile/store_dialogue_progress.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ dialogue_id: dialogueId, line_id: lineId, score: ratio })
        });

        setTimeout(() => { resultElement.textContent = ''; }, 5000);
    }

    function startPronunciationCheck(targetText, resultElement, dialogueId, lineId) {
        if (!('SpeechRecognition' in window || 'webkitSpeechRecognition' in window)) {
            return alert("Sorry, your browser does not support Speech Recognition.");
        }

        document.querySelectorAll('.check-pronunciation-btn').forEach(b => b.disabled = true);
        resultElement.textContent = " Listening...";

        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        recognition = new SpeechRecognition();
        recognition.lang = 'fr-FR';
        recognition.interimResults = false;
        recognition.maxAlternatives = 1;

        recognition.onresult = (event) => {
            const transcript = event.results[0][0].transcript.toLowerCase();
            checkPronunciation(transcript, targetText, resultElement, dialogueId, lineId);
        };
        recognition.onerror = (event) => { resultElement.textContent = ' Error: ' + event.error; };
        recognition.onend = () => { document.querySelectorAll('.check-pronunciation-btn').forEach(b => b.disabled = false); };
        recognition.onspeechend = () => { recognition.stop(); };
        recognition.start();

        if (/iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream) {
            setTimeout(() => { if (recognition) recognition.stop(); }, 5000);
        }
    }

    // --- Phase 1: Shadowing ---
    const dialogueSelect = document.getElementById('dialogueSelect');
    const dialogueContent = document.getElementById('dialogue-content');
    const playFullDialogueBtn = document.getElementById('play-full-dialogue-btn');

    if (dialogueSelect) {
        async function loadDialogueList() {
            try {
                const response = await fetch('api/get_dialogues_list.php');
                const dialogues = await response.json();
                if (response.ok) {
                    dialogueSelect.innerHTML = '<option value="">-- Choose a dialogue --</option>';
                    dialogues.forEach(d => {
                        const option = document.createElement('option');
                        option.value = d.id;
                        option.textContent = d.dialogue_name;
                        dialogueSelect.appendChild(option);
                    });
                    if (dialogues.length > 0) {
                        dialogueSelect.value = dialogues[0].id;
                        dialogueSelect.dispatchEvent(new Event('change'));
                    }
                } else { showToast('Failed to load dialogues.', 'error'); }
            } catch (error) { showToast('Could not fetch dialogue list.', 'error'); }
        }

        async function loadDialogue(id) {
            if (!id) { dialogueContent.innerHTML = ''; return; }
            try {
                const response = await fetch(`api/get_dialogue.php?id=${id}`);
                const dialogue = await response.json();
                if (response.ok) renderDialogue(dialogue);
                else showToast('Failed to load dialogue.', 'error');
            } catch (error) { showToast('Could not fetch dialogue.', 'error'); }
        }

        function renderDialogue(dialogue) {
            dialogueContent.innerHTML = `<h3>${dialogue.name}</h3>`;
            dialogue.lines.forEach((line, index) => {
                const lineEl = document.createElement('p');
                lineEl.innerHTML = `<strong>${line.speaker}:</strong> ${line.line}
                    <button class="play-line-btn" data-line-index="${index}">▶️</button>
                    <button class="check-pronunciation-btn" data-line-index="${index}">🎤</button>
                    <span class="pronunciation-result" data-line-index="${index}"></span>`;
                dialogueContent.appendChild(lineEl);
            });

            dialogueContent.querySelectorAll('.play-line-btn').forEach(btn => {
                btn.addEventListener('click', (e) => speakFrench(dialogue.lines[e.target.dataset.lineIndex].line));
            });
            dialogueContent.querySelectorAll('.check-pronunciation-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const line = dialogue.lines[e.target.dataset.lineIndex];
                    const resultSpan = dialogueContent.querySelector(`.pronunciation-result[data-line-index="${e.target.dataset.lineIndex}"]`);
                    startPronunciationCheck(line.line, resultSpan, dialogue.id, line.id);
                });
            });
        }

        dialogueSelect.addEventListener('change', () => {
            loadDialogue(dialogueSelect.value);
            const progressDisplay = document.getElementById('dialogue-progress-display');
            if (!dialogueSelect.value) { progressDisplay.textContent = ''; return; }
            const progress = dialogueProgress.find(p => p.dialogue_id == dialogueSelect.value);
            if (progress && progress.attempted_lines > 0) {
                progressDisplay.textContent = `Coverage: ${(progress.coverage * 100).toFixed(0)}%, Avg Score: ${(progress.average_score * 100).toFixed(0)}%`;
            } else { progressDisplay.textContent = 'No progress yet.'; }
        });

        playFullDialogueBtn.addEventListener('click', async () => {
            const id = dialogueSelect.value;
            if (!id) return;
            const response = await fetch(`api/get_dialogue.php?id=${id}`);
            const dialogue = await response.json();
            if (response.ok) {
                let i = 0;
                const playNext = () => {
                    if (i < dialogue.lines.length) speakFrench(dialogue.lines[i++].line, playNext);
                };
                playNext();
            }
        });

        (async () => {
            await fetchDialogueProgress();
            await loadDialogueList();
        })();
    }
});
