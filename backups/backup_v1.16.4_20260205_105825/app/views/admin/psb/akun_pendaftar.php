<?php
// File: app/views/admin/psb/akun_pendaftar.php
// Halaman daftar akun calon siswa

$this->view('templates/header', $data);
?>

<div class="flex">
    <?php $this->view('templates/sidebar_psb', $data); ?>

    <main class="flex-1 p-6 bg-gray-50 min-h-screen overflow-x-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-secondary-800">Akun Calon Siswa</h1>
            <p class="text-secondary-500">Daftar akun yang terdaftar di sistem PSB</p>
        </div>

        <!-- Tabel Akun -->
        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
            <div class="p-4 border-b flex items-center justify-between">
                <h3 class="font-semibold text-secondary-700">Daftar Akun</h3>
                <span class="text-sm text-secondary-500"><?= count($data['akun_list']); ?> akun terdaftar</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-secondary-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-secondary-600">No</th>
                            <th class="px-4 py-3 text-left font-semibold text-secondary-600">NISN</th>
                            <th class="px-4 py-3 text-left font-semibold text-secondary-600">Nama Lengkap</th>
                            <th class="px-4 py-3 text-left font-semibold text-secondary-600">No. WA</th>
                            <th class="px-4 py-3 text-left font-semibold text-secondary-600">Pendaftaran</th>
                            <th class="px-4 py-3 text-left font-semibold text-secondary-600">Terdaftar</th>
                            <th class="px-4 py-3 text-center font-semibold text-secondary-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-secondary-100">
                        <?php if (empty($data['akun_list'])): ?>
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-secondary-400">
                                    Belum ada akun terdaftar
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($data['akun_list'] as $i => $akun): ?>
                                <tr class="hover:bg-secondary-50">
                                    <td class="px-4 py-3"><?= $i + 1; ?></td>
                                    <td class="px-4 py-3 font-mono"><?= htmlspecialchars($akun['nisn']); ?></td>
                                    <td class="px-4 py-3 font-medium"><?= htmlspecialchars($akun['nama_lengkap']); ?></td>
                                    <td class="px-4 py-3"><?= htmlspecialchars($akun['no_wa']); ?></td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 bg-sky-100 text-sky-700 rounded-full text-xs font-semibold">
                                            <?= $akun['jumlah_pendaftaran'] ?? 0; ?> pendaftaran
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-secondary-500">
                                        <?= date('d/m/Y H:i', strtotime($akun['created_at'])); ?>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <button onclick="openEditModal(<?= htmlspecialchars(json_encode($akun)); ?>)"
                                                class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="Edit">
                                                <i data-lucide="edit" class="w-4 h-4"></i>
                                            </button>
                                            <?php if (($akun['jumlah_pendaftaran'] ?? 0) == 0): ?>
                                                <a href="<?= BASEURL; ?>/psb/hapusAkun/<?= $akun['id_akun']; ?>"
                                                    onclick="return confirm('Yakin hapus akun ini?')"
                                                    class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Hapus">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- Modal Edit -->
<div id="editModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4">
        <div class="p-5 border-b flex items-center justify-between">
            <h3 class="font-bold text-lg">Edit Akun</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form action="<?= BASEURL; ?>/psb/updateAkun" method="POST" class="p-5 space-y-4">
            <input type="hidden" name="id_akun" id="edit_id_akun">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" id="edit_nama_lengkap" required
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-sky-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">No. WhatsApp</label>
                <input type="text" name="no_wa" id="edit_no_wa" required
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-sky-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru (kosongkan jika tidak
                    ganti)</label>
                <input type="password" name="password"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-sky-500">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeEditModal()"
                    class="flex-1 px-4 py-2 border rounded-lg hover:bg-gray-50">Batal</button>
                <button type="submit"
                    class="flex-1 px-4 py-2 bg-sky-500 text-white rounded-lg hover:bg-sky-600">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(akun) {
        document.getElementById('edit_id_akun').value = akun.id_akun;
        document.getElementById('edit_nama_lengkap').value = akun.nama_lengkap;
        document.getElementById('edit_no_wa').value = akun.no_wa;
        document.getElementById('editModal').classList.remove('hidden');
        document.getElementById('editModal').classList.add('flex');
    }
    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
        document.getElementById('editModal').classList.remove('flex');
    }
</script>

<?php $this->view('templates/footer', $data); ?>