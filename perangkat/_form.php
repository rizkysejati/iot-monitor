<?php
// perangkat/_form.php - form yang dipakai bersama oleh tambah.php dan ubah.php
// Variabel yang dibutuhkan: $v (nilai isian), $errors, $mode ('tambah' / 'ubah')
?>
<form method="post" class="card form-card">
    <?php if (isset($errors['umum'])): ?><div class="flash flash-err"><?= e($errors['umum']) ?></div><?php endif; ?>

    <div class="field">
        <label>Nama Perangkat *</label>
        <input type="text" name="nama_perangkat" maxlength="100" value="<?= e($v['nama_perangkat']) ?>" required>
        <?php if (isset($errors['nama_perangkat'])): ?><small class="err"><?= e($errors['nama_perangkat']) ?></small><?php endif; ?>
    </div>
    <div class="field">
        <label>Lokasi</label>
        <input type="text" name="lokasi" maxlength="100" value="<?= e($v['lokasi']) ?>">
        <?php if (isset($errors['lokasi'])): ?><small class="err"><?= e($errors['lokasi']) ?></small><?php endif; ?>
    </div>
    <div class="field">
        <label>Deskripsi</label>
        <textarea name="deskripsi" rows="3"><?= e($v['deskripsi']) ?></textarea>
    </div>
    <div class="field">
        <label>Status</label>
        <select name="status">
            <option value="aktif" <?= $v['status'] === 'aktif' ? 'selected' : '' ?>>Aktif</option>
            <option value="nonaktif" <?= $v['status'] === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
        </select>
        <?php if (isset($errors['status'])): ?><small class="err"><?= e($errors['status']) ?></small><?php endif; ?>
    </div>
    <?php if ($mode === 'ubah'): ?>
    <div class="field">
        <label>API Key (otomatis, tidak bisa diubah)</label>
        <input type="text" value="<?= e($apiKey) ?>" readonly>
    </div>
    <?php else: ?>
        <p class="hint">API key dibuat otomatis saat data disimpan.</p>
    <?php endif; ?>

    <div class="actions">
        <button class="btn" type="submit">Simpan</button>
        <a class="btn btn-outline" href="index.php">Batal</a>
    </div>
</form>
