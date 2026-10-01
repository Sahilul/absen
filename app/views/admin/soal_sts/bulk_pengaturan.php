<!-- admin/soal_sts/bulk_pengaturan.php - Bulk Pengaturan Soal STS -->
<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
    <!-- Flash Message -->
    <?php Flasher::flash(); ?>

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 space-y-4 sm:space-y-0">
        <div>
            <nav class="flex items-center text-sm text-gray-500 mb-2">
                <a href="<?= BASEURL; ?>/soalSts" class="hover:text-indigo-600">Soal STS</a>
                <i data-lucide="chevron-right" class="w-4 h-4 mx-1"></i>
                <span class="text-gray-700 font-medium">Bulk Pengaturan</span>
            </nav>
            <h2 class="text-2xl font-bold text-gray-800">Bulk Pengaturan Soal</h2>
            <p class="text-gray-600 mt-1">Atur pengaturan soal untuk beberapa kelas sekaligus</p>
        </div>
        <a href="<?= BASEURL; ?>/soalSts"
           class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg flex items-center transition-colors duration-200">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
            Kembali
        </a>
    </div>

    <!-- Tab: Bulk Set / Copy -->
    <div x-data="{ tab: 'bulk' }">
        <div class="flex space-x-1 bg-gray-200 rounded-xl p-1 mb-6 max-w-md">
            <button type="button" @click="tab = 'bulk'"
                    :class="tab === 'bulk' ? 'bg-white shadow text-indigo-700' : 'text-gray-600 hover:text-gray-800'"
                    class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all duration-200 flex items-center justify-center gap-2">
                <i data-lucide="layers" class="w-4 h-4"></i>
                Atur Baru (Bulk)
            </button>
            <button type="button" @click="tab = 'copy'"
                    :class="tab === 'copy' ? 'bg-white shadow text-violet-700' : 'text-gray-600 hover:text-gray-800'"
                    class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all duration-200 flex items-center justify-center gap-2">
                <i data-lucide="copy" class="w-4 h-4"></i>
                Copy dari Kelas Lain
            </button>
        </div>

        <!-- ==================== TAB: BULK SET ==================== -->
        <div x-show="tab === 'bulk'" x-transition>
            <form action="<?= BASEURL; ?>/soalSts/prosessBulkSet" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Kolom Kiri: Pilih Kelas -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                            <div class="p-4 bg-indigo-50 border-b flex items-center justify-between">
                                <div>
                                    <h3 class="text-sm font-semibold text-indigo-800">
                                        <i data-lucide="school" class="w-4 h-4 inline mr-1"></i>
                                        Pilih Kelas
                                    </h3>
                                    <p class="text-xs text-indigo-600 mt-0.5">Centang kelas yang ingin diatur pengaturan soalnya</p>
                                </div>
                                <div class="flex gap-2">
                                    <button type="button" onclick="selectAllKelas(true)"
                                            class="text-xs bg-indigo-100 hover:bg-indigo-200 text-indigo-700 px-3 py-1 rounded-lg transition-colors">
                                        Pilih Semua
                                    </button>
                                    <button type="button" onclick="selectAllKelas(false)"
                                            class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-600 px-3 py-1 rounded-lg transition-colors">
                                        Hapus Semua
                                    </button>
                                </div>
                            </div>
                            <div class="p-4">
                                <?php foreach ($data['kelas_grouped'] as $jenjang => $kelasList): ?>
                                    <div class="mb-4 last:mb-0" x-data="{ expanded: true }">
                                        <div class="flex items-center justify-between mb-2 cursor-pointer" @click="expanded = !expanded">
                                            <h4 class="text-sm font-semibold text-gray-700 flex items-center">
                                                <span class="bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded text-xs mr-2">Kelas <?= $jenjang; ?></span>
                                                <span class="text-gray-400 text-xs">(<?= count($kelasList); ?> kelas)</span>
                                            </h4>
                                            <div class="flex items-center gap-2">
                                                <button type="button" onclick="event.stopPropagation(); selectJenjang('<?= $jenjang; ?>', true)"
                                                        class="text-xs text-indigo-600 hover:text-indigo-800">Pilih semua</button>
                                                <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400 transition-transform" :class="expanded ? 'rotate-180' : ''"></i>
                                            </div>
                                        </div>
                                        <div x-show="expanded" x-transition class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                                            <?php foreach ($kelasList as $kelas): ?>
                                                <label class="flex items-center gap-2 p-2.5 rounded-lg border cursor-pointer transition-all duration-200 hover:bg-indigo-50 hover:border-indigo-300 has-[:checked]:bg-indigo-50 has-[:checked]:border-indigo-400">
                                                    <input type="checkbox" name="kelas_ids[]" value="<?= $kelas['id_kelas']; ?>"
                                                           class="kelas-checkbox jenjang-<?= $jenjang; ?> rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                    <div class="flex-1 min-w-0">
                                                        <span class="text-sm font-medium text-gray-800 block truncate"><?= htmlspecialchars($kelas['nama_kelas']); ?></span>
                                                        <?php if ($kelas['has_pengaturan']): ?>
                                                            <span class="text-xs text-green-600">✓ <?= $kelas['jumlah_pengaturan']; ?> mapel</span>
                                                        <?php else: ?>
                                                            <span class="text-xs text-gray-400">Belum diatur</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom Kanan: Pengaturan -->
                    <div>
                        <div class="bg-white rounded-xl shadow-sm border overflow-hidden sticky top-6">
                            <div class="p-4 bg-indigo-50 border-b">
                                <h3 class="text-sm font-semibold text-indigo-800">
                                    <i data-lucide="settings" class="w-4 h-4 inline mr-1"></i>
                                    Pengaturan Soal
                                </h3>
                                <p class="text-xs text-indigo-600 mt-0.5">Berlaku untuk semua mapel di kelas terpilih</p>
                            </div>
                            <div class="p-4 space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Pilihan Ganda</label>
                                    <input type="number" name="jumlah_pg" min="0" max="100" value="20"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Isian Singkat</label>
                                    <input type="number" name="jumlah_isian" min="0" max="50" value="0"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Essay</label>
                                    <input type="number" name="jumlah_essay" min="0" max="50" value="5"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Opsi Pilihan Ganda</label>
                                    <select name="opsi_pg"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="4" selected>A - D (4 opsi)</option>
                                        <option value="5">A - E (5 opsi)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Waktu Pengerjaan (menit)</label>
                                    <input type="number" name="waktu" min="10" max="300" value="60"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Petunjuk Umum</label>
                                    <textarea name="petunjuk" rows="3"
                                              placeholder="Contoh: Pilihlah jawaban yang paling tepat..."
                                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                                </div>

                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                                    <p class="text-xs text-yellow-700">
                                        <i data-lucide="alert-triangle" class="w-3 h-3 inline mr-1"></i>
                                        <strong>Perhatian:</strong> Pengaturan ini akan diterapkan ke <strong>semua mapel</strong> di kelas yang dipilih. Jika kelas sudah punya pengaturan, nilainya akan di-<em>update</em>.
                                    </p>
                                </div>

                                <button type="submit"
                                        onclick="return confirm('Terapkan pengaturan ini ke semua kelas yang dipilih?')"
                                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 px-4 rounded-lg flex items-center justify-center transition-colors duration-200 shadow-sm">
                                    <i data-lucide="check" class="w-4 h-4 mr-2"></i>
                                    Terapkan ke Kelas Terpilih
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- ==================== TAB: COPY ==================== -->
        <div x-show="tab === 'copy'" x-transition>
            <form action="<?= BASEURL; ?>/soalSts/prosessCopyPengaturan" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Kolom Kiri: Pilih Kelas Tujuan -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                            <div class="p-4 bg-violet-50 border-b flex items-center justify-between">
                                <div>
                                    <h3 class="text-sm font-semibold text-violet-800">
                                        <i data-lucide="arrow-right-circle" class="w-4 h-4 inline mr-1"></i>
                                        Kelas Tujuan
                                    </h3>
                                    <p class="text-xs text-violet-600 mt-0.5">Centang kelas yang ingin menerima salinan pengaturan</p>
                                </div>
                                <div class="flex gap-2">
                                    <button type="button" onclick="selectAllCopy(true)"
                                            class="text-xs bg-violet-100 hover:bg-violet-200 text-violet-700 px-3 py-1 rounded-lg transition-colors">
                                        Pilih Semua
                                    </button>
                                    <button type="button" onclick="selectAllCopy(false)"
                                            class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-600 px-3 py-1 rounded-lg transition-colors">
                                        Hapus Semua
                                    </button>
                                </div>
                            </div>
                            <div class="p-4">
                                <?php foreach ($data['kelas_grouped'] as $jenjang => $kelasList): ?>
                                    <div class="mb-4 last:mb-0" x-data="{ expanded: true }">
                                        <div class="flex items-center justify-between mb-2 cursor-pointer" @click="expanded = !expanded">
                                            <h4 class="text-sm font-semibold text-gray-700 flex items-center">
                                                <span class="bg-violet-100 text-violet-700 px-2 py-0.5 rounded text-xs mr-2">Kelas <?= $jenjang; ?></span>
                                                <span class="text-gray-400 text-xs">(<?= count($kelasList); ?> kelas)</span>
                                            </h4>
                                            <div class="flex items-center gap-2">
                                                <button type="button" onclick="event.stopPropagation(); selectCopyJenjang('<?= $jenjang; ?>', true)"
                                                        class="text-xs text-violet-600 hover:text-violet-800">Pilih semua</button>
                                                <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400 transition-transform" :class="expanded ? 'rotate-180' : ''"></i>
                                            </div>
                                        </div>
                                        <div x-show="expanded" x-transition class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                                            <?php foreach ($kelasList as $kelas): ?>
                                                <label class="flex items-center gap-2 p-2.5 rounded-lg border cursor-pointer transition-all duration-200 hover:bg-violet-50 hover:border-violet-300 has-[:checked]:bg-violet-50 has-[:checked]:border-violet-400">
                                                    <input type="checkbox" name="to_kelas_ids[]" value="<?= $kelas['id_kelas']; ?>"
                                                           class="copy-checkbox copy-jenjang-<?= $jenjang; ?> rounded border-gray-300 text-violet-600 focus:ring-violet-500">
                                                    <div class="flex-1 min-w-0">
                                                        <span class="text-sm font-medium text-gray-800 block truncate"><?= htmlspecialchars($kelas['nama_kelas']); ?></span>
                                                        <?php if ($kelas['has_pengaturan']): ?>
                                                            <span class="text-xs text-green-600">✓ <?= $kelas['jumlah_pengaturan']; ?> mapel</span>
                                                        <?php else: ?>
                                                            <span class="text-xs text-gray-400">Belum diatur</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom Kanan: Pilih Kelas Sumber -->
                    <div>
                        <div class="bg-white rounded-xl shadow-sm border overflow-hidden sticky top-6">
                            <div class="p-4 bg-violet-50 border-b">
                                <h3 class="text-sm font-semibold text-violet-800">
                                    <i data-lucide="copy" class="w-4 h-4 inline mr-1"></i>
                                    Kelas Sumber
                                </h3>
                                <p class="text-xs text-violet-600 mt-0.5">Pilih kelas yang pengaturannya ingin dicopy</p>
                            </div>
                            <div class="p-4 space-y-2">
                                <?php
                                $kelasWithPengaturan = array_filter($data['kelas_list'], function ($k) {
                                    return $k['has_pengaturan'];
                                });
                                ?>
                                <?php if (empty($kelasWithPengaturan)): ?>
                                    <div class="text-center py-6">
                                        <i data-lucide="inbox" class="w-8 h-8 text-gray-300 mx-auto mb-2"></i>
                                        <p class="text-sm text-gray-500">Belum ada kelas yang punya pengaturan</p>
                                        <p class="text-xs text-gray-400 mt-1">Atur pengaturan di satu kelas dulu, baru bisa copy</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($kelasWithPengaturan as $kelas): ?>
                                        <label class="flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-all duration-200 hover:bg-violet-50 hover:border-violet-300 has-[:checked]:bg-violet-50 has-[:checked]:border-violet-400 has-[:checked]:ring-2 has-[:checked]:ring-violet-200">
                                            <input type="radio" name="from_kelas" value="<?= $kelas['id_kelas']; ?>"
                                                   class="text-violet-600 focus:ring-violet-500">
                                            <div class="flex-1">
                                                <span class="text-sm font-medium text-gray-800"><?= htmlspecialchars($kelas['nama_kelas']); ?></span>
                                                <span class="text-xs text-green-600 block"><?= $kelas['jumlah_pengaturan']; ?> mapel sudah diatur</span>
                                            </div>
                                        </label>
                                    <?php endforeach; ?>

                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mt-4">
                                        <p class="text-xs text-blue-700">
                                            <i data-lucide="info" class="w-3 h-3 inline mr-1"></i>
                                            Pengaturan (jumlah PG, Essay, Isian, opsi, waktu, petunjuk) akan dicopy ke kelas tujuan. Jika kelas tujuan sudah punya pengaturan, nilainya akan di-<em>update</em>.
                                        </p>
                                    </div>

                                    <button type="submit"
                                            onclick="return confirm('Copy pengaturan ke semua kelas tujuan yang dipilih?')"
                                            class="w-full mt-4 bg-violet-600 hover:bg-violet-700 text-white font-medium py-2.5 px-4 rounded-lg flex items-center justify-center transition-colors duration-200 shadow-sm">
                                        <i data-lucide="copy" class="w-4 h-4 mr-2"></i>
                                        Copy Pengaturan
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
// Bulk Set helpers
function selectAllKelas(checked) {
    document.querySelectorAll('.kelas-checkbox').forEach(cb => cb.checked = checked);
}
function selectJenjang(jenjang, checked) {
    document.querySelectorAll('.jenjang-' + jenjang).forEach(cb => cb.checked = checked);
}

// Copy helpers
function selectAllCopy(checked) {
    document.querySelectorAll('.copy-checkbox').forEach(cb => cb.checked = checked);
}
function selectCopyJenjang(jenjang, checked) {
    document.querySelectorAll('.copy-jenjang-' + jenjang).forEach(cb => cb.checked = checked);
}
</script>
