<?php
// includes/auth_check.php
// Taruh require file ini di baris paling atas SETIAP halaman yang butuh login.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    header('Location: /iot-monitor/auth/login.php');
    exit;
}
