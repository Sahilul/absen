<?php
// File: app/views/psb/detail_pendaftaran.php
// Halaman detail pendaftaran untuk pendaftar (read-only)

$p = $data['pendaftar'] ?? [];
$dokumen = $data['dokumen'] ?? [];
$akun = $data['akun'] ?? [];
$namaSekolah = getPengaturanAplikasi()['nama_aplikasi'] ?? 'Smart Absensi';

// Status badge
$statusColors = [
    'draft' => 'bg-gray-100 text-gray-700',
    'pending' => 'bg-yellow-100 text-yellow-700',
    'verifikasi' => 'bg-blue-100 text-blue-700',
    'revisi' => 'bg-orange-100 text-orange-700',
    'diterima' => 'bg-green-100 text-green-700',
    'ditolak' => 'bg-red-100 text-red-700'
];
$statusLabels = [
    'draft' => 'Draft',
    'pending' => 'Menunggu Verifikasi',
    'verifikasi' => 'Terverifikasi',
    'revisi' => 'Perlu Revisi',
    'diterima' => 'Diterima',
    'ditolak' => 'Ditolak'
];
$statusColor = $statusColors[$p['status'] ?? 'draft'] ?? 'bg-gray-100 text-gray-700';
$statusLabel = $statusLabels[$p['status'] ?? 'draft'] ?? 'Draft';

