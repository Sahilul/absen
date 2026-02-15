<?php
// File: app/views/bendahara/kelas_detail.php
// Mirip dengan pembayaran_dashboard wali_kelas tapi untuk semua kelas
$kelas = $data['kelas'] ?? [];
$siswaList = $data['siswa_list'] ?? [];
$tagihanList = $data['tagihan_list'] ?? [];
$namaKelas = htmlspecialchars($kelas['nama_kelas'] ?? 'Kelas');
?>

<div class="p-4 sm:p-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <a href="<?= BASEURL ?>/bendahara/pembayaran"
                class="w-10 h-10 bg-secondary-100 hover:bg-secondary-200 rounded-lg flex items-center justify-center transition-colors">
                <i data-lucide="arrow-left" class="w-5 h-5 text-secondary-600"></i>
            </a>
            <div
                class="w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl flex items-center justify-center shadow-lg">
                <i data-lucide="wallet" class="w-6 h-6 text-white"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Pembayaran Kelas <?= $namaKelas ?></h1>
                <p class="text-sm text-gray-500">Kelola tagihan dan pembayaran siswa</p>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="<?= BASEURL ?>/bendahara/riwayat/<?= $kelas['id_kelas'] ?? '' ?>"
                class="px-4 py-2.5 bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white rounded-lg text-sm font-medium flex items-center gap-2 shadow-md hover:shadow-lg transition-all">
                <i data-lucide="history" class="w-4 h-4"></i>
                <span>Riwayat</span>
            </a>
        </div>
    </div>

    <?php if (class_exists('Flasher')) {
        Flasher::flash();
    } ?>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-lg p-4 border border-gray-200">
            <div class="flex items-center">
                <div class="p-3 rounded-xl bg-blue-100">
                    <i data-lucide="users" class="w-6 h-6 text-blue-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Siswa</p>
                    <p class="text-2xl font-bold text-gray-800"><?= count($siswaList); ?></p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-lg p-4 border border-gray-200">
            <div class="flex items-center">
                <div class="p-3 rounded-xl bg-amber-100">
                    <i data-lucide="receipt" class="w-6 h-6 text-amber-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Tagihan</p>
                    <p class="text-2xl font-bold text-gray-800"><?= count($tagihanList); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Daftar Tagihan -->
    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200 mb-6">
        <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 bg-amber-500 rounded-lg flex items-center justify-center">
                <i data-lucide="file-text" class="w-4 h-4 text-white"></i>
            </div>
            <h2 class="text-lg font-semibold text-gray-800">Daftar Tagihan Kelas</h2>
        </div>

        <?php if (empty($tagihanList)): ?>
            <div class="text-center py-12">
                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="inbox" class="w-10 h-10 text-gray-400"></i>
                </div>
                <p class="text-gray-500 font-medium">Belum ada tagihan untuk kelas ini</p>
                <p class="text-sm text-gray-400 mt-1">Tagihan harus dibuat oleh Wali Kelas</p>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($tagihanList as $t): ?>
                    <div
                        class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition-shadow bg-gradient-to-br from-white to-gray-50">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="font-semibold text-gray-800 mb-1"><?= htmlspecialchars($t['nama'] ?? '') ?></div>
                                <div class="flex items-center gap-2 text-sm">
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 bg-green-100 text-green-700 rounded-full font-medium">
                                        Rp <?= number_format((int) ($t['nominal_default'] ?? 0), 0, ',', '.') ?>
                                    </span>
                                    <?php if (!empty($t['jatuh_tempo'])): ?>
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">
                                            <i data-lucide="calendar" class="w-3 h-3 mr-1"></i>
                                            <?= htmlspecialchars($t['jatuh_tempo']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <a href="<?= BASEURL ?>/waliKelas/pembayaranTagihan/<?= (int) ($t['id'] ?? 0) ?>"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white rounded-lg font-medium shadow-md transition-all">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                                Kelola Pembayaran
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Daftar Siswa -->
    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
        <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                <i data-lucide="users" class="w-4 h-4 text-white"></i>
            </div>
            <h2 class="text-lg font-semibold text-gray-800">Daftar Siswa Kelas <?= $namaKelas ?></h2>
        </div>

        <?php if (empty($siswaList)): ?>
            <div class="text-center py-12">
                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="users" class="w-10 h-10 text-gray-400"></i>
                </div>
                <p class="text-gray-500 font-medium">Tidak ada siswa di kelas ini</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gradient-to-r from-gray-50 to-gray-100">
                            <th class="text-left p-4 font-semibold text-gray-700">No</th>
                            <th class="text-left p-4 font-semibold text-gray-700">NISN</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Nama Siswa</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php $no = 1;
                        foreach ($siswaList as $s): ?>
                            <tr class="hover:bg-blue-50 transition-colors">
                                <td class="p-4"><?= $no++; ?></td>
                                <td class="p-4 font-mono text-gray-600"><?= htmlspecialchars($s['nisn'] ?? '-') ?></td>
                                <td class="p-4">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-8 h-8 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white font-bold text-xs">
                                            <?= strtoupper(substr($s['nama_siswa'] ?? '', 0, 1)); ?>
                                        </div>
                                        <span
                                            class="font-medium text-gray-800"><?= htmlspecialchars($s['nama_siswa'] ?? '') ?></span>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });
</script>