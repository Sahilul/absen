<?php
// Ambil data tahun pelajaran
$tahuns = $data['daftar_tp'] ?? [];
?>

<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 space-y-4 sm:space-y-0">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Import Siswa dari PSB</h2>
            <p class="text-gray-600 mt-1">Import siswa yang sudah diterima di PSB ke Data Induk Siswa</p>
        </div>
        <a href="<?= BASEURL; ?>/admin/siswa"
            class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-lg flex items-center justify-center transition-colors duration-200 shadow-sm">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
            Kembali
        </a>
    </div>

    <!-- Flash Messages -->
    <?php if (class_exists('Flasher')) {
        Flasher::flash();
    } ?>

    <!-- Filter Card -->
    <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
        <div class="flex flex-col sm:flex-row gap-4 items-end">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Tahun Pelajaran (PSB)</label>
                <select class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2 px-3 border" id="tp_select">
                    <option value="">-- Pilih Tahun Pelajaran --</option>
                    <?php foreach ($tahuns as $tp): ?>
                        <option value="<?= $tp['id_tp']; ?>">
                            <?= htmlspecialchars($tp['nama_tp']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-6 rounded-lg flex items-center justify-center transition-colors duration-200 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed" id="btn_load" disabled>
                <i data-lucide="search" class="w-4 h-4 mr-2"></i>
                Tampilkan
            </button>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-800">Daftar Calon Siswa</h3>
            <p class="text-sm text-gray-500">Siswa dengan status "Diterima" yang belum diimport</p>
        </div>

        <form action="<?= BASEURL; ?>/admin/processImport" method="POST" id="form-import"
            onsubmit="return confirm('Yakin ingin mengimport siswa terpilih?');">
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200" id="table-candidates">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-center">
                                <input type="checkbox" id="check-all" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" disabled>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No Pendaftaran</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NISN</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Siswa</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jalur</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sekolah Asal</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Akun</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="table-body">
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                <i data-lucide="info" class="w-8 h-8 mx-auto mb-2 text-gray-400"></i>
                                <p>Silakan pilih Tahun Pelajaran terlebih dahulu.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end">
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2 px-6 rounded-lg flex items-center justify-center transition-colors duration-200 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed" id="btn-submit" disabled>
                    <i data-lucide="download" class="w-4 h-4 mr-2"></i>
                    <span id="btn-submit-text">Import Siswa Terpilih</span>
                </button>
            </div>
        </form>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Initialize Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        const tpSelect = document.getElementById('tp_select');
        const btnLoad = document.getElementById('btn_load');
        const tableBody = document.getElementById('table-body');
        const checkAll = document.getElementById('check-all');
        const btnSubmit = document.getElementById('btn-submit');
        const btnSubmitText = document.getElementById('btn-submit-text');

        // Enable load button when TP is selected
        tpSelect.addEventListener('change', function () {
            btnLoad.disabled = !this.value;
        });

        // Check All functionality
        checkAll.addEventListener('change', function () {
            const checkboxes = document.querySelectorAll('.check-item');
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateSubmitButton();
        });

        // Load Data
        btnLoad.addEventListener('click', function () {
            const id_tp = tpSelect.value;
            if (!id_tp) return;

            btnLoad.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Loading...';
            btnLoad.disabled = true;

            fetch('<?= BASEURL; ?>/admin/getCandidates?id_tp=' + id_tp)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    tableBody.innerHTML = '';

                    if (!data || data.length === 0) {
                        tableBody.innerHTML = `
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                    <p>Tidak ada calon siswa yang siap diimport.</p>
                                    <p class="text-sm text-gray-400 mt-1">Pastikan ada pendaftar berstatus "Diterima" di tahun pelajaran ini.</p>
                                </td>
                            </tr>
                        `;
                        checkAll.disabled = true;
                        checkAll.checked = false;
                        return;
                    }

                    checkAll.disabled = false;

                    data.forEach(row => {
                        const hasAccountBadge = row.has_account > 0
                            ? '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Punya Akun</span>'
                            : '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Belum Punya Akun</span>';

                        const tr = document.createElement('tr');
                        tr.className = 'hover:bg-gray-50';
                        tr.innerHTML = `
                            <td class="px-4 py-3 text-center">
                                <input type="checkbox" name="ids[]" value="${row.id_pendaftar}" class="check-item rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">${row.no_pendaftaran || '-'}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">${row.nisn || '-'}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">${row.nama_lengkap || '-'}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">${row.nama_jalur || '-'}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">${row.asal_sekolah || '-'}</td>
                            <td class="px-4 py-3 text-sm">${hasAccountBadge}</td>
                        `;
                        tableBody.appendChild(tr);
                    });

                    // Re-attach event listeners for new checkboxes
                    document.querySelectorAll('.check-item').forEach(cb => {
                        cb.addEventListener('change', updateSubmitButton);
                    });

                    updateSubmitButton();
                    
                    // Re-initialize lucide for new elements if needed
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                })
                .catch(err => {
                    console.error('Error fetching candidates:', err);
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-red-500">
                                <svg class="w-8 h-8 mx-auto mb-2 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                <p>Gagal memuat data. Silakan coba lagi.</p>
                            </td>
                        </tr>
                    `;
                })
                .finally(() => {
                    btnLoad.innerHTML = '<svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg> Tampilkan';
                    btnLoad.disabled = false;
                });
        });

        function updateSubmitButton() {
            const checkedCount = document.querySelectorAll('.check-item:checked').length;
            btnSubmit.disabled = checkedCount === 0;
            btnSubmitText.textContent = checkedCount > 0 
                ? `Import ${checkedCount} Siswa Terpilih` 
                : 'Import Siswa Terpilih';
        }
    });
</script>