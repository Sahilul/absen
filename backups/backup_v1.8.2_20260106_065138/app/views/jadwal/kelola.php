<?php
// File: app/views/jadwal/kelola.php
$kelasList = $data['kelas_list'] ?? [];
$jamList = $data['jam_list'] ?? [];
$tp = $data['tp_aktif'] ?? null;
$pengaturan = $data['pengaturan'] ?? [];

$hariAktif = explode(',', $pengaturan['hari_aktif'] ?? 'Senin,Selasa,Rabu,Kamis,Jumat,Sabtu');

// Filter jam aktif (bukan istirahat)
$jamAktif = array_values(array_filter($jamList, fn($j) => !$j['is_istirahat']));

// Helper untuk sorting kelas berdasarkan romawi
function romanToIntKelola($roman)
{
    $romans = [
        'VII' => 7,
        'VIII' => 8,
        'IX' => 9,
        'X' => 10,
        'XI' => 11,
        'XII' => 12,
        'I' => 1,
        'II' => 2,
        'III' => 3,
        'IV' => 4,
        'V' => 5,
        'VI' => 6
    ];
    foreach ($romans as $r => $v) {
        if (strpos($roman, $r) === 0)
            return $v;
    }
    return 999;
}
usort($kelasList, function ($a, $b) {
    return romanToIntKelola($a['nama_kelas']) - romanToIntKelola($b['nama_kelas']);
});
?>

