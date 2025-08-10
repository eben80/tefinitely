<?php
// --- PayPal Configuration ---
// Load from environment variables.
// These should be set in your server's environment.

// Set to 'https://api-m.sandbox.paypal.com' for sandbox testing
// Set to 'https://api-m.paypal.com' for live production
define('PAYPAL_API_BASE_URL', getenv('PAYPAL_API_BASE_URL') ?: 'https://api-m.sandbox.paypal.com');

define('PAYPAL_CLIENT_ID', getenv('PAYPAL_CLIENT_ID') ?: 'YOUR_PAYPAL_CLIENT_ID');
define('PAYPAL_CLIENT_SECRET', getenv('PAYPAL_CLIENT_SECRET') ?: 'YOUR_PAYPAL_CLIENT_SECRET');

// This will be created in the next step by the setup script.
define('PAYPAL_PLAN_ID', getenv('PAYPAL_PLAN_ID') ?: 'YOUR_PAYPAL_PLAN_ID');
?>
