<?php
// PayPal API configuration
// Copy this file to paypal_config.php and fill in your details.

// Set to 'sandbox' for testing or 'live' for production
define('PAYPAL_ENVIRONMENT', 'sandbox');

if (PAYPAL_ENVIRONMENT === 'sandbox') {
    // Sandbox credentials
    define('PAYPAL_API_BASE_URL', 'https://api.sandbox.paypal.com');
    define('PAYPAL_CLIENT_ID', 'YOUR_SANDBOX_CLIENT_ID');
    define('PAYPAL_CLIENT_SECRET', 'YOUR_SANDBOX_CLIENT_SECRET');
} else {
    // Live credentials
    define('PAYPAL_API_BASE_URL', 'https://api.paypal.com');
    define('PAYPAL_CLIENT_ID', 'YOUR_LIVE_CLIENT_ID');
    define('PAYPAL_CLIENT_SECRET', 'YOUR_LIVE_CLIENT_SECRET');
}

// Your PayPal Subscription Plan ID
define('PAYPAL_PLAN_ID', 'YOUR_PAYPAL_PLAN_ID');

// Your PayPal Webhook ID (It's recommended to set this as an environment variable)
// You can get this from your PayPal developer dashboard after creating a webhook.
// define('PAYPAL_WEBHOOK_ID', getenv('PAYPAL_WEBHOOK_ID') ?: 'YOUR_WEBHOOK_ID');
?>
