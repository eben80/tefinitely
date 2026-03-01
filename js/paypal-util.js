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
                components: ['paypal-payments', 'card-fields', 'googlepay-payments', 'applepay-payments']
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
                    if (instance) {
                        const ppId = `${buttonId}-paypal`;
                        const gpId = `${buttonId}-googlepay`;
                        const apId = `${buttonId}-applepay`;
                        const cfId = `${buttonId}-cardfields`;

                        const methodsContainer = document.createElement('div');
                        methodsContainer.className = 'payment-methods-container';
                        methodsContainer.innerHTML = `
                            <div id="${ppId}"></div>
                            <div id="${gpId}"></div>
                            <div id="${apId}"></div>
                            <div id="${cfId}" class="card-fields-container" style="display: none;">
                                <hr>
                                <h5>Pay with Credit Card</h5>
                                <div id="${cfId}-number" class="card-field"></div>
                                <div style="display: flex; gap: 10px;">
                                    <div id="${cfId}-expiry" class="card-field" style="flex: 1;"></div>
                                    <div id="${cfId}-cvv" class="card-field" style="flex: 1;"></div>
                                </div>
                                <button id="${cfId}-submit" class="card-field-submit">Pay with Card</button>
                            </div>
                        `;
                        document.querySelector(`#${buttonId}`).appendChild(methodsContainer);

                        renderOneTimeButton(instance, plan.id, `#${ppId}`);
                        renderGooglePayButton(instance, plan.id, `#${gpId}`);
                        renderApplePayButton(instance, plan.id, `#${apId}`);
                        renderCardFields(instance, plan.id, cfId);
                    }
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

async function renderGooglePayButton(instance, planId, containerId) {
    try {
        const googlepay = await instance.createGooglePayPaymentSession({
            onApprove: async (data) => {
                handlePaymentApproval('api/paypal/capture_payment.php', { orderID: data.orderId });
            },
            onError: (err) => {
                console.error('Google Pay error:', err);
            }
        });

        const isEligible = await googlepay.isEligible();
        if (isEligible) {
            const buttonContainer = document.createElement('div');
            buttonContainer.className = 'googlepay-button-container';
            buttonContainer.style.marginTop = '10px';
            document.querySelector(containerId).appendChild(buttonContainer);

            const button = document.createElement('googlepay-button');
            button.setAttribute('button-type', 'plain');
            button.setAttribute('button-color', 'black');
            buttonContainer.appendChild(button);

            button.addEventListener('click', async () => {
                try {
                    const response = await fetch('api/paypal/create_payment.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ plan_id: planId })
                    });
                    const data = await response.json();
                    await googlepay.start({
                        orderId: data.orderID,
                        presentationMode: 'auto'
                    });
                } catch (err) {
                    console.error('Google Pay start error:', err);
                }
            });
        }
    } catch (err) {
        console.error('Failed to initialize Google Pay:', err);
    }
}

async function renderCardFields(instance, planId, cfId) {
    try {
        const cardFields = await instance.createCardFields({
            onApprove: async (data) => {
                handlePaymentApproval('api/paypal/capture_payment.php', { orderID: data.orderId });
            },
            onError: (err) => {
                console.error('Card Fields error:', err);
                if (typeof showToast === 'function') showToast('Card payment failed. Please check your details.', 'error');
            }
        });

        if (cardFields.isEligible()) {
            document.getElementById(cfId).style.display = 'block';

            const numberField = cardFields.createNumberField();
            await numberField.render(`#${cfId}-number`);

            const expiryField = cardFields.createExpiryField();
            await expiryField.render(`#${cfId}-expiry`);

            const cvvField = cardFields.createCVVField();
            await cvvField.render(`#${cfId}-cvv`);

            document.getElementById(`${cfId}-submit`).addEventListener('click', async () => {
                try {
                    const response = await fetch('api/paypal/create_payment.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ plan_id: planId })
                    });
                    const data = await response.json();

                    await cardFields.submit({ orderId: data.orderID });
                } catch (err) {
                    console.error('Card submission error:', err);
                }
            });
        }
    } catch (err) {
        console.error('Failed to initialize Card Fields:', err);
    }
}

async function renderApplePayButton(instance, planId, containerId) {
    try {
        const applepay = await instance.createApplePayPaymentSession({
            onApprove: async (data) => {
                handlePaymentApproval('api/paypal/capture_payment.php', { orderID: data.orderId });
            },
            onError: (err) => {
                console.error('Apple Pay error:', err);
            }
        });

        const isEligible = await applepay.isEligible();
        if (isEligible) {
            const buttonContainer = document.createElement('div');
            buttonContainer.className = 'applepay-button-container';
            buttonContainer.style.marginTop = '10px';
            document.querySelector(containerId).appendChild(buttonContainer);

            const button = document.createElement('applepay-button');
            button.setAttribute('buttonstyle', 'black');
            button.setAttribute('type', 'plain');
            button.setAttribute('locale', 'en-US');
            buttonContainer.appendChild(button);

            button.addEventListener('click', async () => {
                try {
                    const response = await fetch('api/paypal/create_payment.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ plan_id: planId })
                    });
                    const data = await response.json();
                    await applepay.start({
                        orderId: data.orderID,
                        presentationMode: 'auto'
                    });
                } catch (err) {
                    console.error('Apple Pay start error:', err);
                }
            });
        }
    } catch (err) {
        console.error('Failed to initialize Apple Pay:', err);
    }
}

async function renderOneTimeButton(instance, planId, containerId) {
    const orderPromise = async () => {
        try {
            const response = await fetch('api/paypal/create_payment.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ plan_id: planId })
            });
            const data = await response.json();
            // v6 SDK expects an object with orderId (case sensitive)
            return { orderId: data.orderID };
        } catch (err) {
            console.error('Create Order error:', err);
            throw err;
        }
    };

    const session = await instance.createPayPalOneTimePaymentSession({
        onApprove: async (data) => {
            handlePaymentApproval('api/paypal/capture_payment.php', { orderID: data.orderId });
        },
        onError: (err) => {
            console.error('PayPal One-Time error:', err);
            if (typeof showToast === 'function') showToast('An error occurred with the payment button.', 'error');
        }
    });

    const button = document.createElement('paypal-button');
    button.setAttribute('color', 'blue');
    document.querySelector(containerId).appendChild(button);
    // session.start(options, orderPromise) - orderPromise is the 2nd argument in v6
    // The SDK specifically expects a Promise as the second argument.
    button.addEventListener('click', () => {
        session.start({ presentationMode: 'auto' }, orderPromise())
            .catch(err => console.error('Failed to start PayPal session:', err));
    });
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
