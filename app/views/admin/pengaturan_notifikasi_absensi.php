<?php
/**
 * Admin - Pengaturan Notifikasi Absensi
 * Halaman untuk mengatur mode notifikasi dan grup WA per kelas
 */
$mode = $data['notif_mode'] ?? 'personal';
$enabled = $data['notif_enabled'] ?? true;
$kelasList = $data['kelas_list'] ?? [];
$grupData = $data['grup_data'] ?? [];
?>
<main class="flex-1 overflow-x-hidden overflow-y-auto bg-secondary-50 p-4 md:p-6">
    <!-- Header -->
    <div class="mb-4">
        <div class="bg-white shadow-sm rounded-xl p-4 md:p-5">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                    <h4 class="text-lg md:text-xl font-bold text-slate-800 mb-1">
                        <i data-lucide="bell" class="w-5 h-5 inline-block mr-2 text-primary-600"></i>
                        Pengaturan Notifikasi Absensi
                    </h4>
                    <p class="text-slate-500 text-sm">
                        Atur mode pengiriman notifikasi WhatsApp untuk laporan absensi siswa
                    </p>
                </div>
            </div>
        </div>
    </div>

    <?php Flasher::flash(); ?>

    <!-- Main Settings Card -->
    <div class="bg-white shadow-sm rounded-xl mb-6">
        <div class="p-4 md:p-5 border-b border-slate-100">
            <h5 class="font-semibold text-slate-800 flex items-center">
                <i data-lucide="settings" class="w-4 h-4 mr-2 text-slate-600"></i>
                Pengaturan Umum
            </h5>
        </div>
        <form action="<?= BASEURL; ?>/admin/simpanPengaturanNotifikasiAbsensi" method="POST" class="p-4 md:p-5">
            <!-- Master Toggle -->
            <div class="flex items-center justify-between mb-6 p-4 bg-slate-50 rounded-lg">
                <div>
                    <h6 class="font-medium text-slate-800">Aktifkan Notifikasi Absensi</h6>
                    <p class="text-sm text-slate-500">Toggle master untuk mengaktifkan/menonaktifkan semua notifikasi
                        absensi</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="notif_enabled" value="1" <?= $enabled ? 'checked' : ''; ?>
                        class="sr-only peer">
                    <div
                        class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600">
                    </div>
                </label>
            </div>

            <!-- Mode Selection -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-slate-700 mb-3">Mode Pengiriman</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label
                        class="flex items-start p-4 border rounded-lg cursor-pointer hover:bg-slate-50 transition-colors <?= $mode === 'personal' ? 'border-primary-500 bg-primary-50' : 'border-slate-200'; ?>">
                        <input type="radio" name="notif_mode" value="personal" <?= $mode === 'personal' ? 'checked' : ''; ?> class="mt-1 mr-3 text-primary-600 focus:ring-primary-500">
                        <div>
                            <span class="font-medium text-slate-800">Personal</span>
                            <p class="text-sm text-slate-500">Kirim ke orang tua masing-masing siswa</p>
                        </div>
                    </label>
                    <label
                        class="flex items-start p-4 border rounded-lg cursor-pointer hover:bg-slate-50 transition-colors <?= $mode === 'grup' ? 'border-primary-500 bg-primary-50' : 'border-slate-200'; ?>">
                        <input type="radio" name="notif_mode" value="grup" <?= $mode === 'grup' ? 'checked' : ''; ?>
                            class="mt-1 mr-3 text-primary-600 focus:ring-primary-500">
                        <div>
                            <span class="font-medium text-slate-800">Grup</span>
                            <p class="text-sm text-slate-500">Kirim ke grup kelas (1 pesan per mapel)</p>
                        </div>
                    </label>
                    <label
                        class="flex items-start p-4 border rounded-lg cursor-pointer hover:bg-slate-50 transition-colors <?= $mode === 'both' ? 'border-primary-500 bg-primary-50' : 'border-slate-200'; ?>">
                        <input type="radio" name="notif_mode" value="both" <?= $mode === 'both' ? 'checked' : ''; ?>
                            class="mt-1 mr-3 text-primary-600 focus:ring-primary-500">
                        <div>
                            <span class="font-medium text-slate-800">Keduanya</span>
                            <p class="text-sm text-slate-500">Personal + Grup (lengkap)</p>
                        </div>
                    </label>
                    <label
                        class="flex items-start p-4 border rounded-lg cursor-pointer hover:bg-slate-50 transition-colors <?= $mode === 'off' ? 'border-primary-500 bg-primary-50' : 'border-slate-200'; ?>">
                        <input type="radio" name="notif_mode" value="off" <?= $mode === 'off' ? 'checked' : ''; ?>
                            class="mt-1 mr-3 text-primary-600 focus:ring-primary-500">
                        <div>
                            <span class="font-medium text-slate-800">Nonaktif</span>
                            <p class="text-sm text-slate-500">Tidak mengirim notifikasi</p>
                        </div>
                    </label>
                </div>
            </div>

            <button type="submit"
                class="w-full bg-primary-600 hover:bg-primary-700 text-white font-medium py-2.5 px-4 rounded-lg transition-colors flex items-center justify-center">
                <i data-lucide="save" class="w-4 h-4 mr-2"></i>
                Simpan Pengaturan
            </button>
        </form>
    </div>

    <!-- Grup WhatsApp per Kelas -->
    <div class="bg-white shadow-sm rounded-xl">
        <div class="p-4 md:p-5 border-b border-slate-100">
            <h5 class="font-semibold text-slate-800 flex items-center">
                <i data-lucide="message-circle" class="w-4 h-4 mr-2 text-green-600"></i>
                Grup WhatsApp per Kelas
            </h5>
            <p class="text-sm text-slate-500 mt-1">Setiap kelas bisa memiliki lebih dari satu grup WA</p>
        </div>
        <div class="p-4 md:p-5">
            <?php if (empty($kelasList)): ?>
                <div class="text-center py-8 text-slate-500">
                    <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-3 text-slate-300"></i>
                    <p>Tidak ada kelas ditemukan</p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($kelasList as $kelas):
                        $kelasGrups = array_filter($grupData, fn($g) => $g['id_kelas'] == $kelas['id_kelas']);
                        ?>
                        <div class="border border-slate-200 rounded-lg overflow-hidden">
                            <!-- Kelas Header -->
                            <div class="bg-slate-50 px-4 py-3 flex items-center justify-between">
                                <div class="flex items-center">
                                    <i data-lucide="users" class="w-4 h-4 mr-2 text-slate-500"></i>
                                    <span
                                        class="font-medium text-slate-800"><?= htmlspecialchars($kelas['nama_kelas']); ?></span>
                                    <span class="ml-2 text-xs text-slate-500">(<?= count($kelasGrups); ?> grup)</span>
                                </div>
                                <button type="button"
                                    onclick="showAddGrupModal(<?= $kelas['id_kelas']; ?>, '<?= htmlspecialchars($kelas['nama_kelas']); ?>')"
                                    class="text-primary-600 hover:text-primary-800 text-sm font-medium flex items-center">
                                    <i data-lucide="plus" class="w-4 h-4 mr-1"></i>
                                    Tambah
                                </button>
                            </div>
                            <!-- Grup List -->
                            <div class="divide-y divide-slate-100" id="grup-list-<?= $kelas['id_kelas']; ?>">
                                <?php if (empty($kelasGrups)): ?>
                                    <div class="px-4 py-3 text-sm text-slate-400 italic">
                                        Belum ada grup WA untuk kelas ini
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($kelasGrups as $grup): ?>
                                        <div class="px-4 py-3 flex items-center justify-between hover:bg-slate-50"
                                            id="grup-row-<?= $grup['id']; ?>">
                                            <div class="flex items-center">
                                                <span
                                                    class="w-2 h-2 rounded-full mr-3 <?= $grup['is_active'] ? 'bg-green-500' : 'bg-slate-300'; ?>"></span>
                                                <div>
                                                    <span
                                                        class="font-medium text-slate-700"><?= htmlspecialchars($grup['nama_grup']); ?></span>
                                                    <span class="text-slate-400 mx-2">•</span>
                                                    <span
                                                        class="text-sm text-slate-500 font-mono"><?= htmlspecialchars($grup['grup_wa_id']); ?></span>
                                                </div>
                                            </div>
                                            <div class="flex items-center space-x-1">
                                                <!-- Toggle Active -->
                                                <button type="button" onclick="toggleGrup(<?= $grup['id']; ?>)"
                                                    class="p-1.5 rounded hover:bg-slate-100 transition-colors"
                                                    title="<?= $grup['is_active'] ? 'Nonaktifkan' : 'Aktifkan'; ?>">
                                                    <i data-lucide="<?= $grup['is_active'] ? 'toggle-right' : 'toggle-left'; ?>"
                                                        class="w-5 h-5 <?= $grup['is_active'] ? 'text-green-600' : 'text-slate-400'; ?>"></i>
                                                </button>
                                                <!-- Edit -->
                                                <button type="button"
                                                    onclick="showEditGrupModal(<?= $grup['id']; ?>, '<?= htmlspecialchars($grup['nama_grup']); ?>', '<?= htmlspecialchars($grup['grup_wa_id']); ?>')"
                                                    class="p-1.5 rounded hover:bg-slate-100 transition-colors" title="Edit">
                                                    <i data-lucide="pencil" class="w-4 h-4 text-blue-600"></i>
                                                </button>
                                                <!-- Test Pesan -->
                                                <button type="button"
                                                    onclick="testGrup(<?= $grup['id']; ?>, '<?= htmlspecialchars($grup['nama_grup']); ?>')"
                                                    class="p-1.5 rounded hover:bg-slate-100 transition-colors" title="Test Pesan">
                                                    <i data-lucide="send" class="w-4 h-4 text-purple-600"></i>
                                                </button>
                                                <!-- Delete -->
                                                <button type="button"
                                                    onclick="deleteGrup(<?= $grup['id']; ?>, '<?= htmlspecialchars($grup['nama_grup']); ?>')"
                                                    class="p-1.5 rounded hover:bg-slate-100 transition-colors" title="Hapus">
                                                    <i data-lucide="trash-2" class="w-4 h-4 text-red-600"></i>
                                                </button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Sync Grup dari WA Gateway -->
            <?php
            $wa_provider = $data['wa_provider'] ?? 'fonnte';
            $fonnte_groups = $_SESSION['fonnte_groups'] ?? [];
            $gowa_groups = $_SESSION['gowa_groups'] ?? [];
            $isGowa = ($wa_provider === 'gowa');
            $syncGroups = $isGowa ? $gowa_groups : $fonnte_groups;
            $syncUrl = $isGowa ? BASEURL . '/admin/syncGrupGowa' : BASEURL . '/admin/syncGrupFonnte';
            $providerName = $isGowa ? 'GOWA (Self-Hosted)' : 'Fonnte';
            ?>
            <div
                class="mt-6 p-4 <?= $isGowa ? 'bg-purple-50 border-purple-100' : 'bg-green-50 border-green-100'; ?> rounded-lg border">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div>
                        <h6
                            class="font-medium <?= $isGowa ? 'text-purple-800' : 'text-green-800'; ?> flex items-center">
                            <i data-lucide="refresh-cw" class="w-4 h-4 mr-2"></i>
                            Sync Grup dari <?= $providerName; ?>
                        </h6>
                        <p class="text-sm <?= $isGowa ? 'text-purple-700' : 'text-green-700'; ?> mt-1">
                            Klik tombol untuk mengambil daftar grup WA dari <?= $providerName; ?>.
                            <?php if (!empty($syncGroups)): ?>
                                <strong>(<?= count($syncGroups); ?> grup tersedia)</strong>
                            <?php endif; ?>
                            <br><span class="text-xs">Provider: <code
                                    class="<?= $isGowa ? 'bg-purple-100' : 'bg-green-100'; ?> px-1 rounded"><?= $wa_provider; ?></code></span>
                        </p>
                    </div>
                    <a href="<?= $syncUrl; ?>"
                        class="<?= $isGowa ? 'bg-purple-600 hover:bg-purple-700' : 'bg-green-600 hover:bg-green-700'; ?> text-white font-medium py-2 px-4 rounded-lg transition-colors flex items-center justify-center whitespace-nowrap">
                        <i data-lucide="cloud-download" class="w-4 h-4 mr-2"></i>
                        Sync Sekarang
                    </a>
                </div>

                <?php if (!empty($syncGroups)): ?>
                    <div class="mt-4 border-t <?= $isGowa ? 'border-purple-200' : 'border-green-200'; ?> pt-4">
                        <label
                            class="block text-sm font-medium <?= $isGowa ? 'text-purple-800' : 'text-green-800'; ?> mb-2">Pilih
                            Grup untuk Ditambahkan:</label>
                        <div
                            class="max-h-48 overflow-y-auto bg-white rounded-lg border <?= $isGowa ? 'border-purple-200 divide-purple-100' : 'border-green-200 divide-green-100'; ?> divide-y">
                            <?php foreach ($syncGroups as $grp):
                                // GOWA returns JID differently
                                $grpId = $isGowa ? ($grp['JID'] ?? $grp['jid'] ?? '') : ($grp['id'] ?? '');
                                $grpName = $isGowa ? ($grp['Name'] ?? $grp['name'] ?? 'Grup') : ($grp['name'] ?? 'Grup');
                                ?>
                                <div class="px-3 py-2 flex items-center justify-between hover:bg-slate-50">
                                    <div>
                                        <span class="font-medium text-slate-800"><?= htmlspecialchars($grpName); ?></span>
                                        <span
                                            class="text-xs text-slate-500 ml-2 font-mono"><?= htmlspecialchars($grpId); ?></span>
                                    </div>
                                    <button type="button"
                                        onclick="useGrupId('<?= htmlspecialchars($grpId); ?>', '<?= htmlspecialchars(addslashes($grpName)); ?>')"
                                        class="text-xs bg-primary-100 hover:bg-primary-200 text-primary-700 px-2 py-1 rounded">
                                        Gunakan
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Info -->
            <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-100">
                <div class="flex">
                    <i data-lucide="info" class="w-5 h-5 text-blue-600 mr-3 flex-shrink-0"></i>
                    <div class="text-sm text-blue-800">
                        <p class="font-medium mb-1">Format ID Grup WhatsApp:</p>
                        <ul class="list-disc list-inside text-blue-700 space-y-1">
                            <li><strong>Format Baru (Rekomendasi):</strong> <code
                                    class="bg-blue-100 px-1 rounded">120363293602727822</code></li>
                            <li><strong>Format Lama (Tidak Didukung):</strong> <code
                                    class="bg-red-100 px-1 rounded line-through">628123456789-1234567890@g.us</code>
                            </li>
                        </ul>
                        <p class="mt-2 text-red-600 font-medium">
                            ⚠️ Gunakan tombol "Sync Sekarang" untuk mendapatkan ID grup yang valid dari Fonnte.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Add Grup Modal -->
