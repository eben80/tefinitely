console.log('admin.js script started');

document.addEventListener('DOMContentLoaded', () => {
    console.log('DOMContentLoaded event fired');

    // --- DOM Elements ---
    console.log('Querying DOM elements...');
    const adminDashboard = document.getElementById('admin-dashboard');
    console.log('adminDashboard:', adminDashboard);
    const authErrorDiv = document.getElementById('auth-error');
    console.log('authErrorDiv:', authErrorDiv);
    const usersTableBody = document.getElementById('users-table-body');
    console.log('usersTableBody:', usersTableBody);
    const modal = document.getElementById('edit-user-modal');
    console.log('modal:', modal);
    const modalUsername = document.getElementById('modal-username');
    console.log('modalUsername:', modalUsername);
    const modalEmailInput = document.getElementById('modal-email');
    console.log('modalEmailInput:', modalEmailInput);
    const modalPasswordInput = document.getElementById('modal-password');
    console.log('modalPasswordInput:', modalPasswordInput);
    const modalSubStartInput = document.getElementById('modal-sub-start');
    console.log('modalSubStartInput:', modalSubStartInput);
    const modalSubEndInput = document.getElementById('modal-sub-end');
    console.log('modalSubEndInput:', modalSubEndInput);
    const editEmailForm = document.getElementById('edit-email-form');
    console.log('editEmailForm:', editEmailForm);
    const editPasswordForm = document.getElementById('edit-password-form');
    console.log('editPasswordForm:', editPasswordForm);
    const editSubscriptionForm = document.getElementById('edit-subscription-form');
    console.log('editSubscriptionForm:', editSubscriptionForm);
    const addUserBtn = document.getElementById('add-user-btn');
    console.log('addUserBtn:', addUserBtn);
    const addUserModal = document.getElementById('add-user-modal');
    console.log('addUserModal:', addUserModal);
    const addUserForm = document.getElementById('add-user-form');
    console.log('addUserForm:', addUserForm);
    const createUserBtn = document.getElementById('create-user-btn');
    console.log('createUserBtn:', createUserBtn);
    const closeBtns = document.querySelectorAll('.close-btn');
    console.log('closeBtns:', closeBtns);

    let currentEditingUserId = null;
    let usersData = []; // Cache user data

    // --- Initial Load ---
    console.log('Calling checkAdminAccess()');
    checkAdminAccess();

    // --- Event Listeners ---
    console.log('Attaching event listeners...');
    if (addUserBtn) {
        console.log('Attaching click listener to addUserBtn');
        addUserBtn.addEventListener('click', () => {
            console.log('Add User button clicked');
            if (addUserModal) {
                addUserModal.style.display = 'block';
                // Attach the event listener here, now that the modal is visible
                const createUserBtnInModal = document.getElementById('create-user-btn');
                console.log('createUserBtnInModal inside click handler:', createUserBtnInModal);
                if (createUserBtnInModal) {
                    console.log('Attaching click listener to createUserBtnInModal');
                    createUserBtnInModal.addEventListener('click', handleAddUser);
                    console.log('Click listener attached to createUserBtnInModal');
                } else {
                    console.error('createUserBtnInModal is null, cannot attach event listener');
                }
            }
        });
        console.log('Click listener attached to addUserBtn');
    } else {
        console.error('addUserBtn is null, cannot attach event listener');
    }

    if (closeBtns) {
        closeBtns.forEach(btn => {
            btn.addEventListener('click', (event) => {
                console.log('Close button clicked');
                const modal = event.target.closest('.modal');
                if (modal) {
                    modal.style.display = 'none';
                }
            });
        });
    }

    window.addEventListener('click', (event) => {
        if (event.target.classList.contains('modal')) {
            console.log('Clicked outside modal');
            event.target.style.display = 'none';
        }
    });

    document.addEventListener('submit', (event) => {
        console.log('Submit event detected on document for form:', event.target.id);
        if (event.target.id === 'edit-email-form') {
            handleEmailUpdate(event);
        } else if (event.target.id === 'edit-password-form') {
            handlePasswordUpdate(event);
        } else if (event.target.id === 'edit-subscription-form') {
            handleSubscriptionUpdate(event);
        }
    });
    console.log('Event listeners attached');

    // --- Functions ---
    async function checkAdminAccess() {
        console.log('checkAdminAccess function started');
        try {
            const response = await fetch('api/check_session.php');
            const data = await response.json();
            console.log('checkAdminAccess response:', data);
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
        console.log('loadUsers function started');
        try {
            const response = await fetch('api/admin/manage_users.php');
            const data = await response.json();
            console.log('loadUsers response:', data);
            if (response.ok && data.status === 'success') {
                usersData = data.users; // Cache the data
                populateTable(usersData);
            } else {
                showToast('Failed to load users: ' + (data.message || 'Unknown error'), 'error');
            }
        } catch (error) {
            console.error('Error loading users:', error);
            showToast('An error occurred while fetching user data.', 'error');
        }
    }

    function populateTable(users) {
        console.log('populateTable function started');
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
                    <button class="edit-user-btn" data-userid="${user.id}">Edit</button>
                    <button class="delete-user-btn" data-userid="${user.id}">Delete</button>
                </td>
            `;
            usersTableBody.appendChild(row);
        });

        // Add event listeners to the new buttons and selects
        console.log('Attaching event listeners to table buttons and selects');
        document.querySelectorAll('.status-select').forEach(select => {
            select.addEventListener('change', handleSubscriptionChange);
        });
        document.querySelectorAll('.edit-user-btn').forEach(button => {
            button.addEventListener('click', openEditModal);
        });
        document.querySelectorAll('.delete-user-btn').forEach(button => {
            button.addEventListener('click', handleDeleteUser);
        });
    }

    function openEditModal(event) {
        console.log('openEditModal function started');
        currentEditingUserId = event.target.dataset.userid;
        const user = usersData.find(u => u.id == currentEditingUserId);

        if (user) {
            modalUsername.textContent = user.username;
            modalEmailInput.value = user.email;
            modalPasswordInput.value = ''; // Clear password field
            modalSubStartInput.value = user.subscription_start_date || '';
            modalSubEndInput.value = user.subscription_end_date || '';
            modal.style.display = 'block';
        }
    }

    async function handleSubscriptionChange(event) {
        console.log('handleSubscriptionChange function started');
        const userId = event.target.dataset.userid;
        const newStatus = event.target.value;
        await updateUser('update_subscription', { user_id: userId, subscription_status: newStatus });
    }

    async function handleDeleteUser(event) {
        console.log('handleDeleteUser function started');
        const userId = event.target.dataset.userid;
        await updateUser('delete_user', { user_id: userId });
    }

    async function handleEmailUpdate(event) {
        console.log('handleEmailUpdate function started');
        event.preventDefault();
        const newEmail = modalEmailInput.value;
        await updateUser('update_email', { user_id: currentEditingUserId, email: newEmail });
    }

    async function handlePasswordUpdate(event) {
        console.log('handlePasswordUpdate function started');
        event.preventDefault();
        const newPassword = modalPasswordInput.value;
        if (newPassword && newPassword.length < 8) {
            showToast('Password must be at least 8 characters long.', 'error');
            return;
        }
        if (newPassword) { // Only update if a new password is provided
            await updateUser('update_password', { user_id: currentEditingUserId, password: newPassword });
        }
    }

    async function handleSubscriptionUpdate(event) {
        console.log('handleSubscriptionUpdate function started');
        event.preventDefault();
        const startDate = modalSubStartInput.value;
        const endDate = modalSubEndInput.value;
        await updateUser('update_subscription_dates', {
            user_id: currentEditingUserId,
            start_date: startDate,
            end_date: endDate
        });
    }

    async function handleAddUser(event) {
        console.log('handleAddUser function started');
        event.preventDefault();
        const username = document.getElementById('add-username').value;
        const email = document.getElementById('add-email').value;
        const password = document.getElementById('add-password').value;
        const role = document.getElementById('add-role').value;

        console.log('handleAddUser data:', { username, email, password, role });

        if (password.length < 8) {
            showToast('Password must be at least 8 characters long.', 'error');
            return;
        }

        const userData = { username, email, password, role };
        await addUser(userData);
    }

    async function addUser(data) {
        console.log('addUser function started with data:', data);
        const payload = { action: 'add_user', ...data };
        try {
            const response = await fetch('api/admin/manage_users.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            console.log('addUser response:', response);
            const result = await response.json();
            console.log('addUser result:', result);
            showToast(result.message, response.ok ? 'success' : 'error');
            if (response.ok && result.status === 'success') {
                addUserModal.style.display = 'none';
                addUserForm.reset();
                loadUsers();
            } else {
                console.error('Failed to add user:', result);
            }
        } catch (error) {
            console.error('Add user failed with error:', error);
            showToast('An error occurred while adding the user.', 'error');
        }
    }

    async function updateUser(action, data) {
        console.log('updateUser function started with action:', action, 'and data:', data);
        const payload = { action, ...data };
        if (!confirm(`Are you sure you want to perform this action?`)) {
            loadUsers();
            return;
        }
        try {
            const response = await fetch('api/admin/manage_users.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await response.json();
            showToast(result.message, response.ok ? 'success' : 'error');
            if (response.ok && result.status === 'success') {
                modal.style.display = 'none';
                loadUsers();
            }
        } catch (error) {
            console.error('Update user failed:', error);
            showToast('An error occurred while updating the user.', 'error');
        }
    }
});
