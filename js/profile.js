document.addEventListener('DOMContentLoaded', () => {
    // --- DOM Elements ---
    const usernameSpan = document.getElementById('username');
    const emailSpan = document.getElementById('email');
    const subStatusSpan = document.getElementById('sub-status');
    const subEndDateSpan = document.getElementById('sub-end-date');
    const updateEmailForm = document.getElementById('updateEmailForm');
    const updatePasswordForm = document.getElementById('updatePasswordForm');
    const toggleEmailFormLink = document.getElementById('toggle-email-form');
    const togglePasswordFormLink = document.getElementById('toggle-password-form');
    const changeEmailSection = document.getElementById('change-email-section');
    const changePasswordSection = document.getElementById('change-password-section');

    // --- Load Profile Data on Page Load ---
    loadProfileData();

    async function loadProfileData() {
        try {
            const response = await fetch('api/profile/get_profile.php');
            const data = await response.json();

            if (response.ok && data.status === 'success') {
                const profile = data.profile;
                usernameSpan.textContent = profile.username;
                emailSpan.textContent = profile.email;
                subStatusSpan.textContent = profile.subscription_status;
                subEndDateSpan.textContent = profile.subscription_end_date ? new Date(profile.subscription_end_date).toLocaleDateString() : 'N/A';
            } else {
                // If not logged in or error, redirect
                showToast(data.message || 'You must be logged in to view this page.', 'error');
                setTimeout(() => { window.location.href = 'login.html'; }, 2000);
            }
        } catch (error) {
            console.error('Failed to load profile data:', error);
            showToast('An error occurred while fetching your profile.', 'error');
            setTimeout(() => { window.location.href = 'login.html'; }, 2000);
        }
    }

    // --- Handle Email Update ---
    updateEmailForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const newEmail = document.getElementById('new-email').value;

        try {
            const response = await fetch('api/profile/update_email.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: newEmail })
            });
            const result = await response.json();

            showToast(result.message, response.ok ? 'success' : 'error');
            if (response.ok && result.status === 'success') {
                loadProfileData(); // Refresh data on page
                updateEmailForm.reset();
            }
        } catch (error) {
            console.error('Email update failed:', error);
            showToast('An error occurred while updating your email.', 'error');
        }
    });

    // --- Handle Password Update ---
    updatePasswordForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const currentPassword = document.getElementById('current-password').value;
        const newPassword = document.getElementById('new-password').value;

        try {
            const response = await fetch('api/profile/update_password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    current_password: currentPassword,
                    new_password: newPassword
                })
            });
            const result = await response.json();
            if (response.ok) {
                showToast(result.message, 'success');
                updatePasswordForm.reset();
            } else {
                showToast(result.message || 'An unexpected error occurred. Please try again.', 'error');
            }
        } catch (error) {
            console.error('Password update failed:', error);
            showToast('An error occurred while updating your password.', 'error');
        }
    });

    // --- Toggle Form Visibility ---
    toggleEmailFormLink.addEventListener('click', (e) => {
        e.preventDefault();
        const isVisible = changeEmailSection.style.display === 'block';
        changeEmailSection.style.display = isVisible ? 'none' : 'block';
    });

    togglePasswordFormLink.addEventListener('click', (e) => {
        e.preventDefault();
        const isVisible = changePasswordSection.style.display === 'block';
        changePasswordSection.style.display = isVisible ? 'none' : 'block';
    });
});
