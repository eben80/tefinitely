// js/paypal-util.js

let paypalV6Instance = null;

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
        const clientId = config.client_id;
        const currency = config.currency || 'CAD';

        const script = document.createElement('script');
        // We use a different namespace (paypalSubscriptions) to avoid conflicts with v6 window.paypal
        script.src = `https://www.paypal.com/sdk/js?client-id=${clientId}&vault=true&intent=subscription&components=buttons&currency=${currency}`;
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

/**
 * Load the Standard PayPal JS SDK for One-Time Payments
 */
async function loadPayPalOneTimeSDK() {
    if (document.getElementById('paypal-onetime-script')) {
        return new Promise((resolve) => {
            if (window.paypal && window.paypal.Buttons) resolve();
            else document.getElementById('paypal-onetime-script').onload = resolve;
        });
    }

    try {
        const configResponse = await fetch('api/paypal/get_config.php');
        const config = await configResponse.json();
        const clientId = config.client_id;
        const currency = config.currency || 'CAD';

        const script = document.createElement('script');
        // Standard SDK for one-time
        script.src = `https://www.paypal.com/sdk/js?client-id=${clientId}&components=buttons,funding-eligibility&currency=${currency}&intent=capture`;
        script.id = 'paypal-onetime-script';
        script.async = true;
        document.head.appendChild(script);

        return new Promise((resolve) => {
            script.onload = resolve;
        });
    } catch (error) {
        console.error('Failed to load PayPal One-Time SDK:', error);
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

                if (plan.type === 'subscription') {
                    container.appendChild(planWrapper);
                    renderSubscriptionButton(plan.paypal_plan_id, `#${buttonId}`);
                } else {
                    // Create containers for additional funding sources for one-time payments
                    const gpayContainer = document.createElement('div');
                    gpayContainer.id = `googlepay-container-${plan.id}`;
                    gpayContainer.className = 'googlepay-container';
                    gpayContainer.style.marginTop = '10px';
                    planWrapper.appendChild(gpayContainer);

                    const applepayContainer = document.createElement('div');
                    applepayContainer.id = `applepay-container-${plan.id}`;
                    applepayContainer.className = 'applepay-container';
                    applepayContainer.style.marginTop = '10px';
                    planWrapper.appendChild(applepayContainer);

                    const cardContainer = document.createElement('div');
                    cardContainer.id = `card-container-${plan.id}`;
                    cardContainer.className = 'card-container';
                    cardContainer.style.marginTop = '10px';
                    planWrapper.appendChild(cardContainer);

                    container.appendChild(planWrapper);
                    renderOneTimeButtons(plan.id, `#${buttonId}`);
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

async function renderOneTimeButtons(planId, containerId) {
    await loadPayPalOneTimeSDK();

    if (window.paypal && window.paypal.Buttons) {
        // Log funding sources for diagnostics as requested
        const fundingSources = window.paypal.getFundingSources();
        console.log('PayPal Available Funding Sources:', fundingSources);

        const baseConfig = {
            style: {
                layout: 'vertical',
                color: 'blue',
                shape: 'rect',
                label: 'pay'
            },
            createOrder: function(data, actions) {
                return fetch('api/paypal/create_payment.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ plan_id: planId })
                })
                .then(res => res.json())
                .then(data => data.orderID);
            },
            onApprove: function(data, actions) {
                return handlePaymentApproval('api/paypal/capture_payment.php', { orderID: data.orderID });
            },
            onError: (err) => {
                console.error('PayPal One-Time error:', err);
                if (typeof showToast === 'function') showToast('An error occurred with the payment button.', 'error');
            }
        };

        // Render standard PayPal button (explicitly selecting PayPal funding source to avoid redundancy)
        window.paypal.Buttons({
            ...baseConfig,
            fundingSource: window.paypal.FUNDING.PAYPAL
        }).render(containerId);

        // Explicitly render Google Pay if eligible
        if (fundingSources.includes(window.paypal.FUNDING.GOOGLEPAY)) {
            window.paypal.Buttons({
                ...baseConfig,
                fundingSource: window.paypal.FUNDING.GOOGLEPAY
            }).render(`#googlepay-container-${planId}`);
        }

        // Explicitly render Apple Pay if eligible
        if (window.paypal.Applepay && fundingSources.includes(window.paypal.FUNDING.APPLEPAY)) {
             window.paypal.Applepay().isEligible().then(eligible => {
                if (eligible) {
                    window.paypal.Buttons({
                        ...baseConfig,
                        fundingSource: window.paypal.FUNDING.APPLEPAY
                    }).render(`#applepay-container-${planId}`);
                }
            });
        }

        // Explicitly render Card if eligible
        if (fundingSources.includes(window.paypal.FUNDING.CARD)) {
            window.paypal.Buttons({
                ...baseConfig,
                style: {
                    ...baseConfig.style,
                    color: 'black' // Card button only supports 'black' or 'white'
                },
                fundingSource: window.paypal.FUNDING.CARD
            }).render(`#card-container-${planId}`);
        }
    } else {
        console.error('PayPal One-Time SDK not loaded correctly.');
    }
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

// Compatibility alias for profile.php
async function loadPayPalSDK() {
    return loadPayPalOneTimeSDK();
}
