document.addEventListener('DOMContentLoaded', () => {
    // --- DOM Elements ---
    const adminDashboard = document.getElementById('admin-dashboard');
    const authErrorDiv = document.getElementById('auth-error');
    const usersTableBody = document.getElementById('users-table-body');
    const modal = document.getElementById('edit-user-modal');
    const modalUsername = document.getElementById('modal-username');
    const modalEmailInput = document.getElementById('modal-email');
    const modalPasswordInput = document.getElementById('modal-password');
    const editEmailForm = document.getElementById('edit-email-form');
    const editPasswordForm = document.getElementById('edit-password-form');
    const closeBtn = document.querySelector('.close-btn');

    let currentEditingUserId = null;

    // --- Initial Load ---
    checkAdminAccess();

    // --- Event Listeners ---
    closeBtn.addEventListener('click', () => modal.style.display = 'none');
    window.addEventListener('click', (event) => {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    });

    editEmailForm.addEventListener('submit', handleEmailUpdate);
    editPasswordForm.addEventListener('submit', handlePasswordUpdate);

    // --- Functions ---
    async function checkAdminAccess() {
        try {
            const response = await fetch('api/check_session.php');
            const data = await response.json();
            if (response.ok && data.loggedIn && data.user.role === 'admin') {
                adminDashboard.style.display = 'block';
                loadUsers();
            } else {
                authErrorDiv.style.display = 'block';
            }
        } catch (error) {
            console.error('Session check failed:', error);
            authErrorDiv.style.display = 'block';
        }
    }

    async function loadUsers() {
        try {
            const response = await fetch('api/admin/manage_users.php');
            const data = await response.json();
            if (response.ok && data.status === 'success') {
                populateTable(data.users);
            } else {
                alert('Failed to load users: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error loading users:', error);
            alert('An error occurred while fetching user data.');
        }
    }

    function populateTable(users) {
        usersTableBody.innerHTML = ''; // Clear existing rows
        users.forEach(user => {
            const row = document.createElement('tr');
            const statusClass = user.subscription_status === 'active' ? 'status-active' : 'status-inactive';
            const endDate = user.subscription_end_date ? new Date(user.subscription_end_date).toLocaleDateString() : 'N/A';

            row.innerHTML = `
                <td>${user.id}</td>
                <td>${user.username}</td>
                <td>${user.email}</td>
                <td>${user.role}</td>
                <td class="${statusClass}">${user.subscription_status}</td>
                <td>${endDate}</td>
                <td>${new Date(user.created_at).toLocaleDateString()}</td>
                <td>
                    <select data-userid="${user.id}" class="status-select">
                        <option value="active" ${user.subscription_status === 'active' ? 'selected' : ''}>Active</option>
                        <option value="inactive" ${user.subscription_status === 'inactive' ? 'selected' : ''}>Inactive</option>
                    </select>
                </td>
                <td>
                    <button class="edit-user-btn" data-userid="${user.id}" data-username="${user.username}" data-email="${user.email}">Edit User</button>
                </td>
            `;
            usersTableBody.appendChild(row);
        });

        // Add event listeners to the new buttons and selects
        document.querySelectorAll('.status-select').forEach(select => {
            select.addEventListener('change', handleSubscriptionChange);
        });
        document.querySelectorAll('.edit-user-btn').forEach(button => {
            button.addEventListener('click', openEditModal);
        });
    }

    function openEditModal(event) {
        currentEditingUserId = event.target.dataset.userid;
        const username = event.target.dataset.username;
        const email = event.target.dataset.email;

        modalUsername.textContent = username;
        modalEmailInput.value = email;
        modalPasswordInput.value = ''; // Clear password field
        modal.style.display = 'block';
    }

    async function handleSubscriptionChange(event) {
        const userId = event.target.dataset.userid;
        const newStatus = event.target.value;
        await updateUser('update_subscription', { user_id: userId, subscription_status: newStatus });
    }

    async function handleEmailUpdate(event) {
        event.preventDefault();
        const newEmail = modalEmailInput.value;
        await updateUser('update_email', { user_id: currentEditingUserId, email: newEmail });
    }

    async function handlePasswordUpdate(event) {
        event.preventDefault();
        const newPassword = modalPasswordInput.value;
        if (!newPassword || newPassword.length < 8) {
            alert('Password must be at least 8 characters long.');
            return;
        }
        await updateUser('update_password', { user_id: currentEditingUserId, password: newPassword });
    }

    async function updateUser(action, data) {
        const payload = { action, ...data };
        if (!confirm(`Are you sure you want to perform this action?`)) {
            loadUsers(); // a bit heavy-handed, but resets the UI state
            return;
        }
        try {
            const response = await fetch('api/admin/manage_users.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await response.json();
            alert(result.message);
            if (response.ok && result.status === 'success') {
                modal.style.display = 'none'; // Close modal on success
                loadUsers(); // Refresh table
            }
        } catch (error) {
            console.error('Update user failed:', error);
            alert('An error occurred while updating the user.');
        }
    }
});
