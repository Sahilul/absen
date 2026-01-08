<?php
// File: app/views/admin/psb/list_pendaftar.php
// List Pendaftar per Periode
$periode = $data['periode'];
?>
<?php $this->view('templates/header', $data); ?>

<div class="animate-fade-in">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div class="flex items-center gap-3">
            <a href="<?= BASEURL; ?>/psb/pendaftar" class="p-2 hover:bg-secondary-100 rounded-lg transition-colors">
                <i data-lucide="arrow-left" class="w-5 h-5 text-secondary-500"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-secondary-800">Calon Siswa</h1>
                <p class="text-secondary-500 mt-1">
                    <?= htmlspecialchars($periode['nama_lembaga'] . ' - ' . $periode['nama_periode']); ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Statistik -->
    <?php if ($data['statistik']): ?>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-sm border p-4 text-center">
                <p class="text-2xl font-bold text-secondary-800"><?= $data['statistik']['total'] ?? 0; ?></p>
                <p class="text-xs text-secondary-500">Total</p>
            </div>
            <div class="bg-orange-50 rounded-lg shadow-sm border border-orange-200 p-4 text-center">
                <p class="text-2xl font-bold text-orange-600"><?= $data['statistik']['revisi'] ?? 0; ?></p>
                <p class="text-xs text-orange-600">Revisi</p>
            </div>
            <div class="bg-success-50 rounded-lg shadow-sm border border-success-200 p-4 text-center">
                <p class="text-2xl font-bold text-success-600"><?= $data['statistik']['diterima'] ?? 0; ?></p>
                <p class="text-xs text-success-600">Diterima</p>
            </div>
            <div class="bg-danger-50 rounded-lg shadow-sm border border-danger-200 p-4 text-center">
                <p class="text-2xl font-bold text-danger-600"><?= $data['statistik']['ditolak'] ?? 0; ?></p>
                <p class="text-xs text-danger-600">Ditolak</p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-secondary-50">
                    <tr>
                        <th class="text-left py-4 px-6 text-sm font-semibold text-secondary-600">No. Daftar</th>
                        <th class="text-left py-4 px-6 text-sm font-semibold text-secondary-600">Nama</th>
                        <th class="text-left py-4 px-6 text-sm font-semibold text-secondary-600">Jalur</th>
                        <th class="text-left py-4 px-6 text-sm font-semibold text-secondary-600">Tanggal</th>
                        <th class="text-center py-4 px-6 text-sm font-semibold text-secondary-600">Status</th>
                        <th class="text-center py-4 px-6 text-sm font-semibold text-secondary-600">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['pendaftar'])): ?>
                        <?php foreach ($data['pendaftar'] as $p): ?>
                            <tr class="border-b border-secondary-100 hover:bg-secondary-50 transition-colors">
                                <td class="py-4 px-6">
                                    <span
                                        class="font-mono text-sm text-primary-600 font-medium"><?= htmlspecialchars($p['no_pendaftaran']); ?></span>
                                </td>
                                <td class="py-4 px-6">
                                    <p class="font-medium text-secondary-800"><?= htmlspecialchars($p['nama_lengkap']); ?></p>
                                    <p class="text-xs text-secondary-400">
                                        <?= $p['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?>
                                    </p>
                                </td>
                                <td class="py-4 px-6">
                                    <span
                                        class="bg-purple-100 text-purple-700 px-2 py-1 rounded text-xs font-medium"><?= htmlspecialchars($p['nama_jalur']); ?></span>
                                </td>
                                <td class="py-4 px-6 text-sm text-secondary-600">
                                    <?= date('d/m/Y H:i', strtotime($p['tanggal_daftar'])); ?>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <?php
                                    $statusColors = [
                                        'draft' => 'bg-gray-100 text-gray-700',
                                        'pending' => 'bg-warning-100 text-warning-700',
                                        'verifikasi' => 'bg-blue-100 text-blue-700',
                                        'revisi' => 'bg-orange-100 text-orange-700',
                                        'diterima' => 'bg-success-100 text-success-700',
                                        'ditolak' => 'bg-danger-100 text-danger-700',
                                        'daftar_ulang' => 'bg-indigo-100 text-indigo-700',
                                        'selesai' => 'bg-green-100 text-green-700'
                                    ];
                                    $color = $statusColors[$p['status']] ?? 'bg-secondary-100 text-secondary-700';
                                    ?>
                                    <span
                                        class="<?= $color; ?> px-2 py-1 rounded-full text-xs font-medium capitalize"><?= str_replace('_', ' ', $p['status']); ?></span>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <a href="<?= BASEURL; ?>/psb/detailPendaftar/<?= $p['id_pendaftar']; ?>"
                                        class="btn-primary text-sm px-3 py-1.5">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="py-12 text-center text-secondary-500">
                                <div class="flex flex-col items-center">
                                    <div class="bg-secondary-100 p-4 rounded-full mb-4">
                                        <i data-lucide="users" class="w-8 h-8 text-secondary-400"></i>
                                    </div>
                                    <p>Belum ada pendaftar di periode ini</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>document.addEventListener('DOMContentLoaded', function () { lucide.createIcons(); });</script>
<?php $this->view('templates/footer'); ?>