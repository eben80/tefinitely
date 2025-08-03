document.addEventListener('DOMContentLoaded', () => {
    // --- DOM Elements ---
    const firstNameSpan = document.getElementById('first-name');
    const lastNameSpan = document.getElementById('last-name');
    const emailSpan = document.getElementById('email');
    const subStatusSpan = document.getElementById('sub-status');
    const subEndDateSpan = document.getElementById('sub-end-date');
    const updateDetailsForm = document.getElementById('updateDetailsForm');
    const updatePasswordForm = document.getElementById('updatePasswordForm');
    const toggleDetailsFormLink = document.getElementById('toggle-details-form');
    const togglePasswordFormLink = document.getElementById('toggle-password-form');
    const updateDetailsSection = document.getElementById('update-details-section');
    const changePasswordSection = document.getElementById('change-password-section');

    // --- Load Profile Data on Page Load ---
    loadProfileData();

    async function loadProfileData() {
        try {
            const response = await fetch('api/profile/get_profile.php');
            const data = await response.json();

            if (response.ok && data.status === 'success') {
                const profile = data.profile;
                firstNameSpan.textContent = profile.first_name;
                lastNameSpan.textContent = profile.last_name;
                emailSpan.textContent = profile.email;
                document.getElementById('new-first-name').value = profile.first_name;
                document.getElementById('new-last-name').value = profile.last_name;
                document.getElementById('new-email').value = profile.email;
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

    // --- Handle Details Update ---
    updateDetailsForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const newFirstName = document.getElementById('new-first-name').value;
        const newLastName = document.getElementById('new-last-name').value;
        const newEmail = document.getElementById('new-email').value;

        try {
            // We can do this in two separate requests, or create a new endpoint.
            // For simplicity, we'll use the existing endpoints.
            const nameUpdateResponse = await fetch('api/profile/update_user_details.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ first_name: newFirstName, last_name: newLastName })
            });
            const nameUpdateResult = await nameUpdateResponse.json();
            if (!nameUpdateResponse.ok) throw new Error(nameUpdateResult.message);

            const emailUpdateResponse = await fetch('api/profile/update_email.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: newEmail })
            });
            const emailUpdateResult = await emailUpdateResponse.json();
            if (!emailUpdateResponse.ok) throw new Error(emailUpdateResult.message);

            showToast('Details updated successfully!', 'success');
            loadProfileData();
            updateDetailsSection.style.display = 'none';

        } catch (error) {
            console.error('Details update failed:', error);
            showToast(`Failed to update details: ${error.message}`, 'error');
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
    toggleDetailsFormLink.addEventListener('click', (e) => {
        e.preventDefault();
        const isVisible = updateDetailsSection.style.display === 'block';
        updateDetailsSection.style.display = isVisible ? 'none' : 'block';
    });

    togglePasswordFormLink.addEventListener('click', (e) => {
        e.preventDefault();
        const isVisible = changePasswordSection.style.display === 'block';
        changePasswordSection.style.display = isVisible ? 'none' : 'block';
    });
});
