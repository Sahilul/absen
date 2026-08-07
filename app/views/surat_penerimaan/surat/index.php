<?php
// app/views/surat_penerimaan/surat/index.php
$idLembaga = $data['filter_lembaga'];
?>
<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Daftar Surat Penerimaan</h1>
            <p class="text-gray-600">Kelola dan cetak surat keterangan penerimaan siswa pindahan</p>
        </div>
        <a href="<?= BASEURL; ?>/suratPenerimaan/inputSurat"
            class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 transition flex items-center gap-2">
            <i data-lucide="plus" class="w-4 h-4"></i> Buat Surat Baru
        </a>
    </div>

    <!-- Filter Lembaga -->
    <div class="bg-white rounded-xl shadow-sm border p-4 mb-6">
        <form action="" method="GET" class="flex items-end gap-4">
            <div class="flex-1 max-w-sm">
                <label class="block text-sm font-medium text-gray-700 mb-1">Filter Lembaga</label>
                <select name="lembaga"
                    class="w-full rounded-lg border-gray-300 focus:ring-emerald-500 focus:border-emerald-500"
                    onchange="this.form.submit()">
                    <option value="">Semua Lembaga</option>
                    <?php foreach ($data['lembaga_list'] as $l): ?>
                        <option value="<?= $l['id_lembaga']; ?>" <?= ($idLembaga == $l['id_lembaga']) ? 'selected' : ''; ?>>
                            <?= $l['nama_lembaga']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($idLembaga): ?>
                <a href="<?= BASEURL; ?>/suratPenerimaan/surat"
                    class="px-4 py-2 text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition whitespace-nowrap mb-0.5">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (class_exists('Flasher'))
        Flasher::flash(); ?>

    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="p-4 text-sm font-semibold text-gray-600">Nomor/Siswa</th>
                    <th class="p-4 text-sm font-semibold text-gray-600">Lembaga/Asal Sekolah</th>
                    <th class="p-4 text-sm font-semibold text-gray-600">Tanggal Surat</th>
                    <th class="p-4 text-sm font-semibold text-gray-600 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php if (empty($data['surat_list'])): ?>
                    <tr>
                        <td colspan="4" class="p-8 text-center text-gray-500">
                            Belum ada surat penerimaan siswa<?= $idLembaga ? ' di lembaga ini' : ''; ?>.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($data['surat_list'] as $s): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="p-4 align-top">
                                <div class="font-bold text-gray-800 text-sm mb-1"><?= htmlspecialchars($s['nomor_surat']); ?></div>
                                <div class="text-sm text-gray-600"><?= htmlspecialchars($s['nama_siswa']); ?></div>
                            </td>
                            <td class="p-4 align-top">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800 mb-1">
                                    <?= htmlspecialchars($s['nama_lembaga']); ?>
                                </span>
                                <div class="text-sm text-gray-800 mt-1"><?= htmlspecialchars($s['asal_sekolah'] ?: '-'); ?></div>
                            </td>
                            <td class="p-4 align-top text-sm text-gray-600 whitespace-nowrap">
                                <?= date('d/m/Y', strtotime($s['tanggal_surat'])); ?>
                            </td>
                            <td class="p-4 align-top text-center">
                                <div class="flex justify-center gap-1">
                                    <a href="<?= BASEURL; ?>/suratPenerimaan/cetak/<?= $s['id_surat']; ?>" target="_blank"
                                        class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg transition"
                                        title="Download PDF" download>
                                        <i data-lucide="download" class="w-4 h-4"></i>
                                    </a>
                                    <a href="<?= BASEURL; ?>/suratPenerimaan/inputSurat/<?= $s['id_surat']; ?>"
                                        class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Edit">
                                        <i data-lucide="edit-2" class="w-4 h-4"></i>
                                    </a>
                                    <a href="<?= BASEURL; ?>/suratPenerimaan/hapusSurat/<?= $s['id_surat']; ?>"
                                        class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition"
                                        onclick="return confirm('Yakin hapus surat ini?')" title="Hapus">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
