<?php
// includes/auth_check.php
// Taruh require file ini di baris paling atas SETIAP halaman yang butuh login.

require_once __DIR__ . '/../config/app.php';   // BASE_URL

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

// Semua permintaan POST (simpan, ubah, hapus) wajib membawa token CSRF yang benar
require_once __DIR__ . '/csrf.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_valid()) {
    http_response_code(403);
    exit('Permintaan ditolak (token keamanan tidak cocok). Muat ulang halaman lalu coba lagi.');
}
