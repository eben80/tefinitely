document.addEventListener('user-active', (e) => {
    const user = e.detail;
    initializeFlashcardApp(user);
});

document.addEventListener('user-inactive', () => {
    renderPayPalSubscriptionButton();
});

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

function speakFrench(text, rate) {
    const utterance = new SpeechSynthesisUtterance(text);
    const setVoiceAndSpeak = () => {
        const voices = speechSynthesis.getVoices();
        let frenchVoice = voices.find(v => v.lang === 'fr-FR' && v.name === 'Thomas') || voices.find(v => v.lang === 'fr-FR');
        if (frenchVoice) utterance.voice = frenchVoice;
        utterance.lang = 'fr-FR';
        utterance.rate = rate;
        speechSynthesis.speak(utterance);
    };

    if (speechSynthesis.getVoices().length > 0) setVoiceAndSpeak();
    else speechSynthesis.onvoiceschanged = setVoiceAndSpeak;
}

function savePosition(topic, index) {
    fetch('api/profile/update_user_details.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ last_topic: topic, last_card_index: index })
    });
}

async function populateTopics(topicSelect) {
    try {
        const response = await fetch('api/get_topics.php');
        const data = await response.json();
        if (response.ok && data.status === 'success') {
            for (const section in data.topics) {
                const optgroup = document.createElement('optgroup');
                optgroup.label = section.replace(/_/g, ' ').replace(/\b\w/g, char => char.toUpperCase());
                data.topics[section].forEach(theme => {
                    const option = document.createElement('option');
                    option.value = `${section}-${theme}`;
                    option.textContent = theme.replace(/_/g, ' ').replace(/\b\w/g, char => char.toUpperCase());
                    optgroup.appendChild(option);
                });
                topicSelect.appendChild(optgroup);
            }
        }
    } catch (error) {
        console.error('Error fetching topics:', error);
    }
}

function updateTopicProgressDisplay(topicValue) {
    const progressDisplay = document.getElementById('topic-progress-display');
    if (!topicValue) {
        progressDisplay.textContent = '';
        return;
    }
    const [section, theme] = topicValue.split('-');
    const topicProgress = userProgress.find(p => p.section === section && p.theme === theme);

    if (topicProgress) {
        const scorePercent = (topicProgress.average_matching_quality * 100).toFixed(0);
        progressDisplay.textContent = `Progress: ${topicProgress.phrases_covered}/${topicProgress.total_phrases} phrases, Avg Score: ${scorePercent}%`;
    } else {
        progressDisplay.textContent = 'No progress yet.';
    }
}

