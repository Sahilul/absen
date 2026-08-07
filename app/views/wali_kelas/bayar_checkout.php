<?php
$isBendahara = isset($data['bendahara_mode']) && $data['bendahara_mode'];
$urlPrefix = $isBendahara ? BASEURL . '/bendahara' : BASEURL . '/waliKelas';
?>
<!-- Checkout Pembayaran -->
<div class="p-4 sm:p-6">
  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div class="flex items-center gap-3">
      <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
      </div>
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Checkout Pembayaran</h1>
        <p class="text-sm text-gray-500"><?= htmlspecialchars($data['siswa']['nama_siswa']) ?> - <?= htmlspecialchars($data['keanggotaan']['nama_kelas'] ?? '-') ?></p>
      </div>
    </div>
    <a href="<?= $urlPrefix ?>/bayarSiswa/<?= $data['siswa']['id_siswa'] ?>" 
       class="px-4 py-2.5 bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white rounded-lg text-sm font-medium flex items-center gap-2 shadow-md hover:shadow-lg transition-all">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
      <span>Kembali</span>
    </a>
  </div>

  <?php if (empty($data['selected_tagihan'])): ?>
    <div class="bg-white rounded-xl shadow-lg p-12 border border-gray-200 text-center">
      <div class="w-24 h-24 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-yellow-500"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" x2="12" y1="9" y2="13"/><line x1="12" x2="12.01" y1="17" y2="17"/></svg>
      </div>
      <h3 class="text-lg font-semibold text-gray-700 mb-2">Tidak Ada Tagihan</h3>
      <p class="text-gray-500">Semua tagihan yang dipilih sudah lunas. Silakan pilih tagihan lain.</p>
      <a href="<?= $urlPrefix ?>/bayarSiswa/<?= $data['siswa']['id_siswa'] ?>" 
         class="inline-flex items-center gap-2 mt-4 px-6 py-2.5 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg font-medium shadow-md hover:shadow-lg transition-all">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg> Kembali ke Tagihan
      </a>
    </div>
  <?php else: ?>
    <div class="grid lg:grid-cols-3 gap-6">
      <!-- Left: Ringkasan Tagihan -->
      <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
          <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" x2="21" y1="6" y2="6"/><line x1="8" x2="21" y1="12" y2="12"/><line x1="8" x2="21" y1="18" y2="18"/><line x1="3" x2="3.01" y1="6" y2="6"/><line x1="3" x2="3.01" y1="12" y2="12"/><line x1="3" x2="3.01" y1="18" y2="18"/></svg>
            </div>
            <h2 class="text-lg font-semibold text-gray-800">Ringkasan Tagihan Dipilih</h2>
          </div>

          <!-- Mobile: Card Layout -->
          <div class="sm:hidden space-y-3">
            <?php $no = 1; foreach ($data['selected_tagihan'] as $t): ?>
              <div class="border border-gray-200 rounded-lg p-3">
                <div class="flex items-start justify-between gap-2 mb-2">
                  <span class="font-medium text-gray-800 text-sm"><?= $no++ ?>. <?= htmlspecialchars($t['nama']) ?></span>
                  <span class="text-red-600 font-bold text-sm whitespace-nowrap">Rp <?= number_format($t['sisa'], 0, ',', '.') ?></span>
                </div>
                <?php if ($t['jatuh_tempo']): ?>
                  <div class="text-xs text-gray-500 mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="inline mr-1"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg> <?= htmlspecialchars($t['jatuh_tempo']) ?>
                  </div>
                <?php endif; ?>
                <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500">
                  <span>Nominal: <span class="text-gray-700">Rp <?= number_format($t['nominal'], 0, ',', '.') ?></span></span>
                  <?php if ($t['diskon'] > 0): ?>
                    <span>Diskon: <span class="text-blue-600">Rp <?= number_format($t['diskon'], 0, ',', '.') ?></span></span>
                  <?php endif; ?>
                  <?php if ($t['total_terbayar'] > 0): ?>
                    <span>Terbayar: <span class="text-green-600">Rp <?= number_format($t['total_terbayar'], 0, ',', '.') ?></span></span>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
            <div class="bg-green-50 border border-green-200 rounded-lg p-3 flex items-center justify-between">
              <span class="font-bold text-gray-800 text-sm">Total Sisa</span>
              <span class="font-bold text-green-700 text-base">Rp <?= number_format($data['total_tagihan'], 0, ',', '.') ?></span>
            </div>
          </div>

          <!-- Desktop: Table Layout -->
          <div class="hidden sm:block overflow-x-auto rounded-lg border border-gray-200">
            <table class="min-w-full text-sm">
              <thead>
                <tr class="bg-gradient-to-r from-gray-50 to-gray-100">
                  <th class="text-left p-4 font-semibold text-gray-700 w-8">No</th>
                  <th class="text-left p-4 font-semibold text-gray-700">Nama Tagihan</th>
                  <th class="text-right p-4 font-semibold text-gray-700">Nominal</th>
                  <th class="text-right p-4 font-semibold text-gray-700">Diskon</th>
                  <th class="text-right p-4 font-semibold text-gray-700">Terbayar</th>
                  <th class="text-right p-4 font-semibold text-gray-700">Sisa</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <?php $no = 1; foreach ($data['selected_tagihan'] as $t): ?>
                  <tr>
                    <td class="p-4 text-gray-500"><?= $no++ ?></td>
                    <td class="p-4">
                      <span class="font-medium text-gray-800"><?= htmlspecialchars($t['nama']) ?></span>
                      <?php if ($t['jatuh_tempo']): ?>
                        <div class="text-xs text-gray-500 mt-1">
                          <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="inline mr-1"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg> <?= htmlspecialchars($t['jatuh_tempo']) ?>
                        </div>
                      <?php endif; ?>
                    </td>
                    <td class="p-4 text-right text-gray-700">Rp <?= number_format($t['nominal'], 0, ',', '.') ?></td>
                    <td class="p-4 text-right text-blue-600">Rp <?= number_format($t['diskon'], 0, ',', '.') ?></td>
                    <td class="p-4 text-right text-green-600">Rp <?= number_format($t['total_terbayar'], 0, ',', '.') ?></td>
                    <td class="p-4 text-right text-red-600 font-semibold">Rp <?= number_format($t['sisa'], 0, ',', '.') ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
              <tfoot>
                <tr class="bg-green-50 font-bold">
                  <td colspan="5" class="p-4 text-right text-gray-800">Total Sisa Tagihan</td>
                  <td class="p-4 text-right text-green-700 text-base">Rp <?= number_format($data['total_tagihan'], 0, ',', '.') ?></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>

      <!-- Right: Form Pembayaran -->
      <div class="lg:col-span-1">
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200 sticky top-4">
          <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
            </div>
            <h2 class="text-lg font-semibold text-gray-800">Form Pembayaran</h2>
          </div>

          <form method="POST" action="<?= $urlPrefix ?>/bayarProses" id="formCheckout">
            <input type="hidden" name="id_siswa" value="<?= $data['siswa']['id_siswa'] ?>">
            <input type="hidden" name="selected_ids" value="<?= htmlspecialchars($data['selected_ids']) ?>">

            <!-- Mode Bayar -->
            <div class="mb-4">
              <label class="block text-sm font-semibold text-gray-700 mb-2">Mode Pembayaran</label>
              <div class="flex gap-2">
                <label class="flex-1">
                  <input type="radio" name="mode" value="full" checked class="hidden peer" onchange="toggleMode()">
                  <div class="px-4 py-3 text-center border-2 border-gray-200 rounded-lg cursor-pointer peer-checked:border-green-500 peer-checked:bg-green-50 peer-checked:text-green-700 hover:border-gray-300 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="block mx-auto mb-1"><path d="M18 6 7 17l-5-5"/><path d="m22 10-7.5 7.5L13 16"/></svg>
                    <span class="text-sm font-semibold">Bayar Full</span>
                    <div class="text-xs text-gray-500 mt-1">Lunas Semua</div>
                  </div>
                </label>
                <label class="flex-1">
                  <input type="radio" name="mode" value="cicil" class="hidden peer" onchange="toggleMode()">
                  <div class="px-4 py-3 text-center border-2 border-gray-200 rounded-lg cursor-pointer peer-checked:border-orange-500 peer-checked:bg-orange-50 peer-checked:text-orange-700 hover:border-gray-300 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="block mx-auto mb-1"><circle cx="8" cy="8" r="6"/><path d="M18.09 10.37A6 6 0 1 1 10.34 18"/><path d="M7 6h1v4"/><path d="m16.71 13.88.7.71-2.82 2.82"/></svg>
                    <span class="text-sm font-semibold">Cicil</span>
                    <div class="text-xs text-gray-500 mt-1">Bayar Sebagian</div>
                  </div>
                </label>
              </div>
            </div>

            <!-- Jumlah Bayar -->
            <div class="mb-4" id="divJumlahFull">
              <label class="block text-sm font-semibold text-gray-700 mb-2">Total Pembayaran</label>
              <div class="px-4 py-3 bg-green-50 border border-green-200 rounded-lg">
                <div class="text-2xl font-bold text-green-700">Rp <?= number_format($data['total_tagihan'], 0, ',', '.') ?></div>
                <div class="text-xs text-green-600 mt-1">Pembayaran penuh untuk semua tagihan terpilih</div>
              </div>
            </div>

            <div class="mb-4 hidden" id="divJumlahCicil">
              <label class="block text-sm font-semibold text-gray-700 mb-2">Jumlah Dibayar</label>
              <input type="text" name="jumlah_bayar" id="jumlahBayar"
                     placeholder="Masukkan jumlah pembayaran"
                     class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-lg font-semibold">
              <div class="text-xs text-gray-500 mt-1">
                Maksimal: Rp <?= number_format($data['total_tagihan'], 0, ',', '.') ?>. 
                Pembayaran akan dialokasikan ke tagihan paling atas terlebih dahulu.
              </div>
            </div>

            <!-- Metode -->
            <div class="mb-4">
              <label class="block text-sm font-semibold text-gray-700 mb-2">Metode Pembayaran</label>
              <select name="metode" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm">
                <option value="Tunai">Tunai</option>
                <option value="Transfer">Transfer</option>
                <option value="QRIS">QRIS</option>
              </select>
            </div>

            <!-- Keterangan -->
            <div class="mb-6">
              <label class="block text-sm font-semibold text-gray-700 mb-2">Keterangan (opsional)</label>
              <input type="text" name="keterangan" placeholder="Misal: Pembayaran SPP Januari"
                     class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm">
            </div>

            <!-- Submit -->
            <button type="submit" 
                    class="w-full px-6 py-3.5 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white rounded-lg font-semibold shadow-lg hover:shadow-xl transition-all flex items-center justify-center gap-2 text-base">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
              <span>Bayar Sekarang</span>
            </button>
          </form>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>

<script>
function toggleMode() {
  var mode = document.querySelector('input[name="mode"]:checked').value;
  var divFull = document.getElementById('divJumlahFull');
  var divCicil = document.getElementById('divJumlahCicil');

  if (mode === 'full') {
    divFull.classList.remove('hidden');
    divCicil.classList.add('hidden');
  } else {
    divFull.classList.add('hidden');
    divCicil.classList.remove('hidden');
  }
}

document.addEventListener('DOMContentLoaded', function() {
  toggleMode();
});
</script>
