<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}


// Fungsi Enkripsi ID untuk URL
function encrypt_id($id) {
    $key = "GEU_Secret_Key_2026"; // Kunci rahasia aplikasi
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = openssl_encrypt($id, 'aes-256-cbc', $key, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}

// Fungsi Dekripsi ID dari URL
function decrypt_id($data) {
    $key = "GEU_Secret_Key_2026";
    $decoded = base64_decode($data);
    $parts = explode('::', $decoded, 2);
    if (count($parts) !== 2) return false;
    list($encrypted, $iv) = $parts;
    return openssl_decrypt($encrypted, 'aes-256-cbc', $key, 0, $iv);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GEU - Procurement Mobile</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <style>
        body {
            background-color: #f4f6f9;
            overflow-x: hidden;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-bottom: 70px;
        }
        .navbar-custom {
            background: linear-gradient(135deg, #4b286d, #6d327c);
            color: #fff;
            padding: 10px 20px;
        }
        .navbar-custom .navbar-brand {
            color: #fff !important;
            font-weight: bold;
            font-size: 20px;
            letter-spacing: 1px;
        }
        .navbar-custom .nav-link, .navbar-custom i {
            color: #fff !important;
        }
        .topbar-sub {
            background-color: #2c4a3e;
            color: #fff;
            padding: 8px 20px;
            font-size: 13px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .notification-dropdown {
            min-width: 280px;
            max-width: 320px;
            padding: 0;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            border: none;
        }
        .notification-header {
            background-color: #f8f9fa;
            padding: 10px 15px;
            font-weight: bold;
            font-size: 13px;
            border-bottom: 1px solid #dee2e6;
            color: #333;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .notification-item {
            padding: 10px 15px;
            border-bottom: 1px solid #f1f3f5;
            font-size: 12px;
            color: #333;
            display: block;
            text-decoration: none;
        }
        .notification-item:hover {
            background-color: #f8f9fa;
            text-decoration: none;
        }
        .notification-item .time {
            font-size: 10px;
            color: #888;
            display: block;
            margin-top: 2px;
        }
        .sidebar-drawer {
            position: fixed;
            top: 0;
            left: -300px;
            width: 300px;
            height: 100%;
            background-color: #ffffff;
            box-shadow: 4px 0 15px rgba(0,0,0,0.2);
            transition: all 0.3s ease-in-out;
            z-index: 1050;
            display: flex;
            flex-direction: column;
        }
        .sidebar-drawer.active {
            left: 0;
        }
        .sidebar-header {
            padding: 20px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            text-align: center;
            position: relative;
        }
        .sidebar-header .close-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 20px;
            cursor: pointer;
            color: #333;
        }
        .user-avatar {
            width: 60px;
            height: 60px;
            background-color: #ced4da;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px auto;
            font-weight: bold;
            color: #495057;
            font-size: 12px;
        }
        .sidebar-body {
            flex: 1;
            overflow-y: auto;
        }
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .sidebar-menu > li > a {
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #333;
            text-decoration: none;
            border-bottom: 1px solid #f1f3f5;
            font-size: 14px;
            font-weight: 600;
        }
        .submenu {
            list-style: none;
            padding-left: 0;
            background-color: #fafbfc;
            border-bottom: 1px solid #f1f3f5;
        }
        .submenu li a {
            padding: 10px 20px 10px 40px;
            display: block;
            color: #555;
            font-size: 13px;
            text-decoration: none;
        }
        .submenu li a:hover {
            background-color: #e9ecef;
            color: #0056b3;
        }
        .sidebar-footer {
            padding: 15px 20px;
            border-top: 1px solid #dee2e6;
            background-color: #f8f9fa;
        }
        .btn-logout-sidebar {
            width: 100%;
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px;
            font-weight: 600;
            font-size: 14px;
            transition: background 0.2s;
            text-align: center;
            display: block;
            text-decoration: none;
        }
        .btn-logout-sidebar:hover {
            background-color: #c82333;
            color: white;
            text-decoration: none;
        }
        .bottom-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 60px;
            background-color: #ffffff;
            border-top: 1px solid #dee2e6;
            display: flex;
            justify-content: space-around;
            align-items: center;
            z-index: 1030;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
        }
        .bottom-bar-item {
            text-align: center;
            color: #6c757d;
            text-decoration: none;
            font-size: 11px;
            flex-grow: 1;
            transition: color 0.2s;
        }
        .bottom-bar-item i {
            font-size: 18px;
            display: block;
            margin-bottom: 2px;
        }
        .bottom-bar-item.active, .bottom-bar-item:hover {
            color: #1d5fa8;
            text-decoration: none;
        }
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.4);
            display: none;
            z-index: 1040;
        }
        .sidebar-overlay.active {
            display: block;
        }
        .main-content {
            padding: 20px;
            min-height: 75vh;
        }
    </style>
</head>
<body>