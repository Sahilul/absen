<!-- admin/soal_sts/daftar_mapel.php - Daftar Soal per Mapel -->
<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
    <!-- Flash Message -->
    <?php Flasher::flash(); ?>

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 space-y-4 sm:space-y-0">
        <div>
            <nav class="flex items-center text-sm text-gray-500 mb-2">
                <a href="<?= BASEURL; ?>/soalSts" class="hover:text-indigo-600">Soal STS</a>
                <i data-lucide="chevron-right" class="w-4 h-4 mx-1"></i>
                <span class="text-gray-700 font-medium">Daftar Soal</span>
            </nav>
            <h2 class="text-2xl font-bold text-gray-800">Daftar Soal — <?= htmlspecialchars($data['kelas']['nama_kelas']); ?></h2>
            <p class="text-gray-600 mt-1">Kelola soal per mata pelajaran</p>
        </div>
        <div class="flex gap-2">
            <a href="<?= BASEURL; ?>/soalSts/cetakBeritaAcara/<?= $data['id_kelas']; ?>"
               target="_blank"
               class="bg-teal-50 hover:bg-teal-100 text-teal-700 font-medium py-2 px-4 rounded-lg flex items-center transition-colors duration-200">
                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i>
                Berita Acara & Daftar Hadir
            </a>
            <a href="<?= BASEURL; ?>/soalSts/pengaturan/<?= $data['id_kelas']; ?>"
               class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-medium py-2 px-4 rounded-lg flex items-center transition-colors duration-200">
                <i data-lucide="settings" class="w-4 h-4 mr-2"></i>
                Pengaturan
            </a>
            <a href="<?= BASEURL; ?>/soalSts"
               class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg flex items-center transition-colors duration-200">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                Kembali
            </a>
        </div>
    </div>

    <?php
    $mapelDenganPengaturan = array_filter($data['mapel_list'], function ($m) {
        return !empty($m['pengaturan']);
    });
    ?>

    <?php if (empty($mapelDenganPengaturan)): ?>
        <div class="bg-white rounded-xl shadow-sm border p-12 text-center">
            <i data-lucide="file-x" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
            <p class="text-gray-500 mb-2">Belum ada pengaturan soal untuk kelas ini</p>
            <p class="text-sm text-gray-400">Silakan atur pengaturan soal terlebih dahulu</p>
            <a href="<?= BASEURL; ?>/soalSts/pengaturan/<?= $data['id_kelas']; ?>"
               class="inline-flex items-center mt-4 bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                <i data-lucide="settings" class="w-4 h-4 mr-2"></i>
                Buat Pengaturan
            </a>
        </div>
    <?php else: ?>
        <!-- Summary Stats -->
        <?php
        $totalMapelSoal = count($mapelDenganPengaturan);
        $countLengkap = 0;
        $countSiapCetak = 0;
        $countDicetak = 0;
        $countBelumSelesai = 0;
        $countBelumCetak = 0;
        foreach ($mapelDenganPengaturan as $m) {
            $p = $m['pengaturan'];
            $target = ($p['jumlah_pilihan_ganda'] ?? 0) + ($p['jumlah_essay'] ?? 0) + ($p['jumlah_isian_singkat'] ?? 0);
            $statusList = $data['kelas']['status_soal'] ?? [];
            $soal = 0;
            foreach ($statusList as $st) {
                if ($st['id_pengaturan'] == $p['id']) {
                    $soal = $st['total_soal'];
                    break;
                }
            }
            if ($target > 0 && $soal >= $target) $countLengkap++;
            else $countBelumSelesai++;
            if (!empty($p['siap_cetak_at'])) $countSiapCetak++;
            if (!empty($p['dicetak_at'])) $countDicetak++;
            if (!empty($p['siap_cetak_at']) && empty($p['dicetak_at'])) $countBelumCetak++;
        }
        ?>
        <div class="grid grid-cols-3 sm:grid-cols-6 gap-3 mb-6">
            <div class="bg-white p-3 rounded-lg shadow-sm border text-center">
                <p class="text-2xl font-bold text-gray-800"><?= $totalMapelSoal; ?></p>
                <p class="text-xs text-gray-500">Total Mapel</p>
            </div>
            <div class="bg-white p-3 rounded-lg shadow-sm border text-center">
                <p class="text-2xl font-bold text-red-500"><?= $countBelumSelesai; ?></p>
                <p class="text-xs text-gray-500">Belum Selesai</p>
            </div>
            <div class="bg-white p-3 rounded-lg shadow-sm border text-center">
                <p class="text-2xl font-bold text-green-600"><?= $countLengkap; ?></p>
                <p class="text-xs text-gray-500">Soal Lengkap</p>
            </div>
            <div class="bg-white p-3 rounded-lg shadow-sm border text-center">
                <p class="text-2xl font-bold text-amber-600"><?= $countSiapCetak; ?></p>
                <p class="text-xs text-gray-500">Siap Cetak</p>
            </div>
            <div class="bg-white p-3 rounded-lg shadow-sm border text-center">
                <p class="text-2xl font-bold text-emerald-600"><?= $countDicetak; ?></p>
                <p class="text-xs text-gray-500">Sudah Dicetak</p>
            </div>
            <div class="bg-white p-3 rounded-lg shadow-sm border text-center">
                <p class="text-2xl font-bold text-orange-500"><?= $countBelumCetak; ?></p>
                <p class="text-xs text-gray-500">Belum Cetak</p>
            </div>
        </div>

        <!-- Filter Buttons -->
        <div x-data="{ filter: 'semua' }" class="mb-6">
            <div class="flex flex-wrap gap-2 mb-4">
                <button @click="filter = 'semua'"
                        :class="filter === 'semua' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-50 border'"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center gap-1.5">
                    <i data-lucide="list" class="w-4 h-4"></i>
                    Semua <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px]" :class="filter === 'semua' ? 'bg-indigo-500' : 'bg-gray-100'"><?= $totalMapelSoal; ?></span>
                </button>
                <button @click="filter = 'belum_selesai'"
                        :class="filter === 'belum_selesai' ? 'bg-red-600 text-white shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-50 border'"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center gap-1.5">
                    <i data-lucide="clock" class="w-4 h-4"></i>
                    Belum Selesai <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px]" :class="filter === 'belum_selesai' ? 'bg-red-500' : 'bg-gray-100'"><?= $countBelumSelesai; ?></span>
                </button>
                <button @click="filter = 'selesai'"
                        :class="filter === 'selesai' ? 'bg-green-600 text-white shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-50 border'"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center gap-1.5">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    Selesai <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px]" :class="filter === 'selesai' ? 'bg-green-500' : 'bg-gray-100'"><?= $countLengkap; ?></span>
                </button>
                <button @click="filter = 'siap_cetak'"
                        :class="filter === 'siap_cetak' ? 'bg-amber-600 text-white shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-50 border'"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center gap-1.5">
                    <i data-lucide="lock" class="w-4 h-4"></i>
                    Siap Cetak <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px]" :class="filter === 'siap_cetak' ? 'bg-amber-500' : 'bg-gray-100'"><?= $countSiapCetak; ?></span>
                </button>
                <button @click="filter = 'sudah_cetak'"
                        :class="filter === 'sudah_cetak' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-50 border'"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center gap-1.5">
                    <i data-lucide="printer-check" class="w-4 h-4"></i>
                    Sudah Dicetak <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px]" :class="filter === 'sudah_cetak' ? 'bg-emerald-500' : 'bg-gray-100'"><?= $countDicetak; ?></span>
                </button>
                <button @click="filter = 'belum_cetak'"
                        :class="filter === 'belum_cetak' ? 'bg-orange-600 text-white shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-50 border'"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center gap-1.5">
                    <i data-lucide="printer-x" class="w-4 h-4"></i>
                    Belum Cetak <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px]" :class="filter === 'belum_cetak' ? 'bg-orange-500' : 'bg-gray-100'"><?= $countBelumCetak; ?></span>
                </button>
            </div>

        <!-- Mapel Cards -->
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
                $sudahDicetak = !empty($p['dicetak_at']);
                $sudahSiapCetak = !empty($p['siap_cetak_at']);
                ?>
                <div x-show="filter === 'semua' || (filter === 'belum_selesai' && <?= !$isComplete ? 'true' : 'false'; ?>) || (filter === 'selesai' && <?= $isComplete ? 'true' : 'false'; ?>) || (filter === 'siap_cetak' && <?= $sudahSiapCetak ? 'true' : 'false'; ?>) || (filter === 'sudah_cetak' && <?= $sudahDicetak ? 'true' : 'false'; ?>) || (filter === 'belum_cetak' && <?= ($sudahSiapCetak && !$sudahDicetak) ? 'true' : 'false'; ?>)"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="bg-white rounded-xl shadow-sm border <?= $isComplete ? 'border-green-200' : 'border-gray-200'; ?> p-4">
                    <div class="flex items-center justify-between mb-2">
                        <div>
                            <h4 class="font-semibold text-gray-800"><?= htmlspecialchars($mapel['nama_mapel']); ?></h4>
                            <p class="text-[11px] text-gray-400 flex items-center gap-1 mt-0.5">
                                <i data-lucide="user" class="w-3 h-3"></i>
                                <?= htmlspecialchars($mapel['nama_guru'] ?? '-'); ?>
                            </p>
                        </div>
                        <div class="flex items-center gap-1">
                            <?php if ($sudahDicetak): ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-100 text-emerald-700"
                                      title="Terakhir dicetak: <?= date('d/m/Y H:i', strtotime($p['dicetak_at'])); ?>">
                                    <i data-lucide="printer-check" class="w-3 h-3 mr-1"></i> Sudah Dicetak
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-gray-100 text-gray-500">
                                    <i data-lucide="printer" class="w-3 h-3 mr-1"></i> Belum Dicetak
                                </span>
                            <?php endif; ?>
                            <?php if ($sudahSiapCetak): ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-amber-100 text-amber-700"
                                      title="Ditandai siap cetak: <?= date('d/m/Y H:i', strtotime($p['siap_cetak_at'])); ?>">
                                    <i data-lucide="lock" class="w-3 h-3 mr-1"></i> Siap Cetak
                                </span>
                            <?php endif; ?>
                            <?php if ($isComplete): ?>
                                <span class="text-green-600"><i data-lucide="check-circle" class="w-5 h-5"></i></span>
                            <?php endif; ?>
                        </div>
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
                        <?php if ($sudahDicetak): ?>
                            <p class="text-[10px] text-emerald-600 pt-0.5">
                                Dicetak: <?= date('d/m/Y H:i', strtotime($p['dicetak_at'])); ?>
                            </p>
                        <?php endif; ?>
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
                        <?php if ($sudahSiapCetak): ?>
                        <a href="<?= BASEURL; ?>/soalSts/previewCetak/<?= $data['id_kelas']; ?>/<?= $mapel['id_mapel']; ?>"
                           class="flex-1 text-center bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-medium py-2 px-2 rounded-lg transition-colors">
                            <i data-lucide="printer" class="w-3 h-3 inline mr-1"></i> Cetak
                        </a>
                        <a href="<?= BASEURL; ?>/soalSts/previewCetakNama/<?= $data['id_kelas']; ?>/<?= $mapel['id_mapel']; ?>"
                           class="flex-1 text-center bg-violet-50 hover:bg-violet-100 text-violet-700 text-xs font-medium py-2 px-2 rounded-lg transition-colors"
                           title="Cetak 1 lembar per siswa dengan nama otomatis">
                            <i data-lucide="users" class="w-3 h-3 inline mr-1"></i> + Nama
                        </a>
                        <?php else: ?>
                        <span class="flex-1 text-center bg-gray-100 text-gray-400 text-xs font-medium py-2 px-2 rounded-lg cursor-not-allowed" title="<?= !$isComplete ? 'Soal belum lengkap' : 'Belum ditandai Siap Cetak'; ?>">
                            <i data-lucide="printer" class="w-3 h-3 inline mr-1"></i> Cetak
                        </span>
                        <span class="flex-1 text-center bg-gray-100 text-gray-400 text-xs font-medium py-2 px-2 rounded-lg cursor-not-allowed" title="<?= !$isComplete ? 'Soal belum lengkap' : 'Belum ditandai Siap Cetak'; ?>">
                            <i data-lucide="users" class="w-3 h-3 inline mr-1"></i> + Nama
                        </span>
                        <?php endif; ?>
                    </div>
                    <form action="<?= BASEURL; ?>/soalSts/toggleDicetak/<?= $data['id_kelas']; ?>/<?= $mapel['id_mapel']; ?>" method="POST" class="mt-2">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="redirect" value="mapel">
                        <button type="submit"
                                class="w-full text-center <?= $sudahDicetak ? 'bg-amber-50 hover:bg-amber-100 text-amber-700' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700'; ?> text-xs font-medium py-2 px-2 rounded-lg transition-colors">
                            <?php if ($sudahDicetak): ?>
                                <i data-lucide="rotate-ccw" class="w-3 h-3 inline mr-1"></i> Tandai Belum Dicetak
                            <?php else: ?>
                                <i data-lucide="check" class="w-3 h-3 inline mr-1"></i> Tandai Sudah Dicetak
                            <?php endif; ?>
                        </button>
                    </form>
                    <?php if ($sudahSiapCetak): ?>
                    <form action="<?= BASEURL; ?>/soalSts/batalSiapCetak/<?= $id_pengaturan; ?>" method="POST" class="mt-1">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                        <button type="submit" onclick="return confirm('Batalkan penanda Siap Cetak untuk <?= htmlspecialchars($mapel['nama_mapel']); ?>?')"
                                class="w-full text-center bg-red-50 hover:bg-red-100 text-red-700 text-xs font-medium py-2 px-2 rounded-lg transition-colors">
                            <i data-lucide="lock-open" class="w-3 h-3 inline mr-1"></i> Batalkan Siap Cetak
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Empty state when filter has no results -->
        <template x-if="filter !== 'semua'">
            <div x-show="document.querySelectorAll('[x-show]:not([style*=\'display: none\'])').length <= 1"
                 x-effect="$nextTick(() => { let visible = 0; document.querySelectorAll('.grid > [x-show]').forEach(el => { if (el.style.display !== 'none') visible++; }); $el._x_visible = visible; })"
                 class="hidden">
            </div>
        </template>
        </div>
    <?php endif; ?>
</main>
