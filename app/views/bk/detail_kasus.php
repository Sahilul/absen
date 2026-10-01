<?php
$kasus = $data['kasus'] ?? [];
$konselingList = $data['konseling_list'] ?? [];
$panggilanList = $data['panggilan_list'] ?? [];
$nextKe = $data['next_konseling_ke'] ?? 1;

$tc = ['ringan'=>'slate','sedang'=>'yellow','berat'=>'red'];
$sc = ['baru'=>'blue','proses'=>'amber','selesai'=>'green','dirujuk'=>'purple'];
$tingkatColor = $tc[$kasus['tingkat']] ?? 'slate';
$statusColor = $sc[$kasus['status']] ?? 'slate';
?>
<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gradient-to-br from-secondary-50 to-secondary-100 p-4 sm:p-6">
    <div class="mb-6">
        <a href="<?= BASEURL; ?>/bk/daftarKasus" class="text-sm text-primary-600 hover:underline flex items-center gap-1 mb-2">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali
        </a>
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <h2 class="text-2xl sm:text-3xl font-bold text-secondary-800 flex items-center">
                <i data-lucide="file-text" class="w-7 h-7 sm:w-8 sm:h-8 mr-2 sm:mr-3 text-rose-500"></i>
                Detail Kasus
            </h2>
            <div class="flex items-center gap-2">
                <a href="<?= BASEURL; ?>/bk/editKasus/<?= $kasus['id_kasus'] ?>" class="px-4 py-2 text-sm bg-amber-500 text-white rounded-xl hover:bg-amber-600 flex items-center gap-1">
                    <i data-lucide="pencil" class="w-4 h-4"></i> Edit
                </a>
                <a href="<?= BASEURL; ?>/bk/tambahKonseling/<?= $kasus['id_kasus'] ?>" class="btn-primary px-4 py-2 text-sm rounded-xl flex items-center gap-1">
                    <i data-lucide="message-circle" class="w-4 h-4"></i> Tambah Konseling
                </a>
            </div>
        </div>
    </div>

    <?php Flasher::flash(); ?>

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

            <?php if (empty($kasus['id_guru_bk'])): ?>
            <div class="mt-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                <p class="text-sm text-blue-700 mb-2">Kasus ini belum ada guru BK yang menangani.</p>
                <form method="POST" action="<?= BASEURL; ?>/bk/ambilKasus" class="inline">
                    <input type="hidden" name="id_kasus" value="<?= $kasus['id_kasus'] ?>">
                    <button type="submit" class="px-4 py-1.5 text-sm font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Ambil Kasus Ini
                    </button>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Riwayat Konseling -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden mb-6">
        <div class="px-4 sm:px-6 py-4 border-b bg-gray-50 flex items-center justify-between">
            <h3 class="text-sm font-bold text-secondary-800 flex items-center gap-2">
                <i data-lucide="message-circle" class="w-4 h-4 text-green-500"></i> Riwayat Konseling (<?= count($konselingList) ?>)
            </h3>
            <a href="<?= BASEURL; ?>/bk/tambahKonseling/<?= $kasus['id_kasus'] ?>" class="text-xs text-primary-600 hover:underline font-medium flex items-center gap-1">
                <i data-lucide="plus" class="w-3 h-3"></i> Tambah
            </a>
        </div>
        <?php if (empty($konselingList)): ?>
            <div class="px-6 py-8 text-center">
                <i data-lucide="message-circle" class="w-10 h-10 text-secondary-300 mx-auto mb-2"></i>
                <p class="text-secondary-500 text-sm">Belum ada sesi konseling</p>
            </div>
        <?php else: ?>
            <div class="divide-y">
                <?php foreach ($konselingList as $kon): ?>
                <div class="px-4 sm:px-6 py-4 hover:bg-gray-50">
                    <div class="flex items-start justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <span class="bg-green-100 text-green-700 text-xs font-bold px-2 py-0.5 rounded-full">Ke-<?= $kon['konseling_ke'] ?></span>
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700"><?= ucfirst($kon['jenis']) ?></span>
                        </div>
                        <span class="text-xs text-secondary-500"><?= date('d/m/Y', strtotime($kon['tanggal'])) ?>
                            <?php if ($kon['waktu_mulai']): ?> <?= substr($kon['waktu_mulai'], 0, 5) ?><?php endif; ?>
                            <?php if ($kon['waktu_selesai']): ?> - <?= substr($kon['waktu_selesai'], 0, 5) ?><?php endif; ?>
                        </span>
                    </div>
                    <?php if (!empty($kon['tempat'])): ?>
                        <p class="text-xs text-secondary-500 mb-1"><i data-lucide="map-pin" class="w-3 h-3 inline"></i> <?= htmlspecialchars($kon['tempat']) ?></p>
                    <?php endif; ?>
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
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden mb-6">
        <div class="px-4 sm:px-6 py-4 border-b bg-gray-50 flex items-center justify-between">
            <h3 class="text-sm font-bold text-secondary-800 flex items-center gap-2">
                <i data-lucide="phone" class="w-4 h-4 text-amber-500"></i> Panggilan Orang Tua (<?= count($panggilanList) ?>)
            </h3>
        </div>

        <?php if (!empty($panggilanList)): ?>
        <div class="divide-y">
            <?php foreach ($panggilanList as $pg): ?>
            <div class="px-4 sm:px-6 py-4 hover:bg-gray-50">
                <div class="flex items-start justify-between mb-2">
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

                <!-- Update status form -->
                <form method="POST" action="<?= BASEURL; ?>/bk/updatePanggilan" class="mt-2 flex flex-wrap items-center gap-2">
                    <input type="hidden" name="id_panggilan" value="<?= $pg['id_panggilan'] ?>">
                    <input type="hidden" name="id_kasus" value="<?= $kasus['id_kasus'] ?>">
                    <input type="hidden" name="tanggal_panggilan" value="<?= $pg['tanggal_panggilan'] ?>">
                    <select name="status" class="text-xs border border-gray-200 rounded-lg px-2 py-1 focus:ring-1 focus:ring-primary-500">
                        <?php foreach (['dijadwalkan','dikirim','hadir','tidak_hadir','dijadwalkan_ulang'] as $pgs): ?>
                            <option value="<?= $pgs ?>" <?= $pg['status'] === $pgs ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $pgs)) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="catatan_hasil" value="<?= htmlspecialchars($pg['catatan_hasil'] ?? '') ?>" placeholder="Catatan..."
                           class="text-xs border border-gray-200 rounded-lg px-2 py-1 flex-1 min-w-[120px] focus:ring-1 focus:ring-primary-500">
                    <button type="submit" class="text-xs px-3 py-1 bg-primary-600 text-white rounded-lg hover:bg-primary-700">Update</button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Form tambah panggilan -->
        <div class="px-4 sm:px-6 py-4 border-t bg-gray-50">
            <p class="text-xs font-bold text-secondary-700 mb-2">Jadwalkan Panggilan Baru</p>
            <form method="POST" action="<?= BASEURL; ?>/bk/tambahPanggilan/<?= $kasus['id_kasus'] ?>" class="flex flex-col sm:flex-row gap-2 items-end">
                <input type="hidden" name="id_kasus" value="<?= $kasus['id_kasus'] ?>">
                <div class="flex-1">
                    <label class="block text-xs text-secondary-500 mb-0.5">No. Surat</label>
                    <input type="text" name="nomor_surat" placeholder="Opsional" class="w-full text-xs border border-gray-200 rounded-lg px-2 py-1.5 focus:ring-1 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-xs text-secondary-500 mb-0.5">Tgl Surat</label>
                    <input type="date" name="tanggal_surat" value="<?= date('Y-m-d') ?>" class="text-xs border border-gray-200 rounded-lg px-2 py-1.5 focus:ring-1 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-xs text-secondary-500 mb-0.5">Tgl Panggilan</label>
                    <input type="date" name="tanggal_panggilan" value="<?= date('Y-m-d', strtotime('+3 days')) ?>" class="text-xs border border-gray-200 rounded-lg px-2 py-1.5 focus:ring-1 focus:ring-primary-500">
                </div>
                <button type="submit" class="px-4 py-1.5 text-xs font-medium bg-amber-500 text-white rounded-lg hover:bg-amber-600 flex items-center gap-1">
                    <i data-lucide="phone" class="w-3 h-3"></i> Jadwalkan
                </button>
            </form>
        </div>
    </div>

    <!-- Hapus Kasus -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="px-4 sm:px-6 py-4 flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold text-red-600">Zona Bahaya</p>
                <p class="text-xs text-secondary-500">Menghapus kasus akan menghapus semua data konseling dan panggilan terkait</p>
            </div>
            <form method="POST" action="<?= BASEURL; ?>/bk/hapusKasus" onsubmit="return confirm('Yakin ingin menghapus kasus ini beserta seluruh data terkait?')">
                <input type="hidden" name="id_kasus" value="<?= $kasus['id_kasus'] ?>">
                <button type="submit" class="px-4 py-2 text-xs font-medium bg-red-600 text-white rounded-lg hover:bg-red-700 flex items-center gap-1">
                    <i data-lucide="trash-2" class="w-3 h-3"></i> Hapus Kasus
                </button>
            </form>
        </div>
    </div>
</main>
