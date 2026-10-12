<!-- admin/soal_sts/input_soal.php - Form Input Soal STS -->
<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
    <!-- Flash Message -->
    <?php Flasher::flash(); ?>

    <!-- Inline notification (dipakai saat terapkan hasil AI, sebelum simpan) -->
    <div id="inlineNotif" class="hidden bg-emerald-50 border border-emerald-300 text-emerald-800 px-4 py-3 rounded-lg mb-4 flex items-start gap-3 animate-slide-up">
        <i data-lucide="check-circle" class="w-5 h-5 mt-0.5 flex-shrink-0"></i>
        <div class="flex-1 text-sm" id="inlineNotifMsg"></div>
        <button type="button" onclick="document.getElementById('inlineNotif').classList.add('hidden')" class="text-emerald-500 hover:text-emerald-700">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>

    <?php
    $p = $data['pengaturan'];
    $soalMap = $data['soal_map'];
    $jumlahPG = (int)$p['jumlah_pilihan_ganda'];
    $jumlahEssay = (int)$p['jumlah_essay'];
    $jumlahIsian = (int)$p['jumlah_isian_singkat'];
    $opsiPG = (int)$p['opsi_pg'];
    $opsiLabels = ['A', 'B', 'C', 'D', 'E'];
    // Admin ATAU guru pengelola Soal STS diperlakukan sama (full UI).
    $isAdmin = ($_SESSION['role'] ?? '') === 'admin' || !empty($data['is_pengelola_sts']);
    $backUrl = ($data['is_pengelola_sts'] ?? false) ? BASEURL . '/soalSts/daftarMapel/' . $p['id_kelas'] : BASEURL . '/guru';
    $isLocked = $data['is_locked'] ?? false;
    $isComplete = $data['is_complete'] ?? false;
    ?>

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 space-y-4 sm:space-y-0">
        <div>
            <nav class="flex items-center text-sm text-gray-500 mb-2">
                <?php if ($isAdmin): ?>
                    <a href="<?= BASEURL; ?>/soalSts" class="hover:text-indigo-600">Soal STS</a>
                    <i data-lucide="chevron-right" class="w-4 h-4 mx-1"></i>
                    <a href="<?= BASEURL; ?>/soalSts/daftarMapel/<?= $p['id_kelas']; ?>" class="hover:text-indigo-600">Daftar Mapel</a>
                <?php else: ?>
                    <a href="<?= BASEURL; ?>/guru" class="hover:text-indigo-600">Dashboard</a>
                <?php endif; ?>
                <i data-lucide="chevron-right" class="w-4 h-4 mx-1"></i>
                <span class="text-gray-700 font-medium">Input Soal</span>
            </nav>
            <h2 class="text-2xl font-bold text-gray-800">Input Soal — <?= htmlspecialchars($p['nama_mapel']); ?></h2>
            <p class="text-gray-600 mt-1">Kelas <?= htmlspecialchars($p['nama_kelas']); ?> • Jenjang <?= $p['jenjang']; ?></p>
        </div>
        <div class="flex gap-2">
            <a href="<?= $backUrl; ?>"
               class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg flex items-center transition-colors duration-200">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                Kembali
            </a>
            <?php if (!$isLocked): ?>
            <button type="button" onclick="document.getElementById('aiModal').classList.remove('hidden')"
                    class="bg-violet-600 hover:bg-violet-700 text-white font-medium py-2 px-4 rounded-lg flex items-center transition-colors duration-200 shadow-sm">
                <i data-lucide="sparkles" class="w-4 h-4 mr-2"></i>
                Generate AI
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Progress Summary -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <?php if ($jumlahPG > 0): ?>
        <div class="bg-white p-4 rounded-lg shadow-sm border">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Pilihan Ganda</p>
                    <p class="text-xl font-semibold text-gray-900"><?= $data['total_pg']; ?> / <?= $jumlahPG; ?></p>
                </div>
                <div class="bg-blue-100 p-2 rounded-lg">
                    <i data-lucide="list-checks" class="w-5 h-5 text-blue-600"></i>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php if ($jumlahIsian > 0): ?>
        <div class="bg-white p-4 rounded-lg shadow-sm border">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Isian Singkat</p>
                    <p class="text-xl font-semibold text-gray-900"><?= $data['total_isian']; ?> / <?= $jumlahIsian; ?></p>
                </div>
                <div class="bg-teal-100 p-2 rounded-lg">
                    <i data-lucide="text-cursor-input" class="w-5 h-5 text-teal-600"></i>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php if ($jumlahEssay > 0): ?>
        <div class="bg-white p-4 rounded-lg shadow-sm border">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Essay</p>
                    <p class="text-xl font-semibold text-gray-900"><?= $data['total_essay']; ?> / <?= $jumlahEssay; ?></p>
                </div>
                <div class="bg-purple-100 p-2 rounded-lg">
                    <i data-lucide="file-text" class="w-5 h-5 text-purple-600"></i>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($isLocked): ?>
    <!-- Lock Banner -->
    <div class="bg-amber-50 border border-amber-300 rounded-lg p-4 mb-6 flex items-start gap-3">
        <i data-lucide="lock" class="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0"></i>
        <div class="flex-1">
            <p class="font-semibold text-amber-800">Soal Terkunci — Siap Cetak</p>
            <p class="text-sm text-amber-700 mt-1">Soal ini sudah ditandai "Siap Cetak" dan tidak bisa diedit. <?php if (!$isAdmin): ?>Hubungi admin untuk membatalkan.<?php endif; ?></p>
            <?php if (!empty($p['siap_cetak_at'])): ?>
                <p class="text-xs text-amber-600 mt-1">Ditandai pada: <?= date('d/m/Y H:i', strtotime($p['siap_cetak_at'])); ?></p>
            <?php endif; ?>
        </div>
        <?php if ($isAdmin): ?>
        <form action="<?= BASEURL; ?>/soalSts/batalSiapCetak/<?= $p['id']; ?>" method="POST" class="flex-shrink-0">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="redirect" value="input">
            <button type="submit" onclick="return confirm('Batalkan penanda Siap Cetak? Soal akan bisa diedit kembali.')"
                    class="bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium py-1.5 px-3 rounded-lg flex items-center transition-colors">
                <i data-lucide="lock-open" class="w-4 h-4 mr-1"></i>
                Batalkan Kunci
            </button>
        </form>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Form Soal -->
    <form action="<?= BASEURL; ?>/soalSts/simpanSemuaSoal" method="POST" enctype="multipart/form-data" id="formSoal">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
        <input type="hidden" name="id_pengaturan" value="<?= $p['id']; ?>">

    <?php if ($isLocked): ?><fieldset disabled><?php endif; ?>

        <!-- Tab Navigation -->
        <?php
        // Tentukan tab pertama yang aktif (skip tipe dengan jumlah 0)
        $firstTab = 'pg';
        if ($jumlahPG <= 0) $firstTab = ($jumlahIsian > 0) ? 'isian' : (($jumlahEssay > 0) ? 'essay' : 'pg');
        ?>
        <div class="mb-4 border-b border-gray-200" x-data="{ activeTab: '<?= $firstTab; ?>' }">
            <nav class="flex space-x-4" aria-label="Tabs">
                <?php if ($jumlahPG > 0): ?>
                <button type="button" @click="activeTab = 'pg'"
                        :class="activeTab === 'pg' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm transition-colors">
                    <i data-lucide="list-checks" class="w-4 h-4 inline mr-1"></i>
                    Pilihan Ganda (<?= $jumlahPG; ?>)
                </button>
                <?php endif; ?>
                <?php if ($jumlahIsian > 0): ?>
                    <button type="button" @click="activeTab = 'isian'"
                            :class="activeTab === 'isian' ? 'border-teal-500 text-teal-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm transition-colors">
                        <i data-lucide="text-cursor-input" class="w-4 h-4 inline mr-1"></i>
                        Isian Singkat (<?= $jumlahIsian; ?>)
                    </button>
                <?php endif; ?>
                <?php if ($jumlahEssay > 0): ?>
                    <button type="button" @click="activeTab = 'essay'"
                            :class="activeTab === 'essay' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm transition-colors">
                        <i data-lucide="file-text" class="w-4 h-4 inline mr-1"></i>
                        Essay (<?= $jumlahEssay; ?>)
                    </button>
                <?php endif; ?>
            </nav>

            <!-- ==================== PILIHAN GANDA ==================== -->
            <?php if ($jumlahPG > 0): ?>
            <div x-show="activeTab === 'pg'" class="py-4 space-y-4">
                <?php for ($i = 1; $i <= $jumlahPG; $i++): ?>
                    <?php
                    $key = "pg_{$i}";
                    $existing = $soalMap[$key] ?? null;
                    ?>
                    <div class="bg-white rounded-xl shadow-sm border p-5" x-data="{ showImage: <?= !empty($existing['gambar_soal']) ? 'true' : 'false'; ?> }">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-semibold text-gray-800">
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 text-sm font-bold mr-2"><?= $i; ?></span>
                                Soal PG #<?= $i; ?>
                            </h4>
                            <?php if ($existing): ?>
                                <span class="text-xs text-green-600 bg-green-50 px-2 py-0.5 rounded-full">Tersimpan</span>
                            <?php endif; ?>
                        </div>

                        <!-- Pertanyaan -->
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pertanyaan</label>
                            <textarea name="soal[<?= $key; ?>][pertanyaan]" rows="3"
                                      placeholder="Tulis pertanyaan soal..."
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"><?= htmlspecialchars($existing['pertanyaan'] ?? ''); ?></textarea>
                        </div>

                        <!-- Gambar Soal -->
                        <div class="mb-3">
                            <button type="button" @click="showImage = !showImage"
                                    class="text-xs text-indigo-600 hover:text-indigo-800 flex items-center">
                                <i data-lucide="image" class="w-3 h-3 mr-1"></i>
                                <span x-text="showImage ? 'Sembunyikan Gambar' : 'Tambah Gambar'"></span>
                            </button>
                            <div x-show="showImage" x-transition class="mt-2">
                                <?php if (!empty($existing['gambar_soal'])): ?>
                                    <div class="mb-2" x-data="{ imgDeleted: false }" x-show="!imgDeleted">
                                        <img src="<?= soalGambarUrl($existing['gambar_soal']); ?>"
                                             class="max-h-32 rounded border" alt="Gambar soal">
                                        <input type="hidden" name="soal[<?= $key; ?>][gambar_existing]" value="<?= htmlspecialchars($existing['gambar_soal']); ?>">
                                        <input type="hidden" name="soal[<?= $key; ?>][hapus_gambar]" :value="imgDeleted ? 1 : 0">
                                        <button type="button" @click="imgDeleted = true"
                                                class="mt-1 text-xs text-red-600 hover:text-red-800 flex items-center">
                                            <i data-lucide="trash-2" class="w-3 h-3 mr-1"></i> Hapus Gambar
                                        </button>
                                    </div>
                                <?php endif; ?>
                                <input type="file" name="gambar_<?= $key; ?>" accept="image/*"
                                       class="text-xs text-gray-500 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                <p class="text-xs text-gray-400 mt-1">Maks 2MB. Format: JPG, PNG, GIF, WebP</p>
                            </div>
                        </div>

                        <!-- Opsi Jawaban -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                            <?php for ($o = 0; $o < $opsiPG; $o++): ?>
                                <?php $opsiField = 'opsi_' . strtolower($opsiLabels[$o]); ?>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-gray-100 text-gray-600 text-sm font-bold flex-shrink-0"><?= $opsiLabels[$o]; ?></span>
                                    <input type="text" name="soal[<?= $key; ?>][<?= $opsiField; ?>]"
                                           value="<?= htmlspecialchars($existing[$opsiField] ?? ''); ?>"
                                           placeholder="Opsi <?= $opsiLabels[$o]; ?>"
                                           class="flex-1 border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                            <?php endfor; ?>
                        </div>

                        <!-- Kunci Jawaban & Skor -->
                        <div class="flex gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Kunci Jawaban</label>
                                <select name="soal[<?= $key; ?>][kunci_jawaban]"
                                        class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">-</option>
                                    <?php for ($o = 0; $o < $opsiPG; $o++): ?>
                                        <option value="<?= $opsiLabels[$o]; ?>" <?= ($existing['kunci_jawaban'] ?? '') === $opsiLabels[$o] ? 'selected' : ''; ?>><?= $opsiLabels[$o]; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Skor</label>
                                <input type="number" name="soal[<?= $key; ?>][skor]" min="1" max="100"
                                       value="<?= $existing['skor'] ?? 1; ?>"
                                       class="w-20 border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
            <?php endif; ?>

            <!-- ==================== ISIAN SINGKAT ==================== -->
            <?php if ($jumlahIsian > 0): ?>
                <div x-show="activeTab === 'isian'" class="py-4 space-y-4">
                    <?php for ($i = 1; $i <= $jumlahIsian; $i++): ?>
                        <?php
                        $key = "isian_{$i}";
                        $existing = $soalMap[$key] ?? null;
                        ?>
                        <div class="bg-white rounded-xl shadow-sm border p-5" x-data="{ showImage: <?= !empty($existing['gambar_soal']) ? 'true' : 'false'; ?> }">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="font-semibold text-gray-800">
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-teal-100 text-teal-700 text-sm font-bold mr-2"><?= $i; ?></span>
                                    Soal Isian #<?= $i; ?>
                                </h4>
                                <?php if ($existing): ?>
                                    <span class="text-xs text-green-600 bg-green-50 px-2 py-0.5 rounded-full">Tersimpan</span>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Pertanyaan</label>
                                <textarea name="soal[<?= $key; ?>][pertanyaan]" rows="3"
                                          placeholder="Tulis pertanyaan isian singkat..."
                                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500"><?= htmlspecialchars($existing['pertanyaan'] ?? ''); ?></textarea>
                            </div>

                            <!-- Gambar Soal -->
                            <div class="mb-3">
                                <button type="button" @click="showImage = !showImage"
                                        class="text-xs text-teal-600 hover:text-teal-800 flex items-center">
                                    <i data-lucide="image" class="w-3 h-3 mr-1"></i>
                                    <span x-text="showImage ? 'Sembunyikan Gambar' : 'Tambah Gambar'"></span>
                                </button>
                                <div x-show="showImage" x-transition class="mt-2">
                                    <?php if (!empty($existing['gambar_soal'])): ?>
                                        <div class="mb-2" x-data="{ imgDeleted: false }" x-show="!imgDeleted">
                                            <img src="<?= soalGambarUrl($existing['gambar_soal']); ?>"
                                                 class="max-h-32 rounded border" alt="Gambar soal">
                                            <input type="hidden" name="soal[<?= $key; ?>][gambar_existing]" value="<?= htmlspecialchars($existing['gambar_soal']); ?>">
                                            <input type="hidden" name="soal[<?= $key; ?>][hapus_gambar]" :value="imgDeleted ? 1 : 0">
                                            <button type="button" @click="imgDeleted = true"
                                                    class="mt-1 text-xs text-red-600 hover:text-red-800 flex items-center">
                                                <i data-lucide="trash-2" class="w-3 h-3 mr-1"></i> Hapus Gambar
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" name="gambar_<?= $key; ?>" accept="image/*"
                                           class="text-xs text-gray-500 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                                    <p class="text-xs text-gray-400 mt-1">Maks 2MB. Format: JPG, PNG, GIF, WebP</p>
                                </div>
                            </div>

                            <!-- Kunci Jawaban & Skor -->
                            <div class="flex gap-4">
                                <div class="flex-1">
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Kunci Jawaban</label>
                                    <input type="text" name="soal[<?= $key; ?>][kunci_jawaban]"
                                           value="<?= htmlspecialchars($existing['kunci_jawaban'] ?? ''); ?>"
                                           placeholder="Jawaban yang benar"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Skor</label>
                                    <input type="number" name="soal[<?= $key; ?>][skor]" min="1" max="100"
                                           value="<?= $existing['skor'] ?? 2; ?>"
                                           class="w-20 border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                                </div>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>

            <!-- ==================== ESSAY ==================== -->
            <?php if ($jumlahEssay > 0): ?>
                <div x-show="activeTab === 'essay'" class="py-4 space-y-4">
                    <?php for ($i = 1; $i <= $jumlahEssay; $i++): ?>
                        <?php
                        $key = "essay_{$i}";
                        $existing = $soalMap[$key] ?? null;
                        ?>
                        <div class="bg-white rounded-xl shadow-sm border p-5" x-data="{ showImage: <?= !empty($existing['gambar_soal']) ? 'true' : 'false'; ?> }">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="font-semibold text-gray-800">
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-purple-100 text-purple-700 text-sm font-bold mr-2"><?= $i; ?></span>
                                    Soal Essay #<?= $i; ?>
                                </h4>
                                <?php if ($existing): ?>
                                    <span class="text-xs text-green-600 bg-green-50 px-2 py-0.5 rounded-full">Tersimpan</span>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Pertanyaan</label>
                                <textarea name="soal[<?= $key; ?>][pertanyaan]" rows="4"
                                          placeholder="Tulis pertanyaan essay..."
                                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500"><?= htmlspecialchars($existing['pertanyaan'] ?? ''); ?></textarea>
                            </div>

                            <!-- Gambar Soal -->
                            <div class="mb-3">
                                <button type="button" @click="showImage = !showImage"
                                        class="text-xs text-purple-600 hover:text-purple-800 flex items-center">
                                    <i data-lucide="image" class="w-3 h-3 mr-1"></i>
                                    <span x-text="showImage ? 'Sembunyikan Gambar' : 'Tambah Gambar'"></span>
                                </button>
                                <div x-show="showImage" x-transition class="mt-2">
                                    <?php if (!empty($existing['gambar_soal'])): ?>
                                        <div class="mb-2" x-data="{ imgDeleted: false }" x-show="!imgDeleted">
                                            <img src="<?= soalGambarUrl($existing['gambar_soal']); ?>"
                                                 class="max-h-32 rounded border" alt="Gambar soal">
                                            <input type="hidden" name="soal[<?= $key; ?>][gambar_existing]" value="<?= htmlspecialchars($existing['gambar_soal']); ?>">
                                            <input type="hidden" name="soal[<?= $key; ?>][hapus_gambar]" :value="imgDeleted ? 1 : 0">
                                            <button type="button" @click="imgDeleted = true"
                                                    class="mt-1 text-xs text-red-600 hover:text-red-800 flex items-center">
                                                <i data-lucide="trash-2" class="w-3 h-3 mr-1"></i> Hapus Gambar
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" name="gambar_<?= $key; ?>" accept="image/*"
                                           class="text-xs text-gray-500 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                                    <p class="text-xs text-gray-400 mt-1">Maks 2MB. Format: JPG, PNG, GIF, WebP</p>
                                </div>
                            </div>

                            <!-- Kunci Jawaban & Skor -->
                            <div class="flex gap-4">
                                <div class="flex-1">
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Kunci Jawaban / Pedoman Penilaian (opsional)</label>
                                    <textarea name="soal[<?= $key; ?>][kunci_jawaban]" rows="2"
                                              placeholder="Tulis kunci jawaban atau pedoman penilaian..."
                                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500"><?= htmlspecialchars($existing['kunci_jawaban'] ?? ''); ?></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Skor</label>
                                    <input type="number" name="soal[<?= $key; ?>][skor]" min="1" max="100"
                                           value="<?= $existing['skor'] ?? 10; ?>"
                                           class="w-20 border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                </div>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Submit / Action Bar (sticky bottom) -->
        <?php if ($isLocked): ?></fieldset><?php endif; ?>

        <div class="sticky bottom-0 bg-white border-t shadow-lg p-4 -mx-6 -mb-6">
            <?php if ($isLocked): ?>
                <!-- Locked: show print buttons -->
                <div class="flex flex-col sm:flex-row justify-between items-center gap-3">
                    <p class="text-sm text-amber-600 flex items-center">
                        <i data-lucide="lock" class="w-4 h-4 inline mr-1"></i>
                        Soal terkunci — siap cetak
                    </p>
                    <div class="flex gap-2 flex-wrap">
                        <a href="<?= BASEURL; ?>/soalSts/previewCetak/<?= $p['id_kelas']; ?>/<?= $p['id_mapel']; ?>"
                           target="_blank"
                           class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg flex items-center transition-colors text-sm">
                            <i data-lucide="eye" class="w-4 h-4 mr-1.5"></i>
                            Preview
                        </a>
                        <a href="<?= BASEURL; ?>/soalSts/previewCetakNama/<?= $p['id_kelas']; ?>/<?= $p['id_mapel']; ?>"
                           target="_blank"
                           class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg flex items-center transition-colors text-sm">
                            <i data-lucide="printer" class="w-4 h-4 mr-1.5"></i>
                            Cetak + Nama
                        </a>
                        <a href="<?= BASEURL; ?>/soalSts/cetakKunci/<?= $p['id_kelas']; ?>/<?= $p['id_mapel']; ?>"
                           target="_blank"
                           class="bg-purple-600 hover:bg-purple-700 text-white font-medium py-2 px-4 rounded-lg flex items-center transition-colors text-sm">
                            <i data-lucide="key" class="w-4 h-4 mr-1.5"></i>
                            Kunci
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Unlocked: save + siap cetak + admin print in one bar -->
                <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3">
                    <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 px-6 rounded-lg flex items-center justify-center transition-colors duration-200 shadow-sm">
                        <i data-lucide="save" class="w-4 h-4 mr-2"></i>
                        Simpan Semua Soal
                    </button>
                    <div class="flex gap-2 flex-wrap items-center">
                        <?php if ($isComplete): ?>
                        <button type="button" onclick="document.getElementById('formSiapCetak').submit()"
                                class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2.5 px-5 rounded-lg flex items-center transition-colors duration-200 shadow-sm">
                            <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i>
                            Siap Cetak
                        </button>
                        <?php else: ?>
                        <span class="bg-gray-100 text-gray-400 font-medium py-2.5 px-5 rounded-lg flex items-center cursor-not-allowed text-sm"
                              title="Lengkapi semua soal terlebih dahulu">
                            <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i>
                            Siap Cetak
                        </span>
                        <?php endif; ?>
                        <?php if ($isAdmin): ?>
                        <a href="<?= BASEURL; ?>/soalSts/previewCetak/<?= $p['id_kelas']; ?>/<?= $p['id_mapel']; ?>"
                           target="_blank"
                           class="bg-blue-50 hover:bg-blue-100 text-blue-700 font-medium py-2.5 px-4 rounded-lg flex items-center transition-colors text-sm border border-blue-200">
                            <i data-lucide="eye" class="w-4 h-4 mr-1.5"></i>
                            Preview
                        </a>
                        <a href="<?= BASEURL; ?>/soalSts/previewCetakNama/<?= $p['id_kelas']; ?>/<?= $p['id_mapel']; ?>"
                           target="_blank"
                           class="bg-green-50 hover:bg-green-100 text-green-700 font-medium py-2.5 px-4 rounded-lg flex items-center transition-colors text-sm border border-green-200">
                            <i data-lucide="printer" class="w-4 h-4 mr-1.5"></i>
                            Cetak
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </form>

    <?php if (!$isLocked && $isComplete): ?>
    <!-- Hidden form for Siap Cetak (outside main form to avoid nesting) -->
    <form id="formSiapCetak" action="<?= BASEURL; ?>/soalSts/siapCetak/<?= $p['id']; ?>" method="POST"
          onsubmit="return confirm('Tandai soal sebagai Siap Cetak? Soal akan terkunci dari perubahan.')" style="display:none;">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
    </form>
    <?php endif; ?>

    <?php if (!$isLocked): ?>
    <!-- ==================== AI GENERATE MODAL ==================== -->
    <div id="aiModal" class="hidden fixed inset-0 z-50">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm" onclick="aiCloseModal()"></div>

        <!-- Dialog -->
        <div class="relative flex items-center justify-center min-h-full p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden">

                <!-- Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-violet-600 to-indigo-600">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center">
                            <i data-lucide="sparkles" class="w-5 h-5 text-white"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-white leading-tight">Generate Soal dengan AI</h3>
                            <p class="text-xs text-violet-100"><?= htmlspecialchars($p['nama_mapel']); ?> • <?= htmlspecialchars($p['nama_kelas']); ?></p>
                        </div>
                    </div>
                    <button type="button" onclick="aiCloseModal()" class="text-white/80 hover:text-white transition-colors" id="aiCloseBtn">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <!-- Body -->
                <div class="flex-1 overflow-y-auto px-6 py-5" id="aiModalBody">

                    <!-- Config form -->
                    <div id="aiConfigView" class="space-y-5">

                        <!-- Struktur soal info -->
                        <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Struktur Soal</p>
                            <div class="flex flex-wrap gap-2">
                                <?php if ($jumlahPG > 0): ?>
                                <span class="inline-flex items-center gap-1.5 bg-white border border-gray-200 rounded-lg px-3 py-1.5 text-sm text-gray-700">
                                    <i data-lucide="list-checks" class="w-3.5 h-3.5 text-blue-500"></i>
                                    <?= $jumlahPG; ?> Pilihan Ganda
                                </span>
                                <?php endif; ?>
                                <?php if ($jumlahIsian > 0): ?>
                                <span class="inline-flex items-center gap-1.5 bg-white border border-gray-200 rounded-lg px-3 py-1.5 text-sm text-gray-700">
                                    <i data-lucide="text-cursor-input" class="w-3.5 h-3.5 text-teal-500"></i>
                                    <?= $jumlahIsian; ?> Isian Singkat
                                </span>
                                <?php endif; ?>
                                <?php if ($jumlahEssay > 0): ?>
                                <span class="inline-flex items-center gap-1.5 bg-white border border-gray-200 rounded-lg px-3 py-1.5 text-sm text-gray-700">
                                    <i data-lucide="file-text" class="w-3.5 h-3.5 text-purple-500"></i>
                                    <?= $jumlahEssay; ?> Essay
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Materi -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Materi / Topik <span class="text-gray-400 font-normal">(opsional)</span>
                            </label>
                            <textarea id="aiMateri" rows="3"
                                      placeholder="Contoh: Bilangan berpangkat, bentuk akar, dan notasi ilmiah. Atau biarkan kosong agar AI menentukan sendiri sesuai kurikulum."
                                      class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-violet-500 focus:border-violet-500 resize-none placeholder:text-gray-400"></textarea>
                            <p class="text-xs text-gray-400 mt-1.5 flex items-center gap-1">
                                <i data-lucide="info" class="w-3 h-3"></i>
                                Kosongkan untuk biarkan AI memilih materi sesuai Kurikulum Merdeka.
                            </p>
                        </div>

                        <!-- Tipe soal -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Soal yang Digenerate</label>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                <label class="relative flex items-center justify-center gap-2 border rounded-xl px-3 py-2.5 cursor-pointer transition-all has-[:checked]:border-violet-500 has-[:checked]:bg-violet-50 has-[:checked]:text-violet-700 border-gray-200 text-gray-600 hover:border-gray-300">
                                    <input type="radio" name="aiTipe" value="all" class="sr-only" checked>
                                    <i data-lucide="layers" class="w-4 h-4"></i>
                                    <span class="text-sm font-medium">Semua</span>
                                </label>
                                <?php if ($jumlahPG > 0): ?>
                                <label class="relative flex items-center justify-center gap-2 border rounded-xl px-3 py-2.5 cursor-pointer transition-all has-[:checked]:border-violet-500 has-[:checked]:bg-violet-50 has-[:checked]:text-violet-700 border-gray-200 text-gray-600 hover:border-gray-300">
                                    <input type="radio" name="aiTipe" value="pg" class="sr-only">
                                    <i data-lucide="list-checks" class="w-4 h-4"></i>
                                    <span class="text-sm font-medium">PG</span>
                                </label>
                                <?php endif; ?>
                                <?php if ($jumlahIsian > 0): ?>
                                <label class="relative flex items-center justify-center gap-2 border rounded-xl px-3 py-2.5 cursor-pointer transition-all has-[:checked]:border-violet-500 has-[:checked]:bg-violet-50 has-[:checked]:text-violet-700 border-gray-200 text-gray-600 hover:border-gray-300">
                                    <input type="radio" name="aiTipe" value="isian" class="sr-only">
                                    <i data-lucide="text-cursor-input" class="w-4 h-4"></i>
                                    <span class="text-sm font-medium">Isian</span>
                                </label>
                                <?php endif; ?>
                                <?php if ($jumlahEssay > 0): ?>
                                <label class="relative flex items-center justify-center gap-2 border rounded-xl px-3 py-2.5 cursor-pointer transition-all has-[:checked]:border-violet-500 has-[:checked]:bg-violet-50 has-[:checked]:text-violet-700 border-gray-200 text-gray-600 hover:border-gray-300">
                                    <input type="radio" name="aiTipe" value="essay" class="sr-only">
                                    <i data-lucide="file-text" class="w-4 h-4"></i>
                                    <span class="text-sm font-medium">Essay</span>
                                </label>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Level Kognitif (HOTS / LOTS) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Level Kognitif Soal
                                <a href="https://www.ruangguru.com/blog/mengenal-tipe-soal-hots" target="_blank" rel="noopener"
                                   class="ml-1 inline-flex items-center gap-0.5 text-xs font-normal text-violet-600 hover:text-violet-800 hover:underline">
                                    <i data-lucide="help-circle" class="w-3 h-3"></i> Apa itu HOTS?
                                </a>
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                <label class="relative flex flex-col gap-1 border rounded-xl px-3 py-2.5 cursor-pointer transition-all has-[:checked]:border-violet-500 has-[:checked]:bg-violet-50 border-gray-200 hover:border-gray-300">
                                    <span class="flex items-center gap-2">
                                        <input type="radio" name="aiLevel" value="lots" class="sr-only">
                                        <i data-lucide="book-open" class="w-4 h-4 text-gray-500"></i>
                                        <span class="text-sm font-medium text-gray-700">LOTS</span>
                                    </span>
                                    <span class="text-[11px] text-gray-500 leading-tight">C1-C3 · hafalan, pemahaman, penerapan dasar</span>
                                </label>
                                <label class="relative flex flex-col gap-1 border rounded-xl px-3 py-2.5 cursor-pointer transition-all has-[:checked]:border-violet-500 has-[:checked]:bg-violet-50 border-gray-200 hover:border-gray-300">
                                    <span class="flex items-center gap-2">
                                        <input type="radio" name="aiLevel" value="campuran" class="sr-only" checked>
                                        <i data-lucide="shuffle" class="w-4 h-4 text-violet-500"></i>
                                        <span class="text-sm font-medium text-gray-700">Campuran</span>
                                    </span>
                                    <span class="text-[11px] text-gray-500 leading-tight">Kombinasi LOTS + HOTS (disarankan)</span>
                                </label>
                                <label class="relative flex flex-col gap-1 border rounded-xl px-3 py-2.5 cursor-pointer transition-all has-[:checked]:border-violet-500 has-[:checked]:bg-violet-50 border-gray-200 hover:border-gray-300">
                                    <span class="flex items-center gap-2">
                                        <input type="radio" name="aiLevel" value="hots" class="sr-only">
                                        <i data-lucide="brain" class="w-4 h-4 text-amber-500"></i>
                                        <span class="text-sm font-medium text-gray-700">HOTS</span>
                                    </span>
                                    <span class="text-[11px] text-gray-500 leading-tight">C4-C6 · analisis, evaluasi, mencipta (berbasis stimulus)</span>
                                </label>
                            </div>
                        </div>

                        <!-- Mode -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Mode Pengisian</label>
                            <div class="space-y-2">
                                <label class="flex items-start gap-3 border rounded-xl px-3.5 py-3 cursor-pointer transition-all has-[:checked]:border-violet-500 has-[:checked]:bg-violet-50 border-gray-200 hover:border-gray-300">
                                    <input type="radio" name="aiMode" value="empty" class="mt-0.5 accent-violet-600" checked>
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">Isi hanya yang kosong</p>
                                        <p class="text-xs text-gray-500">Soal yang sudah diisi tidak akan diubah.</p>
                                    </div>
                                </label>
                                <label class="flex items-start gap-3 border rounded-xl px-3.5 py-3 cursor-pointer transition-all has-[:checked]:border-violet-500 has-[:checked]:bg-violet-50 border-gray-200 hover:border-gray-300">
                                    <input type="radio" name="aiMode" value="overwrite" class="mt-0.5 accent-violet-600">
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">Timpa semua soal</p>
                                        <p class="text-xs text-gray-500">Ganti semua soal yang ada dengan hasil generate baru.</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div id="aiConfigError" class="hidden bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-3.5 py-2.5"></div>
                    </div>

                    <!-- Progress view -->
                    <div id="aiProgressView" class="hidden">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="relative w-9 h-9 flex-shrink-0">
                                <div class="absolute inset-0 rounded-full border-2 border-violet-200"></div>
                                <div class="absolute inset-0 rounded-full border-2 border-violet-600 border-t-transparent animate-spin"></div>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800" id="aiProgressTitle">AI sedang membuat soal...</p>
                                <p class="text-xs text-gray-500" id="aiProgressSub">Mohon tunggu, proses ini bisa memakan waktu 1-3 menit.</p>
                            </div>
                        </div>
                        <div class="bg-gray-900 rounded-xl p-4 max-h-72 overflow-y-auto" id="aiStreamOutput">
                            <pre class="text-xs text-green-400 whitespace-pre-wrap font-mono leading-relaxed" id="aiStreamText"></pre>
                        </div>
                        <p class="text-xs text-gray-400 mt-2" id="aiCharCount">0 karakter diterima</p>
                    </div>

                    <!-- Preview view (sebelum diterapkan ke form) -->
                    <div id="aiPreviewView" class="hidden">
                        <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                            <div class="flex items-center gap-2">
                                <div class="w-9 h-9 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                                    <i data-lucide="eye" class="w-4.5 h-4.5 text-emerald-600"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-800 leading-tight">Pratinjau Hasil Generate</h4>
                                    <p class="text-xs text-gray-500" id="aiPreviewSummary"></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="aiToggleAll(true)"
                                        class="text-xs font-medium text-violet-600 hover:text-violet-800 transition-colors">Pilih semua</button>
                                <span class="text-gray-300">|</span>
                                <button type="button" onclick="aiToggleAll(false)"
                                        class="text-xs font-medium text-gray-500 hover:text-gray-700 transition-colors">Kosongkan</button>
                            </div>
                        </div>

                        <div id="aiPreviewList" class="space-y-2.5 max-h-[26rem] overflow-y-auto pr-1"></div>

                        <p class="text-xs text-gray-400 mt-3 flex items-start gap-1.5">
                            <i data-lucide="info" class="w-3.5 h-3.5 mt-0.5 flex-shrink-0"></i>
                            Hanya soal yang dicentang yang akan dimasukkan ke form. Anda masih bisa mengedit sebelum menyimpan.
                        </p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-2">
                    <button type="button" onclick="aiCloseModal()" id="aiCancelBtn"
                            class="bg-white hover:bg-gray-100 text-gray-700 font-medium py-2 px-4 rounded-lg border border-gray-300 transition-colors">
                        Batal
                    </button>
                    <button type="button" onclick="aiStartGenerate()" id="aiGenerateBtn"
                            class="bg-violet-600 hover:bg-violet-700 text-white font-medium py-2 px-5 rounded-lg flex items-center transition-colors shadow-sm">
                        <i data-lucide="sparkles" class="w-4 h-4 mr-2"></i>
                        Generate
                    </button>
                    <button type="button" onclick="aiBackToConfig()" id="aiBackBtn"
                            class="hidden bg-white hover:bg-gray-100 text-gray-700 font-medium py-2 px-4 rounded-lg border border-gray-300 transition-colors">
                        Kembali
                    </button>
                    <button type="button" onclick="aiApplyPreview()" id="aiApplyBtn"
                            class="hidden bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2 px-5 rounded-lg flex items-center transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                        <i data-lucide="check" class="w-4 h-4 mr-2"></i>
                        Terapkan ke Form
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    (function () {
        const BASEURL = <?= json_encode(BASEURL) ?>;
        const ID_PENGATURAN = <?= json_encode($p['id']) ?>;
        const CSRF = <?= json_encode($_SESSION['csrf_token']) ?>;
        const OPSI_PG = <?= (int)$opsiPG ?>;
        const OPSI_LABELS = ['A', 'B', 'C', 'D', 'E'].slice(0, OPSI_PG);

        const modal = document.getElementById('aiModal');
        const configView = document.getElementById('aiConfigView');
        const progressView = document.getElementById('aiProgressView');
        const previewView = document.getElementById('aiPreviewView');
        const previewList = document.getElementById('aiPreviewList');
        const previewSummary = document.getElementById('aiPreviewSummary');
        const streamText = document.getElementById('aiStreamText');
        const charCount = document.getElementById('aiCharCount');
        const errorBox = document.getElementById('aiConfigError');
        const generateBtn = document.getElementById('aiGenerateBtn');
        const cancelBtn = document.getElementById('aiCancelBtn');
        const backBtn = document.getElementById('aiBackBtn');
        const applyBtn = document.getElementById('aiApplyBtn');
        let isGenerating = false;
        let generatedSoal = [];   // soal hasil generate, menunggu konfirmasi user
        let selectedIndexes = []; // index soal yang dicentang di pratinjau

        function showError(msg) {
            errorBox.textContent = msg;
            errorBox.classList.remove('hidden');
        }

        // Escape HTML untuk mencegah XSS dari konten AI
        function esc(s) {
            return String(s == null ? '' : s)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        }

        function tipeMeta(tipe) {
            const t = (tipe || '').toLowerCase();
            if (t === 'pg')    return { label: 'Pilihan Ganda', color: 'bg-blue-100 text-blue-700',   icon: 'list-checks' };
            if (t === 'isian') return { label: 'Isian Singkat', color: 'bg-teal-100 text-teal-700',   icon: 'text-cursor-input' };
            if (t === 'essay') return { label: 'Essay',         color: 'bg-purple-100 text-purple-700', icon: 'file-text' };
            return { label: t || 'Soal', color: 'bg-gray-100 text-gray-700', icon: 'help-circle' };
        }

        window.aiCloseModal = function () {
            if (isGenerating) {
                if (!confirm('Proses generate sedang berjalan. Tutup paksa?')) return;
                isGenerating = false;
            }
            modal.classList.add('hidden');
            // reset UI state
            configView.classList.remove('hidden');
            progressView.classList.add('hidden');
            previewView.classList.add('hidden');
            generateBtn.classList.remove('hidden');
            cancelBtn.classList.remove('hidden');
            backBtn.classList.add('hidden');
            applyBtn.classList.add('hidden');
            errorBox.classList.add('hidden');
            streamText.textContent = '';
            charCount.textContent = '0 karakter diterima';
        };

        // Kembali ke form konfigurasi (buang hasil generate)
        window.aiBackToConfig = function () {
            generatedSoal = [];
            selectedIndexes = [];
            previewList.innerHTML = '';
            previewView.classList.add('hidden');
            configView.classList.remove('hidden');
            generateBtn.classList.remove('hidden');
            backBtn.classList.add('hidden');
            applyBtn.classList.add('hidden');
            cancelBtn.classList.remove('hidden');
            errorBox.classList.add('hidden');
        };

        // Centang / lepas centang semua soal di pratinjau
        window.aiToggleAll = function (state) {
            const boxes = previewList.querySelectorAll('input[type="checkbox"][data-idx]');
            boxes.forEach(function (cb) { cb.checked = state; });
            updateApplyButton();
        };

        function updateApplyButton() {
            const boxes = previewList.querySelectorAll('input[type="checkbox"][data-idx]');
            const count = Array.from(boxes).filter(function (cb) { return cb.checked; }).length;
            applyBtn.disabled = count === 0;
            applyBtn.innerHTML = '<i data-lucide="check" class="w-4 h-4 mr-2"></i>Terapkan ke Form' +
                (boxes.length ? ` (${count})` : '');
            if (window.lucide) window.lucide.createIcons();
        }
        window.updateApplyButton = updateApplyButton;

        // Bangun kartu pratinjau untuk tiap soal
        function renderPreview(soalArray) {
            previewList.innerHTML = '';
            soalArray.forEach(function (s, idx) {
                const tipe = (s.tipe || '').toLowerCase();
                const meta = tipeMeta(tipe);
                const nomor = s.nomor != null ? s.nomor : (idx + 1);
                const key = `${tipe}_${nomor}`;
                const checked = selectedIndexes.indexOf(idx) !== -1 ? 'checked' : '';

                let body = '';
                if (tipe === 'pg') {
                    let opsiHtml = '<div class="mt-2 space-y-1">';
                    OPSI_LABELS.forEach(function (label) {
                        const val = s[`opsi_${label.toLowerCase()}`];
                        if (val == null || val === '') return;
                        const isKey = String(s.kunci_jawaban || '').toUpperCase() === label;
                        opsiHtml += `<div class="flex items-start gap-2 text-sm ${isKey ? 'text-emerald-700 font-medium' : 'text-gray-600'}">
                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-xs flex-shrink-0 ${isKey ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500'}">${label}</span>
                            <span>${esc(val)}</span>
                        </div>`;
                    });
                    opsiHtml += '</div>';
                    body = opsiHtml;
                } else {
                    const kJawaban = s.kunci_jawaban != null && s.kunci_jawaban !== ''
                        ? `<div class="mt-2 text-sm text-gray-600"><span class="font-medium text-gray-700">Kunci/Pedoman:</span> ${esc(s.kunci_jawaban)}</div>`
                        : '';
                    body = kJawaban;
                }

                const skor = s.skor != null ? s.skor : (tipe === 'essay' ? 10 : (tipe === 'isian' ? 2 : 1));

                const card = document.createElement('label');
                card.className = 'block border border-gray-200 rounded-xl p-3.5 hover:border-violet-300 transition-colors cursor-pointer bg-white has-[:checked]:border-violet-400 has-[:checked]:bg-violet-50/40';
                card.innerHTML = `
                    <div class="flex items-start gap-3">
                        <input type="checkbox" data-idx="${idx}" ${checked}
                               class="mt-1 w-4 h-4 accent-violet-600 flex-shrink-0"
                               onchange="updateApplyButton()">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1.5">
                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-full ${meta.color}">
                                    <i data-lucide="${meta.icon}" class="w-3 h-3"></i>${meta.label}
                                </span>
                                <span class="text-xs text-gray-400">No. ${nomor}</span>
                                <span class="text-xs text-gray-400 ml-auto">Skor: ${skor}</span>
                            </div>
                            <p class="text-sm text-gray-800 leading-snug">${esc(s.pertanyaan)}</p>
                            ${body}
                        </div>
                    </div>`;
                previewList.appendChild(card);
            });
            if (window.lucide) window.lucide.createIcons();
            updateApplyButton();
        }

        // Terapkan soal yang dipilih ke form
        window.aiApplyPreview = function () {
            const boxes = previewList.querySelectorAll('input[type="checkbox"][data-idx]');
            const chosen = [];
            boxes.forEach(function (cb) {
                if (cb.checked) chosen.push(generatedSoal[parseInt(cb.dataset.idx, 10)]);
            });
            if (!chosen.length) return;
            const filled = populateSoal(chosen);

            modal.classList.add('hidden');
            // reset modal state
            configView.classList.remove('hidden');
            progressView.classList.add('hidden');
            previewView.classList.add('hidden');
            generateBtn.classList.remove('hidden');
            backBtn.classList.add('hidden');
            applyBtn.classList.add('hidden');
            cancelBtn.classList.remove('hidden');

            // Beri notifikasi kecil ke user
            const notif = document.getElementById('inlineNotif');
            const notifMsg = document.getElementById('inlineNotifMsg');
            if (notif && notifMsg) {
                notifMsg.textContent = `${filled} soal dari AI diterapkan ke form. Periksa kembali lalu klik "Simpan" untuk menyimpan ke database.`;
                notif.classList.remove('hidden');
                if (window.lucide) window.lucide.createIcons();
                notif.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        };

        function setValue(name, value) {
            const el = document.querySelector(`[name="${name}"]`);
            if (!el) return false;
            el.value = value == null ? '' : value;
            // trigger input event so any listeners update
            el.dispatchEvent(new Event('input', { bubbles: true }));
            return true;
        }

        function setSelectValue(name, value) {
            const el = document.querySelector(`[name="${name}"]`);
            if (!el) return false;
            el.value = value == null ? '' : String(value).toUpperCase();
            el.dispatchEvent(new Event('change', { bubbles: true }));
            return true;
        }

        function populateSoal(soalArray) {
            let filled = 0;
            soalArray.forEach(function (s) {
                const tipe = (s.tipe || '').toLowerCase();
                const nomor = parseInt(s.nomor, 10);
                if (!tipe || !nomor) return;
                const key = `${tipe}_${nomor}`;

                if (setValue(`soal[${key}][pertanyaan]`, s.pertanyaan)) filled++;

                if (tipe === 'pg') {
                    let opsiCount = 0;
                    OPSI_LABELS.forEach(function (label) {
                        const field = `soal[${key}][opsi_${label.toLowerCase()}]`;
                        const val = s[`opsi_${label.toLowerCase()}`];
                        if (val != null && val !== '') opsiCount++;
                        setValue(field, val);
                    });
                    setSelectValue(`soal[${key}][kunci_jawaban]`, s.kunci_jawaban);
                    setValue(`soal[${key}][skor]`, s.skor != null ? s.skor : 1);
                } else {
                    setValue(`soal[${key}][kunci_jawaban]`, s.kunci_jawaban);
                    setValue(`soal[${key}][skor]`, s.skor != null ? s.skor : (tipe === 'essay' ? 10 : 2));
                }
            });
            return filled;
        }

        function extractJson(text) {
            if (text == null) throw new Error('empty');
            let t = String(text).trim();

            // 1) Buang markdown code fence (```json ... ``` atau ``` ... ```)
            t = t.replace(/```[a-zA-Z]*\s*/g, '').replace(/```/g, '').trim();

            // 2) Buang BOM / karakter zero-width yang kadang disisipkan model
            t = t.replace(/[\u200B-\u200D\uFEFF]/g, '');

            // 3) Coba parse langsung dulu (paling umum)
            try { return JSON.parse(t); } catch (e) { /* lanjut ke ekstraksi */ }

            // 4) Ekstraksi dengan pemindaian seimbang (balanced scan) dari '{' atau '['
            //    pertama, menghormati string & escape, agar kurung di dalam teks tidak salah potong.
            function balancedSlice(str) {
                const openIdx = str.search(/[{[]/);
                if (openIdx === -1) return null;
                const open = str[openIdx];
                const close = open === '{' ? '}' : ']';
                let depth = 0;
                let inStr = false;
                let esc = false;
                for (let i = openIdx; i < str.length; i++) {
                    const ch = str[i];
                    if (inStr) {
                        if (esc) { esc = false; }
                        else if (ch === '\\') { esc = true; }
                        else if (ch === '"') { inStr = false; }
                        continue;
                    }
                    if (ch === '"') { inStr = true; continue; }
                    if (ch === open) depth++;
                    else if (ch === close) {
                        depth--;
                        if (depth === 0) return str.slice(openIdx, i + 1);
                    }
                }
                return null; // tidak seimbang (kemungkinan terpotong)
            }

            // 5) Coba opsi ekstraksi berurutan:
            //    a. dari '{' pertama (cocok untuk {"soal":[...]})
            //    b. dari '[' pertama (coba kalau objek gagal, mis. model balas array langsung)
            //    c. buang ekor setelah '}' atau ']' terakhir sebagai fallback terakhir
            const candidates = [];
            const objSlice = balancedSlice(t);
            if (objSlice) candidates.push(objSlice);
            if (t.trim().startsWith('[')) candidates.push(t);
            const fbStart = t.search(/[{[]/);
            const fbEnd = t.lastIndexOf('}');
            const fbEndArr = t.lastIndexOf(']');
            const fbEnd2 = Math.max(fbEnd, fbEndArr);
            if (fbStart !== -1 && fbEnd2 > fbStart) candidates.push(t.slice(fbStart, fbEnd2 + 1));

            for (const c of candidates) {
                try { return JSON.parse(c); } catch (e) { /* coba kandidat berikutnya */ }
            }

            // 6) Fallback terakhir: perbaiki koma berlebih / JSON terpotong ringan
            try {
                const cleaned = objSlice
                    ? objSlice.replace(/,\s*([}\]])/g, '$1')
                    : null;
                if (cleaned) return JSON.parse(cleaned);
            } catch (e) { /* menyerah */ }

            throw new Error('invalid-json');
        }

        window.aiStartGenerate = async function () {
            errorBox.classList.add('hidden');

            const materi = document.getElementById('aiMateri').value.trim();
            const tipeRadio = document.querySelector('input[name="aiTipe"]:checked');
            const modeRadio = document.querySelector('input[name="aiMode"]:checked');
            const levelRadio = document.querySelector('input[name="aiLevel"]:checked');
            const tipeGenerate = tipeRadio ? tipeRadio.value : 'all';
            const mode = modeRadio ? modeRadio.value : 'empty';
            const levelKognitif = levelRadio ? levelRadio.value : 'campuran';

            isGenerating = true;
            configView.classList.add('hidden');
            progressView.classList.remove('hidden');
            generateBtn.classList.add('hidden');
            cancelBtn.classList.add('hidden');
            streamText.textContent = '';
            charCount.textContent = '0 karakter diterima';

            try {
                // 1) Prepare di PHP
                const fd = new FormData();
                fd.append('csrf_token', CSRF);
                fd.append('id_pengaturan', ID_PENGATURAN);
                fd.append('materi', materi);
                fd.append('tipe_generate', tipeGenerate);
                fd.append('mode', mode);
                fd.append('level_kognitif', levelKognitif);

                const prepRes = await fetch(`${BASEURL}/soalSts/prepareGenerateAI`, {
                    method: 'POST',
                    body: fd,
                });
                const prep = await prepRes.json();
                if (!prep.success) throw new Error(prep.error || 'Gagal menyiapkan generate');

                // 2) Stream dari Go backend
                const ac = new AbortController();
                let idleTimer = null;
                const resetIdle = () => {
                    clearTimeout(idleTimer);
                    idleTimer = setTimeout(() => ac.abort(), 180000);
                };
                resetIdle();

                const streamRes = await fetch(`${BASEURL.replace(/\/$/, '')}/api/v1/ai/stream`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-HMAC-Token': prep.hmac_token,
                    },
                    body: JSON.stringify({
                        system_prompt: prep.system_prompt,
                        messages: prep.messages,
                    }),
                    signal: ac.signal,
                });

                if (!streamRes.ok) {
                    const errData = await streamRes.json().catch(() => ({}));
                    throw new Error(errData.error || `HTTP ${streamRes.status}`);
                }

                const reader = streamRes.body.getReader();
                const decoder = new TextDecoder();
                let buffer = '';
                let fullContent = '';
                let lastRender = 0;

                while (true) {
                    const { done, value } = await reader.read();
                    if (done) break;
                    resetIdle();

                    buffer += decoder.decode(value, { stream: true });
                    const lines = buffer.split('\n');
                    buffer = lines.pop();

                    for (const line of lines) {
                        if (!line.startsWith('data: ')) continue;
                        const data = line.slice(6);
                        if (data === '[DONE]') continue;
                        try {
                            const ev = JSON.parse(data);
                            if (ev.error) throw new Error(ev.error);
                            if (ev.content) {
                                fullContent += ev.content;
                                const now = Date.now();
                                if (now - lastRender > 80) {
                                    lastRender = now;
                                    streamText.textContent = fullContent;
                                    const out = document.getElementById('aiStreamOutput');
                                    out.scrollTop = out.scrollHeight;
                                    charCount.textContent = `${fullContent.length} karakter diterima`;
                                }
                            }
                        } catch (e) {
                            if (e instanceof SyntaxError) continue;
                            throw e;
                        }
                    }
                }
                clearTimeout(idleTimer);

                // 3) Parse & populate
                let parsed;
                try {
                    parsed = extractJson(fullContent);
                } catch (e) {
                    console.error('[AI Generate] Gagal parse JSON. Raw output:', fullContent);
                    throw new Error('AI tidak mengembalikan JSON yang valid. Coba lagi (atau klik Generate ulang).');
                }

                const soalArray = Array.isArray(parsed) ? parsed : (parsed.soal || []);
                if (!soalArray.length) throw new Error('AI tidak menghasilkan soal. Coba lagi dengan materi lebih spesifik.');

                // Simpan hasil & tampilkan PRATINJAU (belum masuk ke form)
                generatedSoal = soalArray;
                selectedIndexes = soalArray.map(function (_, i) { return i; }); // default semua terpilih
                renderPreview(soalArray);

                const counts = soalArray.reduce(function (acc, s) {
                    const t = (s.tipe || '').toLowerCase();
                    acc[t] = (acc[t] || 0) + 1;
                    return acc;
                }, {});
                const parts = [];
                if (counts.pg)    parts.push(`${counts.pg} PG`);
                if (counts.isian) parts.push(`${counts.isian} isian`);
                if (counts.essay) parts.push(`${counts.essay} essay`);
                previewSummary.textContent = `${soalArray.length} soal dibuat (${parts.join(', ')}). Periksa lalu terapkan.`;

                // Update progress ke preview
                progressView.classList.add('hidden');
                previewView.classList.remove('hidden');
                generateBtn.classList.add('hidden');
                cancelBtn.classList.add('hidden');
                backBtn.classList.remove('hidden');
                applyBtn.classList.remove('hidden');

                // Re-render ikon lucide baru
                if (window.lucide) window.lucide.createIcons();

            } catch (e) {
                isGenerating = false;
                configView.classList.remove('hidden');
                progressView.classList.add('hidden');
                generateBtn.classList.remove('hidden');
                cancelBtn.classList.remove('hidden');
                const msg = (e && e.name === 'AbortError')
                    ? 'Koneksi AI timeout — tidak ada respons selama 3 menit.'
                    : (e.message || 'Terjadi kesalahan saat generate soal.');
                showError(msg);
            }
            isGenerating = false;
        };
    })();
    </script>
    <?php endif; ?>
</main>
