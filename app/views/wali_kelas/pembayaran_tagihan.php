<?php // View expects $data array, not individual variables ?>
<div class="p-4 sm:p-6">
  <!-- Header Section -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div class="flex items-center gap-3">
      <div
        class="w-12 h-12 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl flex items-center justify-center shadow-lg">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white"
          stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
          <polyline points="14 2 14 8 20 8" />
          <line x1="16" y1="13" x2="8" y2="13" />
          <line x1="16" y1="17" x2="8" y2="17" />
          <polyline points="10 9 9 9 8 9" />
        </svg>
      </div>
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Detail Tagihan</h1>
        <p class="text-sm text-gray-500"><?= htmlspecialchars($data['tagihan']['nama'] ?? 'Baru') ?></p>
      </div>
    </div>

    <div class="flex flex-wrap gap-2">
      <?php if (!empty($data['tagihan']['id'])): ?>
        <a href="<?= BASEURL ?>/waliKelas/pembayaranTagihanPDF/<?= (int) $data['tagihan']['id'] ?>"
          class="px-4 py-2.5 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white rounded-lg text-sm font-medium flex items-center gap-2 shadow-md hover:shadow-lg transition-all">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
            <polyline points="7 10 12 15 17 10" />
            <line x1="12" x2="12" y1="15" y2="3" />
          </svg>
          <span>Download PDF</span>
        </a>
      <?php endif; ?>
      <a href="<?= BASEURL ?>/waliKelas/pembayaran"
        class="px-4 py-2.5 bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white rounded-lg text-sm font-medium flex items-center gap-2 shadow-md hover:shadow-lg transition-all">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="m12 19-7-7 7-7" />
          <path d="M19 12H5" />
        </svg>
        <span>Kembali</span>
      </a>
    </div>
  </div>

  <?php if (empty($data['tagihan'])): ?>
    <!-- Form Aktifkan dari Global -->
    <div class="bg-gradient-to-br from-white to-blue-50 rounded-xl shadow-lg p-6 border border-blue-100">
      <div class="flex items-center gap-2 mb-4">
        <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white"
            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path
              d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" />
            <polyline points="3.27 6.96 12 12.01 20.73 6.96" />
            <line x1="12" x2="12" y1="22.08" y2="12" />
          </svg>
        </div>
        <h2 class="text-lg font-semibold text-gray-800">Aktifkan dari Tagihan Global</h2>
      </div>

      <form action="<?= BASEURL ?>/waliKelas/simpanTagihanKelas" method="POST" class="grid sm:grid-cols-5 gap-4">
        <input type="hidden" name="mode" value="global" />

        <div class="sm:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-1.5">ID Tagihan Global</label>
          <input name="id_global" type="number"
            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
            placeholder="Masukkan ID Global" required />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Nominal Default</label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm font-medium">Rp</span>
            <input name="nominal_default" type="hidden" />
            <input id="nominal-display" type="text"
              class="w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
              placeholder="0" />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Jatuh Tempo</label>
          <input name="jatuh_tempo" type="date"
            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
        </div>

        <div class="flex items-end">
          <button
            class="w-full px-6 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg font-medium shadow-md hover:shadow-lg transition-all">Aktifkan</button>
        </div>
      </form>
    </div>
  <?php else: ?>
    <!-- Info Tagihan -->
    <div class="grid sm:grid-cols-2 gap-4 mb-6">
      <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-5 text-white">
        <div class="flex items-center justify-between mb-2">
          <span class="text-green-100 text-sm font-medium">Nominal Default</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="1" x2="12" y2="23" />
            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
          </svg>
        </div>
        <div class="text-2xl font-bold">Rp
          <?= number_format((int) ($data['tagihan']['nominal_default'] ?? 0), 0, ',', '.') ?></div>
      </div>

      <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-5 text-white">
        <div class="flex items-center justify-between mb-2">
          <span class="text-blue-100 text-sm font-medium">Jatuh Tempo</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect width="18" height="18" x="3" y="4" rx="2" ry="2" />
            <line x1="16" x2="16" y1="2" y2="6" />
            <line x1="8" x2="8" y1="2" y2="6" />
            <line x1="3" x2="21" y1="10" y2="10" />
          </svg>
        </div>
        <div class="text-xl font-bold">
          <?= !empty($data['tagihan']['jatuh_tempo']) ? htmlspecialchars($data['tagihan']['jatuh_tempo']) : 'Tidak Ada' ?>
        </div>
      </div>
    </div>

    <!-- Daftar Siswa -->
    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
      <div class="flex items-center gap-2 mb-4">
        <div class="w-8 h-8 bg-orange-500 rounded-lg flex items-center justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white"
            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
            <circle cx="9" cy="7" r="4" />
            <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
          </svg>
        </div>
        <h2 class="text-lg font-semibold text-gray-800">Status Pembayaran Per Siswa</h2>
      </div>

      <?php
      $map = [];
      foreach (($data['tagihan_siswa'] ?? []) as $ts) {
        $map[$ts['id_siswa']] = $ts;
      }
      ?>

      <!-- Mobile: Cards -->
      <div class="space-y-3 lg:hidden">
        <?php foreach (($data['siswa_list'] ?? []) as $s):
          $ts = $map[$s['id_siswa']] ?? null;
          $nominal = $ts['nominal'] ?? ($data['tagihan']['nominal_default'] ?? 0);
          $diskon = $ts['diskon'] ?? 0;
          $terbayar = $ts['total_terbayar'] ?? 0;
          $status = $ts['status'] ?? 'belum';
          ?>
          <div
            class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition-shadow bg-gradient-to-br from-white to-gray-50">
            <div class="flex items-start justify-between mb-3">
              <div class="flex-1">
                <div class="font-semibold text-gray-800 mb-2"><?= htmlspecialchars($s['nama_siswa']) ?></div>

                <div class="grid grid-cols-2 gap-2 text-xs mb-2">
                  <div>
                    <span class="text-gray-500">Nominal:</span>
                    <div class="font-medium text-gray-700">Rp <?= number_format((int) $nominal, 0, ',', '.') ?></div>
                  </div>
                  <div>
                    <span class="text-gray-500">Diskon:</span>
                    <div class="font-medium text-gray-700">Rp <?= number_format((int) $diskon, 0, ',', '.') ?></div>
                  </div>
                  <div>
                    <span class="text-gray-500">Terbayar:</span>
                    <div class="font-medium text-green-600">Rp <?= number_format((int) $terbayar, 0, ',', '.') ?></div>
                  </div>
                  <div>
                    <span class="text-gray-500">Status:</span>
                    <?php if ($status === 'lunas'): ?>
                      <span
                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none"
                          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1">
                          <polyline points="20 6 9 17 4 12" />
                        </svg>
                        Lunas
                      </span>
                    <?php elseif ($status === 'sebagian'): ?>
                      <span
                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none"
                          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1">
                          <circle cx="12" cy="12" r="10" />
                          <line x1="12" x2="12" y1="8" y2="12" />
                          <line x1="12" x2="12.01" y1="16" y2="16" />
                        </svg>
                        Sebagian
                      </span>
                    <?php else: ?>
                      <span
                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none"
                          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1">
                          <circle cx="12" cy="12" r="10" />
                          <line x1="15" x2="9" y1="9" y2="15" />
                          <line x1="9" x2="15" y1="9" y2="15" />
                        </svg>
                        Belum
                      </span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>

            <div class="flex flex-wrap gap-2">
              <button type="button"
                class="flex-1 inline-flex items-center justify-center gap-2 px-3 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg text-sm font-medium shadow-sm transition-all"
                onclick="openInputModal(<?= (int) ($data['tagihan']['id'] ?? 0) ?>, <?= (int) $s['id_siswa'] ?>, '<?= htmlspecialchars($s['nama_siswa'], ENT_QUOTES) ?>', <?= max(0, (int) $nominal - (int) $diskon - (int) $terbayar) ?>, <?= (int) $nominal ?>, <?= (int) $terbayar ?>)">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                  <path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4Z" />
                </svg>
                Cicil
              </button>
              <?php $sisaRow = max(0, (int) $nominal - (int) $diskon - (int) $terbayar);
              if ($sisaRow > 0): ?>
                <button type="button"
                  class="flex-1 inline-flex items-center justify-center gap-2 px-3 py-2.5 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white rounded-lg text-sm font-medium shadow-sm transition-all"
                  onclick="bayarLunas(<?= (int) ($data['tagihan']['id'] ?? 0) ?>, <?= (int) $s['id_siswa'] ?>, '<?= htmlspecialchars($s['nama_siswa'], ENT_QUOTES) ?>', <?= $sisaRow ?>)">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12" />
                  </svg>
                  Bayar
                </button>
              <?php endif; ?>
              <?php if ($status === 'lunas' || $status === 'sebagian'): ?>
                <button
                  onclick="lihatRiwayat(<?= (int) ($data['tagihan']['id'] ?? 0) ?>, <?= (int) $s['id_siswa'] ?>, '<?= htmlspecialchars($s['nama_siswa'], ENT_QUOTES) ?>')"
                  class="flex-1 inline-flex items-center justify-center gap-2 px-3 py-2.5 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-lg text-sm font-medium shadow-sm transition-all">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  Riwayat
                </button>
                <a class="flex-1 inline-flex items-center justify-center gap-2 px-3 py-2.5 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white rounded-lg text-sm font-medium shadow-sm transition-all"
                  href="<?= BASEURL ?>/waliKelas/invoicePembayaran/<?= (int) ($data['tagihan']['id'] ?? 0) ?>/<?= (int) $s['id_siswa'] ?>">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                    <polyline points="14 2 14 8 20 8" />
                  </svg>
                  PDF
                </a>
                <button
                  onclick="showPrintOptions(<?= (int) ($data['tagihan']['id'] ?? 0) ?>, <?= (int) $s['id_siswa'] ?>, '<?= htmlspecialchars($s['nama_siswa'], ENT_QUOTES) ?>')"
                  class="flex-1 inline-flex items-center justify-center gap-2 px-3 py-2.5 bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white rounded-lg text-sm font-medium shadow-sm transition-all">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 6 2 18 2 18 9" />
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                    <rect x="6" y="14" width="12" height="8" />
                  </svg>
                  Cetak
                </button>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Desktop: Table -->
      <div class="overflow-x-auto hidden lg:block rounded-lg border border-gray-200">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="bg-gradient-to-r from-gray-50 to-gray-100">
              <th class="text-left p-4 font-semibold text-gray-700">Nama Siswa</th>
              <th class="text-left p-4 font-semibold text-gray-700">Nominal</th>
              <th class="text-left p-4 font-semibold text-gray-700">Diskon</th>
              <th class="text-left p-4 font-semibold text-gray-700">Terbayar</th>
              <th class="text-center p-4 font-semibold text-gray-700">Status</th>
              <th class="text-center p-4 font-semibold text-gray-700">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <?php foreach (($data['siswa_list'] ?? []) as $s):
              $ts = $map[$s['id_siswa']] ?? null;
              $nominal = $ts['nominal'] ?? ($data['tagihan']['nominal_default'] ?? 0);
              $diskon = $ts['diskon'] ?? 0;
              $terbayar = $ts['total_terbayar'] ?? 0;
              $status = $ts['status'] ?? 'belum';
              ?>
              <tr class="hover:bg-blue-50 transition-colors">
                <td class="p-4">
                  <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="text-blue-600">
                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                      </svg>
                    </div>
                    <span class="font-medium text-gray-800"><?= htmlspecialchars($s['nama_siswa']) ?></span>
                  </div>
                </td>
                <td class="p-4">
                  <span class="text-gray-700 font-medium">Rp <?= number_format((int) $nominal, 0, ',', '.') ?></span>
                </td>
                <td class="p-4">
                  <span class="text-gray-700 font-medium">Rp <?= number_format((int) $diskon, 0, ',', '.') ?></span>
                </td>
                <td class="p-4">
                  <span class="text-green-600 font-semibold">Rp <?= number_format((int) $terbayar, 0, ',', '.') ?></span>
                </td>
                <td class="p-4 text-center">
                  <?php if ($status === 'lunas'): ?>
                    <span
                      class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1">
                        <polyline points="20 6 9 17 4 12" />
                      </svg>
                      Lunas
                    </span>
                  <?php elseif ($status === 'sebagian'): ?>
                    <span
                      class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="12" x2="12" y1="8" y2="12" />
                        <line x1="12" x2="12.01" y1="16" y2="16" />
                      </svg>
                      Sebagian
                    </span>
                  <?php else: ?>
                    <span
                      class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="15" x2="9" y1="9" y2="15" />
                        <line x1="9" x2="15" y1="9" y2="15" />
                      </svg>
                      Belum
                    </span>
                  <?php endif; ?>
                </td>
                <td class="p-4">
                  <div class="flex justify-center gap-2">
                    <button type="button"
                      class="inline-flex items-center gap-1.5 px-3 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg text-sm font-medium shadow-sm hover:shadow-md transition-all"
                      onclick="openInputModal(<?= (int) ($data['tagihan']['id'] ?? 0) ?>, <?= (int) $s['id_siswa'] ?>, '<?= htmlspecialchars($s['nama_siswa'], ENT_QUOTES) ?>', <?= max(0, (int) $nominal - (int) $diskon - (int) $terbayar) ?>, <?= (int) $nominal ?>, <?= (int) $terbayar ?>)">
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                        <path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4Z" />
                      </svg>
                      Cicil
                    </button>
                    <?php $sisaRow = max(0, (int) $nominal - (int) $diskon - (int) $terbayar);
                    if ($sisaRow > 0): ?>
                      <button type="button"
                        class="inline-flex items-center gap-1.5 px-3 py-2 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white rounded-lg text-sm font-medium shadow-sm hover:shadow-md transition-all"
                        onclick="bayarLunas(<?= (int) ($data['tagihan']['id'] ?? 0) ?>, <?= (int) $s['id_siswa'] ?>, '<?= htmlspecialchars($s['nama_siswa'], ENT_QUOTES) ?>', <?= $sisaRow ?>)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <polyline points="20 6 9 17 4 12" />
                        </svg>
                        Bayar
                      </button>
                    <?php endif; ?>
                    <?php if ($status === 'lunas' || $status === 'sebagian'): ?>
                      <button
                        onclick="lihatRiwayat(<?= (int) ($data['tagihan']['id'] ?? 0) ?>, <?= (int) $s['id_siswa'] ?>, '<?= htmlspecialchars($s['nama_siswa'], ENT_QUOTES) ?>')"
                        class="inline-flex items-center gap-1.5 px-3 py-2 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-lg text-sm font-medium shadow-sm hover:shadow-md transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Riwayat
                      </button>
                      <a class="inline-flex items-center gap-1.5 px-3 py-2 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white rounded-lg text-sm font-medium shadow-sm hover:shadow-md transition-all"
                        href="<?= BASEURL ?>/waliKelas/invoicePembayaran/<?= (int) ($data['tagihan']['id'] ?? 0) ?>/<?= (int) $s['id_siswa'] ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                          <polyline points="14 2 14 8 20 8" />
                        </svg>
                        PDF
                      </a>
                      <button
                        onclick="showPrintOptions(<?= (int) ($data['tagihan']['id'] ?? 0) ?>, <?= (int) $s['id_siswa'] ?>, '<?= htmlspecialchars($s['nama_siswa'], ENT_QUOTES) ?>')"
                        class="inline-flex items-center gap-1.5 px-3 py-2 bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white rounded-lg text-sm font-medium shadow-sm hover:shadow-md transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <polyline points="6 9 6 2 18 2 18 9" />
                          <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                          <rect x="6" y="14" width="12" height="8" />
                        </svg>
                        Cetak
                      </button>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>
