document.addEventListener('DOMContentLoaded', () => {
    // Determine which page we are on
    const path = window.location.pathname;

    if (path.endsWith('logged_in.php') || path.endsWith('index.html') || path === '/') {
        setTimeout(() => checkTourStatus('main'), 500);
    }
    // Section A and B tours are now triggered manually after session starts
});

async function checkTourStatus(tourType) {
    try {
        const response = await fetch('api/check_session.php');
        const data = await response.json();

        if (!data.loggedIn) return;

        if (tourType === 'main') {
            if (data.user.tour_completed == 0 || data.user.tour_completed === false) {
                startMainTour();
            }
        } else if (tourType === 'section_a') {
            if (data.user.tour_section_a_completed == 0 || data.user.tour_section_a_completed === false) {
                startSectionATour();
            }
        } else if (tourType === 'section_b') {
            if (data.user.tour_section_b_completed == 0 || data.user.tour_section_b_completed === false) {
                startSectionBTour();
            }
        }
    } catch (error) {
        console.error('Error checking tour status:', error);
    }
}

// Global exposure for manual triggers
window.startSectionATourIfNecessary = () => checkTourStatus('section_a');
window.startSectionBTourIfNecessary = () => checkTourStatus('section_b');

function createTourInstance() {
    if (typeof Shepherd === 'undefined') {
        console.error('Shepherd is not loaded');
        return null;
    }

    return new Shepherd.Tour({
        useModalOverlay: true,
        defaultStepOptions: {
            cancelIcon: {
                enabled: true
            },
            classes: 'shepherd-theme-arrows',
            scrollTo: { behavior: 'smooth', block: 'center' }
        }
    });
}

async function markTourAsCompleted(tourField) {
    try {
        const body = {};
        body[tourField] = true;

        const response = await fetch('api/profile/update_user_details.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(body)
        });
        const data = await response.json();
        console.log(`Tour ${tourField} completion status updated:`, data);
    } catch (error) {
        console.error(`Error marking tour ${tourField} as completed:`, error);
    }
}

function startMainTour() {
    const tour = createTourInstance();
    if (!tour) return;

    tour.addStep({
        id: 'welcome',
        text: 'Welcome to TEFinitely! Let us show you around the key features of the platform.',
        attachTo: { element: 'h1', on: 'bottom' },
        buttons: [
            { text: 'Skip', action: tour.cancel, classes: 'shepherd-button-secondary' },
            { text: 'Next', action: tour.next }
        ]
    });

    tour.addStep({
        id: 'dashboard-overview',
        text: 'This dashboard is your central hub for TEF Canada preparation.',
        attachTo: { element: '#main-content h1', on: 'bottom' },
        buttons: [
            { text: 'Back', action: tour.back, classes: 'shepherd-button-secondary' },
            { text: 'Next', action: tour.next }
        ]
    });

    tour.addStep({
        id: 'oral-expression-hub',
        text: 'The <strong>Oral Expression</strong> section is where you can find Flashcards and Interactive Practice for Sections A and B.',
        attachTo: { element: '.section-card:nth-of-type(1)', on: 'bottom' },
        buttons: [
            { text: 'Back', action: tour.back, classes: 'shepherd-button-secondary' },
            { text: 'Next', action: tour.next }
        ]
    });

    tour.addStep({
        id: 'phased-training-link',
        text: 'Our <strong>Phased Training</strong> takes you through a structured 5-phase process to build your skills.',
        attachTo: { element: '.section-card:nth-of-type(2)', on: 'bottom' },
        buttons: [
            { text: 'Back', action: tour.back, classes: 'shepherd-button-secondary' },
            { text: 'Next', action: tour.next }
        ]
    });

    tour.addStep({
        id: 'nav-menu',
        text: 'Use the navigation menu to jump between modules from any page.',
        attachTo: { element: '#hamburger-menu', on: 'bottom' },
        buttons: [
            { text: 'Back', action: tour.back, classes: 'shepherd-button-secondary' },
            { text: 'Next', action: tour.next }
        ]
    });

    tour.addStep({
        id: 'profile-link',
        text: 'Access your <strong>Dashboard</strong> to track progress or restart tours.',
        attachTo: { element: 'a[href="profile.php"]', on: 'bottom' },
        buttons: [
            { text: 'Back', action: tour.back, classes: 'shepherd-button-secondary' },
            { text: 'Finish', action: tour.complete }
        ]
    });

    tour.on('complete', () => markTourAsCompleted('tour_completed'));
    tour.on('cancel', () => markTourAsCompleted('tour_completed'));
    tour.start();
}

