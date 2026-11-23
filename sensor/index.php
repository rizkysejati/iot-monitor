<?php
// sensor/index.php - daftar sensor (JOIN dengan perangkat) + pencarian + filter + pagination
require __DIR__ . '/../includes/auth_check.php';
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/functions.php';

$perHalaman = 10;
$cari       = trim($_GET['cari'] ?? '');
$fp         = (int)($_GET['perangkat'] ?? 0);      // filter perangkat (0 = semua)
$halaman    = max(1, (int)($_GET['halaman'] ?? 1));
$like       = '%' . $cari . '%';

// Bagian WHERE dibuat dari teks tetap; nilai input selalu lewat parameter
$where  = "WHERE (s.nama_sensor LIKE :c1 OR s.jenis LIKE :c2 OR s.lokasi LIKE :c3 OR p.nama_perangkat LIKE :c4)";
$params = [':c1' => $like, ':c2' => $like, ':c3' => $like, ':c4' => $like];
if ($fp > 0) {
    $where .= " AND s.id_perangkat = :fp";
    $params[':fp'] = $fp;
}

$data = []; $listPerangkat = []; $total = 0; $totalHalaman = 1; $offset = 0; $galat = '';

try {
    $listPerangkat = daftar_perangkat($pdo);

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM sensor s JOIN perangkat p ON p.id = s.id_perangkat $where");
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();

    $totalHalaman = max(1, (int)ceil($total / $perHalaman));
    $halaman      = min($halaman, $totalHalaman);
    $offset       = ($halaman - 1) * $perHalaman;

    // JOIN: gabungkan sensor dengan perangkat agar nama perangkat ikut tampil
    $stmt = $pdo->prepare(
        "SELECT s.id, s.nama_sensor, s.jenis, s.satuan, s.lokasi, s.batas_min, s.batas_max,
                p.nama_perangkat
         FROM sensor s
         JOIN perangkat p ON p.id = s.id_perangkat
         $where
         ORDER BY s.id DESC
         LIMIT :limit OFFSET :offset"
    );
    foreach ($params as $k => $val) { $stmt->bindValue($k, $val); }
    $stmt->bindValue(':limit', $perHalaman, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Sensor index: ' . $e->getMessage());
    $galat = 'Data gagal dimuat.';
}

function url_halaman(string $cari, int $fp, int $h): string {
    return '?' . http_build_query(['cari' => $cari, 'perangkat' => $fp, 'halaman' => $h]);
}

$judul = 'Data Sensor'; $subjudul = 'Kelola sensor pada tiap perangkat'; $menu = 'sensor';
require __DIR__ . '/../includes/header.php';
?>
<div class="toolbar">
    <form method="get" class="search">
        <select name="perangkat" aria-label="Filter perangkat">
            <option value="0">Semua perangkat</option>
            <?php foreach ($listPerangkat as $p): ?>
                <option value="<?= (int)$p['id'] ?>" <?= $fp === (int)$p['id'] ? 'selected' : '' ?>><?= e($p['nama_perangkat']) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="text" name="cari" value="<?= e($cari) ?>" placeholder="Cari sensor, jenis, lokasi...">
        <button class="btn btn-outline" type="submit">Cari</button>
    </form>
    <a class="btn btn-orange" href="tambah.php">+ Tambah Sensor</a>
</div>

<?php if ($galat): ?><div class="flash flash-err"><?= e($galat) ?></div><?php endif; ?>

<div class="card tbl-wrap">
<table class="tbl">
    <thead>
        <tr><th>No</th><th>Nama Sensor</th><th>Perangkat</th><th>Jenis</th><th>Satuan</th><th>Lokasi</th><th>Batas Min</th><th>Batas Max</th><th>Aksi</th></tr>
    </thead>
    <tbody>
    <?php if (!$data): ?>
        <tr><td colspan="9" class="kosong">Tidak ada data.</td></tr>
    <?php endif; ?>
    <?php foreach ($data as $i => $r): ?>
        <tr>
            <td><?= $offset + $i + 1 ?></td>
            <td><?= e($r['nama_sensor']) ?></td>
            <td><?= e($r['nama_perangkat']) ?></td>
            <td><?= e($r['jenis']) ?></td>
            <td><?= e($r['satuan']) ?></td>
            <td><?= e($r['lokasi']) ?></td>
            <td><?= e(fmt_angka($r['batas_min'])) ?></td>
            <td><?= e(fmt_angka($r['batas_max'])) ?></td>
            <td class="aksi">
                <a href="ubah.php?id=<?= (int)$r['id'] ?>">Ubah</a>
                <form method="post" action="hapus.php" class="inline"
                      onsubmit="return confirm('Hapus sensor ini? Semua data pembacaannya ikut terhapus.');">
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
        <?php if ($halaman > 1): ?><a href="<?= e(url_halaman($cari, $fp, $halaman - 1)) ?>">‹</a><?php endif; ?>
        <?php for ($i = max(1, $halaman - 2); $i <= min($totalHalaman, $halaman + 2); $i++): ?>
            <a class="<?= $i === $halaman ? 'aktif' : '' ?>" href="<?= e(url_halaman($cari, $fp, $i)) ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($halaman < $totalHalaman): ?><a href="<?= e(url_halaman($cari, $fp, $halaman + 1)) ?>">›</a><?php endif; ?>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
