document.addEventListener('DOMContentLoaded', () => {
    // --- Responsive Nav Toggle ---
    const nav = document.getElementById('main-nav');
    const navToggle = document.querySelector('.nav-toggle');
    if (navToggle) {
        navToggle.addEventListener('click', () => {
            if(nav) nav.classList.toggle('is-active');
        });
    }

    // --- DOM Elements ---
    const adminDashboard = document.getElementById('admin-dashboard');
    const authErrorDiv = document.getElementById('auth-error');
    const usersTableBody = document.getElementById('users-table-body');
    const modal = document.getElementById('edit-user-modal');
    const modalEmailInput = document.getElementById('modal-email');
    const modalPasswordInput = document.getElementById('modal-password');
    const modalSubStartInput = document.getElementById('modal-sub-start');
    const modalSubEndInput = document.getElementById('modal-sub-end');
    const addUserBtn = document.getElementById('add-user-btn');
    const addUserModal = document.getElementById('add-user-modal');
    const addUserForm = document.getElementById('add-user-form');
    const closeBtns = document.querySelectorAll('.close-btn');

    // Email Broadcaster elements
    const emailRecipientGroup = document.getElementById('email-recipient-group');
    const manualEmailListContainer = document.getElementById('manual-email-list-container');
    const emailBroadcasterForm = document.getElementById('email-broadcaster-form');

    let currentEditingUserId = null;
    let usersData = []; // Cache user data

    // --- Initial Load ---
    checkAdminAccess();

    // --- Event Listeners ---
    if (addUserBtn) {
        addUserBtn.addEventListener('click', () => {
            if (addUserModal) {
                addUserModal.style.display = 'block';
            }
        });
    }

    const createUserBtn = document.getElementById('create-user-btn');
    if (createUserBtn) {
        createUserBtn.addEventListener('click', handleAddUser);
    }

    if (closeBtns) {
        closeBtns.forEach(btn => {
            btn.addEventListener('click', (event) => {
                const modal = event.target.closest('.modal');
                if (modal) {
                    modal.style.display = 'none';
                }
            });
        });
    }

    window.addEventListener('click', (event) => {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    });

    document.addEventListener('submit', (event) => {
        if (event.target.id === 'edit-email-form') {
            handleEmailUpdate(event);
        } else if (event.target.id === 'edit-password-form') {
            handlePasswordUpdate(event);
        } else if (event.target.id === 'edit-subscription-form') {
            handleSubscriptionUpdate(event);
        } else if (event.target.id === 'email-broadcaster-form') {
            handleSendBroadcastEmail(event);
        }
    });

    if (emailRecipientGroup) {
        emailRecipientGroup.addEventListener('change', () => {
            if (emailRecipientGroup.value === 'manual') {
                manualEmailListContainer.style.display = 'block';
            } else {
                manualEmailListContainer.style.display = 'none';
            }
        });
    }


    // --- Functions ---
    async function checkAdminAccess() {
        const userLinks = document.getElementById('user-links');
        const guestLinks = document.getElementById('guest-links');
        const userNameDisplay = document.getElementById('user-name-display');
        const logoutBtn = document.getElementById('logoutBtn');
        const adminLink = document.getElementById('admin-link');

        try {
            const response = await fetch('api/check_session.php');
            const data = await response.json();

            if (data.loggedIn && data.user.role === 'admin') {
                if(userLinks) userLinks.style.display = 'flex';
                if(guestLinks) guestLinks.style.display = 'none';
                if(userNameDisplay) userNameDisplay.textContent = data.user.first_name;
                if(adminLink) adminLink.style.display = 'block';

                if(logoutBtn) {
                    logoutBtn.addEventListener('click', () => {
                        fetch('api/logout.php').then(() => {
                            window.location.href = 'login.html';
                        });
                    });
                }

                adminDashboard.style.display = 'block';
                authErrorDiv.style.display = 'none';
                loadUsers();
            } else if (data.loggedIn) {
                window.location.href = 'profile.html';
            } else {
                window.location.href = 'login.html';
            }
        } catch (error) {
            console.error('Session check failed:', error);
            window.location.href = 'login.html';
        }
    }

    async function loadUsers() {
        try {
            const response = await fetch('api/admin/manage_users.php');
            const data = await response.json();
            if (response.ok && data.status === 'success') {
                usersData = data.users;
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
        usersTableBody.innerHTML = '';
        users.forEach(user => {
            const row = document.createElement('tr');
            const statusClass = user.subscription_status === 'active' ? 'status-active' : 'status-inactive';
            const endDate = user.subscription_end_date ? new Date(user.subscription_end_date).toLocaleDateString() : 'N/A';

            row.innerHTML = `
                <td>${user.id}</td>
                <td>${user.first_name}</td>
                <td>${user.last_name}</td>
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
        currentEditingUserId = event.target.dataset.userid;
        const user = usersData.find(u => u.id == currentEditingUserId);

        if (user) {
            document.getElementById('modal-user-name').textContent = `${user.first_name} ${user.last_name}`;
            modalEmailInput.value = user.email;
            modalPasswordInput.value = '';
            modalSubStartInput.value = user.subscription_start_date || '';
            modalSubEndInput.value = user.subscription_end_date || '';
            modal.style.display = 'block';
        }
    }

    async function handleSubscriptionChange(event) {
        const userId = event.target.dataset.userid;
        const newStatus = event.target.value;
        await updateUser('update_subscription', { user_id: userId, subscription_status: newStatus });
    }

    async function handleDeleteUser(event) {
        const userId = event.target.dataset.userid;
        if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
            await updateUser('delete_user', { user_id: userId });
        }
    }

    async function handleEmailUpdate(event) {
        event.preventDefault();
        const newEmail = modalEmailInput.value;
        await updateUser('update_email', { user_id: currentEditingUserId, email: newEmail });
    }

    async function handlePasswordUpdate(event) {
        event.preventDefault();
        const newPassword = modalPasswordInput.value;
        if (newPassword && newPassword.length < 8) {
            showToast('Password must be at least 8 characters long.', 'error');
            return;
        }
        if (newPassword) {
            await updateUser('update_password', { user_id: currentEditingUserId, password: newPassword });
        }
    }

    async function handleSubscriptionUpdate(event) {
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
        event.preventDefault();
        const first_name = document.getElementById('add-first-name').value;
        const last_name = document.getElementById('add-last-name').value;
        const email = document.getElementById('add-email').value;
        const password = document.getElementById('add-password').value;
        const role = document.getElementById('add-role').value;

        if (password.length < 8) {
            showToast('Password must be at least 8 characters long.', 'error');
            return;
        }

        const userData = { first_name, last_name, email, password, role };
        await addUser(userData);
    }

    async function handleSendBroadcastEmail(event) {
        event.preventDefault();
        const recipientGroup = document.getElementById('email-recipient-group').value;
        const manualEmailList = document.getElementById('manual-email-list').value;
        const subject = document.getElementById('email-subject').value;
        const body = document.getElementById('email-body').value;
        const sendBtn = document.getElementById('send-email-btn');

        if (!subject || !body) {
            showToast('Subject and body are required.', 'error');
            return;
        }
        if (recipientGroup === 'manual' && !manualEmailList) {
            showToast('Manual email list cannot be empty.', 'error');
            return;
        }

        sendBtn.disabled = true;
        sendBtn.textContent = 'Sending...';

        try {
            const response = await fetch('api/admin/send_email.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    recipient_group: recipientGroup,
                    manual_emails: manualEmailList,
                    subject: subject,
                    body: body
                })
            });
            const result = await response.json();
            if (response.ok) {
                showToast(result.message, 'success');
                emailBroadcasterForm.reset();
                if(manualEmailListContainer) manualEmailListContainer.style.display = 'none';
            } else {
                showToast(result.message || 'Failed to send emails.', 'error');
            }
        } catch (error) {
            console.error('Error sending email broadcast:', error);
            showToast('An unexpected error occurred.', 'error');
        } finally {
            sendBtn.disabled = false;
            sendBtn.textContent = 'Send Email';
        }
    }

    async function addUser(data) {
        const payload = { action: 'add_user', ...data };
        try {
            const response = await fetch('api/admin/manage_users.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await response.json();
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
        const payload = { action, ...data };

        try {
            const response = await fetch('api/admin/manage_users.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await response.json();
            showToast(result.message, response.ok ? 'success' : 'error');
            if (response.ok && result.status === 'success') {
                if(modal) modal.style.display = 'none';
                loadUsers();
            }
        } catch (error) {
            console.error('Update user failed:', error);
            showToast('An error occurred while updating the user.', 'error');
        }
    }
});
