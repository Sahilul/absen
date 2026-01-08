<?php
// File: app/views/admin/psb/lembaga.php
// Kelola Lembaga PSB
?>
<?php $this->view('templates/header', $data); ?>

<div class="animate-fade-in">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div class="flex items-center gap-3">
            <a href="<?= BASEURL; ?>/psb/dashboard" class="p-2 hover:bg-secondary-100 rounded-lg transition-colors">
                <i data-lucide="arrow-left" class="w-5 h-5 text-secondary-500"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-secondary-800">Kelola Lembaga</h1>
                <p class="text-secondary-500 mt-1">Daftar unit pendidikan yang tersedia untuk PSB</p>
            </div>
        </div>
        <a href="<?= BASEURL; ?>/psb/tambahLembaga" class="btn-primary flex items-center gap-2">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Tambah Lembaga
        </a>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-secondary-50">
                    <tr>
                        <th class="text-left py-4 px-6 text-sm font-semibold text-secondary-600">Kode</th>
                        <th class="text-left py-4 px-6 text-sm font-semibold text-secondary-600">Nama Lembaga</th>
                        <th class="text-left py-4 px-6 text-sm font-semibold text-secondary-600">Jenjang</th>
                        <th class="text-center py-4 px-6 text-sm font-semibold text-secondary-600">Kuota Default</th>
                        <th class="text-center py-4 px-6 text-sm font-semibold text-secondary-600">Status</th>
                        <th class="text-center py-4 px-6 text-sm font-semibold text-secondary-600">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['lembaga'])): ?>
                        <?php foreach ($data['lembaga'] as $item): ?>
                            <tr class="border-b border-secondary-100 hover:bg-secondary-50 transition-colors">
                                <td class="py-4 px-6">
                                    <span
                                        class="bg-primary-100 text-primary-700 px-2 py-1 rounded text-sm font-mono font-medium">
                                        <?= htmlspecialchars($item['kode_lembaga']); ?>
                                    </span>
                                </td>
                                <td class="py-4 px-6 font-medium text-secondary-800">
                                    <?= htmlspecialchars($item['nama_lembaga']); ?></td>
                                <td class="py-4 px-6">
                                    <span class="bg-secondary-100 text-secondary-700 px-2 py-1 rounded text-sm">
                                        <?= htmlspecialchars($item['jenjang']); ?>
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-center text-secondary-600"><?= $item['kuota_default']; ?></td>
                                <td class="py-4 px-6 text-center">
                                    <?php if ($item['aktif']): ?>
                                        <span
                                            class="bg-success-100 text-success-700 px-2 py-1 rounded-full text-xs font-medium">Aktif</span>
                                    <?php else: ?>
                                        <span
                                            class="bg-secondary-100 text-secondary-500 px-2 py-1 rounded-full text-xs font-medium">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="<?= BASEURL; ?>/psb/editLembaga/<?= $item['id_lembaga']; ?>"
                                            class="p-2 text-primary-600 hover:bg-primary-50 rounded-lg transition-colors"
                                            title="Edit">
                                            <i data-lucide="pencil" class="w-4 h-4"></i>
                                        </a>
                                        <button
                                            onclick="confirmDelete(<?= $item['id_lembaga']; ?>, '<?= htmlspecialchars($item['nama_lembaga']); ?>')"
                                            class="p-2 text-danger-600 hover:bg-danger-50 rounded-lg transition-colors"
                                            title="Hapus">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="py-12 text-center text-secondary-500">
                                <div class="flex flex-col items-center">
                                    <div class="bg-secondary-100 p-4 rounded-full mb-4">
                                        <i data-lucide="building" class="w-8 h-8 text-secondary-400"></i>
                                    </div>
                                    <p>Belum ada data lembaga</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function confirmDelete(id, nama) {
        if (confirm('Yakin ingin menghapus lembaga "' + nama + '"?')) {
            window.location.href = '<?= BASEURL; ?>/psb/hapusLembaga/' + id;
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>

<?php $this->view('templates/footer'); ?>