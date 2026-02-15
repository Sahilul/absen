<?php
// File: app/views/surat_tugas/index.php
$suratList = $data['surat_list'] ?? [];
$tp = $data['tp_aktif'] ?? null;
?>

<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Surat Tugas</h1>
            <p class="text-gray-600"><?= $tp ? htmlspecialchars($tp['nama_tp']) : ''; ?></p>
        </div>
        <div>
            <a href="<?= BASEURL; ?>/admin/tambahSuratTugas"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i> Buat Surat
            </a>
        </div>
    </div>

    <?php if (class_exists('Flasher'))
        Flasher::flash(); ?>

    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase">No</th>
                        <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase">Nomor Surat</th>
                        <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase">Nama Guru</th>
                        <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase">Perihal</th>
                        <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase">Tanggal</th>
                        <th class="py-3 px-4 text-center text-xs font-semibold text-gray-600 uppercase">Status</th>
                        <th class="py-3 px-4 text-center text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php if (empty($suratList)): ?>
                        <tr>
                            <td colspan="7" class="py-8 text-center text-gray-500">
                                Belum ada surat tugas
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1;
                        foreach ($suratList as $s): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 px-4"><?= $no++; ?></td>
                                <td class="py-3 px-4 font-mono text-sm"><?= htmlspecialchars($s['nomor_surat']); ?></td>
                                <td class="py-3 px-4"><?= htmlspecialchars($s['nama_guru']); ?></td>
                                <td class="py-3 px-4"><?= htmlspecialchars($s['perihal']); ?></td>
                                <td class="py-3 px-4"><?= date('d/m/Y', strtotime($s['tanggal_surat'])); ?></td>
                                <td class="py-3 px-4 text-center">
                                    <?php
                                    $statusClass = match ($s['status']) {
                                        'terbit' => 'bg-green-100 text-green-700',
                                        'selesai' => 'bg-blue-100 text-blue-700',
                                        default => 'bg-gray-100 text-gray-700'
                                    };
                                    ?>
                                    <span class="px-2 py-1 rounded text-xs font-medium <?= $statusClass; ?>">
                                        <?= ucfirst($s['status']); ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <div class="flex justify-center gap-1">
                                        <a href="<?= BASEURL; ?>/admin/cetakSuratTugas/<?= $s['id_surat']; ?>" target="_blank"
                                            class="p-2 bg-indigo-100 hover:bg-indigo-200 text-indigo-600 rounded-lg transition"
                                            title="Cetak">
                                            <i data-lucide="printer" class="w-4 h-4"></i>
                                        </a>
                                        <a href="<?= BASEURL; ?>/admin/editSuratTugas/<?= $s['id_surat']; ?>"
                                            class="p-2 bg-blue-100 hover:bg-blue-200 text-blue-600 rounded-lg transition"
                                            title="Edit">
                                            <i data-lucide="edit" class="w-4 h-4"></i>
                                        </a>
                                        <button onclick="confirmDelete(<?= $s['id_surat']; ?>)"
                                            class="p-2 bg-red-100 hover:bg-red-200 text-red-600 rounded-lg transition"
                                            title="Hapus">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
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
    function confirmDelete(id) {
        if (confirm('Yakin ingin menghapus surat tugas ini?')) {
            window.location.href = '<?= BASEURL; ?>/admin/hapusSuratTugas/' + id;
        }
    }
</script>