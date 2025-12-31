<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Tagihan Siswa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-3 sm:p-6">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/></svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Rekap Tagihan Siswa</h2>
                        <p class="text-sm text-gray-500">Kelas: <span class="font-semibold text-indigo-600"><?= htmlspecialchars($data['wali_kelas_info']['nama_kelas']); ?></span></p>
                    </div>
                </div>
                
                <div class="flex flex-wrap gap-2">
                    <a href="<?= BASEURL; ?>/waliKelas/pembayaran" 
                       class="px-4 py-2.5 bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white rounded-lg text-sm font-medium flex items-center gap-2 shadow-md hover:shadow-lg transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
                        Kembali
                    </a>
                    <a href="<?= BASEURL; ?>/waliKelas/rekapTagihanPDF" 
                       class="px-4 py-2.5 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white rounded-lg text-sm font-medium flex items-center gap-2 shadow-md hover:shadow-lg transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                        Download PDF
                    </a>
                </div>
            </div>
        </div>

        <!-- Flash Message -->
        <?php Flasher::flash(); ?>

        <!-- Summary Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <?php
                $totalSemuaTagihan = 0;
                $totalSemuaDibayar = 0;
                $totalSemuaSisa = 0;
                $jumlahSiswa = count($data['rekap_data']);
                
                foreach ($data['rekap_data'] as $item) {
                    $totalSemuaTagihan += $item['total_tagihan'];
                    $totalSemuaDibayar += $item['total_dibayar'];
                    $totalSemuaSisa += $item['total_sisa'];
                }
            ?>
            
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-5 text-white">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-12 h-12 bg-white/20 backdrop-blur rounded-lg flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                </div>
                <p class="text-sm text-blue-100 mb-1">Total Siswa</p>
                <p class="text-3xl font-bold"><?= $jumlahSiswa; ?></p>
            </div>

            <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl shadow-lg p-5 text-white">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-12 h-12 bg-white/20 backdrop-blur rounded-lg flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    </div>
                </div>
                <p class="text-sm text-orange-100 mb-1">Total Tagihan</p>
                <p class="text-xl font-bold">Rp <?= number_format($totalSemuaTagihan, 0, ',', '.'); ?></p>
            </div>

            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-5 text-white">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-12 h-12 bg-white/20 backdrop-blur rounded-lg flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                </div>
                <p class="text-sm text-green-100 mb-1">Total Dibayar</p>
                <p class="text-xl font-bold">Rp <?= number_format($totalSemuaDibayar, 0, ',', '.'); ?></p>
            </div>

            <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl shadow-lg p-5 text-white">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-12 h-12 bg-white/20 backdrop-blur rounded-lg flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                    </div>
                </div>
                <p class="text-sm text-red-100 mb-1">Total Sisa</p>
                <p class="text-xl font-bold">Rp <?= number_format($totalSemuaSisa, 0, ',', '.'); ?></p>
            </div>
        </div>

        <!-- Rekap Table -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
            <!-- Mobile Cards View -->
            <div class="block lg:hidden">
                <?php if (!empty($data['rekap_data'])): ?>
                    <div class="divide-y divide-gray-200">
                        <?php foreach ($data['rekap_data'] as $index => $item): ?>
                        <div class="p-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center mr-3 shadow-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-gray-900">
                                            <?= htmlspecialchars($item['siswa']['nama_siswa']); ?>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            NISN: <?= htmlspecialchars($item['siswa']['nisn']); ?>
                                        </div>
                                    </div>
                                </div>
                                <span class="inline-flex items-center justify-center w-7 h-7 bg-gray-100 rounded-full text-xs font-bold text-gray-600">#<?= $index + 1; ?></span>
                            </div>
                            
                            <div class="space-y-2 mb-3">
                                <?php foreach ($item['tagihan'] as $tagIdx => $tag): ?>
                                <div class="flex items-center justify-between text-xs bg-gray-50 rounded-lg p-2">
                                    <span class="text-gray-700 font-medium"><?= htmlspecialchars($data['tagihan_list'][$tagIdx]['nama'] ?? ''); ?></span>
                                    <div class="text-right">
                                        <?php if ($tag['status'] === 'Lunas'): ?>
                                            <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-semibold bg-green-100 text-green-700">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1"><polyline points="20 6 9 17 4 12"/></svg>
                                                Lunas
                                            </span>
                                        <?php elseif ($tag['status'] === 'Cicil'): ?>
                                            <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-semibold bg-yellow-100 text-yellow-700 mb-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                                Cicil
                                            </span>
                                            <div class="text-xs text-gray-600">
                                                Rp <?= number_format($tag['dibayar'], 0, ',', '.'); ?> / Rp <?= number_format($tag['nominal'], 0, ',', '.'); ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-semibold bg-red-100 text-red-700 mb-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1"><circle cx="12" cy="12" r="10"/><line x1="15" x2="9" y1="9" y2="15"/><line x1="9" x2="15" y1="9" y2="15"/></svg>
                                                Belum
                                            </span>
                                            <div class="text-xs text-gray-600">
                                                Rp <?= number_format($tag['nominal'], 0, ',', '.'); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="pt-3 border-t-2 border-indigo-100 bg-gradient-to-r from-indigo-50 to-blue-50 -mx-4 px-4 py-3 rounded-b-lg">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-bold text-gray-700">Total Keseluruhan</span>
                                    <div class="text-right">
                                        <div class="font-bold text-gray-900 text-base">
                                            Rp <?= number_format($item['total_tagihan'], 0, ',', '.'); ?>
                                        </div>
                                        <div class="text-xs text-green-600 font-medium">
                                            ✓ Bayar: Rp <?= number_format($item['total_dibayar'], 0, ',', '.'); ?>
                                        </div>
                                        <?php if ($item['total_sisa'] > 0): ?>
                                        <div class="text-xs text-red-600 font-semibold">
                                            ⚠ Sisa: Rp <?= number_format($item['total_sisa'], 0, ',', '.'); ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="px-6 py-12 text-center">
                        <i data-lucide="inbox" class="w-12 h-12 text-gray-400 mb-3 mx-auto"></i>
                        <p class="text-gray-500">Belum ada data siswa atau tagihan</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Desktop Table View -->
            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">No</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">NISN / Nama Siswa</th>
                            <?php foreach ($data['tagihan_list'] as $tagihan): ?>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">
                                <?= htmlspecialchars($tagihan['nama'] ?? ''); ?>
                            </th>
                            <?php endforeach; ?>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider bg-indigo-50">Total Keseluruhan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (!empty($data['rekap_data'])): ?>
                            <?php foreach ($data['rekap_data'] as $index => $item): ?>
                            <tr class="hover:bg-blue-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="inline-flex items-center justify-center w-8 h-8 bg-gray-100 rounded-full text-sm font-bold text-gray-600">
                                        <?= $index + 1; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center mr-3 shadow-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                        </div>
                                        <div>
                                            <div class="text-sm font-semibold text-gray-900">
                                                <?= htmlspecialchars($item['siswa']['nama_siswa']); ?>
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                NISN: <?= htmlspecialchars($item['siswa']['nisn']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <?php foreach ($item['tagihan'] as $tag): ?>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="text-sm">
                                        <?php if ($tag['status'] === 'Lunas'): ?>
                                            <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-green-100 text-green-700 shadow-sm">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1"><polyline points="20 6 9 17 4 12"/></svg>
                                                Lunas
                                            </span>
                                        <?php elseif ($tag['status'] === 'Cicil'): ?>
                                            <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-yellow-100 text-yellow-700 shadow-sm">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                                Cicil
                                            </span>
                                            <div class="text-xs text-gray-600 mt-1.5 font-medium">
                                                Rp <?= number_format($tag['dibayar'], 0, ',', '.'); ?> / Rp <?= number_format($tag['nominal'], 0, ',', '.'); ?>
                                            </div>
                                            <div class="text-xs text-red-600 font-semibold">
                                                Sisa: Rp <?= number_format($tag['sisa'], 0, ',', '.'); ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-100 text-red-700 shadow-sm">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1"><circle cx="12" cy="12" r="10"/><line x1="15" x2="9" y1="9" y2="15"/><line x1="9" x2="15" y1="9" y2="15"/></svg>
                                                Belum
                                            </span>
                                            <div class="text-xs text-gray-600 mt-1.5 font-medium">
                                                Rp <?= number_format($tag['nominal'], 0, ',', '.'); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <?php endforeach; ?>
                                <td class="px-6 py-4 whitespace-nowrap text-center bg-gradient-to-r from-indigo-50 to-blue-50">
                                    <div class="text-sm">
                                        <div class="font-bold text-gray-900 text-base">
                                            Rp <?= number_format($item['total_tagihan'], 0, ',', '.'); ?>
                                        </div>
                                        <div class="text-xs text-green-600 font-semibold mt-1">
                                            ✓ Bayar: Rp <?= number_format($item['total_dibayar'], 0, ',', '.'); ?>
                                        </div>
                                        <?php if ($item['total_sisa'] > 0): ?>
                                        <div class="text-xs text-red-600 font-bold mt-0.5">
                                            ⚠ Sisa: Rp <?= number_format($item['total_sisa'], 0, ',', '.'); ?>
                                        </div>
                                        <?php else: ?>
                                        <div class="text-xs text-green-600 font-bold mt-0.5">
                                            ✓ Lunas Semua
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?= count($data['tagihan_list']) + 3; ?>" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400 mb-3"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                                        <p class="text-gray-500 font-medium">Belum ada data siswa atau tagihan</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Legend -->
        <div class="mt-6 bg-gradient-to-br from-white to-gray-50 rounded-xl shadow-lg border border-gray-200 p-5">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 bg-indigo-500 rounded-lg flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                </div>
                <h3 class="text-sm font-bold text-gray-800">Keterangan Status Pembayaran:</h3>
            </div>
            <div class="grid sm:grid-cols-3 gap-3 text-sm">
                <div class="flex items-center gap-2 bg-green-50 p-3 rounded-lg border border-green-200">
                    <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-green-100 text-green-700 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1"><polyline points="20 6 9 17 4 12"/></svg>
                        Lunas
                    </span>
                    <span class="text-gray-700 font-medium">Dibayar penuh</span>
                </div>
                <div class="flex items-center gap-2 bg-yellow-50 p-3 rounded-lg border border-yellow-200">
                    <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-yellow-100 text-yellow-700 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        Cicil
                    </span>
                    <span class="text-gray-700 font-medium">Dicicil/Sebagian</span>
                </div>
                <div class="flex items-center gap-2 bg-red-50 p-3 rounded-lg border border-red-200">
                    <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-100 text-red-700 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1"><circle cx="12" cy="12" r="10"/><line x1="15" x2="9" y1="9" y2="15"/><line x1="9" x2="15" y1="9" y2="15"/></svg>
                        Belum
                    </span>
                    <span class="text-gray-700 font-medium">Belum dibayar</span>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Initialize Lucide icons (fallback if needed)
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    </script>
</body>
</html>
