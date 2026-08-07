<?php // View expects $data array, not individual variables ?>
<div class="p-4 sm:p-6">
  <!-- Header with Icon -->
  <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
    <div class="flex items-center justify-between flex-wrap gap-4">
      <div class="flex items-center gap-4">
        <div class="bg-gradient-to-br from-gray-600 to-gray-700 p-4 rounded-xl shadow-lg">
          <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <div>
          <h1 class="text-2xl font-bold text-gray-800">
            <?= isset($data['bendahara_mode']) && $data['bendahara_mode'] ? 'Bendahara - ' : '' ?>Riwayat Pembayaran
          </h1>
          <p class="text-sm text-gray-600"><?= htmlspecialchars($data['wali_kelas_info']['nama_kelas'] ?? '') ?></p>
        </div>
      </div>
      <?php
      $isBendahara = isset($data['bendahara_mode']) && $data['bendahara_mode'];
      $urlPrefix = $isBendahara ? BASEURL . '/bendahara' : BASEURL . '/waliKelas';
      $idKelas = $data['wali_kelas_info']['id_kelas'] ?? '';
      ?>
      <div class="flex gap-2">
        <a href="<?= $isBendahara ? $urlPrefix . '/kelolaPembayaran/' . $idKelas : $urlPrefix . '/pembayaran' ?>"
          class="flex items-center gap-2 px-4 py-2 bg-gradient-to-br from-gray-500 to-gray-600 text-white rounded-lg hover:shadow-lg transition-shadow text-sm font-semibold">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Kembali
        </a>
        <a href="<?= $urlPrefix ?>/pembayaranExport<?= $isBendahara ? '/' . $idKelas : '' ?>"
          class="flex items-center gap-2 px-4 py-2 bg-gradient-to-br from-green-500 to-green-600 text-white rounded-lg hover:shadow-lg transition-shadow text-sm font-semibold">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          Download PDF
        </a>
      </div>
    </div>
  </div>

  <!-- Content -->
  <div class="bg-white rounded-xl shadow-lg p-6">
    <?php if (empty($data['riwayat'])): ?>
      <div class="text-center py-8">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <p class="text-gray-500">Belum ada transaksi pembayaran.</p>
      </div>
    <?php else: ?>
      <!-- Mobile: Cards -->
      <div class="space-y-4 lg:hidden">
        <?php foreach ($data['riwayat'] as $r): ?>
          <div
            class="bg-gradient-to-br from-gray-50 to-gray-100 border border-gray-200 rounded-xl p-4 shadow-md hover:shadow-lg transition-shadow">
            <div class="flex items-start gap-3">
              <div class="bg-gradient-to-br from-blue-500 to-blue-600 p-3 rounded-lg shadow-md flex-shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between mb-2">
                  <div class="text-xl font-bold text-blue-600">Rp <?= number_format((int) $r['jumlah'], 0, ',', '.') ?>
                  </div>
                  <div class="flex items-center gap-1 text-xs text-gray-500 bg-white px-2 py-1 rounded-lg">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <?= htmlspecialchars($r['tanggal']) ?>
                  </div>
                </div>
                <div class="space-y-1">
                  <div class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor"
                      viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <div>
                      <div class="text-xs text-gray-500">Siswa</div>
                      <div class="text-sm font-semibold text-gray-700"><?= htmlspecialchars($r['nama_siswa']) ?></div>
                    </div>
                  </div>
                  <div class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor"
                      viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <div>
                      <div class="text-xs text-gray-500">Tagihan</div>
                      <div class="text-sm font-semibold text-gray-700"><?= htmlspecialchars($r['nama_tagihan']) ?></div>
                    </div>
                  </div>
                  <div class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor"
                      viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    <div>
                      <div class="text-xs text-gray-500">Metode</div>
                      <div class="text-sm font-semibold text-gray-700"><?= htmlspecialchars($r['metode'] ?? '-') ?></div>
                    </div>
                  </div>
                  <?php if (!empty($r['keterangan'])): ?>
                    <div class="flex items-start gap-2">
                      <svg class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                      </svg>
                      <div>
                        <div class="text-xs text-gray-500">Keterangan</div>
                        <div class="text-sm text-gray-600"><?= htmlspecialchars($r['keterangan']) ?></div>
                      </div>
                    </div>
                  <?php endif; ?>
                  <div class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-amber-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor"
                      viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <div>
                      <div class="text-xs text-gray-500">Petugas Input</div>
                      <div class="text-sm font-semibold text-amber-700">
                        <?= htmlspecialchars($r['petugas_input'] ?? 'Sistem') ?>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Desktop: Table -->
      <div class="overflow-x-auto hidden lg:block">
        <table class="min-w-full">
          <thead>
            <tr class="bg-gradient-to-r from-gray-600 to-gray-700 text-white">
              <th class="text-left px-4 py-3 rounded-tl-lg text-sm font-semibold">No</th>
              <th class="text-left px-4 py-3 text-sm font-semibold">Tanggal</th>
              <th class="text-left px-4 py-3 text-sm font-semibold">Siswa</th>
              <th class="text-left px-4 py-3 text-sm font-semibold">Tagihan</th>
              <th class="text-right px-4 py-3 text-sm font-semibold">Jumlah</th>
              <th class="text-left px-4 py-3 text-sm font-semibold">Metode</th>
              <th class="text-left px-4 py-3 text-sm font-semibold">Keterangan</th>
              <th class="text-left px-4 py-3 rounded-tr-lg text-sm font-semibold">Petugas Input</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <?php $no = 1;
            foreach ($data['riwayat'] as $r): ?>
              <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-4 py-3">
                  <div class="flex items-center gap-2">
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 p-2 rounded-lg shadow-sm">
                      <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                      </svg>
                    </div>
                    <span class="text-sm font-semibold text-gray-600"><?= $no++ ?></span>
                  </div>
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                  <div class="flex items-center gap-2 text-sm text-gray-700">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <?= htmlspecialchars($r['tanggal']) ?>
                  </div>
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 font-medium"><?= htmlspecialchars($r['nama_siswa']) ?></td>
                <td class="px-4 py-3 text-sm text-gray-700"><?= htmlspecialchars($r['nama_tagihan']) ?></td>
                <td class="px-4 py-3 text-right">
                  <span class="text-sm font-bold text-blue-600">Rp
                    <?= number_format((int) $r['jumlah'], 0, ',', '.') ?></span>
                </td>
                <td class="px-4 py-3 text-sm text-gray-700"><?= htmlspecialchars($r['metode'] ?? '-') ?></td>
                <td class="px-4 py-3 text-sm text-gray-600"><?= htmlspecialchars($r['keterangan'] ?? '-') ?></td>
                <td class="px-4 py-3">
                  <span
                    class="inline-flex items-center px-2.5 py-1 bg-amber-100 text-amber-700 rounded-full text-xs font-medium">
                    <?= htmlspecialchars($r['petugas_input'] ?? 'Sistem') ?>
                  </span>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>