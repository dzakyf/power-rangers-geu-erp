<?php
// 1. Mulai session
session_start();

// 2. Hapus semua variabel session
$_SESSION = array();

// 3. Hancurkan session di server
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// 4. Redirect kembali ke halaman login.php
header("Location: login.php");
exit;
?>