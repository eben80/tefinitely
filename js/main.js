document.addEventListener('DOMContentLoaded', () => {
    // --- Responsive Nav Toggle ---
    const nav = document.getElementById('main-nav');
    const navToggle = document.querySelector('.nav-toggle');
    if (navToggle) {
        navToggle.addEventListener('click', () => {
            if (nav) nav.classList.toggle('is-active');
        });
    }

    // --- DOM Cache for Page Switching ---
    const guestLinks = document.getElementById('guest-links');
    const userLinks = document.getElementById('user-links');
    const userNameDisplay = document.getElementById('user-name-display');
    const adminLink = document.getElementById('admin-link');
    const logoutBtn = document.getElementById('logoutBtn');

    // Page content containers on index.html
    const landingPageContainer = document.getElementById('landing-page-container');
    const flashcardAppContainer = document.getElementById('flashcard-app-container');
    const subscriptionPromptContainer = document.getElementById('subscription-prompt-container');

    // --- Session Check and Page Routing ---
    fetch('api/check_session.php')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.loggedIn) {
                // --- LOGGED-IN USER ---
                if (guestLinks) guestLinks.style.display = 'none';
                if (userLinks) userLinks.style.display = 'flex';
                if (userNameDisplay) userNameDisplay.textContent = data.user.first_name;
                if (adminLink && data.user.role === 'admin') {
                    adminLink.style.display = 'block';
                }

                // Attach logout listener only for logged-in users
                if (logoutBtn) {
                    logoutBtn.addEventListener('click', () => {
                        fetch('api/logout.php').then(() => {
                            showToast('Logged out successfully.', 'success');
                            setTimeout(() => window.location.href = 'login.html', 1000);
                        });
                    });
                }

                // This logic only applies on index.html where these containers exist
                if (landingPageContainer && flashcardAppContainer && subscriptionPromptContainer) {
                    if (data.user.subscription_status === 'active') {
                        landingPageContainer.style.display = 'none';
                        subscriptionPromptContainer.style.display = 'none';
                        flashcardAppContainer.style.display = 'block';
                        // Dispatch event for flashcards.js to initialize
                        document.dispatchEvent(new CustomEvent('user-active', { detail: data.user }));
                    } else {
                        landingPageContainer.style.display = 'none';
                        flashcardAppContainer.style.display = 'none';
                        subscriptionPromptContainer.style.display = 'block';
                        // Dispatch event for flashcards.js to render PayPal button
                        document.dispatchEvent(new CustomEvent('user-inactive'));
                    }
                }

            } else {
                // --- LOGGED-OUT USER ---
                if (guestLinks) guestLinks.style.display = 'flex';
                if (userLinks) userLinks.style.display = 'none';

                if (landingPageContainer) {
                    landingPageContainer.style.display = 'block';
                    if (flashcardAppContainer) flashcardAppContainer.style.display = 'none';
                    if (subscriptionPromptContainer) subscriptionPromptContainer.style.display = 'none';
                }
            }
        })
        .catch(error => {
            console.error('Session check failed:', error);
            // Default to logged-out state on error
            if (guestLinks) guestLinks.style.display = 'flex';
            if (userLinks) userLinks.style.display = 'none';
            if (landingPageContainer) landingPageContainer.style.display = 'block';
        });
});
