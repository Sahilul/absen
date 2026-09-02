<?php
// File: app/views/wali_kelas/izin_siswa.php
$izinList = $data['izin_list'] ?? [];
$waliKelasInfo = $data['wali_kelas_info'] ?? [];
$countAktif = $data['count_aktif'] ?? 0;
?>
<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gradient-to-br from-secondary-50 to-secondary-100 p-4 sm:p-6">
    <!-- Breadcrumb -->
    <div class="mb-4 sm:mb-6">
        <nav class="flex items-center space-x-2 text-sm text-secondary-600">
            <a href="<?= BASEURL; ?>/waliKelas/dashboard" class="hover:text-primary-600 transition-colors">Dashboard</a>
            <i data-lucide="chevron-right" class="w-4 h-4"></i>
            <span class="text-secondary-800 font-medium">Izin Siswa</span>
        </nav>
    </div>

    <!-- Header -->
    <div class="mb-5 sm:mb-8">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center space-x-3 sm:space-x-4">
                <div class="gradient-primary p-3 rounded-xl flex-shrink-0">
                    <i data-lucide="file-check" class="w-7 h-7 text-white"></i>
                </div>
                <div>
                    <h2 class="text-2xl sm:text-3xl font-bold text-secondary-800">Izin Siswa</h2>
                    <p class="text-secondary-600 mt-1 text-sm sm:text-base">
                        Kelola izin, sakit, dan dispensasi siswa kelas <?= htmlspecialchars($waliKelasInfo['nama_kelas'] ?? ''); ?>
                    </p>
                </div>
            </div>
            <a href="<?= BASEURL; ?>/waliKelas/tambahIzin"
               class="btn-primary px-5 py-3 flex items-center gap-2 whitespace-nowrap">
                <i data-lucide="plus-circle" class="w-5 h-5"></i>
                Tambah Izin
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-5 sm:mb-8">
        <?php
        $totalAktif = 0; $totalIzin = 0; $totalSakit = 0; $totalDisp = 0;
        foreach ($izinList as $iz) {
            if ($iz['status'] === 'aktif' && $iz['tanggal_selesai'] >= date('Y-m-d')) {
                $totalAktif++;
                if ($iz['jenis_izin'] === 'I') $totalIzin++;
                if ($iz['jenis_izin'] === 'S') $totalSakit++;
                if ($iz['jenis_izin'] === 'D') $totalDisp++;
            }
        }
        ?>
        <div class="glass-effect rounded-xl p-4 border border-white/20">
            <div class="flex items-center gap-3">
                <div class="bg-primary-100 p-2 rounded-lg"><i data-lucide="users" class="w-5 h-5 text-primary-600"></i></div>
                <div>
                    <p class="text-2xl font-bold text-secondary-800"><?= $totalAktif ?></p>
                    <p class="text-xs text-secondary-500">Aktif Saat Ini</p>
                </div>
            </div>
        </div>
        <div class="glass-effect rounded-xl p-4 border border-white/20">
            <div class="flex items-center gap-3">
                <div class="bg-blue-100 p-2 rounded-lg"><i data-lucide="info" class="w-5 h-5 text-blue-600"></i></div>
                <div>
                    <p class="text-2xl font-bold text-blue-700"><?= $totalIzin ?></p>
                    <p class="text-xs text-secondary-500">Izin</p>
                </div>
            </div>
        </div>
        <div class="glass-effect rounded-xl p-4 border border-white/20">
            <div class="flex items-center gap-3">
                <div class="bg-yellow-100 p-2 rounded-lg"><i data-lucide="thermometer" class="w-5 h-5 text-yellow-600"></i></div>
                <div>
                    <p class="text-2xl font-bold text-yellow-700"><?= $totalSakit ?></p>
                    <p class="text-xs text-secondary-500">Sakit</p>
                </div>
            </div>
        </div>
        <div class="glass-effect rounded-xl p-4 border border-white/20">
            <div class="flex items-center gap-3">
                <div class="bg-purple-100 p-2 rounded-lg"><i data-lucide="shield-check" class="w-5 h-5 text-purple-600"></i></div>
                <div>
                    <p class="text-2xl font-bold text-purple-700"><?= $totalDisp ?></p>
                    <p class="text-xs text-secondary-500">Dispensasi</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="mb-4 sm:mb-6">
        <div class="glass-effect rounded-xl p-4 border border-white/20">
            <form method="GET" action="<?= BASEURL; ?>/waliKelas/izinSiswa" class="flex flex-col sm:flex-row gap-3">
                <select name="status" class="input-modern text-sm py-2">
                    <option value="">Semua Status</option>
                    <option value="aktif" <?= ($_GET['status'] ?? '') === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                    <option value="selesai" <?= ($_GET['status'] ?? '') === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                    <option value="dibatalkan" <?= ($_GET['status'] ?? '') === 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                </select>
                <select name="bulan" class="input-modern text-sm py-2">
                    <option value="">Semua Bulan</option>
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= $m ?>" <?= ($_GET['bulan'] ?? '') == $m ? 'selected' : '' ?>>
                            <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                        </option>
                    <?php endfor; ?>
                </select>
                <button type="submit" class="btn-primary px-4 py-2 text-sm">
                    <i data-lucide="filter" class="w-4 h-4 inline mr-1"></i> Filter
                </button>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="glass-effect rounded-xl border border-white/20 shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gradient-to-r from-secondary-50 to-secondary-100 border-b border-secondary-200">
                        <th class="px-4 py-3 text-left font-semibold text-secondary-700">No</th>
                        <th class="px-4 py-3 text-left font-semibold text-secondary-700">Siswa</th>
                        <th class="px-4 py-3 text-left font-semibold text-secondary-700">Jenis</th>
                        <th class="px-4 py-3 text-left font-semibold text-secondary-700">Tanggal</th>
                        <th class="px-4 py-3 text-left font-semibold text-secondary-700">Keterangan</th>
                        <th class="px-4 py-3 text-left font-semibold text-secondary-700">Status</th>
                        <th class="px-4 py-3 text-center font-semibold text-secondary-700">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-secondary-100">
                    <?php if (empty($izinList)): ?>
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-secondary-400">
                                <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                                <p class="text-lg font-medium">Belum ada data izin</p>
                                <p class="text-sm mt-1">Klik "Tambah Izin" untuk menambahkan</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($izinList as $i => $izin): ?>
                            <?php
                            $jenisLabel = ['I' => 'Izin', 'S' => 'Sakit', 'D' => 'Dispensasi'];
                            $jenisColor = ['I' => 'blue', 'S' => 'yellow', 'D' => 'purple'];
                            $statusColor = ['aktif' => 'green', 'selesai' => 'secondary', 'dibatalkan' => 'red'];
                            $jc = $jenisColor[$izin['jenis_izin']] ?? 'secondary';
                            $sc = $statusColor[$izin['status']] ?? 'secondary';
                            $isExpired = $izin['status'] === 'aktif' && $izin['tanggal_selesai'] < date('Y-m-d');
                            ?>
                            <tr class="hover:bg-white/50 transition-colors">
                                <td class="px-4 py-3 text-secondary-600"><?= $i + 1 ?></td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-secondary-800"><?= htmlspecialchars($izin['nama_siswa']) ?></div>
                                    <div class="text-xs text-secondary-500"><?= htmlspecialchars($izin['nisn']) ?></div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-<?= $jc ?>-100 text-<?= $jc ?>-700">
                                        <?= $jenisLabel[$izin['jenis_izin']] ?? $izin['jenis_izin'] ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-secondary-600">
                                    <div><?= date('d M Y', strtotime($izin['tanggal_mulai'])) ?></div>
                                    <?php if ($izin['tanggal_mulai'] !== $izin['tanggal_selesai']): ?>
                                        <div class="text-xs text-secondary-400">s/d <?= date('d M Y', strtotime($izin['tanggal_selesai'])) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-secondary-600 max-w-[200px] truncate">
                                    <?= htmlspecialchars($izin['keterangan'] ?? '-') ?>
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
                                    <?php if ($izin['status'] === 'aktif'): ?>
                                        <div class="flex items-center justify-center gap-1">
                                            <a href="<?= BASEURL; ?>/waliKelas/editIzin/<?= $izin['id_izin'] ?>"
                                               class="p-2 rounded-lg text-primary-600 hover:bg-primary-50 transition-colors" title="Edit">
                                                <i data-lucide="pencil" class="w-4 h-4"></i>
                                            </a>
                                            <form method="POST" action="<?= BASEURL; ?>/waliKelas/batalkanIzin"
                                                  onsubmit="return confirm('Yakin ingin membatalkan izin ini?');" class="inline">
                                                <input type="hidden" name="id_izin" value="<?= $izin['id_izin'] ?>">
                                                <button type="submit" class="p-2 rounded-lg text-red-600 hover:bg-red-50 transition-colors" title="Batalkan">
                                                    <i data-lucide="x-circle" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-secondary-400">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof lucide !== 'undefined') lucide.createIcons();
});
</script>
