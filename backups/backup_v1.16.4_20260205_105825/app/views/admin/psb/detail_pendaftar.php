<?php
// File: app/views/admin/psb/detail_pendaftar.php
$p = $data['pendaftar'];
?>
<?php $this->view('templates/header', $data); ?>

<div class="animate-fade-in">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= BASEURL; ?>/psb/listPendaftar/<?= $p['id_periode']; ?>"
            class="p-2 hover:bg-secondary-100 rounded-lg transition-colors">
            <i data-lucide="arrow-left" class="w-5 h-5 text-secondary-500"></i>
        </a>
        <div class="flex-1">
            <h1 class="text-2xl font-bold text-secondary-800">Detail Pendaftar</h1>
            <p class="text-secondary-500 mt-1">No: <?= htmlspecialchars($p['no_pendaftaran'] ?? '-'); ?></p>
        </div>
        <?php
        $statusColors = [
            'draft' => 'bg-gray-100 text-gray-700 border-gray-300',
            'pending' => 'bg-warning-100 text-warning-700 border-warning-300',
            'verifikasi' => 'bg-blue-100 text-blue-700 border-blue-300',
            'revisi' => 'bg-orange-100 text-orange-700 border-orange-300',
            'diterima' => 'bg-success-100 text-success-700 border-success-300',
            'ditolak' => 'bg-danger-100 text-danger-700 border-danger-300'
        ];
        $color = $statusColors[$p['status'] ?? ''] ?? 'bg-secondary-100 text-secondary-700';
        ?>
        <span
            class="<?= $color; ?> px-4 py-2 rounded-full text-sm font-semibold capitalize border"><?= str_replace('_', ' ', $p['status'] ?? '-'); ?></span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">

            <!-- Data Pribadi -->
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-secondary-800 mb-4 flex items-center gap-2">
                    <i data-lucide="user" class="w-5 h-5 text-primary-500"></i>
                    Data Pribadi
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-xs text-secondary-400 uppercase">NIK</p>
                        <p class="font-medium text-secondary-800"><?= htmlspecialchars($p['nik'] ?? '-'); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-400 uppercase">NISN</p>
                        <p class="font-medium text-secondary-800"><?= htmlspecialchars($p['nisn'] ?? '-'); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-400 uppercase">No. KIP</p>
                        <p class="font-medium text-secondary-800"><?= htmlspecialchars($p['kip'] ?? '-'); ?></p>
                    </div>
                    <div class="md:col-span-3">
                        <p class="text-xs text-secondary-400 uppercase">Nama Lengkap</p>
                        <p class="font-medium text-secondary-800"><?= htmlspecialchars($p['nama_lengkap'] ?? '-'); ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-400 uppercase">Tempat Lahir</p>
                        <p class="font-medium text-secondary-800"><?= htmlspecialchars($p['tempat_lahir'] ?? '-'); ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-400 uppercase">Tanggal Lahir</p>
                        <p class="font-medium text-secondary-800">
                            <?= $p['tanggal_lahir'] ? date('d/m/Y', strtotime($p['tanggal_lahir'])) : '-'; ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-400 uppercase">Jenis Kelamin</p>
                        <p class="font-medium text-secondary-800">
                            <?= ($p['jenis_kelamin'] ?? '') == 'L' ? 'Laki-laki' : (($p['jenis_kelamin'] ?? '') == 'P' ? 'Perempuan' : '-'); ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-400 uppercase">Agama</p>
                        <p class="font-medium text-secondary-800"><?= htmlspecialchars($p['agama'] ?? '-'); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-400 uppercase">Anak Ke</p>
                        <p class="font-medium text-secondary-800"><?= $p['anak_ke'] ?? '-'; ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-400 uppercase">Jumlah Saudara</p>
                        <p class="font-medium text-secondary-800"><?= $p['jumlah_saudara'] ?? '-'; ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-400 uppercase">No. HP/WA</p>
                        <p class="font-medium text-secondary-800"><?= htmlspecialchars($p['no_hp'] ?? '-'); ?></p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-xs text-secondary-400 uppercase">Email</p>
                        <p class="font-medium text-secondary-800"><?= htmlspecialchars($p['email'] ?? '-'); ?></p>
                    </div>
                </div>
            </div>

            <!-- Alamat -->
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-secondary-800 mb-4 flex items-center gap-2">
                    <i data-lucide="map-pin" class="w-5 h-5 text-emerald-500"></i>
                    Alamat
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div class="md:col-span-3">
                        <p class="text-xs text-secondary-400 uppercase">Alamat Lengkap</p>
                        <p class="font-medium text-secondary-800"><?= htmlspecialchars($p['alamat'] ?? '-'); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-400 uppercase">RT/RW</p>
                        <p class="font-medium text-secondary-800"><?= ($p['rt'] ?? '-') . '/' . ($p['rw'] ?? '-'); ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-400 uppercase">Dusun</p>
                        <p class="font-medium text-secondary-800"><?= htmlspecialchars($p['dusun'] ?? '-'); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-400 uppercase">Desa/Kelurahan</p>
                        <p class="font-medium text-secondary-800"><?= htmlspecialchars($p['desa'] ?? '-'); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-400 uppercase">Kecamatan</p>
                        <p class="font-medium text-secondary-800"><?= htmlspecialchars($p['kecamatan'] ?? '-'); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-400 uppercase">Kabupaten/Kota</p>
                        <p class="font-medium text-secondary-800"><?= htmlspecialchars($p['kabupaten'] ?? '-'); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-400 uppercase">Provinsi</p>
                        <p class="font-medium text-secondary-800"><?= htmlspecialchars($p['provinsi'] ?? '-'); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-400 uppercase">Kode Pos</p>
                        <p class="font-medium text-secondary-800"><?= htmlspecialchars($p['kode_pos'] ?? '-'); ?></p>
                    </div>
                </div>
            </div>

            <!-- Data Ayah -->
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-secondary-800 mb-4 flex items-center gap-2">
                    <i data-lucide="user" class="w-5 h-5 text-blue-500"></i>
                    Data Ayah
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <p class="text-xs text-secondary-400 uppercase">Nama Lengkap</p>
                        <p class="font-medium text-secondary-800">
                            <?= htmlspecialchars($p['ayah_nama'] ?? ($p['nama_ayah'] ?? '-')); ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-400 uppercase">NIK</p>
                        <p class="font-medium text-secondary-800"><?= htmlspecialchars($p['ayah_nik'] ?? '-'); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-400 uppercase">Tempat Lahir</p>
                        <p class="font-medium text-secondary-800">
                            <?= htmlspecialchars($p['ayah_tempat_lahir'] ?? '-'); ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-400 uppercase">Tanggal Lahir</p>
                        <p class="font-medium text-secondary-800">
                            <?= !empty($p['ayah_tanggal_lahir']) ? date('d/m/Y', strtotime($p['ayah_tanggal_lahir'])) : '-'; ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-400 uppercase">Pendidikan</p>
                        <p class="font-medium text-secondary-800"><?= htmlspecialchars($p['ayah_pendidikan'] ?? '-'); ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-400 uppercase">Pekerjaan</p>
                        <p class="font-medium text-secondary-800">
                            <?= htmlspecialchars($p['ayah_pekerjaan'] ?? ($p['pekerjaan_ayah'] ?? '-')); ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-400 uppercase">Penghasilan</p>
                        <p class="font-medium text-secondary-800">
                            <?= htmlspecialchars($p['ayah_penghasilan'] ?? '-'); ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-400 uppercase">No. HP/WA</p>
                        <p class="font-medium text-secondary-800"><?= htmlspecialchars($p['ayah_no_hp'] ?? '-'); ?></p>
                    </div>
                </div>
            </div>

            <!-- Data Ibu -->
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-secondary-800 mb-4 flex items-center gap-2">
                    <i data-lucide="user" class="w-5 h-5 text-pink-500"></i>
                    Data Ibu
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <p class="text-xs text-secondary-400 uppercase">Nama Lengkap</p>
                        <p class="font-medium text-secondary-800">
                            <?= htmlspecialchars($p['ibu_nama'] ?? ($p['nama_ibu'] ?? '-')); ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-400 uppercase">NIK</p>
                        <p class="font-medium text-secondary-800"><?= htmlspecialchars($p['ibu_nik'] ?? '-'); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-400 uppercase">Tempat Lahir</p>
                        <p class="font-medium text-secondary-800">
                            <?= htmlspecialchars($p['ibu_tempat_lahir'] ?? '-'); ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-400 uppercase">Tanggal Lahir</p>
                        <p class="font-medium text-secondary-800">
                            <?= !empty($p['ibu_tanggal_lahir']) ? date('d/m/Y', strtotime($p['ibu_tanggal_lahir'])) : '-'; ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-400 uppercase">Pendidikan</p>
                        <p class="font-medium text-secondary-800"><?= htmlspecialchars($p['ibu_pendidikan'] ?? '-'); ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-400 uppercase">Pekerjaan</p>
                        <p class="font-medium text-secondary-800">
                            <?= htmlspecialchars($p['ibu_pekerjaan'] ?? ($p['pekerjaan_ibu'] ?? '-')); ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-400 uppercase">Penghasilan</p>
                        <p class="font-medium text-secondary-800"><?= htmlspecialchars($p['ibu_penghasilan'] ?? '-'); ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-400 uppercase">No. HP/WA</p>
                        <p class="font-medium text-secondary-800"><?= htmlspecialchars($p['ibu_no_hp'] ?? '-'); ?></p>
                    </div>
                </div>
            </div>

            <!-- Data Wali (jika ada) -->
            <?php if (!empty($p['wali_nama'])): ?>
                <div class="bg-white rounded-xl shadow-sm border p-6">
                    <h3 class="text-lg font-semibold text-secondary-800 mb-4 flex items-center gap-2">
                        <i data-lucide="users" class="w-5 h-5 text-amber-500"></i>
                        Data Wali
                    </h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <p class="text-xs text-secondary-400 uppercase">Nama Lengkap</p>
                            <p class="font-medium text-secondary-800"><?= htmlspecialchars($p['wali_nama'] ?? '-'); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-secondary-400 uppercase">Hubungan</p>
                            <p class="font-medium text-secondary-800"><?= htmlspecialchars($p['wali_hubungan'] ?? '-'); ?>
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-secondary-400 uppercase">NIK</p>
                            <p class="font-medium text-secondary-800"><?= htmlspecialchars($p['wali_nik'] ?? '-'); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-secondary-400 uppercase">Tempat Lahir</p>
                            <p class="font-medium text-secondary-800">
                                <?= htmlspecialchars($p['wali_tempat_lahir'] ?? '-'); ?>
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-secondary-400 uppercase">Tanggal Lahir</p>
                            <p class="font-medium text-secondary-800">
                                <?= !empty($p['wali_tanggal_lahir']) ? date('d/m/Y', strtotime($p['wali_tanggal_lahir'])) : '-'; ?>
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-secondary-400 uppercase">Pekerjaan</p>
                            <p class="font-medium text-secondary-800"><?= htmlspecialchars($p['wali_pekerjaan'] ?? '-'); ?>
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-secondary-400 uppercase">No. HP/WA</p>
                            <p class="font-medium text-secondary-800"><?= htmlspecialchars($p['wali_no_hp'] ?? '-'); ?></p>
                        </div>
                        <div class="md:col-span-3">
                            <p class="text-xs text-secondary-400 uppercase">Alamat</p>
                            <p class="font-medium text-secondary-800"><?= htmlspecialchars($p['wali_alamat'] ?? '-'); ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Asal Sekolah -->
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-secondary-800 mb-4 flex items-center gap-2">
                    <i data-lucide="school" class="w-5 h-5 text-purple-500"></i>
                    Asal Sekolah
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <p class="text-xs text-secondary-400 uppercase">Nama Sekolah</p>
                        <p class="font-medium text-secondary-800">
                            <?= htmlspecialchars($p['nama_sekolah_asal'] ?? ($p['asal_sekolah'] ?? '-')); ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-400 uppercase">NPSN</p>
                        <p class="font-medium text-secondary-800">
                            <?= htmlspecialchars($p['npsn_sekolah_asal'] ?? '-'); ?>
                        </p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-xs text-secondary-400 uppercase">Alamat Sekolah</p>
                        <p class="font-medium text-secondary-800">
                            <?= htmlspecialchars($p['alamat_sekolah_asal'] ?? '-'); ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-400 uppercase">Tahun Lulus</p>
                        <p class="font-medium text-secondary-800"><?= $p['tahun_lulus'] ?? '-'; ?></p>
                    </div>
                </div>
            </div>

            <!-- Dokumen -->
            <?php if (!empty($data['dokumen'])): ?>
                <div class="bg-white rounded-xl shadow-sm border p-6">
                    <h3 class="text-lg font-semibold text-secondary-800 mb-4 flex items-center gap-2">
                        <i data-lucide="file-text" class="w-5 h-5 text-orange-500"></i>
                        Dokumen Pendukung
                    </h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <?php
                        // Load document config from model
                        require_once APPROOT . '/app/models/DokumenConfig_model.php';
                        $dokumenConfigModel = new DokumenConfig_model();
                        $docTypes = $dokumenConfigModel->getAsArray();

                        foreach ($docTypes as $key => $label):
                            $doc = null;
                            $fileExt = '';
                            foreach ($data['dokumen'] as $d) {
                                if ($d['jenis_dokumen'] === $key) {
                                    $doc = $d;
                                    $fileExt = strtolower(pathinfo($d['path_file'] ?? '', PATHINFO_EXTENSION));
                                    break;
                                }
                            }
                            $isPdf = $fileExt === 'pdf';

                            // Check if file is on Google Drive
                            $isGoogleDrive = !empty($doc['drive_file_id']);
                            if ($isGoogleDrive) {
                                $previewUrl = 'https://drive.google.com/file/d/' . $doc['drive_file_id'] . '/preview';
                            } else {
                                $previewUrl = BASEURL . '/psb/serveFile/' . $p['id_pendaftar'] . '/' . $key;
                            }
                            ?>
                            <div
                                class="text-center p-3 rounded-lg <?= $doc ? 'bg-green-50 border border-green-200' : 'bg-gray-50 border border-gray-200'; ?>">
                                <i data-lucide="<?= $doc ? 'file-check' : 'file-x'; ?>"
                                    class="w-6 h-6 mx-auto mb-2 <?= $doc ? 'text-green-500' : 'text-gray-400'; ?>"></i>
                                <p class="text-xs font-medium <?= $doc ? 'text-green-700' : 'text-gray-500'; ?>"><?= $label; ?>
                                </p>
                                <?php if ($doc): ?>
                                    <button type="button"
                                        onclick="previewDoc('<?= $previewUrl; ?>', '<?= $label; ?>', <?= ($isPdf || $isGoogleDrive) ? 'true' : 'false'; ?>)"
                                        class="text-xs text-blue-600 hover:underline mt-1 cursor-pointer"><?= $isGoogleDrive ? '☁️ Lihat' : 'Lihat'; ?></button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Info Pendaftaran -->
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-secondary-800 mb-4">Info Pendaftaran</h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-xs text-secondary-400 uppercase">Lembaga</p>
                        <p class="font-medium text-secondary-800"><?= htmlspecialchars($p['nama_lembaga'] ?? '-'); ?>
                            (<?= $p['jenjang'] ?? '-'; ?>)</p>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-400 uppercase">Jalur</p>
                        <span
                            class="bg-purple-100 text-purple-700 px-2 py-1 rounded text-sm font-medium"><?= htmlspecialchars($p['nama_jalur'] ?? '-'); ?></span>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-400 uppercase">Tanggal Daftar</p>
                        <p class="font-medium text-secondary-800">
                            <?= $p['tanggal_daftar'] ? date('d/m/Y H:i', strtotime($p['tanggal_daftar'])) : '-'; ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Update Status -->
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-secondary-800 mb-4">Update Status</h3>
                <form action="<?= BASEURL; ?>/psb/updateStatus" method="POST" class="space-y-4">
                    <input type="hidden" name="id_pendaftar" value="<?= $p['id_pendaftar']; ?>">

                    <div>
                        <label class="block text-sm font-medium text-secondary-700 mb-2">Status</label>
                        <select name="status"
                            class="w-full px-4 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                            <option value="revisi" <?= ($p['status'] ?? '') == 'revisi' ? 'selected' : ''; ?>>Revisi
                            </option>
                            <option value="diterima" <?= ($p['status'] ?? '') == 'diterima' ? 'selected' : ''; ?>>Terima
                            </option>
                            <option value="ditolak" <?= ($p['status'] ?? '') == 'ditolak' ? 'selected' : ''; ?>>Tolak
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-secondary-700 mb-2">Catatan untuk Siswa</label>
                        <textarea name="catatan_admin" rows="3"
                            class="w-full px-4 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                            placeholder="Catatan ini akan dikirim ke WA siswa..."><?= htmlspecialchars($p['catatan_admin'] ?? ''); ?></textarea>
                    </div>

                    <button type="submit" class="btn-primary w-full flex items-center justify-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        Simpan & Kirim Notifikasi
                    </button>
                </form>
            </div>

            <!-- Konversi ke Siswa -->
            <?php if (($p['status'] ?? '') == 'diterima' && empty($p['id_siswa'])): ?>
                <div class="bg-success-50 rounded-xl border border-success-200 p-6">
                    <h3 class="text-lg font-semibold text-success-800 mb-4 flex items-center gap-2">
                        <i data-lucide="user-plus" class="w-5 h-5"></i>
                        Konversi ke Siswa
                    </h3>
                    <p class="text-sm text-success-700 mb-4">Pendaftar ini sudah diterima dan dapat dikonversi menjadi siswa
                        aktif.</p>
                    <a href="<?= BASEURL; ?>/psb/konversiSiswa/<?= $p['id_pendaftar']; ?>"
                        onclick="return confirm('Konversi pendaftar ini menjadi siswa?')"
                        class="btn-success w-full flex items-center justify-center gap-2">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        Konversi Sekarang
                    </a>
                </div>
            <?php elseif (!empty($p['id_siswa'])): ?>
                <div class="bg-green-50 rounded-xl border border-green-200 p-6">
                    <div class="flex items-center gap-3">
                        <i data-lucide="check-circle-2" class="w-8 h-8 text-green-600"></i>
                        <div>
                            <p class="font-semibold text-green-800">Sudah Menjadi Siswa</p>
                            <p class="text-sm text-green-600">ID: <?= $p['id_siswa']; ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Preview Dokumen -->
