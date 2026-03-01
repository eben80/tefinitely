// js/paypal-util.js

let paypalV6Instance = null;

/**
 * Load the PayPal SDK v6 Core script (for One-Time Payments)
 */
async function loadPayPalV6Core() {
    if (document.getElementById('paypal-v6-script')) {
        return new Promise((resolve) => {
            if (window.paypal && window.paypal.createInstance) resolve();
            else document.getElementById('paypal-v6-script').onload = resolve;
        });
    }

    try {
        const configResponse = await fetch('api/paypal/get_config.php');
        const config = await configResponse.json();
        const isSandbox = config.environment === 'sandbox';

        const script = document.createElement('script');
        script.src = isSandbox ? "https://www.sandbox.paypal.com/web-sdk/v6/core" : "https://www.paypal.com/web-sdk/v6/core";
        script.id = 'paypal-v6-script';
        script.async = true;
        document.head.appendChild(script);

        return new Promise((resolve) => {
            script.onload = resolve;
        });
    } catch (error) {
        console.error('Failed to load PayPal config:', error);
    }
}

/**
 * Load the standard Subscriptions SDK (v5-style, for Subscriptions)
 */
async function loadPayPalSubscriptionsSDK() {
    if (document.getElementById('paypal-subscriptions-script')) {
        return new Promise((resolve) => {
            if (window.paypalSubscriptions) resolve();
            else document.getElementById('paypal-subscriptions-script').onload = resolve;
        });
    }

    try {
        const configResponse = await fetch('api/paypal/get_config.php');
        const config = await configResponse.json();
        const isSandbox = config.environment === 'sandbox';
        const clientId = config.client_id;

        const script = document.createElement('script');
        // We use a different namespace (paypalSubscriptions) to avoid conflicts with v6 window.paypal
        script.src = `https://www.paypal.com/sdk/js?client-id=${clientId}&vault=true&intent=subscription&components=buttons`;
        script.setAttribute('data-namespace', 'paypalSubscriptions');
        script.id = 'paypal-subscriptions-script';
        script.async = true;
        document.head.appendChild(script);

        return new Promise((resolve) => {
            script.onload = resolve;
        });
    } catch (error) {
        console.error('Failed to load PayPal Subscriptions SDK:', error);
    }
}

async function getPayPalV6Instance() {
    if (paypalV6Instance) return paypalV6Instance;

    await loadPayPalV6Core();

    try {
        const response = await fetch('api/paypal/get_client_token.php');
        const data = await response.json();

        if (data.status === 'success') {
            paypalV6Instance = await window.paypal.createInstance({
                clientToken: data.client_token,
                components: ['paypal-payments', 'card-fields']
            });
            return paypalV6Instance;
        } else {
            console.error('Failed to get client token:', data.message);
        }
    } catch (error) {
        console.error('Error initializing PayPal v6:', error);
    }
    return null;
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
                    const instance = await getPayPalV6Instance();
                    if (instance) renderOneTimeButton(instance, plan.id, `#${buttonId}`);
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
    await loadPayPalSubscriptionsSDK();

    if (window.paypalSubscriptions && window.paypalSubscriptions.Buttons) {
        window.paypalSubscriptions.Buttons({
            createSubscription: function(data, actions) {
                return actions.subscription.create({
                    plan_id: paypalPlanId
                });
            },
            onApprove: function(data) {
                handlePaymentApproval('api/paypal/capture_subscription.php', { subscriptionID: data.subscriptionID });
            },
            onError: (err) => {
                console.error('PayPal Subscription error:', err);
                if (typeof showToast === 'function') showToast('An error occurred with the subscription button.', 'error');
            }
        }).render(containerId);
    } else {
        console.error('PayPal Subscriptions SDK not loaded correctly.');
    }
}

async function renderOneTimeButton(instance, planId, containerId) {
    const createOrder = async () => {
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
    };

    const session = await instance.createPayPalOneTimePaymentSession({
        onApprove: async (data) => {
            handlePaymentApproval('api/paypal/capture_payment.php', { orderID: data.orderID });
        },
        onError: (err) => {
            console.error('PayPal One-Time error:', err);
            if (typeof showToast === 'function') showToast('An error occurred with the payment button.', 'error');
        }
    });

    const button = document.createElement('paypal-button');
    button.setAttribute('color', 'blue');
    document.querySelector(containerId).appendChild(button);
    button.addEventListener('click', () => session.start({ createOrder }));
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
    return renderPayPalButtons(containerId);
}
