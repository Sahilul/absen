<?php
$kasusList = $data['kasus_list'] ?? [];
$stats = $data['stats'] ?? [];
$kelasList = $data['kelas_list'] ?? [];
$guruBKList = $data['guru_bk_list'] ?? [];
$filters = $data['filters'] ?? [];
?>
<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gradient-to-br from-secondary-50 to-secondary-100 p-4 sm:p-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl sm:text-3xl font-bold text-secondary-800 flex items-center">
                <i data-lucide="heart-handshake" class="w-7 h-7 sm:w-8 sm:h-8 mr-2 sm:mr-3 text-rose-500"></i>
                Bimbingan Konseling
            </h2>
            <p class="text-secondary-600 mt-1 text-sm">Kelola seluruh kasus BK madrasah</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3 mb-6">
        <?php
        $statCards = [
            ['label' => 'Total', 'value' => $stats['total'] ?? 0, 'icon' => 'list', 'color' => 'secondary'],
            ['label' => 'Baru', 'value' => $stats['baru'] ?? 0, 'icon' => 'alert-circle', 'color' => 'blue'],
            ['label' => 'Proses', 'value' => $stats['proses'] ?? 0, 'icon' => 'clock', 'color' => 'amber'],
            ['label' => 'Selesai', 'value' => $stats['selesai'] ?? 0, 'icon' => 'check-circle', 'color' => 'green'],
            ['label' => 'Ringan', 'value' => $stats['ringan'] ?? 0, 'icon' => 'info', 'color' => 'slate'],
            ['label' => 'Sedang', 'value' => $stats['sedang'] ?? 0, 'icon' => 'alert-triangle', 'color' => 'yellow'],
            ['label' => 'Berat', 'value' => $stats['berat'] ?? 0, 'icon' => 'shield-alert', 'color' => 'red'],
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

    <!-- Guru BK Aktif -->
    <?php if (!empty($guruBKList)): ?>
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden mb-6">
        <div class="px-4 sm:px-6 py-3 border-b bg-green-50">
            <h3 class="text-sm font-bold text-green-800 flex items-center gap-2">
                <i data-lucide="users" class="w-4 h-4"></i> Guru BK Aktif (<?= count($guruBKList) ?>)
            </h3>
        </div>
        <div class="px-4 sm:px-6 py-3 flex flex-wrap gap-2">
            <?php foreach ($guruBKList as $g): ?>
                <span class="px-3 py-1 bg-green-50 text-green-700 text-xs font-medium rounded-full border border-green-200">
                    <?= htmlspecialchars($g['nama_guru']) ?>
                </span>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b bg-gray-50">
            <form method="GET" action="<?= BASEURL; ?>/admin/bimbinganKonseling" class="flex flex-col sm:flex-row gap-3 items-end">
                <div class="flex-1 min-w-0">
                    <label class="block text-xs font-medium text-secondary-500 mb-1">Cari</label>
                    <div class="relative">
                        <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-secondary-400"></i>
                        <input type="text" name="q" value="<?= htmlspecialchars($filters['search'] ?? '') ?>"
                               placeholder="Nama siswa atau judul kasus..."
                               class="w-full pl-10 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-secondary-500 mb-1">Kelas</label>
                    <select name="kelas" class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500">
                        <option value="">Semua</option>
                        <?php foreach ($kelasList as $kls): ?>
                            <option value="<?= $kls['id_kelas'] ?>" <?= ($filters['id_kelas'] ?? '') == $kls['id_kelas'] ? 'selected' : '' ?>><?= htmlspecialchars($kls['nama_kelas']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-secondary-500 mb-1">Status</label>
                    <select name="status" class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500">
                        <option value="">Semua</option>
                        <?php foreach (['baru','proses','selesai','dirujuk'] as $st): ?>
                            <option value="<?= $st ?>" <?= ($filters['status'] ?? '') === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-secondary-500 mb-1">Tingkat</label>
                    <select name="tingkat" class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500">
                        <option value="">Semua</option>
                        <?php foreach (['ringan','sedang','berat'] as $tg): ?>
                            <option value="<?= $tg ?>" <?= ($filters['tingkat'] ?? '') === $tg ? 'selected' : '' ?>><?= ucfirst($tg) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn-primary px-4 py-2 text-sm rounded-lg flex items-center gap-1">
                    <i data-lucide="filter" class="w-4 h-4"></i> Filter
                </button>
            </form>
        </div>

        <?php if (empty($kasusList)): ?>
            <div class="px-6 py-12 text-center">
                <i data-lucide="inbox" class="w-12 h-12 text-secondary-300 mx-auto mb-3"></i>
                <p class="text-secondary-500 text-sm">Tidak ada data kasus BK</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-secondary-600">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">#</th>
                            <th class="px-4 py-3 text-left font-semibold">Siswa</th>
                            <th class="px-4 py-3 text-left font-semibold">Kasus</th>
                            <th class="px-4 py-3 text-center font-semibold">Kategori</th>
                            <th class="px-4 py-3 text-center font-semibold">Tingkat</th>
                            <th class="px-4 py-3 text-center font-semibold">Status</th>
                            <th class="px-4 py-3 text-left font-semibold">Guru BK</th>
                            <th class="px-4 py-3 text-center font-semibold">Tanggal</th>
                            <th class="px-4 py-3 text-center font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php foreach ($kasusList as $i => $k): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-secondary-500"><?= $i + 1 ?></td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-secondary-800"><?= htmlspecialchars($k['nama_siswa']) ?></p>
                                <p class="text-xs text-secondary-500"><?= htmlspecialchars($k['nama_kelas']) ?></p>
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-secondary-800 truncate max-w-[200px]"><?= htmlspecialchars($k['judul']) ?></p>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-700"><?= ucfirst($k['kategori']) ?></span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <?php $tc = ['ringan'=>'slate','sedang'=>'yellow','berat'=>'red']; $c = $tc[$k['tingkat']] ?? 'slate'; ?>
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-<?= $c ?>-100 text-<?= $c ?>-700"><?= ucfirst($k['tingkat']) ?></span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <?php $sc = ['baru'=>'blue','proses'=>'amber','selesai'=>'green','dirujuk'=>'purple']; $s = $sc[$k['status']] ?? 'slate'; ?>
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-<?= $s ?>-100 text-<?= $s ?>-700"><?= ucfirst($k['status']) ?></span>
                            </td>
                            <td class="px-4 py-3 text-sm text-secondary-600"><?= htmlspecialchars($k['nama_guru_bk'] ?? '-') ?></td>
                            <td class="px-4 py-3 text-center text-xs text-secondary-500"><?= date('d/m/Y', strtotime($k['created_at'])) ?></td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="<?= BASEURL; ?>/admin/detailKasusBK/<?= $k['id_kasus'] ?>" class="p-1.5 rounded-lg text-primary-600 hover:bg-primary-50" title="Detail">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                    <form method="POST" action="<?= BASEURL; ?>/admin/hapusKasusBK" class="inline" onsubmit="return confirm('Yakin hapus kasus ini?')">
                                        <input type="hidden" name="id_kasus" value="<?= $k['id_kasus'] ?>">
                                        <button type="submit" class="p-1.5 rounded-lg text-red-600 hover:bg-red-50" title="Hapus">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>
