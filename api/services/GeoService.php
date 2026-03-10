<?php

class GeoService {
    /**
     * Gets the ISO country code for a given IP address.
     *
     * @param string $ip_address
     * @return string|null 2-letter ISO country code or null if not found.
     */
    public static function getCountryCode($ip_address) {
        if ($ip_address === 'Unknown' || $ip_address === '127.0.0.1' || $ip_address === '::1' || empty($ip_address)) {
            return null;
        }

        try {
            $ch = curl_init();
            // Using ip-api.com as it's more reliable in the current environment
            curl_setopt($ch, CURLOPT_URL, "http://ip-api.com/json/{$ip_address}");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 2);
            $geo_data = curl_exec($ch);
            curl_close($ch);

            if ($geo_data) {
                $geo_json = json_decode($geo_data, true);
                // 'countryCode' field in ip-api.com is the 2-letter ISO code
                if (isset($geo_json['status']) && $geo_json['status'] === 'success') {
                    return $geo_json['countryCode'] ?? null;
                }
            }
        } catch (Exception $e) {
            // Silently fail to not block login
        }

        return null;
    }
}
