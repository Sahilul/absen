<?php
// File: app/views/surat_tugas/sidebar_surat.php
// Sidebar khusus panel Surat Tugas (PSB Style)

$judul = $data['judul'] ?? '';
?>

<aside id="sidebar"
    class="sidebar fixed top-0 left-0 md:relative z-[60] w-72 md:w-64 bg-white md:bg-transparent md:glass-effect flex-shrink-0 h-screen md:h-auto flex flex-col border-r border-white/20 shadow-2xl transition-transform duration-300 ease-in-out -translate-x-full md:translate-x-0 overflow-y-auto isolate"
    aria-expanded="false">

    <!-- Header Sidebar -->
    <div
        class="p-6 border-b border-white/20 flex items-center justify-between h-20 bg-white/95 md:bg-transparent backdrop-blur-sm">
        <div class="flex items-center gap-3">
            <div class="gradient-primary p-2 rounded-xl shadow-lg">
                <i data-lucide="file-text" class="w-5 h-5 text-white"></i>
            </div>
            <div>
                <h1 class="text-lg font-bold text-secondary-800 whitespace-nowrap">Surat Tugas</h1>
                <p class="text-xs text-secondary-500 font-medium">Panel Admin</p>
            </div>
        </div>
        <button id="sidebar-toggle-btn"
            class="p-2 rounded-lg text-secondary-400 hover:bg-white/50 transition-colors duration-200 md:hidden">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>
    </div>

    <nav
        class="flex-1 overflow-y-auto sidebar-nav p-4 bg-white/90 md:bg-transparent backdrop-blur-sm md:backdrop-blur-none">
        <ul class="space-y-2">

            <!-- Dashboard -->
            <li>
                <a href="<?= BASEURL; ?>/suratTugas"
                    class="group flex items-center p-3 text-sm font-semibold rounded-xl transition-all duration-200 <?= ($judul == 'Dashboard Surat Tugas') ? 'gradient-primary text-white shadow-lg' : 'text-secondary-600 hover:bg-white/50 hover:text-secondary-800'; ?>">
                    <div
                        class="<?= ($judul == 'Dashboard Surat Tugas') ? 'bg-white/20' : 'bg-secondary-100 group-hover:bg-primary-100'; ?> p-2 rounded-lg transition-colors duration-200">
                        <i data-lucide="layout-dashboard"
                            class="w-4 h-4 <?= ($judul == 'Dashboard Surat Tugas') ? 'text-white' : 'text-primary-600 group-hover:text-primary-700'; ?>"></i>
                    </div>
                    <span class="ml-3 whitespace-nowrap">Dashboard</span>
                </a>
            </li>

            <!-- Section: Manage -->
            <li class="pt-6 pb-2">
                <div class="flex items-center px-3">
                    <i data-lucide="folder-open" class="w-4 h-4 text-secondary-400 mr-2"></i>
                    <span class="text-xs font-bold text-secondary-400 uppercase tracking-wider">Kelola</span>
                    <div class="ml-auto h-px bg-secondary-200 flex-1"></div>
                </div>
            </li>

            <!-- Surat Tugas -->
            <li>
                <a href="<?= BASEURL; ?>/suratTugas/surat"
                    class="group flex items-center p-3 text-sm font-medium rounded-xl transition-all duration-200 <?= (strpos($judul, 'Surat Tugas') !== false && $judul != 'Dashboard Surat Tugas') ? 'gradient-primary text-white shadow-lg' : 'text-secondary-600 hover:bg-white/50 hover:text-secondary-800'; ?>">
                    <div
                        class="<?= (strpos($judul, 'Surat Tugas') !== false && $judul != 'Dashboard Surat Tugas') ? 'bg-white/20' : 'bg-blue-100 group-hover:bg-blue-200'; ?> p-2 rounded-lg transition-colors duration-200">
                        <i data-lucide="file-check"
                            class="w-4 h-4 <?= (strpos($judul, 'Surat Tugas') !== false && $judul != 'Dashboard Surat Tugas') ? 'text-white' : 'text-blue-600 group-hover:text-blue-700'; ?>"></i>
                    </div>
                    <span class="ml-3 whitespace-nowrap">Daftar Surat Tugas</span>
                </a>
            </li>

            <!-- Lembaga -->
            <li>
                <a href="<?= BASEURL; ?>/suratTugas/lembaga"
                    class="group flex items-center p-3 text-sm font-medium rounded-xl transition-all duration-200 <?= (strpos($judul, 'Lembaga') !== false) ? 'gradient-primary text-white shadow-lg' : 'text-secondary-600 hover:bg-white/50 hover:text-secondary-800'; ?>">
                    <div
                        class="<?= (strpos($judul, 'Lembaga') !== false) ? 'bg-white/20' : 'bg-purple-100 group-hover:bg-purple-200'; ?> p-2 rounded-lg transition-colors duration-200">
                        <i data-lucide="building-2"
                            class="w-4 h-4 <?= (strpos($judul, 'Lembaga') !== false) ? 'text-white' : 'text-purple-600 group-hover:text-purple-700'; ?>"></i>
                    </div>
                    <span class="ml-3 whitespace-nowrap">Kelola Lembaga</span>
                </a>
            </li>

            <!-- Section: System -->
            <li class="pt-6 pb-2">
                <div class="flex items-center px-3">
                    <i data-lucide="settings" class="w-4 h-4 text-secondary-400 mr-2"></i>
                    <span class="text-xs font-bold text-secondary-400 uppercase tracking-wider">System</span>
                    <div class="ml-auto h-px bg-secondary-200 flex-1"></div>
                </div>
            </li>

            <!-- Back to Admin -->
            <li>
                <a href="<?= BASEURL; ?>/admin/dashboard"
                    class="group flex items-center p-3 text-sm font-medium rounded-xl transition-all duration-200 text-secondary-600 hover:bg-white/50 hover:text-secondary-800">
                    <div class="bg-gray-100 group-hover:bg-gray-200 p-2 rounded-lg transition-colors duration-200">
                        <i data-lucide="arrow-left-circle" class="w-4 h-4 text-gray-600 group-hover:text-gray-700"></i>
                    </div>
                    <span class="ml-3 whitespace-nowrap">Kembali ke Admin</span>
                </a>
            </li>

        </ul>
    </nav>
</aside>