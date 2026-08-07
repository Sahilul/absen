<?php
// File: app/views/guru/nilai/nilai_tengah_semester.php
?>
<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gradient-to-br from-secondary-50 to-secondary-100 p-4 sm:p-6">
  <!-- Breadcrumb -->
  <div class="mb-4 sm:mb-6">
    <nav class="flex items-center space-x-2 text-sm text-secondary-600">
      <a href="<?= BASEURL; ?>/guru/dashboard" class="hover:text-primary-600 transition-colors">Dashboard</a>
      <i data-lucide="chevron-right" class="w-4 h-4"></i>
      <a href="<?= BASEURL; ?>/nilai" class="hover:text-primary-600 transition-colors">Nilai</a>
      <i data-lucide="chevron-right" class="w-4 h-4"></i>
      <span class="text-secondary-800 font-medium">Tengah Semester</span>
    </nav>
  </div>

  <!-- Header -->
  <div class="mb-8">
    <div class="flex items-center justify-between">
      <div class="flex items-center space-x-4">
        <a href="<?= BASEURL; ?>/nilai" class="p-2 rounded-xl text-secondary-500 hover:text-primary-600 hover:bg-white/50 transition-all duration-200">
          <i data-lucide="arrow-left" class="w-6 h-6"></i>
        </a>
        <div>
          <h2 class="text-3xl font-bold text-secondary-800 flex items-center">
            <i data-lucide="book" class="w-8 h-8 mr-3 text-success-500"></i>
            Input Nilai Tengah Semester
          </h2>
          <p class="text-secondary-600 mt-1">Catat nilai ujian tengah semester untuk pertemuan ini</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Form Container -->
  <div class="max-w-4xl mx-auto">
    <div class="glass-effect rounded-2xl border border-white/20 shadow-xl overflow-hidden animate-fade-in">
      <!-- Form Header -->
  <div class="bg-gradient-to-r from-success-500 to-emerald-600 p-6">
        <div class="flex items-center justify-between text-white">
          <div>
            <h3 class="text-xl font-bold mb-2 drop-shadow-sm">Nilai Tengah Semester (STS)</h3>
            <p class="text-white/95 text-sm font-medium drop-shadow-sm">Mata Pelajaran: <?= htmlspecialchars($data['penugasan']['nama_mapel']); ?> | Kelas: <?= htmlspecialchars($data['penugasan']['nama_kelas']); ?></p>
          </div>
          <div class="flex items-center gap-3">
            <a href="<?= BASEURL; ?>/nilai/downloadNilaiSTSPDF?id_penugasan=<?= $data['penugasan']['id_penugasan']; ?>" 
               class="bg-white/20 hover:bg-white/30 backdrop-blur-sm p-3 rounded-xl transition-colors" title="Download PDF">
              <i data-lucide="download" class="w-6 h-6 text-white"></i>
            </a>
            <div class="bg-white/20 backdrop-blur-sm p-3 rounded-xl">
              <i data-lucide="clipboard-list" class="w-6 h-6"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Form Body -->
      <div class="p-8">
        <form action="<?= BASEURL; ?>/nilai/prosesSimpanTengahSemester" method="POST" id="nilaiForm">
          <input type="hidden" name="id_penugasan" value="<?= $data['penugasan']['id_penugasan']; ?>">

          <!-- Quick Fill Section -->
          <div class="glass-effect rounded-xl p-4 mb-6 border border-white/20 bg-gradient-to-r from-success-50 to-primary-50">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
              <div class="flex items-center gap-2 text-success-700">
                <i data-lucide="zap" class="w-5 h-5"></i>
                <span class="font-semibold">Isi Cepat:</span>
              </div>
              <div class="flex items-center gap-3 flex-1 flex-wrap">
                <input type="number" id="fillAllValue" min="0" max="100" step="1" 
                       class="input-modern w-32" placeholder="Nilai">
                <button type="button" onclick="fillAllNilai()" class="btn-success text-sm py-2 px-4">
                  <i data-lucide="copy" class="w-4 h-4 mr-2"></i> Isi Semua
                </button>
                <button type="button" onclick="clearAllNilai()" class="btn-secondary text-sm py-2 px-4">
                  <i data-lucide="eraser" class="w-4 h-4 mr-2"></i> Hapus Semua
                </button>
                <button type="button" onclick="openImportModal()" class="btn-primary text-sm py-2 px-4">
                  <i data-lucide="file-spreadsheet" class="w-4 h-4 mr-2"></i> Import Excel CBT
                </button>
              </div>
            </div>
          </div>

          <!-- Students List - Table Format -->
          <div class="glass-effect rounded-xl border border-white/20 shadow-lg overflow-hidden animate-slide-up">
            <div class="px-6 py-4 border-b border-secondary-200 bg-gradient-to-r from-success-500 to-emerald-600">
              <h3 class="text-lg font-bold text-white flex items-center drop-shadow-sm">
                <i data-lucide="users" class="w-5 h-5 mr-2"></i>
                Daftar Siswa (<?= count($data['filtered_siswa']); ?> siswa)
              </h3>
            </div>
            
            <div class="overflow-x-auto">
              <table class="w-full table-nilai responsive-table">
                <thead class="bg-secondary-50 border-b border-secondary-200">
                  <tr>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-secondary-700 w-16">No</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-secondary-700 w-32">NISN</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-secondary-700">Nama Siswa</th>
                    <th class="px-4 py-3 text-center text-sm font-semibold text-secondary-700 w-32">Nilai STS</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-secondary-100">
                  <?php 
                  // Buat array nilai untuk akses cepat
                  $nilai_siswa = [];
                  if (!empty($data['nilai_tengah_semester'])) {
                    foreach ($data['nilai_tengah_semester'] as $nilai) {
                      $nilai_siswa[$nilai['id_siswa']] = $nilai;
                    }
                  }
                  
                  foreach ($data['filtered_siswa'] as $index => $siswa): 
                    // Cek apakah siswa ini sudah punya nilai
                    $nilai_existing = $nilai_siswa[$siswa['id_siswa']] ?? null;
                    $nilai_value = $nilai_existing ? $nilai_existing['nilai'] : '';
                  ?>
                    <tr class="transition-all duration-200">
                      <td data-label="No" class="px-4 py-4 text-sm text-secondary-600"><?= $index + 1; ?></td>
                      <td data-label="NISN" class="px-4 py-4 text-sm text-secondary-600 font-mono"><?= htmlspecialchars($siswa['nisn']); ?></td>
                      <td data-label="Nama Siswa" class="px-4 py-4">
                        <div class="flex items-center space-x-3">
                          <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-gradient-to-r from-success-400 to-primary-400 rounded-lg flex items-center justify-center text-white font-bold text-sm">
                              <?= substr(htmlspecialchars($siswa['nama_siswa']), 0, 1); ?>
                            </div>
                          </div>
                          <div class="text-sm font-semibold text-secondary-800">
                            <?= htmlspecialchars($siswa['nama_siswa']); ?>
                          </div>
                        </div>
                      </td>
                      <td data-label="Nilai STS" class="px-4 py-4 text-center">
                        <div class="relative inline-block">
                          <input type="number" 
                                 id="nilai_<?= $siswa['id_siswa']; ?>" 
                                 name="nilai[<?= $siswa['id_siswa']; ?>]" 
                                 value="<?= $nilai_value; ?>" 
                                 min="0" 
                                 max="100" 
                                 step="1" 
                                 inputmode="numeric" pattern="[0-9]*"
                                 class="nilai-input input-modern w-full sm:w-24 text-center font-semibold <?= $nilai_value !== '' ? 'bg-success-50 border-success-300' : ''; ?>" 
                                 placeholder="0-100">
                          <?php if ($nilai_value !== ''): ?>
                            <span class="absolute right-2 top-1/2 -translate-y-1/2 text-success-600">
                              <i data-lucide="check-circle" class="w-4 h-4"></i>
                            </span>
                          <?php endif; ?>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="form-actions mt-8 flex flex-col sm:flex-row justify-between items-center gap-4">
            <button type="button" onclick="window.location.href='<?= BASEURL; ?>/guru'" class="btn-secondary w-full sm:w-auto order-2 sm:order-1">
              <i data-lucide="arrow-left" class="w-5 h-5 mr-2"></i> Kembali ke Dashboard
            </button>
            <button type="submit" class="btn-primary w-full sm:w-auto order-1 sm:order-2 shadow-xl hover:shadow-2xl">
              <i data-lucide="save" class="w-5 h-5 mr-2"></i> Simpan Semua Nilai
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    // Import Excel Modal
    let excelDataGlobal = null;
    let excelInputListener = false;

    function initExcelListener() {
      if (excelInputListener) return;
      
      const excelInput = document.getElementById('excelFile');
      if (!excelInput) {
        console.error('Element #excelFile tidak ditemukan');
        return;
      }
      
      console.log('Memasang event listener untuk #excelFile');
      
      excelInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        const btnProcess = document.getElementById('btnProcessImport');
        
        if (!file) {
          btnProcess.disabled = true;
          excelDataGlobal = null;
          return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
          try {
            const data = new Uint8Array(e.target.result);
            const workbook = XLSX.read(data, { type: 'array' });
            
            const firstSheetName = workbook.SheetNames[0];
            const worksheet = workbook.Sheets[firstSheetName];

            const sheetArray = XLSX.utils.sheet_to_json(worksheet, {
              header: 1
            });

            if (!sheetArray || sheetArray.length === 0) {
              alert('Sheet Excel kosong.');
              btnProcess.disabled = true;
              excelDataGlobal = null;
              return;
            }

            const headerRowIndex = 2;
            const headerRow = sheetArray[headerRowIndex] || [];

            const normalizeHeader = (h) => String(h || '').toUpperCase().trim();

            const idxNomorUjian = headerRow.findIndex(h => normalizeHeader(h).includes('NOMOR UJIAN'));
            const idxHasilAkhir = headerRow.findIndex(h => {
              const x = normalizeHeader(h);
              return x.includes('HASIL AKHIR') || x.includes('NILAI AKHIR') || x === 'HASIL';
            });

            if (idxNomorUjian === -1 || idxHasilAkhir === -1) {
              alert('Kolom "NOMOR UJIAN" atau "HASIL AKHIR" tidak ditemukan di file CBT.');
              btnProcess.disabled = true;
              excelDataGlobal = null;
              return;
            }

            const dataRows = sheetArray.slice(headerRowIndex + 1);

            excelDataGlobal = {
              rows: dataRows,
              idxNomorUjian,
              idxHasilAkhir
            };

            btnProcess.disabled = false;
            
          } catch (error) {
            alert('Error membaca file Excel: ' + error.message);
            btnProcess.disabled = true;
            excelDataGlobal = null;
          }
        };
        reader.readAsArrayBuffer(file);
      });
      
      excelInputListener = true;
      console.log('Event listener sudah terpasang');
    }

    function openImportModal() {
      document.getElementById('importModal').classList.remove('hidden');
      excelDataGlobal = null;
      document.getElementById('btnProcessImport').disabled = true;
      initExcelListener();
    }

    function closeImportModal() {
      document.getElementById('importModal').classList.add('hidden');
      document.getElementById('importForm').reset();
      excelDataGlobal = null;
      document.getElementById('btnProcessImport').disabled = true;
    }

    function processImport() {
      if (!excelDataGlobal || !excelDataGlobal.rows || excelDataGlobal.rows.length === 0) {
        alert('Tidak ada data untuk diimport');
        return;
      }

      const { rows, idxNomorUjian, idxHasilAkhir } = excelDataGlobal;

      const btnProcess = document.getElementById('btnProcessImport');
      const originalHTML = btnProcess.innerHTML;
      btnProcess.disabled = true;
      btnProcess.innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 mr-2 animate-spin"></i> Memproses...';
      lucide.createIcons();

      setTimeout(() => {
        processExcelData(rows, idxNomorUjian, idxHasilAkhir);

        btnProcess.innerHTML = originalHTML;
        btnProcess.disabled = false;
        lucide.createIcons();
      }, 100);
    }

    function processExcelData(data, idxNomorUjian, idxHasilAkhir) {
      let imported = 0;
      let skipped = 0;
      
      data.forEach(row => {
        if (!row) return;

        const nomorUjian = String(row[idxNomorUjian] || '').trim();
        const nilaiAkhir = row[idxHasilAkhir];
        
        if (!nomorUjian || !nilaiAkhir) return;
        
        const inputs = document.querySelectorAll('input[name^="nilai["]');
        let found = false;
        
        inputs.forEach(input => {
          const row = input.closest('tr');
          if (!row) return;
          
          const nisnCell = row.querySelector('td:nth-child(2)');
          if (nisnCell && nisnCell.textContent.trim() === nomorUjian) {
            const nilai = parseFloat(String(nilaiAkhir).replace(',', '.'));
            if (!isNaN(nilai) && nilai >= 0 && nilai <= 100) {
              input.value = Math.round(nilai);
              imported++;
              found = true;
            }
          }
        });
        
        if (!found && nilaiAkhir) {
          skipped++;
        }
      });
      
      closeImportModal();
      alert(`Import selesai!\n\nBerhasil: ${imported} siswa\nDilewati: ${skipped} siswa (NISN tidak ditemukan atau nilai tidak valid)`);
      
      if (imported > 0) {
        console.log('Auto-submit form untuk menyimpan nilai ke database');
        setTimeout(() => {
          const form = document.getElementById('nilaiForm');
          if (form) {
            form.submit();
          }
        }, 500);
      }
    }

    function downloadTemplate() {
      window.open('<?= BASEURL; ?>/nilai/downloadTemplateCBT?id_penugasan=<?= $data['penugasan']['id_penugasan']; ?>', '_blank');
    }

    // Fill all nilai with same value
    function fillAllNilai() {
      const value = document.getElementById('fillAllValue').value;
      if (!value || value < 0 || value > 100) {
        alert('Masukkan nilai antara 0-100');
        return;
      }
      
      const inputs = document.querySelectorAll('input[name^="nilai["]');
      inputs.forEach(input => {
        input.value = value;
      });
    }

    // Clear all nilai
    function clearAllNilai() {
      if (!confirm('Hapus semua nilai yang sudah diisi?')) return;
      
      const inputs = document.querySelectorAll('input[name^="nilai["]');
      inputs.forEach(input => {
        input.value = '';
      });
      document.getElementById('fillAllValue').value = '';
    }

    // Validate before submit
    document.getElementById('nilaiForm').addEventListener('submit', function(e) {
      const inputs = document.querySelectorAll('input[name^="nilai["]');
      let hasValue = false;
      
      inputs.forEach(input => {
        if (input.value) hasValue = true;
      });
      
      if (!hasValue) {
        e.preventDefault();
        alert('Minimal isi 1 nilai siswa!');
        return;
      }
    });
  </script>

  <style>
    /* Mobile responsive table for input nilai */
    @media (max-width: 640px) {
      .responsive-table thead { display: none; }
      .responsive-table, .responsive-table tbody, .responsive-table tr, .responsive-table td { display: block; width: 100%; }
      .responsive-table tr { margin-bottom: .75rem; border: 1px solid #e5e7eb; border-radius: .75rem; background: #ffffff; padding: .25rem .5rem; box-shadow: 0 6px 14px rgba(2,6,23,.06); }
      .responsive-table td { border: none; border-bottom: 1px solid #f1f5f9; padding: .6rem .75rem; display: flex; justify-content: space-between; align-items: center; }
      .responsive-table td:last-child { border-bottom: none; }
      .responsive-table td::before { content: attr(data-label); font-weight: 700; color: #64748b; margin-right: .75rem; }
      .responsive-table .nilai-input { max-width: 8.5rem; width: 100%; }
    }
  </style>
</main>

<!-- Import Excel Modal -->
<div id="importModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
  <div class="glass-effect rounded-2xl border border-white/20 shadow-2xl max-w-2xl w-full animate-scale-up">
    <!-- Modal Header -->
    <div class="bg-gradient-to-r from-success-500 to-emerald-600 p-6 rounded-t-2xl">
      <div class="flex items-center justify-between text-white">
        <div class="flex items-center gap-3">
          <div class="bg-white/20 backdrop-blur-sm p-2 rounded-xl">
            <i data-lucide="file-spreadsheet" class="w-6 h-6"></i>
          </div>
          <div>
            <h3 class="text-xl font-bold drop-shadow-sm">Import Hasil CBT dari Excel</h3>
            <p class="text-white/90 text-sm mt-1">Upload file Excel hasil CBT untuk mengisi nilai otomatis</p>
          </div>
        </div>
        <button type="button" onclick="closeImportModal()" class="p-2 hover:bg-white/10 rounded-lg transition-colors">
          <i data-lucide="x" class="w-5 h-5"></i>
        </button>
      </div>
    </div>

    <!-- Modal Body -->
    <div class="p-6">
      <form id="importForm" class="space-y-6">
        <!-- Info Box -->
        <div class="glass-effect rounded-xl p-4 border border-success-200 bg-gradient-to-br from-success-50 to-blue-50">
          <div class="flex items-start gap-3">
            <div class="bg-success-100 p-2 rounded-lg">
              <i data-lucide="info" class="w-5 h-5 text-success-600"></i>
            </div>
            <div class="flex-1">
              <h4 class="font-bold text-success-900 mb-2">Petunjuk Import:</h4>
              <ul class="text-sm text-success-800 space-y-1 list-disc list-inside">
                <li>File Excel harus sesuai format template hasil CBT</li>
                <li>Kolom <strong>NOMOR UJIAN</strong> harus berisi NISN siswa</li>
                <li>Kolom <strong>HASIL AKHIR</strong> akan diimport sebagai nilai STS</li>
                <li>Sistem akan mencocokkan NISN, jika tidak cocok akan dilewati</li>
                <li>Nilai yang tidak valid (di luar 0-100) akan dilewati</li>
              </ul>
            </div>
          </div>
        </div>

        <!-- File Upload -->
        <div>
          <label class="block text-sm font-semibold text-secondary-700 mb-2">
            <i data-lucide="upload" class="w-4 h-4 inline mr-1"></i>
            Pilih File Excel (.xlsx)
          </label>
          <input type="file" id="excelFile" accept=".xlsx,.xls" required
                 class="block w-full text-sm text-secondary-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-success-50 file:text-success-700 hover:file:bg-success-100 transition-colors cursor-pointer border border-secondary-300 rounded-lg">
          <p class="mt-2 text-xs text-secondary-500">
            <i data-lucide="alert-circle" class="w-3 h-3 inline mr-1"></i>
            Format file: Excel (.xlsx atau .xls), Maksimal 5MB
          </p>
        </div>

        <!-- Download Template -->
        <div class="glass-effect rounded-xl p-4 border border-secondary-200 bg-gradient-to-br from-secondary-50 to-slate-50">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="bg-secondary-100 p-2 rounded-lg">
                <i data-lucide="file-down" class="w-5 h-5 text-secondary-600"></i>
              </div>
              <div>
                <h4 class="font-semibold text-secondary-900">Butuh Template?</h4>
                <p class="text-sm text-secondary-600">Download template Excel untuk melihat format</p>
              </div>
            </div>
            <button type="button" onclick="downloadTemplate()" class="btn-secondary text-sm py-2 px-4">
              <i data-lucide="download" class="w-4 h-4 mr-2"></i>
              Download Template
            </button>
          </div>
        </div>
      </form>
    </div>

    <!-- Modal Footer -->
    <div class="p-6 bg-secondary-50 rounded-b-2xl border-t border-secondary-200 flex justify-end gap-3">
      <button type="button" onclick="closeImportModal()" class="btn-secondary py-2 px-6">
        <i data-lucide="x" class="w-4 h-4 mr-2"></i>
        Batal
      </button>
      <button type="button" id="btnProcessImport" onclick="processImport()" class="btn-success py-2 px-6" disabled>
        <i data-lucide="upload-cloud" class="w-4 h-4 mr-2"></i>
        Proses Import
      </button>
    </div>
  </div>
</div>

<!-- SheetJS Library for Excel Import -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<!-- Initialize Lucide icons for modal -->
<script>
  if (typeof lucide !== 'undefined') {
    lucide.createIcons();
  } else {
    document.addEventListener('DOMContentLoaded', function() {
      if (typeof lucide !== 'undefined') {
        lucide.createIcons();
      }
    });
  }
</script>