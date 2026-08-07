<?php
// File: app/views/templates/sidebar_bendahara.php
// Sidebar khusus untuk halaman Bendahara

// Get pengaturan aplikasi
$pengaturanApp = getPengaturanAplikasi();
$namaAplikasi = htmlspecialchars($pengaturanApp['nama_aplikasi'] ?? 'Smart Absensi');
$logoApp = $pengaturanApp['logo'] ?? '';

// Cek apakah file logo ada
$baseDir = dirname(dirname(dirname(__DIR__)));
$logoPath = $baseDir . '/public/img/app/' . $logoApp;
$logoExists = !empty($logoApp) && file_exists($logoPath);

// Helper untuk cek active menu
function isBendaharaActive($judul, $target)
{
    return strpos($judul, $target) !== false;
}

// Cek role user untuk menentukan link kembali
$userRole = $_SESSION['user_role'] ?? $_SESSION['role'] ?? 'guru';
if ($userRole === 'wali_kelas') {
    $backUrl = BASEURL . '/waliKelas/dashboard';
    $backText = 'Kembali ke Wali Kelas';
} elseif ($userRole === 'admin') {
    $backUrl = BASEURL . '/admin';
    $backText = 'Kembali ke Admin';
} else {
    $backUrl = BASEURL . '/guru/dashboard';
    $backText = 'Kembali ke Dashboard';
}
?>

