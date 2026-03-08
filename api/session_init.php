<?php
/**
 * Centralized session initialization with support for long-lived sessions.
 */
function init_session($remember = null) {
    if (session_status() === PHP_SESSION_NONE) {
        $lifetime = 30 * 24 * 60 * 60; // 30 days in seconds
        $session_path = __DIR__ . '/../sessions';

        // Ensure custom session directory exists
        if (!is_dir($session_path)) {
            mkdir($session_path, 0700, true);
        }
        // Use custom session path to avoid system-level garbage collection (e.g. cron)
        // which often ignores session.gc_maxlifetime and deletes files older than 24 mins.
        session_save_path($session_path);

        $is_https = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
        $cookie_name = 'remember_me_flag';

        // If $remember is null, check if the flag cookie exists
        if ($remember === null) {
            $remember = isset($_COOKIE[$cookie_name]) && $_COOKIE[$cookie_name] === '1';
        }

        if ($remember) {
            // Set session cookie parameters before session_start()
            session_set_cookie_params([
                'lifetime' => $lifetime,
                'path' => '/',
                'domain' => '',
                'secure' => $is_https,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);

            // Set garbage collection max lifetime to match
            ini_set('session.gc_maxlifetime', $lifetime);
            // Ensure cookie persists across browser restarts
            ini_set('session.cookie_lifetime', $lifetime);
        } else {
            // Standard session parameters
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => $is_https,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }

        session_start();

        // If $remember was explicitly passed as true (e.g. during login), set the flag cookie
        if ($remember === true) {
            setcookie($cookie_name, '1', time() + $lifetime, '/', '', $is_https, true);
        }
    }
}
?>
