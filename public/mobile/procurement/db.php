<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
$host = 'localhost';
$dbname = 'erp_ksps';
$username_db = 'nirone99'; // Sesuaikan username database Anda (default XAMPP: root)
$password_db = '0hgitu';     // Sesuaikan password database Anda (default XAMPP: kosong)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username_db, $password_db);
    // Set error mode PDO ke exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
?>