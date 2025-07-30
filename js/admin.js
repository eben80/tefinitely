document.addEventListener('DOMContentLoaded', () => {
    const adminDashboard = document.getElementById('admin-dashboard');
    const authErrorDiv = document.getElementById('auth-error');
    const usersTableBody = document.getElementById('users-table-body');

    // Check session and role first
    checkAdminAccess();

    async function checkAdminAccess() {
        try {
            const response = await fetch('../api/check_session.php');
            const data = await response.json();

            if (response.ok && data.loggedIn && data.user.role === 'admin') {
                // User is an admin, show dashboard and load users
                adminDashboard.style.display = 'block';
                loadUsers();
            } else {
                // Not an admin or not logged in
                authErrorDiv.style.display = 'block';
            }
        } catch (error) {
            console.error('Session check failed:', error);
            authErrorDiv.style.display = 'block';
        }
    }

    // Fetch users from the admin API
    async function loadUsers() {
        try {
            const response = await fetch('../api/admin/manage_users.php');
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

    // Populate the table with user data
    function populateTable(users) {
        usersTableBody.innerHTML = ''; // Clear existing rows

        users.forEach(user => {
            const row = document.createElement('tr');

            const statusClass = user.subscription_status === 'active' ? 'status-active' : 'status-inactive';

            row.innerHTML = `
                <td>${user.id}</td>
                <td>${user.username}</td>
                <td>${user.email}</td>
                <td>${user.role}</td>
                <td class="${statusClass}">${user.subscription_status}</td>
                <td>${user.created_at}</td>
                <td>
                    <select data-userid="${user.id}" class="status-select">
                        <option value="active" ${user.subscription_status === 'active' ? 'selected' : ''}>Active</option>
                        <option value="inactive" ${user.subscription_status === 'inactive' ? 'selected' : ''}>Inactive</option>
                    </select>
                </td>
            `;
            usersTableBody.appendChild(row);
        });

        // Add event listeners to the new select elements
        document.querySelectorAll('.status-select').forEach(select => {
            select.addEventListener('change', handleStatusChange);
        });
    }

    // Handle the change of subscription status
    async function handleStatusChange(event) {
        const selectElement = event.target;
        const userId = selectElement.dataset.userid;
        const newStatus = selectElement.value;

        if (!confirm(`Are you sure you want to change user ${userId}'s status to ${newStatus}?`)) {
            // Reload users to reset the dropdown if the admin cancels
            loadUsers();
            return;
        }

        try {
            const response = await fetch('../api/admin/manage_users.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    user_id: userId,
                    subscription_status: newStatus
                })
            });

            const data = await response.json();

            if (response.ok && data.status === 'success') {
                alert(data.message);
                // Reload the users table to reflect the change
                loadUsers();
            } else {
                alert('Update failed: ' + (data.message || 'Unknown error'));
                loadUsers(); // Reload to reset dropdown
            }
        } catch (error) {
            console.error('Error updating status:', error);
            alert('An error occurred while updating the user status.');
            loadUsers(); // Reload to reset dropdown
        }
    }
});
