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
        setTimeout(() => { window.location.href = 'logged_in.html'; }, 1000);
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

        const restrictedPaths = [
            '/logged_in.html',
            '/oral_expression.html',
            '/practise/section_a/index.html',
            '/practise/section_b/index.html',
            '/training.html',
            '/admin.html'
        ];

        const currentPath = window.location.pathname;
        const isRestricted = restrictedPaths.some(path => currentPath.endsWith(path));

        if (data.loggedIn) {
            if (userStatusDiv) {
                userStatusDiv.style.display = 'flex';
            }
            if (firstNameDisplay) {
                firstNameDisplay.textContent = `Welcome, ${data.user.first_name}`;
            }
            if (data.user.role === 'admin' && adminLink) {
                adminLink.style.display = 'inline';
            }

            if (data.user.subscription_status !== 'active') {
                // Hide restricted links in nav
                const restrictedNavLinks = document.querySelectorAll('.main-nav .nav-links a, .main-nav .dropdown');
                restrictedNavLinks.forEach(link => {
                    const href = link.querySelector('a')?.getAttribute('href') || link.getAttribute('href');
                    if (href && restrictedPaths.some(rp => href.includes(rp.replace('.html', '')))) {
                        link.style.display = 'none';
                    }
                });

                // Redirect to landing page if on a restricted page
                if (isRestricted) {
                    window.location.href = 'index.html';
                    return;
                }
            } else {
                // User is active, check admin access
                if (currentPath.endsWith('admin.html') && data.user.role !== 'admin') {
                    window.location.href = 'logged_in.html';
                    return;
                }
            }

            // Show page content after all checks
            const pageContainer = document.getElementById('page-container') || document.getElementById('app-container') || document.getElementById('main-content');
            if (pageContainer) {
                pageContainer.style.display = 'block';
                if (pageContainer.tagName === 'DIV' && (currentPath.includes('practise') || currentPath.includes('training'))) {
                    // Specific display types for certain pages
                     if (currentPath.includes('practise')) {
                         pageContainer.style.display = 'flex';
                     }
                }
            }

        } else {
            // Redirect to login if not logged in and on restricted page
            const publicPaths = ['/login.html', '/register.html', '/index.html', '/', '/forgot_password.html', '/reset_password.html'];
            const isPublic = publicPaths.some(path => currentPath === path || currentPath.endsWith(path));

            if (!isPublic) {
                window.location.href = 'login.html';
            }
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
