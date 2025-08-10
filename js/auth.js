// Function to handle API requests
async function apiRequest(url, data) {
    try {
        const urlSearchParams = new URLSearchParams(Object.entries(data));
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: urlSearchParams
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
        // Redirect to the main page on successful login
        setTimeout(() => { window.location.href = 'index.html'; }, 1000);
    } catch (error) {
        showToast(`Login Failed: ${error.message}`, 'error');
    }
}

// --- Event Listeners (will be attached in the HTML files) ---

// Example for registration form (to be placed in register.html)
/*
document.addEventListener('DOMContentLoaded', () => {
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const username = document.getElementById('username').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            registerUser(username, email, password);
        });
    }
});
*/

// Example for login form (to be placed in login.html)
/*
document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            loginUser(username, password);
        });
    }
});
*/
