document.addEventListener('DOMContentLoaded', () => {
    // --- DOM Elements ---
    const adminDashboard = document.getElementById('admin-dashboard');
    const authErrorDiv = document.getElementById('auth-error');
    const usersTableBody = document.getElementById('users-table-body');
    const modal = document.getElementById('edit-user-modal');
    const modalUsername = document.getElementById('modal-username');
    const modalEmailInput = document.getElementById('modal-email');
    const modalPasswordInput = document.getElementById('modal-password');
    const modalSubStartInput = document.getElementById('modal-sub-start');
    const modalSubEndInput = document.getElementById('modal-sub-end');
    const editEmailForm = document.getElementById('edit-email-form');
    const editPasswordForm = document.getElementById('edit-password-form');
    const editSubscriptionForm = document.getElementById('edit-subscription-form');
    const addUserBtn = document.getElementById('add-user-btn');
    const addUserModal = document.getElementById('add-user-modal');
    const addUserForm = document.getElementById('add-user-form');
    const closeBtns = document.querySelectorAll('.close-btn');

    const openaiCallsModal = document.getElementById('openai-calls-modal');
    const openaiCallsTableBody = document.getElementById('openai-calls-table-body');
    const modalOpenaiUserName = document.getElementById('modal-openai-user-name');
    const openaiTimeframeSelect = document.getElementById('openai-timeframe');
    const exportCsvBtn = document.getElementById('export-csv-btn');

    // Filtering Elements
    const searchInput = document.getElementById('search-input');
    const filterRole = document.getElementById('filter-role');
    const filterStatus = document.getElementById('filter-status');
    const resetFiltersBtn = document.getElementById('reset-filters-btn');

    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    const auditLogsTableBody = document.getElementById('audit-logs-table-body');

    const sendEmailModal = document.getElementById('send-email-modal');
    const sendEmailForm = document.getElementById('send-email-form');
    const emailModalUserName = document.getElementById('email-modal-user-name');
    const emailUserIdInput = document.getElementById('email-user-id');
    const emailSubjectInput = document.getElementById('email-subject');
    const emailMessageInput = document.getElementById('email-message');

    let currentEditingUserId = null;
    let currentViewingCallsUserId = null;
    let usersData = []; // Cache user data
    let currentCallsData = []; // Cache current calls data for export

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
        }
    });

    if (openaiTimeframeSelect) {
        openaiTimeframeSelect.addEventListener('change', () => {
            if (currentViewingCallsUserId) {
                fetchAndPopulateCalls(currentViewingCallsUserId, openaiTimeframeSelect.value);
            }
        });
    }

    if (exportCsvBtn) {
        exportCsvBtn.addEventListener('click', exportCallsToCsv);
    }

    if (searchInput) {
        searchInput.addEventListener('input', applyFilters);
    }
    if (filterRole) {
        filterRole.addEventListener('change', applyFilters);
    }
    if (filterStatus) {
        filterStatus.addEventListener('change', applyFilters);
    }
    if (resetFiltersBtn) {
        resetFiltersBtn.addEventListener('click', () => {
            searchInput.value = '';
            filterRole.value = 'all';
            filterStatus.value = 'all';
            applyFilters();
        });
    }

    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const targetTab = btn.dataset.tab;

            // Update button UI
            tabBtns.forEach(b => {
                b.classList.remove('active');
                b.style.borderBottom = 'none';
            });
            btn.classList.add('active');
            btn.style.borderBottom = '2px solid #007bff';

            // Show target content
            tabContents.forEach(content => {
                if (content.id === `${targetTab}-tab`) {
                    content.style.display = 'block';
                } else {
                    content.style.display = 'none';
                }
            });

            if (targetTab === 'audit-logs') {
                loadAuditLogs();
            }
        });
    });

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
                usersData = data.users; // Cache the data
                applyFilters(); // Apply current filters to the new data
                updateStats(usersData);
            } else {
                showToast('Failed to load users: ' + (data.message || 'Unknown error'), 'error');
            }
        } catch (error) {
            console.error('Error loading users:', error);
            showToast('An error occurred while fetching user data.', 'error');
        }
    }

    function updateStats(users) {
        let total24h = 0;
        let total7d = 0;
        let totalLife = 0;

        users.forEach(user => {
            total24h += parseInt(user.calls_24h || 0);
            total7d += parseInt(user.calls_7d || 0);
            totalLife += parseInt(user.calls_lifetime || 0);
        });

        document.getElementById('stat-calls-24h').textContent = total24h.toLocaleString();
        document.getElementById('stat-calls-7d').textContent = total7d.toLocaleString();
        document.getElementById('stat-calls-life').textContent = totalLife.toLocaleString();
    }

    function applyFilters() {
        const searchTerm = searchInput.value.toLowerCase();
        const roleFilter = filterRole.value;
        const statusFilter = filterStatus.value;

        const filteredUsers = usersData.filter(user => {
            const matchesSearch = (user.first_name + ' ' + user.last_name).toLowerCase().includes(searchTerm) ||
                                 user.email.toLowerCase().includes(searchTerm);
            const matchesRole = roleFilter === 'all' || user.role === roleFilter;
            const matchesStatus = statusFilter === 'all' || user.subscription_status === statusFilter;

            return matchesSearch && matchesRole && matchesStatus;
        });

        populateTable(filteredUsers, false); // Don't reset filters on sub-population
    }

    function populateTable(users, isInitialLoad = true) {
        usersTableBody.innerHTML = ''; // Clear existing rows
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
                <td>
                    <a href="javascript:void(0)" class="view-calls-link" data-userid="${user.id}" data-name="${user.first_name} ${user.last_name}">
                        ${user.calls_1h} / ${user.calls_24h} / ${user.calls_7d} / ${user.calls_30d} / ${user.calls_lifetime}
                    </a>
                </td>
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
                <td>
                    <button class="send-email-btn" data-userid="${user.id}">Send Email</button>
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
        document.querySelectorAll('.delete-user-btn').forEach(button => {
            button.addEventListener('click', handleDeleteUser);
        });
        document.querySelectorAll('.view-calls-link').forEach(link => {
            link.addEventListener('click', openCallsModal);
        });
        document.querySelectorAll('.send-email-btn').forEach(button => {
            button.addEventListener('click', openEmailModal);
        });
    }

    function openEmailModal(event) {
        const userId = event.target.dataset.userid;
        const user = usersData.find(u => u.id == userId);
        if (user) {
            emailUserIdInput.value = userId;
            emailModalUserName.textContent = `${user.first_name} ${user.last_name}`;
            emailSubjectInput.value = '';
            emailMessageInput.value = '';
            sendEmailModal.style.display = 'block';
        }
    }

    if (sendEmailForm) {
        sendEmailForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            const userId = emailUserIdInput.value;
            const subject = emailSubjectInput.value;
            const message = emailMessageInput.value;

            try {
                const response = await fetch('api/admin/send_email.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ user_id: userId, subject, message })
                });
                const result = await response.json();
                showToast(result.message, response.ok ? 'success' : 'error');
                if (response.ok) {
                    sendEmailModal.style.display = 'none';
                }
            } catch (error) {
                console.error('Send email failed:', error);
                showToast('An error occurred while sending the email.', 'error');
            }
        });
    }

    async function openCallsModal(event) {
        const userId = event.currentTarget.dataset.userid;
        const userName = event.currentTarget.dataset.name;
        currentViewingCallsUserId = userId;
        modalOpenaiUserName.textContent = userName;
        openaiTimeframeSelect.value = 'lifetime'; // Reset to lifetime when opening
        openaiCallsModal.style.display = 'block';

        await fetchAndPopulateCalls(userId, 'lifetime');
    }

    async function fetchAndPopulateCalls(userId, timeframe) {
        openaiCallsTableBody.innerHTML = '<tr><td colspan="3">Loading...</td></tr>';
        currentCallsData = [];

        try {
            const response = await fetch(`api/admin/get_user_calls.php?user_id=${userId}&timeframe=${timeframe}`);
            const data = await response.json();
            if (response.ok && data.status === 'success') {
                currentCallsData = data.calls;
                populateCallsTable(currentCallsData);
            } else {
                openaiCallsTableBody.innerHTML = `<tr><td colspan="3">Error: ${data.message}</td></tr>`;
            }
        } catch (error) {
            console.error('Error fetching call history:', error);
            openaiCallsTableBody.innerHTML = '<tr><td colspan="3">An error occurred.</td></tr>';
        }
    }

    function populateCallsTable(calls) {
        openaiCallsTableBody.innerHTML = '';
        if (calls.length === 0) {
            openaiCallsTableBody.innerHTML = '<tr><td colspan="3">No calls found for this timeframe.</td></tr>';
            return;
        }
        calls.forEach(call => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${call.openai_id}</td>
                <td>${call.model}</td>
                <td>${new Date(call.created_at).toLocaleString()}</td>
            `;
            openaiCallsTableBody.appendChild(row);
        });
    }

    function exportCallsToCsv() {
        if (currentCallsData.length === 0) {
            showToast('No data to export.', 'error');
            return;
        }

        const userName = modalOpenaiUserName.textContent.replace(/\s+/g, '_');
        const timeframe = openaiTimeframeSelect.value;
        const filename = `openai_calls_${userName}_${timeframe}_${new Date().toISOString().split('T')[0]}.csv`;

        const csvRows = [];
        const headers = ['OpenAI ID', 'Model', 'Timestamp'];
        csvRows.push(headers.join(','));

        currentCallsData.forEach(call => {
            const row = [
                `"${call.openai_id}"`,
                `"${call.model}"`,
                `"${call.created_at}"`
            ];
            csvRows.push(row.join(','));
        });

        const csvContent = csvRows.join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement("a");
        if (link.download !== undefined) {
            const url = URL.createObjectURL(blob);
            link.setAttribute("href", url);
            link.setAttribute("download", filename);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    }

    function openEditModal(event) {
        currentEditingUserId = event.target.dataset.userid;
        const user = usersData.find(u => u.id == currentEditingUserId);

        if (user) {
            document.getElementById('modal-user-name').textContent = `${user.first_name} ${user.last_name}`;
            modalEmailInput.value = user.email;
            modalPasswordInput.value = ''; // Clear password field
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
        await updateUser('delete_user', { user_id: userId });
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
        if (newPassword) { // Only update if a new password is provided
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

    async function loadAuditLogs() {
        auditLogsTableBody.innerHTML = '<tr><td colspan="5">Loading logs...</td></tr>';
        try {
            const response = await fetch('api/admin/get_audit_logs.php');
            const data = await response.json();
            if (response.ok && data.status === 'success') {
                populateAuditLogsTable(data.logs);
            } else {
                auditLogsTableBody.innerHTML = `<tr><td colspan="5">Error: ${data.message}</td></tr>`;
            }
        } catch (error) {
            console.error('Error loading audit logs:', error);
            auditLogsTableBody.innerHTML = '<tr><td colspan="5">An error occurred while fetching logs.</td></tr>';
        }
    }

    function populateAuditLogsTable(logs) {
        auditLogsTableBody.innerHTML = '';
        if (logs.length === 0) {
            auditLogsTableBody.innerHTML = '<tr><td colspan="5">No audit logs found.</td></tr>';
            return;
        }

        logs.forEach(log => {
            const row = document.createElement('tr');
            const targetName = log.target_id ? `${log.target_first_name} ${log.target_last_name} (ID: ${log.target_id})` : 'N/A';
            row.innerHTML = `
                <td>${new Date(log.created_at).toLocaleString()}</td>
                <td>${log.admin_first_name} ${log.admin_last_name}</td>
                <td><strong>${log.action}</strong></td>
                <td>${targetName}</td>
                <td>${log.details || ''}</td>
            `;
            auditLogsTableBody.appendChild(row);
        });
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
