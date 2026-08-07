<!-- Admin - Halaman Pembayaran Siswa (Filter Kelas + Cari Siswa) -->
<div class="p-4 sm:p-6">
  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div class="flex items-center gap-3">
      <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="2" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
      </div>
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Pembayaran Siswa</h1>
        <p class="text-sm text-gray-500">Cari siswa dan lakukan pembayaran tagihan</p>
      </div>
    </div>
    <div class="flex flex-wrap gap-2">
      <a href="<?= BASEURL ?>/admin/pembayaran" class="px-4 py-2.5 bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white rounded-lg text-sm font-medium flex items-center gap-2 shadow-md hover:shadow-lg transition-all">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" x2="21" y1="6" y2="6"/><line x1="8" x2="21" y1="12" y2="12"/><line x1="8" x2="21" y1="18" y2="18"/><line x1="3" x2="3.01" y1="6" y2="6"/><line x1="3" x2="3.01" y1="12" y2="12"/><line x1="3" x2="3.01" y1="18" y2="18"/></svg>
        <span>Daftar Kelas</span>
      </a>
      <a href="<?= BASEURL ?>/admin/pembayaranRiwayat" class="px-4 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg text-sm font-medium flex items-center gap-2 shadow-md hover:shadow-lg transition-all">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>Riwayat</span>
      </a>
    </div>
  </div>

  <!-- Filter Section -->
  <div class="bg-white rounded-xl shadow-lg p-6 mb-6 border border-gray-200">
    <form method="GET" action="<?= BASEURL ?>/admin/bayar" class="space-y-4">
      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="inline text-gray-500 mr-1"><path d="m12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.84Z"/><path d="m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65"/><path d="m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65"/></svg> Rombel / Kelas
          </label>
          <select name="id_kelas" onchange="this.form.submit()" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm">
            <option value="">-- Semua Kelas --</option>
            <?php foreach ($data['kelas_list'] as $k): ?>
              <option value="<?= $k['id_kelas'] ?>" <?= (($data['filter_kelas'] ?? '') == $k['id_kelas']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($k['nama_kelas']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="inline text-gray-500 mr-1"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg> Cari Nama / NISN
          </label>
          <input type="text" name="search" id="searchInput" value="<?= htmlspecialchars($data['filter_search'] ?? '') ?>"
                 placeholder="Ketik nama atau NISN siswa..."
                 class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm">
        </div>
      </div>
    </form>
    <script>
    (function() {
      var timer;
      var input = document.getElementById('searchInput');
      if (input) {
        input.addEventListener('input', function() {
          clearTimeout(timer);
          timer = setTimeout(function() { input.form.submit(); }, 500);
        });
      }
    })();
    </script>
  </div>

  <!-- Hasil Pencarian -->
  <?php if (isset($data['siswa_list'])): ?>
    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
      <div class="flex items-center gap-2 mb-4">
        <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <h2 class="text-lg font-semibold text-gray-800">
          Hasil Pencarian
          <span class="text-sm font-normal text-gray-500 ml-2">(<?= count($data['siswa_list']) ?> siswa)</span>
        </h2>
      </div>

      <?php if (empty($data['siswa_list'])): ?>
        <div class="text-center py-12">
          <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="17" x2="22" y1="8" y2="13"/><line x1="22" x2="17" y1="8" y2="13"/></svg>
          </div>
          <p class="text-gray-500 font-medium">Tidak ada siswa ditemukan</p>
          <p class="text-sm text-gray-400 mt-1">Coba ubah filter kelas atau kata kunci pencarian</p>
        </div>
      <?php else: ?>
        <!-- Mobile: Cards -->
        <div class="space-y-3 lg:hidden">
          <?php foreach ($data['siswa_list'] as $s): ?>
            <div class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition-shadow bg-gradient-to-br from-white to-gray-50">
              <div class="flex items-start justify-between">
                <div class="flex-1">
                  <div class="font-semibold text-gray-800 mb-1"><?= htmlspecialchars($s['nama_siswa']) ?></div>
                  <div class="text-sm text-gray-500"><?= htmlspecialchars($s['nisn']) ?></div>
                  <div class="mt-1">
                    <span class="inline-flex items-center px-2.5 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">
                      <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1"><path d="M2 10v10c0 .6.4 1 1 1h4V14h6v7h4c.6 0 1-.4 1-1V10"/><path d="m12 2-9 7h18Z"/></svg> <?= htmlspecialchars($s['nama_kelas']) ?>
                    </span>
                  </div>
                </div>
                <a href="<?= BASEURL ?>/admin/bayarSiswa/<?= $s['id_siswa'] ?>" 
                   class="px-4 py-2.5 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white rounded-lg font-medium shadow-md transition-all text-sm flex items-center gap-2">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="2" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                  Bayar
                </a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Desktop: Table -->
        <div class="overflow-x-auto hidden lg:block rounded-lg border border-gray-200">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="bg-gradient-to-r from-gray-50 to-gray-100">
                <th class="text-left p-4 font-semibold text-gray-700 w-12">No</th>
                <th class="text-left p-4 font-semibold text-gray-700">Nama Siswa</th>
                <th class="text-left p-4 font-semibold text-gray-700">NISN</th>
                <th class="text-left p-4 font-semibold text-gray-700">Kelas</th>
                <th class="text-center p-4 font-semibold text-gray-700 w-32">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <?php $no = 1; foreach ($data['siswa_list'] as $s): ?>
                <tr class="hover:bg-green-50 transition-colors">
                  <td class="p-4 text-gray-500"><?= $no++ ?></td>
                  <td class="p-4">
                    <div class="flex items-center gap-2">
                      <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-600"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                      </div>
                      <span class="font-medium text-gray-800"><?= htmlspecialchars($s['nama_siswa']) ?></span>
                    </div>
                  </td>
                  <td class="p-4 text-gray-600"><?= htmlspecialchars($s['nisn']) ?></td>
                  <td class="p-4">
                    <span class="inline-flex items-center px-2.5 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">
                      <?= htmlspecialchars($s['nama_kelas']) ?>
                    </span>
                  </td>
                  <td class="p-4 text-center">
                    <a href="<?= BASEURL ?>/admin/bayarSiswa/<?= $s['id_siswa'] ?>" 
                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white rounded-lg text-sm font-medium shadow-md transition-all">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="2" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                      Bayar
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <!-- Empty State -->
    <div class="bg-white rounded-xl shadow-lg p-12 border border-gray-200 text-center">
      <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-green-500"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
      </div>
      <h3 class="text-lg font-semibold text-gray-700 mb-2">Cari Siswa</h3>
      <p class="text-gray-500 max-w-md mx-auto">Pilih kelas dan/atau masukkan nama/NISN siswa untuk memulai pembayaran tagihan.</p>
    </div>
  <?php endif; ?>
</div>