<div id="docPreviewModal"
    class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between p-4 border-b">
            <h3 id="docModalTitle" class="text-lg font-semibold text-secondary-800"></h3>
            <button type="button" onclick="closeDocModal()" class="p-2 hover:bg-secondary-100 rounded-lg">
                <i data-lucide="x" class="w-5 h-5 text-secondary-500"></i>
            </button>
        </div>
        <div id="docModalContent" class="flex-1 overflow-auto p-4 flex items-center justify-center min-h-[400px]"></div>
        <div class="flex justify-end gap-3 p-4 border-t bg-secondary-50">
            <a id="docDownloadLink" href="#" target="_blank" class="btn-secondary text-sm flex items-center gap-2">
                <i data-lucide="download" class="w-4 h-4"></i> Download
            </a>
            <button type="button" onclick="closeDocModal()" class="btn-primary text-sm">Tutup</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () { lucide.createIcons(); });

    function previewDoc(url, title, isPdf) {
        const modal = document.getElementById('docPreviewModal');
        document.getElementById('docModalTitle').textContent = title;
        document.getElementById('docDownloadLink').href = url;
        document.getElementById('docModalContent').innerHTML = isPdf
            ? '<iframe src="' + url + '" class="w-full h-[60vh] rounded-lg border"></iframe>'
            : '<img src="' + url + '" alt="' + title + '" class="max-w-full max-h-[60vh] object-contain rounded-lg shadow-lg">';
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
        lucide.createIcons();
    }

    function closeDocModal() {
        document.getElementById('docPreviewModal').classList.add('hidden');
        document.getElementById('docPreviewModal').classList.remove('flex');
        document.body.style.overflow = '';
    }

    document.getElementById('docPreviewModal')?.addEventListener('click', function (e) { if (e.target === this) closeDocModal(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeDocModal(); });
</script>

<?php $this->view('templates/footer'); ?>