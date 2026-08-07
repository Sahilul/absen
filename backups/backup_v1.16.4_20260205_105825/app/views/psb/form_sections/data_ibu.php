<?php
// Form Section: Data Ibu
$p = $pendaftar;
?>
<div class="grid md:grid-cols-2 gap-5">
    <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Lengkap Ibu <span
                class="text-red-500">*</span></label>
        <input type="text" name="ibu_nama" value="<?= htmlspecialchars($p['ibu_nama'] ?? ''); ?>" required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500"
            placeholder="Nama sesuai KTP">
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">NIK Ibu</label>
        <input type="text" name="ibu_nik" value="<?= htmlspecialchars($p['ibu_nik'] ?? ''); ?>" maxlength="16"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500"
            placeholder="16 digit NIK">
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Tempat Lahir</label>
        <input type="text" name="ibu_tempat_lahir" value="<?= htmlspecialchars($p['ibu_tempat_lahir'] ?? ''); ?>"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Lahir</label>
        <input type="date" name="ibu_tanggal_lahir" value="<?= $p['ibu_tanggal_lahir'] ?? ''; ?>"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Pendidikan Terakhir</label>
        <select name="ibu_pendidikan"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
            <option value="">-- Pilih --</option>
            <?php foreach (['Tidak Sekolah', 'SD', 'SMP', 'SMA/SMK', 'D1', 'D2', 'D3', 'S1', 'S2', 'S3'] as $opt): ?>
                <option value="<?= $opt; ?>" <?= ($p['ibu_pendidikan'] ?? '') == $opt ? 'selected' : ''; ?>><?= $opt; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Pekerjaan <span
                class="text-red-500">*</span></label>
        <select name="ibu_pekerjaan" required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
            <option value="">-- Pilih --</option>
            <?php foreach (['PNS', 'TNI/Polri', 'Karyawan Swasta', 'Wiraswasta', 'Ibu Rumah Tangga', 'Petani', 'Buruh', 'Tidak Bekerja', 'Sudah Meninggal', 'Lainnya'] as $opt): ?>
                <option value="<?= $opt; ?>" <?= ($p['ibu_pekerjaan'] ?? '') == $opt ? 'selected' : ''; ?>><?= $opt; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Penghasilan/Bulan</label>
        <select name="ibu_penghasilan"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
            <option value="">-- Pilih --</option>
            <?php foreach (['< Rp 1.000.000', 'Rp 1.000.000 - 3.000.000', 'Rp 3.000.000 - 5.000.000', 'Rp 5.000.000 - 10.000.000', '> Rp 10.000.000'] as $opt): ?>
                <option value="<?= $opt; ?>" <?= ($p['ibu_penghasilan'] ?? '') == $opt ? 'selected' : ''; ?>><?= $opt; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">No. HP/WA</label>
        <input type="tel" name="ibu_no_hp" value="<?= htmlspecialchars($p['ibu_no_hp'] ?? ''); ?>"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500"
            placeholder="08xxx">
    </div>
</div>