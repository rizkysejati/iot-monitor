<?php
// sensor/tambah.php
require __DIR__ . '/../includes/auth_check.php';
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/functions.php';

try {
    $perangkat = daftar_perangkat($pdo);
} catch (PDOException $e) {
    error_log('Tambah sensor: ' . $e->getMessage());
    $perangkat = [];
}
if (!$perangkat) {
    set_flash('err', 'Belum ada perangkat. Tambahkan perangkat terlebih dahulu.');
    header('Location: ../perangkat/tambah.php');
    exit;
}

$errors = [];
$v = ['id_perangkat' => '', 'nama_sensor' => '', 'jenis' => '', 'satuan' => '', 'lokasi' => '', 'batas_min' => '', 'batas_max' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($v as $k => $_) { $v[$k] = trim($_POST[$k] ?? ''); }
    $errors = validasi_sensor($v, $perangkat);

    if (!$errors) {
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO sensor (id_perangkat, nama_sensor, jenis, satuan, lokasi, batas_min, batas_max)
                 VALUES (:idp, :nama, :jenis, :satuan, :lokasi, :bmin, :bmax)"
            );
            $stmt->execute([
                ':idp' => (int)$v['id_perangkat'], ':nama' => $v['nama_sensor'],
                ':jenis' => $v['jenis'], ':satuan' => $v['satuan'], ':lokasi' => $v['lokasi'],
                ':bmin' => angka_atau_null($v['batas_min']), ':bmax' => angka_atau_null($v['batas_max']),
            ]);
            set_flash('ok', 'Sensor berhasil ditambahkan.');
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {   // melanggar UNIQUE (id_perangkat, nama_sensor)
                $errors['nama_sensor'] = 'Nama sensor sudah dipakai pada perangkat ini.';
            } else {
                error_log('Insert sensor: ' . $e->getMessage());
                $errors['umum'] = 'Gagal menyimpan data.';
            }
        }
    }
}

$judul = 'Tambah Sensor'; $subjudul = 'Sensor / Tambah'; $menu = 'sensor';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/_form.php';
require __DIR__ . '/../includes/footer.php';
