<?php
// File: app/views/admin/dokumen_siswa.php
// Using shared component like PSB

$siswa = $data['siswa'] ?? [];
$dokumen = $data['dokumen'] ?? [];

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
$context = 'admin';
$readOnly = false;
?>

<main
    class="flex-1 overflow-x-hidden overflow-y-auto bg-gradient-to-br from-slate-50 via-gray-50 to-slate-100 min-h-screen p-6">
    <div class="max-w-5xl mx-auto">
        <!-- Page Header -->
        <div class="flex items-center gap-4 mb-6">
            <a href="<?= BASEURL; ?>/admin/siswa"
                class="p-2 hover:bg-white rounded-lg transition-colors text-gray-500 hover:text-primary-600">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <i data-lucide="folder-open" class="w-7 h-7 text-amber-600"></i>
                    Dokumen Siswa
                </h2>
                <p class="text-gray-600 mt-1">
                    <?= htmlspecialchars($siswa['nama_siswa'] ?? ''); ?>
                    <span class="text-gray-400">• NISN: <?= $siswa['nisn'] ?? '-'; ?></span>
                </p>
            </div>
        </div>

        <?php if (class_exists('Flasher')) {
            Flasher::flash();
        } ?>

        <!-- Document Upload Section -->
        <div class="bg-white rounded-2xl shadow-sm border p-6">
            <?php include APPROOT . '/app/views/components/dokumen_upload.php'; ?>
        </div>

        <!-- Info Card -->
        <div class="mt-6 bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl border border-amber-200 p-4">
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
</main>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>