async function initializeFlashcardApp(user) {
    const topicSelect = document.getElementById('topicSelect');
    const phraseBox = document.getElementById('phraseBox');
    const phraseFrench = document.getElementById('phraseFrench');
    const phraseEnglish = document.getElementById('phraseEnglish');
    const playPhraseBtn = document.getElementById('playPhraseBtn');
    const firstCardBtn = document.getElementById('firstCardBtn');
    const prevPhraseBtn = document.getElementById('prevPhraseBtn');
    const nextPhraseBtn = document.getElementById('nextPhraseBtn');
    const flipCardBtn = document.getElementById('flipCardBtn');
    const flashcard = document.querySelector('#flashcard-app-container .flashcard');
    const currentCardSpan = document.getElementById('current-card');
    const totalCardsSpan = document.getElementById('total-cards');

    function displayPhrase(idx) {
        const p = phrases[idx];
        phraseFrench.textContent = p.french_text;
        phraseEnglish.textContent = p.english_translation;
        flashcard.classList.remove('is-flipped');
        updateNavButtons();
        currentCardSpan.textContent = idx + 1;
        savePosition(currentTopic, idx);
    }

    function updateNavButtons() {
        firstCardBtn.disabled = (currentPhraseIndex === 0);
        prevPhraseBtn.disabled = (currentPhraseIndex === 0);
        nextPhraseBtn.disabled = (currentPhraseIndex >= phrases.length - 1);
    }

    async function loadPhrases(topicValue, initialCardIndex = 0) {
        currentTopic = topicValue;
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
    flipCardBtn.addEventListener('click', () => flashcard.classList.toggle('is-flipped'));
    flashcard.addEventListener('click', () => flashcard.classList.toggle('is-flipped'));
    playPhraseBtn.addEventListener('click', () => { if (phraseFrench.textContent) speakFrench(phraseFrench.textContent, 1.0); });
    firstCardBtn.addEventListener('click', () => { if (currentPhraseIndex > 0) displayPhrase(0); });
    prevPhraseBtn.addEventListener('click', () => { if (currentPhraseIndex > 0) displayPhrase(currentPhraseIndex - 1); });
    nextPhraseBtn.addEventListener('click', () => { if (currentPhraseIndex < phrases.length - 1) displayPhrase(currentPhraseIndex + 1); });

    // Add swipe listeners
    let touchstartX = 0;
    flashcard.addEventListener('touchstart', e => {
        touchstartX = e.changedTouches[0].screenX;
    }, { passive: true });

    flashcard.addEventListener('touchend', e => {
        const touchendX = e.changedTouches[0].screenX;
        const deltaX = touchendX - touchstartX;
        if (Math.abs(deltaX) > 50) { // Threshold for swipe
            if (deltaX < 0) {
                // Swiped left
                nextPhraseBtn.click();
            } else {
                // Swiped right
                prevPhraseBtn.click();
            }
        }
    }, { passive: true });

    // Initial population
    await populateTopics(topicSelect);
    await fetchProgress();

    if (user.tour_completed == 0) {
        initializeTour(loadPhrases);
    } else if (user.last_topic) {
        topicSelect.value = user.last_topic;
        loadPhrases(user.last_topic, user.last_card_index);
        updateTopicProgressDisplay(user.last_topic);
    }
}

function renderPayPalSubscriptionButton() {
    const paypalScript = document.getElementById('paypal-sdk-script');
    if (!paypalScript) return;
    const planId = paypalScript.getAttribute('data-plan-id');
    if (!planId || planId === 'YOUR_PAYPAL_PLAN_ID') return;

    paypal.Buttons({
        style: { shape: 'rect', color: 'gold', layout: 'vertical', label: 'subscribe' },
        createSubscription: (data, actions) => actions.subscription.create({ 'plan_id': planId }),
        onApprove: async (data, actions) => {
            showToast('Subscription approved! Finalizing...', 'info');
            try {
                const response = await fetch('api/paypal/capture_subscription.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ subscriptionID: data.subscriptionID })
                });
                const result = await response.json();
                if (result.status === 'success') {
                    showToast('Subscription successful! You now have access.', 'success');
                    setTimeout(() => window.location.reload(), 2000);
                } else {
                    showToast('Failed to activate subscription: ' + (result.message || 'Unknown error'), 'error');
                }
            } catch (error) {
                showToast('An error occurred while finalizing your subscription.', 'error');
            }
        },
        onError: (err) => showToast('An error occurred with the PayPal button.', 'error')
    }).render('#paypal-button-container');
}

function initializeTour(loadPhrasesCallback) {
    const tourSteps = [
      { title: 'Welcome!', content: 'This tour will guide you through the flashcard features.', target: null },
      { title: 'Select a Topic', content: 'Start by selecting a topic. Phrases for that topic will load automatically.', target: '#topicSelect' },
      { title: 'The Flashcard', content: 'This is the flashcard. Click it to flip between English and French.', target: '.flashcard' },
      { title: 'Navigation', content: 'Use these buttons to move between cards.', target: '#navButtons' },
      { title: 'Listen', content: 'Click "Play Phrase" to hear the French audio.', target: '#playPhraseBtn' },
      { title: 'All Set!', content: 'You can restart this tour from your profile page. Happy studying!', target: null }
    ];

    let currentStep = 0;
    const tourOverlay = document.getElementById('tour-overlay');
    const tourPopup = document.getElementById('tour-popup');
    const tourTitle = document.getElementById('tour-title');
    const tourContent = document.getElementById('tour-content');
    const tourSkip = document.getElementById('tour-skip');
    const tourNext = document.getElementById('tour-next');

    function showStep(index) {
        if (index === 1) { // When showing the "Select a Topic" step, load a default topic
            const topicSelect = document.getElementById('topicSelect');
            if (topicSelect.options.length > 1) {
              topicSelect.value = topicSelect.options[1].value; // First actual topic
              loadPhrasesCallback(topicSelect.value);
            }
        }

        const step = tourSteps[index];
        tourTitle.textContent = step.title;
        tourContent.textContent = step.content;

        document.querySelectorAll('.tour-highlight').forEach(el => el.classList.remove('tour-highlight'));
        if (step.target) {
            const targetElement = document.querySelector(step.target);
            if (targetElement) targetElement.classList.add('tour-highlight');
        }
        tourOverlay.style.display = 'block';
    }

    function endTour() {
        tourOverlay.style.display = 'none';
        document.querySelectorAll('.tour-highlight').forEach(el => el.classList.remove('tour-highlight'));
        fetch('api/profile/update_user_details.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ tour_completed: true })
        });
    }

    tourNext.addEventListener('click', () => {
        currentStep++;
        if (currentStep < tourSteps.length) showStep(currentStep);
        else endTour();
    });
    tourSkip.addEventListener('click', endTour);

    showStep(0);
}
