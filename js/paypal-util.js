async function loadPayPalSDK() {
    try {
        const response = await fetch('api/paypal/get_config.php');
        const config = await response.json();
        if (config.client_id && config.client_id !== 'YOUR_PAYPAL_CLIENT_ID') {
            if (document.getElementById('paypal-sdk-script')) return; // Already loading/loaded

            const script = document.createElement('script');
            script.src = `https://www.paypal.com/sdk/js?client-id=${config.client_id}&currency=CAD&intent=subscription&vault=true`;
            script.setAttribute('data-plan-id', config.plan_id);
            script.id = 'paypal-sdk-script';
            document.head.appendChild(script);

            return new Promise((resolve) => {
                script.onload = resolve;
            });
        } else {
            console.error('PayPal client_id is not configured.');
        }
    } catch (error) {
        console.error('Failed to load PayPal config:', error);
    }
}

async function renderPayPalButtons(containerId = '#paypal-button-container') {
    try {
        const response = await fetch('api/paypal/get_available_plans.php');
        const data = await response.json();
        const container = document.querySelector(containerId);
        if (!container) return;
        container.innerHTML = ''; // Clear existing

        if (data.status === 'success' && data.plans.length > 0) {
            for (const plan of data.plans) {
                const planWrapper = document.createElement('div');
                planWrapper.className = 'plan-option';
                planWrapper.style.marginBottom = '2rem';
                planWrapper.style.padding = '1rem';
                planWrapper.style.border = '1px solid #ddd';
                planWrapper.style.borderRadius = '8px';

                const planTitle = document.createElement('h4');
                planTitle.textContent = `${plan.name} - ${plan.price} ${plan.currency}`;
                planWrapper.appendChild(planTitle);

                if (plan.description) {
                    const planDesc = document.createElement('p');
                    planDesc.textContent = plan.description;
                    planDesc.style.fontSize = '0.9rem';
                    planWrapper.appendChild(planDesc);
                }

                const buttonId = `paypal-button-${plan.id}`;
                const buttonContainer = document.createElement('div');
                buttonContainer.id = buttonId;
                planWrapper.appendChild(buttonContainer);
                container.appendChild(planWrapper);

                if (plan.type === 'subscription') {
                    renderSubscriptionButton(plan.paypal_plan_id, `#${buttonId}`);
                } else {
                    renderOneTimeButton(plan.id, `#${buttonId}`);
                }
            }
        } else {
            container.innerHTML = '<p>No payment plans available at the moment.</p>';
        }
    } catch (error) {
        console.error('Failed to load available plans:', error);
    }
}

async function renderSubscriptionButton(paypalPlanId, containerId) {
    if (typeof paypal === 'undefined') await loadPayPalSDK();

    paypal.Buttons({
        style: { shape: 'rect', color: 'gold', layout: 'vertical', label: 'subscribe' },
        createSubscription: function(data, actions) {
            return actions.subscription.create({ 'plan_id': paypalPlanId });
        },
        onApprove: async function(data, actions) {
            handlePaymentApproval('api/paypal/capture_subscription.php', { subscriptionID: data.subscriptionID });
        },
        onError: function(err) {
            console.error('PayPal Subscription error:', err);
            if (typeof showToast === 'function') showToast('An error occurred with the subscription button.', 'error');
        }
    }).render(containerId);
}

async function renderOneTimeButton(planId, containerId) {
    if (typeof paypal === 'undefined') await loadPayPalSDK();

    paypal.Buttons({
        style: { shape: 'rect', color: 'blue', layout: 'vertical', label: 'pay' },
        createOrder: async function() {
            try {
                const response = await fetch('api/paypal/create_payment.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ plan_id: planId })
                });
                const data = await response.json();
                return data.orderID;
            } catch (err) {
                console.error('Create Order error:', err);
            }
        },
        onApprove: async function(data, actions) {
            handlePaymentApproval('api/paypal/capture_payment.php', { orderID: data.orderID });
        },
        onError: function(err) {
            console.error('PayPal One-Time error:', err);
            if (typeof showToast === 'function') showToast('An error occurred with the payment button.', 'error');
        }
    }).render(containerId);
}

async function handlePaymentApproval(endpoint, payload) {
    if (typeof showToast === 'function') showToast('Payment approved! Finalizing...', 'info');
    try {
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const result = await response.json();
        if (result.status === 'success') {
            if (typeof showToast === 'function') showToast('Success! Your access is now active.', 'success');
            setTimeout(() => { window.location.reload(); }, 2000);
        } else {
            if (typeof showToast === 'function') showToast('Error: ' + (result.message || 'Unknown error'), 'error');
        }
    } catch (error) {
        if (typeof showToast === 'function') showToast('An error occurred while finalizing your payment.', 'error');
    }
}

async function renderPayPalSubscriptionButton(containerId = '#paypal-button-container') {
    // Deprecated, but keeping for backward compatibility if needed, redirecting to new unified function
    return renderPayPalButtons(containerId);
}
