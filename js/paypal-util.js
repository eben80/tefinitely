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

async function renderPayPalSubscriptionButton(containerId = '#paypal-button-container') {
    if (typeof paypal === 'undefined') {
        const script = document.getElementById('paypal-sdk-script');
        if (script) {
            await new Promise((resolve) => {
                script.onload = resolve;
                if (typeof paypal !== 'undefined') resolve();
            });
        } else {
            await loadPayPalSDK();
        }
    }

    // Check again after attempt to load
    if (typeof paypal === 'undefined') {
        console.error("PayPal SDK not loaded.");
        return;
    }

    const paypalScript = document.getElementById('paypal-sdk-script');
    const planId = paypalScript.getAttribute('data-plan-id');
    if (!planId || planId === 'YOUR_PAYPAL_PLAN_ID') {
        console.error('Subscription plan is not configured.');
        return;
    }

    paypal.Buttons({
        style: { shape: 'rect', color: 'gold', layout: 'vertical', label: 'subscribe' },
        createSubscription: function(data, actions) {
            return actions.subscription.create({ 'plan_id': planId });
        },
        onApprove: async function(data, actions) {
            if (typeof showToast === 'function') showToast('Subscription approved! Finalizing...', 'info');
            try {
                const response = await fetch('api/paypal/capture_subscription.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ subscriptionID: data.subscriptionID })
                });
                const result = await response.json();
                if (result.status === 'success') {
                    if (typeof showToast === 'function') showToast('Subscription successful! You now have access.', 'success');
                    setTimeout(() => { window.location.reload(); }, 2000);
                } else {
                    if (typeof showToast === 'function') showToast('Failed to activate subscription: ' + (result.message || 'Unknown error'), 'error');
                }
            } catch (error) {
                if (typeof showToast === 'function') showToast('An error occurred while finalizing your subscription.', 'error');
            }
        },
        onError: function(err) {
            console.error('PayPal button error:', err);
            if (typeof showToast === 'function') showToast('An error occurred with the PayPal button.', 'error');
        }
    }).render(containerId);
}
