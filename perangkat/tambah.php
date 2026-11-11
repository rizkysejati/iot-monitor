<?php
// perangkat/tambah.php
require __DIR__ . '/../includes/auth_check.php';
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/functions.php';

$errors = [];
$v = ['nama_perangkat' => '', 'lokasi' => '', 'deskripsi' => '', 'status' => 'aktif'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($v as $k => $_) { $v[$k] = trim($_POST[$k] ?? ''); }
    $errors = validasi_perangkat($v);

    if (!$errors) {
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO perangkat (nama_perangkat, lokasi, deskripsi, api_key, status)
                 VALUES (:nama, :lokasi, :deskripsi, :api_key, :status)"
            );
            $stmt->execute([
                ':nama'      => $v['nama_perangkat'],
                ':lokasi'    => $v['lokasi'],
                ':deskripsi' => $v['deskripsi'],
                ':api_key'   => bin2hex(random_bytes(16)),   // 32 karakter acak
                ':status'    => $v['status'],
            ]);
            set_flash('ok', 'Perangkat berhasil ditambahkan.');
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            error_log('Tambah perangkat: ' . $e->getMessage());
            $errors['umum'] = 'Gagal menyimpan data.';
        }
    }
}

$judul = 'Tambah Perangkat'; $subjudul = 'Perangkat / Tambah'; $menu = 'perangkat'; $mode = 'tambah';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/_form.php';
require __DIR__ . '/../includes/footer.php';