function startSectionATour() {
    const tour = createTourInstance();
    if (!tour) return;

    tour.addStep({
        id: 'section-a-simulation-welcome',
        text: 'Welcome to your <strong>Section A Simulation</strong>! Let’s walk through the tools available during your practice.',
        attachTo: { element: '#instruction-display', on: 'bottom' },
        buttons: [
            { text: 'Skip', action: tour.cancel, classes: 'shepherd-button-secondary' },
            { text: 'Next', action: tour.next }
        ]
    });

    tour.addStep({
        id: 'advertisement-focus',
        text: 'This is the <strong>Advertisement</strong>. Your task is to ask questions based on this information.',
        attachTo: { element: '#advertisement-poster', on: 'top' },
        buttons: [
            { text: 'Back', action: tour.back, classes: 'shepherd-button-secondary' },
            { text: 'Next', action: tour.next }
        ]
    });

    tour.addStep({
        id: 'chat-area',
        text: 'The <strong>Chat</strong> shows your conversation with the examiner. Try to stay formal and polite!',
        attachTo: { element: '#chat-container', on: 'top' },
        buttons: [
            { text: 'Back', action: tour.back, classes: 'shepherd-button-secondary' },
            { text: 'Next', action: tour.next }
        ]
    });

    tour.addStep({
        id: 'interaction-area',
        text: 'Type your questions here. You can also use <strong>🎤 Parler</strong> to practice your speaking and get a transcript.',
        attachTo: { element: '#input-area', on: 'top' },
        buttons: [
            { text: 'Back', action: tour.back, classes: 'shepherd-button-secondary' },
            { text: 'Next', action: tour.next }
        ]
    });

    tour.addStep({
        id: 'timer-counter',
        text: 'Keep an eye on the <strong>Timer</strong> and the <strong>Question Counter</strong>. Aim for at least 10 relevant questions!',
        attachTo: { element: '#question-counter', on: 'bottom' },
        buttons: [
            { text: 'Back', action: tour.back, classes: 'shepherd-button-secondary' },
            { text: 'Next', action: tour.next }
        ]
    });

    tour.addStep({
        id: 'hints-section-a',
        text: 'If you’re stuck, click the <strong>💡 Hint</strong> button for question ideas tailored to this specific advertisement.',
        attachTo: { element: '#hint-btn', on: 'top' },
        buttons: [
            { text: 'Back', action: tour.back, classes: 'shepherd-button-secondary' },
            { text: 'Finish', action: tour.complete }
        ]
    });

    tour.on('complete', () => markTourAsCompleted('tour_section_a_completed'));
    tour.on('cancel', () => markTourAsCompleted('tour_section_a_completed'));
    tour.start();
}

function startSectionBTour() {
    const tour = createTourInstance();
    if (!tour) return;

    tour.addStep({
        id: 'section-b-simulation-welcome',
        text: 'Welcome to <strong>Section B Practice</strong>! In this simulation, you must persuade a skeptical friend.',
        attachTo: { element: '#instruction-display', on: 'bottom' },
        buttons: [
            { text: 'Skip', action: tour.cancel, classes: 'shepherd-button-secondary' },
            { text: 'Next', action: tour.next }
        ]
    });

    tour.addStep({
        id: 'topic-focus',
        text: 'Here is the <strong>Topic</strong> or advertisement you need to pitch to your friend.',
        attachTo: { element: '#advertisement-poster', on: 'top' },
        buttons: [
            { text: 'Back', action: tour.back, classes: 'shepherd-button-secondary' },
            { text: 'Next', action: tour.next }
        ]
    });

    tour.addStep({
        id: 'interaction-area-b',
        text: 'Present your arguments here. Your friend will challenge you with objections—be prepared to defend your position with examples!',
        attachTo: { element: '#input-area', on: 'top' },
        buttons: [
            { text: 'Back', action: tour.back, classes: 'shepherd-button-secondary' },
            { text: 'Next', action: tour.next }
        ]
    });

    tour.addStep({
        id: 'timer-counter-b',
        text: 'This task lasts up to 10 minutes. Use the <strong>Échanges</strong> counter to track your progress.',
        attachTo: { element: '#question-counter', on: 'bottom' },
        buttons: [
            { text: 'Back', action: tour.back, classes: 'shepherd-button-secondary' },
            { text: 'Next', action: tour.next }
        ]
    });

    tour.addStep({
        id: 'hints-section-b',
        text: 'Need persuasive points? Click the <strong>💡 Idea</strong> button for structured arguments for this topic.',
        attachTo: { element: '#hint-btn', on: 'top' },
        buttons: [
            { text: 'Back', action: tour.back, classes: 'shepherd-button-secondary' },
            { text: 'Finish', action: tour.complete }
        ]
    });

    tour.on('complete', () => markTourAsCompleted('tour_section_b_completed'));
    tour.on('cancel', () => markTourAsCompleted('tour_section_b_completed'));
    tour.start();
}
