<?php
// api/terima_data.php
// Endpoint penerima data dari alat IoT (misalnya ESP32).
// Contoh: /iot-monitor/api/terima_data.php?api_key=XXXX&id_sensor=1&nilai=31.5
// Tidak memakai auth_check.php karena pemanggilnya alat, bukan admin yang login.

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

// Kirim balasan JSON lalu hentikan script
function balas(int $kode, string $status, string $pesan): void {
    http_response_code($kode);
    echo json_encode(['status' => $status, 'pesan' => $pesan]);
    exit;
}

if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'], true)) {
    balas(405, 'error', 'Metode tidak diizinkan.');
}

// Ambil input dari POST atau GET (POST diutamakan)
$in       = $_POST + $_GET;
$apiKey   = trim((string)($in['api_key'] ?? ''));
$idSensor = trim((string)($in['id_sensor'] ?? ''));
$nilai    = str_replace(',', '.', trim((string)($in['nilai'] ?? '')));

try {
    // 1. Cek API key (prepared statement)
    $stmt = $pdo->prepare("SELECT id, status FROM perangkat WHERE api_key = :key");
    $stmt->execute([':key' => $apiKey]);
    $perangkat = $stmt->fetch();

    if ($apiKey === '' || !$perangkat) {
        balas(401, 'error', 'API key salah.');
    }
    if ($perangkat['status'] !== 'aktif') {
        balas(403, 'error', 'Perangkat nonaktif.');
    }

    // 2. Validasi input
    if (!ctype_digit($idSensor)) {
        balas(422, 'error', 'id_sensor harus berupa angka.');
    }
    if (!preg_match('/^-?\d{1,8}(\.\d{1,6})?$/', $nilai)) {
        balas(422, 'error', 'nilai harus berupa angka.');
    }

    // 3. Pastikan sensor ini milik perangkat pemilik API key
    $stmt = $pdo->prepare("SELECT id FROM sensor WHERE id = :s AND id_perangkat = :p");
    $stmt->execute([':s' => (int)$idSensor, ':p' => $perangkat['id']]);
    if (!$stmt->fetch()) {
        balas(404, 'error', 'Sensor tidak ditemukan pada perangkat ini.');
    }

    // 4. TRANSACTION: simpan pembacaan + update terakhir_aktif (harus sukses keduanya)
    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        "INSERT INTO pembacaan (id_sensor, nilai, waktu_baca) VALUES (:s, :n, NOW())"
    );
    $stmt->execute([':s' => (int)$idSensor, ':n' => $nilai]);

    $stmt = $pdo->prepare("UPDATE perangkat SET terakhir_aktif = NOW() WHERE id = :p");
    $stmt->execute([':p' => $perangkat['id']]);

    $pdo->commit();

    balas(201, 'ok', 'Data tersimpan.');

} catch (PDOException $e) {
    // Kalau salah satu query gagal, batalkan semuanya
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('API terima_data gagal: ' . $e->getMessage());
    balas(500, 'error', 'Terjadi kesalahan pada server.');
}
