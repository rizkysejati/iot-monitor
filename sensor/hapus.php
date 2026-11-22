<?php
// sensor/hapus.php - hanya menerima POST
require __DIR__ . '/../includes/auth_check.php';
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);

try {
    $stmt = $pdo->prepare("DELETE FROM sensor WHERE id = :id");
    $stmt->execute([':id' => $id]);
    if ($stmt->rowCount() > 0) {
        set_flash('ok', 'Sensor berhasil dihapus.');
    } else {
        set_flash('err', 'Data sensor tidak ditemukan.');
    }
} catch (PDOException $e) {
    error_log('Hapus sensor: ' . $e->getMessage());
    set_flash('err', 'Gagal menghapus data.');
}
header('Location: index.php');
exit;
