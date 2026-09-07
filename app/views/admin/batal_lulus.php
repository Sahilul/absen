<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Pembatalan Kelulusan Siswa</h2>
            <p class="text-sm text-gray-500 mt-1">Kembalikan status siswa yang telah lulus menjadi <strong>Aktif</strong> kembali</p>
        </div>
        <a href="<?= BASEURL; ?>/admin/kelulusan" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 shadow-sm transition">
            <i data-lucide="graduation-cap" class="w-4 h-4 mr-2 text-gray-500"></i>
            Menu Kelulusan
        </a>
    </div>

    <?php Flasher::flash(); ?>

    <div class="bg-white rounded-xl shadow-md p-6">
        <div class="mb-6 border-b pb-6">
            <p class="font-semibold text-gray-700">Pilih Kelas Asal Siswa</p>
            <p class="text-sm text-gray-500">Pilih tahun pelajaran dan kelas untuk menyaring daftar siswa yang berstatus lulus.</p>
            
            <form action="<?= BASEURL; ?>/admin/batalLulus" method="POST" class="mt-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tahun Ajaran</label>
                        <select name="id_tp" id="tp_batal_lulus" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-amber-500 focus:border-amber-500" required>
                            <option value="">-- Pilih --</option>
                            <?php foreach ($data['daftar_tp'] as $tp) : ?>
                                <option value="<?= $tp['id_tp']; ?>" <?= (isset($data['id_tp_pilihan']) && $data['id_tp_pilihan'] == $tp['id_tp']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($tp['nama_tp']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kelas</label>
                        <select name="id_kelas" id="kelas_batal_lulus" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-amber-500 focus:border-amber-500" required>
                            <option value="">-- Pilih TP Dulu --</option>
                        </select>
                    </div>
                    <button type="submit" name="tampilkan_siswa" value="true" class="bg-amber-600 hover:bg-amber-700 text-white font-semibold py-2 px-4 rounded-lg shadow-sm transition">
                        Tampilkan Siswa
                    </button>
                </div>
            </form>
        </div>

        <?php if (isset($data['daftar_siswa'])) : ?>
            <?php 
                $total_siswa = count($data['daftar_siswa']);
                $total_lulus = 0;
                $total_aktif = 0;
                foreach ($data['daftar_siswa'] as $s) {
                    if (($s['status_siswa'] ?? 'aktif') === 'lulus') {
                        $total_lulus++;
                    } else {
                        $total_aktif++;
                    }
                }
            ?>
        <form action="<?= BASEURL; ?>/admin/prosesBatalLulus" method="POST">
            <input type="hidden" name="id_tp" value="<?= htmlspecialchars($data['id_tp_pilihan'] ?? ''); ?>">
            <input type="hidden" name="id_kelas" value="<?= htmlspecialchars($data['id_kelas_pilihan'] ?? ''); ?>">

            <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 gap-2">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">Daftar Siswa di Kelas Terpilih</h3>
                    <p class="text-xs text-gray-500">
                        Total: <span class="font-medium text-gray-700"><?= $total_siswa; ?></span> siswa | 
                        Berstatus Lulus: <span class="font-semibold text-green-700"><?= $total_lulus; ?></span> | 
                        Aktif: <span class="font-medium text-blue-600"><?= $total_aktif; ?></span>
                    </p>
                </div>
                <?php if ($total_lulus > 0): ?>
                    <span class="text-xs bg-amber-50 text-amber-800 px-3 py-1.5 rounded-md border border-amber-200">
                        Centang siswa berstatus <strong>Lulus</strong> yang ingin dikembalikan menjadi <strong>Aktif</strong>
                    </span>
                <?php else: ?>
                    <span class="text-xs bg-gray-50 text-gray-600 px-3 py-1.5 rounded-md border border-gray-200">
                        Tidak ada siswa berstatus Lulus di kelas ini
                    </span>
                <?php endif; ?>
            </div>

            <div class="overflow-auto max-h-[500px] border border-gray-200 rounded-lg shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 bg-white">
                    <thead class="bg-gray-50 sticky top-0 z-10">
                        <tr>
                            <th class="p-3 text-center w-12">
                                <input type="checkbox" id="select_all_batal" class="rounded border-gray-300 text-amber-600 focus:ring-amber-500" <?= $total_lulus === 0 ? 'disabled' : ''; ?> title="Pilih semua siswa yang lulus">
                            </th>
                            <th class="p-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nama Siswa</th>
                            <th class="p-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-36">NISN</th>
                            <th class="p-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-32">Status Sekarang</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (empty($data['daftar_siswa'])) : ?>
                            <tr><td colspan="4" class="p-8 text-center text-gray-500">Tidak ada siswa di kelas ini.</td></tr>
                        <?php else : ?>
                            <?php foreach ($data['daftar_siswa'] as $siswa) : ?>
                                <?php $isLulus = (($siswa['status_siswa'] ?? 'aktif') === 'lulus'); ?>
                                <tr class="hover:bg-gray-50 transition-colors <?= $isLulus ? 'bg-amber-50/30' : 'opacity-60'; ?>">
                                    <td class="p-3 text-center">
                                        <?php if ($isLulus) : ?>
                                            <input type="checkbox" name="siswa_terpilih[]" value="<?= $siswa['id_siswa']; ?>" class="siswa-batal-checkbox rounded border-gray-300 text-amber-600 focus:ring-amber-500 cursor-pointer">
                                        <?php else : ?>
                                            <input type="checkbox" disabled class="rounded border-gray-300 opacity-40 cursor-not-allowed" title="Siswa ini sudah aktif">
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-3 text-sm font-medium text-gray-800">
                                        <?= htmlspecialchars($siswa['nama_siswa']); ?>
                                    </td>
                                    <td class="p-3 text-sm text-gray-500">
                                        <?= htmlspecialchars($siswa['nisn'] ?? '-'); ?>
                                    </td>
                                    <td class="p-3 text-center">
                                        <?php if ($isLulus) : ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                                Lulus
                                            </span>
                                        <?php else : ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">
                                                Aktif
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-6 pt-4 border-t border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-3">
                <span class="text-xs text-gray-500" id="selected_batal_count_label">0 siswa terpilih</span>
                <button type="submit" id="btn_submit_batal" class="w-full sm:w-auto bg-amber-600 hover:bg-amber-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold py-2.5 px-5 rounded-lg shadow-sm transition flex items-center justify-center" <?= $total_lulus === 0 ? 'disabled' : ''; ?> onclick="return confirm('Kembalikan status siswa yang dipilih menjadi Aktif?');">
                    <i data-lucide="rotate-ccw" class="w-4 h-4 mr-2"></i>
                    Batalkan Kelulusan Siswa Terpilih
                </button>
            </div>
        </form>
        <?php endif; ?>
    </div>
</main>

<script>
    const tpSelect = document.getElementById('tp_batal_lulus');
    const kelasSelect = document.getElementById('kelas_batal_lulus');
    const selectAllCheckbox = document.getElementById('select_all_batal');
    const selectedCountLabel = document.getElementById('selected_batal_count_label');

    const selectedKelasId = "<?= $data['id_kelas_pilihan'] ?? ''; ?>";

    function loadKelas(id_tp, selectedId = null) {
        if (!id_tp) {
            kelasSelect.innerHTML = '<option value="">-- Pilih TP Dulu --</option>';
            return;
        }

        kelasSelect.innerHTML = '<option>Memuat kelas...</option>';
        fetch(`<?= BASEURL; ?>/admin/getKelasByTP/${id_tp}`)
            .then(response => response.json())
            .then(data => {
                kelasSelect.innerHTML = '<option value="">-- Pilih Kelas --</option>';
                if (data && data.length > 0) {
                    data.forEach(kelas => {
                        const isSelected = selectedId && (kelas.id_kelas == selectedId);
                        kelasSelect.innerHTML += `<option value="${kelas.id_kelas}" ${isSelected ? 'selected' : ''}>${kelas.nama_kelas}</option>`;
                    });
                } else {
                    kelasSelect.innerHTML = '<option value="">-- Tidak ada kelas --</option>';
                }
            })
            .catch(() => {
                kelasSelect.innerHTML = '<option value="">-- Gagal memuat kelas --</option>';
            });
    }

    tpSelect.addEventListener('change', function() {
        loadKelas(this.value);
    });

    if (tpSelect.value) {
        loadKelas(tpSelect.value, selectedKelasId);
    }

    function updateSelectedCount() {
        const checkedCount = document.querySelectorAll('.siswa-batal-checkbox:checked').length;
        if (selectedCountLabel) {
            selectedCountLabel.textContent = checkedCount + ' siswa terpilih';
        }
    }

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.siswa-batal-checkbox:not(:disabled)');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateSelectedCount();
        });
    }

    document.querySelectorAll('.siswa-batal-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
            const allCheckboxes = document.querySelectorAll('.siswa-batal-checkbox:not(:disabled)');
            const checkedBoxes = document.querySelectorAll('.siswa-batal-checkbox:checked');
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = (allCheckboxes.length > 0 && allCheckboxes.length === checkedBoxes.length);
            }
            updateSelectedCount();
        });
    });
</script>