<?php
// sensor/ubah.php
require __DIR__ . '/../includes/auth_check.php';
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/functions.php';

$id = (int)($_GET['id'] ?? 0);

try {
    $perangkat = daftar_perangkat($pdo);
    $stmt = $pdo->prepare("SELECT * FROM sensor WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
} catch (PDOException $e) {
    error_log('Ubah sensor: ' . $e->getMessage());
    $perangkat = []; $row = false;
}
if (!$row) {
    set_flash('err', 'Data sensor tidak ditemukan.');
    header('Location: index.php');
    exit;
}

$errors = [];
$v = [
    'id_perangkat' => (string)$row['id_perangkat'],
    'nama_sensor'  => $row['nama_sensor'],
    'jenis'        => (string)$row['jenis'],
    'satuan'       => (string)$row['satuan'],
    'lokasi'       => (string)$row['lokasi'],
    'batas_min'    => $row['batas_min'] === null ? '' : (string)$row['batas_min'],
    'batas_max'    => $row['batas_max'] === null ? '' : (string)$row['batas_max'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($v as $k => $_) { $v[$k] = trim($_POST[$k] ?? ''); }
    $errors = validasi_sensor($v, $perangkat);

    if (!$errors) {
        try {
            $stmt = $pdo->prepare(
                "UPDATE sensor
                 SET id_perangkat = :idp, nama_sensor = :nama, jenis = :jenis, satuan = :satuan,
                     lokasi = :lokasi, batas_min = :bmin, batas_max = :bmax
                 WHERE id = :id"
            );
            $stmt->execute([
                ':idp' => (int)$v['id_perangkat'], ':nama' => $v['nama_sensor'],
                ':jenis' => $v['jenis'], ':satuan' => $v['satuan'], ':lokasi' => $v['lokasi'],
                ':bmin' => angka_atau_null($v['batas_min']), ':bmax' => angka_atau_null($v['batas_max']),
                ':id' => $id,
            ]);
            set_flash('ok', 'Sensor berhasil diperbarui.');
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $errors['nama_sensor'] = 'Nama sensor sudah dipakai pada perangkat ini.';
            } else {
                error_log('Update sensor: ' . $e->getMessage());
                $errors['umum'] = 'Gagal menyimpan perubahan.';
            }
        }
    }
}

$judul = 'Ubah Sensor'; $subjudul = 'Sensor / Ubah'; $menu = 'sensor';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/_form.php';
require __DIR__ . '/../includes/footer.php';
