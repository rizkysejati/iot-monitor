<?php
// includes/header.php - layout atas (sidebar + topbar)
// Sebelum require, set dulu: $judul, $subjudul (opsional), $menu (menu aktif)
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/../config/app.php';
$base  = BASE_URL;
$judul = $judul ?? 'IoT Monitor';
$menu  = $menu ?? '';
$nama  = $_SESSION['admin_nama'] ?? 'Admin';

// Daftar menu sidebar: kunci => [label, alamat, nama ikon]
$menuItems = [
    'dashboard' => ['Dashboard', '/dashboard.php',         'dashboard'],
    'perangkat' => ['Perangkat', '/perangkat/index.php',   'perangkat'],
    'sensor'    => ['Sensor',    '/sensor/index.php',      'sensor'],
];
$cssFile = __DIR__ . '/../assets/css/style.css';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($judul) ?> - IoT Monitor</title>
    <!-- ?v=waktu ubah file: browser selalu memuat CSS terbaru -->
    <link rel="stylesheet" href="<?= $base ?>/assets/css/style.css?v=<?= file_exists($cssFile) ? filemtime($cssFile) : 1 ?>">
</head>
<body class="app">

<!-- Kumpulan ikon (dipakai lewat <use>) -->
<svg width="0" height="0" style="position:absolute" aria-hidden="true">
    <symbol id="i-dashboard" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/></symbol>
    <symbol id="i-perangkat" viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="2"/><rect x="9" y="9" width="6" height="6"/><path d="M15 2v2M15 20v2M2 15h2M2 9h2M20 15h2M20 9h2M9 2v2M9 20v2"/></symbol>
    <symbol id="i-sensor" viewBox="0 0 24 24"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></symbol>
    <symbol id="i-keluar" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/></symbol>
    <symbol id="i-user" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></symbol>
</svg>

<aside class="sidebar">
    <div class="brand">
        <span class="brand-logo"><svg class="ico"><use href="#i-sensor"/></svg></span>
        IoT Monitor
    </div>
    <nav>
        <?php foreach ($menuItems as $kunci => [$label, $url, $ikon]): ?>
            <a class="<?= $menu === $kunci ? 'active' : '' ?>" href="<?= $base . $url ?>" <?= $menu === $kunci ? 'aria-current="page"' : '' ?>>
                <svg class="ico"><use href="#i-<?= $ikon ?>"/></svg><?= e($label) ?>
            </a>
        <?php endforeach; ?>
    </nav>
    <a class="logout" href="<?= $base ?>/auth/logout.php">
        <svg class="ico"><use href="#i-keluar"/></svg>Keluar
    </a>
</aside>
<div class="overlay"></div>
<main class="main">
    <div class="topbar">
        <div class="topbar-left">
            <button class="menu-toggle" type="button" aria-label="Buka menu">☰</button>
            <div>
                <h1><?= e($judul) ?></h1>
                <?php if (!empty($subjudul)): ?><div class="subjudul"><?= e($subjudul) ?></div><?php endif; ?>
            </div>
        </div>
        <div class="user"><svg class="ico"><use href="#i-user"/></svg><?= e($nama) ?></div>
    </div>
    <?php if (!empty($_SESSION['flash'])): ?>
        <div class="flash <?= $_SESSION['flash']['tipe'] === 'ok' ? 'flash-ok' : 'flash-err' ?>" data-auto role="status">
            <?= e($_SESSION['flash']['pesan']) ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