<div id="addGrupModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50"
    style="display: none;">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
        <div class="p-5 border-b border-slate-200">
            <h5 class="text-lg font-semibold text-slate-800">Tambah Grup WhatsApp</h5>
            <p class="text-sm text-slate-500" id="addGrupKelasName"></p>
        </div>
        <form id="addGrupForm" action="<?= BASEURL; ?>/admin/tambahGrupWaKelas" method="POST" class="p-5">
            <input type="hidden" name="id_kelas" id="addGrupIdKelas">
            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1">Nama Grup</label>
                <input type="text" name="nama_grup" required placeholder="Contoh: Grup Ortu, Grup Umum"
                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
            <div class="mb-6">
                <label class="block text-sm font-medium text-slate-700 mb-1">Nomor/ID Grup WA</label>
                <input type="text" name="grup_wa_id" required placeholder="628123456789"
                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
            <div class="flex space-x-3">
                <button type="button" onclick="closeAddGrupModal()"
                    class="flex-1 px-4 py-2 border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50 transition-colors">
                    Batal
                </button>
                <button type="submit"
                    class="flex-1 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Grup Modal -->
<div id="editGrupModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50"
    style="display: none;">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
        <div class="p-5 border-b border-slate-200">
            <h5 class="text-lg font-semibold text-slate-800">Edit Grup WhatsApp</h5>
        </div>
        <form id="editGrupForm" action="<?= BASEURL; ?>/admin/editGrupWaKelas" method="POST" class="p-5">
            <input type="hidden" name="id" id="editGrupId">
            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1">Nama Grup</label>
                <input type="text" name="nama_grup" id="editGrupNama" required
                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
            <div class="mb-6">
                <label class="block text-sm font-medium text-slate-700 mb-1">Nomor/ID Grup WA</label>
                <input type="text" name="grup_wa_id" id="editGrupWaId" required
                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
            <div class="flex space-x-3">
                <button type="button" onclick="closeEditGrupModal()"
                    class="flex-1 px-4 py-2 border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50 transition-colors">
                    Batal
                </button>
                <button type="submit"
                    class="flex-1 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Add Grup Modal
    function showAddGrupModal(idKelas, namaKelas) {
        document.getElementById('addGrupIdKelas').value = idKelas;
        document.getElementById('addGrupKelasName').textContent = 'Kelas: ' + namaKelas;
        document.getElementById('addGrupModal').style.display = 'flex';
    }

    function closeAddGrupModal() {
        document.getElementById('addGrupModal').style.display = 'none';
    }

    // Edit Grup Modal
    function showEditGrupModal(id, nama, waId) {
        document.getElementById('editGrupId').value = id;
        document.getElementById('editGrupNama').value = nama;
        document.getElementById('editGrupWaId').value = waId;
        document.getElementById('editGrupModal').style.display = 'flex';
    }

    function closeEditGrupModal() {
        document.getElementById('editGrupModal').style.display = 'none';
    }

    // Toggle Grup Active
    function toggleGrup(id) {
        if (!confirm('Ubah status aktif grup ini?')) return;
        window.location.href = '<?= BASEURL; ?>/admin/toggleGrupWaKelas/' + id;
    }

    // Test Pesan Grup
    function testGrup(id, nama) {
        if (!confirm('Kirim pesan test ke grup "' + nama + '"?')) return;
        window.location.href = '<?= BASEURL; ?>/admin/testGrupWa/' + id;
    }

    // Delete Grup
    function deleteGrup(id, nama) {
        if (!confirm('Hapus grup "' + nama + '"? Aksi ini tidak dapat dibatalkan.')) return;
        window.location.href = '<?= BASEURL; ?>/admin/hapusGrupWaKelas/' + id;
    }

    // Use Grup ID from Fonnte sync
    function useGrupId(grupId, grupName) {
        // Auto-fill the Add Grup modal fields
        document.getElementById('addGrupNama').value = grupName;
        document.getElementById('addGrupWaId').value = grupId;

        // Prompt user to select a class first
        alert('ID Grup "' + grupName + '" siap digunakan!\n\nSekarang klik tombol "+ Tambah" pada kelas yang ingin Anda tambahkan grup ini.');
    }

    // Close modal on backdrop click
    document.getElementById('addGrupModal').addEventListener('click', function (e) {
        if (e.target === this) closeAddGrupModal();
    });
    document.getElementById('editGrupModal').addEventListener('click', function (e) {
        if (e.target === this) closeEditGrupModal();
    });
</script>