</div>

<!-- Modal Pilih Opsi Cetak -->
<div id="modalPrintOptions"
  class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-xl shadow-2xl max-w-md w-full overflow-hidden">
    <div class="bg-gradient-to-r from-purple-600 to-purple-700 p-5 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white"
            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="6 9 6 2 18 2 18 9" />
            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
            <rect x="6" y="14" width="12" height="8" />
          </svg>
        </div>
        <div>
          <h3 class="text-lg font-bold text-white">Pilih Metode Cetak</h3>
          <p id="printNamaSiswa" class="text-sm text-purple-100"></p>
        </div>
      </div>
      <button type="button" onclick="closePrintOptions()"
        class="text-white hover:bg-white/20 rounded-lg p-2 transition-all">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" x2="6" y1="6" y2="18" />
          <line x1="6" x2="18" y1="6" y2="18" />
        </svg>
      </button>
    </div>
    <div class="p-5 space-y-3">
      <p class="text-sm text-gray-600 mb-4">Pilih metode cetak thermal printer:</p>

      <!-- Bluetooth Option -->
      <button onclick="printViaBluetooth()"
        class="w-full flex items-center gap-4 p-4 bg-gradient-to-r from-blue-50 to-blue-100 hover:from-blue-100 hover:to-blue-200 border border-blue-200 rounded-xl transition-all group">
        <div
          class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-md group-hover:shadow-lg transition-shadow">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white"
            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="m7 7 10 10-5 5V2l5 5L7 17" />
          </svg>
        </div>
        <div class="text-left flex-1">
          <div class="font-semibold text-blue-800">Bluetooth Printer</div>
          <div class="text-xs text-blue-600">Untuk printer thermal wireless (Android/Desktop)</div>
        </div>
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-400">
          <path d="m9 18 6-6-6-6" />
        </svg>
      </button>

      <!-- USB Option -->
      <button onclick="printViaUSB()"
        class="w-full flex items-center gap-4 p-4 bg-gradient-to-r from-green-50 to-green-100 hover:from-green-100 hover:to-green-200 border border-green-200 rounded-xl transition-all group">
        <div
          class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center shadow-md group-hover:shadow-lg transition-shadow">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white"
            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path
              d="M9.5 2A2.5 2.5 0 0 1 12 4.5v15a2.5 2.5 0 0 1-4.96.44 2.5 2.5 0 0 1-2.96-3.08 3 3 0 0 1-.34-5.58 2.5 2.5 0 0 1 1.32-4.24 2.5 2.5 0 0 1 4.44-1.04z" />
            <path
              d="M14.5 2A2.5 2.5 0 0 0 12 4.5v15a2.5 2.5 0 0 0 4.96.44 2.5 2.5 0 0 0 2.96-3.08 3 3 0 0 0 .34-5.58 2.5 2.5 0 0 0-1.32-4.24 2.5 2.5 0 0 0-4.44-1.04z" />
          </svg>
        </div>
        <div class="text-left flex-1">
          <div class="font-semibold text-green-800">USB Printer</div>
          <div class="text-xs text-green-600">Untuk printer thermal kabel USB (Desktop)</div>
        </div>
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-400">
          <path d="m9 18 6-6-6-6" />
        </svg>
      </button>

      <!-- Browser Print Option -->
      <button onclick="printViaBrowser()"
        class="w-full flex items-center gap-4 p-4 bg-gradient-to-r from-orange-50 to-orange-100 hover:from-orange-100 hover:to-orange-200 border border-orange-200 rounded-xl transition-all group">
        <div
          class="w-12 h-12 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl flex items-center justify-center shadow-md group-hover:shadow-lg transition-shadow">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white"
            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="6 9 6 2 18 2 18 9" />
            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
            <rect x="6" y="14" width="12" height="8" />
          </svg>
        </div>
        <div class="text-left flex-1">
          <div class="font-semibold text-orange-800">Browser Print (58mm/80mm)</div>
          <div class="text-xs text-orange-600">Format thermal via dialog print browser</div>
        </div>
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-orange-400">
          <path d="m9 18 6-6-6-6" />
        </svg>
      </button>
    </div>
    <div class="bg-gray-50 px-5 py-4 flex justify-end">
      <button onclick="closePrintOptions()"
        class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-medium transition-all">Batal</button>
    </div>
  </div>
