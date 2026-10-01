<!-- admin/soal_sts/input_soal.php - Form Input Soal STS -->
<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
    <!-- Flash Message -->
    <?php Flasher::flash(); ?>

    <?php
    $p = $data['pengaturan'];
    $soalMap = $data['soal_map'];
    $jumlahPG = (int)$p['jumlah_pilihan_ganda'];
    $jumlahEssay = (int)$p['jumlah_essay'];
    $jumlahIsian = (int)$p['jumlah_isian_singkat'];
    $opsiPG = (int)$p['opsi_pg'];
    $opsiLabels = ['A', 'B', 'C', 'D', 'E'];
    $isAdmin = ($_SESSION['role'] ?? '') === 'admin';
    $backUrl = $isAdmin ? BASEURL . '/soalSts/pengaturan/' . $p['id_kelas'] : BASEURL . '/guru';
    ?>

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 space-y-4 sm:space-y-0">
        <div>
            <nav class="flex items-center text-sm text-gray-500 mb-2">
                <?php if ($isAdmin): ?>
                    <a href="<?= BASEURL; ?>/soalSts" class="hover:text-indigo-600">Soal STS</a>
                    <i data-lucide="chevron-right" class="w-4 h-4 mx-1"></i>
                    <a href="<?= BASEURL; ?>/soalSts/pengaturan/<?= $p['id_kelas']; ?>" class="hover:text-indigo-600">Pengaturan</a>
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

    <!-- Form Soal -->
    <form action="<?= BASEURL; ?>/soalSts/simpanSemuaSoal" method="POST" enctype="multipart/form-data" id="formSoal">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
        <input type="hidden" name="id_pengaturan" value="<?= $p['id']; ?>">

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
                                    <div class="mb-2">
                                        <img src="<?= soalGambarUrl($existing['gambar_soal']); ?>"
                                             class="max-h-32 rounded border" alt="Gambar soal">
                                        <input type="hidden" name="soal[<?= $key; ?>][gambar_existing]" value="<?= htmlspecialchars($existing['gambar_soal']); ?>">
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
                                        <div class="mb-2">
                                            <img src="<?= soalGambarUrl($existing['gambar_soal']); ?>"
                                                 class="max-h-32 rounded border" alt="Gambar soal">
                                            <input type="hidden" name="soal[<?= $key; ?>][gambar_existing]" value="<?= htmlspecialchars($existing['gambar_soal']); ?>">
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
                                        <div class="mb-2">
                                            <img src="<?= soalGambarUrl($existing['gambar_soal']); ?>"
                                                 class="max-h-32 rounded border" alt="Gambar soal">
                                            <input type="hidden" name="soal[<?= $key; ?>][gambar_existing]" value="<?= htmlspecialchars($existing['gambar_soal']); ?>">
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

        <!-- Submit Button (sticky bottom) -->
        <div class="sticky bottom-0 bg-white border-t shadow-lg p-4 -mx-6 -mb-6 flex justify-between items-center">
            <p class="text-sm text-gray-500">
                <i data-lucide="info" class="w-4 h-4 inline mr-1"></i>
                Soal yang sudah diisi akan otomatis tersimpan. Soal kosong akan dilewati.
            </p>
            <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 px-6 rounded-lg flex items-center transition-colors duration-200 shadow-sm">
                <i data-lucide="save" class="w-4 h-4 mr-2"></i>
                Simpan Semua Soal
            </button>
        </div>
    </form>
</main>
