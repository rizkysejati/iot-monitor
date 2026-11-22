<?php
// dashboard.php - ringkasan data sensor
// Isi: filter, data terbaru tiap sensor, statistik AVG/MAX/MIN, riwayat + pagination.
// Fungsi bantu (dash_status, fmt_tanggal, dll) ada di includes/functions.php.

require_once __DIR__ . '/config/database.php';

$judul    = 'Dashboard';
$subjudul = 'Ringkasan data sensor';
$menu     = 'dashboard';
require_once __DIR__ . '/includes/header.php';

$errDb         = '';
$errFilter     = '';
$perangkatList = [];
$kartu         = [];
$statistik     = [];
$riwayat       = [];
$fPerangkat    = 0;
$dari          = null;
$sampai        = null;
$total         = 0;
$perHal        = 10;
$hal           = 1;
$totalHal      = 1;

try {
    // ===================== FILTER =====================
    $perangkatList = daftar_perangkat($pdo);
    $idValid = array_map('intval', array_column($perangkatList, 'id'));

    $fPerangkat = (int)dash_get_teks('perangkat');
    if (!in_array($fPerangkat, $idValid, true)) { $fPerangkat = 0; }

    $dari   = dash_tgl_valid(dash_get_teks('dari'));
    $sampai = dash_tgl_valid(dash_get_teks('sampai'));
    if ($dari && $sampai && $dari > $sampai) {
        $errFilter = 'Tanggal awal tidak boleh melebihi tanggal akhir.';
        $dari = $sampai = null;
    }

    $whereP = $fPerangkat ? ' WHERE s.id_perangkat = ?' : '';
    $paramP = $fPerangkat ? [$fPerangkat] : [];

    // ============ 1. DATA TERBARU TIAP SENSOR ============
    // LEFT JOIN ke pembacaan paling baru milik tiap sensor (subquery + LIMIT 1).
    [$sqlTgl, $paramTgl] = dash_kondisi_tanggal('r2', $dari, $sampai);
    $sql = "SELECT s.id, s.nama_sensor, s.satuan, s.lokasi, s.batas_min, s.batas_max,
                   r.nilai, r.waktu_baca
            FROM sensor s
            JOIN perangkat p ON p.id = s.id_perangkat
            LEFT JOIN pembacaan r ON r.id = (
                SELECT r2.id FROM pembacaan r2
                WHERE r2.id_sensor = s.id $sqlTgl
                ORDER BY r2.waktu_baca DESC, r2.id DESC
                LIMIT 1)
            $whereP
            ORDER BY p.nama_perangkat, s.nama_sensor";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_merge($paramTgl, $paramP));
    $kartu = $stmt->fetchAll();

    // ============ 2. STATISTIK AVG / MAX / MIN PER SENSOR ============
    // Filter tanggal ada di ON (bukan WHERE) supaya sensor tanpa data tetap tampil.
    [$sqlTgl, $paramTgl] = dash_kondisi_tanggal('r', $dari, $sampai);
    $sql = "SELECT s.id, s.nama_sensor, s.satuan, p.nama_perangkat,
                   COUNT(r.id)  AS jumlah,
                   AVG(r.nilai) AS rata,
                   MAX(r.nilai) AS maks,
                   MIN(r.nilai) AS mins,
                   SUM(CASE WHEN (s.batas_max IS NOT NULL AND r.nilai > s.batas_max)
                              OR (s.batas_min IS NOT NULL AND r.nilai < s.batas_min)
                            THEN 1 ELSE 0 END) AS bahaya
            FROM sensor s
            JOIN perangkat p ON p.id = s.id_perangkat
            LEFT JOIN pembacaan r ON r.id_sensor = s.id $sqlTgl
            $whereP
            GROUP BY s.id, s.nama_sensor, s.satuan, p.nama_perangkat
            ORDER BY p.nama_perangkat, s.nama_sensor";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_merge($paramTgl, $paramP));
    $statistik = $stmt->fetchAll();

    // ============ 3. RIWAYAT PEMBACAAN + PAGINATION ============
    $whereH = ' WHERE 1=1' . $sqlTgl . ($fPerangkat ? ' AND s.id_perangkat = ?' : '');
    $paramH = array_merge($paramTgl, $paramP);
    $dariJoin = "FROM pembacaan r
                 JOIN sensor s ON s.id = r.id_sensor
                 JOIN perangkat p ON p.id = s.id_perangkat";

    $stmt = $pdo->prepare("SELECT COUNT(*) $dariJoin $whereH");
    $stmt->execute($paramH);
    $total = (int)$stmt->fetchColumn();

    $totalHal = max(1, (int)ceil($total / $perHal));
    $hal = min(max(1, (int)dash_get_teks('hal')), $totalHal);

    $stmt = $pdo->prepare(
        "SELECT r.id, r.nilai, r.waktu_baca, s.nama_sensor, s.satuan,
                s.batas_min, s.batas_max, p.nama_perangkat
         $dariJoin $whereH
         ORDER BY r.waktu_baca DESC, r.id DESC
         LIMIT ? OFFSET ?"
    );
    $i = 1;
    foreach ($paramH as $v) { $stmt->bindValue($i++, $v); }
    $stmt->bindValue($i++, $perHal, PDO::PARAM_INT);
    $stmt->bindValue($i,   ($hal - 1) * $perHal, PDO::PARAM_INT);
    $stmt->execute();
    $riwayat = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log('Dashboard gagal: ' . $e->getMessage());
    $errDb = 'Data dashboard tidak dapat dimuat. Silakan coba lagi.';
}