// Load document config from model for labels
require_once APPROOT . '/app/models/DokumenConfig_model.php';
$dokumenConfigModel = new DokumenConfig_model();
$jenisLabels = $dokumenConfigModel->getAsArray();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pendaftaran - PSB <?= $namaSekolah; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= BASEURL; ?>/public/css/custom-styles.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100">
    <!-- Header -->
    <header class="text-white shadow-lg sticky top-0 z-40"
        style="background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);">
        <div class="max-w-4xl mx-auto px-4 py-3 sm:py-4">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                    <a href="<?= BASEURL; ?>/psb/dashboardPendaftar"
                        class="bg-white/20 p-2 rounded-lg hover:bg-white/30 transition-colors flex-shrink-0">
                        <i data-lucide="arrow-left" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                    </a>
                    <div class="min-w-0">
                        <h1 class="font-bold text-base sm:text-lg truncate">Detail Pendaftaran</h1>
                        <p class="text-white/80 text-xs sm:text-sm truncate">
                            <?= htmlspecialchars($p['no_pendaftaran'] ?? '-'); ?></p>
                    </div>
                </div>
                <span
                    class="px-3 py-1.5 rounded-full text-xs sm:text-sm font-semibold <?= $statusColor; ?> flex-shrink-0">
                    <?= $statusLabel; ?>
                </span>
            </div>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-3 sm:px-4 py-4 sm:py-6">
        <!-- Flash Message -->
        <?php if (isset($_SESSION['flash'])): ?>
            <div
                class="mb-4 p-3 rounded-lg <?= $_SESSION['flash']['type'] == 'error' ? 'bg-red-50 text-red-600 border border-red-200' : 'bg-green-50 text-green-600 border border-green-200'; ?>">
                <div class="flex items-center gap-2 text-sm">
                    <i data-lucide="<?= $_SESSION['flash']['type'] == 'error' ? 'alert-circle' : 'check-circle'; ?>"
                        class="w-4 h-4 flex-shrink-0"></i>
                    <span><?= $_SESSION['flash']['message']; ?></span>
                </div>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <!-- Info Pendaftaran -->
        <div class="bg-white rounded-xl shadow-sm border p-4 sm:p-5 mb-4">
            <div class="flex items-start gap-3 mb-3">
                <div class="p-2.5 rounded-lg text-white flex-shrink-0"
                    style="background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);">
                    <i data-lucide="file-text" class="w-5 h-5"></i>
                </div>
                <div class="min-w-0">
                    <h2 class="font-bold text-base sm:text-lg text-gray-800 truncate">
                        <?= htmlspecialchars($p['nama_lembaga'] ?? '-'); ?></h2>
                    <p class="text-xs sm:text-sm text-gray-500 truncate">
                        <?= htmlspecialchars($p['nama_periode'] ?? '-'); ?></p>
                </div>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 sm:gap-3 text-xs sm:text-sm">
                <div class="bg-gray-50 rounded-lg p-2.5">
                    <p class="text-gray-400 text-[10px] sm:text-xs uppercase">No. Pendaftaran</p>
                    <p class="font-bold text-gray-800 truncate"><?= htmlspecialchars($p['no_pendaftaran'] ?? '-'); ?>
                    </p>
                </div>
                <div class="bg-gray-50 rounded-lg p-2.5">
                    <p class="text-gray-400 text-[10px] sm:text-xs uppercase">Jalur</p>
                    <p class="font-medium text-gray-800 truncate"><?= htmlspecialchars($p['nama_jalur'] ?? '-'); ?></p>
                </div>
                <div class="bg-gray-50 rounded-lg p-2.5 col-span-2 sm:col-span-1">
                    <p class="text-gray-400 text-[10px] sm:text-xs uppercase">Tanggal Daftar</p>
                    <p class="font-medium text-gray-800">
                        <?= $p['tanggal_daftar'] ? date('d/m/Y H:i', strtotime($p['tanggal_daftar'])) : '-'; ?></p>
                </div>
            </div>

            <?php if (!empty($p['catatan_admin']) && in_array($p['status'], ['revisi', 'ditolak'])): ?>
                <div class="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                    <div class="flex items-start gap-2">
                        <i data-lucide="message-circle" class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5"></i>
                        <div class="min-w-0">
                            <p class="font-semibold text-amber-700 text-xs">Catatan Admin:</p>
                            <p class="text-amber-600 text-xs sm:text-sm">
                                <?= nl2br(htmlspecialchars($p['catatan_admin'])); ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Data Pribadi -->
        <div class="bg-white rounded-xl shadow-sm border p-4 sm:p-5 mb-4">
            <div class="flex items-center gap-2 mb-3 pb-2 border-b">
                <i data-lucide="user" class="w-4 h-4 text-sky-500"></i>
                <h3 class="font-bold text-sm sm:text-base text-gray-800">Data Pribadi</h3>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs sm:text-sm">
                <div>
                    <p class="text-gray-400 text-[10px] uppercase">NIK</p>
                    <p class="font-medium text-gray-800 break-all"><?= htmlspecialchars($p['nik'] ?? '-'); ?></p>
                </div>
                <div>
                    <p class="text-gray-400 text-[10px] uppercase">NISN</p>
                    <p class="font-medium text-gray-800"><?= htmlspecialchars($p['nisn'] ?? '-'); ?></p>
                </div>
                <div>
                    <p class="text-gray-400 text-[10px] uppercase">No. KIP</p>
                    <p class="font-medium text-gray-800"><?= htmlspecialchars($p['no_kip'] ?? '-'); ?></p>
                </div>
                <div class="col-span-2 sm:col-span-3">
                    <p class="text-gray-400 text-[10px] uppercase">Nama Lengkap</p>
                    <p class="font-bold text-gray-800 text-sm sm:text-base">
                        <?= htmlspecialchars($p['nama_lengkap'] ?? '-'); ?></p>
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <p class="text-gray-400 text-[10px] uppercase">Tempat, Tgl Lahir</p>
                    <p class="font-medium text-gray-800"><?= htmlspecialchars($p['tempat_lahir'] ?? '-'); ?>,
                        <?= $p['tanggal_lahir'] ? date('d/m/Y', strtotime($p['tanggal_lahir'])) : '-'; ?></p>
                </div>
                <div>
                    <p class="text-gray-400 text-[10px] uppercase">Jenis Kelamin</p>
                    <p class="font-medium text-gray-800">
                        <?= $p['jenis_kelamin'] == 'L' ? 'Laki-laki' : ($p['jenis_kelamin'] == 'P' ? 'Perempuan' : '-'); ?>
                    </p>
                </div>
                <div>
                    <p class="text-gray-400 text-[10px] uppercase">Agama</p>
                    <p class="font-medium text-gray-800"><?= htmlspecialchars($p['agama'] ?? '-'); ?></p>
                </div>
                <div>
                    <p class="text-gray-400 text-[10px] uppercase">Anak Ke</p>
                    <p class="font-medium text-gray-800"><?= $p['anak_ke'] ?? '-'; ?> dari
                        <?= $p['jumlah_saudara'] ?? '-'; ?></p>
                </div>
                <div>
                    <p class="text-gray-400 text-[10px] uppercase">No. HP/WA</p>
                    <p class="font-medium text-gray-800"><?= htmlspecialchars($p['no_hp'] ?? '-'); ?></p>
                </div>
                <div>
                    <p class="text-gray-400 text-[10px] uppercase">Email</p>
                    <p class="font-medium text-gray-800 truncate"><?= htmlspecialchars($p['email'] ?? '-'); ?></p>
                </div>
            </div>
        </div>

        <!-- Alamat -->
        <div class="bg-white rounded-xl shadow-sm border p-4 sm:p-5 mb-4">
            <div class="flex items-center gap-2 mb-3 pb-2 border-b">
                <i data-lucide="map-pin" class="w-4 h-4 text-emerald-500"></i>
                <h3 class="font-bold text-sm sm:text-base text-gray-800">Alamat</h3>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs sm:text-sm">
                <div class="col-span-2 sm:col-span-3">
                    <p class="text-gray-400 text-[10px] uppercase">Alamat Lengkap</p>
                    <p class="font-medium text-gray-800"><?= htmlspecialchars($p['alamat'] ?? '-'); ?></p>
                </div>
                <div>
                    <p class="text-gray-400 text-[10px] uppercase">RT/RW</p>
                    <p class="font-medium text-gray-800">
                        <?= htmlspecialchars($p['rt'] ?? '-'); ?>/<?= htmlspecialchars($p['rw'] ?? '-'); ?></p>
                </div>
                <div>
                    <p class="text-gray-400 text-[10px] uppercase">Dusun</p>
                    <p class="font-medium text-gray-800"><?= htmlspecialchars($p['dusun'] ?? '-'); ?></p>
                </div>
                <div>
                    <p class="text-gray-400 text-[10px] uppercase">Desa</p>
                    <p class="font-medium text-gray-800"><?= htmlspecialchars($p['desa'] ?? '-'); ?></p>
                </div>
                <div>
                    <p class="text-gray-400 text-[10px] uppercase">Kecamatan</p>
                    <p class="font-medium text-gray-800"><?= htmlspecialchars($p['kecamatan'] ?? '-'); ?></p>
                </div>
                <div>
                    <p class="text-gray-400 text-[10px] uppercase">Kabupaten</p>
                    <p class="font-medium text-gray-800"><?= htmlspecialchars($p['kabupaten'] ?? '-'); ?></p>
                </div>
                <div>
                    <p class="text-gray-400 text-[10px] uppercase">Provinsi</p>
                    <p class="font-medium text-gray-800"><?= htmlspecialchars($p['provinsi'] ?? '-'); ?></p>
                </div>
            </div>
        </div>

        <!-- Asal Sekolah -->
        <div class="bg-white rounded-xl shadow-sm border p-4 sm:p-5 mb-4">
            <div class="flex items-center gap-2 mb-3 pb-2 border-b">
                <i data-lucide="school" class="w-4 h-4 text-purple-500"></i>
                <h3 class="font-bold text-sm sm:text-base text-gray-800">Asal Sekolah</h3>
            </div>
            <div class="grid grid-cols-2 gap-3 text-xs sm:text-sm">
                <div class="col-span-2">
                    <p class="text-gray-400 text-[10px] uppercase">Nama Sekolah</p>
                    <p class="font-medium text-gray-800">
                        <?= htmlspecialchars($p['nama_sekolah_asal'] ?? ($p['asal_sekolah'] ?? '-')); ?></p>
                </div>
                <div>
                    <p class="text-gray-400 text-[10px] uppercase">NPSN</p>
                    <p class="font-medium text-gray-800"><?= htmlspecialchars($p['npsn_sekolah_asal'] ?? '-'); ?></p>
                </div>
                <div>
                    <p class="text-gray-400 text-[10px] uppercase">Tahun Lulus</p>
                    <p class="font-medium text-gray-800"><?= htmlspecialchars($p['tahun_lulus'] ?? '-'); ?></p>
                </div>
            </div>
        </div>

        <!-- Data Orang Tua -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <div class="bg-white rounded-xl shadow-sm border p-4">
                <div class="flex items-center gap-2 mb-3 pb-2 border-b">
                    <i data-lucide="user-check" class="w-4 h-4 text-blue-500"></i>
                    <h3 class="font-bold text-sm text-gray-800">Data Ayah</h3>
                </div>
                <div class="space-y-2 text-xs sm:text-sm">
                    <div>
                        <p class="text-gray-400 text-[10px] uppercase">Nama</p>
                        <p class="font-medium text-gray-800"><?= htmlspecialchars($p['ayah_nama'] ?? '-'); ?></p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-[10px] uppercase">NIK</p>
                        <p class="font-medium text-gray-800 break-all"><?= htmlspecialchars($p['ayah_nik'] ?? '-'); ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-[10px] uppercase">Pekerjaan</p>
                        <p class="font-medium text-gray-800"><?= htmlspecialchars($p['ayah_pekerjaan'] ?? '-'); ?></p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-[10px] uppercase">No. HP</p>
                        <p class="font-medium text-gray-800"><?= htmlspecialchars($p['ayah_no_hp'] ?? '-'); ?></p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border p-4">
                <div class="flex items-center gap-2 mb-3 pb-2 border-b">
                    <i data-lucide="heart" class="w-4 h-4 text-pink-500"></i>
                    <h3 class="font-bold text-sm text-gray-800">Data Ibu</h3>
                </div>
                <div class="space-y-2 text-xs sm:text-sm">
                    <div>
                        <p class="text-gray-400 text-[10px] uppercase">Nama</p>
                        <p class="font-medium text-gray-800"><?= htmlspecialchars($p['ibu_nama'] ?? '-'); ?></p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-[10px] uppercase">NIK</p>
                        <p class="font-medium text-gray-800 break-all"><?= htmlspecialchars($p['ibu_nik'] ?? '-'); ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-[10px] uppercase">Pekerjaan</p>
                        <p class="font-medium text-gray-800"><?= htmlspecialchars($p['ibu_pekerjaan'] ?? '-'); ?></p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-[10px] uppercase">No. HP</p>
                        <p class="font-medium text-gray-800"><?= htmlspecialchars($p['ibu_no_hp'] ?? '-'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Wali -->
        <?php if (!empty($p['wali_nama'])): ?>
            <div class="bg-white rounded-xl shadow-sm border p-4 sm:p-5 mb-4">
                <div class="flex items-center gap-2 mb-3 pb-2 border-b">
                    <i data-lucide="users" class="w-4 h-4 text-amber-500"></i>
                    <h3 class="font-bold text-sm sm:text-base text-gray-800">Data Wali</h3>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs sm:text-sm">
                    <div>
                        <p class="text-gray-400 text-[10px] uppercase">Nama</p>
                        <p class="font-medium text-gray-800"><?= htmlspecialchars($p['wali_nama']); ?></p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-[10px] uppercase">Hubungan</p>
                        <p class="font-medium text-gray-800"><?= htmlspecialchars($p['wali_hubungan'] ?? '-'); ?></p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-[10px] uppercase">Pekerjaan</p>
                        <p class="font-medium text-gray-800"><?= htmlspecialchars($p['wali_pekerjaan'] ?? '-'); ?></p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-[10px] uppercase">No. HP</p>
                        <p class="font-medium text-gray-800"><?= htmlspecialchars($p['wali_no_hp'] ?? '-'); ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Dokumen -->
        <div class="bg-white rounded-xl shadow-sm border p-4 sm:p-5 mb-4">
            <div class="flex items-center gap-2 mb-3 pb-2 border-b">
                <i data-lucide="file-text" class="w-4 h-4 text-orange-500"></i>
                <h3 class="font-bold text-sm sm:text-base text-gray-800">Dokumen Pendukung</h3>
            </div>
            <?php if (!empty($dokumen)): ?>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 sm:gap-3">
                    <?php foreach ($dokumen as $dok):
                        $label = $jenisLabels[$dok['jenis_dokumen']] ?? ucfirst(str_replace('_', ' ', $dok['jenis_dokumen']));
                        
                        // Check if file is on Google Drive
                        $isGoogleDrive = !empty($dok['drive_url']);
                        $driveFileId = $dok['drive_file_id'] ?? '';
                        
                        // URL untuk preview (embed)
                        if ($isGoogleDrive && $driveFileId) {
                            $previewUrl = 'https://drive.google.com/file/d/' . $driveFileId . '/preview';
                        } else {
                            $previewUrl = BASEURL . '/psb/serveFile/' . $p['id_pendaftar'] . '/' . $dok['jenis_dokumen'];
                        }
                        ?>
                        <button type="button" onclick="openDocModal('<?= $label; ?>', '<?= $previewUrl; ?>')"
                            class="bg-gray-50 rounded-lg p-3 flex items-center gap-2 hover:bg-gray-100 transition-colors text-left w-full">
                            <div class="bg-green-100 p-1.5 rounded flex-shrink-0">
                                <i data-lucide="file-check" class="w-4 h-4 text-green-600"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="font-medium text-gray-800 text-xs truncate"><?= $label; ?></p>
                                <p class="text-[10px] text-gray-500"><?= $isGoogleDrive ? '☁️ Google Drive' : 'Klik untuk lihat'; ?></p>
                            </div>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-6 text-gray-400">
                    <i data-lucide="file-x" class="w-10 h-10 mx-auto mb-2 opacity-50"></i>
                    <p class="text-sm">Belum ada dokumen</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tombol Aksi -->
        <div class="flex flex-col sm:flex-row gap-3 items-center justify-center pb-4">
            <a href="<?= BASEURL; ?>/psb/cetakFormulir/<?= $p['id_pendaftar']; ?>"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors text-sm font-medium w-full sm:w-auto justify-center">
                <i data-lucide="download" class="w-4 h-4"></i>
                Unduh PDF
            </a>
            <a href="<?= BASEURL; ?>/psb/kirimFormulirWA/<?= $p['id_pendaftar']; ?>"
                onclick="return confirm('Kirim formulir PDF ke WhatsApp?')"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors text-sm font-medium w-full sm:w-auto justify-center">
                <i data-lucide="send" class="w-4 h-4"></i>
                Kirim ke WhatsApp
            </a>
            <a href="<?= BASEURL; ?>/psb/dashboardPendaftar"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors text-sm font-medium w-full sm:w-auto justify-center">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Kembali
            </a>
        </div>
    </main>

    <!-- Modal Preview Dokumen -->
    <div id="docModal" class="fixed inset-0 z-[100] hidden">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeDocModal()"></div>
        <div
            class="absolute inset-4 sm:inset-8 md:inset-12 lg:inset-20 bg-white rounded-xl shadow-2xl flex flex-col overflow-hidden">
            <div class="flex items-center justify-between px-4 py-3 border-b bg-gray-50">
                <h4 id="docModalTitle" class="font-bold text-gray-800 text-sm sm:text-base truncate"></h4>
                <button onclick="closeDocModal()" class="p-1.5 hover:bg-gray-200 rounded-lg transition-colors">
                    <i data-lucide="x" class="w-5 h-5 text-gray-600"></i>
                </button>
            </div>
            <div class="flex-1 overflow-auto bg-gray-100 p-2">
                <iframe id="docModalFrame" class="w-full h-full min-h-[300px] bg-white rounded border"
                    frameborder="0"></iframe>
            </div>
        </div>
    </div>

    <footer class="bg-white border-t py-4">
        <div class="max-w-4xl mx-auto px-4 text-center text-gray-400 text-xs">
            &copy; <?= date('Y'); ?> <?= $namaSekolah; ?>
        </div>
    </footer>

    <script>
        lucide.createIcons();

        function openDocModal(title, url) {
            document.getElementById('docModalTitle').textContent = title;
            document.getElementById('docModalFrame').src = url;
            document.getElementById('docModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeDocModal() {
            document.getElementById('docModal').classList.add('hidden');
            document.getElementById('docModalFrame').src = '';
            document.body.style.overflow = '';
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape')  closeDocModal();
        });
    </script>
</body>

</html>