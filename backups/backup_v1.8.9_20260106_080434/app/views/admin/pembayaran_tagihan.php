<?php 
// Admin view - Read-only pembayaran tagihan
// Gunakan view wali_kelas dengan modifikasi untuk admin (read-only)
$data['is_admin'] = true; // Flag untuk membedakan admin dan wali kelas
$data['base_url_back'] = BASEURL . '/admin/pembayaranKelas/' . ($data['kelas']['id_kelas'] ?? '');

// Override header button untuk admin
ob_start();
?>
<div class="p-4 sm:p-6">
  <!-- Header Section -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div class="flex items-center gap-3">
      <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl flex items-center justify-center shadow-lg">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
      </div>
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Detail Tagihan (Viewing)</h1>
        <p class="text-sm text-gray-500"><?= htmlspecialchars($data['tagihan']['nama'] ?? '-') ?> - <?= htmlspecialchars($data['kelas']['nama_kelas'] ?? '') ?></p>
      </div>
    </div>
    
    <a href="<?= BASEURL ?>/admin/pembayaranKelas/<?= (int)($data['kelas']['id_kelas'] ?? 0) ?>" class="px-4 py-2.5 bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white rounded-lg text-sm font-medium flex items-center gap-2 shadow-md hover:shadow-lg transition-all">
      <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
      <span>Kembali</span>
    </a>
  </div>

  <!-- Info Tagihan -->
  <div class="grid sm:grid-cols-2 gap-4 mb-6">
    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-5 text-white">
      <div class="flex items-center justify-between mb-2">
        <span class="text-green-100 text-sm font-medium">Nominal Default</span>
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
      </div>
      <div class="text-2xl font-bold">Rp <?= number_format((int)($data['tagihan']['nominal_default'] ?? 0), 0, ',', '.') ?></div>
    </div>
    
    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-5 text-white">
      <div class="flex items-center justify-between mb-2">
        <span class="text-blue-100 text-sm font-medium">Jatuh Tempo</span>
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
      </div>
      <div class="text-xl font-bold"><?= !empty($data['tagihan']['jatuh_tempo']) ? htmlspecialchars($data['tagihan']['jatuh_tempo']) : 'Tidak Ada' ?></div>
    </div>
  </div>

  <!-- Daftar Siswa (Read-Only untuk Admin) -->
  <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
    <div class="flex items-center gap-2 mb-4">
      <div class="w-8 h-8 bg-orange-500 rounded-lg flex items-center justify-center">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      </div>
      <h2 class="text-lg font-semibold text-gray-800">Status Pembayaran Per Siswa</h2>
    </div>
    
    <?php
      $map = [];
      foreach (($data['tagihan_siswa'] ?? []) as $ts) { $map[$ts['id_siswa']] = $ts; }
    ?>

    <!-- Desktop: Table -->
    <div class="overflow-x-auto rounded-lg border border-gray-200">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="bg-gradient-to-r from-gray-50 to-gray-100">
            <th class="text-left p-4 font-semibold text-gray-700">Nama Siswa</th>
            <th class="text-left p-4 font-semibold text-gray-700">Nominal</th>
            <th class="text-left p-4 font-semibold text-gray-700">Diskon</th>
            <th class="text-left p-4 font-semibold text-gray-700">Terbayar</th>
            <th class="text-left p-4 font-semibold text-gray-700">Sisa</th>
            <th class="text-center p-4 font-semibold text-gray-700">Status</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <?php foreach (($data['siswa_list'] ?? []) as $s):
            $ts = $map[$s['id_siswa']] ?? null;
            $nominal = $ts['nominal'] ?? ($data['tagihan']['nominal_default'] ?? 0);
            $diskon = $ts['diskon'] ?? 0;
            $terbayar = $ts['total_terbayar'] ?? 0;
            $sisa = max(0, $nominal - $diskon - $terbayar);
            $status = $ts['status'] ?? 'belum';
          ?>
          <tr class="hover:bg-blue-50 transition-colors">
            <td class="p-4">
              <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-600"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
                <span class="font-medium text-gray-800"><?= htmlspecialchars($s['nama_siswa']) ?></span>
              </div>
            </td>
            <td class="p-4">
              <span class="text-gray-700 font-medium">Rp <?= number_format((int)$nominal, 0, ',', '.') ?></span>
            </td>
            <td class="p-4">
              <span class="text-gray-700 font-medium">Rp <?= number_format((int)$diskon, 0, ',', '.') ?></span>
            </td>
            <td class="p-4">
              <span class="text-green-600 font-semibold">Rp <?= number_format((int)$terbayar, 0, ',', '.') ?></span>
            </td>
            <td class="p-4">
              <span class="<?= $sisa > 0 ? 'text-red-600' : 'text-green-600' ?> font-semibold">
                Rp <?= number_format((int)$sisa, 0, ',', '.') ?>
              </span>
            </td>
            <td class="p-4 text-center">
              <?php if ($status === 'lunas'): ?>
                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1"><polyline points="20 6 9 17 4 12"/></svg>
                  Lunas
                </span>
              <?php elseif ($status === 'sebagian'): ?>
                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                  Sebagian
                </span>
              <?php else: ?>
                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1"><circle cx="12" cy="12" r="10"/><line x1="15" x2="9" y1="9" y2="15"/><line x1="9" x2="15" y1="9" y2="15"/></svg>
                  Belum
                </span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
