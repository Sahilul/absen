<?php
// File: app/views/templates/sidebar_cms.php
// Sidebar khusus untuk halaman CMS Website

// Get pengaturan aplikasi (Placeholder jika function belum global, tapi biasanya di init sudah ada)
// Asumsi $data['settings'] mungkin ada, tapi better fetch global settings if needed.
// Kita pakai hardcode nama sementara atau dari session kalau ada.
$namaAplikasi = 'Management System';
if (isset($data['settings']['school_name'])) {
    $namaAplikasi = htmlspecialchars($data['settings']['school_name']);
}

// Helper untuk cek active menu
function isCmsActive($judul, $target)
{
    return strpos($judul, $target) !== false;
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
        class="p-6 border-b border-white/20 flex items-center justify-between h-20 bg-white/95 md:bg-transparent backdrop-blur-sm">
        <div class="flex items-center">
            <div class="bg-gradient-to-br from-purple-500 to-indigo-600 p-2 rounded-xl shadow-lg">
                <i data-lucide="globe" class="w-6 h-6 text-white"></i>
            </div>
            <div class="ml-3">
                <h1 class="text-lg font-bold text-secondary-800 whitespace-nowrap">Website CMS</h1>
                <p class="text-xs text-secondary-500 font-medium">Kelola Konten Publik</p>
            </div>
        </div>

        <!-- Close button mobile -->
        <button id="closeSidebarBtn" class="md:hidden p-2 hover:bg-secondary-100 rounded-lg transition-colors">
            <i data-lucide="x" class="w-5 h-5 text-secondary-600"></i>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
        <ul class="space-y-1">

            <!-- Kembali ke Admin Utama -->
            <li>
                <a href="<?= BASEURL; ?>/admin"
                    class="group flex items-center p-3 text-sm font-medium rounded-xl transition-all duration-200 text-secondary-500 hover:bg-secondary-100 hover:text-secondary-700">
                    <div
                        class="bg-secondary-100 group-hover:bg-secondary-200 p-2 rounded-lg transition-colors duration-200">
                        <i data-lucide="arrow-left"
                            class="w-4 h-4 text-secondary-500 group-hover:text-secondary-700"></i>
                    </div>
                    <span class="ml-3 whitespace-nowrap">Kembali ke Admin</span>
                </a>
            </li>

            <li class="pt-4 pb-2">
                <div class="flex items-center px-3">
                    <i data-lucide="layout" class="w-4 h-4 text-secondary-400 mr-2"></i>
                    <span class="text-xs font-bold text-secondary-400 uppercase tracking-wider">Tampilan Depan</span>
                    <div class="ml-auto h-px bg-secondary-200 flex-1"></div>
                </div>
            </li>

            <!-- Identitas Website -->
            <li>
                <a href="<?= BASEURL; ?>/cms/identitas"
                    class="group flex items-center p-3 text-sm font-medium rounded-xl transition-all duration-200 <?= isCmsActive($judul ?? '', 'Identitas') ? 'bg-gradient-to-r from-purple-500 to-indigo-600 text-white shadow-lg' : 'text-secondary-600 hover:bg-white/50 hover:text-secondary-800'; ?>">
                    <div
                        class="<?= isCmsActive($judul ?? '', 'Identitas') ? 'bg-white/20' : 'bg-purple-100 group-hover:bg-purple-200'; ?> p-2 rounded-lg transition-colors duration-200">
                        <i data-lucide="monitor-smartphone"
                            class="w-4 h-4 <?= isCmsActive($judul ?? '', 'Identitas') ? 'text-white' : 'text-purple-600 group-hover:text-purple-700'; ?>"></i>
                    </div>
                    <span class="ml-3 whitespace-nowrap">Identitas & Kontak</span>
                </a>
            </li>

            <!-- Slider Gambar -->
            <li>
                <a href="<?= BASEURL; ?>/cms/sliders"
                    class="group flex items-center p-3 text-sm font-medium rounded-xl transition-all duration-200 <?= isCmsActive($judul ?? '', 'Slider') ? 'bg-gradient-to-r from-purple-500 to-indigo-600 text-white shadow-lg' : 'text-secondary-600 hover:bg-white/50 hover:text-secondary-800'; ?>">
                    <div
                        class="<?= isCmsActive($judul ?? '', 'Slider') ? 'bg-white/20' : 'bg-orange-100 group-hover:bg-orange-200'; ?> p-2 rounded-lg transition-colors duration-200">
                        <i data-lucide="images"
                            class="w-4 h-4 <?= isCmsActive($judul ?? '', 'Slider') ? 'text-white' : 'text-orange-600 group-hover:text-orange-700'; ?>"></i>
                    </div>
                    <span class="ml-3 whitespace-nowrap">Slider Depan</span>
                </a>
            </li>

            <!-- Popup Info -->
            <li>
                <a href="<?= BASEURL; ?>/cms/popups"
                    class="group flex items-center p-3 text-sm font-medium rounded-xl transition-all duration-200 <?= isCmsActive($judul ?? '', 'Popup') ? 'bg-gradient-to-r from-purple-500 to-indigo-600 text-white shadow-lg' : 'text-secondary-600 hover:bg-white/50 hover:text-secondary-800'; ?>">
                    <div
                        class="<?= isCmsActive($judul ?? '', 'Popup') ? 'bg-white/20' : 'bg-pink-100 group-hover:bg-pink-200'; ?> p-2 rounded-lg transition-colors duration-200">
                        <i data-lucide="message-circle"
                            class="w-4 h-4 <?= isCmsActive($judul ?? '', 'Popup') ? 'text-white' : 'text-pink-600 group-hover:text-pink-700'; ?>"></i>
                    </div>
                    <span class="ml-3 whitespace-nowrap">Popup Info</span>
                </a>
            </li>

            <li class="pt-4 pb-2">
                <div class="flex items-center px-3">
                    <i data-lucide="file-text" class="w-4 h-4 text-secondary-400 mr-2"></i>
                    <span class="text-xs font-bold text-secondary-400 uppercase tracking-wider">Konten & Menu</span>
                    <div class="ml-auto h-px bg-secondary-200 flex-1"></div>
                </div>
            </li>

            <!-- Menu Website -->
            <li>
                <a href="<?= BASEURL; ?>/cms/menus"
                    class="group flex items-center p-3 text-sm font-medium rounded-xl transition-all duration-200 <?= isCmsActive($judul ?? '', 'Menu') ? 'bg-gradient-to-r from-purple-500 to-indigo-600 text-white shadow-lg' : 'text-secondary-600 hover:bg-white/50 hover:text-secondary-800'; ?>">
                    <div
                        class="<?= isCmsActive($judul ?? '', 'Menu') ? 'bg-white/20' : 'bg-blue-100 group-hover:bg-blue-200'; ?> p-2 rounded-lg transition-colors duration-200">
                        <i data-lucide="menu"
                            class="w-4 h-4 <?= isCmsActive($judul ?? '', 'Menu') ? 'text-white' : 'text-blue-600 group-hover:text-blue-700'; ?>"></i>
                    </div>
                    <span class="ml-3 whitespace-nowrap">Menu Navigasi</span>
                </a>
            </li>

            <!-- Berita Helper -->
            <li>
                <a href="<?= BASEURL; ?>/cms/posts"
                    class="group flex items-center p-3 text-sm font-medium rounded-xl transition-all duration-200 <?= isCmsActive($judul ?? '', 'Berita') ? 'bg-gradient-to-r from-purple-500 to-indigo-600 text-white shadow-lg' : 'text-secondary-600 hover:bg-white/50 hover:text-secondary-800'; ?>">
                    <div
                        class="<?= isCmsActive($judul ?? '', 'Berita') ? 'bg-white/20' : 'bg-teal-100 group-hover:bg-teal-200'; ?> p-2 rounded-lg transition-colors duration-200">
                        <i data-lucide="newspaper"
                            class="w-4 h-4 <?= isCmsActive($judul ?? '', 'Berita') ? 'text-white' : 'text-teal-600 group-hover:text-teal-700'; ?>"></i>
                    </div>
                    <span class="ml-3 whitespace-nowrap">Berita & Halaman</span>
                </a>
            </li>

            <!-- Lembaga Yayasan -->
            <li>
                <a href="<?= BASEURL; ?>/cms/institutions"
                    class="group flex items-center p-3 text-sm font-medium rounded-xl transition-all duration-200 <?= isCmsActive($judul ?? '', 'Lembaga') ? 'bg-gradient-to-r from-purple-500 to-indigo-600 text-white shadow-lg' : 'text-secondary-600 hover:bg-white/50 hover:text-secondary-800'; ?>">
                    <div
                        class="<?= isCmsActive($judul ?? '', 'Lembaga') ? 'bg-white/20' : 'bg-green-100 group-hover:bg-green-200'; ?> p-2 rounded-lg transition-colors duration-200">
                        <i data-lucide="building-2"
                            class="w-4 h-4 <?= isCmsActive($judul ?? '', 'Lembaga') ? 'text-white' : 'text-green-600 group-hover:text-green-700'; ?>"></i>
                    </div>
                    <span class="ml-3 whitespace-nowrap">Lembaga Yayasan</span>
                </a>
            </li>

        </ul>
    </nav>

    <!-- Footer Sidebar -->
    <div class="p-4 bg-white/50 backdrop-blur-sm border-t border-white/20">
        <a href="<?= BASEURL; ?>/" target="_blank"
            class="flex items-center justify-center gap-2 p-3 rounded-xl bg-gradient-to-r from-teal-500 to-green-600 text-white text-sm font-medium hover:shadow-lg transition-all">
            <i data-lucide="external-link" class="w-4 h-4"></i>
            Lihat Website
        </a>
    </div>
</aside>

<!-- Script Mobile Toggle (sama dengan layout lain) -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.getElementById('sidebar');
        const closeSidebarBtn = document.getElementById('closeSidebarBtn');
        // Asumsi button open ada di header layout utama
        // Kita butuh handle overlay juga jika ada
    });
</script>