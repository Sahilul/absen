<?php
// File: app/views/wali_kelas/dokumen_siswa.php
// Using shared component like PSB

$siswa = $data['siswa'] ?? [];
$dokumen = $data['dokumen'] ?? [];
$backUrl = $data['back_url'] ?? BASEURL . '/waliKelas/daftarSiswa';

// Load document config from model
require_once APPROOT . '/app/models/DokumenConfig_model.php';
$dokumenConfigModel = new DokumenConfig_model();
$dokumenConfig = $dokumenConfigModel->getAllDokumen();

// Index uploaded docs by jenis_dokumen
$uploadedDocs = [];
foreach ($dokumen as $doc) {
    $uploadedDocs[$doc['jenis_dokumen']] = $doc;
}

// Variables for shared component
$nisn = $siswa['nisn'] ?? '';
$namaSiswa = $siswa['nama_siswa'] ?? '';
$idRef = $siswa['id_siswa'] ?? 0;
$context = 'wali_kelas';
$readOnly = false;
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="flex items-center gap-4 mb-6">
        <a href="<?= $backUrl; ?>"
            class="p-2 hover:bg-white rounded-lg transition-colors text-secondary-500 hover:text-primary-600">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-secondary-800 flex items-center gap-2">
                <i data-lucide="folder-open" class="w-6 h-6 text-amber-600"></i>
                Dokumen Siswa
            </h1>
            <p class="text-secondary-600 mt-1">
                <?= htmlspecialchars($siswa['nama_siswa'] ?? ''); ?>
                <span class="text-secondary-400">• NISN: <?= $siswa['nisn'] ?? '-'; ?></span>
            </p>
        </div>
    </div>

    <?php if (class_exists('Flasher')) {
        Flasher::flash();
    } ?>

    <!-- Document Upload Section -->
    <div class="glass-effect rounded-2xl border border-white/20 p-6">
        <?php include APPROOT . '/app/views/components/dokumen_upload.php'; ?>
    </div>

    <!-- Info Card -->
    <div
        class="mt-6 bg-gradient-to-br from-amber-50/50 to-orange-50/50 glass-effect rounded-2xl border border-amber-200/50 p-4">
        <div class="flex items-start gap-3">
            <i data-lucide="info" class="w-5 h-5 text-amber-600 mt-0.5"></i>
            <div>
                <h4 class="font-semibold text-amber-800">Informasi</h4>
                <ul class="text-xs text-amber-700 mt-1 space-y-1">
                    <li>• Dokumen akan otomatis tersimpan setelah dipilih</li>
                    <li>• Format yang didukung: JPG, PNG, PDF (maks 2MB)</li>
                    <li>• Dokumen yang diupload ulang akan menggantikan dokumen lama</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>