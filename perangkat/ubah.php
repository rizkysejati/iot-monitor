<?php
// perangkat/ubah.php
require __DIR__ . '/../includes/auth_check.php';
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/functions.php';

$id = (int)($_GET['id'] ?? 0);

try {
    $stmt = $pdo->prepare("SELECT * FROM perangkat WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
} catch (PDOException $e) {
    error_log('Ubah perangkat: ' . $e->getMessage());
    $row = false;
}
if (!$row) {
    set_flash('err', 'Data perangkat tidak ditemukan.');
    header('Location: index.php');
    exit;
}

$errors = [];
$apiKey = $row['api_key'];
$v = [
    'nama_perangkat' => $row['nama_perangkat'],
    'lokasi'         => (string)$row['lokasi'],
    'deskripsi'      => (string)$row['deskripsi'],
    'status'         => $row['status'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($v as $k => $_) { $v[$k] = trim($_POST[$k] ?? ''); }
    $errors = validasi_perangkat($v);

    if (!$errors) {
        try {
            $stmt = $pdo->prepare(
                "UPDATE perangkat
                 SET nama_perangkat = :nama, lokasi = :lokasi, deskripsi = :deskripsi, status = :status
                 WHERE id = :id"
            );
            $stmt->execute([
                ':nama' => $v['nama_perangkat'], ':lokasi' => $v['lokasi'],
                ':deskripsi' => $v['deskripsi'], ':status' => $v['status'], ':id' => $id,
            ]);
            set_flash('ok', 'Perangkat berhasil diperbarui.');
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            error_log('Update perangkat: ' . $e->getMessage());
            $errors['umum'] = 'Gagal menyimpan perubahan.';
        }
    }
}

$judul = 'Ubah Perangkat'; $subjudul = 'Perangkat / Ubah'; $menu = 'perangkat'; $mode = 'ubah';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/_form.php';
require __DIR__ . '/../includes/footer.php';
