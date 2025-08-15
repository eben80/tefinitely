<?php
// =================================================================================
// PayPal Subscription Setup Script
// =================================================================================
// This script should be run from the command line ONE TIME to create the
// necessary Product and Plan on your PayPal account.
//
// Usage: php scripts/setup_paypal_plan.php
// =================================================================================

// Increase execution time for API calls
set_time_limit(60);

// The script is in /scripts, so we need to go up one level to the project root
require_once __DIR__ . '/../db/db_config.php';
require_once __DIR__ . '/../db/paypal_config.php';

// --- Helper Functions ---

/**
 * Generates a unique ID for PayPal API requests to ensure idempotency.
 * Prevents creating duplicate products/plans if the script is run multiple times.
 */
function generate_request_id() {
    return uniqid('', true);
}

/**
 * Gets a PayPal OAuth2 Access Token.
 */
function get_paypal_access_token() {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, PAYPAL_API_BASE_URL . '/v1/oauth2/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
    curl_setopt($ch, CURLOPT_USERPWD, PAYPAL_CLIENT_ID . ':' . PAYPAL_CLIENT_SECRET);
    $headers = ['Accept: application/json', 'Accept-Language: en_US'];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        echo "cURL Error getting token: " . curl_error($ch) . "\n";
        return null;
    }
    curl_close($ch);
    $json = json_decode($result);
    return $json->access_token ?? null;
}

/**
 * Creates a Product on PayPal.
 */
function create_product($access_token) {
    echo "Attempting to create PayPal Product...\n";
    $ch = curl_init();
    $product_data = [
        'name' => 'TEF Practice Pro Access',
        'description' => 'Monthly subscription for premium access to the TEF Practice platform.',
        'type' => 'SERVICE',
        'category' => 'SOFTWARE',
    ];

    curl_setopt($ch, CURLOPT_URL, PAYPAL_API_BASE_URL . '/v1/catalogs/products');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($product_data));
    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $access_token,
        'PayPal-Request-Id: ' . generate_request_id(),
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $json_result = json_decode($result);

    if ($http_code == 201) {
        echo "Product created successfully!\n";
        echo "Product ID: " . $json_result->id . "\n";
        return $json_result->id;
    } else {
        echo "Error creating product. HTTP Status: {$http_code}\n";
        echo "Response: " . json_encode($json_result, JSON_PRETTY_PRINT) . "\n";
        return null;
    }
}

/**
 * Creates a Subscription Plan on PayPal.
 */
function create_plan($access_token, $product_id) {
    echo "Attempting to create PayPal Plan...\n";
    $ch = curl_init();
    $plan_data = [
        'product_id' => $product_id,
    'name' => 'TEF Practice Pro - Monthly Plan (15 CAD)',
    'description' => '15 CAD per month for full access to all features.',
        'status' => 'ACTIVE',
        'billing_cycles' => [
            [
                'frequency' => [
                    'interval_unit' => 'MONTH',
                    'interval_count' => 1,
                ],
                'tenure_type' => 'REGULAR',
                'sequence' => 1,
                'total_cycles' => 0, // 0 = repeats forever
                'pricing_scheme' => [
                    'fixed_price' => [
                    'value' => '15.00',
                    'currency_code' => 'CAD',
                    ],
                ],
            ],
        ],
        'payment_preferences' => [
            'auto_bill_outstanding' => true,
        'setup_fee_failure_action' => 'CANCEL',
        'payment_failure_threshold' => 0, // 0 means the subscription is suspended immediately after the first failed payment.
        ],
    ];

    curl_setopt($ch, CURLOPT_URL, PAYPAL_API_BASE_URL . '/v1/billing/plans');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($plan_data));
    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $access_token,
        'PayPal-Request-Id: ' . generate_request_id(),
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $json_result = json_decode($result);

    if ($http_code == 201) {
        echo "Plan created successfully!\n";
        echo "Plan ID: " . $json_result->id . "\n";
        return $json_result->id;
    } else {
        echo "Error creating plan. HTTP Status: {$http_code}\n";
        echo "Response: " . json_encode($json_result, JSON_PRETTY_PRINT) . "\n";
        return null;
    }
}


// --- Main Execution ---
echo "=============================================\n";
echo "Starting PayPal Product and Plan Setup...\n";
echo "=============================================\n";

if (empty(PAYPAL_CLIENT_ID) || PAYPAL_CLIENT_ID === 'YOUR_PAYPAL_CLIENT_ID') {
    die("Error: PayPal credentials are not set in your environment variables. Please set PAYPAL_CLIENT_ID and PAYPAL_CLIENT_SECRET.\n");
}

$token = get_paypal_access_token();
if (!$token) {
    die("Failed to get PayPal access token. Please check your credentials and API base URL.\n");
}
echo "Access Token obtained successfully.\n";

$product_id = create_product($token);
if (!$product_id) {
    die("Failed to create PayPal product. Aborting.\n");
}

$plan_id = create_plan($token, $product_id);
if (!$plan_id) {
    die("Failed to create PayPal plan. Aborting.\n");
}

echo "\n=============================================\n";
echo "Setup Complete!\n";
echo "\n";
echo "Your PayPal Plan ID is: " . $plan_id . "\n";
echo "\n";
echo "IMPORTANT: You must now set this Plan ID as an environment variable.\n";
echo "Set the following in your environment (e.g., Codespaces secrets):\n";
echo "PAYPAL_PLAN_ID=" . $plan_id . "\n";
echo "=============================================\n";

?>