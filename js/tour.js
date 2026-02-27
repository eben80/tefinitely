document.addEventListener('DOMContentLoaded', () => {
    // Only run the tour check on the dashboard page
    if (window.location.pathname.endsWith('logged_in.php')) {
        // Delay a bit to ensure session check in auth.js might have finished,
        // though checkTourStatus also fetches session.
        setTimeout(checkTourStatus, 500);
    }
});

async function checkTourStatus() {
    try {
        const response = await fetch('api/check_session.php');
        const data = await response.json();

        // Check if tour_completed is explicitly 0 or false
        if (data.loggedIn && (data.user.tour_completed == 0 || data.user.tour_completed === false)) {
            startTour();
        }
    } catch (error) {
        console.error('Error checking tour status:', error);
    }
}

function startTour() {
    if (typeof Shepherd === 'undefined') {
        console.error('Shepherd is not loaded');
        return;
    }

    const tour = new Shepherd.Tour({
        useModalOverlay: true,
        defaultStepOptions: {
            cancelIcon: {
                enabled: true
            },
            classes: 'shepherd-theme-arrows',
            scrollTo: { behavior: 'smooth', block: 'center' }
        }
    });

    tour.addStep({
        id: 'welcome',
        text: 'Welcome to TEFinitely! Let us show you around the key features of the platform.',
        attachTo: {
            element: 'h1',
            on: 'bottom'
        },
        buttons: [
            {
                text: 'Skip',
                action: tour.cancel,
                classes: 'shepherd-button-secondary'
            },
            {
                text: 'Next',
                action: tour.next
            }
        ]
    });

    tour.addStep({
        id: 'dashboard-overview',
        text: 'This dashboard is your central hub for TEF Canada preparation.',
        attachTo: {
            element: '#main-content h1',
            on: 'bottom'
        },
        buttons: [
            {
                text: 'Back',
                action: tour.back,
                classes: 'shepherd-button-secondary'
            },
            {
                text: 'Next',
                action: tour.next
            }
        ]
    });

    tour.addStep({
        id: 'oral-expression-hub',
        text: 'The <strong>Oral Expression</strong> section is where you can find Flashcards and Interactive Practice for Sections A and B. It is the core of our platform.',
        attachTo: {
            element: '.section-card:nth-of-type(1)',
            on: 'bottom'
        },
        buttons: [
            {
                text: 'Back',
                action: tour.back,
                classes: 'shepherd-button-secondary'
            },
            {
                text: 'Next',
                action: tour.next
            }
        ]
    });

    tour.addStep({
        id: 'phased-training-link',
        text: 'Our <strong>Phased Training</strong> takes you through a structured 5-phase process: Shadowing, Question Drills, Roleplays, Spontaneity, and Script Writing.',
        attachTo: {
            element: '.section-card:nth-of-type(2)',
            on: 'bottom'
        },
        buttons: [
            {
                text: 'Back',
                action: tour.back,
                classes: 'shepherd-button-secondary'
            },
            {
                text: 'Next',
                action: tour.next
            }
        ]
    });

    tour.addStep({
        id: 'nav-menu',
        text: 'You can use the top navigation menu to quickly jump between modules from any page.',
        attachTo: {
            element: '.nav-links',
            on: 'bottom'
        },
        buttons: [
            {
                text: 'Back',
                action: tour.back,
                classes: 'shepherd-button-secondary'
            },
            {
                text: 'Next',
                action: tour.next
            }
        ]
    });

    tour.addStep({
        id: 'profile-link',
        text: 'Access your <strong>Profile</strong> to track your progress, see your performance stats, or restart this tour anytime.',
        attachTo: {
            element: 'a[href="profile.php"]',
            on: 'bottom'
        },
        buttons: [
            {
                text: 'Back',
                action: tour.back,
                classes: 'shepherd-button-secondary'
            },
            {
                text: 'Finish',
                action: tour.complete
            }
        ]
    });

    tour.on('complete', markTourAsCompleted);
    tour.on('cancel', markTourAsCompleted);

    tour.start();
}

async function markTourAsCompleted() {
    try {
        const response = await fetch('api/profile/update_user_details.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ tour_completed: true })
        });
        const data = await response.json();
        console.log('Tour completion status updated:', data);
    } catch (error) {
        console.error('Error marking tour as completed:', error);
    }
}