</div>

<!-- Hidden iframe for browser print -->
<iframe id="printFrame" style="display:none;"></iframe>

<!-- Modal Cicil Pembayaran -->
<div id="modalInput" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full overflow-hidden">
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 p-5 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white"
            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
            <path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4Z" />
          </svg>
        </div>
        <div>
          <h3 class="text-lg font-bold text-white">Cicil Pembayaran</h3>
          <p id="inputNamaSiswa" class="text-sm text-blue-100"></p>
        </div>
      </div>
      <button type="button" onclick="tutupInputModal()"
        class="text-white hover:bg-white/20 rounded-lg p-2 transition-all">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" x2="6" y1="6" y2="18" />
          <line x1="6" x2="18" y1="6" y2="18" />
        </svg>
      </button>
    </div>
    <form id="formInputPembayaran" action="<?= BASEURL ?>/waliKelas/prosesPembayaran" method="POST"
      class="p-5 space-y-4">
      <input type="hidden" name="tagihan_id" value="<?= (int) ($data['tagihan']['id'] ?? 0) ?>" />
      <input type="hidden" name="id_siswa" />
      <div class="grid grid-cols-2 gap-3 text-xs">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
          <div class="text-blue-600 font-medium">Total</div>
          <div id="infoTotal" class="text-lg font-bold text-blue-700">Rp 0</div>
        </div>
        <div class="bg-green-50 border border-green-200 rounded-lg p-3">
          <div class="text-green-600 font-medium">Terbayar</div>
          <div id="infoTerbayar" class="text-lg font-bold text-green-700">Rp 0</div>
        </div>
        <div class="col-span-2 bg-yellow-50 border border-yellow-200 rounded-lg p-3">
          <div class="text-yellow-700 font-medium">Sisa</div>
          <div id="infoSisa" class="text-lg font-bold text-yellow-800">Rp 0</div>
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Jumlah</label>
        <div class="relative">
          <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm font-medium">Rp</span>
          <input name="jumlah" type="hidden" required />
          <input id="jumlahDisplay" type="text" inputmode="numeric"
            class="w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
            placeholder="0" required />
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Metode</label>
        <select name="metode"
          class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
          <option value="Tunai">Tunai</option>
          <option value="Transfer">Transfer</option>
          <option value="Lainnya">Lainnya</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Keterangan</label>
        <input name="keterangan" type="text"
          class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          placeholder="Opsional" />
      </div>
      <div class="flex justify-end gap-2 pt-2">
        <button type="button" onclick="tutupInputModal()"
          class="px-4 py-2.5 bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white rounded-lg font-medium shadow-sm hover:shadow-md transition-all">Batal</button>
        <button type="submit"
          class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg font-medium shadow-sm hover:shadow-md transition-all flex items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
            <polyline points="17 21 17 13 7 13 7 21" />
            <polyline points="7 3 7 8 15 8" />
          </svg>
          Simpan
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Riwayat Transaksi -->
<div id="modalRiwayat" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-hidden">
    <div class="bg-gradient-to-r from-orange-500 to-orange-600 p-6">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
              stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div>
            <h3 class="text-xl font-bold text-white">Riwayat Transaksi</h3>
            <p id="modalNamaSiswa" class="text-sm text-orange-100"></p>
          </div>
        </div>
        <button onclick="tutupModal()"
          class="text-white hover:bg-white hover:bg-opacity-20 rounded-lg p-2 transition-all">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" x2="6" y1="6" y2="18" />
            <line x1="6" x2="18" y1="6" y2="18" />
          </svg>
        </button>
      </div>
    </div>

    <div class="p-6 overflow-y-auto" style="max-height: calc(90vh - 180px);">
      <div id="loadingRiwayat" class="text-center py-8">
        <svg class="animate-spin h-8 w-8 mx-auto text-orange-500" xmlns="http://www.w3.org/2000/svg" fill="none"
          viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor"
            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
          </path>
        </svg>
        <p class="text-gray-500 mt-2">Memuat data...</p>
      </div>
      <div id="contentRiwayat" class="hidden">
        <!-- Summary Cards -->
        <div class="grid grid-cols-2 gap-4 mb-6">
          <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200">
            <div class="text-xs text-blue-600 font-medium mb-1">Total Tagihan</div>
            <div id="totalTagihan" class="text-2xl font-bold text-blue-700"></div>
          </div>
          <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border border-green-200">
            <div class="text-xs text-green-600 font-medium mb-1">Total Dibayar</div>
            <div id="totalDibayar" class="text-2xl font-bold text-green-700"></div>
          </div>
        </div>

        <!-- Transactions List -->
        <div id="listTransaksi" class="space-y-3"></div>

        <div id="emptyRiwayat" class="hidden text-center py-8">
          <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <p class="text-gray-500 mt-2">Belum ada transaksi</p>
        </div>
      </div>
    </div>

    <div class="bg-gray-50 px-6 py-4 flex justify-end gap-2">
      <button onclick="tutupModal()"
        class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-medium transition-all">
        Tutup
      </button>
    </div>
  </div>
