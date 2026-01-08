<?php
// File: app/views/admin/psb/jalur.php
// Kelola Jalur Pendaftaran PSB
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
                <h1 class="text-2xl font-bold text-secondary-800">Kelola Jalur Pendaftaran</h1>
                <p class="text-secondary-500 mt-1">Jalur masuk yang tersedia untuk calon siswa</p>
            </div>
        </div>
        <a href="<?= BASEURL; ?>/psb/tambahJalur" class="btn-primary flex items-center gap-2">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Tambah Jalur
        </a>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-secondary-50">
                    <tr>
                        <th class="text-left py-4 px-6 text-sm font-semibold text-secondary-600">Kode</th>
                        <th class="text-left py-4 px-6 text-sm font-semibold text-secondary-600">Nama Jalur</th>
                        <th class="text-left py-4 px-6 text-sm font-semibold text-secondary-600">Deskripsi</th>
                        <th class="text-center py-4 px-6 text-sm font-semibold text-secondary-600">Status</th>
                        <th class="text-center py-4 px-6 text-sm font-semibold text-secondary-600">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['jalur'])): ?>
                        <?php foreach ($data['jalur'] as $item): ?>
                            <tr class="border-b border-secondary-100 hover:bg-secondary-50 transition-colors">
                                <td class="py-4 px-6">
                                    <span class="bg-purple-100 text-purple-700 px-2 py-1 rounded text-sm font-mono font-medium">
                                        <?= htmlspecialchars($item['kode_jalur']); ?>
                                    </span>
                                </td>
                                <td class="py-4 px-6 font-medium text-secondary-800">
                                    <?= htmlspecialchars($item['nama_jalur']); ?></td>
                                <td class="py-4 px-6 text-secondary-600 text-sm">
                                    <?= htmlspecialchars($item['deskripsi'] ?? '-'); ?></td>
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
                                        <a href="<?= BASEURL; ?>/psb/editJalur/<?= $item['id_jalur']; ?>"
                                            class="p-2 text-primary-600 hover:bg-primary-50 rounded-lg transition-colors"
                                            title="Edit">
                                            <i data-lucide="pencil" class="w-4 h-4"></i>
                                        </a>
                                        <button
                                            onclick="confirmDelete(<?= $item['id_jalur']; ?>, '<?= htmlspecialchars($item['nama_jalur']); ?>')"
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
                            <td colspan="5" class="py-12 text-center text-secondary-500">
                                <div class="flex flex-col items-center">
                                    <div class="bg-secondary-100 p-4 rounded-full mb-4">
                                        <i data-lucide="git-branch" class="w-8 h-8 text-secondary-400"></i>
                                    </div>
                                    <p>Belum ada data jalur pendaftaran</p>
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
        if (confirm('Yakin ingin menghapus jalur "' + nama + '"?')) {
            window.location.href = '<?= BASEURL; ?>/psb/hapusJalur/' + id;
        }
    }
    document.addEventListener('DOMContentLoaded', function () { lucide.createIcons(); });
</script>

<?php $this->view('templates/footer'); ?>