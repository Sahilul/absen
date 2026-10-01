<!-- admin/soal_sts/pengaturan.php - Pengaturan Model Soal per Mapel -->
<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
    <!-- Flash Message -->
    <?php Flasher::flash(); ?>

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 space-y-4 sm:space-y-0">
        <div>
            <nav class="flex items-center text-sm text-gray-500 mb-2">
                <a href="<?= BASEURL; ?>/soalSts" class="hover:text-indigo-600">Soal STS</a>
                <i data-lucide="chevron-right" class="w-4 h-4 mx-1"></i>
                <span class="text-gray-700 font-medium">Pengaturan</span>
            </nav>
            <h2 class="text-2xl font-bold text-gray-800">Pengaturan Soal — <?= htmlspecialchars($data['kelas']['nama_kelas']); ?></h2>
            <p class="text-gray-600 mt-1">Atur jumlah dan model soal untuk setiap mata pelajaran</p>
        </div>
        <a href="<?= BASEURL; ?>/soalSts"
           class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg flex items-center transition-colors duration-200">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
            Kembali
        </a>
    </div>

    <?php if (empty($data['mapel_list'])): ?>
        <div class="bg-white rounded-xl shadow-sm border p-12 text-center">
            <i data-lucide="book-x" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
            <p class="text-gray-500 mb-2">Belum ada penugasan guru untuk kelas ini</p>
            <p class="text-sm text-gray-400">Silakan atur penugasan guru terlebih dahulu di menu Penugasan Guru</p>
        </div>
    <?php else: ?>
        <form action="<?= BASEURL; ?>/soalSts/simpanPengaturan" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="id_kelas" value="<?= $data['id_kelas']; ?>">
            <input type="hidden" name="id_semester" value="<?= $_SESSION['id_semester_aktif']; ?>">

            <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                <div class="p-4 bg-indigo-50 border-b">
                    <h3 class="text-sm font-semibold text-indigo-800">
                        <i data-lucide="settings" class="w-4 h-4 inline mr-1"></i>
                        Pengaturan Model Soal per Mata Pelajaran
                    </h3>
                    <p class="text-xs text-indigo-600 mt-1">Tentukan jumlah soal PG, Isian Singkat, dan Essay untuk setiap mapel</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="text-left p-3 font-semibold text-gray-700">No</th>
                                <th class="text-left p-3 font-semibold text-gray-700">Mata Pelajaran</th>
                                <th class="text-left p-3 font-semibold text-gray-700">Guru</th>
                                <th class="text-center p-3 font-semibold text-gray-700">Pilihan Ganda</th>
                                <th class="text-center p-3 font-semibold text-gray-700">Isian Singkat</th>
                                <th class="text-center p-3 font-semibold text-gray-700">Essay</th>
                                <th class="text-center p-3 font-semibold text-gray-700">Opsi PG</th>
                                <th class="text-center p-3 font-semibold text-gray-700">Waktu (menit)</th>
                                <th class="text-center p-3 font-semibold text-gray-700">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach ($data['mapel_list'] as $idx => $mapel): ?>
                                <?php
                                $p = $mapel['pengaturan'];
                                $hasConfig = !empty($p);
                                ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="p-3 text-gray-500"><?= $idx + 1; ?></td>
                                    <td class="p-3 font-medium text-gray-800">
                                        <?= htmlspecialchars($mapel['nama_mapel']); ?>
                                        <input type="hidden" name="id_mapel[]" value="<?= $mapel['id_mapel']; ?>">
                                    </td>
                                    <td class="p-3 text-gray-600"><?= htmlspecialchars($mapel['nama_guru'] ?? '-'); ?></td>
                                    <td class="p-3 text-center">
                                        <input type="number" name="jumlah_pg[]" min="0" max="100"
                                               value="<?= $hasConfig ? $p['jumlah_pilihan_ganda'] : 20; ?>"
                                               class="w-20 text-center border border-gray-300 rounded-lg px-2 py-1.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    </td>
                                    <td class="p-3 text-center">
                                        <input type="number" name="jumlah_isian[]" min="0" max="50"
                                               value="<?= $hasConfig ? $p['jumlah_isian_singkat'] : 0; ?>"
                                               class="w-20 text-center border border-gray-300 rounded-lg px-2 py-1.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    </td>
                                    <td class="p-3 text-center">
                                        <input type="number" name="jumlah_essay[]" min="0" max="50"
                                               value="<?= $hasConfig ? $p['jumlah_essay'] : 5; ?>"
                                               class="w-20 text-center border border-gray-300 rounded-lg px-2 py-1.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    </td>
                                    <td class="p-3 text-center">
                                        <select name="opsi_pg[]"
                                                class="w-20 text-center border border-gray-300 rounded-lg px-2 py-1.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                            <option value="4" <?= ($hasConfig && $p['opsi_pg'] == 4) || !$hasConfig ? 'selected' : ''; ?>>A-D</option>
                                            <option value="5" <?= $hasConfig && $p['opsi_pg'] == 5 ? 'selected' : ''; ?>>A-E</option>
                                        </select>
                                    </td>
                                    <td class="p-3 text-center">
                                        <input type="number" name="waktu[]" min="10" max="300"
                                               value="<?= $hasConfig ? $p['waktu_pengerjaan'] : 60; ?>"
                                               class="w-20 text-center border border-gray-300 rounded-lg px-2 py-1.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    </td>
                                    <td class="p-3 text-center">
                                        <?php if ($hasConfig): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                                <i data-lucide="check" class="w-3 h-3 mr-1"></i> Tersimpan
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                                                <i data-lucide="minus" class="w-3 h-3 mr-1"></i> Baru
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <!-- Petunjuk Umum (collapsible) -->
                                <tr class="bg-gray-50/50">
                                    <td></td>
                                    <td colspan="8" class="p-3">
                                        <details class="text-xs">
                                            <summary class="cursor-pointer text-gray-500 hover:text-indigo-600">
                                                <i data-lucide="file-text" class="w-3 h-3 inline mr-1"></i>
                                                Petunjuk Umum (opsional)
                                            </summary>
                                            <textarea name="petunjuk[]" rows="2"
                                                      placeholder="Contoh: Pilihlah jawaban yang paling tepat..."
                                                      class="mt-2 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"><?= $hasConfig ? htmlspecialchars($p['petunjuk_umum'] ?? '') : ''; ?></textarea>
                                        </details>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="p-4 bg-gray-50 border-t flex justify-end">
                    <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 px-6 rounded-lg flex items-center transition-colors duration-200 shadow-sm">
                        <i data-lucide="save" class="w-4 h-4 mr-2"></i>
                        Simpan Pengaturan
                    </button>
                </div>
            </div>
        </form>

        <!-- Daftar Mapel yang Sudah Ada Pengaturan -->
        <?php
        $mapelDenganPengaturan = array_filter($data['mapel_list'], function ($m) {
            return !empty($m['pengaturan']);
        });
        ?>
        <?php if (!empty($mapelDenganPengaturan)): ?>
            <div id="daftar" class="mt-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i data-lucide="list" class="w-5 h-5 inline mr-1"></i>
                    Daftar Soal per Mata Pelajaran
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($mapelDenganPengaturan as $mapel): ?>
                        <?php
                        $p = $mapel['pengaturan'];
                        $id_pengaturan = $p['id'];
                        $totalTarget = ($p['jumlah_pilihan_ganda'] ?? 0) + ($p['jumlah_essay'] ?? 0) + ($p['jumlah_isian_singkat'] ?? 0);

                        // Hitung soal yang sudah diinput
                        $statusList = $data['kelas']['status_soal'] ?? [];
                        $totalSoal = 0;
                        foreach ($statusList as $st) {
                            if ($st['id_pengaturan'] == $id_pengaturan) {
                                $totalSoal = $st['total_soal'];
                                break;
                            }
                        }
                        $progressMapel = $totalTarget > 0 ? round(($totalSoal / $totalTarget) * 100) : 0;
                        $isComplete = $totalSoal >= $totalTarget && $totalTarget > 0;
                        ?>
                        <div class="bg-white rounded-xl shadow-sm border <?= $isComplete ? 'border-green-200' : 'border-gray-200'; ?> p-4">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-semibold text-gray-800"><?= htmlspecialchars($mapel['nama_mapel']); ?></h4>
                                <?php if ($isComplete): ?>
                                    <span class="text-green-600"><i data-lucide="check-circle" class="w-5 h-5"></i></span>
                                <?php endif; ?>
                            </div>
                            <div class="text-xs text-gray-500 space-y-1 mb-3">
                                <div class="flex justify-between">
                                    <span>PG: <?= $p['jumlah_pilihan_ganda']; ?> soal</span>
                                    <?php if ($p['jumlah_isian_singkat'] > 0): ?>
                                        <span>Isian: <?= $p['jumlah_isian_singkat']; ?> soal</span>
                                    <?php endif; ?>
                                    <span>Essay: <?= $p['jumlah_essay']; ?> soal</span>
                                </div>
                                <div class="flex justify-between text-gray-400">
                                    <span>Progress: <?= $totalSoal; ?>/<?= $totalTarget; ?></span>
                                    <span><?= $progressMapel; ?>%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-1.5">
                                    <div class="<?= $isComplete ? 'bg-green-500' : 'bg-indigo-500'; ?> h-1.5 rounded-full" style="width: <?= min($progressMapel, 100); ?>%"></div>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <a href="<?= BASEURL; ?>/soalSts/inputSoal/<?= $id_pengaturan; ?>"
                                   class="flex-1 text-center bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-medium py-2 px-2 rounded-lg transition-colors">
                                    <i data-lucide="edit" class="w-3 h-3 inline mr-1"></i> Input Soal
                                </a>
                                <a href="<?= BASEURL; ?>/soalSts/daftarSoal/<?= $data['id_kelas']; ?>/<?= $mapel['id_mapel']; ?>"
                                   class="flex-1 text-center bg-violet-50 hover:bg-violet-100 text-violet-700 text-xs font-medium py-2 px-2 rounded-lg transition-colors">
                                    <i data-lucide="eye" class="w-3 h-3 inline mr-1"></i> Lihat
                                </a>
                                <a href="<?= BASEURL; ?>/soalSts/previewCetak/<?= $data['id_kelas']; ?>/<?= $mapel['id_mapel']; ?>"
                                   class="flex-1 text-center bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-medium py-2 px-2 rounded-lg transition-colors">
                                    <i data-lucide="printer" class="w-3 h-3 inline mr-1"></i> Cetak
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</main>
