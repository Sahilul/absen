<?php
// Form Section: Data Ayah
$p = $pendaftar;
?>
<div class="grid md:grid-cols-2 gap-5">
    <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Lengkap Ayah <span
                class="text-red-500">*</span></label>
        <input type="text" name="ayah_nama" value="<?= htmlspecialchars($p['ayah_nama'] ?? ''); ?>" required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            placeholder="Nama sesuai KTP">
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">NIK Ayah</label>
        <input type="text" name="ayah_nik" value="<?= htmlspecialchars($p['ayah_nik'] ?? ''); ?>" maxlength="16"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            placeholder="16 digit NIK">
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Tempat Lahir</label>
        <input type="text" name="ayah_tempat_lahir" value="<?= htmlspecialchars($p['ayah_tempat_lahir'] ?? ''); ?>"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Lahir</label>
        <input type="date" name="ayah_tanggal_lahir" value="<?= $p['ayah_tanggal_lahir'] ?? ''; ?>"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Pendidikan Terakhir</label>
        <select name="ayah_pendidikan"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            <option value="">-- Pilih --</option>
            <?php foreach (['Tidak Sekolah', 'SD', 'SMP', 'SMA/SMK', 'D1', 'D2', 'D3', 'S1', 'S2', 'S3'] as $opt): ?>
                <option value="<?= $opt; ?>" <?= ($p['ayah_pendidikan'] ?? '') == $opt ? 'selected' : ''; ?>><?= $opt; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Pekerjaan <span
                class="text-red-500">*</span></label>
        <select name="ayah_pekerjaan" required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            <option value="">-- Pilih --</option>
            <?php foreach (['PNS', 'TNI/Polri', 'Karyawan Swasta', 'Wiraswasta', 'Petani', 'Nelayan', 'Buruh', 'Tidak Bekerja', 'Sudah Meninggal', 'Lainnya'] as $opt): ?>
                <option value="<?= $opt; ?>" <?= ($p['ayah_pekerjaan'] ?? '') == $opt ? 'selected' : ''; ?>><?= $opt; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Penghasilan/Bulan</label>
        <select name="ayah_penghasilan"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            <option value="">-- Pilih --</option>
            <?php foreach (['< Rp 1.000.000', 'Rp 1.000.000 - 3.000.000', 'Rp 3.000.000 - 5.000.000', 'Rp 5.000.000 - 10.000.000', '> Rp 10.000.000'] as $opt): ?>
                <option value="<?= $opt; ?>" <?= ($p['ayah_penghasilan'] ?? '') == $opt ? 'selected' : ''; ?>><?= $opt; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">No. HP/WA</label>
        <input type="tel" name="ayah_no_hp" value="<?= htmlspecialchars($p['ayah_no_hp'] ?? ''); ?>"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            placeholder="08xxx">
    </div>
</div>