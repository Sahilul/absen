<?php
// File: app/views/templates/sidebar_siswa.php - DROPDOWN VERSION
?>
<aside id="sidebar"
    class="sidebar fixed md:relative z-40 w-64 bg-white md:glass-effect flex-shrink-0 h-full flex flex-col border-r border-white/20 shadow-2xl transition-all duration-300 ease-in-out -translate-x-full md:translate-x-0">

    <!-- Logo Header -->
    <div
        class="p-6 border-b border-secondary-200 md:border-white/20 bg-white md:bg-transparent flex items-center justify-between h-20">
        <div class="flex items-center">
            <div class="gradient-warning p-2 rounded-xl shadow-lg">
                <i data-lucide="user-check-2" class="w-6 h-6 text-white"></i>
            </div>
            <div class="ml-3 min-w-0">
                <h1 class="text-lg font-bold text-secondary-800 leading-tight break-words">Smart Absensi</h1>
                <p class="text-xs text-secondary-500 font-medium mt-0.5">Panel Siswa</p>
            </div>
        </div>
        <button id="sidebar-toggle-btn"
            class="p-2 rounded-lg text-secondary-400 hover:bg-white/50 transition-colors duration-200 md:hidden">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto sidebar-nav p-4">
        <ul class="space-y-1">
            <?php $judul = $data['judul'] ?? ''; ?>

            <!-- Dashboard -->
            <li>
                <a href="<?= BASEURL; ?>/siswa/dashboard"
                    class="group flex items-center p-3 text-sm font-semibold rounded-xl transition-all duration-200 <?= ($judul == 'Dashboard Siswa') ? 'gradient-warning text-white shadow-lg' : 'text-secondary-600 hover:bg-white/50 hover:text-secondary-800'; ?>">
                    <div
                        class="<?= ($judul == 'Dashboard Siswa') ? 'bg-white/20' : 'bg-secondary-100 group-hover:bg-warning-100'; ?> p-2 rounded-lg transition-colors duration-200">
                        <i data-lucide="layout-dashboard"
                            class="w-4 h-4 <?= ($judul == 'Dashboard Siswa') ? 'text-white' : 'text-secondary-500 group-hover:text-warning-600'; ?>"></i>
                    </div>
                    <span class="ml-3 whitespace-nowrap">Dashboard</span>
                </a>
            </li>

            <!-- Pesan/Inbox -->
            <li>
                <a href="<?= BASEURL; ?>/siswa/pesan"
                    class="group flex items-center p-3 text-sm font-semibold rounded-xl transition-all duration-200 <?= (strpos($judul, 'Pesan') !== false) ? 'gradient-warning text-white shadow-lg' : 'text-secondary-600 hover:bg-white/50 hover:text-secondary-800'; ?>">
                    <div
                        class="<?= (strpos($judul, 'Pesan') !== false) ? 'bg-white/20' : 'bg-indigo-100 group-hover:bg-indigo-200'; ?> p-2 rounded-lg transition-colors duration-200">
                        <i data-lucide="mail"
                            class="w-4 h-4 <?= (strpos($judul, 'Pesan') !== false) ? 'text-white' : 'text-indigo-500 group-hover:text-indigo-600'; ?>"></i>
                    </div>
                    <span class="ml-3 whitespace-nowrap">Kotak Masuk</span>
                </a>
            </li>

            <!-- DROPDOWN: Absensi -->
            <?php $absensiActive = in_array($judul, ['Absensi Harian', 'Rekap Absensi']); ?>
            <li class="pt-3" x-data="{ open: <?= $absensiActive ? 'true' : 'false' ?> }">
                <button @click="open = !open"
                    class="w-full group flex items-center p-3 text-sm font-semibold rounded-xl transition-all duration-200 <?= $absensiActive ? 'bg-primary-50 text-primary-700' : 'text-secondary-600 hover:bg-white/50' ?>">
                    <div
                        class="<?= $absensiActive ? 'bg-primary-200' : 'bg-blue-100 group-hover:bg-blue-200' ?> p-2 rounded-lg transition-colors duration-200">
                        <i data-lucide="calendar-check"
                            class="w-4 h-4 <?= $absensiActive ? 'text-primary-700' : 'text-blue-600' ?>"></i>
                    </div>
                    <span class="ml-3 whitespace-nowrap flex-1 text-left">Absensi Saya</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-200"
                        :class="open ? 'rotate-180' : ''"></i>
                </button>
                <ul x-show="open" x-collapse class="mt-1 ml-4 space-y-1 border-l-2 border-blue-200 pl-3">
                    <li>
                        <a href="<?= BASEURL; ?>/siswa/absensiHarian"
                            class="flex items-center p-2.5 text-sm font-medium rounded-lg transition-all duration-200 <?= ($judul == 'Absensi Harian') ? 'bg-primary-100 text-primary-700' : 'text-secondary-600 hover:bg-blue-50 hover:text-blue-700' ?>">
                            <i data-lucide="calendar-days" class="w-4 h-4 mr-2"></i>
                            Absensi Harian
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASEURL; ?>/siswa/rekapAbsensi"
                            class="flex items-center p-2.5 text-sm font-medium rounded-lg transition-all duration-200 <?= ($judul == 'Rekap Absensi') ? 'bg-primary-100 text-primary-700' : 'text-secondary-600 hover:bg-green-50 hover:text-green-700' ?>">
                            <i data-lucide="pie-chart" class="w-4 h-4 mr-2"></i>
                            Rekap Absensi
                        </a>
                    </li>
                </ul>
            </li>

            <!-- DROPDOWN: Keuangan -->
            <?php if (defined('MENU_PEMBAYARAN_ENABLED') && MENU_PEMBAYARAN_ENABLED): ?>
                <?php $keuanganActive = in_array($judul, ['Pembayaran', 'Riwayat Pembayaran']); ?>
                <li class="pt-2" x-data="{ open: <?= $keuanganActive ? 'true' : 'false' ?> }">
                    <button @click="open = !open"
                        class="w-full group flex items-center p-3 text-sm font-semibold rounded-xl transition-all duration-200 <?= $keuanganActive ? 'bg-emerald-50 text-emerald-700' : 'text-secondary-600 hover:bg-white/50' ?>">
                        <div
                            class="<?= $keuanganActive ? 'bg-emerald-200' : 'bg-emerald-100 group-hover:bg-emerald-200' ?> p-2 rounded-lg transition-colors duration-200">
                            <i data-lucide="wallet"
                                class="w-4 h-4 <?= $keuanganActive ? 'text-emerald-700' : 'text-emerald-600' ?>"></i>
                        </div>
                        <span class="ml-3 whitespace-nowrap flex-1 text-left">Keuangan</span>
                        <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-200"
                            :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <ul x-show="open" x-collapse class="mt-1 ml-4 space-y-1 border-l-2 border-emerald-200 pl-3">
                        <li>
                            <a href="<?= BASEURL; ?>/siswa/pembayaran"
                                class="flex items-center p-2.5 text-sm font-medium rounded-lg transition-all duration-200 <?= ($judul == 'Pembayaran') ? 'bg-emerald-100 text-emerald-700' : 'text-secondary-600 hover:bg-emerald-50 hover:text-emerald-700' ?>">
                                <i data-lucide="credit-card" class="w-4 h-4 mr-2"></i>
                                Pembayaran
                            </a>
                        </li>
                        <li>
                            <a href="<?= BASEURL; ?>/siswa/riwayatPembayaran"
                                class="flex items-center p-2.5 text-sm font-medium rounded-lg transition-all duration-200 <?= ($judul == 'Riwayat Pembayaran') ? 'bg-emerald-100 text-emerald-700' : 'text-secondary-600 hover:bg-emerald-50 hover:text-emerald-700' ?>">
                                <i data-lucide="receipt" class="w-4 h-4 mr-2"></i>
                                Riwayat Pembayaran
                            </a>
                        </li>
                    </ul>
                </li>
            <?php endif; ?>

            <!-- DROPDOWN: Profil Saya (Dokumen + Edit Identitas) -->
            <?php
            $docCount = 0;
            try {
                $db = new Database();
                $db->query("SELECT COUNT(*) as total FROM siswa_dokumen WHERE id_siswa = :id");
                $db->bind('id', $_SESSION['id_ref'] ?? 0);
                $docCount = $db->single()['total'] ?? 0;
            } catch (PDOException $e) {
                // Table siswa_dokumen may not exist in hosting
                $docCount = 0;
            }
            $profilActive = in_array($judul, ['Dokumen Saya', 'Edit Identitas']);
            ?>
            <li class="pt-2" x-data="{ open: <?= $profilActive ? 'true' : 'false' ?> }">
                <button @click="open = !open"
                    class="w-full group flex items-center p-3 text-sm font-semibold rounded-xl transition-all duration-200 <?= $profilActive ? 'bg-violet-50 text-violet-700' : 'text-secondary-600 hover:bg-white/50' ?>">
                    <div
                        class="<?= $profilActive ? 'bg-violet-200' : 'bg-violet-100 group-hover:bg-violet-200' ?> p-2 rounded-lg transition-colors duration-200">
                        <i data-lucide="user-circle"
                            class="w-4 h-4 <?= $profilActive ? 'text-violet-700' : 'text-violet-600' ?>"></i>
                    </div>
                    <span class="ml-3 whitespace-nowrap flex-1 text-left">Profil Saya</span>
                    <?php if ($docCount > 0): ?>
                        <span
                            class="text-xs bg-violet-100 text-violet-700 px-2 py-0.5 rounded-full mr-2"><?= $docCount; ?></span>
                    <?php endif; ?>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-200"
                        :class="open ? 'rotate-180' : ''"></i>
                </button>
                <ul x-show="open" x-collapse class="mt-1 ml-4 space-y-1 border-l-2 border-violet-200 pl-3">
                    <li>
                        <a href="<?= BASEURL; ?>/siswa/dokumen"
                            class="flex items-center p-2.5 text-sm font-medium rounded-lg transition-all duration-200 <?= ($judul == 'Dokumen Saya') ? 'bg-violet-100 text-violet-700' : 'text-secondary-600 hover:bg-violet-50 hover:text-violet-700' ?>">
                            <i data-lucide="file-text" class="w-4 h-4 mr-2"></i>
                            Dokumen Saya
                            <?php if ($docCount > 0): ?>
                                <span
                                    class="ml-auto text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full"><?= $docCount; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASEURL; ?>/siswa/editIdentitas"
                            class="flex items-center p-2.5 text-sm font-medium rounded-lg transition-all duration-200 <?= ($judul == 'Edit Identitas') ? 'bg-violet-100 text-violet-700' : 'text-secondary-600 hover:bg-violet-50 hover:text-violet-700' ?>">
                            <i data-lucide="user-pen" class="w-4 h-4 mr-2"></i>
                            Edit Identitas
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASEURL; ?>/siswa/cetakSKSA"
                            class="flex items-center p-2.5 text-sm font-medium rounded-lg transition-all duration-200 text-secondary-600 hover:bg-green-50 hover:text-green-700">
                            <i data-lucide="file-badge" class="w-4 h-4 mr-2"></i>
                            Cetak SKSA
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Logout -->
            <li class="pt-4">
                <div class="border-t border-white/20 pt-4">
                    <a href="<?= BASEURL; ?>/auth/logout"
                        class="group flex items-center p-3 text-sm font-medium text-danger-600 hover:bg-danger-50 rounded-xl transition-all duration-200 w-full">
                        <div
                            class="bg-danger-100 group-hover:bg-danger-200 p-2 rounded-lg transition-colors duration-200">
                            <i data-lucide="log-out" class="w-4 h-4 text-danger-600"></i>
                        </div>
                        <span class="ml-3 whitespace-nowrap font-semibold">Logout</span>
                        <i data-lucide="arrow-right"
                            class="w-4 h-4 ml-auto opacity-0 group-hover:opacity-100 transition-opacity duration-200"></i>
                    </a>
                </div>
            </li>
        </ul>
    </nav>

    <!-- Footer dengan info session -->
    <div class="p-4 mx-4 mb-4">
        <?php
        $versionFile = dirname(dirname(dirname(__DIR__))) . '/version.json';
        $versionData = file_exists($versionFile) ? json_decode(file_get_contents($versionFile), true) : [];
        $appVersion = $versionData['version'] ?? '1.0.0';
        $pengaturanApp = getPengaturanAplikasi();
        $namaApp = htmlspecialchars($pengaturanApp['nama_aplikasi'] ?? 'Smart Absensi');
        ?>
        <div class="text-center text-xs text-secondary-400 mb-3">
            <span class="font-medium"><?= $namaApp; ?></span> • <span>v<?= $appVersion; ?></span>
        </div>
        <div
            class="bg-gradient-to-r from-warning-50 to-primary-50 rounded-xl border border-secondary-200 md:border-white/20 p-4">
            <div class="text-center">
                <div
                    class="gradient-warning text-white text-xl font-bold py-1 px-3 rounded-lg inline-flex items-center mb-2">
                    <i data-lucide="calendar-check" class="w-4 h-4 mr-2"></i>
                    <span><?= date('d'); ?></span>
                </div>
                <p class="text-xs text-secondary-600 font-medium"><?= date('M Y'); ?></p>
                <p class="text-xs text-secondary-500">Hari ini</p>
            </div>
        </div>
    </div>
</aside>

<!-- Mobile Overlay -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black/50 md:bg-black/20 md:backdrop-blur-sm z-30 md:hidden hidden">
</div>

<!-- Alpine.js collapse style -->
<style>
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
            overlay.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        };

        const showSidebar = () => {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        };

        if (menuButton) {
            menuButton.addEventListener('click', (e) => {
                e.preventDefault();
                showSidebar();
            });
        }

        if (sidebarToggleBtn) {
            sidebarToggleBtn.addEventListener('click', hideSidebar);
        }

        if (overlay) {
            overlay.addEventListener('click', hideSidebar);
        }

        const sidebarLinks = sidebar.querySelectorAll('a[href]');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 768) {
                    hideSidebar();
                }
            });
        });

        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>