<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-4">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Kelola Jadwal Pelajaran</h2>
            <p class="text-gray-600 text-sm">
                <?php if ($tp): ?>
                    <?= htmlspecialchars($tp['tahun_pelajaran']); ?> | Klik header hari untuk atur jumlah jam
                <?php else: ?>
                    <span class="text-red-500">Tahun pelajaran tidak ditemukan!</span>
                <?php endif; ?>
            </p>
        </div>
        <div class="flex gap-2 mt-2 sm:mt-0">
            <!-- Buttons -->
            <a href="<?= BASEURL; ?>/jadwal/cetakJadwal" target="_blank"
                class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-3 py-1 rounded-lg flex items-center gap-1 transition h-[30px]"
                title="Cetak Jadwal">
                <i data-lucide="printer" class="w-4 h-4"></i> <span class="hidden sm:inline">Cetak</span>
            </a>
            <button onclick="confirmResetJadwal()"
                class="bg-red-600 hover:bg-red-700 text-white text-sm px-3 py-1 rounded-lg flex items-center gap-1 transition h-[30px]"
                title="Reset Jadwal">
                <i data-lucide="refresh-ccw" class="w-4 h-4"></i> <span class="hidden sm:inline">Reset</span>
            </button>
            <div class="w-[1px] bg-gray-300 mx-1"></div>
            <!-- Filter -->
            <select id="filterKelas"
                class="text-sm border rounded-lg px-2 py-1 h-[30px] focus:ring-2 focus:ring-indigo-500">
                <option value="">Semua Kelas</option>
                <?php foreach ($kelasList as $k): ?>
                    <option value="<?= $k['id_kelas']; ?>"><?= htmlspecialchars($k['nama_kelas']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <?php if (class_exists('Flasher'))
        Flasher::flash(); ?>

    <?php if (empty($kelasList)): ?>
        <div class="bg-white rounded-lg shadow-sm border p-8 text-center text-gray-500">
            <p>Belum ada data kelas</p>
        </div>
    <?php elseif (empty($jamAktif)): ?>
        <div class="bg-white rounded-lg shadow-sm border p-8 text-center text-gray-500">
            <p>Jam pelajaran belum diatur</p>
            <a href="<?= BASEURL; ?>/jadwal/jamPelajaran" class="text-indigo-600 hover:underline">Atur jam pelajaran</a>
        </div>
    <?php else: ?>
        <!-- Master Schedule - Per Kelas -->
        <div class="space-y-4" id="scheduleContainer">
            <?php foreach ($kelasList as $kelas): ?>
                <div class="bg-white rounded-lg shadow-sm border overflow-hidden kelas-card"
                    data-kelas="<?= $kelas['id_kelas']; ?>">
                    <div class="bg-gradient-to-r from-indigo-500 to-purple-500 px-4 py-2">
                        <h3 class="font-bold text-white"><?= htmlspecialchars($kelas['nama_kelas']); ?></h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-[10px]" id="table-<?= $kelas['id_kelas']; ?>">
                            <thead class="bg-gray-50">
                                <tr>
                                    <?php foreach ($hariAktif as $hari): ?>
                                        <th class="px-1 py-2 text-center font-medium text-gray-600 border-r min-w-[90px]"
                                            data-kelas="<?= $kelas['id_kelas']; ?>" data-hari="<?= $hari; ?>">
                                            <div class="flex items-center justify-center gap-1 mb-1">
                                                <button onclick="removeHariJam(<?= $kelas['id_kelas']; ?>, '<?= $hari; ?>')"
                                                    class="w-5 h-5 bg-red-100 hover:bg-red-200 text-red-600 rounded font-bold text-sm flex items-center justify-center"
                                                    title="Kurangi">−</button>
                                                <span class="text-[10px] font-semibold"><?= $hari; ?></span>
                                                <button onclick="addHariJam(<?= $kelas['id_kelas']; ?>, '<?= $hari; ?>')"
                                                    class="w-5 h-5 bg-green-100 hover:bg-green-200 text-green-600 rounded font-bold text-sm flex items-center justify-center"
                                                    title="Tambah Jam">+</button>
                                                <button onclick="addIstirahat(<?= $kelas['id_kelas']; ?>, '<?= $hari; ?>')"
                                                    class="w-5 h-5 bg-amber-100 hover:bg-amber-200 text-amber-600 rounded font-bold text-[9px] flex items-center justify-center"
                                                    title="Tambah Istirahat">☕</button>
                                            </div>
                                        </th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody id="body-<?= $kelas['id_kelas']; ?>">
                                <!-- Rows will be generated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<!-- Modal for editing schedule -->
<div id="scheduleModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="px-4 py-3 border-b bg-indigo-600 rounded-t-lg">
            <h3 class="text-lg font-semibold text-white" id="modalTitle">Edit Jadwal</h3>
        </div>
        <form id="scheduleForm" class="p-4 space-y-4">
            <input type="hidden" id="inputKelas" name="id_kelas">
            <input type="hidden" id="inputJam" name="id_jam">
            <input type="hidden" id="inputHari" name="hari">
            <input type="hidden" id="inputJadwal" name="id_jadwal">

            <div id="noMapelMessage"
                class="hidden bg-yellow-50 border border-yellow-200 rounded-lg p-3 text-yellow-700 text-sm">
                <i data-lucide="alert-triangle" class="w-4 h-4 inline mr-1"></i>
                Tidak ada mapel yang ditugaskan untuk kelas ini. Silakan atur penugasan terlebih dahulu.
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mata Pelajaran</label>
                <select id="selectMapel" name="id_mapel"
                    class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" required>
                    <option value="">-- Pilih --</option>
                </select>
            </div>

            <div id="guruInfo" class="hidden">
                <label class="block text-sm font-medium text-gray-700 mb-1">Guru Pengampu</label>
                <div class="bg-gray-50 rounded-lg px-3 py-2 text-sm">
                    <span id="guruName" class="font-medium text-indigo-600"></span>
                </div>
                <input type="hidden" id="inputGuru" name="id_guru">
            </div>

            <div id="errorMessage" class="hidden bg-red-50 border border-red-200 rounded-lg p-3 text-red-700 text-sm">
            </div>

            <div class="flex gap-2 pt-2">
                <button type="button" onclick="closeModal()"
                    class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition">
                    Batal
                </button>
                <button type="button" onclick="kosongkanJam()" id="btnKosongkan"
                    class="hidden bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    🗑️ Kosongkan
                </button>
                <button type="submit" id="btnSubmit"
                    class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const jamAktif = <?= json_encode($jamAktif); ?>;
    const hariAktif = <?= json_encode($hariAktif); ?>;
    const kelasList = <?= json_encode($kelasList); ?>;
    const pengaturan = <?= json_encode($pengaturan); ?>;
    let allSchedules = {};
    let currentPenugasan = [];

    // Initialize on load
    document.addEventListener('DOMContentLoaded', function () {
        initializeScheduleGrid();
        loadAllSchedules();

        // Filter functionality
        document.getElementById('filterKelas').addEventListener('change', function () {
            const selectedKelas = this.value;
            document.querySelectorAll('.kelas-card').forEach(card => {
                if (!selectedKelas || card.dataset.kelas === selectedKelas) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            });
        });
    });

    function initializeScheduleGrid() {
        kelasList.forEach(kelas => {
            const tbody = document.getElementById(`body-${kelas.id_kelas}`);
            if (!tbody) return;

            // Get max jam for this kelas (default 8)
            const maxJam = 8;

            for (let i = 0; i < maxJam && i < jamAktif.length; i++) {
                const jam = jamAktif[i];
                const tr = document.createElement('tr');
                tr.className = 'border-b';
                tr.dataset.jamIndex = i;

                hariAktif.forEach(hari => {
                    const td = document.createElement('td');
                    td.className = 'p-1 border-r';
                    td.innerHTML = `
                        <div class="cell-content min-h-[60px] bg-gray-100 rounded cursor-pointer hover:bg-gray-200 flex items-center justify-center text-gray-400 text-[9px]"
                             data-kelas="${kelas.id_kelas}" 
                             data-hari="${hari}" 
                             data-jam="${jam.id_jam}"
                             data-jam-ke="${jam.jam_ke}"
                             data-waktu="${jam.waktu_mulai.substring(0, 5)} - ${jam.waktu_selesai.substring(0, 5)}"
                             onclick="openModal(${kelas.id_kelas}, ${jam.id_jam}, '${hari}')">
                            <span class="text-gray-400">+ Tambah</span>
                        </div>
                    `;
                    tr.appendChild(td);
                });

                tbody.appendChild(tr);
            }
        });
    }

    async function loadAllSchedules() {
        try {
            const response = await fetch('<?= BASEURL; ?>/jadwal/getAllSchedules');
            const data = await response.json();

            // Reset all cells first
            document.querySelectorAll('.cell-content').forEach(cell => {
                const jamKe = cell.dataset.jamKe || '';
                const waktu = cell.dataset.waktu || '';
                cell.innerHTML = '<span class="text-gray-400">+ Tambah</span>';
                cell.className = 'cell-content min-h-[60px] bg-gray-100 rounded cursor-pointer hover:bg-gray-200 flex items-center justify-center text-gray-400 text-[9px]';
                cell.dataset.idJadwal = '';
            });

            allSchedules = {};

            data.forEach(s => {
                const key = `${s.id_kelas}_${s.hari}_${s.id_jam}`;
                allSchedules[key] = s;

                const cell = document.querySelector(`.cell-content[data-kelas="${s.id_kelas}"][data-hari="${s.hari}"][data-jam="${s.id_jam}"]`);
                if (cell) {
                    cell.dataset.idJadwal = s.id_jadwal;
                    const jamKe = cell.dataset.jamKe || '';
                    const waktu = cell.dataset.waktu || '';
                    cell.innerHTML = `<div class="flex items-center justify-between w-full px-1.5 py-1 gap-1">
                        <div class="text-[9px] text-left shrink-0">
                            <div class="text-indigo-500 font-semibold">Jam ${jamKe}</div>
                            <div class="text-gray-400">${waktu}</div>
                        </div>
                        <div class="text-[10px] text-right leading-tight">
                            <div class="font-semibold text-indigo-800">${s.nama_mapel}</div>
                            <div class="text-indigo-600">${s.nama_guru}</div>
                        </div>
                    </div>`;
                    cell.className = 'cell-content min-h-[60px] bg-indigo-100 rounded cursor-pointer hover:bg-indigo-200 flex items-center';
                }
            });

            // Also load istirahat data
            await loadAllIstirahat();
        } catch (error) {
            console.error('Error:', error);
        }
    }

    async function loadAllIstirahat() {
        try {
            const response = await fetch('<?= BASEURL; ?>/jadwal/getAllIstirahat');
            const istirahatData = await response.json();

            // Process istirahat data if needed
            console.log('Istirahat loaded:', istirahatData.length);
        } catch (error) {
            console.error('Error loading istirahat:', error);
        }
    }

    async function addIstirahat(idKelas, hari) {
        const jamVisible = document.querySelectorAll(`#body-${idKelas} tr`).length;
        if (jamVisible === 0) {
            alert('Tidak ada jam untuk ditambahkan istirahat');
            return;
        }

        const setelahJam = prompt(`Tambah istirahat setelah jam ke- (1-${jamVisible}):`, '2');
        if (!setelahJam || isNaN(setelahJam) || setelahJam < 1 || setelahJam > jamVisible) {
            return;
        }

        try {
            const response = await fetch('<?= BASEURL; ?>/jadwal/addIstirahat', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id_kelas=${idKelas}&hari=${hari}&setelah_jam=${setelahJam}`
            });
            const result = await response.json();

            if (result.success) {
                loadAllSchedules();
            } else {
                alert(result.message || 'Gagal menambahkan istirahat');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan');
        }
    }

    function addHariJam(idKelas, hari) {
        const tbody = document.getElementById(`body-${idKelas}`);
        const currentRows = tbody.querySelectorAll('tr').length;

        if (currentRows >= jamAktif.length) {
            alert('Sudah mencapai maksimum jam');
            return;
        }

        const jam = jamAktif[currentRows];
        const tr = document.createElement('tr');
        tr.className = 'border-b';
        tr.dataset.jamIndex = currentRows;

        hariAktif.forEach(h => {
            const td = document.createElement('td');
            td.className = 'p-1 border-r';
            td.innerHTML = `
                <div class="cell-content min-h-[60px] bg-gray-100 rounded cursor-pointer hover:bg-gray-200 flex items-center justify-center text-gray-400 text-[9px]"
                     data-kelas="${idKelas}" 
                     data-hari="${h}" 
                     data-jam="${jam.id_jam}"
                     data-jam-ke="${jam.jam_ke}"
                     data-waktu="${jam.waktu_mulai.substring(0, 5)} - ${jam.waktu_selesai.substring(0, 5)}"
                     onclick="openModal(${idKelas}, ${jam.id_jam}, '${h}')">
                    <span class="text-gray-400">+ Tambah</span>
                </div>
            `;
            tr.appendChild(td);
        });

        tbody.appendChild(tr);
        loadAllSchedules();
    }

    function removeHariJam(idKelas, hari) {
        const tbody = document.getElementById(`body-${idKelas}`);
        const rows = tbody.querySelectorAll('tr');

        if (rows.length <= 1) {
            alert('Minimal harus ada 1 jam');
            return;
        }

        if (confirm('Hapus baris jam terakhir? Data jadwal di baris ini akan hilang.')) {
            rows[rows.length - 1].remove();
        }
    }

    async function openModal(idKelas, idJam, hari) {
        document.getElementById('inputKelas').value = idKelas;
        document.getElementById('inputJam').value = idJam;
        document.getElementById('inputHari').value = hari;

        // Check if editing existing
        const cell = document.querySelector(`.cell-content[data-kelas="${idKelas}"][data-hari="${hari}"][data-jam="${idJam}"]`);
        const idJadwal = cell?.dataset?.idJadwal || '';
        document.getElementById('inputJadwal').value = idJadwal;

        // Show/hide kosongkan button
        if (idJadwal) {
            document.getElementById('btnKosongkan').classList.remove('hidden');
        } else {
            document.getElementById('btnKosongkan').classList.add('hidden');
        }

        // Reset
        document.getElementById('noMapelMessage').classList.add('hidden');
        document.getElementById('guruInfo').classList.add('hidden');
        document.getElementById('errorMessage').classList.add('hidden');
        document.getElementById('selectMapel').innerHTML = '<option value="">Memuat...</option>';

        // Show modal
        document.getElementById('scheduleModal').classList.remove('hidden');
        document.getElementById('scheduleModal').classList.add('flex');

        // Load penugasan for this kelas
        try {
            const response = await fetch('<?= BASEURL; ?>/jadwal/getPenugasanByKelas/' + idKelas);
            currentPenugasan = await response.json();

            if (currentPenugasan.length === 0) {
                document.getElementById('noMapelMessage').classList.remove('hidden');
                document.getElementById('selectMapel').innerHTML = '<option value="">-- Tidak ada mapel --</option>';
                document.getElementById('btnSubmit').disabled = true;
                return;
            }

            document.getElementById('btnSubmit').disabled = false;
            document.getElementById('selectMapel').innerHTML = '<option value="">-- Pilih --</option>';
            currentPenugasan.forEach(p => {
                document.getElementById('selectMapel').innerHTML +=
                    `<option value="${p.id_mapel}" data-guru="${p.id_guru}" data-guru-name="${p.nama_guru}">${p.nama_mapel} (${p.nama_guru})</option>`;
            });

            const key = `${idKelas}_${hari}_${idJam}`;
            if (allSchedules[key]) {
                document.getElementById('selectMapel').value = allSchedules[key].id_mapel;
                document.getElementById('inputGuru').value = allSchedules[key].id_guru;
                document.getElementById('guruName').textContent = allSchedules[key].nama_guru;
                document.getElementById('guruInfo').classList.remove('hidden');
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    async function kosongkanJam() {
        let idJadwal = document.getElementById('inputJadwal').value;
        console.log('Clicked Kosongkan. Initial Input ID:', idJadwal);

        // Fallback: if not found, try to get from allSchedules
        if (!idJadwal) {
            const kelas = document.getElementById('inputKelas').value;
            const hari = document.getElementById('inputHari').value;
            const jam = document.getElementById('inputJam').value;
            const key = `${kelas}_${hari}_${jam}`;
            console.log('Trying fallback with key:', key);
            console.log('Data in allSchedules:', allSchedules[key]);

            if (allSchedules[key]) {
                idJadwal = allSchedules[key].id_jadwal;
                console.log('Found ID from allSchedules:', idJadwal);
            }
        }

        if (!idJadwal) {
            alert('Error: ID Jadwal tidak ditemukan. Silakan refresh halaman.');
            console.error('No ID Jadwal found');
            return;
        }

        if (!confirm('Yakin ingin mengosongkan jadwal ini? (ID: ' + idJadwal + ')')) {
            return;
        }

        console.log('Sending delete request for ID:', idJadwal);

        try {
            const url = '<?= BASEURL; ?>/jadwal/hapusJadwalItem/' + idJadwal;
            console.log('Fetch URL:', url);

            const response = await fetch(url);
            console.log('Response status:', response.status);

            const textHTML = await response.text();
            console.log('Response body:', textHTML);

            let result;
            try {
                result = JSON.parse(textHTML);
            } catch (e) {
                console.error('JSON Parse Error:', e);
                alert('Server Error (Invalid JSON)');
                return;
            }

            if (result.success) {
                console.log('Delete success');
                closeModal();
                loadAllSchedules();
            } else {
                console.error('Delete failed:', result);
                alert(result.message || 'Gagal mengosongkan jadwal');
            }
        } catch (error) {
            console.error('Fetch Error:', error);
            alert('Terjadi kesalahan koneksi saat mengosongkan jadwal');
        }
    }

    function closeModal() {
        document.getElementById('scheduleModal').classList.add('hidden');
        document.getElementById('scheduleModal').classList.remove('flex');
    }

    document.getElementById('selectMapel').addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        if (selected.value) {
            document.getElementById('inputGuru').value = selected.dataset.guru;
            document.getElementById('guruName').textContent = selected.dataset.guruName;
            document.getElementById('guruInfo').classList.remove('hidden');
        } else {
            document.getElementById('guruInfo').classList.add('hidden');
        }
    });

    document.getElementById('scheduleForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        document.getElementById('errorMessage').classList.add('hidden');

        try {
            const response = await fetch('<?= BASEURL; ?>/jadwal/tambahJadwal', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                closeModal();
                loadAllSchedules();
            } else {
                document.getElementById('errorMessage').textContent = result.message;
                document.getElementById('errorMessage').classList.remove('hidden');
            }
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('errorMessage').textContent = 'Terjadi kesalahan';
            document.getElementById('errorMessage').classList.remove('hidden');
        }
    });

    async function confirmResetJadwal() {
        if (confirm('PERINGATAN: Anda akan mereset jadwal untuk tahun pelajaran ini.\n\nSemua data jadwal pelajaran dan jadwal istirahat akan DIHAPUS PERMANEN.\n\nLanjutkan?')) {
            if (confirm('YAKIN? Tindakan ini tidak dapat dibatalkan.')) {
                try {
                    const response = await fetch('<?= BASEURL; ?>/jadwal/resetJadwal');
                    const result = await response.json();

                    if (result.success) {
                        alert('Jadwal berhasil direset.');
                        location.reload();
                    } else {
                        alert(result.message || 'Gagal mereset jadwal.');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat mereset jadwal.');
                }
            }
        }
    }
</script>