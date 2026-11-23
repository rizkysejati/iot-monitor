<?php
// tools/simulator_esp32.php - simulator alat IoT (ESP32 palsu) untuk demo
// Membaca sensor dari database, lalu mengirim nilai acak ke api/terima_data.php
// seperti yang dilakukan alat sungguhan. Sebagian nilai sengaja melewati batas
// supaya penandaan "Bahaya" di dashboard terlihat.
//
// Cara pakai (di terminal, dari folder project):
//   php tools/simulator_esp32.php                 kirim tiap 5 detik, tanpa henti
//   php tools/simulator_esp32.php 3               kirim tiap 3 detik
//   php tools/simulator_esp32.php 3 http://localhost/iot-monitor 4
//                                                 jeda 3 detik, alamat dasar, berhenti setelah 4 putaran
// Hentikan dengan Ctrl+C.

if (PHP_SAPI !== 'cli') {
    exit('Skrip ini hanya untuk dijalankan lewat terminal.');
}

require __DIR__ . '/../config/database.php';

$jeda    = max(1, (int)($argv[1] ?? 5));
$alamat  = rtrim($argv[2] ?? 'http://localhost/iot-monitor', '/');
$putaran = max(0, (int)($argv[3] ?? 0));      // 0 = tanpa henti

// Nilai acak di sekitar rentang normal sensor (kadang sedikit di luar batas)
function nilai_acak(?string $min, ?string $max): float {
    $min    = $min === null ? 20.0 : (float)$min;
    $max    = $max === null ? 35.0 : (float)$max;
    $tengah = ($min + $max) / 2;
    $lebar  = max(1.0, ($max - $min) / 2);
    return round($tengah + mt_rand(-110, 110) / 100 * $lebar, 2);
}

// Kirim satu pembacaan ke endpoint, kembalikan ringkasan balasannya
function kirim(string $url): string {
    $konteks = stream_context_create(['http' => ['timeout' => 5, 'ignore_errors' => true]]);
    $balas   = @file_get_contents($url, false, $konteks);
    if ($balas === false) {
        return 'GAGAL terhubung (Apache sudah menyala? alamat benar?)';
    }
    $j = json_decode($balas, true);
    return is_array($j) ? ($j['status'] . ' - ' . $j['pesan']) : 'balasan tidak dikenal';
}

// Hanya perangkat berstatus aktif yang diterima endpoint
try {
    $daftar = $pdo->query(
        "SELECT s.id, s.nama_sensor, s.batas_min, s.batas_max, p.nama_perangkat, p.api_key
         FROM sensor s
         JOIN perangkat p ON p.id = s.id_perangkat
         WHERE p.status = 'aktif'
         ORDER BY p.id, s.id"
    )->fetchAll();
} catch (PDOException $e) {
    exit("Gagal membaca database.\n");
}
if (!$daftar) {
    exit("Tidak ada sensor pada perangkat aktif. Tambahkan sensor dulu atau aktifkan perangkatnya.\n");
}

echo "Simulator ESP32 aktif: " . count($daftar) . " sensor, jeda {$jeda} detik, tujuan {$alamat}\n";
echo "Tekan Ctrl+C untuk berhenti.\n\n";

for ($i = 1; $putaran === 0 || $i <= $putaran; $i++) {
    foreach ($daftar as $s) {
        $nilai = nilai_acak($s['batas_min'], $s['batas_max']);
        $url   = $alamat . '/api/terima_data.php?' . http_build_query([
            'api_key'   => $s['api_key'],
            'id_sensor' => $s['id'],
            'nilai'     => $nilai,
        ]);
        printf("[%s] %-18s | %-22s | nilai %7.2f -> %s\n",
            date('H:i:s'), $s['nama_perangkat'], $s['nama_sensor'], $nilai, kirim($url));
    }
    if ($putaran === 0 || $i < $putaran) {
        sleep($jeda);
    }
}
echo "\nSelesai.\n";
