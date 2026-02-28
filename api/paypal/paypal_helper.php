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
        // Default to current host if not provided
        $host = $_SERVER['HTTP_HOST'];
        // Remove port if present
        if (($pos = strpos($host, ':')) !== false) {
            $host = substr($host, 0, $pos);
        }
        $domains[] = $host;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, PAYPAL_API_BASE_URL . '/v1/oauth2/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);

    // For v6 token, PayPal expects response_type=client_token
    // And domains[] should typically be just the hostname(s)
    $body = 'grant_type=client_credentials&response_type=client_token';
    foreach ($domains as $domain) {
        $body .= '&domains[]=' . urlencode($domain);
    }

    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_USERPWD, PAYPAL_CLIENT_ID . ':' . PAYPAL_CLIENT_SECRET);

    $headers = [
        'Accept: application/json',
        'Accept-Language: en_US',
        'Content-Type: application/x-www-form-urlencoded'
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        $error = curl_error($ch);
        error_log("PayPal cURL error: " . $error);
        return ['error' => "cURL error: $error"];
    }
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200) {
        error_log("PayPal API error: Status $http_code. Response: $result");
        return ['error' => "PayPal API error ($http_code): $result"];
    }

    $json = json_decode($result);
    if (isset($json->access_token)) {
        return ['token' => $json->access_token];
    } else {
        error_log("PayPal response missing access_token: " . $result);
        return ['error' => "Token missing in response"];
    }
}
?>
