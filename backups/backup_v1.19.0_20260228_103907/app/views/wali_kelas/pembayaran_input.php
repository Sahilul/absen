<?php // View expects $data array, not individual variables ?>
<div class="p-4 sm:p-6 max-w-3xl mx-auto">
  <!-- Header Section -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div class="flex items-center gap-3">
      <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4Z"/></svg>
      </div>
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Input Pembayaran</h1>
        <p class="text-sm text-gray-500">Catat transaksi pembayaran siswa</p>
      </div>
    </div>
    
    <a href="<?= BASEURL ?>/waliKelas/pembayaranTagihan/<?= (int)($data['tagihan']['id'] ?? 0) ?>" class="px-4 py-2.5 bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white rounded-lg text-sm font-medium flex items-center gap-2 shadow-md hover:shadow-lg transition-all">
      <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
      Kembali
    </a>
  </div>

  <!-- Info Cards -->
  <div class="grid sm:grid-cols-2 gap-4 mb-6">
    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-5 text-white">
      <div class="flex items-center gap-3 mb-2">
        <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </div>
        <div>
          <div class="text-blue-100 text-xs font-medium">Nama Siswa</div>
          <div class="text-lg font-bold"><?= htmlspecialchars($data['siswa']['nama_siswa'] ?? '-') ?></div>
        </div>
      </div>
      <?php if (!empty($data['siswa']['nisn'])): ?>
        <div class="text-xs text-blue-100 mt-2">NISN: <?= htmlspecialchars($data['siswa']['nisn']) ?></div>
      <?php endif; ?>
    </div>
    
    <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl shadow-lg p-5 text-white">
      <div class="flex items-center gap-3 mb-2">
        <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        </div>
        <div>
          <div class="text-orange-100 text-xs font-medium">Nama Tagihan</div>
          <div class="text-lg font-bold"><?= htmlspecialchars($data['tagihan']['nama'] ?? '-') ?></div>
        </div>
      </div>
      <?php if (!empty($data['tagihan']['nominal_default'])): ?>
        <div class="text-xs text-orange-100 mt-2">Nominal: Rp <?= number_format((int)$data['tagihan']['nominal_default'], 0, ',', '.') ?></div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Form Input -->
  <form action="<?= BASEURL ?>/waliKelas/prosesPembayaran" method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
    <input type="hidden" name="tagihan_id" value="<?= (int)($data['tagihan']['id'] ?? 0) ?>" />
    <input type="hidden" name="id_siswa" value="<?= (int)($data['siswa']['id_siswa'] ?? 0) ?>" />

    <div class="flex items-center gap-2 mb-6">
      <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
      </div>
      <h2 class="text-lg font-semibold text-gray-800">Detail Pembayaran</h2>
    </div>

    <div class="space-y-5">
      <div>
        <label class="flex items-center gap-2 text-sm font-medium text-gray-700 mb-2">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
          Jumlah Pembayaran <span class="text-red-500">*</span>
        </label>
        <div class="relative">
          <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-semibold">Rp</span>
          <input name="jumlah" type="hidden" required />
          <input id="jumlah-display" type="text" 
                 class="w-full border-2 border-gray-300 rounded-lg pl-12 pr-4 py-3 text-lg font-semibold focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all" 
                 placeholder="0" />
        </div>
        <p class="text-xs text-gray-500 mt-1.5">Masukkan jumlah uang yang dibayarkan</p>
      </div>

      <div>
        <label class="flex items-center gap-2 text-sm font-medium text-gray-700 mb-2">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
          Metode Pembayaran <span class="text-red-500">*</span>
        </label>
        <select name="metode" class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 text-gray-700 font-medium focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all">
          <option value="tunai">💵 Tunai</option>
          <option value="transfer">🏦 Transfer Bank</option>
          <option value="e-wallet">📱 E-Wallet (GoPay, OVO, Dana, dll)</option>
          <option value="lainnya">📋 Lainnya</option>
        </select>
      </div>

      <div>
        <label class="flex items-center gap-2 text-sm font-medium text-gray-700 mb-2">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          Keterangan <span class="text-gray-400 text-xs">(Opsional)</span>
        </label>
        <textarea name="keterangan" rows="3" 
                  class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all" 
                  placeholder="Contoh: Pembayaran cicilan pertama, atau informasi tambahan lainnya..."></textarea>
      </div>
    </div>

    <div class="flex flex-col sm:flex-row justify-end gap-3 mt-8 pt-6 border-t border-gray-200">
      <a href="<?= BASEURL ?>/waliKelas/pembayaranTagihan/<?= (int)($data['tagihan']['id'] ?? 0) ?>" 
         class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-all text-center">
        Batal
      </a>
      <button type="submit" class="px-8 py-3 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white rounded-lg font-semibold shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
        Simpan Pembayaran
      </button>
    </div>
  </form>
</div>

<script>
// Format Rupiah dengan pemisah titik
const jumlahDisplay = document.getElementById('jumlah-display');
const jumlahHidden = document.querySelector('input[name="jumlah"]');

function formatRupiah(angka) {
  return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function cleanNumber(str) {
  return str.replace(/\./g, '');
}

jumlahDisplay.addEventListener('input', function(e) {
  let value = cleanNumber(e.target.value);
  
  // Hanya izinkan angka
  value = value.replace(/\D/g, '');
  
  // Update hidden input dengan nilai asli
  jumlahHidden.value = value;
  
  // Format tampilan
  if (value) {
    e.target.value = formatRupiah(value);
  } else {
    e.target.value = '';
  }
});

// Validasi saat submit
document.querySelector('form').addEventListener('submit', function(e) {
  if (!jumlahHidden.value || jumlahHidden.value == '0') {
    e.preventDefault();
    alert('Jumlah pembayaran harus diisi dan lebih dari 0');
    jumlahDisplay.focus();
  }
});
</script>
