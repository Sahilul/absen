<?php
$kasus = $data['kasus'] ?? [];
$konselingList = $data['konseling_list'] ?? [];
$panggilanList = $data['panggilan_list'] ?? [];

$tc = ['ringan'=>'slate','sedang'=>'yellow','berat'=>'red'];
$sc = ['baru'=>'blue','proses'=>'amber','selesai'=>'green','dirujuk'=>'purple'];
$tingkatColor = $tc[$kasus['tingkat']] ?? 'slate';
$statusColor = $sc[$kasus['status']] ?? 'slate';
?>
<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gradient-to-br from-secondary-50 to-secondary-100 p-4 sm:p-6">
    <div class="mb-6">
        <a href="<?= BASEURL; ?>/admin/bimbinganKonseling" class="text-sm text-primary-600 hover:underline flex items-center gap-1 mb-2">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali
        </a>
        <h2 class="text-2xl sm:text-3xl font-bold text-secondary-800 flex items-center">
            <i data-lucide="file-text" class="w-7 h-7 sm:w-8 sm:h-8 mr-2 sm:mr-3 text-rose-500"></i>
            Detail Kasus BK
        </h2>
    </div>

    <!-- Info Kasus -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden mb-6">
        <div class="px-4 sm:px-6 py-4 border-b bg-gray-50 flex items-center justify-between">
            <h3 class="text-sm font-bold text-secondary-800 flex items-center gap-2">
                <i data-lucide="info" class="w-4 h-4 text-blue-500"></i> Informasi Kasus
            </h3>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-<?= $tingkatColor ?>-100 text-<?= $tingkatColor ?>-700"><?= ucfirst($kasus['tingkat']) ?></span>
                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-<?= $statusColor ?>-100 text-<?= $statusColor ?>-700"><?= ucfirst($kasus['status']) ?></span>
            </div>
        </div>
        <div class="p-4 sm:p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <p class="text-xs text-secondary-500 mb-0.5">Siswa</p>
                    <p class="text-sm font-semibold text-secondary-800"><?= htmlspecialchars($kasus['nama_siswa']) ?></p>
                </div>
                <div>
                    <p class="text-xs text-secondary-500 mb-0.5">Kelas</p>
                    <p class="text-sm font-semibold text-secondary-800"><?= htmlspecialchars($kasus['nama_kelas']) ?></p>
                </div>
                <div>
                    <p class="text-xs text-secondary-500 mb-0.5">Kategori</p>
                    <p class="text-sm"><span class="px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-700"><?= ucfirst($kasus['kategori']) ?></span></p>
                </div>
                <div>
                    <p class="text-xs text-secondary-500 mb-0.5">Poin</p>
                    <p class="text-sm font-semibold text-secondary-800"><?= $kasus['poin'] ?></p>
                </div>
                <div>
                    <p class="text-xs text-secondary-500 mb-0.5">Pelapor</p>
                    <p class="text-sm text-secondary-800"><?= htmlspecialchars($kasus['nama_pelapor'] ?? '-') ?></p>
                </div>
                <div>
                    <p class="text-xs text-secondary-500 mb-0.5">Guru BK</p>
                    <p class="text-sm text-secondary-800"><?= htmlspecialchars($kasus['nama_guru_bk'] ?? '-') ?></p>
                </div>
                <div>
                    <p class="text-xs text-secondary-500 mb-0.5">Tanggal Dibuat</p>
                    <p class="text-sm text-secondary-800"><?= date('d F Y, H:i', strtotime($kasus['created_at'])) ?></p>
                </div>
            </div>

            <div class="mb-4">
                <p class="text-xs text-secondary-500 mb-1">Judul</p>
                <p class="text-sm font-semibold text-secondary-800"><?= htmlspecialchars($kasus['judul']) ?></p>
            </div>

            <?php if (!empty($kasus['deskripsi'])): ?>
            <div class="mb-4">
                <p class="text-xs text-secondary-500 mb-1">Deskripsi</p>
                <p class="text-sm text-secondary-700 whitespace-pre-line"><?= htmlspecialchars($kasus['deskripsi']) ?></p>
            </div>
            <?php endif; ?>

            <?php if (!empty($kasus['tindak_lanjut'])): ?>
            <div>
                <p class="text-xs text-secondary-500 mb-1">Tindak Lanjut</p>
                <p class="text-sm text-secondary-700 whitespace-pre-line"><?= htmlspecialchars($kasus['tindak_lanjut']) ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Riwayat Konseling -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden mb-6">
        <div class="px-4 sm:px-6 py-4 border-b bg-gray-50">
            <h3 class="text-sm font-bold text-secondary-800 flex items-center gap-2">
                <i data-lucide="message-circle" class="w-4 h-4 text-green-500"></i> Riwayat Konseling (<?= count($konselingList) ?>)
            </h3>
        </div>
        <?php if (empty($konselingList)): ?>
            <div class="px-6 py-8 text-center">
                <p class="text-secondary-500 text-sm">Belum ada sesi konseling</p>
            </div>
        <?php else: ?>
            <div class="divide-y">
                <?php foreach ($konselingList as $kon): ?>
                <div class="px-4 sm:px-6 py-4">
                    <div class="flex items-start justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <span class="bg-green-100 text-green-700 text-xs font-bold px-2 py-0.5 rounded-full">Ke-<?= $kon['konseling_ke'] ?></span>
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700"><?= ucfirst($kon['jenis']) ?></span>
                        </div>
                        <span class="text-xs text-secondary-500"><?= date('d/m/Y', strtotime($kon['tanggal'])) ?></span>
                    </div>
                    <?php if (!empty($kon['catatan'])): ?>
                        <p class="text-sm text-secondary-700 mb-1"><span class="font-medium">Catatan:</span> <?= htmlspecialchars($kon['catatan']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($kon['hasil'])): ?>
                        <p class="text-sm text-secondary-700 mb-1"><span class="font-medium">Hasil:</span> <?= htmlspecialchars($kon['hasil']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($kon['rencana_tindak_lanjut'])): ?>
                        <p class="text-sm text-secondary-700"><span class="font-medium">Rencana:</span> <?= htmlspecialchars($kon['rencana_tindak_lanjut']) ?></p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Panggilan Orang Tua -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b bg-gray-50">
            <h3 class="text-sm font-bold text-secondary-800 flex items-center gap-2">
                <i data-lucide="phone" class="w-4 h-4 text-amber-500"></i> Panggilan Orang Tua (<?= count($panggilanList) ?>)
            </h3>
        </div>
        <?php if (empty($panggilanList)): ?>
            <div class="px-6 py-8 text-center">
                <p class="text-secondary-500 text-sm">Belum ada panggilan orang tua</p>
            </div>
        <?php else: ?>
            <div class="divide-y">
                <?php foreach ($panggilanList as $pg): ?>
                <div class="px-4 sm:px-6 py-4">
                    <div class="flex items-start justify-between mb-1">
                        <div>
                            <?php if (!empty($pg['nomor_surat'])): ?>
                                <p class="text-xs text-secondary-500">No. Surat: <?= htmlspecialchars($pg['nomor_surat']) ?></p>
                            <?php endif; ?>
                            <p class="text-sm text-secondary-800">Tanggal: <?= date('d/m/Y', strtotime($pg['tanggal_panggilan'])) ?></p>
                        </div>
                        <?php
                        $pgc = ['dijadwalkan'=>'blue','dikirim'=>'amber','hadir'=>'green','tidak_hadir'=>'red','dijadwalkan_ulang'=>'purple'];
                        $pgColor = $pgc[$pg['status']] ?? 'slate';
                        ?>
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-<?= $pgColor ?>-100 text-<?= $pgColor ?>-700"><?= ucfirst(str_replace('_', ' ', $pg['status'])) ?></span>
                    </div>
                    <?php if (!empty($pg['catatan_hasil'])): ?>
                        <p class="text-sm text-secondary-700"><?= htmlspecialchars($pg['catatan_hasil']) ?></p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>
