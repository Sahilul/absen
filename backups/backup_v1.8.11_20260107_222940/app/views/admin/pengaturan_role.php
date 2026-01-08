<?php
// File: app/views/admin/pengaturan_role.php
$guruList = $data['guru_list'] ?? [];
$fungsiTersedia = $data['fungsi_tersedia'] ?? [];
$namaTp = $data['nama_tp'] ?? '';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-secondary-800 mb-2">
                    <i data-lucide="user-cog" class="inline-block w-8 h-8 mr-2 text-primary-600"></i>
                    Pengaturan Fungsi Guru
                </h1>
                <p class="text-secondary-600">
                    Tetapkan fungsi tambahan untuk guru (Bendahara, Petugas PSB, dll) -
                    <span class="font-semibold text-primary-600"><?= htmlspecialchars($namaTp); ?></span>
                </p>
            </div>
        </div>
    </div>

    <?php if (class_exists('Flasher')) {
        Flasher::flash();
    } ?>

    <!-- Info Card -->
    <div class="glass-effect rounded-2xl p-4 mb-6 border border-blue-200 bg-blue-50/50">
        <div class="flex items-start gap-3">
            <i data-lucide="info" class="w-5 h-5 text-blue-600 mt-0.5"></i>
            <div class="text-sm text-blue-800">
                <p class="font-medium mb-1">Cara Penggunaan:</p>
                <ul class="list-disc list-inside space-y-1 text-blue-700">
                    <li><strong>Bendahara</strong> - Dapat menginput pembayaran untuk SEMUA kelas</li>
                    <li><strong>Petugas PSB</strong> - Dapat mengelola data PSB</li>
                    <li><strong>Petugas Buku Tamu</strong> - Dapat mengelola buku tamu digital</li>
                    <li>Satu guru bisa memiliki beberapa fungsi sekaligus</li>
                    <li>Wali kelas juga bisa merangkap sebagai bendahara</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Form -->
    <form action="<?= BASEURL; ?>/admin/savePengaturanRole" method="POST">
        <div class="glass-effect rounded-2xl border border-white/20 overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-primary-500 to-primary-600 px-6 py-4">
                <h2 class="text-xl font-bold text-white flex items-center">
                    <i data-lucide="users" class="w-5 h-5 mr-2"></i>
                    Daftar Guru & Fungsi
                </h2>
            </div>

            <!-- Search -->
            <div class="p-4 bg-white/50 border-b">
                <input type="text" id="searchGuru" placeholder="Cari nama guru..."
                    class="w-full max-w-md px-4 py-2 border border-secondary-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:outline-none">
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-secondary-200">
                    <thead class="bg-secondary-50">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                                No</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                                Nama Guru</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                                NIK</th>
                            <?php foreach ($fungsiTersedia as $kode => $label): ?>
                                <th
                                    class="px-4 py-3 text-center text-xs font-medium text-secondary-500 uppercase tracking-wider">
                                    <?= $label; ?>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-secondary-200" id="tableBody">
                        <?php if (!empty($guruList)): ?>
                            <?php $no = 1;
                            foreach ($guruList as $guru):
                                $fungsiGuru = explode(',', $guru['fungsi_list'] ?? '');
                                ?>
                                <tr class="hover:bg-secondary-50 transition-colors guru-row">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary-900"><?= $no++; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap guru-nama">
                                        <div class="flex items-center">
                                            <div
                                                class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white font-bold mr-3">
                                                <?= strtoupper(substr($guru['nama_guru'] ?? '', 0, 1)); ?>
                                            </div>
                                            <div class="text-sm font-medium text-secondary-900">
                                                <?= htmlspecialchars($guru['nama_guru'] ?? ''); ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary-500">
                                        <?= htmlspecialchars($guru['nik'] ?? '-'); ?>
                                    </td>
                                    <?php foreach ($fungsiTersedia as $kode => $label):
                                        $checked = in_array($kode, $fungsiGuru) ? 'checked' : '';
                                        ?>
                                        <td class="px-4 py-4 text-center">
                                            <label class="inline-flex items-center">
                                                <input type="checkbox" name="guru[<?= $guru['id_guru']; ?>][]" value="<?= $kode; ?>"
                                                    <?= $checked; ?>
                                                    class="w-5 h-5 text-primary-600 bg-white border-secondary-300 rounded focus:ring-primary-500 cursor-pointer">
                                            </label>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?= 3 + count($fungsiTersedia); ?>"
                                    class="px-6 py-8 text-center text-secondary-500">
                                    <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-2 text-secondary-300"></i>
                                    <p>Tidak ada data guru</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 bg-secondary-50 border-t flex justify-between items-center">
                <p class="text-sm text-secondary-600">
                    Total: <span class="font-semibold"><?= count($guruList); ?></span> guru
                </p>
                <button type="submit"
                    class="gradient-primary text-white font-semibold py-2 px-6 rounded-lg flex items-center gap-2 shadow-lg hover:shadow-xl transition-all">
                    <i data-lucide="save" class="w-4 h-4"></i> Simpan Pengaturan
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof lucide !== 'undefined') lucide.createIcons();

        // Search functionality
        const searchInput = document.getElementById('searchGuru');
        const rows = document.querySelectorAll('.guru-row');

        searchInput.addEventListener('keyup', function () {
            const term = this.value.toLowerCase();
            rows.forEach(row => {
                const nama = row.querySelector('.guru-nama').textContent.toLowerCase();
                row.style.display = nama.includes(term) ? '' : 'none';
            });
        });
    });
</script>