<?php
// File: app/views/bendahara/pembayaran.php
// View sederhana: list kelas, klik untuk ke pembayaran
$kelasList = $data['kelas_list'] ?? [];
?>

<div class="p-4 sm:p-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div
                class="w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl flex items-center justify-center shadow-lg">
                <i data-lucide="wallet-cards" class="w-6 h-6 text-white"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Bendahara - Pilih Kelas</h1>
                <p class="text-sm text-gray-500"><?= $_SESSION['nama_semester_aktif'] ?? ''; ?></p>
            </div>
        </div>
    </div>

    <?php if (class_exists('Flasher')) {
        Flasher::flash();
    } ?>

    <!-- Info -->
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6">
        <div class="flex items-start gap-3">
            <i data-lucide="info" class="w-5 h-5 text-amber-600 mt-0.5"></i>
            <p class="text-sm text-amber-800">Pilih kelas untuk mengelola pembayaran. Tampilan pembayaran sama dengan
                wali kelas.</p>
        </div>
    </div>

    <!-- Daftar Kelas -->
    <?php if (empty($kelasList)): ?>
        <div class="bg-white rounded-xl shadow-lg p-12 text-center">
            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i data-lucide="inbox" class="w-10 h-10 text-gray-400"></i>
            </div>
            <p class="text-gray-500 font-medium">Tidak ada kelas tersedia</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <?php foreach ($kelasList as $kelas): ?>
                <a href="<?= BASEURL ?>/bendahara/kelolaPembayaran/<?= $kelas['id_kelas'] ?>"
                    class="bg-white rounded-xl shadow-lg p-5 border border-gray-200 hover:shadow-xl hover:border-amber-300 transition-all group">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-14 h-14 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white font-bold text-xl shadow-lg group-hover:scale-105 transition-transform">
                            <?= strtoupper(substr($kelas['nama_kelas'] ?? '', 0, 2)); ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold text-gray-800 truncate"><?= htmlspecialchars($kelas['nama_kelas'] ?? ''); ?>
                            </h3>
                            <p class="text-sm text-gray-500"><?= $kelas['jumlah_siswa'] ?? 0; ?> siswa</p>
                        </div>
                        <i data-lucide="chevron-right"
                            class="w-5 h-5 text-gray-400 group-hover:text-amber-500 transition-colors"></i>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });
</script>