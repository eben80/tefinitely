// Function to handle API requests
async function apiRequest(url, data) {
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (!response.ok) {
            // Throw an error with the message from the backend
            throw new Error(result.message || `HTTP error! status: ${response.status}`);
        }

        return result;
    } catch (error) {
        console.error('API Request Error:', error);
        throw error; // Re-throw the error to be caught by the caller
    }
}

// --- Registration ---
async function registerUser(first_name, last_name, email, password) {
    try {
        const result = await apiRequest('api/register.php', { first_name, last_name, email, password });
        showToast(result.message, 'success', 5000);
        // Redirect to login page on successful registration
        setTimeout(() => {
            const urlParams = new URLSearchParams(window.location.search);
            const redirect = urlParams.get('registered') === 'true' ? '' : '?registered=true';
            window.location.href = 'login.html' + redirect;
        }, 5000);
    } catch (error) {
        showToast(`Registration Failed: ${error.message}`, 'error');
    }
}

// --- Login ---
async function loginUser(email, password, remember = false) {
    try {
        const result = await apiRequest('api/login.php', { email, password, remember });
        showToast(result.message, 'success');
        // Redirect to the logged-in dashboard on successful login
        setTimeout(() => { window.location.href = 'logged_in.php'; }, 1000);
    } catch (error) {
        showToast(`Login Failed: ${error.message}`, 'error');
    }
}

