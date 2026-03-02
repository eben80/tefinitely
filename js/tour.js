document.addEventListener('DOMContentLoaded', () => {
    // Determine which page we are on
    const path = window.location.pathname;

    if (path.endsWith('logged_in.php')) {
        setTimeout(() => checkTourStatus('main'), 500);
    } else if (path.includes('practise/section_a/')) {
        setTimeout(() => checkTourStatus('section_a'), 500);
    } else if (path.includes('practise/section_b/')) {
        setTimeout(() => checkTourStatus('section_b'), 500);
    }
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
        text: 'Access your <strong>Profile</strong> to track progress or restart tours.',
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
        id: 'section-a-welcome',
        text: 'Welcome to <strong>Section A Practice</strong>! Here you will practice gathering information through questions.',
        attachTo: { element: '#page-title', on: 'bottom' },
        buttons: [
            { text: 'Skip', action: tour.cancel, classes: 'shepherd-button-secondary' },
            { text: 'Next', action: tour.next }
        ]
    });

    tour.addStep({
        id: 'setup-options',
        text: 'Start by choosing your language, level, and speech speed.',
        attachTo: { element: '#setup-container', on: 'top' },
        buttons: [
            { text: 'Back', action: tour.back, classes: 'shepherd-button-secondary' },
            { text: 'Next', action: tour.next }
        ]
    });

    // We can't easily highlight elements inside #main-content if it's hidden,
    // but the tour will wait for startSession to show it.
    // For now, let's assume they start it.

    tour.addStep({
        id: 'start-session',
        text: 'Click <strong>Démarrer</strong> (or Start) to begin your simulation.',
        attachTo: { element: '#start-btn', on: 'bottom' },
        buttons: [
            { text: 'Back', action: tour.back, classes: 'shepherd-button-secondary' },
            { text: 'Next', action: tour.next }
        ]
    });

    tour.addStep({
        id: 'interaction-area',
        text: 'During the simulation, use the input box to ask questions. You can also use <strong>🎤 Parler</strong> for speech-to-text.',
        attachTo: { element: '#input-area', on: 'top' },
        buttons: [
            { text: 'Back', action: tour.back, classes: 'shepherd-button-secondary' },
            { text: 'Next', action: tour.next }
        ]
    });

    tour.addStep({
        id: 'hints-section-a',
        text: 'Stuck? The <strong>💡</strong> button provides question ideas matching the advertisement.',
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
        id: 'section-b-welcome',
        text: 'Welcome to <strong>Section B Practice</strong>! This section focuses on persuasion and structured arguments.',
        attachTo: { element: '#page-title', on: 'bottom' },
        buttons: [
            { text: 'Skip', action: tour.cancel, classes: 'shepherd-button-secondary' },
            { text: 'Next', action: tour.next }
        ]
    });

    tour.addStep({
        id: 'setup-options-b',
        text: 'Configure your practice session here.',
        attachTo: { element: '#setup-container', on: 'top' },
        buttons: [
            { text: 'Back', action: tour.back, classes: 'shepherd-button-secondary' },
            { text: 'Next', action: tour.next }
        ]
    });

    tour.addStep({
        id: 'persuasion-task',
        text: 'In Section B, you must convince a skeptical friend. Click <strong>Démarrer</strong> to begin.',
        attachTo: { element: '#start-btn', on: 'bottom' },
        buttons: [
            { text: 'Back', action: tour.back, classes: 'shepherd-button-secondary' },
            { text: 'Next', action: tour.next }
        ]
    });

    tour.addStep({
        id: 'interaction-area-b',
        text: 'Present your arguments here. Your partner will push back, so be prepared to defend your position!',
        attachTo: { element: '#input-area', on: 'top' },
        buttons: [
            { text: 'Back', action: tour.back, classes: 'shepherd-button-secondary' },
            { text: 'Next', action: tour.next }
        ]
    });

    tour.addStep({
        id: 'hints-section-b',
        text: 'Need arguments? The <strong>💡</strong> button gives you persuasive ideas for the current topic.',
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
