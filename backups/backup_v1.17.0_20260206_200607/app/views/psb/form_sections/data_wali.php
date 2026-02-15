<?php
// Form Section: Data Wali
$p = $pendaftar;
?>
<div class="mb-5 p-4 bg-amber-50 border border-amber-200 rounded-lg">
    <p class="text-sm text-amber-700">
        <i data-lucide="info" class="w-4 h-4 inline"></i>
        Data wali hanya perlu diisi jika siswa tinggal dengan wali (bukan orang tua kandung). Kosongkan jika tidak ada
        wali.
    </p>
</div>

<div class="grid md:grid-cols-2 gap-5">
    <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Lengkap Wali</label>
        <input type="text" name="wali_nama" value="<?= htmlspecialchars($p['wali_nama'] ?? ''); ?>"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500"
            placeholder="Nama sesuai KTP">
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">NIK Wali</label>
        <input type="text" name="wali_nik" value="<?= htmlspecialchars($p['wali_nik'] ?? ''); ?>" maxlength="16"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500"
            placeholder="16 digit NIK">
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Hubungan dengan Siswa</label>
        <select name="wali_hubungan"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
            <option value="">-- Pilih --</option>
            <?php foreach (['Kakek', 'Nenek', 'Paman', 'Bibi', 'Kakak', 'Lainnya'] as $opt): ?>
                <option value="<?= $opt; ?>" <?= ($p['wali_hubungan'] ?? '') == $opt ? 'selected' : ''; ?>><?= $opt; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Tempat Lahir</label>
        <input type="text" name="wali_tempat_lahir" value="<?= htmlspecialchars($p['wali_tempat_lahir'] ?? ''); ?>"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Lahir</label>
        <input type="date" name="wali_tanggal_lahir" value="<?= $p['wali_tanggal_lahir'] ?? ''; ?>"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Pekerjaan</label>
        <select name="wali_pekerjaan"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
            <option value="">-- Pilih --</option>
            <?php foreach (['PNS', 'TNI/Polri', 'Karyawan Swasta', 'Wiraswasta', 'Petani', 'Buruh', 'Pensiunan', 'Lainnya'] as $opt): ?>
                <option value="<?= $opt; ?>" <?= ($p['wali_pekerjaan'] ?? '') == $opt ? 'selected' : ''; ?>><?= $opt; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">No. HP/WA</label>
        <input type="tel" name="wali_no_hp" value="<?= htmlspecialchars($p['wali_no_hp'] ?? ''); ?>"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500"
            placeholder="08xxx">
    </div>
    <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-gray-700 mb-2">Alamat Wali</label>
        <textarea name="wali_alamat" rows="2"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500"
            placeholder="Alamat lengkap wali"><?= htmlspecialchars($p['wali_alamat'] ?? ''); ?></textarea>
    </div>
</div>