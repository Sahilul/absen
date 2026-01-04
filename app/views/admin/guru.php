<?php
// File: app/views/admin/guru.php - Responsive + Modal Edit Version
$guruList = $data['guru'] ?? [];
$totalGuru = count($guruList);
$totalAkunAktif = count(array_filter($guruList, function ($g) {
    return !empty($g['password_plain']);
}));
$totalBelumPassword = $totalGuru - $totalAkunAktif;
?>
<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-4 sm:p-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Manajemen Data Guru</h2>
            <p class="text-gray-600 mt-1">Kelola data dan akun guru</p>
        </div>
        <button onclick="openAddModal()"
            class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 px-4 rounded-xl flex items-center justify-center gap-2 transition-colors shadow-sm">
            <i data-lucide="plus-circle" class="w-5 h-5"></i>
            Tambah Guru
        </button>
    </div>

    <?php Flasher::flash(); ?>

    <!-- Stats Cards -->
    <div class="grid grid-cols-3 gap-3 sm:gap-4 mb-6">
        <div class="bg-white p-3 sm:p-4 rounded-xl shadow-sm border">
            <div class="flex items-center gap-3">
                <div class="bg-indigo-100 p-2 sm:p-3 rounded-xl">
                    <i data-lucide="users" class="w-5 h-5 sm:w-6 sm:h-6 text-indigo-600"></i>
                </div>
                <div>
                    <p class="text-xs sm:text-sm text-gray-600">Total</p>
                    <p class="text-lg sm:text-2xl font-bold text-gray-900"><?= $totalGuru ?></p>
                </div>
            </div>
        </div>
        <div class="bg-white p-3 sm:p-4 rounded-xl shadow-sm border">
            <div class="flex items-center gap-3">
                <div class="bg-green-100 p-2 sm:p-3 rounded-xl">
                    <i data-lucide="shield-check" class="w-5 h-5 sm:w-6 sm:h-6 text-green-600"></i>
                </div>
                <div>
                    <p class="text-xs sm:text-sm text-gray-600">Aktif</p>
                    <p class="text-lg sm:text-2xl font-bold text-gray-900"><?= $totalAkunAktif ?></p>
                </div>
            </div>
        </div>
        <div class="bg-white p-3 sm:p-4 rounded-xl shadow-sm border">
            <div class="flex items-center gap-3">
                <div class="bg-orange-100 p-2 sm:p-3 rounded-xl">
                    <i data-lucide="alert-circle" class="w-5 h-5 sm:w-6 sm:h-6 text-orange-600"></i>
                </div>
                <div>
                    <p class="text-xs sm:text-sm text-gray-600">Pending</p>
                    <p class="text-lg sm:text-2xl font-bold text-gray-900"><?= $totalBelumPassword ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="bg-white rounded-xl shadow-sm border p-4 mb-4">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 relative">
                <i data-lucide="search" class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" id="searchInput" placeholder="Cari guru..."
                    class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    oninput="filterGuru()">
            </div>
            <div class="flex items-center gap-3">
                <div class="text-sm text-gray-500">
                    <span id="countDisplay"><?= $totalGuru ?></span> guru
                </div>
                <button id="bulkDeleteBtn" onclick="openBulkDeleteModal()"
                    class="hidden bg-red-100 hover:bg-red-200 text-red-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors items-center gap-1">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                    Hapus (<span id="selectedCount">0</span>)
                </button>
            </div>
        </div>
    </div>

    <!-- Guru List - Mobile Cards / Desktop Table -->
    <?php if (!empty($guruList)): ?>

        <!-- Desktop Table View -->
        <div class="hidden md:block bg-white rounded-xl shadow-sm border overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full" id="guruTable">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-3 py-3 text-center text-xs font-semibold text-gray-600 uppercase w-12">
                                <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)"
                                    class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">NIK</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Nama Guru</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">No. WA</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Password</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100" id="guruTableBody">
                        <?php foreach ($guruList as $i => $guru): ?>
                            <tr class="hover:bg-gray-50 guru-row" 
                                data-id="<?= (int) $guru['id_guru'] ?>"
                                data-nama="<?= strtolower($guru['nama_guru']) ?>"
                                data-nik="<?= $guru['nik'] ?>">
                                <td class="px-3 py-3 text-center">
                                    <input type="checkbox" class="guru-checkbox w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                        value="<?= (int) $guru['id_guru'] ?>"
                                        data-nama="<?= htmlspecialchars($guru['nama_guru']) ?>"
                                        onchange="updateBulkDeleteButton()">
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="w-7 h-7 bg-indigo-100 rounded-lg flex items-center justify-center text-xs font-semibold text-indigo-600"><?= $i + 1 ?></span>
                                        <span class="font-mono text-sm"><?= htmlspecialchars($guru['nik']) ?></span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900"><?= htmlspecialchars($guru['nama_guru']) ?></div>
                                    <?php if (!empty($guru['email'])): ?>
                                        <div class="text-xs text-gray-500"><?= htmlspecialchars($guru['email']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3">
                                    <?php if (!empty($guru['no_wa'])): ?>
                                        <span class="text-sm text-gray-700"><?= htmlspecialchars($guru['no_wa']) ?></span>
                                    <?php else: ?>
                                        <span class="text-xs text-gray-400 italic">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3">
                                    <?php if (!empty($guru['password_plain'])): ?>
                                        <code
                                            class="px-2 py-1 bg-gray-100 rounded text-xs font-mono text-gray-800"><?= htmlspecialchars($guru['password_plain']) ?></code>
                                    <?php else: ?>
                                        <span class="text-xs text-gray-400 italic">Belum diset</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3">
                                    <?php if (!empty($guru['password_plain'])): ?>
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i data-lucide="check-circle" class="w-3 h-3"></i> Aktif
                                        </span>
                                    <?php else: ?>
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                            <i data-lucide="alert-circle" class="w-3 h-3"></i> Pending
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button onclick='openEditModal(<?= json_encode($guru) ?>)'
                                            class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg" title="Edit">
                                            <i data-lucide="edit-2" class="w-4 h-4"></i>
                                        </button>
                                        <button
                                            onclick="confirmDelete(<?= $guru['id_guru'] ?>, '<?= htmlspecialchars($guru['nama_guru'], ENT_QUOTES) ?>')"
                                            class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Hapus">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Mobile Card View -->
        <div class="md:hidden space-y-3" id="guruCards">
            <?php foreach ($guruList as $i => $guru): ?>
                <div class="bg-white rounded-xl shadow-sm border p-4 guru-card"
                    data-nama="<?= strtolower($guru['nama_guru']) ?>" data-nik="<?= $guru['nik'] ?>">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                <i data-lucide="user" class="w-5 h-5 text-indigo-600"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900"><?= htmlspecialchars($guru['nama_guru']) ?></h3>
                                <p class="text-xs text-gray-500 font-mono"><?= htmlspecialchars($guru['nik']) ?></p>
                            </div>
                        </div>
                        <?php if (!empty($guru['password_plain'])): ?>
                            <span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">Aktif</span>
                        <?php else: ?>
                            <span class="px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-700">Pending</span>
                        <?php endif; ?>
                    </div>

                    <div class="space-y-2 text-sm mb-4">
                        <?php if (!empty($guru['email'])): ?>
                            <div class="flex items-center gap-2 text-gray-600">
                                <i data-lucide="mail" class="w-4 h-4 text-gray-400"></i>
                                <span class="truncate"><?= htmlspecialchars($guru['email']) ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($guru['no_wa'])): ?>
                            <div class="flex items-center gap-2 text-gray-600">
                                <i data-lucide="phone" class="w-4 h-4 text-gray-400"></i>
                                <span><?= htmlspecialchars($guru['no_wa']) ?></span>
                            </div>
                        <?php endif; ?>

                        <div class="flex items-center gap-2 text-gray-600">
                            <i data-lucide="key" class="w-4 h-4 text-gray-400"></i>
                            <?php if (!empty($guru['password_plain'])): ?>
                                <code
                                    class="px-1.5 py-0.5 bg-gray-100 rounded text-xs font-mono"><?= htmlspecialchars($guru['password_plain']) ?></code>
                            <?php else: ?>
                                <span class="text-xs italic text-gray-400">Belum diset</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="flex gap-2 pt-3 border-t border-gray-100">
                        <button onclick='openEditModal(<?= json_encode($guru) ?>)'
                            class="flex-1 py-2 px-3 bg-indigo-50 text-indigo-600 rounded-lg text-sm font-medium flex items-center justify-center gap-2 hover:bg-indigo-100">
                            <i data-lucide="edit-2" class="w-4 h-4"></i> Edit
                        </button>
                        <button
                            onclick="confirmDelete(<?= $guru['id_guru'] ?>, '<?= htmlspecialchars($guru['nama_guru'], ENT_QUOTES) ?>')"
                            class="py-2 px-3 bg-red-50 text-red-600 rounded-lg text-sm font-medium flex items-center justify-center gap-2 hover:bg-red-100">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php else: ?>
        <!-- Empty State -->
        <div class="bg-white rounded-xl shadow-sm border p-12 text-center">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i data-lucide="users-x" class="w-8 h-8 text-gray-400"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Data Guru</h3>
            <p class="text-gray-500 mb-6">Mulai dengan menambahkan data guru pertama.</p>
            <button onclick="openAddModal()"
                class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                <i data-lucide="plus-circle" class="w-4 h-4"></i> Tambah Guru
            </button>
        </div>
    <?php endif; ?>