// --- Session Check ---
async function checkSession() {
    let data = { loggedIn: false };
    try {
        const response = await fetch('api/check_session.php');
        if (response.ok) {
            data = await response.json();
        } else if (response.status === 401) {
            // Specifically handle 401 Unauthorized as "not logged in"
            data = { loggedIn: false };
        } else {
            console.warn('Session check failed with status ' + response.status + '. Defaulting to not logged in.');
        }
    } catch (error) {
        console.error('Session check failed:', error);
    }

    try {
        const userStatusElements = document.querySelectorAll('.main-nav, #user-status');
        const firstNameDisplay = document.getElementById('first-name-display');
        const adminLink = document.getElementById('admin-link');

        const landingNav = document.getElementById('landing-nav');
        const loginPromptLandingPageElements = document.querySelectorAll('#login-prompt, .landing-page');
        const subscriptionPrompt = document.getElementById('subscription-prompt');
        const landingFooter = document.getElementById('landing-footer');
        const authContainer = document.getElementById('auth-container');

        // Always show main nav
        userStatusElements.forEach(el => el.style.display = 'flex');
        if (landingNav) {
            landingNav.style.display = 'none';
        }

        if (data.loggedIn) {
            if (firstNameDisplay) {
                firstNameDisplay.textContent = `Welcome, ${data.user.first_name}`;
                firstNameDisplay.style.display = 'inline';
            }
            if (data.user.role === 'admin' && adminLink) {
                adminLink.style.display = 'inline';
            }
            const logoutBtn = document.getElementById('logoutBtn');
            if (logoutBtn) logoutBtn.style.display = 'inline-block';

            const navLoginBtn = document.getElementById('nav-login-btn');
            if (navLoginBtn) navLoginBtn.style.display = 'none';

            // Always show landing page content on index.html even if logged in
            if (window.location.pathname.endsWith('index.html') || window.location.pathname === '/' || window.location.pathname === '') {
                if (authContainer) authContainer.style.display = 'block';
                if (landingFooter) landingFooter.style.display = 'block';
                if (data.user.subscription_status === 'active') {
                    loginPromptLandingPageElements.forEach(el => el.style.display = 'block');
                    if (subscriptionPrompt) subscriptionPrompt.style.display = 'none';
                }
            }

            if (data.user.subscription_status !== 'active') {
                // Handle restricted links for inactive users
                const restrictedPaths = ['logged_in.php', 'oral_expression.php', 'oral_expression_section_a.php', 'practise/tef_canada/section_a/index.php', 'practise/tef_canada/section_b/index.php', 'practise/celpip/section_a/index.php', 'practise/celpip/section_b/index.php', 'practise/french_level_test/index.php', 'training.php', 'admin.php'];

                // Select all links that might be restricted (nav and footer)
                const allLinks = document.querySelectorAll('a');

                allLinks.forEach(link => {
                    const href = link.getAttribute('href');
                    if (href && restrictedPaths.some(rp => href.includes(rp))) {
                        link.addEventListener('click', (e) => {
                            e.preventDefault();
                            if (window.location.pathname.endsWith('index.html') || window.location.pathname === '/' || window.location.pathname === '') {
                                if (subscriptionPrompt) {
                                    subscriptionPrompt.scrollIntoView({ behavior: 'smooth' });
                                    subscriptionPrompt.style.display = 'block'; // Ensure it's visible
                                    showToast('Please subscribe to access this feature.', 'info');
                                }
                            } else {
                                window.location.href = 'index.html?trigger=subscribe';
                            }
                        });
                    }
                });

                // On index page, if logged in but inactive, show subscription prompt
                if (window.location.pathname.endsWith('index.html') || window.location.pathname === '/' || window.location.pathname === '') {
                    if (authContainer) authContainer.style.display = 'block';
                    loginPromptLandingPageElements.forEach(el => el.style.display = 'none');
                    if (subscriptionPrompt) {
                        subscriptionPrompt.style.display = 'block';
                        if (typeof renderPayPalButtons === 'function') {
                            try {
                                renderPayPalButtons();
                            } catch (e) {
                                console.error('Error rendering PayPal buttons:', e);
                            }
                        }
                    }
                    if (landingFooter) landingFooter.style.display = 'block';

                    // Check for trigger in URL
                    if (window.location.search.includes('trigger=subscribe')) {
                        setTimeout(() => {
                            subscriptionPrompt.scrollIntoView({ behavior: 'smooth' });
                        }, 500);
                    }
                }
            }
        } else {
            // Not logged in
            if (firstNameDisplay) firstNameDisplay.style.display = 'none';
            if (adminLink) adminLink.style.display = 'none';

            const logoutBtn = document.getElementById('logoutBtn');
            if (logoutBtn) logoutBtn.style.display = 'none';

            const navLoginBtn = document.getElementById('nav-login-btn');
            if (navLoginBtn) navLoginBtn.style.display = 'inline-block';

            if (authContainer) authContainer.style.display = 'block';
            loginPromptLandingPageElements.forEach(el => el.style.display = 'block');
            if (subscriptionPrompt) subscriptionPrompt.style.display = 'none';
            if (landingFooter) landingFooter.style.display = 'block';

            // Handle restricted links for guests
            const restrictedPaths = ['logged_in.php', 'oral_expression.php', 'oral_expression_section_a.php', 'practise/tef_canada/section_a/index.php', 'practise/tef_canada/section_b/index.php', 'practise/celpip/section_a/index.php', 'practise/celpip/section_b/index.php', 'practise/french_level_test/index.php', 'training.php', 'admin.php', 'profile.php'];

            const allLinks = document.querySelectorAll('a');
            allLinks.forEach(link => {
                const href = link.getAttribute('href');
                if (href && restrictedPaths.some(rp => href.includes(rp))) {
                    link.addEventListener('click', (e) => {
                        e.preventDefault();
                        showToast('Please log in to access this feature.', 'info');
                    });
                }
            });
        }
    } catch (uiError) {
        console.error('Error updating UI during session check:', uiError);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // Attach event listener for logout button
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', async () => {
            const response = await fetch('api/logout.php');
            if (response.ok) {
                showToast('You have been logged out.', 'success');
                setTimeout(() => { window.location.href = 'login.html'; }, 1000);
            } else {
                showToast('Logout failed. Please try again.', 'error');
            }
        });
    }

    // Run session check on all pages that include this script
    checkSession();
});
