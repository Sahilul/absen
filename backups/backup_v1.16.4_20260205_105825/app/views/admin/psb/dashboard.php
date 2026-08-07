<?php
// File: app/views/admin/psb/dashboard.php
// Dashboard PSB Admin
?>
<?php $this->view('templates/header', $data); ?>

<div class="animate-fade-in">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-secondary-800">Dashboard PSB</h1>
            <p class="text-secondary-500 mt-1">Monitoring Penerimaan Siswa Baru</p>
        </div>
        <a href="<?= BASEURL; ?>/psb/periode" class="btn-primary flex items-center gap-2">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Kelola Periode
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Pendaftar -->
        <div class="bg-white rounded-xl p-6 shadow-sm border card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-secondary-500 text-sm font-medium">Total Pendaftar</p>
                    <p class="text-3xl font-bold text-secondary-800 mt-1">
                        <?= $data['statistik']['total_pendaftar'] ?? 0; ?></p>
                </div>
                <div class="gradient-primary p-3 rounded-xl">
                    <i data-lucide="users" class="w-6 h-6 text-white"></i>
                </div>
            </div>
            <p class="text-xs text-secondary-400 mt-3">Periode aktif</p>
        </div>

        <!-- Pending -->
        <div class="bg-white rounded-xl p-6 shadow-sm border card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-secondary-500 text-sm font-medium">Menunggu Verifikasi</p>
                    <p class="text-3xl font-bold text-warning-600 mt-1"><?= $data['statistik']['pending'] ?? 0; ?></p>
                </div>
                <div class="gradient-warning p-3 rounded-xl">
                    <i data-lucide="clock" class="w-6 h-6 text-white"></i>
                </div>
            </div>
            <p class="text-xs text-secondary-400 mt-3">Perlu diproses</p>
        </div>

        <!-- Diterima -->
        <div class="bg-white rounded-xl p-6 shadow-sm border card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-secondary-500 text-sm font-medium">Diterima</p>
                    <p class="text-3xl font-bold text-success-600 mt-1"><?= $data['statistik']['diterima'] ?? 0; ?></p>
                </div>
                <div class="gradient-success p-3 rounded-xl">
                    <i data-lucide="user-check" class="w-6 h-6 text-white"></i>
                </div>
            </div>
            <p class="text-xs text-secondary-400 mt-3">Siswa baru diterima</p>
        </div>

        <!-- Hari Ini -->
        <div class="bg-white rounded-xl p-6 shadow-sm border card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-secondary-500 text-sm font-medium">Pendaftar Hari Ini</p>
                    <p class="text-3xl font-bold text-primary-600 mt-1">
                        <?= $data['statistik']['pendaftar_hari_ini'] ?? 0; ?></p>
                </div>
                <div class="bg-primary-100 p-3 rounded-xl">
                    <i data-lucide="calendar-plus" class="w-6 h-6 text-primary-600"></i>
                </div>
            </div>
            <p class="text-xs text-secondary-400 mt-3"><?= date('d M Y'); ?></p>
        </div>
    </div>

    <!-- Quick Actions & Periode Aktif -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Quick Actions -->
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <h3 class="text-lg font-semibold text-secondary-800 mb-4 flex items-center gap-2">
                <i data-lucide="zap" class="w-5 h-5 text-warning-500"></i>
                Aksi Cepat
            </h3>
            <div class="space-y-3">
                <a href="<?= BASEURL; ?>/psb/lembaga"
                    class="flex items-center gap-3 p-3 rounded-lg hover:bg-secondary-50 transition-colors">
                    <div class="bg-blue-100 p-2 rounded-lg">
                        <i data-lucide="building" class="w-4 h-4 text-blue-600"></i>
                    </div>
                    <div>
                        <p class="font-medium text-secondary-700">Kelola Lembaga</p>
                        <p class="text-xs text-secondary-400">SD, SMP, SMA</p>
                    </div>
                </a>
                <a href="<?= BASEURL; ?>/psb/jalur"
                    class="flex items-center gap-3 p-3 rounded-lg hover:bg-secondary-50 transition-colors">
                    <div class="bg-purple-100 p-2 rounded-lg">
                        <i data-lucide="git-branch" class="w-4 h-4 text-purple-600"></i>
                    </div>
                    <div>
                        <p class="font-medium text-secondary-700">Kelola Jalur</p>
                        <p class="text-xs text-secondary-400">Reguler, Prestasi, dll</p>
                    </div>
                </a>
                <a href="<?= BASEURL; ?>/psb/pendaftar"
                    class="flex items-center gap-3 p-3 rounded-lg hover:bg-secondary-50 transition-colors">
                    <div class="bg-green-100 p-2 rounded-lg">
                        <i data-lucide="users" class="w-4 h-4 text-green-600"></i>
                    </div>
                    <div>
                        <p class="font-medium text-secondary-700">Lihat Pendaftar</p>
                        <p class="text-xs text-secondary-400">Verifikasi & kelola</p>
                    </div>
                </a>
                <a href="<?= BASEURL; ?>/psb/pengaturan"
                    class="flex items-center gap-3 p-3 rounded-lg hover:bg-secondary-50 transition-colors">
                    <div class="bg-secondary-100 p-2 rounded-lg">
                        <i data-lucide="settings" class="w-4 h-4 text-secondary-600"></i>
                    </div>
                    <div>
                        <p class="font-medium text-secondary-700">Pengaturan</p>
                        <p class="text-xs text-secondary-400">Halaman PSB publik</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Periode Aktif -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border p-6">
            <h3 class="text-lg font-semibold text-secondary-800 mb-4 flex items-center gap-2">
                <i data-lucide="calendar" class="w-5 h-5 text-primary-500"></i>
                Periode Aktif
            </h3>

            <?php if (!empty($data['periode_aktif'])): ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-secondary-200">
                                <th class="text-left py-3 px-2 text-sm font-semibold text-secondary-600">Lembaga</th>
                                <th class="text-left py-3 px-2 text-sm font-semibold text-secondary-600">Periode</th>
                                <th class="text-left py-3 px-2 text-sm font-semibold text-secondary-600">Tanggal</th>
                                <th class="text-center py-3 px-2 text-sm font-semibold text-secondary-600">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['periode_aktif'] as $periode): ?>
                                <tr class="border-b border-secondary-100 hover:bg-secondary-50">
                                    <td class="py-3 px-2">
                                        <span
                                            class="font-medium text-secondary-800"><?= htmlspecialchars($periode['nama_lembaga']); ?></span>
                                        <span class="text-xs text-secondary-400 ml-1">(<?= $periode['jenjang']; ?>)</span>
                                    </td>
                                    <td class="py-3 px-2 text-secondary-600"><?= htmlspecialchars($periode['nama_periode']); ?>
                                    </td>
                                    <td class="py-3 px-2 text-sm text-secondary-500">
                                        <?= date('d/m/Y', strtotime($periode['tanggal_buka'])); ?> -
                                        <?= date('d/m/Y', strtotime($periode['tanggal_tutup'])); ?>
                                    </td>
                                    <td class="py-3 px-2 text-center">
                                        <a href="<?= BASEURL; ?>/psb/pendaftar/<?= $periode['id_periode']; ?>"
                                            class="text-primary-600 hover:text-primary-700 text-sm font-medium">
                                            Lihat Pendaftar
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <div class="bg-secondary-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="calendar-x" class="w-8 h-8 text-secondary-400"></i>
                    </div>
                    <p class="text-secondary-500 mb-4">Belum ada periode PSB yang aktif</p>
                    <a href="<?= BASEURL; ?>/psb/tambahPeriode" class="btn-primary inline-flex items-center gap-2">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        Buat Periode Baru
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>

<?php $this->view('templates/footer'); ?>