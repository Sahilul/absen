<?php
$laporanList = $data['laporan_list'] ?? [];
$stats = $data['stats'] ?? [];
$filters = $data['filters'] ?? [];
$currentStatus = $filters['status'] ?? '';
?>
<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gradient-to-br from-secondary-50 to-secondary-100 p-4 sm:p-6">
    <div class="mb-6">
        <a href="<?= BASEURL; ?>/guru/dashboard" class="text-sm text-primary-600 hover:underline flex items-center gap-1 mb-2">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali
        </a>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-2xl sm:text-3xl font-bold text-secondary-800 flex items-center">
                    <i data-lucide="file-text" class="w-7 h-7 sm:w-8 sm:h-8 mr-2 sm:mr-3 text-primary-500"></i>
                    Riwayat Laporan BK
                </h2>
                <p class="text-secondary-600 mt-1 text-sm">Daftar laporan siswa yang pernah Anda kirim ke BK</p>
            </div>
            <a href="<?= BASEURL; ?>/guru/laporSiswa" class="btn-primary px-4 py-2.5 text-sm rounded-xl flex items-center gap-2 w-fit">
                <i data-lucide="plus" class="w-4 h-4"></i> Lapor Baru
            </a>
        </div>
    </div>

    <?php Flasher::flash(); ?>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        <div class="bg-white rounded-xl p-4 border shadow-sm">
            <div class="text-2xl font-bold text-secondary-800"><?= $stats['total'] ?? 0 ?></div>
            <div class="text-xs text-secondary-500 mt-1">Total Laporan</div>
        </div>
        <a href="<?= BASEURL; ?>/guru/riwayatLaporan?status=baru" class="bg-white rounded-xl p-4 border shadow-sm hover:border-blue-300 transition-colors <?= $currentStatus === 'baru' ? 'ring-2 ring-blue-400' : '' ?>">
            <div class="text-2xl font-bold text-blue-600"><?= $stats['baru'] ?? 0 ?></div>
            <div class="text-xs text-secondary-500 mt-1">Menunggu</div>
        </a>
        <a href="<?= BASEURL; ?>/guru/riwayatLaporan?status=proses" class="bg-white rounded-xl p-4 border shadow-sm hover:border-amber-300 transition-colors <?= $currentStatus === 'proses' ? 'ring-2 ring-amber-400' : '' ?>">
            <div class="text-2xl font-bold text-amber-600"><?= $stats['proses'] ?? 0 ?></div>
            <div class="text-xs text-secondary-500 mt-1">Diproses</div>
        </a>
        <a href="<?= BASEURL; ?>/guru/riwayatLaporan?status=selesai" class="bg-white rounded-xl p-4 border shadow-sm hover:border-green-300 transition-colors <?= $currentStatus === 'selesai' ? 'ring-2 ring-green-400' : '' ?>">
            <div class="text-2xl font-bold text-green-600"><?= $stats['selesai'] ?? 0 ?></div>
            <div class="text-xs text-secondary-500 mt-1">Selesai</div>
        </a>
    </div>

    <?php if ($currentStatus): ?>
        <div class="mb-4">
            <a href="<?= BASEURL; ?>/guru/riwayatLaporan" class="text-sm text-primary-600 hover:underline flex items-center gap-1">
                <i data-lucide="x" class="w-3 h-3"></i> Hapus filter: <?= ucfirst($currentStatus) ?>
            </a>
        </div>
    <?php endif; ?>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <?php if (empty($laporanList)): ?>
            <div class="p-8 text-center">
                <i data-lucide="inbox" class="w-12 h-12 text-secondary-300 mx-auto mb-3"></i>
                <p class="text-secondary-500 text-sm">Belum ada laporan<?= $currentStatus ? ' dengan status "' . ucfirst($currentStatus) . '"' : '' ?>.</p>
                <a href="<?= BASEURL; ?>/guru/laporSiswa" class="text-primary-600 hover:underline text-sm mt-2 inline-block">Buat laporan baru</a>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="text-left px-4 py-3 font-semibold text-secondary-600">Tanggal</th>
                            <th class="text-left px-4 py-3 font-semibold text-secondary-600">Siswa</th>
                            <th class="text-left px-4 py-3 font-semibold text-secondary-600 hidden sm:table-cell">Kelas</th>
                            <th class="text-left px-4 py-3 font-semibold text-secondary-600">Judul</th>
                            <th class="text-left px-4 py-3 font-semibold text-secondary-600 hidden md:table-cell">Kategori</th>
                            <th class="text-left px-4 py-3 font-semibold text-secondary-600">Status</th>
                            <th class="text-left px-4 py-3 font-semibold text-secondary-600 hidden lg:table-cell">Ditangani</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php foreach ($laporanList as $l): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 text-secondary-600 whitespace-nowrap">
                                    <?= date('d/m/Y', strtotime($l['created_at'])) ?>
                                </td>
                                <td class="px-4 py-3 font-medium text-secondary-800">
                                    <?= htmlspecialchars($l['nama_siswa']) ?>
                                </td>
                                <td class="px-4 py-3 text-secondary-600 hidden sm:table-cell">
                                    <?= htmlspecialchars($l['nama_kelas']) ?>
                                </td>
                                <td class="px-4 py-3 text-secondary-700 max-w-[200px] truncate">
                                    <?= htmlspecialchars($l['judul']) ?>
                                </td>
                                <td class="px-4 py-3 hidden md:table-cell">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                        <?php
                                        switch ($l['kategori']) {
                                            case 'pelanggaran': echo 'bg-red-100 text-red-700'; break;
                                            case 'akademik': echo 'bg-blue-100 text-blue-700'; break;
                                            case 'pribadi': echo 'bg-purple-100 text-purple-700'; break;
                                            case 'sosial': echo 'bg-teal-100 text-teal-700'; break;
                                            case 'karir': echo 'bg-amber-100 text-amber-700'; break;
                                            default: echo 'bg-gray-100 text-gray-700';
                                        }
                                        ?>">
                                        <?= ucfirst($l['kategori']) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                        <?php
                                        switch ($l['status']) {
                                            case 'baru': echo 'bg-blue-100 text-blue-700'; break;
                                            case 'proses': echo 'bg-amber-100 text-amber-700'; break;
                                            case 'selesai': echo 'bg-green-100 text-green-700'; break;
                                            case 'dirujuk': echo 'bg-red-100 text-red-700'; break;
                                            default: echo 'bg-gray-100 text-gray-700';
                                        }
                                        ?>">
                                        <?= ucfirst($l['status']) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-secondary-600 hidden lg:table-cell">
                                    <?= $l['nama_guru_bk'] ? htmlspecialchars($l['nama_guru_bk']) : '<span class="text-secondary-400 italic">Belum diambil</span>' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>
