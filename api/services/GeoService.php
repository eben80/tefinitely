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
            curl_setopt($ch, CURLOPT_URL, "https://ipapi.co/{$ip_address}/json/");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 2);
            $geo_data = curl_exec($ch);
            curl_close($ch);

            if ($geo_data) {
                $geo_json = json_decode($geo_data, true);
                // 'country' field in ipapi.co is the 2-letter ISO code
                return $geo_json['country'] ?? null;
            }
        } catch (Exception $e) {
            // Silently fail to not block login
        }

        return null;
    }
}
