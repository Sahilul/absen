<!-- Admin - Detail Tagihan Per Siswa -->
<div class="p-4 sm:p-6">
  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div class="flex items-center gap-3">
      <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/><path d="M16 13H8"/><path d="M16 17H8"/><path d="M10 9H8"/></svg>
      </div>
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Tagihan Siswa</h1>
        <p class="text-sm text-gray-500"><?= htmlspecialchars($data['siswa']['nama_siswa']) ?> - <?= htmlspecialchars($data['siswa']['nisn']) ?></p>
      </div>
    </div>
    <div class="flex flex-wrap gap-2">
      <a href="<?= BASEURL ?>/admin/bayar" class="px-4 py-2.5 bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white rounded-lg text-sm font-medium flex items-center gap-2 shadow-md hover:shadow-lg transition-all">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
        <span>Kembali</span>
      </a>
      <button onclick="showPrintOptions()" class="px-4 py-2.5 bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white rounded-lg text-sm font-medium flex items-center gap-2 shadow-md hover:shadow-lg transition-all">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
        <span>Print</span>
      </button>
    </div>
  </div>

  <!-- Info Siswa -->
  <div class="bg-white rounded-xl shadow-lg p-5 mb-6 border border-gray-200">
    <div class="grid sm:grid-cols-3 gap-4">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-600"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </div>
        <div>
          <div class="text-xs text-gray-500">Nama Siswa</div>
          <div class="font-semibold text-gray-800"><?= htmlspecialchars($data['siswa']['nama_siswa']) ?></div>
        </div>
      </div>
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-purple-600"><rect width="20" height="14" x="2" y="5" rx="2"/><path d="M12 13a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/><path d="M6 19c0-2 2-3 6-3s6 1 6 3"/></svg>
        </div>
        <div>
          <div class="text-xs text-gray-500">NISN</div>
          <div class="font-semibold text-gray-800"><?= htmlspecialchars($data['siswa']['nisn']) ?></div>
        </div>
      </div>
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-600"><path d="M2 10v10c0 .6.4 1 1 1h4V14h6v7h4c.6 0 1-.4 1-1V10"/><path d="m12 2-9 7h18Z"/></svg>
        </div>
        <div>
          <div class="text-xs text-gray-500">Kelas</div>
          <div class="font-semibold text-gray-800"><?= htmlspecialchars($data['keanggotaan']['nama_kelas'] ?? '-') ?></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Daftar Tagihan -->
  <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
    <form id="formBayar" method="GET" action="<?= BASEURL ?>/admin/bayarCheckout/<?= $data['siswa']['id_siswa'] ?>">
      <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 bg-orange-500 rounded-lg flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 17.5v-11"/></svg>
          </div>
          <h2 class="text-lg font-semibold text-gray-800">
            Daftar Tagihan
            <span class="text-sm font-normal text-gray-500 ml-2">(<?= count($data['tagihan_list']) ?> tagihan)</span>
          </h2>
        </div>
        <div id="totalSelected" class="hidden text-sm font-semibold text-green-700 bg-green-50 px-4 py-2 rounded-lg">
          Total dipilih: <span id="totalNominal">Rp 0</span>
        </div>
      </div>

      <?php if (empty($data['tagihan_list'])): ?>
        <div class="text-center py-12">
          <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400"><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 17.5v-11"/></svg>
          </div>
          <p class="text-gray-500 font-medium">Tidak ada tagihan untuk siswa ini</p>
          <p class="text-sm text-gray-400 mt-1">Siswa belum memiliki tagihan di kelasnya</p>
        </div>
      <?php else: ?>
        <!-- Mobile: Cards -->
        <div class="space-y-3 lg:hidden">
          <?php foreach ($data['tagihan_list'] as $t):
            $isLunas = $t['status'] === 'lunas';
            $statusClass = $isLunas ? 'bg-green-100 text-green-700 border-green-300' : ($t['status'] === 'sebagian' ? 'bg-yellow-100 text-yellow-700 border-yellow-300' : 'bg-red-100 text-red-700 border-red-300');
            $statusIcon = $isLunas ? 'check-circle' : ($t['status'] === 'sebagian' ? 'clock' : 'alert-circle');
          ?>
            <div class="border rounded-xl p-4 hover:shadow-md transition-shadow <?= $isLunas ? 'bg-gray-50 border-green-200' : 'bg-white border-gray-200' ?>">
              <div class="flex items-start gap-3">
                <?php if ($isLunas): ?>
                  <div class="mt-1 w-6 h-6 bg-green-100 rounded flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-600"><polyline points="20 6 9 17 4 12"/></svg>
                  </div>
                <?php else: ?>
                  <input type="checkbox" name="ids[]" value="<?= $t['id'] ?>" 
                         class="mt-1 w-5 h-5 text-green-600 rounded focus:ring-green-500 checkbox-tagihan flex-shrink-0"
                         data-nominal="<?= $t['sisa'] ?>">
                <?php endif; ?>
                <div class="flex-1">
                  <div class="font-semibold text-gray-800"><?= htmlspecialchars($t['nama']) ?></div>
                  <div class="flex flex-wrap items-center gap-2 mt-1">
                    <span class="inline-flex items-center px-2.5 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-medium">
                      Rp <?= number_format($t['nominal'], 0, ',', '.') ?>
                    </span>
                    <?php if ($t['diskon'] > 0): ?>
                      <span class="inline-flex items-center px-2.5 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">
                        Diskon: Rp <?= number_format($t['diskon'], 0, ',', '.') ?>
                      </span>
                    <?php endif; ?>
                    <?php if ($t['total_terbayar'] > 0): ?>
                      <span class="inline-flex items-center px-2.5 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">
                        Dibayar: Rp <?= number_format($t['total_terbayar'], 0, ',', '.') ?>
                      </span>
                    <?php endif; ?>
                  </div>
                  <div class="flex items-center gap-2 mt-2">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium border <?= $statusClass ?>">
                      <?php if ($statusIcon === 'check-circle'): ?><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg><?php elseif ($statusIcon === 'clock'): ?><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg><?php else: ?><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg><?php endif; ?>
                      <?= $isLunas ? 'Lunas' : 'Sisa: Rp ' . number_format($t['sisa'], 0, ',', '.') ?>
                    </span>
                    <?php if ($t['jatuh_tempo']): ?>
                      <span class="text-xs text-gray-500">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="inline mr-1"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg> Jatuh tempo: <?= htmlspecialchars($t['jatuh_tempo']) ?>
                      </span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Desktop: Table -->
        <div class="overflow-x-auto hidden lg:block rounded-lg border border-gray-200">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="bg-gradient-to-r from-gray-50 to-gray-100">
                <th class="text-center p-4 font-semibold text-gray-700 w-12">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                </th>
                <th class="text-left p-4 font-semibold text-gray-700">Nama Tagihan</th>
                <th class="text-left p-4 font-semibold text-gray-700">Nominal</th>
                <th class="text-left p-4 font-semibold text-gray-700">Diskon</th>
                <th class="text-left p-4 font-semibold text-gray-700">Terbayar</th>
                <th class="text-left p-4 font-semibold text-gray-700">Sisa</th>
                <th class="text-center p-4 font-semibold text-gray-700">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <?php foreach ($data['tagihan_list'] as $t):
                $isLunas = $t['status'] === 'lunas';
              ?>
                <tr class="hover:bg-green-50 transition-colors <?= $isLunas ? 'bg-gray-50' : '' ?>">
                  <td class="p-4 text-center">
                    <?php if ($isLunas): ?>
                      <span class="inline-flex items-center justify-center w-6 h-6 bg-green-100 rounded">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-600"><polyline points="20 6 9 17 4 12"/></svg>
                      </span>
                    <?php else: ?>
                      <input type="checkbox" name="ids[]" value="<?= $t['id'] ?>" 
                             class="w-5 h-5 text-green-600 rounded focus:ring-green-500 checkbox-tagihan"
                             data-nominal="<?= $t['sisa'] ?>">
                    <?php endif; ?>
                  </td>
                  <td class="p-4">
                    <div class="flex items-center gap-2">
                      <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-600"><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 17.5v-11"/></svg>
                      </div>
                      <span class="font-medium text-gray-800"><?= htmlspecialchars($t['nama']) ?></span>
                    </div>
                    <?php if ($t['jatuh_tempo']): ?>
                      <div class="text-xs text-gray-500 mt-1 ml-10">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="inline mr-1"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg> <?= htmlspecialchars($t['jatuh_tempo']) ?>
                      </div>
                    <?php endif; ?>
                  </td>
                  <td class="p-4">
                    <span class="text-gray-700 font-medium">Rp <?= number_format($t['nominal'], 0, ',', '.') ?></span>
                  </td>
                  <td class="p-4">
                    <span class="text-blue-600 font-medium">Rp <?= number_format($t['diskon'], 0, ',', '.') ?></span>
                  </td>
                  <td class="p-4">
                    <span class="text-green-600 font-semibold">Rp <?= number_format($t['total_terbayar'], 0, ',', '.') ?></span>
                  </td>
                  <td class="p-4">
                    <span class="<?= $t['sisa'] > 0 ? 'text-red-600 font-semibold' : 'text-green-600' ?>">
                      Rp <?= number_format($t['sisa'], 0, ',', '.') ?>
                    </span>
                  </td>
                  <td class="p-4 text-center">
                    <?php if ($isLunas): ?>
                      <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-green-100 text-green-700 rounded-full text-xs font-semibold">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> Lunas
                      </span>
                    <?php elseif ($t['status'] === 'sebagian'): ?>
                      <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-yellow-100 text-yellow-700 rounded-full text-xs font-semibold">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> Sebagian
                      </span>
                    <?php else: ?>
                      <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-100 text-red-700 rounded-full text-xs font-semibold">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg> Belum
                      </span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <!-- Submit Button -->
        <div class="mt-6 flex justify-end">
          <button type="submit" id="btnLanjut" disabled
                  class="px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white rounded-lg font-semibold shadow-lg hover:shadow-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
            <span>Lanjut Pembayaran</span>
          </button>
        </div>
      <?php endif; ?>
    </form>
  </div>
