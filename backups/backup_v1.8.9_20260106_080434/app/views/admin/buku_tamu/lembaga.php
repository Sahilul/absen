<?php
$lembaga_list = $data['lembaga_list'] ?? [];
?>
<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-4 sm:p-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Kelola Lembaga</h2>
            <p class="text-gray-600 mt-1">Unit yang bisa menerima tamu</p>
        </div>
        <div class="flex gap-2">
            <a href="<?= BASEURL ?>/bukuTamu"
                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl text-gray-700 flex items-center gap-2">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali
            </a>
            <button onclick="openModal()"
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i> Tambah
            </button>
        </div>
    </div>

    <?php Flasher::flash(); ?>

    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <?php if (empty($lembaga_list)): ?>
            <div class="p-12 text-center text-gray-500">
                <p>Belum ada lembaga</p>
            </div>
        <?php else: ?>
            <!-- Mobile Card View -->
            <div class="md:hidden divide-y">
                <?php foreach ($lembaga_list as $lb): ?>
                    <div class="p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="font-semibold text-gray-900"><?= htmlspecialchars($lb['nama_lembaga']) ?></div>
                                <code
                                    class="text-xs bg-gray-100 px-2 py-0.5 rounded"><?= htmlspecialchars($lb['kode_lembaga'] ?? '-') ?></code>
                            </div>
                            <?php if ($lb['is_active']): ?>
                                <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs">Aktif</span>
                            <?php else: ?>
                                <span class="px-2 py-1 bg-gray-100 text-gray-500 rounded-full text-xs">Nonaktif</span>
                            <?php endif; ?>
                        </div>
                        <div class="flex items-center gap-2 mt-3 pt-3 border-t">
                            <button onclick='editLembaga(<?= json_encode($lb) ?>)'
                                class="flex-1 py-2 text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg text-sm font-medium">Edit</button>
                            <a href="<?= BASEURL ?>/bukuTamu/toggleLembaga/<?= $lb['id'] ?>"
                                class="flex-1 py-2 text-amber-600 bg-amber-50 hover:bg-amber-100 rounded-lg text-sm font-medium text-center">Toggle</a>
                            <a href="<?= BASEURL ?>/bukuTamu/hapusLembaga/<?= $lb['id'] ?>"
                                class="py-2 px-3 text-red-600 bg-red-50 hover:bg-red-100 rounded-lg"
                                onclick="return confirm('Hapus lembaga ini?')">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Desktop Table View -->
            <div class="hidden md:block">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">Nama Lembaga</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">Kode</th>
                            <th class="px-4 py-3 text-center font-medium text-gray-600">Status</th>
                            <th class="px-4 py-3 text-center font-medium text-gray-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php foreach ($lembaga_list as $lb): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium"><?= htmlspecialchars($lb['nama_lembaga']) ?></td>
                                <td class="px-4 py-3"><code
                                        class="bg-gray-100 px-2 py-0.5 rounded"><?= htmlspecialchars($lb['kode_lembaga'] ?? '-') ?></code>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <?php if ($lb['is_active']): ?>
                                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs">Aktif</span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 bg-gray-100 text-gray-500 rounded-full text-xs">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button onclick='editLembaga(<?= json_encode($lb) ?>)'
                                            class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg">
                                            <i data-lucide="edit-2" class="w-4 h-4"></i>
                                        </button>
                                        <a href="<?= BASEURL ?>/bukuTamu/toggleLembaga/<?= $lb['id'] ?>"
                                            class="p-2 text-amber-600 hover:bg-amber-50 rounded-lg">
                                            <i data-lucide="power" class="w-4 h-4"></i>
                                        </a>
                                        <a href="<?= BASEURL ?>/bukuTamu/hapusLembaga/<?= $lb['id'] ?>"
                                            class="p-2 text-red-600 hover:bg-red-50 rounded-lg"
                                            onclick="return confirm('Hapus lembaga ini?')">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </a>
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

<!-- Modal -->
<div id="lembagaModal" class="fixed inset-0 bg-black/50 z-[9999] hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <h3 id="modalTitle" class="font-bold text-lg">Tambah Lembaga</h3>
            <button onclick="closeModal()" class="p-2 hover:bg-gray-100 rounded-lg"><i data-lucide="x"
                    class="w-5 h-5"></i></button>
        </div>
        <form action="<?= BASEURL ?>/bukuTamu/prosesLembaga" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="id" id="formId">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lembaga *</label>
                <input type="text" name="nama_lembaga" id="formNama" required
                    class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kode</label>
                <input type="text" name="kode_lembaga" id="formKode"
                    class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500"
                    placeholder="contoh: TK, SD, SMP">
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" id="formAktif" checked class="rounded">
                <label for="formAktif" class="text-sm text-gray-700">Aktif</label>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeModal()"
                    class="flex-1 py-2 border rounded-xl hover:bg-gray-50">Batal</button>
                <button type="submit"
                    class="flex-1 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal() {
        document.getElementById('modalTitle').textContent = 'Tambah Lembaga';
        document.getElementById('formId').value = '';
        document.getElementById('formNama').value = '';
        document.getElementById('formKode').value = '';
        document.getElementById('formAktif').checked = true;
        document.getElementById('lembagaModal').classList.remove('hidden');
        document.getElementById('lembagaModal').classList.add('flex');
    }
    function editLembaga(lb) {
        document.getElementById('modalTitle').textContent = 'Edit Lembaga';
        document.getElementById('formId').value = lb.id;
        document.getElementById('formNama').value = lb.nama_lembaga;
        document.getElementById('formKode').value = lb.kode_lembaga || '';
        document.getElementById('formAktif').checked = lb.is_active == 1;
        document.getElementById('lembagaModal').classList.remove('hidden');
        document.getElementById('lembagaModal').classList.add('flex');
    }
    function closeModal() {
        document.getElementById('lembagaModal').classList.add('hidden');
        document.getElementById('lembagaModal').classList.remove('flex');
    }
    document.getElementById('lembagaModal').addEventListener('click', e => { if (e.target.id === 'lembagaModal') closeModal(); });
    document.addEventListener('DOMContentLoaded', () => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
</script>