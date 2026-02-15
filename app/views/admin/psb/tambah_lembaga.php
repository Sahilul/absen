<?php
// File: app/views/admin/psb/tambah_lembaga.php
// Form Tambah Lembaga PSB
?>
<?php $this->view('templates/header', $data); ?>

<div class="animate-fade-in">
    <!-- Page Header -->
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= BASEURL; ?>/psb/lembaga" class="p-2 hover:bg-secondary-100 rounded-lg transition-colors">
            <i data-lucide="arrow-left" class="w-5 h-5 text-secondary-500"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-secondary-800">Tambah Lembaga</h1>
            <p class="text-secondary-500 mt-1">Tambahkan unit pendidikan baru</p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-xl shadow-sm border max-w-2xl">
        <div class="p-6 border-b border-secondary-200">
            <div class="flex items-center gap-3">
                <div class="bg-primary-100 p-2 rounded-lg">
                    <i data-lucide="building" class="w-5 h-5 text-primary-600"></i>
                </div>
                <h2 class="text-lg font-semibold text-secondary-800">Data Lembaga</h2>
            </div>
        </div>

        <form action="<?= BASEURL; ?>/psb/prosesTambahLembaga" method="POST" enctype="multipart/form-data"
            class="p-6 space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-secondary-700 mb-2">Kode Lembaga <span
                            class="text-danger-500">*</span></label>
                    <input type="text" name="kode_lembaga" required maxlength="20" placeholder="Contoh: SD"
                        class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <p class="text-xs text-secondary-400 mt-1">Akan dikonversi ke huruf kapital</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-secondary-700 mb-2">Jenjang <span
                            class="text-danger-500">*</span></label>
                    <select name="jenjang" required
                        class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">-- Pilih Jenjang --</option>
                        <option value="TK">TK</option>
                        <option value="SD">SD</option>
                        <option value="SMP">SMP</option>
                        <option value="SMA">SMA</option>
                        <option value="SMK">SMK</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-secondary-700 mb-2">Nama Lembaga <span
                        class="text-danger-500">*</span></label>
                <input type="text" name="nama_lembaga" required placeholder="Contoh: SD Sabilillah"
                    class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>

            <div>
                <label class="block text-sm font-semibold text-secondary-700 mb-2">Alamat</label>
                <textarea name="alamat" rows="2" placeholder="Alamat lengkap lembaga"
                    class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-secondary-700 mb-2">Kuota Default</label>
                    <input type="number" name="kuota_default" min="0" value="0"
                        class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-secondary-700 mb-2">Urutan</label>
                    <input type="number" name="urutan" min="0" value="0"
                        class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>

            <!-- KOP SURAT SECTION -->
            <div class="border-t pt-5 mt-5">
                <div class="flex items-center gap-3 mb-4">
                    <div class="bg-blue-100 p-2 rounded-lg">
                        <i data-lucide="file-text" class="w-5 h-5 text-blue-600"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-secondary-800">Kop Surat</h3>
                        <p class="text-sm text-secondary-500">Gambar kop surat lengkap untuk formulir PDF</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-secondary-700 mb-2">Gambar Kop Surat
                            (Utama)</label>
                        <input type="file" name="kop_gambar" accept="image/*"
                            class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <p class="text-xs text-secondary-400 mt-1">Upload gambar kop surat lengkap (lebar penuh, rasio
                            sekitar 6:1). Format: PNG/JPG</p>
                        <p class="text-xs text-secondary-400">Contoh: gambar yang berisi logo + nama sekolah + alamat +
                            kontak dalam satu file</p>
                    </div>

                    <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                        <p class="text-sm text-blue-800 font-medium mb-2">💡 Tips:</p>
                        <ul class="text-xs text-blue-700 list-disc list-inside space-y-1">
                            <li>Gunakan gambar dengan lebar sekitar 800-1200px</li>
                            <li>Tinggi sekitar 120-200px (rasio landscape)</li>
                            <li>Background transparan (PNG) atau putih</li>
                            <li>Pastikan teks terbaca jelas saat dicetak</li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- END KOP SURAT -->

            <div class="flex items-center gap-2">
                <input type="checkbox" name="aktif" id="aktif" checked
                    class="w-4 h-4 text-primary-600 border-secondary-300 rounded focus:ring-primary-500">
                <label for="aktif" class="text-sm font-medium text-secondary-700">Aktif (tampil di PSB)</label>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t">
                <a href="<?= BASEURL; ?>/psb/lembaga" class="btn-secondary px-6 py-2.5">Batal</a>
                <button type="submit" class="btn-primary px-6 py-2.5 flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Simpan Lembaga
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>

<?php $this->view('templates/footer'); ?>