<?php
$stats = $data['stats'] ?? [];
$kasusList = $data['kasus_list'] ?? [];
$kasusBaru = $data['kasus_baru'] ?? [];
?>
<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gradient-to-br from-secondary-50 to-secondary-100 p-4 sm:p-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl sm:text-3xl font-bold text-secondary-800 flex items-center">
                <i data-lucide="heart-handshake" class="w-7 h-7 sm:w-8 sm:h-8 mr-2 sm:mr-3 text-rose-500"></i>
                Dashboard BK
            </h2>
            <p class="text-secondary-600 mt-1 text-sm">Ringkasan bimbingan konseling semester aktif</p>
        </div>
        <a href="<?= BASEURL; ?>/bk/tambahKasus" class="btn-primary px-4 py-2.5 flex items-center gap-2 text-sm rounded-xl shadow-sm">
            <i data-lucide="plus-circle" class="w-4 h-4"></i> Tambah Kasus
        </a>
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

    <!-- Kasus Baru (belum diambil) -->
    <?php if (!empty($kasusBaru)): ?>
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden mb-6">
        <div class="px-4 sm:px-6 py-4 border-b bg-blue-50 flex items-center gap-2">
            <i data-lucide="alert-circle" class="w-5 h-5 text-blue-600"></i>
            <h3 class="text-sm font-bold text-blue-800">Kasus Baru Menunggu Penanganan (<?= count($kasusBaru) ?>)</h3>
        </div>
        <div class="divide-y">
            <?php foreach ($kasusBaru as $kb): ?>
            <div class="px-4 sm:px-6 py-3 flex items-center justify-between hover:bg-gray-50">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-secondary-800 truncate"><?= htmlspecialchars($kb['judul']) ?></p>
                    <p class="text-xs text-secondary-500"><?= htmlspecialchars($kb['nama_siswa']) ?> — <?= htmlspecialchars($kb['nama_kelas']) ?></p>
                </div>
                <div class="flex items-center gap-2 ml-3">
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-<?= $kb['tingkat'] === 'berat' ? 'red' : ($kb['tingkat'] === 'sedang' ? 'yellow' : 'slate') ?>-100 text-<?= $kb['tingkat'] === 'berat' ? 'red' : ($kb['tingkat'] === 'sedang' ? 'yellow' : 'slate') ?>-700">
                        <?= ucfirst($kb['tingkat']) ?>
                    </span>
                    <form method="POST" action="<?= BASEURL; ?>/bk/ambilKasus" class="inline">
                        <input type="hidden" name="id_kasus" value="<?= $kb['id_kasus'] ?>">
                        <button type="submit" class="px-3 py-1 text-xs font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Ambil
                        </button>
                    </form>
                    <a href="<?= BASEURL; ?>/bk/detailKasus/<?= $kb['id_kasus'] ?>" class="text-secondary-400 hover:text-secondary-600">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Kasus Saya -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b bg-gray-50 flex items-center justify-between">
            <h3 class="text-sm font-bold text-secondary-800 flex items-center gap-2">
                <i data-lucide="clipboard-list" class="w-4 h-4 text-rose-500"></i>
                Kasus yang Saya Tangani
            </h3>
            <a href="<?= BASEURL; ?>/bk/daftarKasus" class="text-xs text-primary-600 hover:underline font-medium">Lihat Semua →</a>
        </div>
        <?php if (empty($kasusList)): ?>
            <div class="px-6 py-12 text-center">
                <i data-lucide="inbox" class="w-12 h-12 text-secondary-300 mx-auto mb-3"></i>
                <p class="text-secondary-500 text-sm">Belum ada kasus yang ditangani</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-secondary-600">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Siswa</th>
                            <th class="px-4 py-3 text-left font-semibold">Kasus</th>
                            <th class="px-4 py-3 text-center font-semibold">Kategori</th>
                            <th class="px-4 py-3 text-center font-semibold">Tingkat</th>
                            <th class="px-4 py-3 text-center font-semibold">Status</th>
                            <th class="px-4 py-3 text-center font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php foreach (array_slice($kasusList, 0, 10) as $k): ?>
                        <tr class="hover:bg-gray-50">
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
                                <?php
                                $tc = ['ringan' => 'slate', 'sedang' => 'yellow', 'berat' => 'red'];
                                $c = $tc[$k['tingkat']] ?? 'slate';
                                ?>
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-<?= $c ?>-100 text-<?= $c ?>-700"><?= ucfirst($k['tingkat']) ?></span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <?php
                                $sc = ['baru' => 'blue', 'proses' => 'amber', 'selesai' => 'green', 'dirujuk' => 'purple'];
                                $s = $sc[$k['status']] ?? 'slate';
                                ?>
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-<?= $s ?>-100 text-<?= $s ?>-700"><?= ucfirst($k['status']) ?></span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="<?= BASEURL; ?>/bk/detailKasus/<?= $k['id_kasus'] ?>" class="text-primary-600 hover:text-primary-800">
                                    <i data-lucide="eye" class="w-4 h-4 inline"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>
