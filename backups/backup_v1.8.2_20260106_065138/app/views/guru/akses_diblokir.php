<main class="flex-1 overflow-x-hidden overflow-y-auto bg-secondary-50 p-4 md:p-6">
    <!-- Header -->
    <div class="mb-4">
        <div class="bg-white shadow-sm rounded-xl p-4 md:p-5">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                    <h4 class="text-lg md:text-xl font-bold text-slate-800 mb-1"><?= $data['judul'] ?? 'Akses Dibatasi' ?></h4>
                    <p class="text-slate-500 text-sm">
                        Fitur ini memerlukan RPP yang sudah disetujui
                    </p>
                </div>
            </div>
        </div>
    </div>

    <?php Flasher::flash(); ?>

    <!-- Peringatan Blokir -->
    <div class="bg-red-50 border border-red-200 rounded-xl p-6 mb-6">
        <div class="flex gap-4">
            <div class="shrink-0">
                <div class="p-3 bg-red-100 rounded-xl">
                    <i data-lucide="shield-alert" class="w-8 h-8 text-red-600"></i>
                </div>
            </div>
            <div class="flex-1">
                <h5 class="text-lg font-bold text-red-800 mb-2">Akses Fitur Dibatasi</h5>
                <p class="text-red-700 mb-4"><?= htmlspecialchars($data['blokir_akses']['pesan'] ?? 'Anda belum dapat mengakses fitur ini.') ?></p>
                
                <!-- Fitur yang diblokir -->
                <div class="flex flex-wrap gap-2 mb-4">
                    <?php if (!empty($data['blokir_akses']['blokir_absensi'])): ?>
                    <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-100 text-red-700 text-sm font-medium rounded-lg">
                        <i data-lucide="lock" class="w-4 h-4"></i> Absensi
                    </span>
                    <?php endif; ?>
                    <?php if (!empty($data['blokir_akses']['blokir_jurnal'])): ?>
                    <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-100 text-red-700 text-sm font-medium rounded-lg">
                        <i data-lucide="lock" class="w-4 h-4"></i> Jurnal
                    </span>
                    <?php endif; ?>
                    <?php if (!empty($data['blokir_akses']['blokir_nilai'])): ?>
                    <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-100 text-red-700 text-sm font-medium rounded-lg">
                        <i data-lucide="lock" class="w-4 h-4"></i> Input Nilai
                    </span>
                    <?php endif; ?>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="<?= BASEURL ?>/guru/rpp" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition-colors">
                        <i data-lucide="file-plus" class="w-4 h-4"></i>
                        Buat RPP Sekarang
                    </a>
                    <a href="<?= BASEURL ?>/guru/dashboard" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-lg transition-colors">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistik RPP -->
    <?php 
    $statsRPP = getStatistikRPPGuru();
    ?>
    <div class="bg-white shadow-sm rounded-xl p-5 mb-6">
        <h5 class="font-bold text-slate-700 mb-4">
            <i data-lucide="file-text" class="w-4 h-4 inline-block mr-1"></i>
            Status RPP Anda Semester Ini
        </h5>
        
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <!-- Total RPP -->
            <div class="p-4 bg-slate-50 rounded-xl text-center">
                <div class="text-2xl font-bold text-slate-800"><?= (int)($statsRPP['total_rpp'] ?? 0) ?></div>
                <div class="text-xs text-slate-500 font-medium">Total RPP</div>
            </div>
            
            <!-- Draft -->
            <div class="p-4 bg-slate-50 rounded-xl text-center">
                <div class="text-2xl font-bold text-slate-500"><?= (int)($statsRPP['draft'] ?? 0) ?></div>
                <div class="text-xs text-slate-500 font-medium">Draft</div>
            </div>
            
            <!-- Submitted -->
            <div class="p-4 bg-blue-50 rounded-xl text-center">
                <div class="text-2xl font-bold text-blue-600"><?= (int)($statsRPP['submitted'] ?? 0) ?></div>
                <div class="text-xs text-blue-600 font-medium">Diajukan</div>
            </div>
            
            <!-- Approved -->
            <div class="p-4 bg-green-50 rounded-xl text-center">
                <div class="text-2xl font-bold text-green-600"><?= (int)($statsRPP['approved'] ?? 0) ?></div>
                <div class="text-xs text-green-600 font-medium">Disetujui</div>
            </div>
            
            <!-- Revision -->
            <div class="p-4 bg-amber-50 rounded-xl text-center">
                <div class="text-2xl font-bold text-amber-600"><?= (int)($statsRPP['revision'] ?? 0) ?></div>
                <div class="text-xs text-amber-600 font-medium">Perlu Revisi</div>
            </div>
        </div>
        
        <?php if (($statsRPP['approved'] ?? 0) == 0): ?>
        <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-lg">
            <p class="text-sm text-amber-700">
                <i data-lucide="alert-triangle" class="w-4 h-4 inline mr-1"></i>
                <strong>Perhatian:</strong> Anda belum memiliki RPP yang disetujui untuk semester ini. 
                Silakan buat dan ajukan RPP untuk mendapatkan akses ke fitur Absensi, Jurnal, dan Input Nilai.
            </p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Panduan -->
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-5">
        <h5 class="font-bold text-blue-800 mb-3">
            <i data-lucide="help-circle" class="w-4 h-4 inline-block mr-1"></i>
            Langkah untuk Membuka Akses
        </h5>
        <ol class="text-sm text-blue-700 space-y-2 list-decimal list-inside">
            <li>Buka menu <strong>RPP</strong> dari sidebar atau klik tombol "Buat RPP Sekarang" di atas</li>
            <li>Buat RPP baru untuk mata pelajaran dan kelas yang Anda ampu</li>
            <li>Lengkapi semua field yang diperlukan</li>
            <li>Klik <strong>Ajukan</strong> untuk mengirim RPP ke Kepala Madrasah</li>
            <li>Tunggu persetujuan dari Kepala Madrasah</li>
            <li>Setelah disetujui, Anda dapat mengakses semua fitur</li>
        </ol>
    </div>
</main>

<script>
// Initialize Lucide icons
if (typeof lucide !== 'undefined') {
    lucide.createIcons();
}
</script>
