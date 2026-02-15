<?php // Admin - Daftar Kelas untuk Pembayaran ?>
<div class="p-4 sm:p-6">
  <!-- Header Section -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div class="flex items-center gap-3">
      <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
      </div>
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Pembayaran Sekolah</h1>
        <p class="text-sm text-gray-500">Kelola pembayaran per kelas</p>
      </div>
    </div>
    
    <div class="flex flex-wrap gap-2">
      <a href="<?= BASEURL ?>/admin/pembayaranRiwayat" class="px-4 py-2.5 bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white rounded-lg text-sm font-medium flex items-center gap-2 shadow-md hover:shadow-lg transition-all">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>Riwayat Semua</span>
      </a>
    </div>
  </div>

  <!-- Daftar Kelas -->
  <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
    <?php if (empty($data['kelas_list'])): ?>
      <div class="col-span-full text-center py-12">
        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400"><path d="M3 9h18v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9Z"/><path d="m3 9 2.45-4.9A2 2 0 0 1 7.24 3h9.52a2 2 0 0 1 1.8 1.1L21 9"/><path d="M12 3v6"/></svg>
        </div>
        <p class="text-gray-500 font-medium">Belum ada kelas</p>
      </div>
    <?php else: ?>
      <?php foreach ($data['kelas_list'] as $kelas): ?>
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 hover:shadow-xl transition-shadow overflow-hidden">
          <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-5 text-white">
            <div class="flex items-center justify-between mb-2">
              <h3 class="text-lg font-bold"><?= htmlspecialchars($kelas['nama_kelas']) ?></h3>
              <span class="bg-white/20 px-2 py-1 rounded-full text-xs font-medium"><?= $kelas['total_tagihan'] ?> Tagihan</span>
            </div>
            <p class="text-blue-100 text-sm">
              <?php if (!empty($kelas['wali_kelas'])): ?>
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="inline mr-1"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Wali: <?= htmlspecialchars($kelas['wali_kelas']['nama_guru'] ?? 'Belum ditugaskan') ?>
              <?php else: ?>
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="inline mr-1"><circle cx="12" cy="12" r="10"/><line x1="15" x2="9" y1="9" y2="15"/><line x1="9" x2="15" y1="9" y2="15"/></svg>
                Belum ada wali kelas
              <?php endif; ?>
            </p>
          </div>
          
          <div class="p-4">
            <a href="<?= BASEURL ?>/admin/pembayaranKelas/<?= (int)$kelas['id_kelas'] ?>" class="block w-full px-4 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg font-medium shadow-sm hover:shadow-md transition-all text-center">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="inline mr-2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
              Lihat Pembayaran
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>
