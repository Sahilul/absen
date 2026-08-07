<?php // Admin - Riwayat Semua Transaksi ?>
<div class="p-4 sm:p-6">
  <!-- Header -->
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
          <h1 class="text-2xl font-bold text-gray-800">Riwayat Transaksi Pembayaran</h1>
          <p class="text-sm text-gray-600">Semua transaksi di semua kelas</p>
        </div>
      </div>
      <div class="flex gap-2">
        <a href="<?= BASEURL ?>/admin/pembayaran"
          class="flex items-center gap-2 px-4 py-2 bg-gradient-to-br from-gray-500 to-gray-600 text-white rounded-lg hover:shadow-lg transition-shadow text-sm font-semibold">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Kembali
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
                  <div class="text-xl font-bold text-blue-600">Rp <?= number_format((int) $r['jumlah'], 0, ',', '.') ?></div>
                  <div class="flex items-center gap-1 text-xs text-gray-500 bg-white px-2 py-1 rounded-lg">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <?= htmlspecialchars($r['tanggal']) ?>
                  </div>
                </div>
                <div class="space-y-1">
                  <div class="text-sm">
                    <span class="text-gray-500">Kelas:</span>
                    <span class="font-semibold text-gray-700 ml-1"><?= htmlspecialchars($r['nama_kelas']) ?></span>
                  </div>
                  <div class="text-sm">
                    <span class="text-gray-500">Siswa:</span>
                    <span class="font-semibold text-gray-700 ml-1"><?= htmlspecialchars($r['nama_siswa']) ?></span>
                  </div>
                  <div class="text-sm">
                    <span class="text-gray-500">Tagihan:</span>
                    <span class="font-medium text-gray-600 ml-1"><?= htmlspecialchars($r['nama_tagihan']) ?></span>
                  </div>
                  <div class="text-sm">
                    <span class="text-gray-500">Metode:</span>
                    <span
                      class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 font-medium ml-1"><?= htmlspecialchars($r['metode'] ?? '-') ?></span>
                  </div>
                </div>
                <?php if (!empty($r['keterangan'])): ?>
                  <div class="text-sm text-gray-600 bg-gray-100 rounded px-2 py-1 mt-2">
                    <?= htmlspecialchars($r['keterangan']) ?>
                  </div>
                <?php endif; ?>
                <div class="text-sm mt-1">
                  <span class="text-gray-500">Petugas Input:</span>
                  <span
                    class="font-medium text-amber-600 ml-1"><?= htmlspecialchars($r['petugas_input'] ?? 'Sistem') ?></span>
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
            <th class="text-left px-4 py-3 text-sm font-semibold">Kelas</th>
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
              <td class="px-4 py-3 text-sm font-medium text-gray-700"><?= htmlspecialchars($r['nama_kelas']) ?></td>
              <td class="px-4 py-3 text-sm text-gray-700"><?= htmlspecialchars($r['nama_siswa']) ?></td>
              <td class="px-4 py-3 text-sm text-gray-700"><?= htmlspecialchars($r['nama_tagihan']) ?></td>
              <td class="px-4 py-3 text-right">
                <span class="text-sm font-bold text-blue-600">Rp <?= number_format((int) $r['jumlah'], 0, ',', '.') ?></span>
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