<!-- admin/soal_sts/index.php - Dashboard Soal STS -->
<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
    <!-- Flash Message -->
    <?php Flasher::flash(); ?>

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 space-y-4 sm:space-y-0">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Soal STS</h2>
            <p class="text-gray-600 mt-1">Kelola soal Sumatif Tengah Semester — <strong><?= $_SESSION['nama_semester_aktif'] ?? 'Belum ada sesi'; ?></strong></p>
        </div>
        <a href="<?= BASEURL; ?>/soalSts/bulkPengaturan"
           class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg flex items-center transition-colors duration-200 shadow-sm">
            <i data-lucide="layers" class="w-4 h-4 mr-2"></i>
            Bulk Pengaturan
        </a>
    </div>

    <!-- Stats Cards -->
    <?php
    $totalKelas = count($data['kelas_list']);
    $totalLengkap = 0;
    $totalSebagian = 0;
    $totalBelum = 0;
    foreach ($data['kelas_list'] as $k) {
        if ($k['total_mapel_soal'] > 0 && $k['mapel_lengkap'] == $k['total_mapel_soal']) {
            $totalLengkap++;
        } elseif ($k['mapel_sebagian'] > 0 || $k['mapel_lengkap'] > 0) {
            $totalSebagian++;
        } else {
            $totalBelum++;
        }
    }
    ?>
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-4 rounded-lg shadow-sm border">
            <div class="flex items-center">
                <div class="bg-blue-100 p-2 rounded-lg mr-3">
                    <i data-lucide="school" class="w-5 h-5 text-blue-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Total Kelas</p>
                    <p class="text-xl font-semibold text-gray-900"><?= $totalKelas; ?></p>
                    <p class="text-xs text-gray-500">Sesi aktif</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm border">
            <div class="flex items-center">
                <div class="bg-green-100 p-2 rounded-lg mr-3">
                    <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Soal Lengkap</p>
                    <p class="text-xl font-semibold text-green-700"><?= $totalLengkap; ?></p>
                    <p class="text-xs text-gray-500">Kelas</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm border">
            <div class="flex items-center">
                <div class="bg-yellow-100 p-2 rounded-lg mr-3">
                    <i data-lucide="clock" class="w-5 h-5 text-yellow-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Sebagian</p>
                    <p class="text-xl font-semibold text-yellow-700"><?= $totalSebagian; ?></p>
                    <p class="text-xs text-gray-500">Kelas</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm border">
            <div class="flex items-center">
                <div class="bg-red-100 p-2 rounded-lg mr-3">
                    <i data-lucide="alert-circle" class="w-5 h-5 text-red-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Belum Ada</p>
                    <p class="text-xl font-semibold text-red-700"><?= $totalBelum; ?></p>
                    <p class="text-xs text-gray-500">Kelas</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Kelas Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php if (empty($data['kelas_list'])): ?>
            <div class="col-span-full text-center py-12">
                <i data-lucide="inbox" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
                <p class="text-gray-500">Belum ada kelas di tahun pelajaran aktif</p>
            </div>
        <?php else: ?>
            <?php foreach ($data['kelas_list'] as $kelas): ?>
                <?php
                $totalMapel = $kelas['total_mapel'];
                $totalPengaturan = $kelas['total_mapel_soal'];
                $mapelLengkap = $kelas['mapel_lengkap'];
                $mapelSebagian = $kelas['mapel_sebagian'];

                // Status badge
                if ($totalPengaturan > 0 && $mapelLengkap == $totalPengaturan) {
                    $statusColor = 'bg-green-100 text-green-700 border-green-200';
                    $statusText = 'Lengkap';
                    $statusIcon = 'check-circle';
                    $cardBorder = 'border-green-200';
                } elseif ($mapelSebagian > 0 || $mapelLengkap > 0) {
                    $statusColor = 'bg-yellow-100 text-yellow-700 border-yellow-200';
                    $statusText = 'Sebagian';
                    $statusIcon = 'clock';
                    $cardBorder = 'border-yellow-200';
                } else {
                    $statusColor = 'bg-gray-100 text-gray-500 border-gray-200';
                    $statusText = 'Belum Ada';
                    $statusIcon = 'minus-circle';
                    $cardBorder = 'border-gray-200';
                }

                // Progress
                $progress = $totalPengaturan > 0 ? round(($mapelLengkap / $totalPengaturan) * 100) : 0;
                ?>
                <div class="bg-white rounded-xl shadow-sm border <?= $cardBorder; ?> hover:shadow-md transition-shadow duration-200">
                    <div class="p-5">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-lg font-semibold text-gray-800"><?= htmlspecialchars($kelas['nama_kelas']); ?></h3>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusColor; ?>">
                                <i data-lucide="<?= $statusIcon; ?>" class="w-3 h-3 mr-1"></i>
                                <?= $statusText; ?>
                            </span>
                        </div>

                        <div class="space-y-2 text-sm text-gray-600 mb-4">
                            <div class="flex justify-between">
                                <span>Total Mapel</span>
                                <span class="font-medium text-gray-800"><?= $totalMapel; ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span>Pengaturan Soal</span>
                                <span class="font-medium text-gray-800"><?= $totalPengaturan; ?> / <?= $totalMapel; ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span>Soal Lengkap</span>
                                <span class="font-medium text-green-600"><?= $mapelLengkap; ?> mapel</span>
                            </div>
                        </div>

                        <?php if ($totalPengaturan > 0): ?>
                            <div class="mb-4">
                                <div class="flex justify-between text-xs text-gray-500 mb-1">
                                    <span>Progress</span>
                                    <span><?= $progress; ?>%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-500 h-2 rounded-full transition-all duration-300" style="width: <?= $progress; ?>%"></div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="flex gap-2">
                            <a href="<?= BASEURL; ?>/soalSts/pengaturan/<?= $kelas['id_kelas']; ?>"
                               class="flex-1 text-center bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-sm font-medium py-2 px-3 rounded-lg transition-colors duration-200">
                                <i data-lucide="settings" class="w-4 h-4 inline mr-1"></i>
                                Pengaturan
                            </a>
                            <?php if ($totalPengaturan > 0): ?>
                                <a href="<?= BASEURL; ?>/soalSts/pengaturan/<?= $kelas['id_kelas']; ?>#daftar"
                                   class="flex-1 text-center bg-violet-50 hover:bg-violet-100 text-violet-700 text-sm font-medium py-2 px-3 rounded-lg transition-colors duration-200">
                                    <i data-lucide="list" class="w-4 h-4 inline mr-1"></i>
                                    Lihat Soal
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>
