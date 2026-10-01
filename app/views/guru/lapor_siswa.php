<?php
$laporanList = $data['laporan_list'] ?? [];
$stats = $data['stats'] ?? [];
$filters = $data['filters'] ?? [];
$currentStatus = $filters['status'] ?? '';
$kelasList = $data['kelas_list'] ?? [];
$siswaList = $data['siswa_list'] ?? [];
$selectedKelas = $data['selected_kelas'] ?? '';
$showForm = $data['show_form'] ?? false;
?>
<style>[x-cloak] { display: none !important; }</style>
<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gradient-to-br from-secondary-50 to-secondary-100 p-4 sm:p-6"
      x-data="{ showForm: <?= $showForm ? 'true' : 'false' ?> }">
    <div class="mb-6">
        <a href="<?= BASEURL; ?>/guru/dashboard" class="text-sm text-primary-600 hover:underline flex items-center gap-1 mb-2">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali
        </a>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-2xl sm:text-3xl font-bold text-secondary-800 flex items-center">
                    <i data-lucide="alert-triangle" class="w-7 h-7 sm:w-8 sm:h-8 mr-2 sm:mr-3 text-amber-500"></i>
                    Laporan BK
                </h2>
                <p class="text-secondary-600 mt-1 text-sm">Daftar laporan siswa yang pernah Anda kirim ke BK</p>
            </div>
            <button @click="showForm = !showForm"
                    class="px-4 py-2.5 text-sm rounded-xl flex items-center gap-2 w-fit transition-all duration-200"
                    :class="showForm ? 'border border-gray-300 text-secondary-700 hover:bg-gray-50' : 'btn-primary'">
                <template x-if="!showForm">
                    <span class="flex items-center gap-2"><i data-lucide="plus" class="w-4 h-4"></i> Tambah Laporan</span>
                </template>
                <template x-if="showForm">
                    <span class="flex items-center gap-2"><i data-lucide="x" class="w-4 h-4"></i> Tutup Form</span>
                </template>
            </button>
        </div>
    </div>

    <?php Flasher::flash(); ?>

    <!-- Form Tambah Laporan (collapsible) -->
    <div x-show="showForm"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         x-cloak class="mb-6">
        <div class="bg-white rounded-xl shadow-sm border overflow-hidden max-w-3xl">
            <div class="px-4 sm:px-6 py-4 border-b bg-amber-50">
                <div class="flex items-center gap-2">
                    <i data-lucide="info" class="w-4 h-4 text-amber-600 flex-shrink-0"></i>
                    <p class="text-sm text-amber-800">Laporan akan masuk ke antrian BK dengan status <strong>Baru</strong>. Guru BK akan mengambil dan menangani kasus ini.</p>
                </div>
            </div>

            <form method="POST" action="<?= BASEURL; ?>/guru/prosesLaporSiswa" class="p-4 sm:p-6 space-y-5">
                <div>
                    <label class="block text-sm font-medium text-secondary-700 mb-1">Kelas <span class="text-red-500">*</span></label>
                    <select name="id_kelas" id="select-kelas" required
                            onchange="window.location.href='<?= BASEURL; ?>/guru/laporSiswa?tambah&kelas='+this.value"
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">-- Pilih Kelas --</option>
                        <?php foreach ($kelasList as $k): ?>
                            <option value="<?= $k['id_kelas'] ?>" <?= ($selectedKelas == $k['id_kelas']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($k['nama_kelas']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (empty($kelasList)): ?>
                        <p class="text-xs text-red-500 mt-1">Anda belum memiliki penugasan kelas di semester ini.</p>
                    <?php endif; ?>
                </div>

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
                    <input type="hidden" name="id_siswa" :value="selected" x-ref="hiddenInput">
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
                                    <span><span x-text="s.nama"></span> <span class="text-secondary-400" x-text="'(' + s.nisn + ')'"></span></span>
                                    <i x-show="selected === s.id" data-lucide="check" class="w-4 h-4 text-primary-500"></i>
                                </li>
                            </template>
                            <li x-show="filtered.length === 0" class="px-3 py-3 text-sm text-secondary-400 text-center">Tidak ditemukan</li>
                        </ul>
                    </div>
                </div>

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

                <div>
                    <label class="block text-sm font-medium text-secondary-700 mb-1">Judul Laporan <span class="text-red-500">*</span></label>
                    <input type="text" name="judul" required maxlength="255" placeholder="Ringkasan singkat kasus..."
                           class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-secondary-700 mb-1">Deskripsi / Kronologi</label>
                    <textarea name="deskripsi" rows="4" placeholder="Jelaskan kronologi atau detail kejadian..."
                              class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-secondary-700 mb-1">Tingkat</label>
                        <select name="tingkat" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
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
                        <i data-lucide="send" class="w-4 h-4"></i> Laporkan ke BK
                    </button>
                    <button type="button" @click="showForm = false" class="px-6 py-2.5 text-sm text-secondary-600 hover:text-secondary-800 border border-gray-200 rounded-xl">
                        Batal
                    </button>
                </div>
                <?php else: ?>
                    <?php if ($selectedKelas): ?>
                        <p class="text-sm text-secondary-500 py-4">Tidak ada siswa di kelas ini.</p>
                    <?php else: ?>
                        <p class="text-sm text-secondary-500 py-4">Pilih kelas terlebih dahulu untuk menampilkan daftar siswa.</p>
                    <?php endif; ?>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        <div class="bg-white rounded-xl p-4 border shadow-sm">
            <div class="text-2xl font-bold text-secondary-800"><?= $stats['total'] ?? 0 ?></div>
            <div class="text-xs text-secondary-500 mt-1">Total Laporan</div>
        </div>
        <a href="<?= BASEURL; ?>/guru/laporSiswa?status=baru" class="bg-white rounded-xl p-4 border shadow-sm hover:border-blue-300 transition-colors <?= $currentStatus === 'baru' ? 'ring-2 ring-blue-400' : '' ?>">
            <div class="text-2xl font-bold text-blue-600"><?= $stats['baru'] ?? 0 ?></div>
            <div class="text-xs text-secondary-500 mt-1">Menunggu</div>
        </a>
        <a href="<?= BASEURL; ?>/guru/laporSiswa?status=proses" class="bg-white rounded-xl p-4 border shadow-sm hover:border-amber-300 transition-colors <?= $currentStatus === 'proses' ? 'ring-2 ring-amber-400' : '' ?>">
            <div class="text-2xl font-bold text-amber-600"><?= $stats['proses'] ?? 0 ?></div>
            <div class="text-xs text-secondary-500 mt-1">Diproses</div>
        </a>
        <a href="<?= BASEURL; ?>/guru/laporSiswa?status=selesai" class="bg-white rounded-xl p-4 border shadow-sm hover:border-green-300 transition-colors <?= $currentStatus === 'selesai' ? 'ring-2 ring-green-400' : '' ?>">
            <div class="text-2xl font-bold text-green-600"><?= $stats['selesai'] ?? 0 ?></div>
            <div class="text-xs text-secondary-500 mt-1">Selesai</div>
        </a>
    </div>

    <?php if ($currentStatus): ?>
        <div class="mb-4">
            <a href="<?= BASEURL; ?>/guru/laporSiswa" class="text-sm text-primary-600 hover:underline flex items-center gap-1">
                <i data-lucide="x" class="w-3 h-3"></i> Hapus filter: <?= ucfirst($currentStatus) ?>
            </a>
        </div>
    <?php endif; ?>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <?php if (empty($laporanList)): ?>
            <div class="p-8 text-center">
                <i data-lucide="inbox" class="w-12 h-12 text-secondary-300 mx-auto mb-3"></i>
                <p class="text-secondary-500 text-sm">Belum ada laporan<?= $currentStatus ? ' dengan status "' . ucfirst($currentStatus) . '"' : '' ?>.</p>
                <button @click="showForm = true" class="text-primary-600 hover:underline text-sm mt-2 inline-block">Buat laporan baru</button>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="text-left px-4 py-3 font-semibold text-secondary-600">Tanggal</th>
                            <th class="text-left px-4 py-3 font-semibold text-secondary-600">Siswa</th>
                            <th class="text-left px-4 py-3 font-semibold text-secondary-600 hidden sm:table-cell">Kelas</th>
                            <th class="text-left px-4 py-3 font-semibold text-secondary-600">Judul</th>
                            <th class="text-left px-4 py-3 font-semibold text-secondary-600 hidden md:table-cell">Kategori</th>
                            <th class="text-left px-4 py-3 font-semibold text-secondary-600">Status</th>
                            <th class="text-left px-4 py-3 font-semibold text-secondary-600 hidden lg:table-cell">Ditangani</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php foreach ($laporanList as $l): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 text-secondary-600 whitespace-nowrap">
                                    <?= date('d/m/Y', strtotime($l['created_at'])) ?>
                                </td>
                                <td class="px-4 py-3 font-medium text-secondary-800">
                                    <?= htmlspecialchars($l['nama_siswa']) ?>
                                </td>
                                <td class="px-4 py-3 text-secondary-600 hidden sm:table-cell">
                                    <?= htmlspecialchars($l['nama_kelas']) ?>
                                </td>
                                <td class="px-4 py-3 text-secondary-700 max-w-[200px] truncate">
                                    <?= htmlspecialchars($l['judul']) ?>
                                </td>
                                <td class="px-4 py-3 hidden md:table-cell">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                        <?php
                                        switch ($l['kategori']) {
                                            case 'pelanggaran': echo 'bg-red-100 text-red-700'; break;
                                            case 'akademik': echo 'bg-blue-100 text-blue-700'; break;
                                            case 'pribadi': echo 'bg-purple-100 text-purple-700'; break;
                                            case 'sosial': echo 'bg-teal-100 text-teal-700'; break;
                                            case 'karir': echo 'bg-amber-100 text-amber-700'; break;
                                            default: echo 'bg-gray-100 text-gray-700';
                                        }
                                        ?>">
                                        <?= ucfirst($l['kategori']) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                        <?php
                                        switch ($l['status']) {
                                            case 'baru': echo 'bg-blue-100 text-blue-700'; break;
                                            case 'proses': echo 'bg-amber-100 text-amber-700'; break;
                                            case 'selesai': echo 'bg-green-100 text-green-700'; break;
                                            case 'dirujuk': echo 'bg-red-100 text-red-700'; break;
                                            default: echo 'bg-gray-100 text-gray-700';
                                        }
                                        ?>">
                                        <?= ucfirst($l['status']) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-secondary-600 hidden lg:table-cell">
                                    <?= $l['nama_guru_bk'] ? htmlspecialchars($l['nama_guru_bk']) : '<span class="text-secondary-400 italic">Belum diambil</span>' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>