</div>

<!-- Modal Pilih Opsi Cetak -->
<div id="modalPrintOptions" class="hidden fixed inset-0 bg-black bg-opacity-50 z-[9999] flex items-center justify-center p-4">
  <div class="bg-white rounded-xl shadow-2xl max-w-md w-full overflow-hidden">
    <div class="bg-gradient-to-r from-purple-600 to-purple-700 p-5 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
        </div>
        <div>
          <h3 class="text-lg font-bold text-white">Pilih Metode Cetak</h3>
          <p class="text-sm text-purple-100"><?= htmlspecialchars($data['siswa']['nama_siswa']) ?></p>
        </div>
      </div>
      <button type="button" onclick="closePrintOptions()" class="text-white hover:bg-white/20 rounded-lg p-2 transition-all">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" x2="6" y1="6" y2="18"/><line x1="6" x2="18" y1="6" y2="18"/></svg>
      </button>
    </div>
    <div class="p-5 space-y-3">
      <p class="text-sm text-gray-600 mb-4">Pilih metode cetak thermal printer:</p>

      <!-- Bluetooth Option -->
      <button onclick="printViaBluetooth()"
        class="w-full flex items-center gap-4 p-4 bg-gradient-to-r from-blue-50 to-blue-100 hover:from-blue-100 hover:to-blue-200 border border-blue-200 rounded-xl transition-all group">
        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-md group-hover:shadow-lg transition-shadow">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7 7 10 10-5 5V2l5 5L7 17"/></svg>
        </div>
        <div class="text-left flex-1">
          <div class="font-semibold text-blue-800">Bluetooth Printer</div>
          <div class="text-xs text-blue-600">Untuk printer thermal wireless (Android/Desktop)</div>
        </div>
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-400"><path d="m9 18 6-6-6-6"/></svg>
      </button>

      <!-- USB Option -->
      <button onclick="printViaUSB()"
        class="w-full flex items-center gap-4 p-4 bg-gradient-to-r from-green-50 to-green-100 hover:from-green-100 hover:to-green-200 border border-green-200 rounded-xl transition-all group">
        <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center shadow-md group-hover:shadow-lg transition-shadow">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.5 2A2.5 2.5 0 0 1 12 4.5v15a2.5 2.5 0 0 1-4.96.44 2.5 2.5 0 0 1-2.96-3.08 3 3 0 0 1-.34-5.58 2.5 2.5 0 0 1 1.32-4.24 2.5 2.5 0 0 1 4.44-1.04z"/><path d="M14.5 2A2.5 2.5 0 0 0 12 4.5v15a2.5 2.5 0 0 0 4.96.44 2.5 2.5 0 0 0 2.96-3.08 3 3 0 0 0 .34-5.58 2.5 2.5 0 0 0-1.32-4.24 2.5 2.5 0 0 0-4.44-1.04z"/></svg>
        </div>
        <div class="text-left flex-1">
          <div class="font-semibold text-green-800">USB Printer</div>
          <div class="text-xs text-green-600">Untuk printer thermal kabel USB (Desktop)</div>
        </div>
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-400"><path d="m9 18 6-6-6-6"/></svg>
      </button>

      <!-- Browser Print Option -->
      <button onclick="printViaBrowser()"
        class="w-full flex items-center gap-4 p-4 bg-gradient-to-r from-orange-50 to-orange-100 hover:from-orange-100 hover:to-orange-200 border border-orange-200 rounded-xl transition-all group">
        <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl flex items-center justify-center shadow-md group-hover:shadow-lg transition-shadow">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
        </div>
        <div class="text-left flex-1">
          <div class="font-semibold text-orange-800">Browser Print (58mm/80mm)</div>
          <div class="text-xs text-orange-600">Format thermal via dialog print browser</div>
        </div>
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-orange-400"><path d="m9 18 6-6-6-6"/></svg>
      </button>
    </div>
    <div class="bg-gray-50 px-5 py-4 flex justify-end">
      <button onclick="closePrintOptions()" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-medium transition-all">Batal</button>
    </div>
  </div>
