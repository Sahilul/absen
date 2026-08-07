<?php
// File: app/views/admin/psb/periode.php
// Kelola Periode PSB
?>
<?php $this->view('templates/header', $data); ?>

<div class="animate-fade-in">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div class="flex items-center gap-3">
            <a href="<?= BASEURL; ?>/psb/dashboard" class="p-2 hover:bg-secondary-100 rounded-lg transition-colors">
                <i data-lucide="arrow-left" class="w-5 h-5 text-secondary-500"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-secondary-800">Kelola Periode PSB</h1>
                <p class="text-secondary-500 mt-1">Atur periode penerimaan siswa baru</p>
            </div>
        </div>
        <a href="<?= BASEURL; ?>/psb/tambahPeriode" class="btn-primary flex items-center gap-2">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Tambah Periode
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-secondary-50">
                    <tr>
                        <th class="text-left py-4 px-6 text-sm font-semibold text-secondary-600">Lembaga</th>
                        <th class="text-left py-4 px-6 text-sm font-semibold text-secondary-600">Periode</th>
                        <th class="text-left py-4 px-6 text-sm font-semibold text-secondary-600">Tanggal</th>
                        <th class="text-center py-4 px-6 text-sm font-semibold text-secondary-600">Pendaftar</th>
                        <th class="text-center py-4 px-6 text-sm font-semibold text-secondary-600">Status</th>
                        <th class="text-center py-4 px-6 text-sm font-semibold text-secondary-600">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['periode'])): ?>
                        <?php foreach ($data['periode'] as $p): ?>
                            <tr class="border-b border-secondary-100 hover:bg-secondary-50 transition-colors">
                                <td class="py-4 px-6">
                                    <span
                                        class="font-medium text-secondary-800"><?= htmlspecialchars($p['nama_lembaga']); ?></span>
                                    <span
                                        class="text-xs bg-secondary-100 text-secondary-600 px-1.5 py-0.5 rounded ml-1"><?= $p['jenjang']; ?></span>
                                </td>
                                <td class="py-4 px-6">
                                    <p class="font-medium text-secondary-800"><?= htmlspecialchars($p['nama_periode']); ?></p>
                                    <p class="text-xs text-secondary-400"><?= htmlspecialchars($p['nama_tp']); ?></p>
                                </td>
                                <td class="py-4 px-6 text-sm text-secondary-600">
                                    <?= date('d/m/Y', strtotime($p['tanggal_buka'])); ?> -
                                    <?= date('d/m/Y', strtotime($p['tanggal_tutup'])); ?>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <a href="<?= BASEURL; ?>/psb/pendaftar/<?= $p['id_periode']; ?>"
                                        class="text-primary-600 hover:underline font-semibold">
                                        <?= $p['total_pendaftar']; ?> orang
                                    </a>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <?php
                                    $statusColors = [
                                        'draft' => 'bg-secondary-100 text-secondary-700',
                                        'aktif' => 'bg-success-100 text-success-700',
                                        'selesai' => 'bg-blue-100 text-blue-700'
                                    ];
                                    $color = $statusColors[$p['status']] ?? 'bg-secondary-100 text-secondary-700';
                                    ?>
                                    <span
                                        class="<?= $color; ?> px-2 py-1 rounded-full text-xs font-medium capitalize"><?= $p['status']; ?></span>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="<?= BASEURL; ?>/psb/editPeriode/<?= $p['id_periode']; ?>"
                                            class="p-2 text-primary-600 hover:bg-primary-50 rounded-lg" title="Edit">
                                            <i data-lucide="pencil" class="w-4 h-4"></i>
                                        </a>
                                        <a href="<?= BASEURL; ?>/psb/pendaftar/<?= $p['id_periode']; ?>"
                                            class="p-2 text-green-600 hover:bg-green-50 rounded-lg" title="Lihat Pendaftar">
                                            <i data-lucide="users" class="w-4 h-4"></i>
                                        </a>
                                        <button onclick="confirmDelete(<?= $p['id_periode']; ?>)"
                                            class="p-2 text-danger-600 hover:bg-danger-50 rounded-lg" title="Hapus">
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
                                        <i data-lucide="calendar" class="w-8 h-8 text-secondary-400"></i>
                                    </div>
                                    <p>Belum ada periode PSB</p>
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
    function confirmDelete(id) {
        if (confirm('Yakin ingin menghapus periode ini? Semua data pendaftar di periode ini akan ikut terhapus.')) {
            window.location.href = '<?= BASEURL; ?>/psb/hapusPeriode/' + id;
        }
    }
    document.addEventListener('DOMContentLoaded', function () { lucide.createIcons(); });
</script>

<?php $this->view('templates/footer'); ?>