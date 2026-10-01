<?php
$kelasList = $data['kelas_list'] ?? [];
$siswaList = $data['siswa_list'] ?? [];
$guruBKList = $data['guru_bk_list'] ?? [];
$selectedKelas = $data['selected_kelas'] ?? '';
?>
<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gradient-to-br from-secondary-50 to-secondary-100 p-4 sm:p-6">
    <div class="mb-6">
        <a href="<?= BASEURL; ?>/bk/daftarKasus" class="text-sm text-primary-600 hover:underline flex items-center gap-1 mb-2">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali
        </a>
        <h2 class="text-2xl sm:text-3xl font-bold text-secondary-800 flex items-center">
            <i data-lucide="plus-circle" class="w-7 h-7 sm:w-8 sm:h-8 mr-2 sm:mr-3 text-rose-500"></i>
            Tambah Kasus BK
        </h2>
    </div>

    <?php Flasher::flash(); ?>

    <div class="bg-white rounded-xl shadow-sm border overflow-hidden max-w-3xl">
        <div class="px-4 sm:px-6 py-4 border-b bg-gray-50">
            <h3 class="text-sm font-bold text-secondary-800">Data Kasus</h3>
        </div>
        <form method="POST" action="<?= BASEURL; ?>/bk/prosesTambahKasus" class="p-4 sm:p-6 space-y-5">
            <!-- Pilih Kelas -->
            <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Kelas <span class="text-red-500">*</span></label>
                <select name="id_kelas" id="selectKelas" required
                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                        onchange="window.location.href='<?= BASEURL; ?>/bk/tambahKasus?kelas='+this.value">
                    <option value="">-- Pilih Kelas --</option>
                    <?php foreach ($kelasList as $kls): ?>
                        <option value="<?= $kls['id_kelas'] ?>" <?= $selectedKelas == $kls['id_kelas'] ? 'selected' : '' ?>><?= htmlspecialchars($kls['nama_kelas']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Pilih Siswa -->
            <?php if (!empty($siswaList)): ?>
            <div x-data="{
                open: false,
                search: '',
                selected: null,
                selectedName: '',
                siswa: <?= htmlspecialchars(json_encode(array_map(function($s) {
                    return ['id' => $s['id_siswa'], 'nama' => $s['nama_siswa'], 'nisn' => $s['nisn']];
                }, $siswaList)), ENT_QUOTES, 'UTF-8') ?>,
                get filtered() {
                    if (!this.search) return this.siswa;
                    const q = this.search.toLowerCase();
                    return this.siswa.filter(s => s.nama.toLowerCase().includes(q) || s.nisn.includes(q));
                },
                pick(s) {
                    this.selected = s.id;
                    this.selectedName = s.nama + ' (' + s.nisn + ')';
                    this.search = '';
                    this.open = false;
                },
                clear() {
                    this.selected = null;
                    this.selectedName = '';
                    this.search = '';
                }
            }" @click.outside="open = false" class="relative">
                <label class="block text-sm font-medium text-secondary-700 mb-1">Siswa <span class="text-red-500">*</span></label>
                <input type="hidden" name="id_siswa" :value="selected" required>
                <div @click="open = !open; $nextTick(() => { if(open) $refs.searchInput.focus() })"
                     class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 cursor-pointer flex items-center justify-between focus-within:ring-2 focus-within:ring-primary-500 focus-within:border-primary-500 bg-white"
                     :class="open ? 'ring-2 ring-primary-500 border-primary-500' : ''">
                    <span x-show="selected" x-text="selectedName" class="text-secondary-800 truncate"></span>
                    <span x-show="!selected" class="text-gray-400">-- Pilih Siswa --</span>
                    <div class="flex items-center gap-1 flex-shrink-0 ml-2">
                        <button type="button" x-show="selected" @click.stop="clear()" class="text-gray-400 hover:text-red-500 p-0.5">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                        </button>
                        <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''"></i>
                    </div>
                </div>
                <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                     class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden">
                    <div class="p-2 border-b">
                        <div class="relative">
                            <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2"></i>
                            <input type="text" x-model="search" @click.stop placeholder="Cari nama atau NISN..."
                                   x-ref="searchInput" @keydown.escape="open = false"
                                   class="w-full text-sm border border-gray-200 rounded-md pl-8 pr-3 py-2 focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                    </div>
                    <ul class="max-h-48 overflow-y-auto py-1">
                        <template x-for="s in filtered" :key="s.id">
                            <li @click="pick(s)"
                                class="px-3 py-2 text-sm cursor-pointer hover:bg-primary-50 flex items-center justify-between"
                                :class="selected === s.id ? 'bg-primary-50 text-primary-700 font-medium' : 'text-secondary-700'">
                                <span x-text="s.nama"></span>
                                <span class="text-xs text-gray-400 ml-2" x-text="s.nisn"></span>
                            </li>
                        </template>
                        <li x-show="filtered.length === 0" class="px-3 py-2 text-sm text-gray-400 text-center">Tidak ditemukan</li>
                    </ul>
                </div>
            </div>
            <?php elseif ($selectedKelas): ?>
            <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Siswa <span class="text-red-500">*</span></label>
                <select disabled class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 bg-gray-50">
                    <option>-- Pilih Siswa --</option>
                </select>
                <p class="text-xs text-amber-600 mt-1">Tidak ada siswa di kelas ini</p>
            </div>
            <?php else: ?>
            <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Siswa <span class="text-red-500">*</span></label>
                <select disabled class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 bg-gray-50">
                    <option>-- Pilih kelas dulu --</option>
                </select>
            </div>
            <?php endif; ?>

            <!-- Kategori -->
            <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Kategori <span class="text-red-500">*</span></label>
                <select name="kategori" required class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="pelanggaran">Pelanggaran</option>
                    <option value="akademik">Akademik</option>
                    <option value="pribadi">Pribadi</option>
                    <option value="sosial">Sosial</option>
                    <option value="karir">Karir</option>
                </select>
            </div>

            <!-- Judul -->
            <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Judul Kasus <span class="text-red-500">*</span></label>
                <input type="text" name="judul" required maxlength="255" placeholder="Ringkasan singkat kasus..."
                       class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>

            <!-- Deskripsi -->
            <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Deskripsi</label>
                <textarea name="deskripsi" rows="4" placeholder="Jelaskan kronologi atau detail kasus..."
                          class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"></textarea>
            </div>

            <!-- Tingkat & Poin -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-secondary-700 mb-1">Tingkat <span class="text-red-500">*</span></label>
                    <select name="tingkat" required class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="ringan">Ringan</option>
                        <option value="sedang">Sedang</option>
                        <option value="berat">Berat</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-secondary-700 mb-1">Poin Pelanggaran</label>
                    <input type="number" name="poin" value="0" min="0" max="100"
                           class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="btn-primary px-6 py-2.5 text-sm rounded-xl flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i> Simpan Kasus
                </button>
                <a href="<?= BASEURL; ?>/bk/daftarKasus" class="px-6 py-2.5 text-sm text-secondary-600 hover:text-secondary-800 border border-gray-200 rounded-xl">
                    Batal
                </a>
            </div>
        </form>
    </div>
</main>
