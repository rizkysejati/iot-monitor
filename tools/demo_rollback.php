<?php
// tools/demo_rollback.php - peragaan TRANSACTION: commit vs rollBack
// Skenario: INSERT pembacaan berhasil, tetapi query kedua sengaja dibuat gagal.
// Akibatnya rollBack() membatalkan INSERT tadi, jadi tidak ada data setengah jadi.
//
// Cara pakai (di terminal, dari folder project):   php tools/demo_rollback.php

if (PHP_SAPI !== 'cli') {
    exit('Skrip ini hanya untuk dijalankan lewat terminal.');
}

require __DIR__ . '/../config/database.php';

function jumlah_pembacaan(PDO $pdo): int {
    return (int)$pdo->query('SELECT COUNT(*) FROM pembacaan')->fetchColumn();
}

$sensor = $pdo->query('SELECT id, nama_sensor FROM sensor ORDER BY id LIMIT 1')->fetch();
if (!$sensor) {
    exit("Belum ada sensor di database.\n");
}

echo "=== Peragaan TRANSACTION ===\n";
echo "Sensor uji                  : {$sensor['nama_sensor']}\n";
echo "Jumlah pembacaan di awal    : " . jumlah_pembacaan($pdo) . "\n\n";

try {
    $pdo->beginTransaction();
    echo "1. beginTransaction()\n";

    $stmt = $pdo->prepare("INSERT INTO pembacaan (id_sensor, nilai, waktu_baca) VALUES (:s, 99.99, NOW())");
    $stmt->execute([':s' => $sensor['id']]);
    echo "2. INSERT pembacaan berhasil (belum permanen). Jumlah di dalam transaction: " . jumlah_pembacaan($pdo) . "\n";

    // Sengaja salah: kolom 'kolom_palsu' tidak ada, sehingga memicu PDOException
    $pdo->exec("UPDATE perangkat SET kolom_palsu = NOW() WHERE id = 1");
    $pdo->commit();
    echo "3. commit()\n";
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "3. UPDATE gagal -> rollBack() dijalankan, INSERT tadi dibatalkan\n";
}

echo "\nJumlah pembacaan di akhir   : " . jumlah_pembacaan($pdo) . "\n";
echo "Kesimpulan: jumlah akhir sama dengan awal. Semua query dalam transaction berhasil bersama atau batal bersama.\n";
