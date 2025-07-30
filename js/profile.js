document.addEventListener('DOMContentLoaded', () => {
    // --- DOM Elements ---
    const usernameSpan = document.getElementById('username');
    const emailSpan = document.getElementById('email');
    const subStatusSpan = document.getElementById('sub-status');
    const subEndDateSpan = document.getElementById('sub-end-date');
    const updateEmailForm = document.getElementById('updateEmailForm');
    const updatePasswordForm = document.getElementById('updatePasswordForm');

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
                alert(data.message || 'You must be logged in to view this page.');
                window.location.href = 'login.html';
            }
        } catch (error) {
            console.error('Failed to load profile data:', error);
            alert('An error occurred while fetching your profile.');
            window.location.href = 'login.html';
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

            alert(result.message);
            if (response.ok && result.status === 'success') {
                loadProfileData(); // Refresh data on page
                updateEmailForm.reset();
            }
        } catch (error) {
            console.error('Email update failed:', error);
            alert('An error occurred while updating your email.');
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

            alert(result.message);
            if (response.ok && result.status === 'success') {
                updatePasswordForm.reset();
            }
        } catch (error) {
            console.error('Password update failed:', error);
            alert('An error occurred while updating your password.');
        }
    });
});