</main>

<!-- Edit/Add Modal -->
<div id="editModal" class="fixed inset-0 bg-black/50 z-[9999] hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b px-6 py-4 flex items-center justify-between rounded-t-2xl">
            <h3 id="modalTitle" class="text-lg font-bold text-gray-900">Edit Data Guru</h3>
            <button onclick="closeEditModal()" class="p-2 hover:bg-gray-100 rounded-lg">
                <i data-lucide="x" class="w-5 h-5 text-gray-500"></i>
            </button>
        </div>

        <form id="guruForm" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="id_guru" id="formIdGuru">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span
                        class="text-red-500">*</span></label>
                <input type="text" name="nama_guru" id="formNamaGuru" required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">NIK (Username) <span
                        class="text-red-500">*</span></label>
                <input type="text" name="nik" id="formNik" required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-mono">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" id="formEmail"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">No. WhatsApp</label>
                <input type="text" name="no_wa" id="formNoWa" placeholder="08123456789"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div id="passwordSection">
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" id="formPassword"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                <p class="text-xs text-gray-500 mt-1" id="passwordHint">Kosongkan jika tidak ingin mengubah password</p>
            </div>

            <div class="flex gap-3 pt-4 border-t">
                <button type="button" onclick="closeEditModal()"
                    class="flex-1 py-2.5 px-4 border border-gray-300 rounded-xl font-medium hover:bg-gray-50">
                    Batal
                </button>
                <button type="submit" id="btnSubmit"
                    class="flex-1 py-2.5 px-4 bg-indigo-600 text-white rounded-xl font-medium hover:bg-indigo-700 flex items-center justify-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span id="btnSubmitText">Simpan</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const BASEURL = '<?= BASEURL ?>';

    function openEditModal(guru) {
        document.getElementById('modalTitle').textContent = 'Edit Data Guru';
        const form = document.getElementById('guruForm');
        form.action = BASEURL + '/admin/prosesUpdateGuru';

        document.getElementById('formIdGuru').value = guru.id_guru;
        document.getElementById('formNamaGuru').value = guru.nama_guru;
        document.getElementById('formNik').value = guru.nik;
        document.getElementById('formEmail').value = guru.email || '';
        document.getElementById('formNoWa').value = guru.no_wa || '';
        document.getElementById('formPassword').value = '';
        document.getElementById('passwordHint').textContent = 'Kosongkan jika tidak ingin mengubah password';
        document.getElementById('btnSubmitText').textContent = 'Simpan Perubahan';

        document.getElementById('editModal').classList.remove('hidden');
        document.getElementById('editModal').classList.add('flex');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function openAddModal() {
        document.getElementById('modalTitle').textContent = 'Tambah Guru Baru';
        const form = document.getElementById('guruForm');
        form.action = BASEURL + '/admin/prosesTambahGuru';

        document.getElementById('formIdGuru').value = '';
        document.getElementById('formNamaGuru').value = '';
        document.getElementById('formNik').value = '';
        document.getElementById('formEmail').value = '';
        document.getElementById('formNoWa').value = '';
        document.getElementById('formPassword').value = '';
        document.getElementById('passwordHint').textContent = 'Wajib diisi untuk guru baru';
        document.getElementById('btnSubmitText').textContent = 'Tambah Guru';

        document.getElementById('editModal').classList.remove('hidden');
        document.getElementById('editModal').classList.add('flex');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
        document.getElementById('editModal').classList.remove('flex');
    }

    // Form submit handler removed to allow natural submission with Flash messages

    function confirmDelete(id, nama) {
        if (confirm('Apakah Anda yakin ingin menghapus data guru ' + nama + '?\n\nSemua data terkait akan ikut terhapus!')) {
            window.location.href = BASEURL + '/admin/hapusGuru/' + id;
        }
    }

    function filterGuru() {
        const search = document.getElementById('searchInput').value.toLowerCase();
        let count = 0;

        // Filter table rows
        document.querySelectorAll('.guru-row').forEach(row => {
            const nama = row.dataset.nama || '';
            const nik = row.dataset.nik || '';
            const match = nama.includes(search) || nik.includes(search);
            row.style.display = match ? '' : 'none';
            if (match) count++;
        });

        // Filter cards
        document.querySelectorAll('.guru-card').forEach(card => {
            const nama = card.dataset.nama || '';
            const nik = card.dataset.nik || '';
            const match = nama.includes(search) || nik.includes(search);
            card.style.display = match ? '' : 'none';
        });

        document.getElementById('countDisplay').textContent = count;
    }

    // Close modal on backdrop click
    document.getElementById('editModal').addEventListener('click', function (e) {
        if (e.target === this) closeEditModal();
    });

    // Init icons
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });

    // ==========================================
    // BULK DELETE FUNCTIONS
    // ==========================================

    function toggleSelectAll(masterCheckbox) {
        const checkboxes = document.querySelectorAll('.guru-checkbox');
        checkboxes.forEach(cb => {
            const row = cb.closest('tr');
            if (row && row.style.display !== 'none') {
                cb.checked = masterCheckbox.checked;
            }
        });
        updateBulkDeleteButton();
    }

    function updateBulkDeleteButton() {
        const checkboxes = document.querySelectorAll('.guru-checkbox:checked');
        const count = checkboxes.length;
        const btn = document.getElementById('bulkDeleteBtn');
        const countSpan = document.getElementById('selectedCount');
        
        if (count > 0) {
            btn.classList.remove('hidden');
            btn.classList.add('flex');
            countSpan.textContent = count;
        } else {
            btn.classList.add('hidden');
            btn.classList.remove('flex');
        }

        const allCheckboxes = document.querySelectorAll('.guru-checkbox');
        const visibleCheckboxes = Array.from(allCheckboxes).filter(cb => {
            const row = cb.closest('tr');
            return row && row.style.display !== 'none';
        });
        const selectAllCheckbox = document.getElementById('selectAllCheckbox');
        if (visibleCheckboxes.length > 0 && visibleCheckboxes.every(cb => cb.checked)) {
            selectAllCheckbox.checked = true;
            selectAllCheckbox.indeterminate = false;
        } else if (count > 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = true;
        } else {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        }

        if (typeof lucide !== 'undefined') {
            setTimeout(() => lucide.createIcons(), 50);
        }
    }

    function openBulkDeleteModal() {
        const checkboxes = document.querySelectorAll('.guru-checkbox:checked');
        const count = checkboxes.length;
        
        if (count === 0) {
            alert('Pilih minimal 1 guru untuk dihapus.');
            return;
        }

        let listHtml = '<ul class="list-disc list-inside space-y-1">';
        checkboxes.forEach(cb => {
            const nama = cb.getAttribute('data-nama') || 'Unknown';
            listHtml += `<li>${nama}</li>`;
        });
        listHtml += '</ul>';

        document.getElementById('bulkDeleteCount').textContent = count;
        document.getElementById('bulkDeleteList').innerHTML = listHtml;

        const modal = document.getElementById('bulkDeleteModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        if (typeof lucide !== 'undefined') {
            setTimeout(() => lucide.createIcons(), 50);
        }
    }

    function closeBulkDeleteModal() {
        const modal = document.getElementById('bulkDeleteModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function confirmBulkDelete() {
        const checkboxes = document.querySelectorAll('.guru-checkbox:checked');
        const ids = Array.from(checkboxes).map(cb => cb.value);

        if (ids.length === 0) {
            alert('Tidak ada guru yang dipilih.');
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = BASEURL + '/admin/bulkHapusGuru';

        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = id;
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
    }

    document.addEventListener('click', function (e) {
        const modal = document.getElementById('bulkDeleteModal');
        if (!modal || modal.classList.contains('hidden')) return;
        if (e.target === modal) {
            closeBulkDeleteModal();
        }
    });
</script>

<!-- Bulk Delete Modal -->
<div id="bulkDeleteModal"
    class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-lg border border-gray-200">
        <div class="px-6 py-4 border-b flex items-center">
            <i data-lucide="alert-triangle" class="w-5 h-5 text-red-500 mr-2"></i>
            <h4 class="text-lg font-semibold text-gray-800">Hapus Beberapa Guru</h4>
        </div>
        <div class="px-6 py-5 space-y-4">
            <p class="text-sm text-gray-600 leading-relaxed">
                Anda akan menghapus <span class="font-bold text-red-600" id="bulkDeleteCount">0</span> guru:
            </p>
            <div id="bulkDeleteList" class="max-h-40 overflow-y-auto bg-gray-50 border border-gray-200 rounded-lg p-3 text-sm text-gray-700">
            </div>
            <div class="bg-red-50 border border-red-200 rounded-lg p-3 text-xs text-red-700">
                <strong>Peringatan:</strong> Semua data terkait (penugasan, jurnal, absensi) akan dihapus permanen.
            </div>
            <p class="text-xs text-gray-500">Tindakan ini tidak dapat dibatalkan.</p>
        </div>
        <div class="px-6 py-4 bg-gray-50 border-t flex justify-end space-x-3 rounded-b-xl">
            <button onclick="closeBulkDeleteModal()"
                class="px-4 py-2 text-sm font-medium rounded-md bg-gray-100 hover:bg-gray-200 text-gray-700">Batal</button>
            <button onclick="confirmBulkDelete()"
                class="px-4 py-2 text-sm font-medium rounded-md bg-red-600 hover:bg-red-700 text-white flex items-center">
                <i data-lucide="trash-2" class="w-4 h-4 mr-1"></i> Hapus Semua
            </button>
        </div>
    </div>
</div>