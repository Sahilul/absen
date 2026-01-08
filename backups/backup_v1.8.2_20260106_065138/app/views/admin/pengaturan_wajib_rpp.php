<main class="flex-1 overflow-x-hidden overflow-y-auto bg-secondary-50 p-4 md:p-6">
    <!-- Header -->
    <div class="mb-4">
        <div class="bg-white shadow-sm rounded-xl p-4 md:p-5">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                    <h4 class="text-lg md:text-xl font-bold text-slate-800 mb-1">Pengaturan Wajib RPP</h4>
                    <p class="text-slate-500 text-sm">
                        Kelola pembatasan akses fitur berdasarkan status RPP guru
                    </p>
                </div>
            </div>
        </div>
    </div>

    <?php Flasher::flash(); ?>

    <!-- Form Pengaturan -->
    <div class="bg-white shadow-sm rounded-xl p-5 md:p-6">
        <form action="<?= BASEURL ?>/admin/simpanPengaturanWajibRPP" method="POST">
            
            <!-- Toggle Utama -->
            <div class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-blue-100 rounded-xl">
                            <i data-lucide="shield-check" class="w-6 h-6 text-blue-600"></i>
                        </div>
                        <div>
                            <h5 class="font-bold text-slate-800">Aktifkan Wajib RPP Disetujui</h5>
                            <p class="text-sm text-slate-500">Guru wajib memiliki RPP yang disetujui untuk mengakses fitur tertentu</p>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="wajib_rpp_disetujui" value="1" 
                               class="sr-only peer" id="toggle-wajib"
                               <?= !empty($data['pengaturan']['wajib_rpp_disetujui']) ? 'checked' : '' ?>>
                        <div class="w-14 h-7 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>
            </div>

            <!-- Pengaturan Fitur yang Diblokir -->
            <div id="blokir-options" class="<?= empty($data['pengaturan']['wajib_rpp_disetujui']) ? 'opacity-50 pointer-events-none' : '' ?>">
                <h5 class="font-bold text-slate-700 mb-4">
                    <i data-lucide="lock" class="w-4 h-4 inline-block mr-1"></i>
                    Fitur yang Diblokir (jika RPP belum disetujui)
                </h5>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <!-- Blokir Absensi -->
                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i data-lucide="user-check" class="w-5 h-5 text-green-600"></i>
                                <span class="font-semibold text-slate-700">Absensi</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="blokir_absensi" value="1" 
                                       class="sr-only peer"
                                       <?= !empty($data['pengaturan']['blokir_absensi']) ? 'checked' : '' ?>>
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-500"></div>
                            </label>
                        </div>
                        <p class="text-xs text-slate-500 mt-2">Blokir akses input absensi siswa</p>
                    </div>

                    <!-- Blokir Jurnal -->
                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i data-lucide="book-open" class="w-5 h-5 text-blue-600"></i>
                                <span class="font-semibold text-slate-700">Jurnal</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="blokir_jurnal" value="1" 
                                       class="sr-only peer"
                                       <?= !empty($data['pengaturan']['blokir_jurnal']) ? 'checked' : '' ?>>
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-500"></div>
                            </label>
                        </div>
                        <p class="text-xs text-slate-500 mt-2">Blokir akses input jurnal mengajar</p>
                    </div>

                    <!-- Blokir Nilai -->
                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i data-lucide="file-text" class="w-5 h-5 text-purple-600"></i>
                                <span class="font-semibold text-slate-700">Input Nilai</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="blokir_nilai" value="1" 
                                       class="sr-only peer"
                                       <?= !empty($data['pengaturan']['blokir_nilai']) ? 'checked' : '' ?>>
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-500"></div>
                            </label>
                        </div>
                        <p class="text-xs text-slate-500 mt-2">Blokir akses input nilai siswa</p>
                    </div>
                </div>

                <!-- Pesan Blokir -->
                <div class="mb-6">
                    <label for="pesan_blokir" class="block text-sm font-semibold text-slate-700 mb-2">
                        <i data-lucide="message-square" class="w-4 h-4 inline-block mr-1"></i>
                        Pesan yang Ditampilkan
                    </label>
                    <textarea 
                        id="pesan_blokir" 
                        name="pesan_blokir" 
                        rows="3"
                        class="w-full border border-slate-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-primary-200 focus:border-primary-400 transition-all"
                        placeholder="Pesan yang akan ditampilkan kepada guru saat fitur diblokir"
                    ><?= htmlspecialchars($data['pengaturan']['pesan_blokir'] ?? 'Anda belum dapat mengakses fitur ini karena RPP belum dibuat atau belum disetujui oleh Kepala Madrasah. Silakan buat dan ajukan RPP terlebih dahulu.') ?></textarea>
                    <p class="text-xs text-slate-500 mt-1">Pesan ini akan muncul di dashboard guru dan saat mencoba mengakses fitur yang diblokir</p>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-slate-200">
                <button 
                    type="submit" 
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm"
                >
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Simpan Pengaturan
                </button>
                <a 
                    href="<?= BASEURL ?>/admin/dashboard" 
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-lg transition-colors"
                >
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    Kembali
                </a>
            </div>
        </form>
    </div>

    <!-- Info Box -->
    <div class="mt-5 bg-amber-50 border border-amber-200 rounded-xl p-4">
        <div class="flex gap-3">
            <div class="shrink-0">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600"></i>
            </div>
            <div class="flex-1">
                <h6 class="font-semibold text-amber-900 text-sm mb-1">Informasi Penting</h6>
                <ul class="text-xs text-amber-700 space-y-1 list-disc list-inside">
                    <li><strong>Wajib RPP Disetujui:</strong> Jika diaktifkan, guru harus memiliki minimal 1 RPP dengan status "Disetujui" untuk semester aktif</li>
                    <li><strong>Fitur yang Diblokir:</strong> Pilih fitur mana saja yang akan diblokir jika guru belum punya RPP disetujui</li>
                    <li>Guru tetap bisa membuat RPP baru meskipun fitur lain diblokir</li>
                    <li>Kepala Madrasah/Admin tidak terpengaruh oleh pengaturan ini</li>
                    <li>Perubahan akan langsung berlaku setelah disimpan</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Preview Status -->
    <div class="mt-5 bg-white shadow-sm rounded-xl p-5">
        <h5 class="font-bold text-slate-700 mb-4">
            <i data-lucide="eye" class="w-4 h-4 inline-block mr-1"></i>
            Preview Tampilan di Dashboard Guru
        </h5>
        
        <div class="p-4 bg-red-50 border border-red-200 rounded-xl">
            <div class="flex gap-3">
                <div class="shrink-0">
                    <div class="p-2 bg-red-100 rounded-lg">
                        <i data-lucide="alert-circle" class="w-5 h-5 text-red-600"></i>
                    </div>
                </div>
                <div class="flex-1">
                    <h6 class="font-bold text-red-800 mb-1">Akses Fitur Dibatasi</h6>
                    <p class="text-sm text-red-700 mb-3"><?= htmlspecialchars($data['pengaturan']['pesan_blokir'] ?? 'Anda belum dapat mengakses fitur ini karena RPP belum dibuat atau belum disetujui oleh Kepala Madrasah.') ?></p>
                    <div class="flex flex-wrap gap-2">
                        <?php if (!empty($data['pengaturan']['blokir_absensi'])): ?>
                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-red-100 text-red-700 text-xs font-medium rounded-lg">
                            <i data-lucide="lock" class="w-3 h-3"></i> Absensi
                        </span>
                        <?php endif; ?>
                        <?php if (!empty($data['pengaturan']['blokir_jurnal'])): ?>
                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-red-100 text-red-700 text-xs font-medium rounded-lg">
                            <i data-lucide="lock" class="w-3 h-3"></i> Jurnal
                        </span>
                        <?php endif; ?>
                        <?php if (!empty($data['pengaturan']['blokir_nilai'])): ?>
                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-red-100 text-red-700 text-xs font-medium rounded-lg">
                            <i data-lucide="lock" class="w-3 h-3"></i> Input Nilai
                        </span>
                        <?php endif; ?>
                    </div>
                    <a href="#" class="inline-flex items-center gap-1 mt-3 text-sm font-semibold text-red-700 hover:text-red-800">
                        <i data-lucide="file-plus" class="w-4 h-4"></i>
                        Buat RPP Sekarang
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
// Initialize Lucide icons
if (typeof lucide !== 'undefined') {
    lucide.createIcons();
}

// Toggle options visibility
document.getElementById('toggle-wajib').addEventListener('change', function() {
    const options = document.getElementById('blokir-options');
    if (this.checked) {
        options.classList.remove('opacity-50', 'pointer-events-none');
    } else {
        options.classList.add('opacity-50', 'pointer-events-none');
    }
});
</script>
