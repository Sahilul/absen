<?php
// File: app/views/admin/psb/pendaftar.php
// Daftar Periode PSB dengan Card View
?>
<?php $this->view('templates/header', $data); ?>

<div class="animate-fade-in">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div class="flex items-center gap-3">
            <a href="<?= BASEURL; ?>/psb/dashboard" class="p-2 hover:bg-secondary-100 rounded-lg transition-colors">
                <i data-lucide="arrow-left" class="w-5 h-5 text-secondary-500"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-secondary-800">Daftar Pendaftar</h1>
                <p class="text-secondary-500 mt-1">Pilih periode untuk melihat calon siswa</p>
            </div>
        </div>
    </div>

    <!-- Period Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if (!empty($data['periode_list'])): ?>
            <?php foreach ($data['periode_list'] as $p):
                // Get statistik per periode
                $stat = $data['statistik_per_periode'][$p['id_periode']] ?? ['total' => 0, 'pending' => 0, 'diterima' => 0];
                $isActive = ($p['status'] ?? '') === 'aktif';
                ?>
                <a href="<?= BASEURL; ?>/psb/listPendaftar/<?= $p['id_periode']; ?>"
                    class="group bg-white rounded-xl shadow-sm border overflow-hidden hover:shadow-lg transition-all duration-300 hover:-translate-y-1">

                    <!-- Header dengan warna berdasarkan jenjang -->
                    <?php
                    $jenjangColors = [
                        'SMP' => 'from-blue-500 to-blue-600',
                        'SMA' => 'from-green-500 to-green-600',
                        'SMK' => 'from-purple-500 to-purple-600',
                        'MI' => 'from-teal-500 to-teal-600',
                        'MTs' => 'from-cyan-500 to-cyan-600',
                        'MA' => 'from-indigo-500 to-indigo-600'
                    ];
                    $colorClass = $jenjangColors[$p['jenjang'] ?? ''] ?? 'from-primary-500 to-primary-600';
                    ?>
                    <div class="bg-gradient-to-r <?= $colorClass; ?> p-4 text-white">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm opacity-90"><?= htmlspecialchars($p['jenjang'] ?? ''); ?></p>
                                <h3 class="text-lg font-bold"><?= htmlspecialchars($p['nama_lembaga']); ?></h3>
                            </div>
                            <?php if ($isActive): ?>
                                <span class="bg-white/20 text-white px-2 py-1 rounded-full text-xs font-medium">Aktif</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="p-5">
                        <h4 class="font-semibold text-secondary-800 mb-2"><?= htmlspecialchars($p['nama_periode']); ?></h4>

                        <?php if (!empty($p['tanggal_mulai']) && !empty($p['tanggal_selesai'])): ?>
                            <div class="flex items-center gap-2 text-sm text-secondary-500 mb-4">
                                <i data-lucide="calendar" class="w-4 h-4"></i>
                                <?= date('d M Y', strtotime($p['tanggal_mulai'])); ?> -
                                <?= date('d M Y', strtotime($p['tanggal_selesai'])); ?>
                            </div>
                        <?php endif; ?>

                        <!-- Stats -->
                        <div class="grid grid-cols-3 gap-3 pt-4 border-t border-secondary-100">
                            <div class="text-center">
                                <p class="text-2xl font-bold text-secondary-800"><?= $stat['total'] ?? 0; ?></p>
                                <p class="text-xs text-secondary-500">Total</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold text-warning-600"><?= $stat['pending'] ?? 0; ?></p>
                                <p class="text-xs text-secondary-500">Pending</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold text-success-600"><?= $stat['diterima'] ?? 0; ?></p>
                                <p class="text-xs text-secondary-500">Diterima</p>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-5 py-3 bg-secondary-50 border-t border-secondary-100 flex items-center justify-between">
                        <span class="text-sm text-secondary-600">Lihat Pendaftar</span>
                        <i data-lucide="chevron-right"
                            class="w-5 h-5 text-secondary-400 group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="md:col-span-2 lg:col-span-3 bg-white rounded-xl shadow-sm border p-12 text-center">
                <div class="bg-secondary-100 p-4 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                    <i data-lucide="calendar-x" class="w-8 h-8 text-secondary-400"></i>
                </div>
                <h3 class="text-lg font-semibold text-secondary-700 mb-2">Belum Ada Periode</h3>
                <p class="text-secondary-500 mb-4">Silakan buat periode pendaftaran terlebih dahulu</p>
                <a href="<?= BASEURL; ?>/psb/periode" class="btn-primary inline-flex items-center gap-2">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Buat Periode
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>document.addEventListener('DOMContentLoaded', function () { lucide.createIcons(); });</script>
<?php $this->view('templates/footer'); ?>