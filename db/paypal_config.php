<?php
// --- PayPal Configuration ---
// Load from environment variables.
// These should be set in your server's environment.

// Set to 'https://api-m.sandbox.paypal.com' for sandbox testing
// Set to 'https://api-m.paypal.com' for live production
define('PAYPAL_API_BASE_URL', getenv('PAYPAL_API_BASE_URL') ?: 'https://api-m.paypal.com');

define('PAYPAL_CLIENT_ID', getenv('PAYPAL_CLIENT_ID') ?: 'ARzNSw1AjNHDkFY_W6iQz455bFkn6P8z2VOS1JJucJRQjoPJT-hqQ3aRzCV28bu7hbgsVyhG7h7cHGqi');
define('PAYPAL_CLIENT_SECRET', getenv('PAYPAL_CLIENT_SECRET') ?: 'EJtZqJZT1glJ_bhkLYkyFnhOAnpNEimjAALyUXvgJIWI_h1zUwGdGrwLp-Kg_sEvSbWIx_7zrIpkqbcQ');

// This will be created in the next step by the setup script.
define('PAYPAL_PLAN_ID', getenv('PAYPAL_PLAN_ID') ?: 'P-2CD10536EL9059522NCMP2RQ');
?>
