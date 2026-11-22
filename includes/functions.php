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

// ===================== PERANGKAT =====================
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

// ======================= SENSOR ======================
// Daftar perangkat untuk dropdown (tanpa input pengguna, jadi query() aman)
function daftar_perangkat(PDO $pdo): array {
    return $pdo->query("SELECT id, nama_perangkat FROM perangkat ORDER BY nama_perangkat")->fetchAll();
}

// Teks angka dari form -> string angka (koma jadi titik) atau null jika kosong
function angka_atau_null(string $s): ?string {
    $s = str_replace(',', '.', trim($s));
    return $s === '' ? null : $s;
}

// Validasi input form sensor. $perangkat = hasil daftar_perangkat()
function validasi_sensor(array $v, array $perangkat): array {
    $err = [];
    $idValid = array_map('intval', array_column($perangkat, 'id'));
    if (!in_array((int)$v['id_perangkat'], $idValid, true)) {
        $err['id_perangkat'] = 'Pilih perangkat yang valid.';
    }
    if ($v['nama_sensor'] === '') {
        $err['nama_sensor'] = 'Nama sensor wajib diisi.';
    } elseif (mb_strlen($v['nama_sensor']) > 100) {
        $err['nama_sensor'] = 'Maksimal 100 karakter.';
    }
    if (mb_strlen($v['jenis']) > 50)  { $err['jenis']  = 'Maksimal 50 karakter.'; }
    if (mb_strlen($v['satuan']) > 20) { $err['satuan'] = 'Maksimal 20 karakter.'; }
    if (mb_strlen($v['lokasi']) > 100){ $err['lokasi'] = 'Maksimal 100 karakter.'; }

    foreach (['batas_min', 'batas_max'] as $k) {
        $n = angka_atau_null($v[$k]);
        if ($n !== null && (!is_numeric($n) || abs((float)$n) >= 100000000)) {
            $err[$k] = 'Isi dengan angka yang valid.';
        }
    }
    if (!isset($err['batas_min']) && !isset($err['batas_max'])) {
        $min = angka_atau_null($v['batas_min']);
        $max = angka_atau_null($v['batas_max']);
        if ($min !== null && $max !== null && (float)$min > (float)$max) {
            $err['batas_max'] = 'Batas maksimum harus lebih besar atau sama dengan batas minimum.';
        }
    }
    return $err;
}

// ====================== DASHBOARD =====================
// Angka 2 desimal dengan titik, contoh 31.40. Nilai kosong (null) ditampilkan '-'
function fmt_angka($n): string {
    return $n === null ? '-' : number_format((float)$n, 2, '.', '');
}

// Status nilai terhadap batas: 'normal', 'tinggi' (> batas_max), atau 'rendah' (< batas_min)
function dash_status($nilai, $min, $max): string {
    if ($max !== null && (float)$nilai > (float)$max) { return 'tinggi'; }
    if ($min !== null && (float)$nilai < (float)$min) { return 'rendah'; }
    return 'normal';
}

// Tanggal harus berformat YYYY-MM-DD dan benar-benar ada, kalau tidak hasilnya null
function dash_tgl_valid(string $s): ?string {
    $d = DateTime::createFromFormat('Y-m-d', $s);
    return ($d && $d->format('Y-m-d') === $s) ? $s : null;
}

// Ambil nilai $_GET sebagai teks (kalau bukan teks dianggap kosong)
function dash_get_teks(string $k): string {
    return (isset($_GET[$k]) && is_string($_GET[$k])) ? trim($_GET[$k]) : '';
}

// Tanggal gaya Indonesia, contoh: 10 Nov 2026 14:00
function fmt_tanggal(?string $waktu, bool $denganTahun = true): string {
    if (!$waktu) { return '-'; }
    $t   = strtotime($waktu);
    $bln = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $s   = date('j', $t) . ' ' . $bln[(int)date('n', $t)];
    if ($denganTahun) { $s .= ' ' . date('Y', $t); }
    return $s . ' ' . date('H:i', $t);
}

// Potongan SQL untuk filter rentang tanggal pada tabel pembacaan ber-alias $a.
// Hasil: [potongan SQL, daftar parameter]. $a selalu alias tetap dari kode kita, bukan input pengguna.
function dash_kondisi_tanggal(string $a, ?string $dari, ?string $sampai): array {
    $sql = '';
    $par = [];
    if ($dari)   { $sql .= " AND $a.waktu_baca >= ?"; $par[] = $dari   . ' 00:00:00'; }
    if ($sampai) { $sql .= " AND $a.waktu_baca <= ?"; $par[] = $sampai . ' 23:59:59'; }
    return [$sql, $par];
}

// Alamat halaman pagination dengan filter ikut terbawa
function dash_url_halaman(int $h, int $perangkat, ?string $dari, ?string $sampai): string {
    $qs = array_filter(['perangkat' => $perangkat ?: null, 'dari' => $dari, 'sampai' => $sampai]);
    return '?' . http_build_query($qs + ['hal' => $h]);
}
