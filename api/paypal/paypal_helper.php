<?php
// api/paypal/paypal_helper.php

require_once __DIR__ . '/../../db/paypal_config.php';

/**
 * Get a standard OAuth 2.0 Access Token from PayPal
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
    if (curl_errno($ch)) { return null; }
    curl_close($ch);
    $json = json_decode($result);
    return $json->access_token ?? null;
}

/**
 * Get a Client Token for initialized the v6 Web SDK
 * Requires the domain(s) where the SDK will be used.
 */
function get_paypal_client_token($domains = []) {
    if (empty($domains)) {
        // Default to current domain if not provided
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $domains[] = $protocol . $_SERVER['HTTP_HOST'];
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, PAYPAL_API_BASE_URL . '/v1/oauth2/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);

    $post_fields = [
        'grant_type' => 'client_credentials',
        'response_type' => 'client_token'
    ];

    // Add domains to post fields correctly
    $post_data = http_build_query($post_fields);
    foreach ($domains as $domain) {
        $post_data .= '&domains[]=' . urlencode($domain);
    }

    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_USERPWD, PAYPAL_CLIENT_ID . ':' . PAYPAL_CLIENT_SECRET);

    $headers = [
        'Accept: application/json',
        'Accept-Language: en_US',
        'Content-Type: application/x-www-form-urlencoded'
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    if (curl_errno($ch)) { return null; }
    curl_close($ch);

    $json = json_decode($result);
    return $json->access_token ?? null; // For response_type=client_token, it returns it in access_token field
}
?>
