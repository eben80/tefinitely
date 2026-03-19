// --- Application State ---
let phrases = [];
let currentPhraseIndex = 0;
let currentTopic = '';
let speechRate = 1.0;
let recognition;
let userProgress = [];

async function fetchProgress() {
    try {
        const response = await fetch('api/profile/get_progress.php');
        const data = await response.json();
        if (data.status === 'success') {
            userProgress = data.progress;
        }
    } catch (error) {
        console.error('Failed to fetch user progress:', error);
    }
}

// --- Utility Functions ---
function speakFrench(text, rate) {
    const utterance = new SpeechSynthesisUtterance(text);

    const setVoiceAndSpeak = () => {
        const voices = speechSynthesis.getVoices();
        let frenchVoice;

        const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
        if (isIOS) {
            frenchVoice = voices.find(voice => voice.name === 'Thomas' && voice.lang === 'fr-FR');
        }

        if (!frenchVoice) {
          frenchVoice = voices.find(voice => voice.lang === 'fr-FR');
        }

        if (frenchVoice) {
          utterance.voice = frenchVoice;
        }

        utterance.lang = 'fr-FR';
        utterance.rate = rate;
        speechSynthesis.speak(utterance);
    };

    if (speechSynthesis.getVoices().length > 0) {
        setVoiceAndSpeak();
    } else {
        speechSynthesis.onvoiceschanged = setVoiceAndSpeak;
    }
}

function stopRecording() {
    const startRecordBtn = document.getElementById('startRecordBtn');
    if (startRecordBtn) startRecordBtn.disabled = false;

    if (recognition) {
      recognition.stop();
    }
}

async function checkPronunciation(userTranscript) {
    const recordingResult = document.getElementById('recordingResult');
    if (!recordingResult) return;

    const target = phrases[currentPhraseIndex].french_text.toLowerCase().replace(/[.,!?;]/g, '');
    const userWords = userTranscript.toLowerCase().replace(/[.,!?;]/g, '').split(/\s+/);
    const targetWords = target.split(/\s+/);

    let matchCount = 0;
    targetWords.forEach(w => {
      if (userWords.includes(w)) matchCount++;
    });

    const ratio = targetWords.length > 0 ? matchCount / targetWords.length : 0;
    const score = (ratio * 100).toFixed(2);
    let resultText = '';
    if (ratio > 0.6) {
      resultText = 'Good pronunciation!';
      recordingResult.style.color = 'green';
    } else {
      resultText = 'Pronunciation could be improved. Try again.';
      recordingResult.style.color = 'red';
    }
    recordingResult.textContent = `${resultText} (Score: ${score}%)`;

    if (phrases[currentPhraseIndex]) {
        try {
            const response = await fetch('api/profile/store_progress.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    phrase_id: phrases[currentPhraseIndex].id,
                    matching_quality: ratio
                })
            });
            if (response.ok) {
                await fetchProgress();
                updateTopicProgressDisplay(document.getElementById('topicSelect').value);
            }
        } catch (error) {
            console.error('Failed to store progress:', error);
        }
    }
}

function savePosition(topic, index) {
    fetch('api/profile/update_user_details.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ last_topic: topic, last_card_index: index })
    });
}

async function populateTopics(topicSelect) {
    if (!topicSelect) return;
    try {
        const response = await fetch('api/get_topics.php?section=A');
        const data = await response.json();
        if (response.ok && data.status === 'success') {
            const topics = data.topics;
            if (topics.A) {
                topics.A.forEach(theme => {
                    const option = document.createElement('option');
                    option.value = `A-${theme}`;

                    let displayName = theme.replace('Ask a question about A - ', '');
                    displayName = displayName.replace(/_/g, ' ').replace(/\b\w/g, char => char.toUpperCase());

                    option.textContent = displayName;
                    topicSelect.appendChild(option);
                });
            }
        }
    } catch (error) {
        console.error('Error fetching topics:', error);
    }
}

// --- Initializers ---
function updateTopicProgressDisplay(topicValue) {
    const progressDisplay = document.getElementById('topic-progress-display');
    if (!topicValue) {
        progressDisplay.textContent = '';
        return;
    }
    const [sectionShort, theme] = topicValue.split('-');
    const section = `Section ${sectionShort}`;

    // In our DB, section A is often stored as 'section_a' instead of 'Section A'
    // Let's try to match both common formats
    const topicProgress = userProgress.find(p =>
        (p.section === section || p.section === `section_${sectionShort.toLowerCase()}`) &&
        p.theme === theme
    );

    if (topicProgress) {
        const scorePercent = (topicProgress.average_matching_quality * 100).toFixed(0);
        progressDisplay.textContent = `Progress: ${topicProgress.phrases_covered}/${topicProgress.total_phrases} phrases, Avg Score: ${scorePercent}%`;
    } else {
        progressDisplay.textContent = 'No progress yet.';
    }
}

