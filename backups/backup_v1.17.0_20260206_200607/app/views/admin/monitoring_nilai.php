<?php // Admin - Monitoring Nilai: Daftar Kelas ?>
<div class="p-4 sm:p-6">
  <!-- Header -->
  <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
    <div class="flex items-center justify-between flex-wrap gap-4">
      <div class="flex items-center gap-4">
        <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 p-4 rounded-xl shadow-lg">
          <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v18h18M7 13l3 3 7-7"/>
          </svg>
        </div>
        <div>
          <h1 class="text-2xl font-bold text-gray-800">Monitoring Nilai</h1>
          <p class="text-sm text-gray-600">Pilih kelas untuk melihat ringkasan nilai</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Kelas List -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    <?php if (empty($data['kelas_list'])): ?>
      <div class="col-span-full bg-white rounded-xl shadow p-8 text-center">
        <svg class="w-14 h-14 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c1.657 0 3-.895 3-2s-1.343-2-3-2-3 .895-3 2 1.343 2 3 2zm0 2c-2.21 0-4 1.343-4 3v3h8v-3c0-1.657-1.79-3-4-3z"/>
        </svg>
        <p class="text-gray-500">Belum ada data kelas untuk ditampilkan.</p>
      </div>
    <?php else: ?>
      <?php foreach ($data['kelas_list'] as $k): ?>
        <a href="<?= BASEURL ?>/admin/monitoringNilaiKelas/<?= (int)$k['id_kelas'] ?>" class="block group">
          <div class="bg-gradient-to-br from-white to-slate-50 rounded-xl shadow hover:shadow-lg transition p-4 border border-slate-100">
            <div class="flex items-center gap-3 mb-3">
              <div class="bg-indigo-100 text-indigo-600 p-2 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                </svg>
              </div>
              <div class="text-slate-800 font-semibold text-lg truncate"><?= htmlspecialchars($k['nama_kelas']) ?></div>
            </div>
            <div class="flex items-center justify-between text-sm text-slate-600">
              <div class="flex items-center gap-1">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A3 3 0 017 17h10a3 3 0 011.879.804M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span>Wali Kelas:</span>
                <span class="font-medium ml-1"><?php $wk = $k['wali_kelas']; echo htmlspecialchars($wk['nama_guru'] ?? '-'); ?></span>
              </div>
              <div class="text-indigo-600 font-semibold group-hover:translate-x-0.5 transition">Lihat »</div>
            </div>
          </div>
        </a>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>
