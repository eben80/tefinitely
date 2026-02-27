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
        showToast(result.message, 'success');
        // Redirect to login page on successful registration
        setTimeout(() => { window.location.href = 'login.html'; }, 1000);
    } catch (error) {
        showToast(`Registration Failed: ${error.message}`, 'error');
    }
}

// --- Login ---
async function loginUser(email, password) {
    try {
        const result = await apiRequest('api/login.php', { email, password });
        showToast(result.message, 'success');
        // Redirect to the logged-in dashboard on successful login
        setTimeout(() => { window.location.href = 'logged_in.php'; }, 1000);
    } catch (error) {
        showToast(`Login Failed: ${error.message}`, 'error');
    }
}

// --- Session Check ---
async function checkSession() {
    try {
        const response = await fetch('api/check_session.php');
        if (!response.ok) {
            throw new Error('Session check failed with status ' + response.status);
        }
        const data = await response.json();
        const userStatusDiv = document.getElementById('user-status');
        const firstNameDisplay = document.getElementById('first-name-display');
        const adminLink = document.getElementById('admin-link');

        const landingNav = document.getElementById('landing-nav');
        const loginPrompt = document.getElementById('login-prompt');
        const subscriptionPrompt = document.getElementById('subscription-prompt');
        const authContainer = document.getElementById('auth-container');

        if (data.loggedIn) {
            if (userStatusDiv) {
                userStatusDiv.style.display = 'flex';
            }
            if (landingNav) {
                landingNav.style.display = 'none';
            }
            if (firstNameDisplay) {
                firstNameDisplay.textContent = `Welcome, ${data.user.first_name}`;
            }
            if (data.user.role === 'admin' && adminLink) {
                adminLink.style.display = 'inline';
            }

            if (data.user.subscription_status !== 'active') {
                // Handle restricted links for inactive users
                const restrictedPaths = ['logged_in.php', 'oral_expression.php', 'oral_expression_section_a.php', 'practise/section_a/index.php', 'practise/section_b/index.php', 'training.php', 'admin.php'];

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
                    if (loginPrompt) loginPrompt.style.display = 'none';
                    if (subscriptionPrompt) {
                        subscriptionPrompt.style.display = 'block';
                        if (typeof renderPayPalSubscriptionButton === 'function') {
                            renderPayPalSubscriptionButton();
                        }
                    }

                    // Check for trigger in URL
                    if (window.location.search.includes('trigger=subscribe')) {
                        setTimeout(() => {
                            subscriptionPrompt.scrollIntoView({ behavior: 'smooth' });
                        }, 500);
                    }
                }
            } else {
                // Active subscriber on index page should be redirected to dashboard
                if (window.location.pathname.endsWith('index.html') || window.location.pathname === '/' || window.location.pathname === '') {
                    window.location.href = 'logged_in.php';
                }
            }
        } else {
            // Not logged in
            if (userStatusDiv) userStatusDiv.style.display = 'none';
            if (landingNav) landingNav.style.display = 'flex';
            if (authContainer) authContainer.style.display = 'block';
            if (loginPrompt) loginPrompt.style.display = 'block';
            if (subscriptionPrompt) subscriptionPrompt.style.display = 'none';
        }
    } catch (error) {
        console.error('Session check failed:', error);
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
