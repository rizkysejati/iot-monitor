<?php
// tools/buat_admin.php - membuat hash password untuk tabel admin
// Jalankan: php tools/buat_admin.php katasandi
$password = $argv[1] ?? 'admin123';
echo password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]) . "\n";