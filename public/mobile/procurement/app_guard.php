<?php
// Ganti dengan string acak panjang (minimal 32 karakter), jangan dibagikan
// define('APP_KEY', 'GANTI_DENGAN_STRING_ACAK_PANJANG_MINIMAL_32_KARAKTER');
define('APP_KEY', 'WP8Lk6WvJINW-X%$uS8zuRXLv63F_');

function appAccessToken() {
    return hash_hmac('sha256', 'Procurement-mobile-app', APP_KEY);
}

function appGuard($json = false) {
	// header('Referrer-Policy: no-referrer');
    header('Referrer-Policy: strict-origin-when-cross-origin');
	$json = $json || strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
    $given = $_GET['key'] ?? '';

    // Token benar -> beri cookie akses (khusus folder /mobile/)
    if ($given !== '' && hash_equals(APP_KEY, $given)) {
        setcookie('app_access', appAccessToken(), [
            'expires'  => time() + 60 * 60 * 24 * 365,
            'path'     => '/mobile/',
            'secure'   => true,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        $_COOKIE['app_access'] = appAccessToken();
        return;
    }

    // Tidak ada token di URL -> cek cookie dari kunjungan sebelumnya
    $cookie = $_COOKIE['app_access'] ?? '';
    if ($cookie !== '' && hash_equals(appAccessToken(), $cookie)) {
        return;
    }

    http_response_code(403);
    if ($json) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Akses ditolak.']);
    } else {
        echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Akses Ditolak</title></head>'
           . '<body style="font-family:sans-serif;text-align:center;padding:60px 20px;">'
           . '<h3>Akses Ditolak</h3><p>Halaman ini sudah Tidak Tersedia.</p>
                <p><img src="aksesditolak.jpg" width="200">
           </body></html>';
    }
    exit;
}