<aside id="sidebar" class="sidebar fixed top-0 left-0 md:relative z-[60]
         w-72 md:w-64 bg-white md:bg-transparent md:glass-effect
         flex-shrink-0 h-screen md:h-auto flex flex-col
         border-r border-white/20 shadow-2xl
         transition-transform duration-300 ease-in-out
         -translate-x-full md:translate-x-0 overflow-y-auto isolate" aria-expanded="false">

    <!-- Logo Header -->
    <div
        class="sticky top-0 z-10 p-6 border-b border-white/20 flex items-center justify-between h-20 bg-white/95 md:bg-transparent backdrop-blur-sm">
        <div class="flex items-center">
            <?php if ($logoExists): ?>
                <div class="bg-white p-1 rounded-xl shadow-lg">
                    <img src="<?= BASEURL; ?>/public/img/app/<?= htmlspecialchars($logoApp); ?>" alt="<?= $namaAplikasi; ?>"
                        class="w-8 h-8 object-contain">
                </div>
            <?php else: ?>
                <div class="bg-gradient-to-br from-amber-500 to-orange-600 p-2 rounded-xl shadow-lg">
                    <i data-lucide="wallet" class="w-6 h-6 text-white"></i>
                </div>
            <?php endif; ?>
            <div class="ml-3 min-w-0">
                <h1 class="text-lg font-bold text-secondary-800 leading-tight break-words">Bendahara</h1>
                <p class="text-xs text-secondary-500 font-medium mt-0.5">Kelola Pembayaran</p>
            </div>
        </div>

        <!-- Close button mobile -->
        <button id="sidebar-toggle-btn" class="md:hidden p-2 hover:bg-secondary-100 rounded-lg transition-colors"
            aria-label="Tutup menu">
            <i data-lucide="x" class="w-5 h-5 text-secondary-600"></i>
        </button>
    </div>

    <!-- Session Info -->
    <div class="px-6 py-3 bg-gradient-to-r from-amber-50 to-orange-50 border-b border-white/20">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-secondary-500 font-medium">Semester Aktif</p>
                <p class="text-sm font-bold text-secondary-800"><?= $_SESSION['nama_semester_aktif'] ?? ''; ?></p>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
        <ul class="space-y-1">
            <?php $judul = $data['judul'] ?? ''; ?>

            <!-- Kembali -->
            <li>
                <a href="<?= $backUrl; ?>"
                    class="group flex items-center p-3 text-sm font-medium rounded-xl transition-all duration-200 text-secondary-500 hover:bg-secondary-100 hover:text-secondary-700">
                    <div
                        class="bg-secondary-100 group-hover:bg-secondary-200 p-2 rounded-lg transition-colors duration-200">
                        <i data-lucide="arrow-left"
                            class="w-4 h-4 text-secondary-500 group-hover:text-secondary-700"></i>
                    </div>
                    <span class="ml-3 whitespace-nowrap"><?= $backText; ?></span>
                </a>
            </li>

            <li class="pt-4 pb-2">
                <div class="flex items-center px-3">
                    <i data-lucide="wallet" class="w-4 h-4 text-secondary-400 mr-2"></i>
                    <span class="text-xs font-bold text-secondary-400 uppercase tracking-wider">Menu Bendahara</span>
                    <div class="ml-auto h-px bg-secondary-200 flex-1"></div>
                </div>
            </li>

            <!-- Pembayaran (Pilih Kelas) -->
            <li>
                <a href="<?= BASEURL; ?>/bendahara/pembayaran"
                    class="group flex items-center p-3 text-sm font-medium rounded-xl transition-all duration-200 <?= isBendaharaActive($judul, 'Pembayaran Semua Kelas') ? 'gradient-primary text-white shadow-lg' : 'text-secondary-600 hover:bg-white/50 hover:text-secondary-800'; ?>">
                    <div
                        class="<?= isBendaharaActive($judul, 'Pembayaran Semua Kelas') ? 'bg-white/20' : 'bg-amber-100 group-hover:bg-amber-200'; ?> p-2 rounded-lg transition-colors duration-200">
                        <i data-lucide="layout-grid"
                            class="w-4 h-4 <?= isBendaharaActive($judul, 'Pembayaran Semua Kelas') ? 'text-white' : 'text-amber-600 group-hover:text-amber-700'; ?>"></i>
                    </div>
                    <span class="ml-3 whitespace-nowrap">Pilih Kelas</span>
                </a>
            </li>

            <!-- Riwayat Pembayaran -->
            <li>
                <a href="<?= BASEURL; ?>/bendahara/riwayat"
                    class="group flex items-center p-3 text-sm font-medium rounded-xl transition-all duration-200 <?= (isBendaharaActive($judul, 'Riwayat Pembayaran') && !isBendaharaActive($judul, 'Pembayaran Kelas')) ? 'gradient-primary text-white shadow-lg' : 'text-secondary-600 hover:bg-white/50 hover:text-secondary-800'; ?>">
                    <div
                        class="<?= (isBendaharaActive($judul, 'Riwayat Pembayaran') && !isBendaharaActive($judul, 'Pembayaran Kelas')) ? 'bg-white/20' : 'bg-blue-100 group-hover:bg-blue-200'; ?> p-2 rounded-lg transition-colors duration-200">
                        <i data-lucide="history"
                            class="w-4 h-4 <?= (isBendaharaActive($judul, 'Riwayat Pembayaran') && !isBendaharaActive($judul, 'Pembayaran Kelas')) ? 'text-white' : 'text-blue-600 group-hover:text-blue-700'; ?>"></i>
                    </div>
                    <span class="ml-3 whitespace-nowrap">Riwayat Pembayaran</span>
                </a>
            </li>

            <?php
            // Tampilkan menu kontekstual jika sedang di halaman kelas tertentu
            $isInKelas = isBendaharaActive($judul, 'Pembayaran Kelas') || isBendaharaActive($judul, 'Rekap Tagihan') || isBendaharaActive($judul, 'Input Pembayaran') || isBendaharaActive($judul, 'Detail Pembayaran') || isBendaharaActive($judul, 'Riwayat Pembayaran');
            if ($isInKelas && isset($data['wali_kelas_info']['id_kelas'])):
                $currentKelasId = $data['wali_kelas_info']['id_kelas'];
                $currentKelasNama = $data['wali_kelas_info']['nama_kelas'] ?? 'Kelas';
            ?>

                <li class="pt-4 pb-2">
                    <div class="flex items-center px-3">
                        <i data-lucide="school" class="w-4 h-4 text-secondary-400 mr-2"></i>
                        <span class="text-xs font-bold text-secondary-400 uppercase tracking-wider"><?= htmlspecialchars($currentKelasNama); ?></span>
                        <div class="ml-auto h-px bg-secondary-200 flex-1"></div>
                    </div>
                </li>

                <!-- Kelola Tagihan -->
                <li>
                    <a href="<?= BASEURL; ?>/bendahara/kelolaPembayaran/<?= $currentKelasId; ?>"
                        class="group flex items-center p-3 text-sm font-medium rounded-xl transition-all duration-200 <?= isBendaharaActive($judul, 'Pembayaran Kelas') ? 'gradient-primary text-white shadow-lg' : 'text-secondary-600 hover:bg-white/50 hover:text-secondary-800'; ?>">
                        <div
                            class="<?= isBendaharaActive($judul, 'Pembayaran Kelas') ? 'bg-white/20' : 'bg-green-100 group-hover:bg-green-200'; ?> p-2 rounded-lg transition-colors duration-200">
                            <i data-lucide="receipt"
                                class="w-4 h-4 <?= isBendaharaActive($judul, 'Pembayaran Kelas') ? 'text-white' : 'text-green-600 group-hover:text-green-700'; ?>"></i>
                        </div>
                        <span class="ml-3 whitespace-nowrap">Kelola Tagihan</span>
                    </a>
                </li>

                <!-- Rekap Tagihan -->
                <li>
                    <a href="<?= BASEURL; ?>/bendahara/rekapTagihan/<?= $currentKelasId; ?>"
                        class="group flex items-center p-3 text-sm font-medium rounded-xl transition-all duration-200 <?= isBendaharaActive($judul, 'Rekap Tagihan') ? 'gradient-primary text-white shadow-lg' : 'text-secondary-600 hover:bg-white/50 hover:text-secondary-800'; ?>">
                        <div
                            class="<?= isBendaharaActive($judul, 'Rekap Tagihan') ? 'bg-white/20' : 'bg-purple-100 group-hover:bg-purple-200'; ?> p-2 rounded-lg transition-colors duration-200">
                            <i data-lucide="clipboard-list"
                                class="w-4 h-4 <?= isBendaharaActive($judul, 'Rekap Tagihan') ? 'text-white' : 'text-purple-600 group-hover:text-purple-700'; ?>"></i>
                        </div>
                        <span class="ml-3 whitespace-nowrap">Rekap Tagihan</span>
                    </a>
                </li>

                <!-- Riwayat Kelas -->
                <li>
                    <a href="<?= BASEURL; ?>/bendahara/pembayaranRiwayat/<?= $currentKelasId; ?>"
                        class="group flex items-center p-3 text-sm font-medium rounded-xl transition-all duration-200 <?= (isBendaharaActive($judul, 'Riwayat Pembayaran') && isBendaharaActive($judul, 'Pembayaran Kelas')) ? 'gradient-primary text-white shadow-lg' : 'text-secondary-600 hover:bg-white/50 hover:text-secondary-800'; ?>">
                        <div
                            class="<?= (isBendaharaActive($judul, 'Riwayat Pembayaran') && isBendaharaActive($judul, 'Pembayaran Kelas')) ? 'bg-white/20' : 'bg-indigo-100 group-hover:bg-indigo-200'; ?> p-2 rounded-lg transition-colors duration-200">
                            <i data-lucide="clock"
                                class="w-4 h-4 <?= (isBendaharaActive($judul, 'Riwayat Pembayaran') && isBendaharaActive($judul, 'Pembayaran Kelas')) ? 'text-white' : 'text-indigo-600 group-hover:text-indigo-700'; ?>"></i>
                        </div>
                        <span class="ml-3 whitespace-nowrap">Riwayat Kelas</span>
                    </a>
                </li>

            <?php endif; ?>

        </ul>
    </nav>

    <!-- Footer: User info + Logout -->
    <div class="p-4 border-t border-white/20 bg-white/50">
        <div class="flex items-center p-3 rounded-xl bg-white/60">
            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center shadow-md">
                <span class="text-white text-sm font-bold"><?= strtoupper(substr($_SESSION['nama'] ?? 'U', 0, 1)); ?></span>
            </div>
            <div class="ml-3 min-w-0 flex-1">
                <p class="text-sm font-semibold text-secondary-800 truncate"><?= htmlspecialchars($_SESSION['nama'] ?? 'User'); ?></p>
                <p class="text-xs text-secondary-500">Bendahara</p>
            </div>
        </div>
    </div>
</aside>
