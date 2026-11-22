<?php
// sensor/_form.php - form bersama untuk tambah.php dan ubah.php
// Variabel: $v (isian), $errors, $perangkat (untuk dropdown)
?>
<form method="post" class="card form-card">
    <?php if (isset($errors['umum'])): ?><div class="flash flash-err"><?= e($errors['umum']) ?></div><?php endif; ?>

    <div class="form-grid">
        <div class="field full">
            <label>Perangkat *</label>
            <select name="id_perangkat" required>
                <option value="">-- Pilih perangkat --</option>
                <?php foreach ($perangkat as $p): ?>
                    <option value="<?= (int)$p['id'] ?>" <?= (int)$v['id_perangkat'] === (int)$p['id'] ? 'selected' : '' ?>><?= e($p['nama_perangkat']) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['id_perangkat'])): ?><small class="err"><?= e($errors['id_perangkat']) ?></small><?php endif; ?>
        </div>

        <div class="field">
            <label>Nama Sensor *</label>
            <input type="text" name="nama_sensor" maxlength="100" value="<?= e($v['nama_sensor']) ?>" required>
            <?php if (isset($errors['nama_sensor'])): ?><small class="err"><?= e($errors['nama_sensor']) ?></small><?php endif; ?>
        </div>
        <div class="field">
            <label>Jenis</label>
            <input type="text" name="jenis" maxlength="50" list="daftar-jenis" value="<?= e($v['jenis']) ?>">
            <?php if (isset($errors['jenis'])): ?><small class="err"><?= e($errors['jenis']) ?></small><?php endif; ?>
        </div>

        <div class="field">
            <label>Satuan</label>
            <input type="text" name="satuan" maxlength="20" list="daftar-satuan" value="<?= e($v['satuan']) ?>">
            <?php if (isset($errors['satuan'])): ?><small class="err"><?= e($errors['satuan']) ?></small><?php endif; ?>
        </div>
        <div class="field">
            <label>Lokasi</label>
            <input type="text" name="lokasi" maxlength="100" value="<?= e($v['lokasi']) ?>">
            <?php if (isset($errors['lokasi'])): ?><small class="err"><?= e($errors['lokasi']) ?></small><?php endif; ?>
        </div>

        <div class="field">
            <label>Batas Minimum</label>
            <input type="text" name="batas_min" inputmode="decimal" value="<?= e($v['batas_min']) ?>" placeholder="contoh: 18.00">
            <?php if (isset($errors['batas_min'])): ?><small class="err"><?= e($errors['batas_min']) ?></small><?php endif; ?>
        </div>
        <div class="field">
            <label>Batas Maksimum</label>
            <input type="text" name="batas_max" inputmode="decimal" value="<?= e($v['batas_max']) ?>" placeholder="contoh: 30.00">
            <?php if (isset($errors['batas_max'])): ?><small class="err"><?= e($errors['batas_max']) ?></small><?php endif; ?>
        </div>
    </div>
    <p class="hint">Batas minimum dan maksimum dipakai dashboard untuk menandai data Bahaya. Boleh dikosongkan.</p>

    <div class="actions">
        <button class="btn" type="submit">Simpan</button>
        <a class="btn btn-outline" href="index.php">Batal</a>
    </div>
</form>
<datalist id="daftar-jenis"><option value="suhu"><option value="kelembapan"><option value="tekanan"><option value="cahaya"></datalist>
<datalist id="daftar-satuan"><option value="°C"><option value="%"><option value="hPa"><option value="lux"></datalist>
