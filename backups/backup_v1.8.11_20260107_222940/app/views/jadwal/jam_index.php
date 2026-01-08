<?php
// File: app/views/jadwal/jam_index.php
$jamList = $data['jam_list'] ?? [];
$pengaturan = $data['pengaturan'] ?? [];
?>

<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Jam Pelajaran</h1>
            <p class="text-gray-600">Kelola jam pelajaran dan istirahat</p>
        </div>
        <div class="flex gap-2">
            <a href="<?= BASEURL; ?>/jadwal/tambahJam"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition">
                <i data-lucide="plus" class="w-4 h-4"></i> Tambah Jam
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
                        <th class="py-3 px-6 text-left text-xs font-semibold text-gray-600 uppercase">Urutan</th>
                        <th class="py-3 px-6 text-left text-xs font-semibold text-gray-600 uppercase">Jam Ke</th>
                        <th class="py-3 px-6 text-left text-xs font-semibold text-gray-600 uppercase">Waktu</th>
                        <th class="py-3 px-6 text-left text-xs font-semibold text-gray-600 uppercase">Keterangan</th>
                        <th class="py-3 px-6 text-center text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php if (empty($jamList)): ?>
                        <tr>
                            <td colspan="5" class="py-8 text-center text-gray-500">
                                Belum ada data jam pelajaran
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($jamList as $j): ?>
                            <tr class="border-b hover:bg-gray-50 <?= $j['is_istirahat'] ? 'bg-amber-50' : ''; ?>">
                                <td class="py-4 px-6"><?= $j['urutan']; ?></td>
                                <td class="py-4 px-6">
                                    <?php if ($j['is_istirahat']): ?>
                                        <span class="text-gray-400">-</span>
                                    <?php else: ?>
                                        <span class="font-medium">Jam <?= $j['jam_ke']; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="font-mono"><?= substr($j['waktu_mulai'], 0, 5); ?></span> -
                                    <span class="font-mono"><?= substr($j['waktu_selesai'], 0, 5); ?></span>
                                </td>
                                <td class="py-4 px-6"><?= htmlspecialchars($j['keterangan'] ?? ''); ?></td>
                                <td class="py-4 px-6 text-center">
                                    <div class="flex justify-center gap-2">
                                        <a href="<?= BASEURL; ?>/jadwal/editJam/<?= $j['id_jam']; ?>"
                                            class="p-2 bg-blue-100 hover:bg-blue-200 text-blue-600 rounded-lg transition"
                                            title="Edit">
                                            <i data-lucide="edit" class="w-4 h-4"></i>
                                        </a>
                                        <button onclick="confirmDelete(<?= $j['id_jam']; ?>)"
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
        if (confirm('Yakin ingin menghapus jam pelajaran ini?')) {
            window.location.href = '<?= BASEURL; ?>/jadwal/hapusJam/' + id;
        }
    }
</script>