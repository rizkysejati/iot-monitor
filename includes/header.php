<?php
// includes/header.php - layout atas (sidebar + topbar)
// Sebelum require, set dulu: $judul (judul halaman) dan $menu (menu aktif)
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/functions.php';
$base  = '/iot-monitor';
$judul = $judul ?? 'IoT Monitor';
$menu  = $menu ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($judul) ?> - IoT Monitor</title>
    <link rel="stylesheet" href="<?= $base ?>/assets/css/style.css">
</head>
<body class="app">
<aside class="sidebar">
    <div class="brand">IoT Monitor<small>Kelompok 4</small></div>
    <nav>
        <a class="<?= $menu === 'dashboard' ? 'active' : '' ?>" href="<?= $base ?>/dashboard.php">Dashboard</a>
        <a class="<?= $menu === 'perangkat' ? 'active' : '' ?>" href="<?= $base ?>/perangkat/index.php">Perangkat</a>
        <a class="<?= $menu === 'sensor' ? 'active' : '' ?>" href="<?= $base ?>/sensor/index.php">Sensor</a>
    </nav>
    <a class="logout" href="<?= $base ?>/auth/logout.php">Keluar</a>
</aside>
<main class="main">
    <div class="topbar">
        <div>
            <h1><?= e($judul) ?></h1>
            <?php if (!empty($subjudul)): ?><div class="subjudul"><?= e($subjudul) ?></div><?php endif; ?>
        </div>
        <div class="user"><?= e($_SESSION['admin_nama'] ?? 'Admin') ?></div>
    </div>
    <?php if (!empty($_SESSION['flash'])): ?>
        <div class="flash <?= $_SESSION['flash']['tipe'] === 'ok' ? 'flash-ok' : 'flash-err' ?>">
            <?= e($_SESSION['flash']['pesan']) ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>
