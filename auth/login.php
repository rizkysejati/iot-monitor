<?php
// auth/login.php
session_start();
require __DIR__ . '/../config/database.php';

// Kalau sudah login, tidak perlu lihat halaman login lagi
if (isset($_SESSION['admin_id'])) {
    header('Location: ../dashboard.php');
    exit;
}

$error    = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        try {
            // Prepared statement: input pengguna tidak pernah disambung ke SQL
            $stmt = $pdo->prepare(
                "SELECT id, username, password, nama_lengkap
                 FROM admin WHERE username = :username LIMIT 1"
            );
            $stmt->execute([':username' => $username]);
            $admin = $stmt->fetch();

            // Bandingkan password yang diketik dengan hash di database
            if ($admin && password_verify($password, $admin['password'])) {
                session_regenerate_id(true);   // ganti ID session (anti session fixation)
                $_SESSION['admin_id']   = $admin['id'];
                $_SESSION['admin_nama'] = $admin['nama_lengkap'];
                header('Location: ../dashboard.php');
                exit;
            }

            // Pesan sengaja sama untuk username salah maupun password salah
            $error = 'Username atau password salah.';
        } catch (PDOException $e) {
            error_log('Login error: ' . $e->getMessage());
            $error = 'Terjadi kesalahan pada server.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - IoT Monitor</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-card">
        <div class="logo">IoT</div>
        <h1>IoT Monitor</h1>
        <p class="sub">Masuk sebagai Admin</p>

        <?php if ($error !== ''): ?>
            <div class="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" autocomplete="off">
            <label for="username">Username</label>
            <input type="text" id="username" name="username"
                   value="<?= htmlspecialchars($username) ?>"
                   placeholder="Masukkan username" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password"
                   placeholder="Masukkan password" required>

            <button type="submit">Masuk</button>
        </form>
    </div>
</body>
</html>