function initializeMainFlashcard() {
    const mainContent = document.getElementById('main-content');
    const topicSelect = document.getElementById('topicSelect');
    const phraseBox = document.getElementById('phraseBox');
    const phraseFrench = document.getElementById('phraseFrench');
    const phraseEnglish = document.getElementById('phraseEnglish');
    const playPhraseBtn = document.getElementById('playPhraseBtn');
    const firstCardBtn = document.getElementById('firstCardBtn');
    const prevPhraseBtn = document.getElementById('prevPhraseBtn');
    const nextPhraseBtn = document.getElementById('nextPhraseBtn');
    const flipCardBtn = document.getElementById('flipCardBtn');
    const flashcard = document.querySelector('.flashcard');
    const currentCardSpan = document.getElementById('current-card');
    const totalCardsSpan = document.getElementById('total-cards');
    const startRecordBtn = document.getElementById('startRecordBtn');
    const recordingResult = document.getElementById('recordingResult');

    let silenceTimer;
    let isListening = false;

    let accumulatedTranscript = '';
    if(startRecordBtn) {
        startRecordBtn.onclick = () => {
            const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;

            if (isListening) {
                recognition.stop();
                return;
            }

            if (!('SpeechRecognition' in window || 'webkitSpeechRecognition' in window)) {
              alert("Sorry, your browser does not support Speech Recognition.");
              return;
            }

            accumulatedTranscript = '';
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            recognition = new SpeechRecognition();
            recognition.lang = 'fr-FR';
            recognition.interimResults = true;
            recognition.continuous = true;

            const resetSilenceTimer = () => {
                clearTimeout(silenceTimer);
                silenceTimer = setTimeout(() => {
                    if (isListening) {
                        recognition.stop();
                    }
                }, 2000); // 2 seconds of silence before auto-stopping
            };

            recognition.onstart = () => {
                console.log('Speech recognition started.');
                isListening = true;
                startRecordBtn.classList.add('listening');
                recordingResult.textContent = 'Listening...';
                resetSilenceTimer();
            };

            recognition.onnomatch = () => console.log('Speech not recognized.');

            recognition.onresult = (event) => {
                for (let i = event.resultIndex; i < event.results.length; ++i) {
                    if (event.results[i].isFinal) {
                        accumulatedTranscript += event.results[i][0].transcript + ' ';
                    }
                }
                resetSilenceTimer();
            };

            recognition.onerror = (event) => {
                console.log('Speech recognition error:', event);
                recordingResult.textContent = 'Speech recognition error: ' + event.error;
            };

            recognition.onend = () => {
                console.log('Speech recognition ended.');
                isListening = false;
                if (startRecordBtn) {
                    startRecordBtn.classList.remove('listening');
                }
                clearTimeout(silenceTimer);
                if (accumulatedTranscript.trim()) {
                    checkPronunciation(accumulatedTranscript.trim());
                } else {
                    recordingResult.textContent = 'No speech detected.';
                }
            };

            recognition.start();

            if (isIOS) {
              console.log('iOS device detected. Setting 15s safety timeout.');
              setTimeout(() => {
                if (isListening && recognition) {
                  console.log('iOS safety timeout reached. Forcing recognition.stop().');
                  recognition.stop();
                }
              }, 15000);
            }
        };
    }

    function displayPhrase(idx) {
        const p = phrases[idx];
        phraseFrench.textContent = p.french_text;
        phraseEnglish.textContent = p.english_translation;
        flashcard.classList.remove('is-flipped');
        updateNavButtons();
        currentCardSpan.textContent = idx + 1;
        savePosition(currentTopic, idx);
        if (recordingResult) recordingResult.textContent = '';
    }

    function updateNavButtons() {
        firstCardBtn.disabled = (currentPhraseIndex === 0);
        prevPhraseBtn.disabled = (currentPhraseIndex === 0);
        nextPhraseBtn.disabled = (currentPhraseIndex >= phrases.length - 1);
    }

    async function loadPhrases(topicValue, initialCardIndex = 0) {
        currentTopic = topicValue;
        savePosition(currentTopic, initialCardIndex);
        const [section, theme] = topicValue.split('-');
        if (!section || !theme) return;
        try {
            const res = await fetch(`api/get_phrases.php?theme=${encodeURIComponent(theme)}&section=${encodeURIComponent(section)}`);
            phrases = await res.json();
            totalCardsSpan.textContent = phrases.length;
            currentPhraseIndex = initialCardIndex || 0;
            if (currentPhraseIndex >= phrases.length) currentPhraseIndex = 0;
            displayPhrase(currentPhraseIndex);
            phraseBox.style.display = 'block';
        } catch (e) {
            showToast('Error loading phrases: ' + e.message, 'error');
        }
    }

    topicSelect.addEventListener('change', () => {
        loadPhrases(topicSelect.value);
        updateTopicProgressDisplay(topicSelect.value);
    });
    flipCardBtn.addEventListener('click', (e) => { e.stopPropagation(); flashcard.classList.toggle('is-flipped'); });
    flashcard.addEventListener('click', () => flashcard.classList.toggle('is-flipped'));
    playPhraseBtn.addEventListener('click', () => { if (phraseFrench.textContent) speakFrench(phraseFrench.textContent, speechRate); });
    firstCardBtn.addEventListener('click', () => {
        if (currentPhraseIndex > 0) {
            currentPhraseIndex = 0;
            displayPhrase(0);
        }
    });

    prevPhraseBtn.addEventListener('click', () => {
        if (currentPhraseIndex > 0) {
            flashcard.classList.remove('is-flipped');
            phraseBox.classList.add('slide-out-right');
            setTimeout(() => {
                currentPhraseIndex--;
                displayPhrase(currentPhraseIndex);
                phraseBox.classList.remove('slide-out-right');
            }, 300);
        }
    });

    nextPhraseBtn.addEventListener('click', () => {
        if (currentPhraseIndex < phrases.length - 1) {
            flashcard.classList.remove('is-flipped');
            phraseBox.classList.add('slide-out-left');
            setTimeout(() => {
                currentPhraseIndex++;
                displayPhrase(currentPhraseIndex);
                phraseBox.classList.remove('slide-out-left');
            }, 300);
        }
    });

    let touchstartX = 0;
    flashcard.addEventListener('touchstart', e => { touchstartX = e.changedTouches[0].screenX; });
    flashcard.addEventListener('touchend', e => {
        const touchendX = e.changedTouches[0].screenX;
        if (Math.abs(touchendX - touchstartX) > 50) {
            if (touchendX < touchstartX) nextPhraseBtn.click();
            else prevPhraseBtn.click();
        }
    });

    // Return the function to load phrases so it can be called after topics are populated
    return { loadPhrases };
}

