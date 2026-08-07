<?php // View expects $data array, not individual variables 
$isBendahara = isset($data['bendahara_mode']) && $data['bendahara_mode'];
$urlPrefix = $isBendahara ? BASEURL . '/bendahara' : BASEURL . '/waliKelas';
$idKelas = $data['wali_kelas_info']['id_kelas'] ?? '';
?>
<div class="p-4 sm:p-6">
  <!-- Header Section -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div class="flex items-center gap-3">
      <?php if ($isBendahara): ?>
        <a href="<?= BASEURL ?>/bendahara/pembayaran" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="m12 19-7-7 7-7" />
            <path d="M19 12H5" />
          </svg>
        </a>
      <?php endif; ?>
      <div
        class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white"
          stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect width="20" height="14" x="2" y="5" rx="2" />
          <line x1="2" x2="22" y1="10" y2="10" />
        </svg>
      </div>
      <div>
        <h1 class="text-2xl font-bold text-gray-800"><?= $isBendahara ? 'Bendahara - ' : '' ?>Pembayaran Kelas</h1>
        <p class="text-sm text-gray-500"><?= htmlspecialchars($data['wali_kelas_info']['nama_kelas'] ?? '') ?></p>
      </div>
    </div>

    <div class="flex flex-wrap gap-2">
      <button type="button" onclick="openTagihanModal('baru')"
        class="px-4 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg text-sm font-medium flex items-center gap-2 shadow-md hover:shadow-lg transition-all">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="12" x2="12" y1="5" y2="19" />
          <line x1="5" x2="19" y1="12" y2="12" />
        </svg>
        <span>Buat Tagihan Baru</span>
      </button>
      <a href="<?= $urlPrefix ?>/rekapTagihan<?= $isBendahara ? '/' . $idKelas : '' ?>"
        class="px-4 py-2.5 bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white rounded-lg text-sm font-medium flex items-center gap-2 shadow-md hover:shadow-lg transition-all">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M3 3v18h18" />
          <path d="M18 17V9" />
          <path d="M13 17V5" />
          <path d="M8 17v-3" />
        </svg>
        <span>Rekap Tagihan</span>
      </a>
      <a href="<?= $urlPrefix ?>/pembayaranRiwayat<?= $isBendahara ? '/' . $idKelas : '' ?>"
        class="px-4 py-2.5 bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white rounded-lg text-sm font-medium flex items-center gap-2 shadow-md hover:shadow-lg transition-all">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8" />
          <path d="M3 3v5h5" />
          <path d="M12 7v5l4 2" />
        </svg>
        <span>Riwayat</span>
      </a>
      <a href="<?= $urlPrefix ?>/pembayaranExport<?= $isBendahara ? '/' . $idKelas : '' ?>"
        class="px-4 py-2.5 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white rounded-lg text-sm font-medium flex items-center gap-2 shadow-md hover:shadow-lg transition-all">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
          <polyline points="7 10 12 15 17 10" />
          <line x1="12" x2="12" y1="15" y2="3" />
        </svg>
        <span>Download PDF</span>
      </a>
    </div>
  </div>

  <!-- Modal Buat/Edit Tagihan -->
  <div id="tagihan-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" onclick="closeTagihanModal()"></div>
    <div class="relative z-10 max-w-2xl w-[92%] sm:w-[560px] mx-auto mt-24">
      <div class="bg-white rounded-xl shadow-2xl border border-gray-200 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3 bg-gradient-to-r from-blue-600 to-blue-700">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" x2="12" y1="5" y2="19" />
                <line x1="5" x2="19" y1="12" y2="12" />
              </svg>
            </div>
            <h3 id="tagihan-modal-title" class="text-white font-semibold">Buat Tagihan Baru</h3>
          </div>
          <button type="button" onclick="closeTagihanModal()" class="text-white/90 hover:text-white">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="18" x2="6" y1="6" y2="18" />
              <line x1="6" x2="18" y1="6" y2="18" />
            </svg>
          </button>
        </div>
        <?php
        $formAction = isset($data['bendahara_mode']) && $data['bendahara_mode']
          ? BASEURL . '/bendahara/simpanTagihanKelas'
          : BASEURL . '/waliKelas/simpanTagihanKelas';
        ?>
        <form id="tagihan-form" action="<?= $formAction ?>" method="POST" class="p-5 space-y-4">
          <input type="hidden" name="mode" value="baru" />
          <input type="hidden" name="id_tagihan" />
          <input type="hidden" name="id_kelas" value="<?= $data['wali_kelas_info']['id_kelas'] ?? '' ?>" />
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Tagihan</label>
            <input name="nama" type="text"
              class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
              placeholder="Contoh: SPP November" required />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Nominal Default</label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm font-medium">Rp</span>
              <input name="nominal_default" type="hidden" required />
              <input id="nominal-modal-display" type="text"
                class="w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                placeholder="0" />
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Jatuh Tempo</label>
            <input name="jatuh_tempo" type="date"
              class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
          </div>
          <div class="flex items-center justify-end gap-2 pt-2">
            <button type="button" onclick="closeTagihanModal()"
              class="px-4 py-2.5 bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white rounded-lg font-medium shadow-sm hover:shadow-md transition-all">Batal</button>
            <button type="submit" id="tagihan-submit-btn"
              class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg font-medium shadow-sm hover:shadow-md transition-all flex items-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                <polyline points="17 21 17 13 7 13 7 21" />
                <polyline points="7 3 7 8 15 8" />
              </svg>
              <span id="tagihan-submit-text">Simpan</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Daftar Tagihan -->
  <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
    <div class="flex items-center gap-2 mb-4">
      <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white"
          stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M3 9h18v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9Z" />
          <path d="m3 9 2.45-4.9A2 2 0 0 1 7.24 3h9.52a2 2 0 0 1 1.8 1.1L21 9" />
          <path d="M12 3v6" />
        </svg>
      </div>
      <h2 class="text-lg font-semibold text-gray-800">Daftar Tagihan Kelas</h2>
    </div>

    <?php if (empty($data['tagihan_list'])): ?>
      <div class="text-center py-12">
        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
            <circle cx="12" cy="12" r="10" />
            <path d="M16 16s-1.5-2-4-2-4 2-4 2" />
            <line x1="9" x2="9.01" y1="9" y2="9" />
            <line x1="15" x2="15.01" y1="9" y2="9" />
          </svg>
        </div>
        <p class="text-gray-500 font-medium">Belum ada tagihan</p>
        <p class="text-sm text-gray-400 mt-1">Klik tombol "Buat Tagihan Baru"</p>
      </div>
    <?php else: ?>
      <!-- Mobile: Cards -->
      <div class="space-y-3 lg:hidden">
        <?php foreach ($data['tagihan_list'] as $t): ?>
          <div
            class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition-shadow bg-gradient-to-br from-white to-gray-50">
            <div class="flex items-start justify-between mb-3">
              <div class="flex-1">
                <div class="font-semibold text-gray-800 mb-1"><?= htmlspecialchars($t['nama']) ?></div>
                <div class="flex items-center gap-2 text-sm">
                  <span class="inline-flex items-center px-2.5 py-1 bg-green-100 text-green-700 rounded-full font-medium">
                    Rp <?= number_format((int) $t['nominal_default'], 0, ',', '.') ?>
                  </span>
                  <span
                    class="inline-flex items-center px-2.5 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium capitalize">
                    <?= htmlspecialchars($t['tipe']) ?>
                  </span>
                </div>
                <?php if ($t['jatuh_tempo']): ?>
                  <div class="flex items-center gap-1 mt-2 text-xs text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <rect width="18" height="18" x="3" y="4" rx="2" ry="2" />
                      <line x1="16" x2="16" y1="2" y2="6" />
                      <line x1="8" x2="8" y1="2" y2="6" />
                      <line x1="3" x2="21" y1="10" y2="10" />
                    </svg>
                    JT: <?= htmlspecialchars($t['jatuh_tempo']) ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>
            <div class="grid grid-cols-4 gap-2">
              <a href="<?= $urlPrefix ?>/pembayaranTagihan/<?= (int) $t['id'] ?>"
                class="col-span-4 sm:col-span-2 inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg font-medium shadow-md transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path
                    d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z" />
                  <circle cx="12" cy="12" r="3" />
                </svg>
                Kelola
              </a>
              <button
                onclick="editTagihan(<?= (int) $t['id'] ?>, '<?= htmlspecialchars($t['nama'], ENT_QUOTES) ?>', '<?= htmlspecialchars($t['nominal_default'], ENT_QUOTES) ?>', '<?= htmlspecialchars($t['jatuh_tempo'], ENT_QUOTES) ?>')"
                class="col-span-2 sm:col-span-1 inline-flex items-center justify-center gap-1.5 px-3 py-2.5 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-lg font-medium shadow-md transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                  <path d="m15 5 4 4" />
                </svg>
                <span class="hidden sm:inline">Edit</span>
              </button>
              <button onclick="hapusTagihan(<?= (int) $t['id'] ?>, '<?= htmlspecialchars($t['nama'], ENT_QUOTES) ?>')"
                class="col-span-2 sm:col-span-1 inline-flex items-center justify-center gap-1.5 px-3 py-2.5 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-lg font-medium shadow-md transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M3 6h18" />
                  <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                  <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
                  <line x1="10" x2="10" y1="11" y2="17" />
                  <line x1="14" x2="14" y1="11" y2="17" />
                </svg>
                <span class="hidden sm:inline">Hapus</span>
              </button>
            </div>
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
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="text-blue-600">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                        <polyline points="14 2 14 8 20 8" />
                        <line x1="16" x2="8" y1="13" y2="13" />
                        <line x1="16" x2="8" y1="17" y2="17" />
                        <polyline points="10 9 9 9 8 9" />
                      </svg>
                    </div>
                    <span class="font-medium text-gray-800"><?= htmlspecialchars($t['nama']) ?></span>
                  </div>
                </td>
                <td class="p-4">
                  <span
                    class="inline-flex items-center px-3 py-1.5 bg-green-100 text-green-700 rounded-full font-semibold text-sm">
                    Rp <?= number_format((int) $t['nominal_default'], 0, ',', '.') ?>
                  </span>
                </td>
                <td class="p-4">
                  <?php if ($t['jatuh_tempo']): ?>
                    <div class="flex items-center gap-1.5 text-gray-600">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="18" height="18" x="3" y="4" rx="2" ry="2" />
                        <line x1="16" x2="16" y1="2" y2="6" />
                        <line x1="8" x2="8" y1="2" y2="6" />
                        <line x1="3" x2="21" y1="10" y2="10" />
                      </svg>
                      <?= htmlspecialchars($t['jatuh_tempo']) ?>
                    </div>
                  <?php else: ?>
                    <span class="text-gray-400">-</span>
                  <?php endif; ?>
                </td>
                <td class="p-4">
                  <span
                    class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium capitalize">
                    <?= htmlspecialchars($t['tipe']) ?>
                  </span>
                </td>
                <td class="p-4">
                  <div class="flex items-center justify-center gap-2">
                    <a href="<?= $urlPrefix ?>/pembayaranTagihan/<?= (int) $t['id'] ?>"
                      class="inline-flex items-center gap-1.5 px-3 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg font-medium shadow-sm hover:shadow-md transition-all text-sm">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path
                          d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z" />
                        <circle cx="12" cy="12" r="3" />
                      </svg>
                      Kelola
                    </a>
                    <button
                      onclick="editTagihan(<?= (int) $t['id'] ?>, '<?= htmlspecialchars($t['nama'], ENT_QUOTES) ?>', '<?= htmlspecialchars($t['nominal_default'], ENT_QUOTES) ?>', '<?= htmlspecialchars($t['jatuh_tempo'], ENT_QUOTES) ?>')"
                      class="inline-flex items-center gap-1.5 px-3 py-2 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-lg font-medium shadow-sm hover:shadow-md transition-all text-sm">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                        <path d="m15 5 4 4" />
                      </svg>
                      Edit
                    </button>
                    <button onclick="hapusTagihan(<?= (int) $t['id'] ?>, '<?= htmlspecialchars($t['nama'], ENT_QUOTES) ?>')"
                      class="inline-flex items-center gap-1.5 px-3 py-2 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-lg font-medium shadow-sm hover:shadow-md transition-all text-sm">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 6h18" />
                        <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                        <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
                        <line x1="10" x2="10" y1="11" y2="17" />
                        <line x1="14" x2="14" y1="11" y2="17" />
                      </svg>
                      Hapus
                    </button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
  // Utilities format Rupiah
  function formatRupiah(angka) {
    return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
  }
  function cleanNumber(str) { return (str || '').toString().replace(/\./g, ''); }

  // Modal logic
  const modal = document.getElementById('tagihan-modal');
  const form = document.getElementById('tagihan-form');
  const titleEl = document.getElementById('tagihan-modal-title');
  const submitBtn = document.getElementById('tagihan-submit-btn');
  const submitText = document.getElementById('tagihan-submit-text');
  const nominalDisplay = document.getElementById('nominal-modal-display');
  const nominalHidden = form ? form.querySelector('input[name="nominal_default"]') : null;

  if (nominalDisplay && nominalHidden) {
    nominalDisplay.addEventListener('input', function (e) {
      let value = cleanNumber(e.target.value);
      value = value.replace(/\D/g, '');
      nominalHidden.value = value;
      e.target.value = value ? formatRupiah(value) : '';
    });
  }

  function openTagihanModal(mode = 'baru', payload = {}) {
    if (!form) return;
    const modeInput = form.querySelector('input[name="mode"]');
    const idInput = form.querySelector('input[name="id_tagihan"]');
    const namaInput = form.querySelector('input[name="nama"]');
    const jatuhTempoInput = form.querySelector('input[name="jatuh_tempo"]');

    if (mode === 'edit') {
      modeInput.value = 'edit';
      idInput.value = payload.id || '';
      namaInput.value = payload.nama || '';
      nominalHidden.value = payload.nominal || '';
      nominalDisplay.value = payload.nominal ? formatRupiah(payload.nominal) : '';
      jatuhTempoInput.value = payload.jatuhTempo || '';
      titleEl.textContent = 'Edit Tagihan';
      submitText.textContent = 'Update';
      submitBtn.classList.remove('from-blue-600', 'to-blue-700', 'hover:from-blue-700', 'hover:to-blue-800');
      submitBtn.classList.add('from-orange-500', 'to-orange-600', 'hover:from-orange-600', 'hover:to-orange-700');
    } else {
      modeInput.value = 'baru';
      idInput.value = '';
      form.reset();
      nominalHidden.value = '';
      nominalDisplay.value = '';
      titleEl.textContent = 'Buat Tagihan Baru';
      submitText.textContent = 'Simpan';
      submitBtn.classList.remove('from-orange-500', 'to-orange-600', 'hover:from-orange-600', 'hover:to-orange-700');
      submitBtn.classList.add('from-blue-600', 'to-blue-700', 'hover:from-blue-700', 'hover:to-blue-800');
    }

    modal.classList.remove('hidden');
  }

  function closeTagihanModal() {
    if (!form) return;
    form.reset();
    form.querySelector('input[name="mode"]').value = 'baru';
    form.querySelector('input[name="id_tagihan"]').value = '';
    if (nominalHidden) nominalHidden.value = '';
    if (nominalDisplay) nominalDisplay.value = '';
    titleEl.textContent = 'Buat Tagihan Baru';
    submitText.textContent = 'Simpan';
    submitBtn.classList.remove('from-orange-500', 'to-orange-600', 'hover:from-orange-600', 'hover:to-orange-700');
    submitBtn.classList.add('from-blue-600', 'to-blue-700', 'hover:from-blue-700', 'hover:to-blue-800');
    modal.classList.add('hidden');
  }

  // Open edit with payload
  function editTagihan(id, nama, nominal, jatuhTempo) {
    openTagihanModal('edit', { id, nama, nominal, jatuhTempo });
  }

  // Close on ESC
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeTagihanModal();
  });

  // Reusable Confirm Modal
  function showConfirm({ title = 'Konfirmasi', message = '', confirmText = 'Ya', cancelText = 'Batal' } = {}) {
    return new Promise((resolve) => {
      let modal = document.getElementById('confirmModal');
      if (!modal) {
        const wrapper = document.createElement('div');
        wrapper.id = 'confirmModal';
        wrapper.className = 'hidden fixed inset-0 z-50 flex items-center justify-center p-4';
        wrapper.innerHTML = `
        <div class="absolute inset-0 bg-black/50" data-overlay></div>
        <div class="relative z-10 w-full max-w-md bg-white rounded-xl shadow-2xl overflow-hidden border border-gray-200">
          <div class=\"bg-gradient-to-r from-red-600 to-red-700 px-5 py-3\">
            <h3 id=\"confirmTitle\" class=\"text-white font-semibold\">Konfirmasi</h3>
          </div>
          <div class=\"p-5\">
            <p id=\"confirmMessage\" class=\"text-gray-700 whitespace-pre-line\"></p>
          </div>
          <div class=\"px-5 pb-5 flex justify-end gap-2\">
            <button id=\"confirmCancel\" class=\"px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg\">Batal</button>
            <button id=\"confirmOk\" class=\"px-4 py-2 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white rounded-lg\">Ya</button>
          </div>
        </div>`;
        document.body.appendChild(wrapper);
        modal = wrapper;

        const close = (val) => {
          modal.classList.add('hidden');
          resolve(val);
        };
        modal.addEventListener('click', (e) => { if (e.target.hasAttribute('data-overlay')) close(false); });
        modal.querySelector('#confirmCancel').addEventListener('click', () => close(false));
        modal.querySelector('#confirmOk').addEventListener('click', () => close(true));
        document.addEventListener('keydown', (e) => { if (!modal.classList.contains('hidden') && e.key === 'Escape') close(false); });
      }

      modal.querySelector('#confirmTitle').textContent = title;
      modal.querySelector('#confirmMessage').textContent = message;
      modal.querySelector('#confirmOk').textContent = confirmText;
      modal.querySelector('#confirmCancel').textContent = cancelText;
      modal.classList.remove('hidden');
    });
  }

  // Fungsi Hapus Tagihan
  async function hapusTagihan(id, nama) {
    const ok = await showConfirm({
      title: 'Hapus Tagihan',
      message: `Apakah Anda yakin ingin menghapus tagihan \"${nama}\"?\n\nPerhatian: Semua data pembayaran terkait tagihan ini juga akan terhapus!`,
      confirmText: 'Hapus',
      cancelText: 'Batal'
    });
    if (!ok) return;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?= isset($data['bendahara_mode']) && $data['bendahara_mode'] ? BASEURL . '/bendahara/hapusTagihan' : BASEURL . '/waliKelas/hapusTagihan' ?>';
    const idInput = document.createElement('input');
    idInput.type = 'hidden';
    idInput.name = 'id_tagihan';
    idInput.value = id;
    form.appendChild(idInput);
    document.body.appendChild(form);
    form.submit();
  }
</script>