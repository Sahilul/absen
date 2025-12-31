<?php
// File: app/views/siswa/edit_identitas.php
$siswa = $data['siswa'] ?? [];
?>
<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gradient-to-br from-secondary-50 to-secondary-100 p-4 sm:p-6">
    <!-- Breadcrumb -->
    <div class="mb-4 sm:mb-6">
        <nav class="flex items-center space-x-2 text-sm text-secondary-600">
            <a href="<?= BASEURL; ?>/siswa/dashboard" class="hover:text-primary-600 transition-colors">Dashboard</a>
            <i data-lucide="chevron-right" class="w-4 h-4"></i>
            <span class="text-secondary-800 font-medium">Edit Identitas</span>
        </nav>
    </div>

    <!-- Header -->
    <div class="mb-5 sm:mb-8">
        <div class="flex items-center space-x-3 sm:space-x-4">
            <div class="gradient-warning p-3 rounded-xl shadow-lg">
                <i data-lucide="user-pen" class="w-6 h-6 sm:w-8 sm:h-8 text-white"></i>
            </div>
            <div>
                <h2 class="text-2xl sm:text-3xl font-bold text-secondary-800">Edit Identitas</h2>
                <p class="text-secondary-600 mt-1 text-sm sm:text-base">Perbarui data pribadi Anda</p>
            </div>
        </div>
    </div>

    <!-- Flash Message -->
    <?php if (isset($_SESSION['flash'])): ?>
        <div
            class="mb-6 p-4 rounded-xl <?= $_SESSION['flash']['type'] === 'success' ? 'bg-green-100 text-green-800 border border-green-200' : ($_SESSION['flash']['type'] === 'info' ? 'bg-blue-100 text-blue-800 border border-blue-200' : 'bg-red-100 text-red-800 border border-red-200'); ?>">
            <div class="flex items-center">
                <i data-lucide="<?= $_SESSION['flash']['type'] === 'success' ? 'check-circle' : ($_SESSION['flash']['type'] === 'info' ? 'info' : 'alert-circle'); ?>"
                    class="w-5 h-5 mr-2"></i>
                <?= $_SESSION['flash']['message']; ?>
            </div>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <!-- Form -->
    <form action="<?= BASEURL; ?>/siswa/prosesEditIdentitas" method="POST" class="space-y-6">

        <!-- Data Pribadi -->
        <div class="glass-effect rounded-xl p-6 shadow-lg border border-white/20">
            <h3 class="text-lg font-semibold text-secondary-800 mb-4 flex items-center">
                <i data-lucide="user" class="w-5 h-5 mr-2 text-violet-600"></i>
                Data Pribadi
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                    <input type="text" name="nama_siswa" value="<?= htmlspecialchars($siswa['nama_siswa'] ?? ''); ?>"
                        class="input-modern w-full" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="input-modern w-full">
                        <option value="L" <?= ($siswa['jenis_kelamin'] ?? '') == 'L' ? 'selected' : ''; ?>>Laki-laki
                        </option>
                        <option value="P" <?= ($siswa['jenis_kelamin'] ?? '') == 'P' ? 'selected' : ''; ?>>Perempuan
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir"
                        value="<?= htmlspecialchars($siswa['tempat_lahir'] ?? ''); ?>" class="input-modern w-full">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir</label>
                    <input type="date" name="tgl_lahir" value="<?= $siswa['tgl_lahir'] ?? ''; ?>"
                        class="input-modern w-full">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Agama</label>
                    <select name="agama" class="input-modern w-full">
                        <?php $agamaList = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu']; ?>
                        <?php foreach ($agamaList as $ag): ?>
                            <option value="<?= $ag; ?>" <?= ($siswa['agama'] ?? '') == $ag ? 'selected' : ''; ?>><?= $ag; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Hobi</label>
                    <input type="text" name="hobi" value="<?= htmlspecialchars($siswa['hobi'] ?? ''); ?>"
                        class="input-modern w-full" placeholder="Contoh: Membaca, Olahraga">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cita-cita</label>
                    <input type="text" name="cita_cita" value="<?= htmlspecialchars($siswa['cita_cita'] ?? ''); ?>"
                        class="input-modern w-full" placeholder="Contoh: Dokter, Guru">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">No. WhatsApp</label>
                    <input type="text" name="no_wa" value="<?= htmlspecialchars($siswa['no_wa'] ?? ''); ?>"
                        class="input-modern w-full" placeholder="08xxxxxxxxxx">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($siswa['email'] ?? ''); ?>"
                        class="input-modern w-full" placeholder="contoh@email.com">
                </div>
            </div>
        </div>

        <!-- Alamat -->
        <div class="glass-effect rounded-xl p-6 shadow-lg border border-white/20">
            <h3 class="text-lg font-semibold text-secondary-800 mb-4 flex items-center">
                <i data-lucide="map-pin" class="w-5 h-5 mr-2 text-green-600"></i>
                Alamat
            </h3>
            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap</label>
                    <textarea name="alamat" rows="2"
                        class="input-modern w-full"><?= htmlspecialchars($siswa['alamat'] ?? ''); ?></textarea>
                </div>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">RT</label>
                    <input type="text" name="rt" value="<?= htmlspecialchars($siswa['rt'] ?? ''); ?>"
                        class="input-modern w-full">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">RW</label>
                    <input type="text" name="rw" value="<?= htmlspecialchars($siswa['rw'] ?? ''); ?>"
                        class="input-modern w-full">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dusun</label>
                    <input type="text" name="dusun" value="<?= htmlspecialchars($siswa['dusun'] ?? ''); ?>"
                        class="input-modern w-full">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kode Pos</label>
                    <input type="text" name="kode_pos" value="<?= htmlspecialchars($siswa['kode_pos'] ?? ''); ?>"
                        class="input-modern w-full">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kelurahan/Desa</label>
                    <input type="text" name="kelurahan" value="<?= htmlspecialchars($siswa['kelurahan'] ?? ''); ?>"
                        class="input-modern w-full">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kecamatan</label>
                    <input type="text" name="kecamatan" value="<?= htmlspecialchars($siswa['kecamatan'] ?? ''); ?>"
                        class="input-modern w-full">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kabupaten/Kota</label>
                    <input type="text" name="kabupaten" value="<?= htmlspecialchars($siswa['kabupaten'] ?? ''); ?>"
                        class="input-modern w-full">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Provinsi</label>
                    <input type="text" name="provinsi" value="<?= htmlspecialchars($siswa['provinsi'] ?? ''); ?>"
                        class="input-modern w-full">
                </div>
            </div>
        </div>

        <!-- Data Ayah -->
        <div class="glass-effect rounded-xl p-6 shadow-lg border border-white/20">
            <h3 class="text-lg font-semibold text-secondary-800 mb-4 flex items-center">
                <i data-lucide="user" class="w-5 h-5 mr-2 text-blue-600"></i>
                Data Ayah
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Ayah</label>
                    <input type="text" name="ayah_kandung"
                        value="<?= htmlspecialchars($siswa['ayah_kandung'] ?? ''); ?>" class="input-modern w-full">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">NIK Ayah</label>
                    <input type="text" name="ayah_nik" value="<?= htmlspecialchars($siswa['ayah_nik'] ?? ''); ?>"
                        class="input-modern w-full">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">No. HP Ayah <span class="text-xs text-amber-600">🔒</span></label>
                    <input type="text" value="<?= htmlspecialchars($siswa['ayah_no_hp'] ?? ''); ?>" 
                        class="input-modern w-full bg-gray-100 cursor-not-allowed" readonly 
                        title="Hubungi Wali Kelas atau Admin untuk mengubah nomor ini">
                    <p class="text-xs text-gray-400 mt-1">Hubungi Wali Kelas/Admin untuk mengubah</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tempat Lahir</label>
                    <input type="text" name="ayah_tempat_lahir"
                        value="<?= htmlspecialchars($siswa['ayah_tempat_lahir'] ?? ''); ?>" class="input-modern w-full">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir</label>
                    <input type="date" name="ayah_tanggal_lahir" value="<?= $siswa['ayah_tanggal_lahir'] ?? ''; ?>"
                        class="input-modern w-full">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="ayah_status" class="input-modern w-full">
                        <option value="Masih Hidup" <?= ($siswa['ayah_status'] ?? '') == 'Masih Hidup' ? 'selected' : ''; ?>>Masih Hidup</option>
                        <option value="Sudah Meninggal" <?= ($siswa['ayah_status'] ?? '') == 'Sudah Meninggal' ? 'selected' : ''; ?>>Sudah Meninggal</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pendidikan</label>
                    <select name="ayah_pendidikan" class="input-modern w-full">
                        <?php $pendidikanList = ['Tidak Sekolah', 'SD/Sederajat', 'SMP/Sederajat', 'SMA/Sederajat', 'D1', 'D2', 'D3', 'D4/S1', 'S2', 'S3']; ?>
                        <?php foreach ($pendidikanList as $p): ?>
                            <option value="<?= $p; ?>" <?= ($siswa['ayah_pendidikan'] ?? '') == $p ? 'selected' : ''; ?>>
                                <?= $p; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pekerjaan</label>
                    <input type="text" name="ayah_pekerjaan"
                        value="<?= htmlspecialchars($siswa['ayah_pekerjaan'] ?? ''); ?>" class="input-modern w-full">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Penghasilan</label>
                    <select name="ayah_penghasilan" class="input-modern w-full">
                        <?php $penghasilanList = ['< Rp 1.000.000', 'Rp 1.000.000 - Rp 3.000.000', 'Rp 3.000.000 - Rp 5.000.000', 'Rp 5.000.000 - Rp 10.000.000', '> Rp 10.000.000']; ?>
                        <?php foreach ($penghasilanList as $ph): ?>
                            <option value="<?= $ph; ?>" <?= ($siswa['ayah_penghasilan'] ?? '') == $ph ? 'selected' : ''; ?>>
                                <?= $ph; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Data Ibu -->
        <div class="glass-effect rounded-xl p-6 shadow-lg border border-white/20">
            <h3 class="text-lg font-semibold text-secondary-800 mb-4 flex items-center">
                <i data-lucide="heart" class="w-5 h-5 mr-2 text-pink-600"></i>
                Data Ibu
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Ibu</label>
                    <input type="text" name="ibu_kandung" value="<?= htmlspecialchars($siswa['ibu_kandung'] ?? ''); ?>"
                        class="input-modern w-full">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">NIK Ibu</label>
                    <input type="text" name="ibu_nik" value="<?= htmlspecialchars($siswa['ibu_nik'] ?? ''); ?>"
                        class="input-modern w-full">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">No. HP Ibu <span class="text-xs text-amber-600">🔒</span></label>
                    <input type="text" value="<?= htmlspecialchars($siswa['ibu_no_hp'] ?? ''); ?>" 
                        class="input-modern w-full bg-gray-100 cursor-not-allowed" readonly 
                        title="Hubungi Wali Kelas atau Admin untuk mengubah nomor ini">
                    <p class="text-xs text-gray-400 mt-1">Hubungi Wali Kelas/Admin untuk mengubah</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tempat Lahir</label>
                    <input type="text" name="ibu_tempat_lahir"
                        value="<?= htmlspecialchars($siswa['ibu_tempat_lahir'] ?? ''); ?>" class="input-modern w-full">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir</label>
                    <input type="date" name="ibu_tanggal_lahir" value="<?= $siswa['ibu_tanggal_lahir'] ?? ''; ?>"
                        class="input-modern w-full">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="ibu_status" class="input-modern w-full">
                        <option value="Masih Hidup" <?= ($siswa['ibu_status'] ?? '') == 'Masih Hidup' ? 'selected' : ''; ?>>Masih Hidup</option>
                        <option value="Sudah Meninggal" <?= ($siswa['ibu_status'] ?? '') == 'Sudah Meninggal' ? 'selected' : ''; ?>>Sudah Meninggal</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pendidikan</label>
                    <select name="ibu_pendidikan" class="input-modern w-full">
                        <?php foreach ($pendidikanList as $p): ?>
                            <option value="<?= $p; ?>" <?= ($siswa['ibu_pendidikan'] ?? '') == $p ? 'selected' : ''; ?>>
                                <?= $p; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pekerjaan</label>
                    <input type="text" name="ibu_pekerjaan"
                        value="<?= htmlspecialchars($siswa['ibu_pekerjaan'] ?? ''); ?>" class="input-modern w-full">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Penghasilan</label>
                    <select name="ibu_penghasilan" class="input-modern w-full">
                        <?php foreach ($penghasilanList as $ph): ?>
                            <option value="<?= $ph; ?>" <?= ($siswa['ibu_penghasilan'] ?? '') == $ph ? 'selected' : ''; ?>>
                                <?= $ph; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Data Wali (Opsional) -->
        <div class="glass-effect rounded-xl p-6 shadow-lg border border-white/20">
            <h3 class="text-lg font-semibold text-secondary-800 mb-4 flex items-center">
                <i data-lucide="users" class="w-5 h-5 mr-2 text-amber-600"></i>
                Data Wali (Opsional)
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Wali</label>
                    <input type="text" name="wali_nama" value="<?= htmlspecialchars($siswa['wali_nama'] ?? ''); ?>"
                        class="input-modern w-full">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Hubungan</label>
                    <input type="text" name="wali_hubungan"
                        value="<?= htmlspecialchars($siswa['wali_hubungan'] ?? ''); ?>" class="input-modern w-full"
                        placeholder="Contoh: Paman, Kakek">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">NIK Wali</label>
                    <input type="text" name="wali_nik" value="<?= htmlspecialchars($siswa['wali_nik'] ?? ''); ?>"
                        class="input-modern w-full">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">No. HP Wali</label>
                    <input type="text" name="wali_no_hp" value="<?= htmlspecialchars($siswa['wali_no_hp'] ?? ''); ?>"
                        class="input-modern w-full" placeholder="08xxxxxxxxxx">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pendidikan</label>
                    <select name="wali_pendidikan" class="input-modern w-full">
                        <option value="">-- Pilih --</option>
                        <?php foreach ($pendidikanList as $p): ?>
                            <option value="<?= $p; ?>" <?= ($siswa['wali_pendidikan'] ?? '') == $p ? 'selected' : ''; ?>>
                                <?= $p; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pekerjaan</label>
                    <input type="text" name="wali_pekerjaan"
                        value="<?= htmlspecialchars($siswa['wali_pekerjaan'] ?? ''); ?>" class="input-modern w-full">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Penghasilan</label>
                    <select name="wali_penghasilan" class="input-modern w-full">
                        <option value="">-- Pilih --</option>
                        <?php foreach ($penghasilanList as $ph): ?>
                            <option value="<?= $ph; ?>" <?= ($siswa['wali_penghasilan'] ?? '') == $ph ? 'selected' : ''; ?>>
                                <?= $ph; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end gap-4">
            <a href="<?= BASEURL; ?>/siswa/dashboard" class="btn-secondary px-6 py-3">
                <i data-lucide="x" class="w-4 h-4 mr-2"></i>
                Batal
            </a>
            <button type="submit" class="btn-primary px-8 py-3">
                <i data-lucide="save" class="w-4 h-4 mr-2"></i>
                Simpan Perubahan
            </button>
        </div>
    </form>
</main>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>