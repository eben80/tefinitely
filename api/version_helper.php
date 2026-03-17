<?php
/**
 * Helper function to append a version number to asset URLs based on file modification time.
 * This prevents browser caching issues when files are updated.
 *
 * @param string $path The path to the asset relative to the site root.
 * @return string The asset URL with a version query parameter, starting with a leading slash.
 */
function asset_v($path) {
    $cleanPath = ltrim($path, '/');
    $fullPath = __DIR__ . '/../' . $cleanPath;
    if (file_exists($fullPath)) {
        $version = filemtime($fullPath);
        return '/' . $cleanPath . (strpos($cleanPath, '?') === false ? '?' : '&') . 'v=' . $version;
    }
    return '/' . $cleanPath;
}
?>