</div>

<script>
  const BASE_URL = '<?= BASEURL ?>';

  // State for print options
  let currentPrintTagihanId = null;
  let currentPrintSiswaId = null;
  let currentPrintNamaSiswa = '';
  let currentPrintData = null;

  // Show print options modal
  function showPrintOptions(tagihanId, siswaId, namaSiswa) {
    currentPrintTagihanId = tagihanId;
    currentPrintSiswaId = siswaId;
    currentPrintNamaSiswa = namaSiswa;

    document.getElementById('printNamaSiswa').textContent = namaSiswa;
    document.getElementById('modalPrintOptions').classList.remove('hidden');
  }

  function closePrintOptions() {
    document.getElementById('modalPrintOptions').classList.add('hidden');
    resetPrintState();
  }

  function resetPrintState() {
    currentPrintTagihanId = null;
    currentPrintSiswaId = null;
    currentPrintNamaSiswa = '';
    currentPrintData = null;
  }

  // Fetch thermal data
  async function fetchThermalData() {
    if (!currentPrintTagihanId || !currentPrintSiswaId) {
      alert('Data tidak lengkap. Silakan coba lagi.');
      return null;
    }

    try {
      const response = await fetch(`${BASE_URL}/waliKelas/invoiceThermalData/${currentPrintTagihanId}/${currentPrintSiswaId}`);
      const result = await response.json();

      if (!result.success) {
        throw new Error(result.error || 'Failed to fetch data');
      }

      currentPrintData = result.data;
      return result.data;
    } catch (error) {
      console.error('Fetch error:', error);
      alert('Gagal mengambil data: ' + error.message);
      return null;
    }
  }

  // Print via Bluetooth
  async function printViaBluetooth() {
    // Close modal first but keep the data (don't use closePrintOptions which resets state)
    document.getElementById('modalPrintOptions').classList.add('hidden');

    // Copy values before any async operations
    const tagihanId = currentPrintTagihanId;
    const siswaId = currentPrintSiswaId;

    // Detect iOS
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    if (isIOS) {
      alert('Cetak thermal Bluetooth tidak didukung di iPhone/iPad.\n\nGunakan opsi "Browser Print" atau perangkat Android/Desktop.');
      resetPrintState();
      return;
    }

    // Check if Web Bluetooth is available
    if (!navigator.bluetooth) {
      alert('Web Bluetooth tidak didukung di browser ini.\nGunakan Chrome/Edge di Android atau desktop.\n\nAtau coba opsi "USB Printer" atau "Browser Print".');
      resetPrintState();
      return;
    }

    try {
      // Fetch data using copied values
      const response = await fetch(`${BASE_URL}/waliKelas/invoiceThermalData/${tagihanId}/${siswaId}`);
      const result = await response.json();

      if (!result.success) {
        throw new Error(result.error || 'Failed to fetch data');
      }

      const data = result.data;

      // Request Bluetooth device
      const device = await navigator.bluetooth.requestDevice({
        filters: [{ services: ['000018f0-0000-1000-8000-00805f9b34fb'] }],
        optionalServices: ['000018f0-0000-1000-8000-00805f9b34fb']
      });

      const server = await device.gatt.connect();
      const service = await server.getPrimaryService('000018f0-0000-1000-8000-00805f9b34fb');
      const characteristic = await service.getCharacteristic('00002af1-0000-1000-8000-00805f9b34fb');

      await sendToThermalPrinter(characteristic, data);

      await device.gatt.disconnect();
      alert('Berhasil mencetak via Bluetooth!');

    } catch (error) {
      console.error('Bluetooth print error:', error);
      if (error.name !== 'NotFoundError') { // User cancelled
        alert('Gagal mencetak via Bluetooth: ' + error.message);
      }
    }

    resetPrintState();
  }

  // Print via USB
  async function printViaUSB() {
    // Close modal first but keep the data
    document.getElementById('modalPrintOptions').classList.add('hidden');

    // Copy values before any async operations
    const tagihanId = currentPrintTagihanId;
    const siswaId = currentPrintSiswaId;

    // Check if WebUSB is available
    if (!navigator.usb) {
      alert('WebUSB tidak didukung di browser ini.\nGunakan Chrome/Edge di desktop.\n\nAtau coba opsi "Browser Print" untuk cetak via dialog printer.');
      resetPrintState();
      return;
    }

    try {
      // Fetch data using copied values
      const response = await fetch(`${BASE_URL}/waliKelas/invoiceThermalData/${tagihanId}/${siswaId}`);
      const result = await response.json();

      if (!result.success) {
        throw new Error(result.error || 'Failed to fetch data');
      }

      const data = result.data;

      // Request USB device - common thermal printer vendor IDs
      const device = await navigator.usb.requestDevice({
        filters: [
          { vendorId: 0x0483 }, // STMicroelectronics (common for thermal printers)
          { vendorId: 0x0416 }, // Winbond (Epson compatible)
          { vendorId: 0x04B8 }, // Epson
          { vendorId: 0x0519 }, // Star Micronics
          { vendorId: 0x0DD4 }, // Custom (Italian)
          { vendorId: 0x154F }, // SNBC
          { vendorId: 0x0FE6 }, // ICS Advent
          { vendorId: 0x1504 }, // pos-x
          { vendorId: 0x0525 }, // Netchip (USB Gadget)
          { vendorId: 0x28E9 }, // GD32 (common for Chinese thermal printers)
          { vendorId: 0x1A86 }, // QinHeng (CH340 - common USB chip)
          { vendorId: 0x067B }, // Prolific (PL2303 - USB to serial)
        ]
      });

      await device.open();

      // Find the bulk out endpoint
      let interfaceNumber = 0;
      let endpointNumber = 1;

      for (const config of device.configurations) {
        for (const iface of config.interfaces) {
          for (const alternate of iface.alternates) {
            for (const endpoint of alternate.endpoints) {
              if (endpoint.direction === 'out' && endpoint.type === 'bulk') {
                interfaceNumber = iface.interfaceNumber;
                endpointNumber = endpoint.endpointNumber;
                break;
              }
            }
          }
        }
      }

      await device.selectConfiguration(1);
      await device.claimInterface(interfaceNumber);

      // Build ESC/POS commands
      const commands = buildESCPOSCommands(data);

      // Send data
      await device.transferOut(endpointNumber, commands);

      await device.releaseInterface(interfaceNumber);
      await device.close();

      alert('Berhasil mencetak via USB!');

    } catch (error) {
      console.error('USB print error:', error);
      if (error.name !== 'NotFoundError') { // User cancelled
        alert('Gagal mencetak via USB: ' + error.message + '\n\nPastikan:\n1. Printer USB terhubung\n2. Driver printer terinstall\n3. Printer dalam keadaan ready');
      }
    }

    resetPrintState();
  }

  // Print via Browser (thermal format)
  async function printViaBrowser() {
    // Close modal first but keep the data
    document.getElementById('modalPrintOptions').classList.add('hidden');

    // Copy values before any async operations
    const tagihanId = currentPrintTagihanId;
    const siswaId = currentPrintSiswaId;

    try {
      // Fetch data using copied values
      const response = await fetch(`${BASE_URL}/waliKelas/invoiceThermalData/${tagihanId}/${siswaId}`);
      const result = await response.json();

      if (!result.success) {
        throw new Error(result.error || 'Failed to fetch data');
      }

      const data = result.data;

      // Build HTML for thermal print (58mm width = ~48 chars, 80mm = ~64 chars)
      const html = buildThermalHTML(data);

      // Open new window for printing
      const printWindow = window.open('', '_blank', 'width=400,height=600');
      if (!printWindow) {
        alert('Popup diblokir browser. Izinkan popup untuk halaman ini.');
        resetPrintState();
        return;
      }

      printWindow.document.write(html);
      printWindow.document.close();

      // Wait for content to load then print
      printWindow.onload = function () {
        setTimeout(() => {
          printWindow.print();
        }, 250);
      };

    } catch (error) {
      console.error('Browser print error:', error);
      alert('Gagal mencetak: ' + error.message);
    }

    resetPrintState();
  }

  // Build thermal HTML for browser print
  function buildThermalHTML(data) {
    const transaksiHTML = data.transaksi.length > 0
      ? data.transaksi.map(trx => `
      <div style="border-bottom: 1px dashed #000; padding: 1px 0; font-size: 10px;">
        <div>${trx.tanggal}</div>
        <div>${trx.keterangan || '-'}</div>
        <div style="text-align: right;">${trx.metode}: Rp ${formatRupiah(trx.jumlah)}</div>
      </div>
    `).join('')
      : '<div style="text-align: center; padding: 2px 0;">Tidak ada transaksi</div>';

    return `<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Invoice Thermal</title>
  <style>
    @page {
      size: 58mm auto;
      margin: 0;
    }
    @media print {
      html, body {
        width: 58mm;
        margin: 0;
        padding: 0;
      }
    }
    body {
      font-family: 'Courier New', monospace;
      font-size: 11px;
      font-weight: bold;
      width: 58mm;
      margin: 0 auto;
      padding: 0 2mm;
      line-height: 1.1;
    }
    .center { text-align: center; }
    .right { text-align: right; }
    .bold { font-weight: bold; }
    .big { font-size: 14px; font-weight: bold; }
    .divider { border-top: 1px dashed #000; margin: 2px 0; }
    .double-divider { border-top: 2px solid #000; margin: 2px 0; }
    .row { display: flex; justify-content: space-between; margin: 0; padding: 0; }
    .qr-container { text-align: center; margin: 3px 0; }
    .qr-container img { width: 80px; height: 80px; }
  </style>
</head>
<body><div class="center big">${data.sekolah}</div>
<div class="center bold">${data.judul}</div>
<div class="divider"></div>
<div class="row"><span>Tanggal</span><span>${data.tanggal}</span></div>
<div class="row"><span>Semester</span><span>${data.semester}</span></div>
<div class="divider"></div>
<div><strong>Siswa:</strong></div>
<div class="row"><span>Nama</span><span>${data.siswa.nama}</span></div>
<div class="row"><span>NISN</span><span>${data.siswa.nisn}</span></div>
<div class="row"><span>Kelas</span><span>${data.siswa.kelas}</span></div>
<div class="divider"></div>
<div><strong>Tagihan:</strong> ${data.tagihan.nama}</div>
<div class="divider"></div>
<div><strong>Transaksi:</strong></div>
${transaksiHTML}
<div class="double-divider"></div>
<div class="row"><span>Nominal</span><span>Rp ${formatRupiah(data.tagihan.nominal)}</span></div>
<div class="row"><span>Diskon</span><span>Rp ${formatRupiah(data.tagihan.diskon)}</span></div>
<div class="row"><span>Total</span><span>Rp ${formatRupiah(data.tagihan.total)}</span></div>
<div class="divider"></div>
<div class="row bold"><span>Dibayar</span><span>Rp ${formatRupiah(data.tagihan.terbayar)}</span></div>
<div class="row bold"><span>Sisa</span><span>Rp ${formatRupiah(data.tagihan.sisa)}</span></div>
<div class="double-divider"></div>
<div class="center big">*** ${data.tagihan.status} ***</div>
${data.qr ? `
<div class="qr-container">
<div style="font-size: 9px;">Scan untuk validasi:</div>
<img src="${data.qr.url}" alt="QR Code">
</div>
` : ''}
<div class="center" style="font-size: 9px;">
Terima kasih<br>
Invoice sah tanpa tanda tangan
</div>
<br><br><br><br><br>
</body>
</html>`;
  }

  // Build ESC/POS commands for USB/Bluetooth
  function buildESCPOSCommands(data) {
    const encoder = new TextEncoder();
    const commands = [];

    // Helper to add text
    const addText = (text) => commands.push(...encoder.encode(text));
    const addBytes = (bytes) => commands.push(...bytes);

    // Initialize printer
    addBytes([0x1B, 0x40]); // ESC @ - Initialize

    // Set emphasized/bold mode ON
    addBytes([0x1B, 0x45, 0x01]); // ESC E 1 - Bold ON

    // Center align
    addBytes([0x1B, 0x61, 0x01]);

    // Double size for header
    addBytes([0x1D, 0x21, 0x11]);
    addText(data.sekolah + '\n');
    addBytes([0x1D, 0x21, 0x00]); // Normal size
    addText(data.judul + '\n');
    addText('--------------------------------\n');

    // Left align
    addBytes([0x1B, 0x61, 0x00]);
    addText(`Tanggal  : ${data.tanggal}\n`);
    addText(`Semester : ${data.semester}\n`);
    addText('--------------------------------\n');
    addText(`Nama  : ${data.siswa.nama}\n`);
    addText(`NISN  : ${data.siswa.nisn}\n`);
    addText(`Kelas : ${data.siswa.kelas}\n`);
    addText('--------------------------------\n');
    addText(`Tagihan: ${data.tagihan.nama}\n`);
    addText('--------------------------------\n');

    // Transaksi
    if (data.transaksi.length > 0) {
      for (const trx of data.transaksi) {
        addText(`${trx.tanggal}\n`);
        addText(`${trx.keterangan || '-'}\n`);
        addText(`${trx.metode}: Rp ${formatRupiah(trx.jumlah)}\n`);
        addText('- - - - - - - - - - - - - - - -\n');
      }
    }

    // Summary
    addText('================================\n');
    addText(`Nominal  : Rp ${formatRupiah(data.tagihan.nominal)}\n`);
    addText(`Diskon   : Rp ${formatRupiah(data.tagihan.diskon)}\n`);
    addText(`Total    : Rp ${formatRupiah(data.tagihan.total)}\n`);
    addText('--------------------------------\n');

    // Double size for totals
    addBytes([0x1D, 0x21, 0x11]);
    addText(`Dibayar: Rp ${formatRupiah(data.tagihan.terbayar)}\n`);
    addText(`Sisa   : Rp ${formatRupiah(data.tagihan.sisa)}\n`);
    addBytes([0x1D, 0x21, 0x00]);
    addText('================================\n');

    // Status - center
    addBytes([0x1B, 0x61, 0x01]);
    addBytes([0x1D, 0x21, 0x11]);
    addText(`*** ${data.tagihan.status} ***\n`);
    addBytes([0x1D, 0x21, 0x00]);

    // QR Code if available
    if (data.qr && data.qr.data) {
      addText('Scan untuk validasi:\n');

      const qrData = data.qr.data;
      const qrLength = qrData.length;
      const pL = qrLength % 256;
      const pH = Math.floor(qrLength / 256);

      // QR Code model
      addBytes([0x1D, 0x28, 0x6B, 0x04, 0x00, 0x31, 0x41, 0x32, 0x00]);
      // QR Code size
      addBytes([0x1D, 0x28, 0x6B, 0x03, 0x00, 0x31, 0x43, 0x06]);
      // Error correction
      addBytes([0x1D, 0x28, 0x6B, 0x03, 0x00, 0x31, 0x45, 0x30]);
      // Store data
      addBytes([0x1D, 0x28, 0x6B, pL + 3, pH, 0x31, 0x50, 0x30]);
      for (let i = 0; i < qrLength; i++) {
        commands.push(qrData.charCodeAt(i));
      }
      // Print QR
      addBytes([0x1D, 0x28, 0x6B, 0x03, 0x00, 0x31, 0x51, 0x30]);
    }

    // Footer
    addText('Terima kasih\n');
    addText('Invoice sah tanpa tanda tangan\n');
    addText('\n\n\n\n\n\n'); // Extra line feeds untuk keluarkan kertas

    // Cut paper
    addBytes([0x1D, 0x56, 0x00]);

    // Bold OFF (cleanup)
    addBytes([0x1B, 0x45, 0x00]);

    return new Uint8Array(commands);
  }

  // Send data to thermal printer via Bluetooth characteristic
  async function sendToThermalPrinter(characteristic, data) {
    const commands = buildESCPOSCommands(data);

    // Send in chunks (BLE has MTU limit ~512 bytes typically)
    const chunkSize = 200;
    for (let i = 0; i < commands.length; i += chunkSize) {
      const chunk = commands.slice(i, i + chunkSize);
      await characteristic.writeValue(chunk);
      // Small delay between chunks
      await new Promise(resolve => setTimeout(resolve, 50));
    }
  }

  // Reusable confirm modal
  function showConfirm({ title = 'Konfirmasi', message = '', confirmText = 'Ya', cancelText = 'Batal' } = {}) {
    return new Promise((resolve) => {
      const overlay = document.createElement('div');
      overlay.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4';
      overlay.innerHTML = `
      <div class="bg-white rounded-xl shadow-2xl max-w-md w-full overflow-hidden">
        <div class="bg-gradient-to-r from-orange-500 to-orange-600 p-4">
          <h3 class="text-white font-bold">${title}</h3>
        </div>
        <div class="p-5">
          <p class="text-gray-700 whitespace-pre-line">${message}</p>
        </div>
        <div class="bg-gray-50 p-4 flex justify-end gap-2">
          <button data-cancel class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-medium transition-all">${cancelText}</button>
          <button data-ok class="px-4 py-2 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white rounded-lg font-medium transition-all">${confirmText}</button>
        </div>
      </div>
    `;
      function cleanup() {
        document.removeEventListener('keydown', onKey);
        overlay.removeEventListener('click', onOverlayClick);
        overlay.remove();
      }
      function onKey(e) {
        if (e.key === 'Escape') { cleanup(); resolve(false); }
        if (e.key === 'Enter') { cleanup(); resolve(true); }
      }
      function onOverlayClick(e) {
        if (e.target === overlay) { cleanup(); resolve(false); }
      }
      overlay.querySelector('[data-cancel]').addEventListener('click', () => { cleanup(); resolve(false); });
      overlay.querySelector('[data-ok]').addEventListener('click', () => { cleanup(); resolve(true); });
      document.addEventListener('keydown', onKey);
      overlay.addEventListener('click', onOverlayClick);
      document.body.appendChild(overlay);
    });
  }

  function formatRupiah(angka) {
    return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
  }

  // Fungsi Lihat Riwayat Transaksi
  async function lihatRiwayat(tagihanId, siswaId, namaSiswa) {
    const modal = document.getElementById('modalRiwayat');
    const loading = document.getElementById('loadingRiwayat');
    const content = document.getElementById('contentRiwayat');
    const namaSiswaEl = document.getElementById('modalNamaSiswa');

    // Show modal
    modal.classList.remove('hidden');
    loading.classList.remove('hidden');
    content.classList.add('hidden');
    namaSiswaEl.textContent = namaSiswa;

    try {
      // Fetch transactions
      const response = await fetch(`${BASE_URL}/waliKelas/getRiwayatTransaksi/${tagihanId}/${siswaId}`);
      const result = await response.json();

      if (!result.success) {
        throw new Error(result.error || 'Gagal memuat data');
      }

      const data = result.data;

      // Update summary
      document.getElementById('totalTagihan').textContent = 'Rp ' + formatRupiah(data.summary.total_tagihan);
      document.getElementById('totalDibayar').textContent = 'Rp ' + formatRupiah(data.summary.total_dibayar);

      // Render transactions
      const listTransaksi = document.getElementById('listTransaksi');
      const emptyRiwayat = document.getElementById('emptyRiwayat');

      if (data.transaksi.length === 0) {
        listTransaksi.innerHTML = '';
        emptyRiwayat.classList.remove('hidden');
      } else {
        emptyRiwayat.classList.add('hidden');
        listTransaksi.innerHTML = data.transaksi.map((trx, index) => `
        <div class="bg-gradient-to-br from-white to-gray-50 border border-gray-200 rounded-xl p-4 hover:shadow-md transition-shadow">
          <div class="flex items-start justify-between mb-3">
            <div class="flex items-start gap-3 flex-1">
              <div class="bg-gradient-to-br from-blue-500 to-blue-600 p-2.5 rounded-lg shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                  <span class="text-xs font-medium text-gray-500">#${index + 1}</span>
                  <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 font-medium">${trx.metode || '-'}</span>
                </div>
                <div class="text-xl font-bold text-blue-600 mb-1">Rp ${formatRupiah(trx.jumlah)}</div>
                <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
                  <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                  ${trx.tanggal}
                </div>
                <div class="flex items-center gap-2 text-xs text-amber-600 mb-2">
                  <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                   By: ${trx.petugas_input || 'Sistem'}
                </div>
                ${trx.keterangan ? `<div class="text-sm text-gray-600 bg-gray-100 rounded px-2 py-1">${trx.keterangan}</div>` : ''}
              </div>
            </div>
            <button onclick="hapusTransaksi(${trx.id}, ${tagihanId}, ${siswaId}, '${namaSiswa}')" class="ml-2 p-2 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-lg shadow-sm hover:shadow-md transition-all" title="Hapus transaksi">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
            </button>
          </div>
        </div>
      `).join('');
      }

      loading.classList.add('hidden');
      content.classList.remove('hidden');

    } catch (error) {
      console.error('Error loading riwayat:', error);
      alert('Gagal memuat riwayat transaksi: ' + error.message);
      tutupModal();
    }
  }

  function tutupModal() {
    document.getElementById('modalRiwayat').classList.add('hidden');
  }

  // Close modal on background click
  document.getElementById('modalRiwayat')?.addEventListener('click', function (e) {
    if (e.target === this) {
      tutupModal();
    }
  });

  // Fungsi Hapus Transaksi
  async function hapusTransaksi(transaksiId, tagihanId, siswaId, namaSiswa) {
    const ok = await showConfirm({
      title: 'Hapus Transaksi',
      message: 'Apakah Anda yakin ingin menghapus transaksi ini?\n\nPerhatian: Saldo terbayar siswa akan berkurang!',
      confirmText: 'Hapus',
      cancelText: 'Batal'
    });
    if (!ok) return;
    try {
      const formData = new FormData();
      formData.append('id_transaksi', transaksiId);

      const response = await fetch(`${BASE_URL}/waliKelas/hapusTransaksi`, {
        method: 'POST',
        body: formData
      });

      const result = await response.json();

      if (result.success) {
        alert('Transaksi berhasil dihapus!');
        await lihatRiwayat(tagihanId, siswaId, namaSiswa);
        setTimeout(() => location.reload(), 500);
      } else {
        alert('Gagal menghapus transaksi: ' + (result.error || 'Unknown error'));
      }
    } catch (error) {
      console.error('Error hapus transaksi:', error);
      alert('Terjadi kesalahan saat menghapus transaksi');
    }
  }

  // Format input nominal default untuk form global
  const nominalDisplay = document.getElementById('nominal-display');
  const nominalHidden = document.querySelector('input[name="nominal_default"]');

  if (nominalDisplay && nominalHidden) {
    function cleanNumber(str) {
      return str.replace(/\./g, '');
    }

    nominalDisplay.addEventListener('input', function (e) {
      let value = cleanNumber(e.target.value);

      // Hanya izinkan angka
      value = value.replace(/\D/g, '');

      // Update hidden input dengan nilai asli
      nominalHidden.value = value;

      // Format tampilan
      if (value) {
        e.target.value = formatRupiah(value);
      } else {
        e.target.value = '';
      }
    });
  }

  // ===== Modal Input Pembayaran Logic =====
  const modalInput = document.getElementById('modalInput');
  const formInput = document.getElementById('formInputPembayaran');
  const namaSiswaEl = document.getElementById('inputNamaSiswa');
  const jumlahDisplay = document.getElementById('jumlahDisplay');
  const jumlahHidden = formInput ? formInput.querySelector('input[name="jumlah"]') : null;
  const idSiswaHidden = formInput ? formInput.querySelector('input[name="id_siswa"]') : null;

  const infoTotal = document.getElementById('infoTotal');
  const infoTerbayar = document.getElementById('infoTerbayar');
  const infoSisa = document.getElementById('infoSisa');

  if (jumlahDisplay && jumlahHidden) {
    const cleanNum = (s) => (s || '').toString().replace(/\./g, '');
    jumlahDisplay.addEventListener('input', (e) => {
      let value = cleanNum(e.target.value).replace(/\D/g, '');
      jumlahHidden.value = value;
      e.target.value = value ? formatRupiah(value) : '';
    });
  }

  function openInputModal(tagihanId, siswaId, namaSiswa, sisa, total, terbayar) {
    if (!formInput) return;
    namaSiswaEl.textContent = namaSiswa;
    idSiswaHidden.value = siswaId;
    infoTotal.textContent = 'Rp ' + formatRupiah(total);
    infoTerbayar.textContent = 'Rp ' + formatRupiah(terbayar);
    infoSisa.textContent = 'Rp ' + formatRupiah(sisa);
    jumlahHidden.value = sisa > 0 ? sisa : '';
    jumlahDisplay.value = sisa > 0 ? formatRupiah(sisa) : '';
    modalInput.classList.remove('hidden');
  }

  function tutupInputModal() {
    if (!formInput) return;
    formInput.reset();
    if (jumlahHidden) jumlahHidden.value = '';
    if (jumlahDisplay) jumlahDisplay.value = '';
    modalInput.classList.add('hidden');
  }

  // Close input modal on ESC
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modalInput && !modalInput.classList.contains('hidden')) {
      tutupInputModal();
    }
  });

  // Close input modal on background click (only if clicking outside content)
  modalInput?.addEventListener('click', function (e) {
    if (e.target === this) {
      tutupInputModal();
    }
  });

  // Bayar Lunas (AJAX)
  async function bayarLunas(tagihanId, siswaId, namaSiswa, sisa) {
    if (sisa <= 0) return;
    const ok = await showConfirm({
      title: 'Bayar Lunas',
      message: `Bayar lunas tagihan untuk "${namaSiswa}" sebesar Rp ${formatRupiah(sisa)}?`,
      confirmText: 'Bayar',
      cancelText: 'Batal'
    });
    if (!ok) return;

    try {
      const formData = new FormData();
      formData.append('tagihan_id', tagihanId);
      formData.append('id_siswa', siswaId);

      const btn = event?.target?.closest('button');
      let originalHtml;
      if (btn) {
        btn.disabled = true; originalHtml = btn.innerHTML;
        btn.innerHTML = '<svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
      }

      const res = await fetch(`${BASE_URL}/waliKelas/bayarLunas`, { method: 'POST', body: formData });
      const result = await res.json();
      if (result.success) {
        alert('Tagihan berhasil dilunasi.');
        location.reload();
      } else {
        alert('Gagal melunasi: ' + (result.error || 'Unknown error'));
      }
      if (btn) { btn.disabled = false; btn.innerHTML = originalHtml; }
    } catch (err) {
      console.error('bayarLunas error:', err);
      alert('Terjadi kesalahan saat melunasi.');
      const btn = event?.target?.closest('button');
      if (btn) { btn.disabled = false; }
    }
  }
</script>