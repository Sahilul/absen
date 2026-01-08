<?php
// File: app/views/templates/sidebar_psb.php
// Sidebar khusus untuk halaman admin PSB

// Get pengaturan aplikasi
$pengaturanApp = getPengaturanAplikasi();
$namaAplikasi = htmlspecialchars($pengaturanApp['nama_aplikasi'] ?? 'Smart Absensi');
$logoApp = $pengaturanApp['logo'] ?? '';

// Cek apakah file logo ada
$baseDir = dirname(dirname(dirname(__DIR__)));
$logoPath = $baseDir . '/public/img/app/' . $logoApp;
$logoExists = !empty($logoApp) && file_exists($logoPath);

// Helper untuk cek active menu
function isPSBActive($judul, $target)
{
    return strpos($judul, $target) !== false;
}

// Cek role user untuk menentukan title dan link kembali
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
$backUrl = $isAdmin ? BASEURL . '/admin' : BASEURL . '/guru';
$backText = $isAdmin ? 'Kembali ke Admin' : 'Kembali ke Dashboard';
$headerTitle = $isAdmin ? 'PSB Admin' : 'Petugas PSB';
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
                    <i data-lucide="user-plus" class="w-6 h-6 text-white"></i>
                </div>
            <?php endif; ?>
            <div class="ml-3 min-w-0">
                <h1 class="text-lg font-bold text-secondary-800 leading-tight break-words"><?= $headerTitle; ?></h1>
                <p class="text-xs text-secondary-500 font-medium mt-0.5">Penerimaan Siswa Baru</p>
            </div>
        </div>

        <!-- Close button mobile -->
        <button id="closeSidebarBtn" class="md:hidden p-2 hover:bg-secondary-100 rounded-lg transition-colors"
            aria-label="Tutup menu">
            <i data-lucide="x" class="w-5 h-5 text-secondary-600"></i>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
        <ul class="space-y-1">

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
                    <i data-lucide="layout-dashboard" class="w-4 h-4 text-secondary-400 mr-2"></i>
                    <span class="text-xs font-bold text-secondary-400 uppercase tracking-wider">Menu Utama</span>
                    <div class="ml-auto h-px bg-secondary-200 flex-1"></div>
                </div>
            </li>

            <!-- Dashboard PSB -->
            <li>
                <a href="<?= BASEURL; ?>/psb/dashboard"
                    class="group flex items-center p-3 text-sm font-medium rounded-xl transition-all duration-200 <?= isPSBActive($judul, 'Dashboard PSB') ? 'gradient-primary text-white shadow-lg' : 'text-secondary-600 hover:bg-white/50 hover:text-secondary-800'; ?>">
                    <div
                        class="<?= isPSBActive($judul, 'Dashboard PSB') ? 'bg-white/20' : 'bg-sky-100 group-hover:bg-sky-200'; ?> p-2 rounded-lg transition-colors duration-200">
                        <i data-lucide="layout-grid"
                            class="w-4 h-4 <?= isPSBActive($judul, 'Dashboard PSB') ? 'text-white' : 'text-sky-600 group-hover:text-sky-700'; ?>"></i>
                    </div>
                    <span class="ml-3 whitespace-nowrap">Dashboard</span>
                </a>
            </li>

            <!-- Pendaftar -->
            <li>
                <a href="<?= BASEURL; ?>/psb/pendaftar"
                    class="group flex items-center p-3 text-sm font-medium rounded-xl transition-all duration-200 <?= isPSBActive($judul, 'Pendaftar') ? 'gradient-primary text-white shadow-lg' : 'text-secondary-600 hover:bg-white/50 hover:text-secondary-800'; ?>">
                    <div
                        class="<?= isPSBActive($judul, 'Pendaftar') ? 'bg-white/20' : 'bg-green-100 group-hover:bg-green-200'; ?> p-2 rounded-lg transition-colors duration-200">
                        <i data-lucide="users"
                            class="w-4 h-4 <?= isPSBActive($judul, 'Pendaftar') ? 'text-white' : 'text-green-600 group-hover:text-green-700'; ?>"></i>
                    </div>
                    <span class="ml-3 whitespace-nowrap">Daftar Pendaftar</span>
                </a>
            </li>

            <!-- Akun Calon Siswa -->
            <li>
                <a href="<?= BASEURL; ?>/psb/akunPendaftar"
                    class="group flex items-center p-3 text-sm font-medium rounded-xl transition-all duration-200 <?= isPSBActive($judul, 'Akun Calon') ? 'gradient-primary text-white shadow-lg' : 'text-secondary-600 hover:bg-white/50 hover:text-secondary-800'; ?>">
                    <div
                        class="<?= isPSBActive($judul, 'Akun Calon') ? 'bg-white/20' : 'bg-teal-100 group-hover:bg-teal-200'; ?> p-2 rounded-lg transition-colors duration-200">
                        <i data-lucide="user-check"
                            class="w-4 h-4 <?= isPSBActive($judul, 'Akun Calon') ? 'text-white' : 'text-teal-600 group-hover:text-teal-700'; ?>"></i>
                    </div>
                    <span class="ml-3 whitespace-nowrap">Akun Calon Siswa</span>
                </a>
            </li>

            <li class="pt-4 pb-2">
                <div class="flex items-center px-3">
                    <i data-lucide="database" class="w-4 h-4 text-secondary-400 mr-2"></i>
                    <span class="text-xs font-bold text-secondary-400 uppercase tracking-wider">Master Data</span>
                    <div class="ml-auto h-px bg-secondary-200 flex-1"></div>
                </div>
            </li>

            <!-- Lembaga -->
            <li>
                <a href="<?= BASEURL; ?>/psb/lembaga"
                    class="group flex items-center p-3 text-sm font-medium rounded-xl transition-all duration-200 <?= isPSBActive($judul, 'Lembaga') ? 'gradient-primary text-white shadow-lg' : 'text-secondary-600 hover:bg-white/50 hover:text-secondary-800'; ?>">
                    <div
                        class="<?= isPSBActive($judul, 'Lembaga') ? 'bg-white/20' : 'bg-blue-100 group-hover:bg-blue-200'; ?> p-2 rounded-lg transition-colors duration-200">
                        <i data-lucide="building"
                            class="w-4 h-4 <?= isPSBActive($judul, 'Lembaga') ? 'text-white' : 'text-blue-600 group-hover:text-blue-700'; ?>"></i>
                    </div>
                    <span class="ml-3 whitespace-nowrap">Lembaga</span>
                </a>
            </li>

            <!-- Jalur Pendaftaran -->
            <li>
                <a href="<?= BASEURL; ?>/psb/jalur"
                    class="group flex items-center p-3 text-sm font-medium rounded-xl transition-all duration-200 <?= isPSBActive($judul, 'Jalur') ? 'gradient-primary text-white shadow-lg' : 'text-secondary-600 hover:bg-white/50 hover:text-secondary-800'; ?>">
                    <div
                        class="<?= isPSBActive($judul, 'Jalur') ? 'bg-white/20' : 'bg-purple-100 group-hover:bg-purple-200'; ?> p-2 rounded-lg transition-colors duration-200">
                        <i data-lucide="git-branch"
                            class="w-4 h-4 <?= isPSBActive($judul, 'Jalur') ? 'text-white' : 'text-purple-600 group-hover:text-purple-700'; ?>"></i>
                    </div>
                    <span class="ml-3 whitespace-nowrap">Jalur Pendaftaran</span>
                </a>
            </li>

            <!-- Periode PSB -->
            <li>
                <a href="<?= BASEURL; ?>/psb/periode"
                    class="group flex items-center p-3 text-sm font-medium rounded-xl transition-all duration-200 <?= isPSBActive($judul, 'Periode') ? 'gradient-primary text-white shadow-lg' : 'text-secondary-600 hover:bg-white/50 hover:text-secondary-800'; ?>">
                    <div
                        class="<?= isPSBActive($judul, 'Periode') ? 'bg-white/20' : 'bg-orange-100 group-hover:bg-orange-200'; ?> p-2 rounded-lg transition-colors duration-200">
                        <i data-lucide="calendar-range"
                            class="w-4 h-4 <?= isPSBActive($judul, 'Periode') ? 'text-white' : 'text-orange-600 group-hover:text-orange-700'; ?>"></i>
                    </div>
                    <span class="ml-3 whitespace-nowrap">Periode</span>
                </a>
            </li>

            <li class="pt-4 pb-2">
                <div class="flex items-center px-3">
                    <i data-lucide="settings" class="w-4 h-4 text-secondary-400 mr-2"></i>
                    <span class="text-xs font-bold text-secondary-400 uppercase tracking-wider">Pengaturan</span>
                    <div class="ml-auto h-px bg-secondary-200 flex-1"></div>
                </div>
            </li>

            <!-- Pengaturan PSB -->
            <li>
                <a href="<?= BASEURL; ?>/psb/pengaturan"
                    class="group flex items-center p-3 text-sm font-medium rounded-xl transition-all duration-200 <?= isPSBActive($judul, 'Pengaturan PSB') ? 'gradient-primary text-white shadow-lg' : 'text-secondary-600 hover:bg-white/50 hover:text-secondary-800'; ?>">
                    <div
                        class="<?= isPSBActive($judul, 'Pengaturan PSB') ? 'bg-white/20' : 'bg-secondary-100 group-hover:bg-secondary-200'; ?> p-2 rounded-lg transition-colors duration-200">
                        <i data-lucide="sliders"
                            class="w-4 h-4 <?= isPSBActive($judul, 'Pengaturan PSB') ? 'text-white' : 'text-secondary-600 group-hover:text-secondary-700'; ?>"></i>
                    </div>
                    <span class="ml-3 whitespace-nowrap">Pengaturan Halaman</span>
                </a>
            </li>

        </ul>
    </nav>

    <!-- Footer -->
    <div class="p-4 bg-white/50 backdrop-blur-sm border-t border-white/20">
        <?php
        $versionFile = dirname(dirname(dirname(__DIR__))) . '/version.json';
        $versionData = file_exists($versionFile) ? json_decode(file_get_contents($versionFile), true) : [];
        $appVersion = $versionData['version'] ?? '1.0.0';
        ?>
        <div class="text-center text-xs text-secondary-400 mb-3">
            <span class="font-medium"><?= $namaAplikasi; ?></span> • <span>v<?= $appVersion; ?></span>
        </div>
        <a href="<?= BASEURL; ?>/psb" target="_blank"
            class="flex items-center justify-center gap-2 p-3 rounded-xl bg-gradient-to-r from-sky-500 to-blue-600 text-white text-sm font-medium hover:shadow-lg transition-all">
            <i data-lucide="external-link" class="w-4 h-4"></i>
            Lihat Halaman Publik
        </a>
    </div>
</aside>

<!-- JavaScript untuk mobile toggle -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.getElementById('sidebar');
        const closeSidebarBtn = document.getElementById('closeSidebarBtn');
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const overlay = document.getElementById('mobileMenuOverlay');

        function closeSidebar() {
            if (sidebar) {
                sidebar.classList.add('-translate-x-full');
                sidebar.setAttribute('aria-expanded', 'false');
            }
            if (overlay) {
                overlay.classList.add('hidden');
                overlay.classList.remove('flex');
            }
        }

        function openSidebar() {
            if (sidebar) {
                sidebar.classList.remove('-translate-x-full');
                sidebar.setAttribute('aria-expanded', 'true');
            }
            if (overlay) {
                overlay.classList.remove('hidden');
                overlay.classList.add('flex');
            }
        }

        if (closeSidebarBtn) {
            closeSidebarBtn.addEventListener('click', closeSidebar);
        }

        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', openSidebar);
        }

        if (overlay) {
            overlay.addEventListener('click', closeSidebar);
        }
    });
</script>