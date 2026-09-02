<?php
// File: app/views/admin/tambah_izin.php
$siswaList = $data['siswa_list'] ?? [];
$kelasList = $data['kelas_list'] ?? [];
$selectedKelas = $data['selected_kelas'] ?? 0;
?>
<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gradient-to-br from-secondary-50 to-secondary-100 p-4 sm:p-6">
    <!-- Breadcrumb -->
    <div class="mb-4 sm:mb-6">
        <nav class="flex items-center space-x-2 text-sm text-secondary-600">
            <a href="<?= BASEURL; ?>/admin/dashboard" class="hover:text-primary-600 transition-colors">Dashboard</a>
            <i data-lucide="chevron-right" class="w-4 h-4"></i>
            <a href="<?= BASEURL; ?>/admin/izinSiswa" class="hover:text-primary-600 transition-colors">Izin Siswa</a>
            <i data-lucide="chevron-right" class="w-4 h-4"></i>
            <span class="text-secondary-800 font-medium">Tambah Izin</span>
        </nav>
    </div>

    <!-- Header -->
    <div class="mb-5 sm:mb-8">
        <div class="flex items-center space-x-3 sm:space-x-4">
            <a href="<?= BASEURL; ?>/admin/izinSiswa"
               class="p-2 rounded-xl text-secondary-500 hover:text-primary-600 hover:bg-white/50 transition-all duration-200">
                <i data-lucide="arrow-left" class="w-6 h-6"></i>
            </a>
            <div>
                <h2 class="text-2xl sm:text-3xl font-bold text-secondary-800 flex items-center">
                    <i data-lucide="plus-circle" class="w-7 h-7 sm:w-8 sm:h-8 mr-2 sm:mr-3 text-primary-500"></i>
                    Tambah Izin Siswa
                </h2>
                <p class="text-secondary-600 mt-1 text-sm sm:text-base">Input izin, sakit, atau dispensasi siswa</p>
            </div>
        </div>
    </div>

    <?php Flasher::flash(); ?>

    <!-- Form -->
    <div class="bg-white rounded-xl shadow-sm border p-4 sm:p-6 max-w-2xl">
        <form method="POST" action="<?= BASEURL; ?>/admin/adminProsesTambahIzin" id="formIzin">

            <!-- Pilih Kelas -->
            <div class="mb-5">
                <label class="block text-sm font-semibold text-secondary-700 mb-2">
                    <i data-lucide="school" class="w-4 h-4 inline mr-1"></i> Kelas <span class="text-red-500">*</span>
                </label>
                <select name="id_kelas" id="selectKelas" required
                        class="input-modern py-3 w-full">
                    <option value="">-- Pilih Kelas --</option>
                    <?php foreach ($kelasList as $kls): ?>
                        <option value="<?= $kls['id_kelas'] ?>" <?= $selectedKelas == $kls['id_kelas'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($kls['nama_kelas']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Pilih Siswa -->
            <div class="mb-5" id="siswaSection" style="<?= $selectedKelas ? '' : 'display:none' ?>">
                <label class="block text-sm font-semibold text-secondary-700 mb-2">
                    <i data-lucide="user" class="w-4 h-4 inline mr-1"></i> Siswa <span class="text-red-500">*</span>
                </label>
                <input type="hidden" name="id_siswa" id="selectedSiswaId" value="">
                <div class="relative" id="siswaSearchWrap">
                    <div class="relative">
                        <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-secondary-400 pointer-events-none"></i>
                        <input type="text" id="siswaSearch" autocomplete="off"
                               class="input-modern py-3 pl-10 pr-10 w-full"
                               placeholder="Ketik nama atau NISN siswa...">
                        <button type="button" id="clearSiswa" class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400 hover:text-red-500 hidden">
                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                        </button>
                    </div>
                    <ul id="siswaDropdown" class="absolute z-50 w-full mt-1 bg-white border border-secondary-200 rounded-xl shadow-lg max-h-60 overflow-y-auto hidden">
                        <?php foreach ($siswaList as $i => $s): ?>
                            <li class="siswa-option px-4 py-3 cursor-pointer hover:bg-primary-50 flex items-center gap-3 transition-colors <?= $i > 0 ? 'border-t border-secondary-100' : '' ?>"
                                data-id="<?= $s['id_siswa'] ?>"
                                data-nama="<?= htmlspecialchars($s['nama_siswa']) ?>"
                                data-nisn="<?= htmlspecialchars($s['nisn'] ?? '-') ?>">
                                <div class="w-8 h-8 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center text-xs font-bold flex-shrink-0">
                                    <?= strtoupper(substr($s['nama_siswa'], 0, 1)) ?>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-sm font-medium text-secondary-800 truncate"><?= htmlspecialchars($s['nama_siswa']) ?></div>
                                    <div class="text-xs text-secondary-500">NISN: <?= htmlspecialchars($s['nisn'] ?? '-') ?></div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                        <li id="siswaEmpty" class="px-4 py-3 text-sm text-secondary-400 text-center hidden">Tidak ditemukan</li>
                    </ul>
                </div>
                <?php if ($selectedKelas && empty($siswaList)): ?>
                    <p class="text-sm text-amber-600 mt-2"><i data-lucide="alert-triangle" class="w-4 h-4 inline mr-1"></i>Tidak ada siswa aktif di kelas ini.</p>
                <?php endif; ?>
            </div>

            <!-- Jenis Izin -->
            <div class="mb-5">
                <label class="block text-sm font-semibold text-secondary-700 mb-2">
                    <i data-lucide="tag" class="w-4 h-4 inline mr-1"></i> Jenis <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-3 gap-3">
                    <?php
                    $jenisOptions = [
                        'I' => ['label' => 'Izin', 'icon' => 'info', 'color' => 'blue'],
                        'S' => ['label' => 'Sakit', 'icon' => 'thermometer', 'color' => 'yellow'],
                        'D' => ['label' => 'Dispensasi', 'icon' => 'shield-check', 'color' => 'purple'],
                    ];
                    foreach ($jenisOptions as $val => $opt): ?>
                        <label class="cursor-pointer">
                            <input type="radio" name="jenis_izin" value="<?= $val ?>" required class="sr-only peer">
                            <div class="flex flex-col items-center gap-2 p-4 rounded-xl border-2 border-secondary-200
                                        peer-checked:border-<?= $opt['color'] ?>-500 peer-checked:bg-<?= $opt['color'] ?>-50
                                        hover:border-<?= $opt['color'] ?>-300 transition-all">
                                <i data-lucide="<?= $opt['icon'] ?>" class="w-6 h-6 text-<?= $opt['color'] ?>-600"></i>
                                <span class="text-sm font-medium text-secondary-700"><?= $opt['label'] ?></span>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Tanggal -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                <div>
                    <label class="block text-sm font-semibold text-secondary-700 mb-2">
                        <i data-lucide="calendar" class="w-4 h-4 inline mr-1"></i> Tanggal Mulai <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="tanggal_mulai" required class="input-modern py-3 w-full"
                           value="<?= date('Y-m-d') ?>" id="tglMulai">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-secondary-700 mb-2">
                        <i data-lucide="calendar" class="w-4 h-4 inline mr-1"></i> Tanggal Selesai <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="tanggal_selesai" required class="input-modern py-3 w-full"
                           value="<?= date('Y-m-d') ?>" id="tglSelesai">
                </div>
            </div>

            <!-- Info durasi -->
            <div class="mb-5 p-3 bg-blue-50 rounded-lg border border-blue-200 text-sm text-blue-700" id="infoDurasi">
                <i data-lucide="info" class="w-4 h-4 inline mr-1"></i>
                <span id="durasiText">Durasi: 1 hari</span>
            </div>

            <!-- Keterangan -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-secondary-700 mb-2">
                    <i data-lucide="file-text" class="w-4 h-4 inline mr-1"></i> Keterangan <span class="text-red-500">*</span>
                </label>
                <textarea name="keterangan" rows="3" required class="input-modern py-3 w-full"
                          placeholder="Contoh: Izin mengikuti lomba MTQ tingkat kabupaten"></textarea>
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-3">
                <button type="submit" class="btn-primary px-6 py-3 flex items-center justify-center gap-2" id="submitBtn">
                    <i data-lucide="save" class="w-5 h-5"></i> Simpan Izin
                </button>
                <a href="<?= BASEURL; ?>/admin/izinSiswa" class="btn-secondary px-6 py-3 flex items-center justify-center gap-2">
                    <i data-lucide="x" class="w-5 h-5"></i> Batal
                </a>
            </div>
        </form>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof lucide !== 'undefined') lucide.createIcons();

    // --- Kelas change → reload page with siswa list ---
    const selectKelas = document.getElementById('selectKelas');
    selectKelas.addEventListener('change', function() {
        const val = this.value;
        if (val) {
            window.location.href = '<?= BASEURL; ?>/admin/adminTambahIzin?kelas=' + val;
        } else {
            document.getElementById('siswaSection').style.display = 'none';
        }
    });

    // --- Searchable siswa dropdown ---
    const searchInput = document.getElementById('siswaSearch');
    const dropdown = document.getElementById('siswaDropdown');
    const hiddenId = document.getElementById('selectedSiswaId');
    const clearBtn = document.getElementById('clearSiswa');
    const emptyMsg = document.getElementById('siswaEmpty');

    if (searchInput && dropdown) {
        const options = dropdown.querySelectorAll('.siswa-option');

        searchInput.addEventListener('focus', () => {
            dropdown.classList.remove('hidden');
            filterOptions();
        });

        searchInput.addEventListener('input', filterOptions);

        function filterOptions() {
            const q = searchInput.value.toLowerCase().trim();
            let found = 0;
            options.forEach(li => {
                const nama = li.dataset.nama.toLowerCase();
                const nisn = li.dataset.nisn.toLowerCase();
                const match = !q || nama.includes(q) || nisn.includes(q);
                li.classList.toggle('hidden', !match);
                if (match) found++;
            });
            emptyMsg.classList.toggle('hidden', found > 0);
        }

        options.forEach(li => {
            li.addEventListener('click', () => {
                hiddenId.value = li.dataset.id;
                searchInput.value = li.dataset.nama + ' (' + li.dataset.nisn + ')';
                searchInput.classList.add('bg-green-50', 'border-green-300');
                dropdown.classList.add('hidden');
                clearBtn.classList.remove('hidden');
            });
        });

        clearBtn.addEventListener('click', () => {
            hiddenId.value = '';
            searchInput.value = '';
            searchInput.classList.remove('bg-green-50', 'border-green-300');
            clearBtn.classList.add('hidden');
            searchInput.focus();
        });

        document.addEventListener('click', (e) => {
            if (!document.getElementById('siswaSearchWrap').contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
    }

    // --- Durasi calculator ---
    const tglMulai = document.getElementById('tglMulai');
    const tglSelesai = document.getElementById('tglSelesai');
    const durasiText = document.getElementById('durasiText');

    function updateDurasi() {
        if (tglMulai.value && tglSelesai.value) {
            const d1 = new Date(tglMulai.value);
            const d2 = new Date(tglSelesai.value);
            const diff = Math.round((d2 - d1) / (1000 * 60 * 60 * 24)) + 1;
            durasiText.textContent = diff > 0 ? 'Durasi: ' + diff + ' hari' : 'Tanggal tidak valid';
        }
    }
    tglMulai.addEventListener('change', updateDurasi);
    tglSelesai.addEventListener('change', updateDurasi);

    // --- Form validation ---
    document.getElementById('formIzin').addEventListener('submit', function(e) {
        if (!hiddenId || !hiddenId.value) {
            e.preventDefault();
            alert('Pilih siswa terlebih dahulu.');
            if (searchInput) searchInput.focus();
        }
    });
});
</script>