$awalData  = $total ? ($hal - 1) * $perHal + 1 : 0;
$akhirData = min($total, $hal * $perHal);
?>

<?php if ($errDb): ?><div class="flash flash-err"><?= e($errDb) ?></div><?php endif; ?>
<?php if ($errFilter): ?><div class="flash flash-err"><?= e($errFilter) ?></div><?php endif; ?>

<!-- Filter -->
<form class="card filter-bar" method="get" action="dashboard.php">
    <div class="field">
        <label for="perangkat">Perangkat</label>
        <select name="perangkat" id="perangkat">
            <option value="0">Semua perangkat</option>
            <?php foreach ($perangkatList as $p): ?>
                <option value="<?= (int)$p['id'] ?>" <?= (int)$p['id'] === $fPerangkat ? 'selected' : '' ?>>
                    <?= e($p['nama_perangkat']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field">
        <label for="dari">Tanggal awal</label>
        <input type="date" name="dari" id="dari" value="<?= e($dari ?? '') ?>">
    </div>
    <div class="field">
        <label for="sampai">Tanggal akhir</label>
        <input type="date" name="sampai" id="sampai" value="<?= e($sampai ?? '') ?>">
    </div>
    <button class="btn" type="submit">Terapkan</button>
    <a class="btn btn-outline" href="dashboard.php">Reset</a>
</form>

<!-- Data terbaru -->
<h2 class="section-title">Data Terbaru</h2>
<?php if (!$kartu): ?>
    <div class="card placeholder">Belum ada sensor.</div>
<?php else: ?>
<div class="grid-stat">
    <?php foreach ($kartu as $k):
        $ada = $k['nilai'] !== null;
        $st  = $ada ? dash_status($k['nilai'], $k['batas_min'], $k['batas_max']) : 'kosong';
        $info = trim(($k['lokasi'] ?? '') . ($k['lokasi'] && $ada ? ' · ' : '') . ($ada ? fmt_tanggal($k['waktu_baca'], false) : ''));
    ?>
    <div class="card sensor-card <?= in_array($st, ['tinggi', 'rendah'], true) ? 'bahaya' : '' ?>">
        <div class="nama"><?= e($k['nama_sensor']) ?></div>
        <div class="nilai"><?= $ada ? e(fmt_angka($k['nilai'])) . ' ' . e($k['satuan']) : '-' ?></div>
        <div class="bawah">
            <?php if ($st === 'normal'): ?>
                <span class="badge ok">Normal</span>
            <?php elseif ($st === 'tinggi'): ?>
                <span class="badge bad" title="Di atas batas maksimum">Bahaya</span>
            <?php elseif ($st === 'rendah'): ?>
                <span class="badge bad" title="Di bawah batas minimum">Bahaya</span>
            <?php else: ?>
                <span class="badge netral">Belum ada data</span>
            <?php endif; ?>
            <span class="meta"><?= e($info) ?></span>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Statistik -->
<h2 class="section-title">Statistik per Sensor</h2>
<div class="card tbl-wrap">
    <table class="tbl">
        <thead>
            <tr><th>Sensor</th><th>Perangkat</th><th>Rata-rata</th><th>Maksimum</th><th>Minimum</th><th>Status</th></tr>
        </thead>
        <tbody>
        <?php if (!$statistik): ?>
            <tr><td colspan="6" class="kosong">Tidak ada data.</td></tr>
        <?php endif; ?>
        <?php foreach ($statistik as $s): $punya = (int)$s['jumlah'] > 0; ?>
            <tr>
                <td><?= e($s['nama_sensor']) ?></td>
                <td><?= e($s['nama_perangkat']) ?></td>
                <td><?= $punya ? e(fmt_angka($s['rata'])) . ' ' . e($s['satuan']) : '-' ?></td>
                <td><?= $punya ? e(fmt_angka($s['maks'])) . ' ' . e($s['satuan']) : '-' ?></td>
                <td><?= $punya ? e(fmt_angka($s['mins'])) . ' ' . e($s['satuan']) : '-' ?></td>
                <td>
                    <?php if (!$punya): ?>
                        <span class="badge netral">Tanpa data</span>
                    <?php elseif ((int)$s['bahaya'] > 0): ?>
                        <span class="badge bad" title="<?= (int)$s['bahaya'] ?> pembacaan di luar batas">Pernah bahaya</span>
                    <?php else: ?>
                        <span class="badge ok">Normal</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Riwayat -->
<h2 class="section-title">Riwayat Pembacaan</h2>
<div class="card tbl-wrap">
    <table class="tbl">
        <thead>
            <tr><th>Waktu</th><th>Sensor</th><th>Perangkat</th><th>Nilai</th><th>Status</th></tr>
        </thead>
        <tbody>
        <?php if (!$riwayat): ?>
            <tr><td colspan="5" class="kosong">Tidak ada pembacaan pada filter ini.</td></tr>
        <?php endif; ?>
        <?php foreach ($riwayat as $r): $st = dash_status($r['nilai'], $r['batas_min'], $r['batas_max']); ?>
            <tr>
                <td><?= e(fmt_tanggal($r['waktu_baca'])) ?></td>
                <td><?= e($r['nama_sensor']) ?></td>
                <td><?= e($r['nama_perangkat']) ?></td>
                <td><?= e(fmt_angka($r['nilai'])) ?> <?= e($r['satuan']) ?></td>
                <td>
                    <?php if ($st === 'normal'): ?>
                        <span class="badge ok">Normal</span>
                    <?php else: ?>
                        <span class="badge bad" title="<?= $st === 'tinggi' ? 'Di atas batas maksimum' : 'Di bawah batas minimum' ?>">Bahaya</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<div class="pag-bar">
    <span>Menampilkan <?= $awalData ?>-<?= $akhirData ?> dari <?= $total ?> pembacaan</span>
    <div class="pag">
        <?php if ($hal > 1): ?>
            <a href="<?= e(dash_url_halaman($hal - 1, $fPerangkat, $dari, $sampai)) ?>" aria-label="Sebelumnya">&lsaquo;</a>
        <?php endif; ?>
        <?php for ($h = max(1, $hal - 2); $h <= min($totalHal, $hal + 2); $h++): ?>
            <a class="<?= $h === $hal ? 'aktif' : '' ?>" href="<?= e(dash_url_halaman($h, $fPerangkat, $dari, $sampai)) ?>"><?= $h ?></a>
        <?php endfor; ?>
        <?php if ($hal < $totalHal): ?>
            <a href="<?= e(dash_url_halaman($hal + 1, $fPerangkat, $dari, $sampai)) ?>" aria-label="Berikutnya">&rsaquo;</a>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
