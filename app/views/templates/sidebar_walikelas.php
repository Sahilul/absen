<?php
// File: app/views/templates/sidebar_walikelas.php - DROPDOWN VERSION

// Get pengaturan aplikasi
$pengaturanApp = getPengaturanAplikasi();
$namaAplikasi = htmlspecialchars($pengaturanApp['nama_aplikasi'] ?? 'Smart Absensi');
$logoApp = $pengaturanApp['logo'] ?? '';

// Cek apakah file logo ada
$baseDir = dirname(dirname(dirname(__DIR__))); // Path ke root folder absen
$logoPath = $baseDir . '/public/img/app/' . $logoApp;
$logoExists = !empty($logoApp) && file_exists($logoPath);

// Helper function
function isMenuActive($judul, $keyword)
{
  return !empty($judul) && strpos($judul, $keyword) !== false;
}
?>
<aside id="sidebar" class="sidebar fixed top-0 left-0 z-50 w-64 h-screen flex flex-col border-r border-white/20 shadow-2xl
         bg-white/95 md:bg-transparent md:glass-effect
         transform-gpu will-change-transform transition-transform duration-300 ease-in-out
         -translate-x-full md:translate-x-0 md:relative md:h-full md:flex-shrink-0
         overflow-y-auto md:overflow-visible">

  <!-- Header (sticky) -->
  <div
    class="sticky top-0 z-10 p-6 border-b border-white/20 flex items-center justify-between h-20 bg-white/95 md:bg-transparent">
    <div class="flex items-center">
      <?php if ($logoExists): ?>
        <div class="bg-white p-1 rounded-xl shadow-lg">
          <img src="<?= BASEURL; ?>/public/img/app/<?= htmlspecialchars($logoApp); ?>" alt="<?= $namaAplikasi; ?>"
            class="w-8 h-8 object-contain">
        </div>
      <?php else: ?>
        <div class="gradient-primary p-2 rounded-xl shadow-lg">
          <i data-lucide="user-check" class="w-6 h-6 text-white"></i>
        </div>
      <?php endif; ?>
      <div class="ml-3">
        <h1 class="text-lg font-bold text-secondary-800 whitespace-nowrap"><?= $namaAplikasi; ?></h1>
        <p class="text-xs text-secondary-500 font-medium">Wali Kelas</p>
      </div>
    </div>
    <button id="sidebar-toggle-btn"
      class="p-2 rounded-lg text-secondary-400 hover:bg-white/50 transition-colors duration-200 md:hidden"
      aria-label="Tutup sidebar">
      <i data-lucide="x" class="w-5 h-5"></i>
    </button>
  </div>

  <!-- Session Info -->
  <div class="px-6 py-3 bg-gradient-to-r from-indigo-50 to-blue-50 border-b border-white/20">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-xs text-secondary-500 font-medium">Semester Aktif</p>
        <p class="text-sm font-bold text-secondary-800"><?= $_SESSION['nama_semester_aktif'] ?? ''; ?></p>
      </div>
    </div>
  </div>

  <!-- Navigation -->
  <nav class="flex-1 sidebar-nav p-4">
    <ul class="space-y-1">
      <?php $judul = $data['judul'] ?? ''; ?>

      <!-- Dashboard -->
      <li>
        <a href="<?= BASEURL; ?>/waliKelas/dashboard"
          class="group flex items-center p-3 text-sm font-semibold rounded-xl transition-all duration-200 <?= ($judul == 'Dashboard Guru') ? 'gradient-primary text-white shadow-lg' : 'text-secondary-600 hover:bg-white/60 hover:text-secondary-800'; ?>">
          <div
            class="<?= ($judul == 'Dashboard Guru') ? 'bg-white/20' : 'bg-secondary-100 group-hover:bg-primary-100'; ?> p-2 rounded-lg transition-colors duration-200">
            <i data-lucide="layout-dashboard"
              class="w-4 h-4 <?= ($judul == 'Dashboard Guru') ? 'text-white' : 'text-secondary-500 group-hover:text-primary-600'; ?>"></i>
          </div>
          <span class="ml-3 whitespace-nowrap">Dashboard</span>
        </a>
      </li>

      <!-- DROPDOWN: Kelola Kelas -->
      <?php
      $kelolaKelasActive = in_array($judul, ['Daftar Siswa', 'Monitoring Absensi', 'Monitoring Nilai', 'Pembayaran Kelas', 'Kelola Tagihan Kelas', 'Riwayat Pembayaran', 'Input Pembayaran']);
      ?>
      <li class="pt-3" x-data="{ open: <?= $kelolaKelasActive ? 'true' : 'false' ?> }">
        <button @click="open = !open"
          class="w-full group flex items-center p-3 text-sm font-semibold rounded-xl transition-all duration-200 <?= $kelolaKelasActive ? 'bg-primary-50 text-primary-700' : 'text-secondary-600 hover:bg-white/50' ?>">
          <div
            class="<?= $kelolaKelasActive ? 'bg-primary-200' : 'bg-green-100 group-hover:bg-green-200' ?> p-2 rounded-lg transition-colors duration-200">
            <i data-lucide="users"
              class="w-4 h-4 <?= $kelolaKelasActive ? 'text-primary-700' : 'text-green-600' ?>"></i>
          </div>
          <span class="ml-3 whitespace-nowrap flex-1 text-left">Kelola Kelas</span>
          <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-200"
            :class="open ? 'rotate-180' : ''"></i>
        </button>
        <ul x-show="open" x-collapse class="mt-1 ml-4 space-y-1 border-l-2 border-green-200 pl-3">
          <li>
            <a href="<?= BASEURL; ?>/waliKelas/daftarSiswa"
              class="flex items-center p-2.5 text-sm font-medium rounded-lg transition-all duration-200 <?= ($judul == 'Daftar Siswa') ? 'bg-primary-100 text-primary-700' : 'text-secondary-600 hover:bg-green-50 hover:text-green-700' ?>">
              <i data-lucide="users-round" class="w-4 h-4 mr-2"></i>
              Daftar Siswa
            </a>
          </li>
          <li>
            <a href="<?= BASEURL; ?>/waliKelas/monitoringAbsensi"
              class="flex items-center p-2.5 text-sm font-medium rounded-lg transition-all duration-200 <?= ($judul == 'Monitoring Absensi') ? 'bg-primary-100 text-primary-700' : 'text-secondary-600 hover:bg-amber-50 hover:text-amber-700' ?>">
              <i data-lucide="clipboard-check" class="w-4 h-4 mr-2"></i>
              Monitoring Absensi
            </a>
          </li>
          <?php if (defined('MENU_INPUT_NILAI_ENABLED') && MENU_INPUT_NILAI_ENABLED): ?>
            <li>
              <a href="<?= BASEURL; ?>/waliKelas/monitoringNilai"
                class="flex items-center p-2.5 text-sm font-medium rounded-lg transition-all duration-200 <?= ($judul == 'Monitoring Nilai') ? 'bg-primary-100 text-primary-700' : 'text-secondary-600 hover:bg-blue-50 hover:text-blue-700' ?>">
                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i>
                Monitoring Nilai
              </a>
            </li>
          <?php endif; ?>
          <?php if (defined('MENU_PEMBAYARAN_ENABLED') && MENU_PEMBAYARAN_ENABLED): ?>
            <?php $isPembayaranActive = in_array($judul, ['Pembayaran Kelas', 'Kelola Tagihan Kelas', 'Riwayat Pembayaran', 'Input Pembayaran']); ?>
            <li>
              <a href="<?= BASEURL; ?>/waliKelas/pembayaran"
                class="flex items-center p-2.5 text-sm font-medium rounded-lg transition-all duration-200 <?= $isPembayaranActive ? 'bg-primary-100 text-primary-700' : 'text-secondary-600 hover:bg-emerald-50 hover:text-emerald-700' ?>">
                <i data-lucide="wallet" class="w-4 h-4 mr-2"></i>
                Pembayaran
              </a>
            </li>
          <?php endif; ?>
        </ul>
      </li>

      <!-- DROPDOWN: Cetak Rapor -->
      <?php if (defined('MENU_INPUT_NILAI_ENABLED') && MENU_INPUT_NILAI_ENABLED): ?>
        <?php $cetakRaporActive = in_array($judul, ['Pengaturan Rapor', 'Cetak Rapor']); ?>
        <li class="pt-2" x-data="{ open: <?= $cetakRaporActive ? 'true' : 'false' ?> }">
          <button @click="open = !open"
            class="w-full group flex items-center p-3 text-sm font-semibold rounded-xl transition-all duration-200 <?= $cetakRaporActive ? 'bg-primary-50 text-primary-700' : 'text-secondary-600 hover:bg-white/50' ?>">
            <div
              class="<?= $cetakRaporActive ? 'bg-primary-200' : 'bg-indigo-100 group-hover:bg-indigo-200' ?> p-2 rounded-lg transition-colors duration-200">
              <i data-lucide="printer"
                class="w-4 h-4 <?= $cetakRaporActive ? 'text-primary-700' : 'text-indigo-600' ?>"></i>
            </div>
            <span class="ml-3 whitespace-nowrap flex-1 text-left">Cetak Rapor</span>
            <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-200"
              :class="open ? 'rotate-180' : ''"></i>
          </button>
          <ul x-show="open" x-collapse class="mt-1 ml-4 space-y-1 border-l-2 border-indigo-200 pl-3">
            <li>
              <a href="<?= BASEURL; ?>/waliKelas/pengaturanRapor"
                class="flex items-center p-2.5 text-sm font-medium rounded-lg transition-all duration-200 <?= ($judul == 'Pengaturan Rapor') ? 'bg-primary-100 text-primary-700' : 'text-secondary-600 hover:bg-indigo-50 hover:text-indigo-700' ?>">
                <i data-lucide="settings" class="w-4 h-4 mr-2"></i>
                Pengaturan Rapor
              </a>
            </li>
            <li>
              <a href="<?= BASEURL; ?>/waliKelas/cetakRapor"
                class="flex items-center p-2.5 text-sm font-medium rounded-lg transition-all duration-200 <?= ($judul == 'Cetak Rapor') ? 'bg-primary-100 text-primary-700' : 'text-secondary-600 hover:bg-indigo-50 hover:text-indigo-700' ?>">
                <i data-lucide="printer" class="w-4 h-4 mr-2"></i>
                Cetak Rapor
              </a>
            </li>
          </ul>
        </li>
      <?php endif; ?>

      <?php
      // Check if user has additional functions (bendahara, petugas_psb, admin_cms)
      $isBendahara = false;
      $isPetugasPSB = false;
      $isAdminCms = false;
      if (isset($_SESSION['id_ref']) && isset($_SESSION['id_tp_aktif'])) {
        require_once APPROOT . '/app/models/GuruFungsi_model.php';
        $guruFungsiModel = new GuruFungsi_model();
        $isBendahara = $guruFungsiModel->isBendahara($_SESSION['id_ref'], $_SESSION['id_tp_aktif']);
        $isPetugasPSB = $guruFungsiModel->isPetugasPSB($_SESSION['id_ref'], $_SESSION['id_tp_aktif']);
        $isAdminCms = $guruFungsiModel->isAdminCMS($_SESSION['id_ref'], $_SESSION['id_tp_aktif']);
      }
      ?>

      <?php if ($isBendahara || $isPetugasPSB || $isAdminCms): ?>
        <!-- DROPDOWN: Tugas Tambahan -->
        <?php
        $tugasTambahanActive = strpos($judul, 'Bendahara') !== false || strpos($judul, 'PSB') !== false || strpos($judul, 'Penerimaan') !== false || strpos($judul, 'CMS') !== false || strpos($judul, 'Berita') !== false || strpos($judul, 'Post') !== false;
        ?>
        <li class="pt-2" x-data="{ open: <?= $tugasTambahanActive ? 'true' : 'false' ?> }">
          <button @click="open = !open"
            class="w-full group flex items-center p-3 text-sm font-semibold rounded-xl transition-all duration-200 <?= $tugasTambahanActive ? 'bg-amber-50 text-amber-700' : 'text-secondary-600 hover:bg-white/50' ?>">
            <div
              class="<?= $tugasTambahanActive ? 'bg-amber-200' : 'bg-amber-100 group-hover:bg-amber-200' ?> p-2 rounded-lg transition-colors duration-200">
              <i data-lucide="briefcase"
                class="w-4 h-4 <?= $tugasTambahanActive ? 'text-amber-700' : 'text-amber-600' ?>"></i>
            </div>
            <span class="ml-3 whitespace-nowrap flex-1 text-left">Tugas Tambahan</span>
            <span class="bg-amber-100 text-amber-700 py-0.5 px-2 rounded-full text-xs font-medium mr-1">Extra</span>
            <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-200"
              :class="open ? 'rotate-180' : ''"></i>
          </button>
          <ul x-show="open" x-collapse class="mt-1 ml-4 space-y-1 border-l-2 border-amber-200 pl-3">
            <?php if ($isBendahara): ?>
              <li>
                <a href="<?= BASEURL; ?>/bendahara/pembayaran"
                  class="flex items-center p-2.5 text-sm font-medium rounded-lg transition-all duration-200 <?= strpos($judul, 'Bendahara') !== false ? 'bg-amber-100 text-amber-700' : 'text-secondary-600 hover:bg-amber-50 hover:text-amber-700' ?>">
                  <i data-lucide="wallet" class="w-4 h-4 mr-2"></i>
                  Bendahara
                </a>
              </li>
            <?php endif; ?>
            <?php if ($isPetugasPSB): ?>
              <li>
                <a href="<?= BASEURL; ?>/psb/dashboard"
                  class="flex items-center p-2.5 text-sm font-medium rounded-lg transition-all duration-200 <?= (strpos($judul, 'PSB') !== false || strpos($judul, 'Penerimaan') !== false) ? 'bg-teal-100 text-teal-700' : 'text-secondary-600 hover:bg-teal-50 hover:text-teal-700' ?>">
                  <i data-lucide="user-plus" class="w-4 h-4 mr-2"></i>
                  Petugas PSB
                </a>
              </li>
            <?php endif; ?>
            <?php if ($isAdminCms): ?>
              <li>
                <a href="<?= BASEURL; ?>/cms/posts"
                  class="flex items-center p-2.5 text-sm font-medium rounded-lg transition-all duration-200 <?= (strpos($judul, 'CMS') !== false || strpos($judul, 'Berita') !== false || strpos($judul, 'Post') !== false) ? 'bg-purple-100 text-purple-700' : 'text-secondary-600 hover:bg-purple-50 hover:text-purple-700' ?>">
                  <i data-lucide="newspaper" class="w-4 h-4 mr-2"></i>
                  Admin CMS
                </a>
              </li>
            <?php endif; ?>
          </ul>
        </li>
      <?php endif; ?>

      <!-- Divider + Logout -->
      <li class="pt-4">
        <div class="border-t border-white/20 pt-4">
          <a href="<?= BASEURL; ?>/auth/logout"
            class="group flex items-center p-3 text-sm font-semibold rounded-xl text-red-600 hover:bg-red-50 transition-all duration-200"
            onclick="return confirm('Yakin ingin logout?')">
            <div class="bg-red-100 group-hover:bg-red-200 p-2 rounded-lg transition-colors duration-200">
              <i data-lucide="log-out" class="w-4 h-4 text-red-600"></i>
            </div>
            <span class="ml-3 whitespace-nowrap">Logout</span>
          </a>
        </div>
      </li>
    </ul>
  </nav>

  <!-- User Info -->
  <div class="p-4 border-t border-white/20 bg-gradient-to-r from-indigo-50/50 to-purple-50/50">
    <div class="flex items-center gap-3">
      <div class="gradient-primary p-3 rounded-xl">
        <i data-lucide="user-circle" class="w-6 h-6 text-white"></i>
      </div>
      <div class="flex-1 min-w-0">
        <p class="text-sm font-bold text-secondary-800 truncate"><?= $_SESSION['nama_lengkap'] ?? 'User'; ?></p>
        <p class="text-xs text-secondary-500">Wali Kelas</p>
      </div>
    </div>
  </div>
</aside>

<!-- Alpine.js collapse style -->
<style>
  [x-cloak] {
    display: none !important;
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    if (typeof lucide !== 'undefined') {
      lucide.createIcons();
    }
  });
</script>