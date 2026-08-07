<main class="flex-1 overflow-x-hidden overflow-y-auto bg-secondary-50 p-4 md:p-6">
    <!-- Header -->
    <div class="mb-4">
        <div class="bg-white shadow-sm rounded-xl p-4 md:p-5">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                    <h4 class="text-lg md:text-xl font-bold text-slate-800 mb-1">Pengaturan Rapor</h4>
                    <p class="text-slate-500 text-sm">
                        Kelola pengaturan rapor untuk setiap wali kelas
                        <span class="mx-2">•</span>
                        <span class="font-semibold text-slate-700"><?= $_SESSION['nama_semester_aktif'] ?? 'Semester Tidak Diketahui'; ?></span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <?php Flasher::flash(); ?>

    <!-- Daftar Wali Kelas -->
    <div class="bg-white shadow-sm rounded-xl overflow-hidden">
        <div class="p-4 border-b border-gray-100">
            <h5 class="font-semibold text-gray-800">
                <i data-lucide="users" class="w-5 h-5 inline-block mr-2 text-primary-500"></i>
                Daftar Wali Kelas
            </h5>
        </div>
        
        <?php if (!empty($data['wali_kelas_list'])): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kelas</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Wali Kelas</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Pengaturan</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php 
                    $no = 1;
                    foreach ($data['wali_kelas_list'] as $wk): 
                        $sudahDiatur = $wk['sudah_diatur'] ?? false;
                    ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $no++; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800">
                                <?= htmlspecialchars($wk['nama_kelas'] ?? '-'); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white font-bold">
                                        <?= strtoupper(substr($wk['nama_guru'] ?? 'G', 0, 1)); ?>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($wk['nama_guru'] ?? '-'); ?></div>
                                    <div class="text-sm text-gray-500"><?= htmlspecialchars($wk['nik'] ?? '-'); ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($sudahDiatur): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i data-lucide="check-circle" class="w-3 h-3 mr-1"></i>
                                    Sudah Diatur
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <i data-lucide="alert-circle" class="w-3 h-3 mr-1"></i>
                                    Belum Diatur
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <a href="<?= BASEURL; ?>/admin/pengaturanRapor/<?= $wk['id_guru']; ?>" 
                               class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-lg shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                                <i data-lucide="settings" class="w-3.5 h-3.5 mr-1"></i>
                                Atur Rapor
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="p-8 text-center">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                <i data-lucide="users" class="w-8 h-8 text-gray-400"></i>
            </div>
            <h6 class="text-gray-500 font-medium mb-2">Belum Ada Wali Kelas</h6>
            <p class="text-sm text-gray-400">Silakan tambahkan wali kelas terlebih dahulu</p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Info Box -->
    <div class="mt-5 bg-blue-50 border border-blue-200 rounded-xl p-4">
        <div class="flex gap-3">
            <div class="shrink-0">
                <i data-lucide="info" class="w-5 h-5 text-blue-600"></i>
            </div>
            <div class="flex-1">
                <h6 class="font-semibold text-blue-900 text-sm mb-1">Informasi</h6>
                <ul class="text-xs text-blue-700 space-y-1 list-disc list-inside">
                    <li>Klik "Atur Rapor" untuk mengatur pengaturan rapor setiap wali kelas</li>
                    <li>Pengaturan mencakup kop rapor, nama madrasah, tanda tangan, dan persentase nilai</li>
                    <li>Setiap wali kelas dapat memiliki pengaturan yang berbeda</li>
                </ul>
            </div>
        </div>
    </div>
</main>

<script>
if (typeof lucide !== 'undefined') {
    lucide.createIcons();
}
</script>
