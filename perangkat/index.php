<?php
// perangkat/index.php - daftar perangkat + pencarian + pagination
require __DIR__ . '/../includes/auth_check.php';
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/functions.php';

$perHalaman = 10;
$cari       = trim($_GET['cari'] ?? '');
$halaman    = max(1, (int)($_GET['halaman'] ?? 1));
$like       = '%' . $cari . '%';
$data = []; $total = 0; $galat = '';

try {
    // 1) Hitung total data (untuk menentukan jumlah halaman)
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM perangkat
         WHERE nama_perangkat LIKE :c1 OR lokasi LIKE :c2"
    );
    $stmt->execute([':c1' => $like, ':c2' => $like]);
    $total = (int)$stmt->fetchColumn();

    $totalHalaman = max(1, (int)ceil($total / $perHalaman));
    $halaman      = min($halaman, $totalHalaman);
    $offset       = ($halaman - 1) * $perHalaman;

    // 2) Ambil data untuk halaman ini (LIMIT / OFFSET)
    $stmt = $pdo->prepare(
        "SELECT id, nama_perangkat, lokasi, api_key, status, terakhir_aktif
         FROM perangkat
         WHERE nama_perangkat LIKE :c1 OR lokasi LIKE :c2
         ORDER BY id DESC
         LIMIT :limit OFFSET :offset"
    );
    $stmt->bindValue(':c1', $like);
    $stmt->bindValue(':c2', $like);
    $stmt->bindValue(':limit', $perHalaman, PDO::PARAM_INT);   // LIMIT harus integer
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Perangkat index: ' . $e->getMessage());
    $galat = 'Data gagal dimuat.';
    $totalHalaman = 1;
}

function url_halaman(string $cari, int $h): string {
    return '?' . http_build_query(['cari' => $cari, 'halaman' => $h]);
}

$judul    = 'Data Perangkat';
$subjudul = 'Kelola perangkat IoT';
$menu     = 'perangkat';
require __DIR__ . '/../includes/header.php';
?>
<div class="toolbar">
    <form method="get" class="search">
        <input type="text" name="cari" value="<?= e($cari) ?>" placeholder="Cari nama atau lokasi...">
        <button class="btn btn-outline" type="submit">Cari</button>
    </form>
    <a class="btn btn-orange" href="tambah.php">+ Tambah Perangkat</a>
</div>

<?php if ($galat): ?><div class="flash flash-err"><?= e($galat) ?></div><?php endif; ?>

<div class="card tbl-wrap">
<table class="tbl">
    <thead>
        <tr><th>No</th><th>Nama Perangkat</th><th>Lokasi</th><th>API Key</th><th>Status</th><th>Terakhir Aktif</th><th>Aksi</th></tr>
    </thead>
    <tbody>
    <?php if (!$data): ?>
        <tr><td colspan="7" class="kosong">Tidak ada data.</td></tr>
    <?php endif; ?>
    <?php foreach ($data as $i => $r): ?>
        <tr>
            <td><?= $offset + $i + 1 ?></td>
            <td><?= e($r['nama_perangkat']) ?></td>
            <td><?= e($r['lokasi']) ?></td>
            <td><code><?= e(substr($r['api_key'], 0, 8)) ?>••••••••</code></td>
            <td><span class="badge <?= $r['status'] === 'aktif' ? 'ok' : 'bad' ?>"><?= e(ucfirst($r['status'])) ?></span></td>
            <td><?= e(fmt_tanggal($r['terakhir_aktif'])) ?></td>
            <td class="aksi">
                <a href="ubah.php?id=<?= (int)$r['id'] ?>">Ubah</a>
                <form method="post" action="hapus.php" class="inline"
                      onsubmit="return confirm('Hapus perangkat ini? Semua sensor dan data pembacaannya ikut terhapus.');">
                    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="link-danger">Hapus</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>

<div class="pag-bar">
    <span>Total <?= $total ?> data</span>
    <div class="pag">
        <?php if ($halaman > 1): ?><a href="<?= e(url_halaman($cari, $halaman - 1)) ?>">‹</a><?php endif; ?>
        <?php for ($i = max(1, $halaman - 2); $i <= min($totalHalaman, $halaman + 2); $i++): ?>
            <a class="<?= $i === $halaman ? 'aktif' : '' ?>" href="<?= e(url_halaman($cari, $i)) ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($halaman < $totalHalaman): ?><a href="<?= e(url_halaman($cari, $halaman + 1)) ?>">›</a><?php endif; ?>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
