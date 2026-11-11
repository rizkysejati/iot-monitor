<?php
header('Content-Type: text/html; charset=utf-8');
require __DIR__ . '/config/database.php';

echo "Koneksi PDO berhasil ✅<br>";

$stmt = $pdo->query("SELECT COUNT(*) AS total FROM perangkat");
$row  = $stmt->fetch();

echo "Jumlah perangkat di database: " . htmlspecialchars($row['total']);
