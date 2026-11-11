<?php
// config/database.php
// Koneksi ke database memakai PDO. File ini di-require dari halaman lain.

$host    = 'localhost';
$db      = 'iot_monitor';
$user    = 'root';
$pass    = '';          // XAMPP bawaan: password root kosong
$charset = 'utf8mb4';

// DSN = alamat dan "resep" koneksi
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    // Error database dilempar sebagai exception (bisa ditangkap try-catch)
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    // Hasil query berupa array asosiatif: $row['nama_sensor']
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // Prepared statement asli dari MySQL (lebih aman)
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // Detail error dicatat di log server, tidak ditampilkan ke pengguna
    error_log('Koneksi DB gagal: ' . $e->getMessage());
    die('Koneksi database gagal. Silakan hubungi administrator.');
}
