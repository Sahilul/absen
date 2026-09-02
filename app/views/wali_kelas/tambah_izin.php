<?php
// File: app/views/wali_kelas/tambah_izin.php
$siswaList = $data['siswa_list'] ?? [];
$waliKelasInfo = $data['wali_kelas_info'] ?? [];
$isEdit = !empty($data['izin']);
$izin = $data['izin'] ?? [];
?>
<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gradient-to-br from-secondary-50 to-secondary-100 p-4 sm:p-6">
    <!-- Breadcrumb -->
    <div class="mb-4 sm:mb-6">
        <nav class="flex items-center space-x-2 text-sm text-secondary-600">
            <a href="<?= BASEURL; ?>/waliKelas/dashboard" class="hover:text-primary-600 transition-colors">Dashboard</a>
            <i data-lucide="chevron-right" class="w-4 h-4"></i>
            <a href="<?= BASEURL; ?>/waliKelas/izinSiswa" class="hover:text-primary-600 transition-colors">Izin Siswa</a>
            <i data-lucide="chevron-right" class="w-4 h-4"></i>
            <span class="text-secondary-800 font-medium"><?= $isEdit ? 'Edit' : 'Tambah' ?> Izin</span>
        </nav>
    </div>

    <!-- Header -->
    <div class="mb-5 sm:mb-8">
        <div class="flex items-center space-x-3 sm:space-x-4">
            <a href="<?= BASEURL; ?>/waliKelas/izinSiswa"
               class="p-2 rounded-xl text-secondary-500 hover:text-primary-600 hover:bg-white/50 transition-all duration-200">
                <i data-lucide="arrow-left" class="w-6 h-6"></i>
            </a>
            <div>
                <h2 class="text-2xl sm:text-3xl font-bold text-secondary-800 flex items-center">
                    <i data-lucide="<?= $isEdit ? 'pencil' : 'plus-circle' ?>" class="w-7 h-7 sm:w-8 sm:h-8 mr-2 sm:mr-3 text-primary-500"></i>
                    <?= $isEdit ? 'Edit' : 'Tambah' ?> Izin Siswa
                </h2>
                <p class="text-secondary-600 mt-1 text-sm sm:text-base">
                    Kelas <?= htmlspecialchars($waliKelasInfo['nama_kelas'] ?? '') ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="glass-effect rounded-xl border border-white/20 shadow-lg p-4 sm:p-6 max-w-2xl">
        <form method="POST" action="<?= BASEURL; ?>/waliKelas/<?= $isEdit ? 'prosesEditIzin' : 'prosesTambahIzin' ?>" id="formIzin">
            <?php if ($isEdit): ?>
                <input type="hidden" name="id_izin" value="<?= $izin['id_izin'] ?>">
            <?php endif; ?>

            <!-- Pilih Siswa -->
            <div class="mb-5">
                <label class="block text-sm font-semibold text-secondary-700 mb-2">
                    <i data-lucide="user" class="w-4 h-4 inline mr-1"></i> Siswa <span class="text-red-500">*</span>
                </label>
                <?php if ($isEdit): ?>
                    <input type="hidden" name="id_siswa" value="<?= $izin['id_siswa'] ?>">
                    <div class="input-modern py-3 bg-secondary-50 text-secondary-700">
                        <?= htmlspecialchars($izin['nama_siswa']) ?> (<?= htmlspecialchars($izin['nisn']) ?>)
                    </div>
                <?php else: ?>
                    <input type="hidden" name="id_siswa" id="selectedSiswaId" value="" required>
                    <div class="relative" id="siswaSearchWrap">
                        <div class="relative">
                            <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-secondary-400 pointer-events-none"></i>
                            <input type="text" id="siswaSearch" autocomplete="off"
                                   class="input-modern py-3 pl-10 pr-10"
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
                    foreach ($jenisOptions as $val => $opt):
                        $checked = ($isEdit && ($izin['jenis_izin'] ?? '') === $val) ? 'checked' : '';
                    ?>
                        <label class="cursor-pointer">
                            <input type="radio" name="jenis_izin" value="<?= $val ?>" <?= $checked ?> required class="sr-only peer">
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
                    <input type="date" name="tanggal_mulai" required class="input-modern py-3"
                           value="<?= $isEdit ? $izin['tanggal_mulai'] : date('Y-m-d') ?>" id="tglMulai">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-secondary-700 mb-2">
                        <i data-lucide="calendar" class="w-4 h-4 inline mr-1"></i> Tanggal Selesai <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="tanggal_selesai" required class="input-modern py-3"
                           value="<?= $isEdit ? $izin['tanggal_selesai'] : date('Y-m-d') ?>" id="tglSelesai">
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
                <textarea name="keterangan" rows="3" required class="input-modern py-3"
                          placeholder="Contoh: Izin mengikuti lomba MTQ tingkat kabupaten"><?= htmlspecialchars($izin['keterangan'] ?? '') ?></textarea>
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-3">
                <button type="submit" class="btn-primary px-6 py-3 flex items-center justify-center gap-2" id="submitBtn">
                    <i data-lucide="save" class="w-5 h-5"></i>
                    <?= $isEdit ? 'Update Izin' : 'Simpan Izin' ?>
                </button>
                <a href="<?= BASEURL; ?>/waliKelas/izinSiswa" class="btn-secondary px-6 py-3 flex items-center justify-center gap-2">
                    <i data-lucide="x" class="w-5 h-5"></i> Batal
                </a>
            </div>
        </form>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof lucide !== 'undefined') lucide.createIcons();

    // --- Searchable siswa dropdown ---
    const searchInput = document.getElementById('siswaSearch');
    const dropdown = document.getElementById('siswaDropdown');
    const hiddenId = document.getElementById('selectedSiswaId');
    const clearBtn = document.getElementById('clearSiswa');
    const emptyMsg = document.getElementById('siswaEmpty');

    if (searchInput) {
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
                searchInput.classList.add('text-secondary-800', 'font-medium');
                dropdown.classList.add('hidden');
                clearBtn.classList.remove('hidden');
            });
        });

        clearBtn.addEventListener('click', () => {
            hiddenId.value = '';
            searchInput.value = '';
            searchInput.classList.remove('text-secondary-800', 'font-medium');
            clearBtn.classList.add('hidden');
            searchInput.focus();
        });

        document.addEventListener('click', (e) => {
            if (!e.target.closest('#siswaSearchWrap')) {
                dropdown.classList.add('hidden');
            }
        });
    }

    // --- Durasi calculator ---
    const tglMulai = document.getElementById('tglMulai');
    const tglSelesai = document.getElementById('tglSelesai');
    const durasiText = document.getElementById('durasiText');

    function updateDurasi() {
        const start = new Date(tglMulai.value);
        const end = new Date(tglSelesai.value);
        if (start && end && end >= start) {
            const diff = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
            durasiText.textContent = 'Durasi: ' + diff + ' hari (' + tglMulai.value + ' s/d ' + tglSelesai.value + ')';
        }
    }

    tglMulai.addEventListener('change', function() {
        if (tglSelesai.value < tglMulai.value) tglSelesai.value = tglMulai.value;
        updateDurasi();
    });
    tglSelesai.addEventListener('change', updateDurasi);
    updateDurasi();

    document.getElementById('formIzin').addEventListener('submit', function(e) {
        if (hiddenId && !hiddenId.value) {
            e.preventDefault();
            searchInput.focus();
            searchInput.classList.add('border-red-500');
            return;
        }
        const btn = document.getElementById('submitBtn');
        btn.innerHTML = '<i data-lucide="loader-2" class="w-5 h-5 animate-spin"></i> Menyimpan...';
        btn.disabled = true;
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });
});
</script>
