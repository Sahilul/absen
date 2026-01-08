<?php
// File: app/views/bendahara/riwayat.php
$riwayat = $data['riwayat'] ?? [];
$kelas = $data['kelas'] ?? null;
$kelasList = $data['kelas_list'] ?? [];
$namaKelas = $kelas ? htmlspecialchars($kelas['nama_kelas']) : 'Semua Kelas';
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
                class="w-12 h-12 bg-gradient-to-br from-gray-600 to-gray-700 rounded-xl flex items-center justify-center shadow-lg">
                <i data-lucide="history" class="w-6 h-6 text-white"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Riwayat Pembayaran</h1>
                <p class="text-sm text-gray-500"><?= $namaKelas ?></p>
            </div>
        </div>

        <!-- Filter Kelas -->
        <div class="flex items-center gap-2">
            <label class="text-sm text-gray-600">Filter:</label>
            <select id="filterKelas" onchange="filterByKelas(this.value)"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500">
                <option value="">Semua Kelas</option>
                <?php foreach ($kelasList as $k): ?>
                    <option value="<?= $k['id_kelas'] ?>" <?= ($kelas && $kelas['id_kelas'] == $k['id_kelas']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($k['nama_kelas']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <?php if (class_exists('Flasher')) {
        Flasher::flash();
    } ?>

    <!-- Riwayat Table -->
    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
        <?php if (empty($riwayat)): ?>
            <div class="text-center py-12">
                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="inbox" class="w-10 h-10 text-gray-400"></i>
                </div>
                <p class="text-gray-500 font-medium">Belum ada riwayat pembayaran</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gradient-to-r from-gray-50 to-gray-100">
                            <th class="text-left p-4 font-semibold text-gray-700">Tanggal</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Nama Siswa</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Tagihan</th>
                            <th class="text-right p-4 font-semibold text-gray-700">Jumlah</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Metode</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Petugas Input</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($riwayat as $r): ?>
                            <tr class="hover:bg-blue-50 transition-colors">
                                <td class="p-4 text-gray-600">
                                    <?= date('d/m/Y H:i', strtotime($r['created_at'] ?? $r['tanggal'] ?? '')) ?>
                                </td>
                                <td class="p-4 font-medium text-gray-800"><?= htmlspecialchars($r['nama_siswa'] ?? '-') ?></td>
                                <td class="p-4 text-gray-600">
                                    <?= htmlspecialchars($r['nama_tagihan'] ?? $r['tagihan_nama'] ?? '-') ?>
                                </td>
                                <td class="p-4 text-right">
                                    <span
                                        class="inline-flex items-center px-3 py-1 bg-green-100 text-green-700 rounded-full font-semibold">
                                        Rp <?= number_format((int) ($r['jumlah'] ?? 0), 0, ',', '.') ?>
                                    </span>
                                </td>
                                <span
                                    class="inline-flex items-center px-2.5 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium capitalize">
                                    <?= htmlspecialchars($r['metode'] ?? 'tunai') ?>
                                </span>
                                </td>
                                <td class="p-4">
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 bg-amber-100 text-amber-700 rounded-full text-xs font-medium">
                                        <?= htmlspecialchars($r['petugas_input'] ?? 'Sistem') ?>
                                    </span>
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

    function filterByKelas(idKelas) {
        if (idKelas) {
            window.location.href = '<?= BASEURL ?>/bendahara/riwayat/' + idKelas;
        } else {
            window.location.href = '<?= BASEURL ?>/bendahara/riwayat';
        }
    }
</script>