document.addEventListener('keydown', (e) => {
    // Check if the main content is visible before handling keyboard shortcuts
    const mainContent = document.getElementById('main-content');
    if (mainContent && mainContent.style.display !== 'none') {
        switch (e.code) {
            case 'ArrowLeft':
                document.getElementById('prevPhraseBtn').click();
                break;
            case 'ArrowRight':
                document.getElementById('nextPhraseBtn').click();
                break;
            case 'Space':
                e.preventDefault(); // Prevent page from scrolling
                document.getElementById('flipCardBtn').click();
                break;
            case 'KeyS':
                document.getElementById('playPhraseBtn').click();
                break;
            case 'KeyR':
                document.getElementById('startRecordBtn').click();
                break;
        }
    }
});

// --- Page Load Logic ---
document.addEventListener('DOMContentLoaded', async () => {
    const mainContentDiv = document.getElementById('main-content');
    const speechRateInput = document.getElementById('speechRate');
    const rateDisplay = document.getElementById('rateDisplay');

    speechRateInput.addEventListener('input', () => {
        speechRate = parseFloat(speechRateInput.value);
        rateDisplay.textContent = speechRate.toFixed(1);
        localStorage.setItem('savedSpeechRate', speechRate);
    });

    const savedRate = localStorage.getItem('savedSpeechRate');
    if (savedRate) {
        speechRate = parseFloat(savedRate);
        speechRateInput.value = savedRate;
        rateDisplay.textContent = parseFloat(savedRate).toFixed(1);
    }

    // Check if the user is a subscriber before initializing the flashcards
    const response = await fetch('api/check_session.php');
    if (response.ok) {
        const data = await response.json();
        if (data.loggedIn && data.user.subscription_status === 'active') {
            mainContentDiv.style.display = 'block';

            const mainFlashcard = initializeMainFlashcard();
            const topicSelect = document.getElementById('topicSelect');
            await populateTopics(topicSelect);
            await fetchProgress();

            if (data.user.last_topic) {
                topicSelect.value = data.user.last_topic;
                mainFlashcard.loadPhrases(data.user.last_topic, data.user.last_card_index);
                updateTopicProgressDisplay(data.user.last_topic);
            }
        }
    }
});
