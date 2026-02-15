<?php
// File: app/views/templates/sidebar_admin.php - COLLAPSIBLE DROPDOWN VERSION
// Data sidebar sudah dikirim dari controller melalui $data

// Set default values jika data sidebar tidak ada
$sidebarData = [
    'attendance_percentage' => 0,
    'total_kelas' => 0,
    'total_siswa' => 0,
    'total_guru' => 0
];

// Override dengan data dari controller jika ada
if (isset($data['sidebar_data'])) {
    $sidebarData = array_merge($sidebarData, $data['sidebar_data']);
}

// Get pengaturan aplikasi
$pengaturanApp = getPengaturanAplikasi();
$namaAplikasi = htmlspecialchars($pengaturanApp['nama_aplikasi'] ?? 'Smart Absensi');
$logoApp = $pengaturanApp['logo'] ?? '';

// Cek apakah file logo ada
$baseDir = dirname(dirname(dirname(__DIR__))); // Path ke root folder absen
$logoPath = $baseDir . '/public/img/app/' . $logoApp;
$logoExists = !empty($logoApp) && file_exists($logoPath);
?>
<aside id="sidebar" class="sidebar fixed top-0 left-0 md:relative z-[60]
         w-72 md:w-64 bg-white md:bg-transparent md:glass-effect
         flex-shrink-0 h-screen md:h-auto flex flex-col
         border-r border-white/20 shadow-2xl
         transition-transform duration-300 ease-in-out
         -translate-x-full md:translate-x-0 overflow-y-auto isolate" aria-expanded="false">
    <!-- Logo Header -->
    <div
        class="p-6 border-b border-white/20 flex items-center justify-between h-20 bg-white/95 md:bg-transparent backdrop-blur-sm">
        <div class="flex items-center">
            <?php if ($logoExists): ?>
                <div class="bg-white p-1 rounded-xl shadow-lg">
                    <img src="<?= BASEURL; ?>/public/img/app/<?= htmlspecialchars($logoApp); ?>" alt="<?= $namaAplikasi; ?>"
                        class="w-8 h-8 object-contain">
                </div>
            <?php else: ?>
                <div class="gradient-primary p-2 rounded-xl shadow-lg">
                    <i data-lucide="graduation-cap" class="w-6 h-6 text-white"></i>
                </div>
            <?php endif; ?>
            <div class="ml-3 min-w-0">
                <h1 class="text-lg font-bold text-secondary-800 leading-tight break-words"><?= $namaAplikasi; ?></h1>
                <p class="text-xs text-secondary-500 font-medium mt-0.5">Admin Panel</p>
            </div>
        </div>
        <button id="sidebar-toggle-btn"
            class="p-2 rounded-lg text-secondary-400 hover:bg-white/50 transition-colors duration-200 md:hidden"
            aria-label="Tutup sidebar">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>
    </div>

    <!-- Session Info Quick Access -->
    <div class="px-6 py-3 bg-gradient-to-r from-indigo-50 to-blue-50 border-b border-white/20">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-secondary-500 font-medium">Sesi Aktif</p>
                <p class="text-sm font-bold text-secondary-800">
                    <?= $_SESSION['nama_semester_aktif'] ?? '2024/2025 - Ganjil'; ?>
                </p>
            </div>
            <?php if (isset($data['daftar_semester']) && !empty($data['daftar_semester'])): ?>
                <form method="POST" action="<?= BASEURL; ?>/admin/setAktifTP" class="inline">
                    <select name="id_semester" onchange="this.form.submit()"
                        class="text-xs bg-primary-100 text-primary-700 px-2 py-1 rounded-lg hover:bg-primary-200 transition-colors border-0">
                        <?php foreach ($data['daftar_semester'] as $semester): ?>
                            <option value="<?= $semester['id_semester']; ?>" <?= (isset($_SESSION['id_semester_aktif']) && $_SESSION['id_semester_aktif'] == $semester['id_semester']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($semester['nama_tp'] . ' - ' . $semester['semester']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Navigation -->
    <nav
        class="flex-1 overflow-y-auto sidebar-nav p-4 bg-white/90 md:bg-transparent backdrop-blur-sm md:backdrop-blur-none">
        <ul class="space-y-1">
            <?php
            $judul = $data['judul'] ?? '';

            // Helper function untuk cek judul
            function isActive($judul, $keyword)
            {
                return !empty($judul) && strpos($judul, $keyword) !== false;
            }

            // Check if any menu in group is active
            function isGroupActive($judul, $keywords)
            {
                foreach ($keywords as $kw) {
                    if (isActive($judul, $kw))
                        return true;
                }
                return false;
            }
            ?>

            <!-- ============================================== -->
            <!-- DASHBOARD -->
            <!-- ============================================== -->
            <li>
                <a href="<?= BASEURL; ?>/admin/dashboard"
                    class="group flex items-center p-3 text-sm font-semibold rounded-xl transition-all duration-200 <?= ($judul == 'Dashboard Admin') ? 'gradient-primary text-white shadow-lg' : 'text-secondary-600 hover:bg-white/50 hover:text-secondary-800'; ?>">
                    <div
                        class="<?= ($judul == 'Dashboard Admin') ? 'bg-white/20' : 'bg-secondary-100 group-hover:bg-primary-100'; ?> p-2 rounded-lg transition-colors duration-200">
                        <i data-lucide="layout-dashboard"
                            class="w-4 h-4 <?= ($judul == 'Dashboard Admin') ? 'text-white' : 'text-secondary-500 group-hover:text-primary-600'; ?>"></i>
                    </div>
                    <span class="ml-3 whitespace-nowrap flex-1">Dashboard</span>
                </a>
            </li>


            <!-- ============================================== -->
            <!-- DROPDOWN: APLIKASI LAIN -->
            <!-- ============================================== -->
            <?php $aplikasiLainActive = isGroupActive($judul, ['Pesan', 'Buku Tamu', 'PSB', 'Surat Tugas', 'Website']); ?>
            <li class="pt-4" x-data="{ open: <?= $aplikasiLainActive ? 'true' : 'false' ?> }">
                <button @click="open = !open"
                    class="w-full group flex items-center p-3 text-sm font-semibold rounded-xl transition-all duration-200 <?= $aplikasiLainActive ? 'bg-purple-50 text-purple-700' : 'text-secondary-600 hover:bg-white/50' ?>">
                    <div
                        class="<?= $aplikasiLainActive ? 'bg-purple-200' : 'bg-purple-100 group-hover:bg-purple-200' ?> p-2 rounded-lg transition-colors duration-200">
                        <i data-lucide="layout-grid"
                            class="w-4 h-4 <?= $aplikasiLainActive ? 'text-purple-700' : 'text-purple-600' ?>"></i>
                    </div>
                    <span class="ml-3 whitespace-nowrap flex-1 text-left">Aplikasi Lain</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-200"
                        :class="open ? 'rotate-180' : ''"></i>
                </button>
                <ul x-show="open" x-collapse class="mt-1 ml-4 space-y-1 border-l-2 border-purple-200 pl-3">
                    <li>
                        <a href="<?= BASEURL; ?>/admin/pesan"
                            class="group flex items-center p-2.5 text-sm font-medium rounded-lg transition-all duration-200 <?= isActive($judul, 'Pesan') ? 'bg-primary-100 text-primary-700' : 'text-secondary-600 hover:bg-indigo-50 hover:text-indigo-700' ?>">
                            <i data-lucide="mail" class="w-4 h-4 mr-2 text-indigo-600"></i>
                            Pesan
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASEURL; ?>/bukuTamu"
                            class="group flex items-center p-2.5 text-sm font-medium rounded-lg transition-all duration-200 <?= isActive($judul, 'Buku Tamu') ? 'bg-primary-100 text-primary-700' : 'text-secondary-600 hover:bg-teal-50 hover:text-teal-700' ?>">
                            <i data-lucide="book-user" class="w-4 h-4 mr-2 text-teal-600"></i>
                            Buku Tamu Digital
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASEURL; ?>/psb/dashboard"
                            class="group flex items-center p-2.5 text-sm font-medium rounded-lg transition-all duration-200 text-secondary-600 hover:bg-sky-50 hover:text-sky-700">
                            <i data-lucide="user-plus" class="w-4 h-4 mr-2 text-sky-600"></i>
                            Panel PSB
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASEURL; ?>/suratTugas"
                            class="group flex items-center p-2.5 text-sm font-medium rounded-lg transition-all duration-200 text-secondary-600 hover:bg-indigo-50 hover:text-indigo-700">
                            <i data-lucide="file-text" class="w-4 h-4 mr-2 text-indigo-600"></i>
                            Panel Surat Tugas
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASEURL; ?>/cms/identitas"
                            class="group flex items-center p-2.5 text-sm font-medium rounded-lg transition-all duration-200 text-secondary-600 hover:bg-purple-50 hover:text-purple-700">
                            <i data-lucide="globe" class="w-4 h-4 mr-2 text-purple-600"></i>
                            Website Sekolah
                        </a>
                    </li>
                </ul>
            </li>

            <!-- ============================================== -->
            <!-- DROPDOWN: DATA MASTER -->
            <!-- ============================================== -->
            <?php $dataMasterActive = isGroupActive($judul, ['Tahun Pelajaran', 'Kelas', 'Siswa', 'Monitoring Dokumen', 'Guru', 'Mata Pelajaran']); ?>
            <li class="pt-2" x-data="{ open: <?= $dataMasterActive ? 'true' : 'false' ?> }">
                <button @click="open = !open"
                    class="w-full group flex items-center p-3 text-sm font-semibold rounded-xl transition-all duration-200 <?= $dataMasterActive ? 'bg-primary-50 text-primary-700' : 'text-secondary-600 hover:bg-white/50' ?>">
                    <div
                        class="<?= $dataMasterActive ? 'bg-primary-200' : 'bg-blue-100 group-hover:bg-blue-200' ?> p-2 rounded-lg transition-colors duration-200">
                        <i data-lucide="database"
                            class="w-4 h-4 <?= $dataMasterActive ? 'text-primary-700' : 'text-blue-600' ?>"></i>
                    </div>
                    <span class="ml-3 whitespace-nowrap flex-1 text-left">Data Master</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-200"
                        :class="open ? 'rotate-180' : ''"></i>
                </button>
                <ul x-show="open" x-collapse class="mt-1 ml-4 space-y-1 border-l-2 border-blue-200 pl-3">
                    <li>
                        <a href="<?= BASEURL; ?>/admin/tahunPelajaran"
                            class="flex items-center p-2.5 text-sm font-medium rounded-lg transition-all duration-200 <?= isActive($judul, 'Tahun Pelajaran') ? 'bg-primary-100 text-primary-700' : 'text-secondary-600 hover:bg-blue-50 hover:text-blue-700' ?>">
                            <i data-lucide="calendar-days" class="w-4 h-4 mr-2"></i>
                            Tahun Pelajaran
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASEURL; ?>/admin/kelas"
                            class="flex items-center p-2.5 text-sm font-medium rounded-lg transition-all duration-200 <?= isActive($judul, 'Kelas') ? 'bg-primary-100 text-primary-700' : 'text-secondary-600 hover:bg-purple-50 hover:text-purple-700' ?>">
                            <i data-lucide="school" class="w-4 h-4 mr-2"></i>
                            Kelas
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASEURL; ?>/admin/siswa"
                            class="flex items-center p-2.5 text-sm font-medium rounded-lg transition-all duration-200 <?= isActive($judul, 'Siswa') ? 'bg-primary-100 text-primary-700' : 'text-secondary-600 hover:bg-blue-50 hover:text-blue-700' ?>">
                            <i data-lucide="users" class="w-4 h-4 mr-2"></i>
                            Siswa
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASEURL; ?>/admin/monitoringDokumen"
                            class="flex items-center p-2.5 text-sm font-medium rounded-lg transition-all duration-200 <?= isActive($judul, 'Monitoring Dokumen') ? 'bg-primary-100 text-primary-700' : 'text-secondary-600 hover:bg-blue-50 hover:text-blue-700' ?>">
                            <i data-lucide="file-check" class="w-4 h-4 mr-2"></i>
                            Monitoring Dokumen
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASEURL; ?>/admin/guru"
                            class="flex items-center p-2.5 text-sm font-medium rounded-lg transition-all duration-200 <?= isActive($judul, 'Guru') ? 'bg-primary-100 text-primary-700' : 'text-secondary-600 hover:bg-green-50 hover:text-green-700' ?>">
                            <i data-lucide="user-check" class="w-4 h-4 mr-2"></i>
                            Guru
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASEURL; ?>/admin/mapel"
                            class="flex items-center p-2.5 text-sm font-medium rounded-lg transition-all duration-200 <?= isActive($judul, 'Mata Pelajaran') ? 'bg-primary-100 text-primary-700' : 'text-secondary-600 hover:bg-orange-50 hover:text-orange-700' ?>">
                            <i data-lucide="book-copy" class="w-4 h-4 mr-2"></i>
                            Mata Pelajaran
                        </a>
                    </li>
                </ul>
            </li>

            <!-- ============================================== -->
            <!-- DROPDOWN: AKADEMIK -->
            <!-- ============================================== -->
            <?php $akademikActive = isGroupActive($judul, ['Penugasan', 'Anggota Kelas', 'Monitoring Nilai', 'Review RPP', 'Pengaturan RPP', 'Pengaturan Rapor']); ?>
            <li class="pt-2" x-data="{ open: <?= $akademikActive ? 'true' : 'false' ?> }">
                <button @click="open = !open"
                    class="w-full group flex items-center p-3 text-sm font-semibold rounded-xl transition-all duration-200 <?= $akademikActive ? 'bg-primary-50 text-primary-700' : 'text-secondary-600 hover:bg-white/50' ?>">
                    <div
                        class="<?= $akademikActive ? 'bg-primary-200' : 'bg-indigo-100 group-hover:bg-indigo-200' ?> p-2 rounded-lg transition-colors duration-200">
                        <i data-lucide="book-open"
                            class="w-4 h-4 <?= $akademikActive ? 'text-primary-700' : 'text-indigo-600' ?>"></i>
                    </div>
                    <span class="ml-3 whitespace-nowrap flex-1 text-left">Akademik</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-200"
                        :class="open ? 'rotate-180' : ''"></i>
                </button>
                <ul x-show="open" x-collapse class="mt-1 ml-4 space-y-1 border-l-2 border-indigo-200 pl-3">
                    <li>
                        <a href="<?= BASEURL; ?>/admin/penugasan"
                            class="flex items-center p-2.5 text-sm font-medium rounded-lg transition-all duration-200 <?= isActive($judul, 'Penugasan') ? 'bg-primary-100 text-primary-700' : 'text-secondary-600 hover:bg-indigo-50 hover:text-indigo-700' ?>">
                            <i data-lucide="link" class="w-4 h-4 mr-2"></i>
                            Penugasan Guru
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASEURL; ?>/admin/keanggotaan"
                            class="flex items-center p-2.5 text-sm font-medium rounded-lg transition-all duration-200 <?= isActive($judul, 'Anggota Kelas') ? 'bg-primary-100 text-primary-700' : 'text-secondary-600 hover:bg-teal-50 hover:text-teal-700' ?>">
                            <i data-lucide="users-2" class="w-4 h-4 mr-2"></i>
                            Anggota Kelas
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASEURL; ?>/admin/monitoringNilai"
                            class="flex items-center p-2.5 text-sm font-medium rounded-lg transition-all duration-200 <?= isActive($judul, 'Monitoring Nilai') ? 'bg-primary-100 text-primary-700' : 'text-secondary-600 hover:bg-indigo-50 hover:text-indigo-700' ?>">
                            <i data-lucide="bar-chart-3" class="w-4 h-4 mr-2"></i>
                            Monitoring Nilai
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASEURL; ?>/admin/listRPPReview"
                            class="flex items-center p-2.5 text-sm font-medium rounded-lg transition-all duration-200 <?= isActive($judul, 'Review RPP') ? 'bg-primary-100 text-primary-700' : 'text-secondary-600 hover:bg-amber-50 hover:text-amber-700' ?>">
                            <i data-lucide="file-check" class="w-4 h-4 mr-2"></i>
                            Review RPP
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASEURL; ?>/admin/pengaturanRPP"
                            class="flex items-center p-2.5 text-sm font-medium rounded-lg transition-all duration-200 <?= isActive($judul, 'Pengaturan RPP') ? 'bg-primary-100 text-primary-700' : 'text-secondary-600 hover:bg-rose-50 hover:text-rose-700' ?>">
                            <i data-lucide="file-cog" class="w-4 h-4 mr-2"></i>
                            Pengaturan RPP
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASEURL; ?>/admin/pengaturanRapor"
                            class="flex items-center p-2.5 text-sm font-medium rounded-lg transition-all duration-200 <?= isActive($judul, 'Pengaturan Rapor') ? 'bg-primary-100 text-primary-700' : 'text-secondary-600 hover:bg-teal-50 hover:text-teal-700' ?>">
                            <i data-lucide="file-badge" class="w-4 h-4 mr-2"></i>
                            Pengaturan Rapor
                        </a>
                    </li>
                </ul>
            </li>

            <!-- ============================================== -->
            <!-- DROPDOWN: KESISWAAN -->
            <!-- ============================================== -->
            <?php $kesiswaanActive = isGroupActive($judul, ['Performa Kehadiran Siswa', 'Naik Kelas', 'Kelulusan']); ?>
            <li class="pt-2" x-data="{ open: <?= $kesiswaanActive ? 'true' : 'false' ?> }">
                <button @click="open = !open"
                    class="w-full group flex items-center p-3 text-sm font-semibold rounded-xl transition-all duration-200 <?= $kesiswaanActive ? 'bg-primary-50 text-primary-700' : 'text-secondary-600 hover:bg-white/50' ?>">
                    <div
                        class="<?= $kesiswaanActive ? 'bg-primary-200' : 'bg-cyan-100 group-hover:bg-cyan-200' ?> p-2 rounded-lg transition-colors duration-200">
                        <i data-lucide="users"
                            class="w-4 h-4 <?= $kesiswaanActive ? 'text-primary-700' : 'text-cyan-600' ?>"></i>
                    </div>
                    <span class="ml-3 whitespace-nowrap flex-1 text-left">Kesiswaan</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-200"
                        :class="open ? 'rotate-180' : ''"></i>
                </button>
                <ul x-show="open" x-collapse class="mt-1 ml-4 space-y-1 border-l-2 border-cyan-200 pl-3">
                    <li>
                        <a href="<?= BASEURL; ?>/PerformaSiswa"
                            class="flex items-center p-2.5 text-sm font-medium rounded-lg transition-all duration-200 <?= ($judul == 'Performa Kehadiran Siswa') ? 'bg-primary-100 text-primary-700' : 'text-secondary-600 hover:bg-cyan-50 hover:text-cyan-700' ?>">
                            <i data-lucide="activity" class="w-4 h-4 mr-2"></i>
                            Performa Kehadiran
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASEURL; ?>/admin/naikKelas"
                            class="flex items-center p-2.5 text-sm font-medium rounded-lg transition-all duration-200 <?= isActive($judul, 'Naik Kelas') ? 'bg-primary-100 text-primary-700' : 'text-secondary-600 hover:bg-green-50 hover:text-green-700' ?>">
                            <i data-lucide="trending-up" class="w-4 h-4 mr-2"></i>
                            Naik Kelas
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASEURL; ?>/admin/kelulusan"
                            class="flex items-center p-2.5 text-sm font-medium rounded-lg transition-all duration-200 <?= isActive($judul, 'Kelulusan') ? 'bg-primary-100 text-primary-700' : 'text-secondary-600 hover:bg-amber-50 hover:text-amber-700' ?>">
                            <i data-lucide="graduation-cap" class="w-4 h-4 mr-2"></i>
                            Kelulusan
                        </a>
                    </li>
                </ul>
            </li>

            <!-- ============================================== -->
            <!-- SINGLE: KEPEGAWAIAN (Performa Guru) -->
            <!-- ============================================== -->
            <li class="pt-2">
                <a href="<?= BASEURL; ?>/PerformaGuru"
                    class="group flex items-center p-3 text-sm font-medium rounded-xl transition-all duration-200 <?= ($judul == 'Performa Kinerja Guru') ? 'gradient-primary text-white shadow-lg' : 'text-secondary-600 hover:bg-white/50 hover:text-secondary-800'; ?>">
                    <div
                        class="<?= ($judul == 'Performa Kinerja Guru') ? 'bg-white/20' : 'bg-emerald-100 group-hover:bg-emerald-200'; ?> p-2 rounded-lg transition-colors duration-200">
                        <i data-lucide="user-check-2"
                            class="w-4 h-4 <?= ($judul == 'Performa Kinerja Guru') ? 'text-white' : 'text-emerald-600 group-hover:text-emerald-700'; ?>"></i>
                    </div>
                    <span class="ml-3 whitespace-nowrap">Performa Guru</span>
                </a>
            </li>

            <!-- ============================================== -->
            <!-- SINGLE: KEUANGAN (Pembayaran SPP) -->
            <!-- ============================================== -->
            <li class="pt-2">
                <a href="<?= BASEURL; ?>/admin/pembayaran"
                    class="group flex items-center p-3 text-sm font-medium rounded-xl transition-all duration-200 <?= isActive($judul, 'Pembayaran') ? 'gradient-primary text-white shadow-lg' : 'text-secondary-600 hover:bg-white/50 hover:text-secondary-800'; ?>">
                    <div
                        class="<?= isActive($judul, 'Pembayaran') ? 'bg-white/20' : 'bg-blue-100 group-hover:bg-blue-200'; ?> p-2 rounded-lg transition-colors duration-200">
                        <i data-lucide="wallet"
                            class="w-4 h-4 <?= isActive($judul, 'Pembayaran') ? 'text-white' : 'text-blue-600 group-hover:text-blue-700'; ?>"></i>
                    </div>
                    <span class="ml-3 whitespace-nowrap">Pembayaran SPP</span>
                </a>
            </li>

            <!-- ============================================== -->
            <!-- DROPDOWN: PENGATURAN -->
            <!-- ============================================== -->
            <?php $pengaturanActive = isGroupActive($judul, ['Konfigurasi QR', 'Pengaturan Aplikasi', 'Pengaturan Menu', 'Pengaturan Fungsi Guru', 'Antrian Pesan WhatsApp', 'Riwayat Login', 'Pengaturan WA Gateway', 'Pengaturan Notifikasi Absensi']); ?>
            <li class="pt-2" x-data="{ open: <?= $pengaturanActive ? 'true' : 'false' ?> }">
                <button @click="open = !open"
                    class="w-full group flex items-center p-3 text-sm font-semibold rounded-xl transition-all duration-200 <?= $pengaturanActive ? 'bg-primary-50 text-primary-700' : 'text-secondary-600 hover:bg-white/50' ?>">
                    <div
                        class="<?= $pengaturanActive ? 'bg-primary-200' : 'bg-slate-100 group-hover:bg-slate-200' ?> p-2 rounded-lg transition-colors duration-200">
                        <i data-lucide="settings"
                            class="w-4 h-4 <?= $pengaturanActive ? 'text-primary-700' : 'text-slate-600' ?>"></i>
                    </div>
                    <span class="ml-3 whitespace-nowrap flex-1 text-left">Pengaturan</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-200"
                        :class="open ? 'rotate-180' : ''"></i>
                </button>
                <ul x-show="open" x-collapse class="mt-1 ml-4 space-y-1 border-l-2 border-slate-200 pl-3">
                    <li>
                        <a href="<?= BASEURL; ?>/admin/waGateway"
                            class="flex items-center p-2.5 text-sm font-medium rounded-lg transition-all duration-200 <?= isActive($judul, 'Pengaturan WA Gateway') ? 'bg-primary-100 text-primary-700' : 'text-secondary-600 hover:bg-green-50 hover:text-green-700' ?>">
                            <i data-lucide="smartphone" class="w-4 h-4 mr-2"></i>
                            WA Gateway (Multi)
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASEURL; ?>/admin/pengaturanNotifikasiAbsensi"
                            class="flex items-center p-2.5 text-sm font-medium rounded-lg transition-all duration-200 <?= isActive($judul, 'Pengaturan Notifikasi Absensi') ? 'bg-primary-100 text-primary-700' : 'text-secondary-600 hover:bg-orange-50 hover:text-orange-700' ?>">
                            <i data-lucide="bell" class="w-4 h-4 mr-2"></i>
                            Notifikasi Absensi
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASEURL; ?>/admin/riwayatLogin"
                            class="flex items-center p-2.5 text-sm font-medium rounded-lg transition-all duration-200 <?= isActive($judul, 'Riwayat Login') ? 'bg-primary-100 text-primary-700' : 'text-secondary-600 hover:bg-cyan-50 hover:text-cyan-700' ?>">
                            <i data-lucide="shield-check" class="w-4 h-4 mr-2"></i>
                            Riwayat Login
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASEURL; ?>/admin/configQR"
                            class="flex items-center p-2.5 text-sm font-medium rounded-lg transition-all duration-200 <?= isActive($judul, 'Konfigurasi QR') ? 'bg-primary-100 text-primary-700' : 'text-secondary-600 hover:bg-purple-50 hover:text-purple-700' ?>">
                            <i data-lucide="qr-code" class="w-4 h-4 mr-2"></i>
                            Konfigurasi QR
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASEURL; ?>/admin/pengaturanAplikasi"
                            class="flex items-center p-2.5 text-sm font-medium rounded-lg transition-all duration-200 <?= isActive($judul, 'Pengaturan Aplikasi') ? 'bg-primary-100 text-primary-700' : 'text-secondary-600 hover:bg-indigo-50 hover:text-indigo-700' ?>">
                            <i data-lucide="settings-2" class="w-4 h-4 mr-2"></i>
                            Pengaturan Aplikasi
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASEURL; ?>/admin/pengaturanFieldSiswa"
                            class="flex items-center p-2.5 text-sm font-medium rounded-lg transition-all duration-200 <?= isActive($judul, 'Pengaturan Field Data Siswa') ? 'bg-primary-100 text-primary-700' : 'text-secondary-600 hover:bg-cyan-50 hover:text-cyan-700' ?>">
                            <i data-lucide="toggle-right" class="w-4 h-4 mr-2"></i>
                            Pengaturan Field Siswa
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASEURL; ?>/admin/pengaturanMenu"
                            class="flex items-center p-2.5 text-sm font-medium rounded-lg transition-all duration-200 <?= isActive($judul, 'Pengaturan Menu') ? 'bg-primary-100 text-primary-700' : 'text-secondary-600 hover:bg-blue-50 hover:text-blue-700' ?>">
                            <i data-lucide="menu" class="w-4 h-4 mr-2"></i>
                            Pengaturan Menu
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASEURL; ?>/admin/pengaturanRole"
                            class="flex items-center p-2.5 text-sm font-medium rounded-lg transition-all duration-200 <?= isActive($judul, 'Pengaturan Fungsi Guru') ? 'bg-primary-100 text-primary-700' : 'text-secondary-600 hover:bg-amber-50 hover:text-amber-700' ?>">
                            <i data-lucide="user-cog" class="w-4 h-4 mr-2"></i>
                            Fungsi Guru
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASEURL; ?>/update"
                            class="flex items-center p-2.5 text-sm font-medium rounded-lg transition-all duration-200 <?= isActive($judul, 'Pembaruan Aplikasi') ? 'bg-primary-100 text-primary-700' : 'text-secondary-600 hover:bg-green-50 hover:text-green-700' ?>">
                            <i data-lucide="download-cloud" class="w-4 h-4 mr-2"></i>
                            Pembaruan Aplikasi
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASEURL; ?>/admin/antrianWa"
                            class="flex items-center p-2.5 text-sm font-medium rounded-lg transition-all duration-200 <?= isActive($judul, 'Antrian Pesan WhatsApp') ? 'bg-primary-100 text-primary-700' : 'text-secondary-600 hover:bg-teal-50 hover:text-teal-700' ?>">
                            <i data-lucide="message-circle" class="w-4 h-4 mr-2"></i>
                            Antrian WA
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>

    <!-- Footer dengan Logout -->
    <div
        class="flex-shrink-0 p-4 border-t border-white/20 mt-auto bg-white/95 md:bg-transparent backdrop-blur-sm md:backdrop-blur-none pb-safe">
        <!-- Version Info -->
        <?php
        $versionFile = dirname(dirname(dirname(__DIR__))) . '/version.json';
        $versionData = file_exists($versionFile) ? json_decode(file_get_contents($versionFile), true) : [];
        $appVersion = $versionData['version'] ?? '1.0.0';
        ?>
        <div class="text-center text-xs text-secondary-400 mb-3">
            <span class="font-medium"><?= $namaAplikasi; ?></span>
            <span class="text-secondary-300">•</span>
            <span>v<?= $appVersion; ?></span>
        </div>
        <!-- Logout -->
        <a href="<?= BASEURL; ?>/auth/logout"
            class="group flex items-center p-3 text-sm font-medium text-danger-600 hover:bg-danger-50 rounded-xl transition-all duration-200 w-full">
            <div class="bg-danger-100 group-hover:bg-danger-200 p-2 rounded-lg transition-colors duration-200">
                <i data-lucide="log-out" class="w-4 h-4 text-danger-600"></i>
            </div>
            <span class="ml-3 whitespace-nowrap font-semibold">Logout</span>
            <i data-lucide="arrow-right"
                class="w-4 h-4 ml-auto opacity-0 group-hover:opacity-100 transition-opacity duration-200"></i>
        </a>
    </div>
</aside>

<!-- Mobile Overlay -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 md:hidden hidden"></div>

<style>
    /* Mobile */
    @media (max-width: 767px) {
        .sidebar {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.96) 0%, rgba(248, 250, 252, 0.98) 100%);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }

        .sidebar::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.9);
            z-index: 0;
            pointer-events: none;
        }

        .sidebar>* {
            position: relative;
            z-index: 1;
        }
    }

    /* Smooth scroll untuk navigation */
    .sidebar-nav {
        scrollbar-width: thin;
        scrollbar-color: rgba(156, 163, 175, 0.3) transparent;
    }

    .sidebar-nav::-webkit-scrollbar {
        width: 4px;
    }

    .sidebar-nav::-webkit-scrollbar-track {
        background: transparent;
    }

    .sidebar-nav::-webkit-scrollbar-thumb {
        background-color: rgba(156, 163, 175, 0.3);
        border-radius: 2px;
    }

    .sidebar-nav::-webkit-scrollbar-thumb:hover {
        background-color: rgba(156, 163, 175, 0.5);
    }

    /* Safe area padding for mobile (iPhone notch, etc) */
    .pb-safe {
        padding-bottom: max(1rem, env(safe-area-inset-bottom));
    }

    /* Alpine.js collapse animation */
    [x-cloak] {
        display: none !important;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const sidebar = document.getElementById('sidebar');
        const sidebarToggleBtn = document.getElementById('sidebar-toggle-btn');
        const menuButton = document.getElementById('menu-button');
        const overlay = document.getElementById('sidebar-overlay');

        const hideSidebar = () => {
            sidebar.classList.add('-translate-x-full');
            sidebar.classList.remove('translate-x-0');
            overlay.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
            sidebar.setAttribute('aria-expanded', 'false');
        };

        const showSidebar = () => {
            sidebar.classList.remove('-translate-x-full');
            sidebar.classList.add('translate-x-0');
            overlay.classList.remove('hidden');
            if (window.innerWidth < 768) document.body.classList.add('overflow-hidden');
            sidebar.setAttribute('aria-expanded', 'true');
        };

        if (menuButton) {
            menuButton.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                showSidebar();
            });
        }

        if (sidebarToggleBtn) {
            sidebarToggleBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                hideSidebar();
            });
        }

        if (overlay) overlay.addEventListener('click', hideSidebar);

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && window.innerWidth < 768 && !overlay.classList.contains('hidden')) {
                hideSidebar();
            }
        });

        const sidebarLinks = sidebar.querySelectorAll('a[href]');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 768) setTimeout(hideSidebar, 100);
            });
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth >= 768) {
                document.body.classList.remove('overflow-hidden');
                overlay.classList.add('hidden');
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.remove('translate-x-0');
            } else {
                if (!overlay.classList.contains('hidden')) hideSidebar();
            }
        });

        sidebar.addEventListener('click', (e) => e.stopPropagation());

        // Touch events untuk mobile (swipe)
        let touchStartX = null;
        document.addEventListener('touchstart', (e) => {
            touchStartX = e.touches[0].clientX;
        }, { passive: true });

        document.addEventListener('touchmove', (e) => {
            if (touchStartX === null) return;
            const touchX = e.touches[0].clientX;
            const diffX = touchX - touchStartX;

            if (window.innerWidth < 768 && touchStartX < 20 && diffX > 50) {
                showSidebar();
                touchStartX = null;
            }
            if (window.innerWidth < 768 && !overlay.classList.contains('hidden') && diffX < -50) {
                hideSidebar();
                touchStartX = null;
            }
        }, { passive: true });

        document.addEventListener('touchend', () => { touchStartX = null; }, { passive: true });

        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>

<?php
// Include update notification modal
require_once APPROOT . '/app/core/Updater.php';
include APPROOT . '/app/views/components/update_notification.php';
?>