</div>

<script>
var THERMAL_URL = '<?= BASEURL ?>/admin/bayarSiswaThermalData/<?= (int)$data['siswa']['id_siswa'] ?>';

function showPrintOptions() {
  document.getElementById('modalPrintOptions').classList.remove('hidden');
}

function closePrintOptions() {
  document.getElementById('modalPrintOptions').classList.add('hidden');
}

async function fetchThermalData() {
  try {
    const response = await fetch(THERMAL_URL);
    const result = await response.json();
    if (!result.success) throw new Error(result.error || 'Gagal mengambil data');
    return result.data;
  } catch (error) {
    alert('Gagal mengambil data: ' + error.message);
    return null;
  }
}

function formatRupiah(angka) {
  return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// === BLUETOOTH ===
async function printViaBluetooth() {
  closePrintOptions();

  var isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
  if (isIOS) {
    alert('Cetak Bluetooth tidak didukung di iPhone/iPad.\nGunakan opsi "Browser Print" atau perangkat Android/Desktop.');
    return;
  }
  if (!navigator.bluetooth) {
    alert('Web Bluetooth tidak didukung di browser ini.\nGunakan Chrome/Edge di Android atau desktop.\n\nAtau coba opsi "Browser Print".');
    return;
  }

  try {
    var data = await fetchThermalData();
    if (!data) return;

    var device = await navigator.bluetooth.requestDevice({
      filters: [{ services: ['000018f0-0000-1000-8000-00805f9b34fb'] }],
      optionalServices: ['000018f0-0000-1000-8000-00805f9b34fb']
    });

    var server = await device.gatt.connect();
    var service = await server.getPrimaryService('000018f0-0000-1000-8000-00805f9b34fb');
    var characteristic = await service.getCharacteristic('00002af1-0000-1000-8000-00805f9b34fb');

    var commands = buildESCPOSCommands(data);
    var chunkSize = 200;
    for (var i = 0; i < commands.length; i += chunkSize) {
      var chunk = commands.slice(i, i + chunkSize);
      await characteristic.writeValue(chunk);
      await new Promise(resolve => setTimeout(resolve, 50));
    }

    await device.gatt.disconnect();
    alert('Berhasil mencetak via Bluetooth!');
  } catch (error) {
    if (error.name !== 'NotFoundError') {
      alert('Gagal mencetak via Bluetooth: ' + error.message);
    }
  }
}

// === USB ===
async function printViaUSB() {
  closePrintOptions();

  if (!navigator.usb) {
    alert('WebUSB tidak didukung di browser ini.\nGunakan Chrome/Edge di desktop.\n\nAtau coba opsi "Browser Print".');
    return;
  }

  try {
    var data = await fetchThermalData();
    if (!data) return;

    var device = await navigator.usb.requestDevice({
      filters: [
        { vendorId: 0x0483 }, { vendorId: 0x0416 }, { vendorId: 0x04B8 },
        { vendorId: 0x0519 }, { vendorId: 0x0DD4 }, { vendorId: 0x154F },
        { vendorId: 0x0FE6 }, { vendorId: 0x1504 }, { vendorId: 0x0525 },
        { vendorId: 0x28E9 }, { vendorId: 0x1A86 }, { vendorId: 0x067B }
      ]
    });

    await device.open();
    var interfaceNumber = 0, endpointNumber = 1;
    for (var config of device.configurations) {
      for (var iface of config.interfaces) {
        for (var alternate of iface.alternates) {
          for (var endpoint of alternate.endpoints) {
            if (endpoint.direction === 'out' && endpoint.type === 'bulk') {
              interfaceNumber = iface.interfaceNumber;
              endpointNumber = endpoint.endpointNumber;
            }
          }
        }
      }
    }

    await device.selectConfiguration(1);
    await device.claimInterface(interfaceNumber);
    var commands = buildESCPOSCommands(data);
    await device.transferOut(endpointNumber, commands);
    await device.releaseInterface(interfaceNumber);
    await device.close();
    alert('Berhasil mencetak via USB!');
  } catch (error) {
    if (error.name !== 'NotFoundError') {
      alert('Gagal mencetak via USB: ' + error.message + '\n\nPastikan:\n1. Printer USB terhubung\n2. Printer dalam keadaan ready');
    }
  }
}

// === BROWSER PRINT ===
async function printViaBrowser() {
  closePrintOptions();

  try {
    var data = await fetchThermalData();
    if (!data) return;

    var html = buildThermalHTML(data);
    var printWindow = window.open('', '_blank', 'width=400,height=600');
    if (!printWindow) {
      alert('Popup diblokir browser. Izinkan popup untuk halaman ini.');
      return;
    }
    printWindow.document.write(html);
    printWindow.document.close();
    printWindow.onload = function() {
      setTimeout(function() { printWindow.print(); }, 250);
    };
  } catch (error) {
    alert('Gagal mencetak: ' + error.message);
  }
}

// === BUILD THERMAL HTML ===
function buildThermalHTML(data) {
  var tagihanHTML = '';
  data.tagihan_list.forEach(function(t) {
    var statusLabel = t.status === 'lunas' ? '✓ Lunas' : (t.status === 'sebagian' ? '~ Sebagian' : '✗ Belum');
    tagihanHTML += '<div style="border-bottom:1px dashed #000;padding:2px 0;">';
    tagihanHTML += '<div>' + t.nama + '</div>';
    tagihanHTML += '<div class="row"><span>' + statusLabel + '</span><span>Rp ' + formatRupiah(t.terbayar) + '/' + formatRupiah(t.nominal - t.diskon) + '</span></div>';
    tagihanHTML += '</div>';
  });

  var qrHTML = '';
  if (data.qr && data.qr.url) {
    qrHTML = '<div class="qr-container"><div style="font-size:9px;">Scan untuk validasi:</div><img src="' + data.qr.url + '" alt="QR Code"></div>';
  }

  return '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Rekap Tagihan</title>'
    + '<style>'
    + '@page { size: 58mm auto; margin: 0; }'
    + '@media print { html, body { width: 58mm; margin: 0; padding: 0; } }'
    + 'body { font-family: "Courier New", monospace; font-size: 11px; font-weight: bold; width: 58mm; margin: 0 auto; padding: 0 2mm; line-height: 1.1; }'
    + '.center { text-align: center; }'
    + '.bold { font-weight: bold; }'
    + '.big { font-size: 14px; font-weight: bold; }'
    + '.divider { border-top: 1px dashed #000; margin: 2px 0; }'
    + '.double-divider { border-top: 2px solid #000; margin: 2px 0; }'
    + '.row { display: flex; justify-content: space-between; margin: 0; padding: 0; }'
    + '.qr-container { text-align: center; margin: 3px 0; }'
    + '.qr-container img { width: 80px; height: 80px; }'
    + '</style></head><body>'
    + '<div class="center big">' + data.sekolah + '</div>'
    + '<div class="center bold">' + data.judul + '</div>'
    + '<div class="divider"></div>'
    + '<div class="row"><span>Tanggal</span><span>' + data.tanggal + '</span></div>'
    + '<div class="row"><span>Semester</span><span>' + data.semester + '</span></div>'
    + '<div class="divider"></div>'
    + '<div><strong>Siswa:</strong></div>'
    + '<div class="row"><span>Nama</span><span>' + data.siswa.nama + '</span></div>'
    + '<div class="row"><span>NISN</span><span>' + data.siswa.nisn + '</span></div>'
    + '<div class="row"><span>Kelas</span><span>' + data.siswa.kelas + '</span></div>'
    + '<div class="divider"></div>'
    + '<div><strong>Daftar Tagihan:</strong></div>'
    + tagihanHTML
    + '<div class="double-divider"></div>'
    + '<div class="row"><span>Total Tagihan</span><span>Rp ' + formatRupiah(data.total.nominal) + '</span></div>'
    + '<div class="row"><span>Total Diskon</span><span>Rp ' + formatRupiah(data.total.diskon) + '</span></div>'
    + '<div class="row bold"><span>Total Terbayar</span><span>Rp ' + formatRupiah(data.total.terbayar) + '</span></div>'
    + '<div class="row bold"><span>Total Sisa</span><span>Rp ' + formatRupiah(data.total.sisa) + '</span></div>'
    + '<div class="double-divider"></div>'
    + qrHTML
    + '<div class="center" style="font-size:9px;margin-top:4px;">Terima kasih<br>Invoice sah tanpa tanda tangan</div>'
    + '<br><br><br><br><br></body></html>';
}

// === BUILD ESC/POS COMMANDS ===
function buildESCPOSCommands(data) {
  var encoder = new TextEncoder();
  var commands = [];

  function addText(text) { var encoded = encoder.encode(text); for (var i = 0; i < encoded.length; i++) commands.push(encoded[i]); }
  function addBytes(bytes) { for (var i = 0; i < bytes.length; i++) commands.push(bytes[i]); }

  // Initialize
  addBytes([0x1B, 0x40]);
  // Bold ON
  addBytes([0x1B, 0x45, 0x01]);
  // Center
  addBytes([0x1B, 0x61, 0x01]);
  // Double size header
  addBytes([0x1D, 0x21, 0x11]);
  addText(data.sekolah + '\n');
  addBytes([0x1D, 0x21, 0x00]);
  addText(data.judul + '\n');
  addText('--------------------------------\n');

  // Left align
  addBytes([0x1B, 0x61, 0x00]);
  addText('Tanggal  : ' + data.tanggal + '\n');
  addText('Semester : ' + data.semester + '\n');
  addText('--------------------------------\n');
  addText('Nama  : ' + data.siswa.nama + '\n');
  addText('NISN  : ' + data.siswa.nisn + '\n');
  addText('Kelas : ' + data.siswa.kelas + '\n');
  addText('--------------------------------\n');
  addText('DAFTAR TAGIHAN:\n');
  addText('--------------------------------\n');

  data.tagihan_list.forEach(function(t) {
    var statusLabel = t.status === 'lunas' ? '[LUNAS]' : (t.status === 'sebagian' ? '[SEBAGIAN]' : '[BELUM]');
    addText(t.nama + ' ' + statusLabel + '\n');
    addText('  Bayar: Rp ' + formatRupiah(t.terbayar) + '/' + formatRupiah(t.nominal - t.diskon) + '\n');
    addText('- - - - - - - - - - - - - - - -\n');
  });

  addText('================================\n');
  addText('Total Tagihan : Rp ' + formatRupiah(data.total.nominal) + '\n');
  addText('Total Diskon  : Rp ' + formatRupiah(data.total.diskon) + '\n');
  addText('--------------------------------\n');

  addBytes([0x1D, 0x21, 0x11]);
  addText('Terbayar: Rp ' + formatRupiah(data.total.terbayar) + '\n');
  addText('Sisa    : Rp ' + formatRupiah(data.total.sisa) + '\n');
  addBytes([0x1D, 0x21, 0x00]);
  addText('================================\n');

  // QR Code if available
  if (data.qr && data.qr.data) {
    addBytes([0x1B, 0x61, 0x01]); // Center
    addText('Scan untuk validasi:\n');

    var qrData = data.qr.data;
    var qrLength = qrData.length;
    var pL = qrLength % 256;
    var pH = Math.floor(qrLength / 256);

    // QR Code model
    addBytes([0x1D, 0x28, 0x6B, 0x04, 0x00, 0x31, 0x41, 0x32, 0x00]);
    // QR Code size
    addBytes([0x1D, 0x28, 0x6B, 0x03, 0x00, 0x31, 0x43, 0x06]);
    // Error correction
    addBytes([0x1D, 0x28, 0x6B, 0x03, 0x00, 0x31, 0x45, 0x30]);
    // Store data
    addBytes([0x1D, 0x28, 0x6B, (pL + 3) & 0xFF, pH, 0x31, 0x50, 0x30]);
    for (var q = 0; q < qrLength; q++) {
      commands.push(qrData.charCodeAt(q));
    }
    // Print QR
    addBytes([0x1D, 0x28, 0x6B, 0x03, 0x00, 0x31, 0x51, 0x30]);
  }

  // Center footer
  addBytes([0x1B, 0x61, 0x01]);
  addText('Terima kasih\n');
  addText('Invoice sah tanpa tanda tangan\n');
  addText('\n\n\n\n\n\n');

  // Cut paper
  addBytes([0x1D, 0x56, 0x00]);
  // Bold OFF
  addBytes([0x1B, 0x45, 0x00]);

  return new Uint8Array(commands);
}

document.addEventListener('DOMContentLoaded', function() {
  var checkboxes = document.querySelectorAll('.checkbox-tagihan');
  var btnLanjut = document.getElementById('btnLanjut');
  var totalSelected = document.getElementById('totalSelected');
  var totalNominal = document.getElementById('totalNominal');

  function updateTotal() {
    var total = 0;
    var count = 0;
    checkboxes.forEach(function(cb) {
      if (cb.checked) {
        total += parseInt(cb.getAttribute('data-nominal')) || 0;
        count++;
      }
    });

    if (count > 0) {
      totalSelected.classList.remove('hidden');
      totalNominal.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
      btnLanjut.disabled = false;
    } else {
      totalSelected.classList.add('hidden');
      btnLanjut.disabled = true;
    }
  }

  checkboxes.forEach(function(cb) {
    cb.addEventListener('change', updateTotal);
  });

  updateTotal();
});
</script>
