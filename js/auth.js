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
async function registerUser(username, email, password) {
    try {
        const result = await apiRequest('api/register.php', { username, email, password });
        alert(result.message); // "User registered successfully."
        // Redirect to login page on successful registration
        window.location.href = 'login.html';
    } catch (error) {
        alert(`Registration Failed: ${error.message}`);
    }
}

// --- Login ---
async function loginUser(username, password) {
    try {
        const result = await apiRequest('api/login.php', { username, password });
        alert(result.message); // "Login successful."
        // Redirect to the main page on successful login
        window.location.href = 'index.html';
    } catch (error) {
        alert(`Login Failed: ${error.message}`);
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
