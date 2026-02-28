// js/paypal-util.js

let paypalInstance = null;

async function loadPayPalSDK() {
    if (document.getElementById('paypal-sdk-script')) {
        return new Promise((resolve) => {
            if (window.paypal && window.paypal.createInstance) resolve();
            else document.getElementById('paypal-sdk-script').onload = resolve;
        });
    }

    try {
        const configResponse = await fetch('api/paypal/get_config.php');
        const config = await configResponse.json();
        const isSandbox = config.environment === 'sandbox';

        const script = document.createElement('script');
        // Using v6 core
        script.src = isSandbox ? "https://www.sandbox.paypal.com/web-sdk/v6/core" : "https://www.paypal.com/web-sdk/v6/core";
        script.id = 'paypal-sdk-script';
        script.async = true;
        document.head.appendChild(script);

        return new Promise((resolve) => {
            script.onload = resolve;
        });
    } catch (error) {
        console.error('Failed to load PayPal config:', error);
    }
}

async function getPayPalInstance() {
    if (paypalInstance) return paypalInstance;

    await loadPayPalSDK();

    try {
        const response = await fetch('api/paypal/get_client_token.php');
        const data = await response.json();

        if (data.status === 'success') {
            paypalInstance = await window.paypal.createInstance({
                clientToken: data.client_token,
                components: ['paypal-payments', 'paypal-subscriptions', 'card-fields']
            });
            return paypalInstance;
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
            const instance = await getPayPalInstance();
            if (!instance) return;

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
                    renderSubscriptionButton(instance, plan.paypal_plan_id, `#${buttonId}`);
                } else {
                    renderOneTimeButton(instance, plan.id, `#${buttonId}`);
                }
            }
        } else {
            container.innerHTML = '<p>No payment plans available at the moment.</p>';
        }
    } catch (error) {
        console.error('Failed to load available plans:', error);
    }
}

async function renderSubscriptionButton(instance, paypalPlanId, containerId) {
    const session = await instance.createPayPalSubscriptionSession({
        planId: paypalPlanId,
        onApprove: async (data) => {
            handlePaymentApproval('api/paypal/capture_subscription.php', { subscriptionID: data.subscriptionID });
        },
        onError: (err) => {
            console.error('PayPal Subscription error:', err);
            if (typeof showToast === 'function') showToast('An error occurred with the subscription button.', 'error');
        }
    });

    const button = document.createElement('paypal-button');
    document.querySelector(containerId).appendChild(button);
    button.addEventListener('click', () => session.start());
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
