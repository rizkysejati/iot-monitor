<?php
// includes/csrf.php - perlindungan CSRF (Cross-Site Request Forgery)
// Ide: setiap form membawa token rahasia yang disimpan di session. Situs lain tidak
// bisa menebak token itu, jadi tidak bisa memalsukan permintaan atas nama admin.

// Ambil token milik session ini (dibuat sekali, acak dan sulit ditebak)
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

// Kolom tersembunyi untuk ditaruh di dalam <form method="post">
function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

// Cocokkan token yang dikirim form dengan token di session (hash_equals = aman dari timing attack)
function csrf_valid(): bool {
    $kirim = $_POST['csrf'] ?? '';
    return is_string($kirim)
        && !empty($_SESSION['csrf'])
        && hash_equals($_SESSION['csrf'], $kirim);
}
