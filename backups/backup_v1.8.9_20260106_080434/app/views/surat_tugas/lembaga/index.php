<?php
// app/views/surat_tugas/lembaga/index.php
?>
<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Kelola Lembaga</h1>
            <p class="text-gray-600">Daftar lembaga/instansi untuk kop surat</p>
        </div>
        <a href="<?= BASEURL; ?>/suratTugas/tambahLembaga"
            class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition flex items-center gap-2">
            <i data-lucide="plus" class="w-4 h-4"></i> Tambah Lembaga
        </a>
    </div>

    <?php if (class_exists('Flasher'))
        Flasher::flash(); ?>

    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="p-4 text-sm font-semibold text-gray-600">Nama Lembaga</th>
                    <th class="p-4 text-sm font-semibold text-gray-600">Kepala</th>
                    <th class="p-4 text-sm font-semibold text-gray-600">Alamat</th>
                    <th class="p-4 text-sm font-semibold text-gray-600 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php if (empty($data['lembaga_list'])): ?>
                    <tr>
                        <td colspan="4" class="p-8 text-center text-gray-500">Belum ada data lembaga.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($data['lembaga_list'] as $l): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="p-4">
                                <div class="font-bold text-gray-800"><?= $l['nama_lembaga']; ?></div>
                                <div class="text-xs text-gray-500"><?= $l['kota']; ?></div>
                            </td>
                            <td class="p-4">
                                <div class="text-sm font-medium text-gray-800"><?= $l['nama_kepala_lembaga']; ?></div>
                                <div class="text-xs text-gray-500"><?= $l['jabatan_kepala']; ?></div>
                            </td>
                            <td class="p-4 text-sm text-gray-600 truncate max-w-xs">
                                <?= $l['alamat']; ?>
                            </td>
                            <td class="p-4 text-center">
                                <div class="flex justify-center gap-2">
                                    <a href="<?= BASEURL; ?>/suratTugas/editLembaga/<?= $l['id_lembaga']; ?>"
                                        class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Edit">
                                        <i data-lucide="edit-2" class="w-4 h-4"></i>
                                    </a>
                                    <a href="<?= BASEURL; ?>/suratTugas/hapusLembaga/<?= $l['id_lembaga']; ?>"
                                        class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition"
                                        onclick="return confirm('Yakin hapus data lembaga ini? Data surat terkait juga akan terhapus.')"
                                        title="Hapus">
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