<?php
// Form Section: Data Diri
$p = $pendaftar;
?>
<div class="grid md:grid-cols-2 gap-5">
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">NIK <span class="text-red-500">*</span></label>
        <input type="text" name="nik" value="<?= htmlspecialchars($p['nik'] ?? ''); ?>" maxlength="16"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500"
            placeholder="16 digit NIK" required>
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">NISN <span class="text-red-500">*</span></label>
        <input type="text" name="nisn" value="<?= htmlspecialchars($p['nisn'] ?? ''); ?>" maxlength="10"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500"
            placeholder="10 digit NISN" required>
    </div>
    <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Lengkap <span
                class="text-red-500">*</span></label>
        <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($p['nama_lengkap'] ?? ''); ?>"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500"
            placeholder="Nama sesuai akta" required>
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Nomor KIP (opsional)</label>
        <input type="text" name="kip" value="<?= htmlspecialchars($p['kip'] ?? ''); ?>"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500"
            placeholder="Jika ada">
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Tempat Lahir <span
                class="text-red-500">*</span></label>
        <input type="text" name="tempat_lahir" value="<?= htmlspecialchars($p['tempat_lahir'] ?? ''); ?>"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500" required>
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Lahir <span
                class="text-red-500">*</span></label>
        <input type="date" name="tanggal_lahir" value="<?= $p['tanggal_lahir'] ?? ''; ?>"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500" required>
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis Kelamin <span
                class="text-red-500">*</span></label>
        <select name="jenis_kelamin" required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500">
            <option value="">-- Pilih --</option>
            <option value="L" <?= ($p['jenis_kelamin'] ?? '') == 'L' ? 'selected' : ''; ?>>Laki-laki</option>
            <option value="P" <?= ($p['jenis_kelamin'] ?? '') == 'P' ? 'selected' : ''; ?>>Perempuan</option>
        </select>
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Agama <span class="text-red-500">*</span></label>
        <select name="agama" required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500">
            <option value="">-- Pilih --</option>
            <?php foreach (['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'] as $agama): ?>
                <option value="<?= $agama; ?>" <?= ($p['agama'] ?? '') == $agama ? 'selected' : ''; ?>><?= $agama; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Jumlah Saudara</label>
        <input type="number" name="jumlah_saudara" value="<?= $p['jumlah_saudara'] ?? 0; ?>" min="0"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500">
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Anak Ke</label>
        <input type="number" name="anak_ke" value="<?= $p['anak_ke'] ?? 1; ?>" min="1"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500">
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">No. HP/WA <span
                class="text-red-500">*</span></label>
        <?php $noHpAkun = $_SESSION['psb_akun']['no_wa'] ?? $p['no_hp'] ?? ''; ?>
        <input type="tel" name="no_hp" value="<?= htmlspecialchars($noHpAkun); ?>"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-600 cursor-not-allowed"
            placeholder="08xxx" required readonly>
        <p class="text-xs text-gray-500 mt-1">Nomor diambil dari akun terdaftar</p>
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($p['email'] ?? ''); ?>"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500"
            placeholder="email@contoh.com">
    </div>
</div>