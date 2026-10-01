<!-- admin/soal_sts/daftar_soal.php - Daftar Soal per Mapel -->
<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
    <!-- Flash Message -->
    <?php Flasher::flash(); ?>

    <?php
    $p = $data['pengaturan'];
    $kelas = $data['kelas'];
    $soalList = $data['soal_list'];
    $isAdmin = ($_SESSION['role'] ?? '') === 'admin';
    $backUrl = $isAdmin ? BASEURL . '/soalSts/pengaturan/' . $kelas['id_kelas'] : BASEURL . '/guru';
    ?>

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 space-y-4 sm:space-y-0">
        <div>
            <nav class="flex items-center text-sm text-gray-500 mb-2">
                <?php if ($isAdmin): ?>
                    <a href="<?= BASEURL; ?>/soalSts" class="hover:text-indigo-600">Soal STS</a>
                    <i data-lucide="chevron-right" class="w-4 h-4 mx-1"></i>
                    <a href="<?= BASEURL; ?>/soalSts/pengaturan/<?= $kelas['id_kelas']; ?>" class="hover:text-indigo-600">Pengaturan</a>
                <?php else: ?>
                    <a href="<?= BASEURL; ?>/guru" class="hover:text-indigo-600">Dashboard</a>
                <?php endif; ?>
                <i data-lucide="chevron-right" class="w-4 h-4 mx-1"></i>
                <span class="text-gray-700 font-medium">Daftar Soal</span>
            </nav>
            <h2 class="text-2xl font-bold text-gray-800">Daftar Soal — <?= htmlspecialchars($p['nama_mapel']); ?></h2>
            <p class="text-gray-600 mt-1">Kelas <?= htmlspecialchars($kelas['nama_kelas']); ?></p>
        </div>
        <div class="flex gap-2">
            <a href="<?= BASEURL; ?>/soalSts/inputSoal/<?= $p['id']; ?>"
               class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg flex items-center transition-colors duration-200 shadow-sm">
                <i data-lucide="edit" class="w-4 h-4 mr-2"></i>
                Edit Soal
            </a>
            <a href="<?= BASEURL; ?>/soalSts/previewCetak/<?= $kelas['id_kelas']; ?>/<?= $p['id_mapel']; ?>"
               class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2 px-4 rounded-lg flex items-center transition-colors duration-200 shadow-sm">
                <i data-lucide="printer" class="w-4 h-4 mr-2"></i>
                Cetak
            </a>
            <a href="<?= $backUrl; ?>"
               class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg flex items-center transition-colors duration-200">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                Kembali
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-4 rounded-lg shadow-sm border">
            <p class="text-sm text-gray-600">Total Soal</p>
            <p class="text-xl font-semibold text-gray-900"><?= count($soalList); ?></p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm border">
            <p class="text-sm text-gray-600">Pilihan Ganda</p>
            <p class="text-xl font-semibold text-blue-600"><?= $data['total_pg']; ?> / <?= $p['jumlah_pilihan_ganda']; ?></p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm border">
            <p class="text-sm text-gray-600">Isian Singkat</p>
            <p class="text-xl font-semibold text-teal-600"><?= $data['total_isian']; ?> / <?= $p['jumlah_isian_singkat']; ?></p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm border">
            <p class="text-sm text-gray-600">Essay</p>
            <p class="text-xl font-semibold text-purple-600"><?= $data['total_essay']; ?> / <?= $p['jumlah_essay']; ?></p>
        </div>
    </div>

    <!-- Soal List -->
    <?php if (empty($soalList)): ?>
        <div class="bg-white rounded-xl shadow-sm border p-12 text-center">
            <i data-lucide="file-question" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
            <p class="text-gray-500 mb-2">Belum ada soal yang diinput</p>
            <a href="<?= BASEURL; ?>/soalSts/inputSoal/<?= $p['id']; ?>"
               class="inline-flex items-center text-indigo-600 hover:text-indigo-800 font-medium text-sm">
                <i data-lucide="plus" class="w-4 h-4 mr-1"></i>
                Mulai Input Soal
            </a>
        </div>
    <?php else: ?>
        <?php
        // Group by tipe
        $grouped = ['pg' => [], 'isian' => [], 'essay' => []];
        foreach ($soalList as $s) {
            $grouped[$s['tipe_soal']][] = $s;
        }
        // Nomor bagian dinamis
        $sectionNum = 0;
        $romanNums = ['', 'I', 'II', 'III'];
        ?>

        <!-- Pilihan Ganda -->
        <?php if (!empty($grouped['pg'])): ?>
            <?php $sectionNum++; ?>
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
                    <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm mr-2"><?= $romanNums[$sectionNum]; ?></span>
                    Pilihan Ganda
                </h3>
                <div class="bg-white rounded-xl shadow-sm border divide-y">
                    <?php foreach ($grouped['pg'] as $soal): ?>
                        <div class="p-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-start gap-3">
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-blue-100 text-blue-700 text-sm font-bold flex-shrink-0 mt-0.5"><?= $soal['nomor_soal']; ?></span>
                                        <div class="flex-1">
                                            <p class="text-gray-800 whitespace-pre-line"><?= htmlspecialchars($soal['pertanyaan']); ?></p>
                                            <?php if (!empty($soal['gambar_soal'])): ?>
                                                <img src="<?= soalGambarUrl($soal['gambar_soal']); ?>"
                                                     class="max-h-24 rounded border mt-2" alt="Gambar soal">
                                            <?php endif; ?>
                                            <div class="mt-2 grid grid-cols-2 gap-1 text-sm">
                                                <?php foreach (['A' => 'opsi_a', 'B' => 'opsi_b', 'C' => 'opsi_c', 'D' => 'opsi_d', 'E' => 'opsi_e'] as $label => $field): ?>
                                                    <?php if (!empty($soal[$field])): ?>
                                                        <span class="<?= ($soal['kunci_jawaban'] ?? '') === $label ? 'text-green-700 font-semibold bg-green-50 px-2 py-0.5 rounded' : 'text-gray-600'; ?>">
                                                            <?= $label; ?>. <?= htmlspecialchars($soal[$field]); ?>
                                                            <?= ($soal['kunci_jawaban'] ?? '') === $label ? ' ✓' : ''; ?>
                                                        </span>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 ml-4">
                                    <span class="text-xs text-gray-400">Skor: <?= $soal['skor']; ?></span>
                                    <a href="<?= BASEURL; ?>/soalSts/hapusSoal/<?= $soal['id_soal']; ?>"
                                       onclick="return confirm('Hapus soal ini?')"
                                       class="text-red-400 hover:text-red-600 transition-colors">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Isian Singkat -->
        <?php if (!empty($grouped['isian'])): ?>
            <?php $sectionNum++; ?>
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
                    <span class="bg-teal-100 text-teal-700 px-3 py-1 rounded-full text-sm mr-2"><?= $romanNums[$sectionNum]; ?></span>
                    Isian Singkat
                </h3>
                <div class="bg-white rounded-xl shadow-sm border divide-y">
                    <?php foreach ($grouped['isian'] as $soal): ?>
                        <div class="p-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-start gap-3">
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-teal-100 text-teal-700 text-sm font-bold flex-shrink-0 mt-0.5"><?= $soal['nomor_soal']; ?></span>
                                        <div class="flex-1">
                                            <p class="text-gray-800 whitespace-pre-line"><?= htmlspecialchars($soal['pertanyaan']); ?></p>
                                            <?php if (!empty($soal['gambar_soal'])): ?>
                                                <img src="<?= soalGambarUrl($soal['gambar_soal']); ?>"
                                                     class="max-h-24 rounded border mt-2" alt="Gambar soal">
                                            <?php endif; ?>
                                            <?php if (!empty($soal['kunci_jawaban'])): ?>
                                                <p class="mt-2 text-sm text-green-700 bg-green-50 px-3 py-1 rounded">
                                                    <strong>Jawaban:</strong> <?= htmlspecialchars($soal['kunci_jawaban']); ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 ml-4">
                                    <span class="text-xs text-gray-400">Skor: <?= $soal['skor']; ?></span>
                                    <a href="<?= BASEURL; ?>/soalSts/hapusSoal/<?= $soal['id_soal']; ?>"
                                       onclick="return confirm('Hapus soal ini?')"
                                       class="text-red-400 hover:text-red-600 transition-colors">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Essay -->
        <?php if (!empty($grouped['essay'])): ?>
            <?php $sectionNum++; ?>
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
                    <span class="bg-purple-100 text-purple-700 px-3 py-1 rounded-full text-sm mr-2"><?= $romanNums[$sectionNum]; ?></span>
                    Essay
                </h3>
                <div class="bg-white rounded-xl shadow-sm border divide-y">
                    <?php foreach ($grouped['essay'] as $soal): ?>
                        <div class="p-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-start gap-3">
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-purple-100 text-purple-700 text-sm font-bold flex-shrink-0 mt-0.5"><?= $soal['nomor_soal']; ?></span>
                                        <div class="flex-1">
                                            <p class="text-gray-800 whitespace-pre-line"><?= htmlspecialchars($soal['pertanyaan']); ?></p>
                                            <?php if (!empty($soal['gambar_soal'])): ?>
                                                <img src="<?= soalGambarUrl($soal['gambar_soal']); ?>"
                                                     class="max-h-24 rounded border mt-2" alt="Gambar soal">
                                            <?php endif; ?>
                                            <?php if (!empty($soal['kunci_jawaban'])): ?>
                                                <p class="mt-2 text-sm text-green-700 bg-green-50 px-3 py-1 rounded">
                                                    <strong>Kunci:</strong> <?= htmlspecialchars($soal['kunci_jawaban']); ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 ml-4">
                                    <span class="text-xs text-gray-400">Skor: <?= $soal['skor']; ?></span>
                                    <a href="<?= BASEURL; ?>/soalSts/hapusSoal/<?= $soal['id_soal']; ?>"
                                       onclick="return confirm('Hapus soal ini?')"
                                       class="text-red-400 hover:text-red-600 transition-colors">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</main>
