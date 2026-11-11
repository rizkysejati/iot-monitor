<?php
// includes/functions.php - fungsi bantu yang dipakai di banyak halaman

// Escape output supaya aman dari XSS
function e($s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

// Simpan pesan sementara (tampil sekali setelah redirect)
function set_flash(string $tipe, string $pesan): void {
    $_SESSION['flash'] = ['tipe' => $tipe, 'pesan' => $pesan];
}

// Validasi input form perangkat, mengembalikan array pesan error
function validasi_perangkat(array $v): array {
    $err = [];
    if ($v['nama_perangkat'] === '') {
        $err['nama_perangkat'] = 'Nama perangkat wajib diisi.';
    } elseif (mb_strlen($v['nama_perangkat']) > 100) {
        $err['nama_perangkat'] = 'Maksimal 100 karakter.';
    }
    if (mb_strlen($v['lokasi']) > 100) {
        $err['lokasi'] = 'Maksimal 100 karakter.';
    }
    if (!in_array($v['status'], ['aktif', 'nonaktif'], true)) {
        $err['status'] = 'Status tidak valid.';
    }
    return $err;
}
