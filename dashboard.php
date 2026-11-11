<?php
require __DIR__ . '/includes/auth_check.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - IoT Monitor</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Dashboard</h1>
        <p>Halo, <strong><?= htmlspecialchars($_SESSION['admin_nama']) ?></strong>. Login berhasil ✅</p>
        <p>(Halaman ini masih sementara. Isinya kita buat di langkah berikutnya.)</p>
        <a class="btn-link" href="auth/logout.php">Keluar</a>
    </div>
</body>
</html>
