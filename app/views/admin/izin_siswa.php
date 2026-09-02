<?php
// File: app/views/admin/izin_siswa.php
$izinList = $data['izin_list'] ?? [];
$stats = $data['stats'] ?? [];
$kelasList = $data['kelas_list'] ?? [];
$filters = $data['filters'] ?? [];
?>
<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gradient-to-br from-secondary-50 to-secondary-100 p-4 sm:p-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl sm:text-3xl font-bold text-secondary-800 flex items-center">
                <i data-lucide="file-check" class="w-7 h-7 sm:w-8 sm:h-8 mr-2 sm:mr-3 text-primary-500"></i>
                Izin Siswa
            </h2>
            <p class="text-secondary-600 mt-1 text-sm">Monitoring izin, sakit, dan dispensasi seluruh siswa</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3 mb-6">
        <?php
        $statCards = [
            ['label' => 'Total', 'value' => $stats['total'] ?? 0, 'icon' => 'list', 'color' => 'secondary'],
            ['label' => 'Aktif', 'value' => $stats['aktif'] ?? 0, 'icon' => 'clock', 'color' => 'green'],
            ['label' => 'Izin', 'value' => $stats['izin'] ?? 0, 'icon' => 'info', 'color' => 'blue'],
            ['label' => 'Sakit', 'value' => $stats['sakit'] ?? 0, 'icon' => 'thermometer', 'color' => 'yellow'],
            ['label' => 'Dispensasi', 'value' => $stats['dispensasi'] ?? 0, 'icon' => 'shield-check', 'color' => 'purple'],
            ['label' => 'Selesai', 'value' => $stats['selesai'] ?? 0, 'icon' => 'check-circle', 'color' => 'slate'],
            ['label' => 'Dibatalkan', 'value' => $stats['dibatalkan'] ?? 0, 'icon' => 'x-circle', 'color' => 'red'],
        ];
        foreach ($statCards as $card): ?>
            <div class="bg-white rounded-xl p-3 sm:p-4 shadow-sm border border-gray-100">
                <div class="flex items-center gap-2">
                    <div class="bg-<?= $card['color'] ?>-100 p-1.5 rounded-lg flex-shrink-0">
                        <i data-lucide="<?= $card['icon'] ?>" class="w-4 h-4 text-<?= $card['color'] ?>-600"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-lg sm:text-xl font-bold text-secondary-800"><?= $card['value'] ?></p>
                        <p class="text-[10px] sm:text-xs text-secondary-500 truncate"><?= $card['label'] ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php Flasher::flash(); ?>

    <!-- Table Card -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <!-- Filters -->
        <div class="px-4 sm:px-6 py-4 border-b bg-gray-50">
            <form method="GET" action="<?= BASEURL; ?>/admin/izinSiswa" class="flex flex-col sm:flex-row gap-3 items-end">
                <div class="flex-1 min-w-0">
                    <label class="block text-xs font-medium text-secondary-500 mb-1">Cari Siswa</label>
                    <div class="relative">
                        <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-secondary-400"></i>
                        <input type="text" name="q" value="<?= htmlspecialchars($filters['search'] ?? '') ?>"
                               placeholder="Nama atau NISN..."
                               class="w-full pl-10 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-secondary-500 mb-1">Kelas</label>
                    <select name="kelas" class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500">
                        <option value="">Semua Kelas</option>
                        <?php foreach ($kelasList as $kls): ?>
                            <option value="<?= $kls['id_kelas'] ?>" <?= ($filters['id_kelas'] ?? '') == $kls['id_kelas'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($kls['nama_kelas']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-secondary-500 mb-1">Jenis</label>
                    <select name="jenis" class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500">
                        <option value="">Semua Jenis</option>
                        <option value="I" <?= ($filters['jenis'] ?? '') === 'I' ? 'selected' : '' ?>>Izin</option>
                        <option value="S" <?= ($filters['jenis'] ?? '') === 'S' ? 'selected' : '' ?>>Sakit</option>
                        <option value="D" <?= ($filters['jenis'] ?? '') === 'D' ? 'selected' : '' ?>>Dispensasi</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-secondary-500 mb-1">Status</label>
                    <select name="status" class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500">
                        <option value="">Semua Status</option>
                        <option value="aktif" <?= ($filters['status'] ?? '') === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                        <option value="selesai" <?= ($filters['status'] ?? '') === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                        <option value="dibatalkan" <?= ($filters['status'] ?? '') === 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-secondary-500 mb-1">Bulan</label>
                    <select name="bulan" class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500">
                        <option value="">Semua</option>
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= $m ?>" <?= ($filters['bulan'] ?? '') == $m ? 'selected' : '' ?>>
                                <?= date('M', mktime(0, 0, 0, $m, 1)) ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 transition-colors">
                        <i data-lucide="filter" class="w-4 h-4 inline mr-1"></i>Filter
                    </button>
                    <?php if (!empty($filters)): ?>
                        <a href="<?= BASEURL; ?>/admin/izinSiswa" class="px-4 py-2 text-sm font-medium text-secondary-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                            <i data-lucide="x" class="w-4 h-4 inline mr-1"></i>Reset
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Desktop Table -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-secondary-500 uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-secondary-500 uppercase tracking-wider">Siswa</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-secondary-500 uppercase tracking-wider">Kelas</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-secondary-500 uppercase tracking-wider">Jenis</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-secondary-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-secondary-500 uppercase tracking-wider">Keterangan</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-secondary-500 uppercase tracking-wider">Diinput Oleh</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-secondary-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-secondary-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (empty($izinList)): ?>
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center text-secondary-400">
                                <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                                <p class="text-lg font-medium">Belum ada data izin</p>
                                <p class="text-sm mt-1">Data izin siswa akan muncul setelah wali kelas menginput</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($izinList as $i => $izin):
                            $jenisLabel = ['I' => 'Izin', 'S' => 'Sakit', 'D' => 'Dispensasi'];
                            $jenisColor = ['I' => 'blue', 'S' => 'yellow', 'D' => 'purple'];
                            $statusColor = ['aktif' => 'green', 'selesai' => 'gray', 'dibatalkan' => 'red'];
                            $jc = $jenisColor[$izin['jenis_izin']] ?? 'gray';
                            $sc = $statusColor[$izin['status']] ?? 'gray';
                            $isExpired = $izin['status'] === 'aktif' && $izin['tanggal_selesai'] < date('Y-m-d');
                        ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 text-secondary-500"><?= $i + 1 ?></td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-secondary-800"><?= htmlspecialchars($izin['nama_siswa']) ?></div>
                                    <div class="text-xs text-secondary-500"><?= htmlspecialchars($izin['nisn'] ?? '-') ?></div>
                                </td>
                                <td class="px-4 py-3 text-secondary-600"><?= htmlspecialchars($izin['nama_kelas']) ?></td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-<?= $jc ?>-100 text-<?= $jc ?>-700">
                                        <?= $jenisLabel[$izin['jenis_izin']] ?? $izin['jenis_izin'] ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-secondary-600 whitespace-nowrap">
                                    <div><?= date('d M Y', strtotime($izin['tanggal_mulai'])) ?></div>
                                    <?php if ($izin['tanggal_mulai'] !== $izin['tanggal_selesai']): ?>
                                        <div class="text-xs text-secondary-400">s/d <?= date('d M Y', strtotime($izin['tanggal_selesai'])) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-secondary-600 max-w-[200px]">
                                    <div class="truncate" title="<?= htmlspecialchars($izin['keterangan'] ?? '') ?>">
                                        <?= htmlspecialchars($izin['keterangan'] ?? '-') ?>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-secondary-600 text-xs">
                                    <?= htmlspecialchars($izin['nama_guru'] ?? '-') ?>
                                </td>
                                <td class="px-4 py-3">
                                    <?php if ($isExpired): ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Expired</span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-<?= $sc ?>-100 text-<?= $sc ?>-700">
                                            <?= ucfirst($izin['status']) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <a href="<?= BASEURL; ?>/admin/detailIzin/<?= $izin['id_izin'] ?>"
                                           class="px-3 py-1.5 text-xs font-semibold rounded-lg text-white bg-blue-500 hover:bg-blue-600 transition-colors inline-flex items-center gap-1" title="Detail">
                                            <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                        </a>
                                        <?php if ($izin['status'] === 'aktif'): ?>
                                            <form method="POST" action="<?= BASEURL; ?>/admin/adminBatalkanIzin"
                                                  onsubmit="return confirm('Yakin ingin membatalkan izin <?= htmlspecialchars($izin['nama_siswa']) ?>?');" class="inline">
                                                <input type="hidden" name="id_izin" value="<?= $izin['id_izin'] ?>">
                                                <button type="submit" class="px-3 py-1.5 text-xs font-semibold rounded-lg text-white bg-red-500 hover:bg-red-600 transition-colors inline-flex items-center gap-1" title="Batalkan">
                                                    <i data-lucide="x-circle" class="w-3.5 h-3.5"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div class="md:hidden divide-y divide-gray-100">
            <?php if (empty($izinList)): ?>
                <div class="px-4 py-12 text-center text-secondary-400">
                    <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                    <p class="text-lg font-medium">Belum ada data izin</p>
                </div>
            <?php else: ?>
                <?php foreach ($izinList as $izin):
                    $jenisLabel = ['I' => 'Izin', 'S' => 'Sakit', 'D' => 'Dispensasi'];
                    $jenisColor = ['I' => 'blue', 'S' => 'yellow', 'D' => 'purple'];
                    $statusColor = ['aktif' => 'green', 'selesai' => 'gray', 'dibatalkan' => 'red'];
                    $jc = $jenisColor[$izin['jenis_izin']] ?? 'gray';
                    $sc = $statusColor[$izin['status']] ?? 'gray';
                    $isExpired = $izin['status'] === 'aktif' && $izin['tanggal_selesai'] < date('Y-m-d');
                ?>
                    <div class="p-4 hover:bg-gray-50">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-10 h-10 rounded-full bg-<?= $jc ?>-100 text-<?= $jc ?>-600 flex items-center justify-center text-sm font-bold flex-shrink-0">
                                    <?= strtoupper(substr($izin['nama_siswa'], 0, 1)) ?>
                                </div>
                                <div class="min-w-0">
                                    <div class="font-medium text-secondary-800 truncate"><?= htmlspecialchars($izin['nama_siswa']) ?></div>
                                    <div class="text-xs text-secondary-500"><?= htmlspecialchars($izin['nama_kelas']) ?> &middot; <?= htmlspecialchars($izin['nisn'] ?? '-') ?></div>
                                </div>
                            </div>
                            <?php if ($isExpired): ?>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-amber-100 text-amber-700 flex-shrink-0">Expired</span>
                            <?php else: ?>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-<?= $sc ?>-100 text-<?= $sc ?>-700 flex-shrink-0"><?= ucfirst($izin['status']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="mt-2 flex flex-wrap gap-2 text-xs">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-<?= $jc ?>-100 text-<?= $jc ?>-700 font-medium">
                                <?= $jenisLabel[$izin['jenis_izin']] ?? $izin['jenis_izin'] ?>
                            </span>
                            <span class="text-secondary-500">
                                <?= date('d M', strtotime($izin['tanggal_mulai'])) ?><?= $izin['tanggal_mulai'] !== $izin['tanggal_selesai'] ? ' - ' . date('d M', strtotime($izin['tanggal_selesai'])) : '' ?>
                            </span>
                        </div>
                        <?php if (!empty($izin['keterangan'])): ?>
                            <p class="mt-1.5 text-xs text-secondary-500 line-clamp-2"><?= htmlspecialchars($izin['keterangan']) ?></p>
                        <?php endif; ?>
                        <div class="mt-2 flex items-center gap-2">
                            <a href="<?= BASEURL; ?>/admin/detailIzin/<?= $izin['id_izin'] ?>"
                               class="px-3 py-1.5 text-xs font-semibold rounded-lg text-white bg-blue-500 hover:bg-blue-600 inline-flex items-center gap-1">
                                <i data-lucide="eye" class="w-3 h-3"></i> Detail
                            </a>
                            <?php if ($izin['status'] === 'aktif'): ?>
                                <form method="POST" action="<?= BASEURL; ?>/admin/adminBatalkanIzin"
                                      onsubmit="return confirm('Batalkan izin?');" class="inline">
                                    <input type="hidden" name="id_izin" value="<?= $izin['id_izin'] ?>">
                                    <button type="submit" class="px-3 py-1.5 text-xs font-semibold rounded-lg text-white bg-red-500 hover:bg-red-600 inline-flex items-center gap-1">
                                        <i data-lucide="x-circle" class="w-3 h-3"></i> Batalkan
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Footer count -->
        <?php if (!empty($izinList)): ?>
            <div class="px-4 sm:px-6 py-3 border-t bg-gray-50 text-xs text-secondary-500">
                Menampilkan <?= count($izinList) ?> data izin
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof lucide !== 'undefined') lucide.createIcons();
});
</script>
