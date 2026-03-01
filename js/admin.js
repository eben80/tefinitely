document.addEventListener('DOMContentLoaded', () => {
    // --- DOM Elements ---
    const adminDashboard = document.getElementById('admin-dashboard');
    const authErrorDiv = document.getElementById('auth-error');
    const usersTableBody = document.getElementById('users-table-body');
    const modal = document.getElementById('edit-user-modal');
    const modalUsername = document.getElementById('modal-user-name');
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

    const tabBtns = document.querySelectorAll('.tab-link');
    const tabContents = document.querySelectorAll('.tab-content');
    const auditLogsTableBody = document.getElementById('audit-logs-table-body');
    const loginHistoryTableBody = document.getElementById('login-history-table-body');
    const loginSearchInput = document.getElementById('login-search-input');
    const loginSearchBtn = document.getElementById('login-search-btn');
    const loginResetBtn = document.getElementById('login-reset-btn');
    const clearLoginHistoryBtn = document.getElementById('clear-login-history-btn');
    const clearAuditLogsBtn = document.getElementById('clear-audit-logs-btn');
    const resetFinancialBtn = document.getElementById('reset-financial-btn');
    const supportTicketsTableBody = document.getElementById('support-tickets-table-body');

    const sendEmailModal = document.getElementById('send-email-modal');
    const sendEmailForm = document.getElementById('send-email-form');
    const emailModalUserName = document.getElementById('email-modal-user-name');
    const emailUserIdInput = document.getElementById('email-user-id');
    const emailBulkIdsInput = document.getElementById('email-bulk-ids');
    const emailSubjectInput = document.getElementById('email-subject');
    const emailMessageInput = document.getElementById('email-message');

    const selectAllUsersCheckbox = document.getElementById('select-all-users');
    const bulkEmailBtn = document.getElementById('bulk-email-btn');
    const bulkActivateBtn = document.getElementById('bulk-activate-btn');
    const bulkDeactivateBtn = document.getElementById('bulk-deactivate-btn');
    const bulkDeleteBtn = document.getElementById('bulk-delete-btn');

    const paymentHistoryModal = document.getElementById('payment-history-modal');
    const paymentHistoryTableBody = document.getElementById('payment-history-table-body');
    const modalPaymentUserName = document.getElementById('modal-payment-user-name');

    // Payment Plans elements
    const plansTableBody = document.getElementById('plans-table-body');
    const planModal = document.getElementById('plan-modal');
    const planForm = document.getElementById('plan-form');
    const planTypeSelect = document.getElementById('plan-type');
    const durationField = document.getElementById('duration-field');
    const paypalPlanField = document.getElementById('paypal-plan-field');
    const addPlanBtn = document.getElementById('add-plan-btn');

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
                    content.classList.add('active');
                } else {
                    content.style.display = 'none';
                    content.classList.remove('active');
                }
            });

            if (targetTab === 'audit-logs') {
                loadAuditLogs();
            } else if (targetTab === 'login-history') {
                loadLoginHistory();
            } else if (targetTab === 'support-tickets') {
                loadSupportTickets();
            } else if (targetTab === 'financial-overview') {
                loadFinancialStats();
            } else if (targetTab === 'payment-settings') {
                loadPaymentPlans();
            }
        });
    });

    if (selectAllUsersCheckbox) {
        selectAllUsersCheckbox.addEventListener('change', () => {
            const checkboxes = document.querySelectorAll('.user-checkbox');
            checkboxes.forEach(cb => cb.checked = selectAllUsersCheckbox.checked);
        });
    }

    if (bulkEmailBtn) {
        bulkEmailBtn.addEventListener('click', () => {
            const selectedIds = getSelectedUserIds();
            if (selectedIds.length === 0) return showToast('No users selected', 'error');

            emailUserIdInput.value = '';
            emailBulkIdsInput.value = selectedIds.join(',');
            emailModalUserName.textContent = `${selectedIds.length} selected users`;
            emailSubjectInput.value = '';
            emailMessageInput.value = '';
            sendEmailModal.style.display = 'block';
        });
    }

    if (bulkActivateBtn) {
        bulkActivateBtn.addEventListener('click', () => handleBulkStatusUpdate('active'));
    }
    if (bulkDeactivateBtn) {
        bulkDeactivateBtn.addEventListener('click', () => handleBulkStatusUpdate('inactive'));
    }
    if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', handleBulkDelete);
    }

    if (addPlanBtn) {
        addPlanBtn.addEventListener('click', () => {
            planForm.reset();
            document.getElementById('plan-id').value = '';
            document.getElementById('plan-modal-title').textContent = 'Add New Payment Plan';
            togglePlanFields();
            planModal.style.display = 'block';
        });
    }

    if (planTypeSelect) {
        planTypeSelect.addEventListener('change', togglePlanFields);
    }

    if (planForm) {
        planForm.addEventListener('submit', handleSavePlan);
    }

    if (loginSearchBtn) {
        loginSearchBtn.addEventListener('click', () => loadLoginHistory(loginSearchInput.value));
    }
    if (loginResetBtn) {
        loginResetBtn.addEventListener('click', () => {
            loginSearchInput.value = '';
            loadLoginHistory();
        });
    }
    if (clearLoginHistoryBtn) {
        clearLoginHistoryBtn.addEventListener('click', () => handleClearLogs('login_history'));
    }
    if (clearAuditLogsBtn) {
        clearAuditLogsBtn.addEventListener('click', () => handleClearLogs('audit_logs'));
    }
    if (resetFinancialBtn) {
        resetFinancialBtn.addEventListener('click', () => handleClearLogs('financial'));
    }

    function togglePlanFields() {
        const type = planTypeSelect.value;
        if (type === 'subscription') {
            durationField.style.display = 'none';
            paypalPlanField.style.display = 'block';
        } else {
            durationField.style.display = 'block';
            paypalPlanField.style.display = 'none';
        }
    }

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

        populateTable(filteredUsers);
    }

    function escapeHTML(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function populateTable(users) {
        usersTableBody.innerHTML = ''; // Clear existing rows
        users.forEach(user => {
            const row = document.createElement('tr');
            const statusClass = user.subscription_status === 'active' ? 'status-active' : 'status-inactive';
            const endDate = user.subscription_end_date ? new Date(user.subscription_end_date).toLocaleDateString() : 'N/A';

            const fullName = `${user.first_name} ${user.last_name}`;

            row.innerHTML = `
                <td><input type="checkbox" class="user-checkbox" data-userid="${user.id}"></td>
                <td>${escapeHTML(user.id)}</td>
                <td>${escapeHTML(user.first_name)}</td>
                <td>${escapeHTML(user.last_name)}</td>
                <td>${escapeHTML(user.email)}</td>
                <td>${escapeHTML(user.role)}</td>
                <td class="${statusClass}">${escapeHTML(user.subscription_status)}</td>
                <td>
                    ${escapeHTML(endDate)}
                    <br>
                    <a href="javascript:void(0)" class="view-payments-link" data-userid="${user.id}" data-name="${escapeHTML(fullName)}" style="font-size: 0.8rem;">View History</a>
                </td>
                <td>
                    <a href="javascript:void(0)" class="view-calls-link" data-userid="${user.id}" data-name="${escapeHTML(fullName)}">
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
        document.querySelectorAll('.view-payments-link').forEach(link => {
            link.addEventListener('click', openPaymentHistoryModal);
        });
    }

    function getSelectedUserIds() {
        return Array.from(document.querySelectorAll('.user-checkbox:checked')).map(cb => cb.dataset.userid);
    }

    async function handleBulkStatusUpdate(status) {
        const selectedIds = getSelectedUserIds();
        if (selectedIds.length === 0) return showToast('No users selected', 'error');

        if (!confirm(`Are you sure you want to set ${selectedIds.length} users to ${status}?`)) return;

        try {
            const response = await fetch('api/admin/manage_users.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'bulk_status_update', user_ids: selectedIds, subscription_status: status })
            });
            const result = await response.json();
            showToast(result.message, response.ok ? 'success' : 'error');
            if (response.ok) loadUsers();
        } catch (error) {
            console.error('Bulk status update failed:', error);
            showToast('An error occurred.', 'error');
        }
    }

    async function handleBulkDelete() {
        const selectedIds = getSelectedUserIds();
        if (selectedIds.length === 0) return showToast('No users selected', 'error');

        if (!confirm(`Are you sure you want to delete ${selectedIds.length} users? This cannot be undone.`)) return;

        try {
            const response = await fetch('api/admin/manage_users.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'bulk_delete', user_ids: selectedIds })
            });
            const result = await response.json();
            showToast(result.message, response.ok ? 'success' : 'error');
            if (response.ok) loadUsers();
        } catch (error) {
            console.error('Bulk delete failed:', error);
            showToast('An error occurred.', 'error');
        }
    }

    function openEmailModal(event) {
        const userId = event.target.dataset.userid;
        const user = usersData.find(u => u.id == userId);
        if (user) {
            emailUserIdInput.value = userId;
            emailBulkIdsInput.value = '';
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
            const bulkIds = emailBulkIdsInput.value;
            const subject = emailSubjectInput.value;
            const message = emailMessageInput.value;

            const endpoint = bulkIds ? 'api/admin/manage_users.php' : 'api/admin/send_email.php';
            const payload = bulkIds
                ? { action: 'bulk_email', user_ids: bulkIds.split(','), subject, message }
                : { user_id: userId, subject, message };

            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
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

    async function openPaymentHistoryModal(event) {
        const userId = event.target.dataset.userid;
        const name = event.target.dataset.name;
        modalPaymentUserName.textContent = name;
        paymentHistoryTableBody.innerHTML = '<tr><td colspan="4">Loading...</td></tr>';
        paymentHistoryModal.style.display = 'block';

        try {
            const response = await fetch(`api/admin/get_user_payments.php?user_id=${userId}`);
            const data = await response.json();
            if (response.ok) {
                paymentHistoryTableBody.innerHTML = '';
                if (data.payments.length === 0) {
                    paymentHistoryTableBody.innerHTML = '<tr><td colspan="4">No payments found.</td></tr>';
                    return;
                }
                data.payments.forEach(p => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${escapeHTML(p.paypal_transaction_id)}</td>
                        <td>${parseFloat(p.amount).toFixed(2)}</td>
                        <td>${escapeHTML(p.currency)}</td>
                        <td>${new Date(p.payment_date).toLocaleString()}</td>
                    `;
                    paymentHistoryTableBody.appendChild(row);
                });
            } else {
                paymentHistoryTableBody.innerHTML = `<tr><td colspan="4">Error: ${data.message}</td></tr>`;
            }
        } catch (error) {
            console.error('Failed to load payment history:', error);
        }
    }

    let paymentPlans = [];
    async function loadPaymentPlans() {
        try {
            const response = await fetch('api/admin/manage_payment_plans.php');
            const data = await response.json();
            if (response.ok) {
                paymentPlans = data.plans;
                populatePlansTable(paymentPlans);
            }
        } catch (error) {
            console.error('Failed to load payment plans:', error);
        }
    }

    function populatePlansTable(plans) {
        plansTableBody.innerHTML = '';
        plans.forEach(plan => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${plan.id}</td>
                <td>${escapeHTML(plan.name)}</td>
                <td>${escapeHTML(plan.type)}</td>
                <td>${parseFloat(plan.price).toFixed(2)}</td>
                <td>${escapeHTML(plan.currency)}</td>
                <td>${plan.duration_days || 'N/A'}</td>
                <td>${escapeHTML(plan.paypal_plan_id) || 'N/A'}</td>
                <td>${plan.is_active ? '<span class="status-active">Active</span>' : '<span class="status-inactive">Inactive</span>'}</td>
                <td>
                    <button class="edit-plan-btn" data-id="${plan.id}">Edit</button>
                    <button class="delete-plan-btn" data-id="${plan.id}" style="background-color: #dc3545; color: white;">Delete</button>
                </td>
            `;
            plansTableBody.appendChild(row);
        });

        document.querySelectorAll('.edit-plan-btn').forEach(btn => {
            btn.addEventListener('click', openEditPlanModal);
        });
        document.querySelectorAll('.delete-plan-btn').forEach(btn => {
            btn.addEventListener('click', handleDeletePlan);
        });
    }

    function openEditPlanModal(e) {
        const id = e.target.dataset.id;
        const plan = paymentPlans.find(p => p.id == id);
        if (plan) {
            document.getElementById('plan-id').value = plan.id;
            document.getElementById('plan-name').value = plan.name;
            document.getElementById('plan-type').value = plan.type;
            document.getElementById('plan-price').value = plan.price;
            document.getElementById('plan-currency').value = plan.currency;
            document.getElementById('plan-duration').value = plan.duration_days;
            document.getElementById('plan-paypal-id').value = plan.paypal_plan_id;
            document.getElementById('plan-description').value = plan.description;
            document.getElementById('plan-active').value = plan.is_active;

            document.getElementById('plan-modal-title').textContent = 'Edit Payment Plan';
            togglePlanFields();
            planModal.style.display = 'block';
        }
    }

    async function handleSavePlan(e) {
        e.preventDefault();
        const id = document.getElementById('plan-id').value;
        const duration_days = document.getElementById('plan-duration').value;
        const paypal_plan_id = document.getElementById('plan-paypal-id').value;

        const payload = {
            action: id ? 'update_plan' : 'add_plan',
            id: id,
            name: document.getElementById('plan-name').value,
            type: document.getElementById('plan-type').value,
            price: document.getElementById('plan-price').value,
            currency: document.getElementById('plan-currency').value,
            duration_days: duration_days === "" ? null : duration_days,
            paypal_plan_id: paypal_plan_id === "" ? null : paypal_plan_id,
            description: document.getElementById('plan-description').value,
            is_active: document.getElementById('plan-active').value
        };

        try {
            const response = await fetch('api/admin/manage_payment_plans.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await response.json();
            showToast(result.message, response.ok ? 'success' : 'error');
            if (response.ok) {
                planModal.style.display = 'none';
                loadPaymentPlans();
            }
        } catch (error) {
            console.error('Failed to save plan:', error);
            showToast('An error occurred while saving the plan.', 'error');
        }
    }

    async function handleDeletePlan(e) {
        if (!confirm('Are you sure you want to delete this plan?')) return;
        const id = e.target.dataset.id;
        try {
            const response = await fetch('api/admin/manage_payment_plans.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'delete_plan', id: id })
            });
            const result = await response.json();
            showToast(result.message, response.ok ? 'success' : 'error');
            if (response.ok) loadPaymentPlans();
        } catch (error) {
            console.error('Failed to delete plan:', error);
            showToast('An error occurred while deleting the plan.', 'error');
        }
    }

    async function loadFinancialStats() {
        try {
            const response = await fetch('api/admin/get_financial_stats.php');
            const data = await response.json();
            if (response.ok) {
                document.getElementById('stat-active-subscribers').textContent = data.stats.active_subscribers;
                document.getElementById('stat-mrr').textContent = `$${data.stats.mrr.toFixed(2)}`;

                const trendsBody = document.getElementById('revenue-trends-body');
                trendsBody.innerHTML = '';
                data.stats.trends.forEach(t => {
                    const row = document.createElement('tr');
                    row.innerHTML = `<td>${t.month}</td><td>$${parseFloat(t.revenue).toFixed(2)}</td>`;
                    trendsBody.appendChild(row);
                });
            }
        } catch (error) {
            console.error('Failed to load financial stats:', error);
        }
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
                <td>${escapeHTML(log.admin_first_name)} ${escapeHTML(log.admin_last_name)}</td>
                <td><strong>${escapeHTML(log.action)}</strong></td>
                <td>${escapeHTML(targetName)}</td>
                <td>${escapeHTML(log.details) || ''}</td>
            `;
            auditLogsTableBody.appendChild(row);
        });
    }

    async function loadLoginHistory(search = '') {
        loginHistoryTableBody.innerHTML = '<tr><td colspan="6">Loading history...</td></tr>';
        try {
            const url = search ? `api/admin/get_login_history.php?search=${encodeURIComponent(search)}` : 'api/admin/get_login_history.php';
            const response = await fetch(url);
            const data = await response.json();
            if (response.ok && data.status === 'success') {
                populateLoginHistoryTable(data.history);
            } else {
                loginHistoryTableBody.innerHTML = `<tr><td colspan="6">Error: ${data.message}</td></tr>`;
            }
        } catch (error) {
            console.error('Error loading login history:', error);
            loginHistoryTableBody.innerHTML = '<tr><td colspan="6">An error occurred while fetching history.</td></tr>';
        }
    }

    async function handleClearLogs(type) {
        const confirmMsg = {
            'login_history': 'Are you sure you want to clear ALL login history? This cannot be undone.',
            'audit_logs': 'Are you sure you want to clear ALL audit logs? This cannot be undone.',
            'financial': 'Are you sure you want to reset financial data (clear all payment records)? This cannot be undone.'
        };

        if (!confirm(confirmMsg[type])) return;

        try {
            const response = await fetch('api/admin/clear_logs.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ type: type })
            });
            const result = await response.json();
            showToast(result.message, response.ok ? 'success' : 'error');
            if (response.ok) {
                if (type === 'login_history') loadLoginHistory();
                if (type === 'audit_logs') loadAuditLogs();
                if (type === 'financial') loadFinancialStats();
            }
        } catch (error) {
            console.error(`Clear ${type} failed:`, error);
            showToast('An error occurred.', 'error');
        }
    }

    function populateLoginHistoryTable(history) {
        loginHistoryTableBody.innerHTML = '';
        if (history.length === 0) {
            loginHistoryTableBody.innerHTML = '<tr><td colspan="6">No login history found.</td></tr>';
            return;
        }

        history.forEach(entry => {
            const row = document.createElement('tr');
            const statusClass = entry.status === 'success' ? 'status-active' : 'status-inactive';
            const userInfo = entry.first_name ? `${entry.first_name} ${entry.last_name}` : 'Unknown / Guest';

            row.innerHTML = `
                <td>${new Date(entry.created_at).toLocaleString()}</td>
                <td>${escapeHTML(entry.email)}</td>
                <td>${escapeHTML(entry.ip_address)}</td>
                <td class="${statusClass}">${escapeHTML(entry.status.toUpperCase())}</td>
                <td>${escapeHTML(userInfo)}</td>
                <td title="${escapeHTML(entry.user_agent)}" style="font-size: 0.8rem; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                    ${escapeHTML(entry.user_agent)}
                </td>
            `;
            loginHistoryTableBody.appendChild(row);
        });
    }

    async function loadSupportTickets() {
        supportTicketsTableBody.innerHTML = '<tr><td colspan="6">Loading tickets...</td></tr>';
        try {
            const response = await fetch('api/admin/get_tickets.php');
            const data = await response.json();
            if (response.ok && data.status === 'success') {
                populateSupportTicketsTable(data.tickets);
            } else {
                supportTicketsTableBody.innerHTML = `<tr><td colspan="6">Error: ${data.message}</td></tr>`;
            }
        } catch (error) {
            console.error('Error loading support tickets:', error);
            supportTicketsTableBody.innerHTML = '<tr><td colspan="6">An error occurred while fetching tickets.</td></tr>';
        }
    }

    function populateSupportTicketsTable(tickets) {
        supportTicketsTableBody.innerHTML = '';
        if (tickets.length === 0) {
            supportTicketsTableBody.innerHTML = '<tr><td colspan="6">No support tickets found.</td></tr>';
            return;
        }

        tickets.forEach(ticket => {
            const row = document.createElement('tr');
            const userName = ticket.first_name ? `${ticket.first_name} ${ticket.last_name}` : 'Guest';
            const statusClass = `status-${ticket.status.replace(' ', '-')}`;

            row.innerHTML = `
                <td>${new Date(ticket.created_at).toLocaleString()}</td>
                <td>${escapeHTML(userName)}</td>
                <td>${escapeHTML(ticket.email)}</td>
                <td title="${escapeHTML(ticket.message)}"><strong>${escapeHTML(ticket.subject)}</strong></td>
                <td>
                    <select class="ticket-status-select" data-ticketid="${ticket.id}">
                        <option value="open" ${ticket.status === 'open' ? 'selected' : ''}>Open</option>
                        <option value="in-progress" ${ticket.status === 'in-progress' ? 'selected' : ''}>In-Progress</option>
                        <option value="resolved" ${ticket.status === 'resolved' ? 'selected' : ''}>Resolved</option>
                    </select>
                </td>
                <td>
                    <button class="view-ticket-btn" data-ticketid="${ticket.id}">View</button>
                    <button class="reply-ticket-btn" data-userid="${ticket.user_id || ''}" data-email="${ticket.email}" data-subject="Re: ${ticket.subject}">Reply</button>
                </td>
            `;
            supportTicketsTableBody.appendChild(row);
        });

        document.querySelectorAll('.ticket-status-select').forEach(select => {
            select.addEventListener('change', handleTicketStatusChange);
        });

        document.querySelectorAll('.view-ticket-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const ticketId = e.target.dataset.ticketid;
                const ticket = tickets.find(t => t.id == ticketId);
                if (ticket) {
                    alert(`From: ${ticket.email}\nSubject: ${ticket.subject}\n\n${ticket.message}`);
                }
            });
        });

        document.querySelectorAll('.reply-ticket-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const userId = e.target.dataset.userid;
                const email = e.target.dataset.email;
                const subject = e.target.dataset.subject;

                emailUserIdInput.value = userId;
                emailModalUserName.textContent = email;
                emailSubjectInput.value = subject;
                emailMessageInput.value = '';
                sendEmailModal.style.display = 'block';
            });
        });
    }

    async function handleTicketStatusChange(event) {
        const ticketId = event.target.dataset.ticketid;
        const newStatus = event.target.value;

        try {
            const response = await fetch('api/admin/update_ticket_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ticket_id: ticketId, status: newStatus })
            });
            const result = await response.json();
            showToast(result.message, response.ok ? 'success' : 'error');
        } catch (error) {
            console.error('Update ticket status failed:', error);
            showToast('An error occurred while updating the ticket status.', 'error');
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
