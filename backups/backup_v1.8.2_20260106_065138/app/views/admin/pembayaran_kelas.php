<?php // Admin - Daftar Tagihan per Kelas ?>
<div class="p-4 sm:p-6">
  <!-- Header Section -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div class="flex items-center gap-3">
      <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl flex items-center justify-center shadow-lg">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9h18v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9Z"/><path d="m3 9 2.45-4.9A2 2 0 0 1 7.24 3h9.52a2 2 0 0 1 1.8 1.1L21 9"/><path d="M12 3v6"/></svg>
      </div>
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Tagihan Kelas</h1>
        <p class="text-sm text-gray-500"><?= htmlspecialchars($data['kelas']['nama_kelas'] ?? '') ?></p>
      </div>
    </div>
    
    <a href="<?= BASEURL ?>/admin/pembayaran" class="px-4 py-2.5 bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white rounded-lg text-sm font-medium flex items-center gap-2 shadow-md hover:shadow-lg transition-all">
      <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
      <span>Kembali</span>
    </a>
  </div>

  <!-- Daftar Tagihan -->
  <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
    <div class="flex items-center gap-2 mb-4">
      <div class="w-8 h-8 bg-orange-500 rounded-lg flex items-center justify-center">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
      </div>
      <h2 class="text-lg font-semibold text-gray-800">Daftar Tagihan</h2>
    </div>
    
    <?php if (empty($data['tagihan_list'])): ?>
      <div class="text-center py-12">
        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        </div>
        <p class="text-gray-500 font-medium">Belum ada tagihan</p>
        <p class="text-sm text-gray-400 mt-1">Wali kelas dapat membuat tagihan untuk kelas ini</p>
      </div>
    <?php else: ?>
      <!-- Mobile: Cards -->
      <div class="space-y-3 lg:hidden">
        <?php foreach ($data['tagihan_list'] as $t): ?>
          <div class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition-shadow bg-gradient-to-br from-white to-gray-50">
            <div class="flex items-start justify-between mb-3">
              <div class="flex-1">
                <div class="font-semibold text-gray-800 mb-1"><?= htmlspecialchars($t['nama']) ?></div>
                <div class="flex items-center gap-2 text-sm">
                  <span class="inline-flex items-center px-2.5 py-1 bg-green-100 text-green-700 rounded-full font-medium">
                    Rp <?= number_format((int)$t['nominal_default'], 0, ',', '.') ?>
                  </span>
                  <span class="inline-flex items-center px-2.5 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium capitalize">
                    <?= htmlspecialchars($t['tipe']) ?>
                  </span>
                </div>
                <?php if ($t['jatuh_tempo']): ?>
                  <div class="flex items-center gap-1 mt-2 text-xs text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                    JT: <?= htmlspecialchars($t['jatuh_tempo']) ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>
            <a href="<?= BASEURL ?>/admin/pembayaranTagihan/<?= (int)$t['id'] ?>" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg font-medium shadow-md transition-all">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
              Lihat Detail
            </a>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Desktop: Table -->
      <div class="overflow-x-auto hidden lg:block rounded-lg border border-gray-200">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="bg-gradient-to-r from-gray-50 to-gray-100">
              <th class="text-left p-4 font-semibold text-gray-700">Nama Tagihan</th>
              <th class="text-left p-4 font-semibold text-gray-700">Nominal Default</th>
              <th class="text-left p-4 font-semibold text-gray-700">Jatuh Tempo</th>
              <th class="text-left p-4 font-semibold text-gray-700">Tipe</th>
              <th class="text-center p-4 font-semibold text-gray-700">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
          <?php foreach ($data['tagihan_list'] as $t): ?>
            <tr class="hover:bg-blue-50 transition-colors">
              <td class="p-4">
                <div class="flex items-center gap-2">
                  <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-600"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                  </div>
                  <span class="font-medium text-gray-800"><?= htmlspecialchars($t['nama']) ?></span>
                </div>
              </td>
              <td class="p-4">
                <span class="inline-flex items-center px-3 py-1.5 bg-green-100 text-green-700 rounded-full font-semibold text-sm">
                  Rp <?= number_format((int)$t['nominal_default'], 0, ',', '.') ?>
                </span>
              </td>
              <td class="p-4">
                <?php if ($t['jatuh_tempo']): ?>
                  <div class="flex items-center gap-1.5 text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                    <?= htmlspecialchars($t['jatuh_tempo']) ?>
                  </div>
                <?php else: ?>
                  <span class="text-gray-400">-</span>
                <?php endif; ?>
              </td>
              <td class="p-4">
                <span class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium capitalize">
                  <?= htmlspecialchars($t['tipe']) ?>
                </span>
              </td>
              <td class="p-4 text-center">
                <a href="<?= BASEURL ?>/admin/pembayaranTagihan/<?= (int)$t['id'] ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg font-medium shadow-sm hover:shadow-md transition-all">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                  Lihat Detail
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>
