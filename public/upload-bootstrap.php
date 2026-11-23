<?php

if (!function_exists('ensure_upload_dir')) {
    /**
     * Ensure a relative upload directory exists.
     *
     * Legacy upload endpoints run directly from public/, so this helper uses
     * paths relative to the public document root.
     */
    function ensure_upload_dir(string $relativeDir): bool
    {
        $relativeDir = trim(str_replace('\\', '/', $relativeDir), '/');

        if ($relativeDir === '' || strpos($relativeDir, '..') !== false) {
            return false;
        }

        if (is_dir($relativeDir)) {
            return true;
        }

        return @mkdir($relativeDir, 0775, true) || is_dir($relativeDir);
    }
}

if (!function_exists('ensure_upload_dir_or_fail')) {
    function ensure_upload_dir_or_fail(string $relativeDir): void
    {
        if (ensure_upload_dir($relativeDir)) {
            return;
        }

        header('Content-Type: text/json', true, 500);
        echo json_encode([
            'state' => false,
            'message' => 'Gagal membuat direktori upload',
        ]);
        exit;
    }
}
