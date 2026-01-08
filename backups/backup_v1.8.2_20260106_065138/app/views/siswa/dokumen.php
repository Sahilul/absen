<?php
$siswa = $data['siswa'] ?? [];
$dokumen = $data['dokumen'] ?? [];

// Load document config from model
require_once APPROOT . '/app/models/DokumenConfig_model.php';
$dokumenConfigModel = new DokumenConfig_model();
$dokumenConfig = $dokumenConfigModel->getDokumenSiswa();

// Index uploaded docs by jenis_dokumen
$uploadedDocs = [];
foreach ($dokumen as $doc) {
    $uploadedDocs[$doc['jenis_dokumen']] = $doc;
}

// Variables for shared component
$nisn = $siswa['nisn'] ?? '';
$namaSiswa = $siswa['nama_lengkap'] ?? '';
$idRef = $_SESSION['id_ref'] ?? 0;
$context = 'siswa';
$readOnly = false;
?>

<main
    class="flex-1 overflow-x-hidden overflow-y-auto bg-gradient-to-br from-slate-50 via-gray-50 to-slate-100 min-h-screen">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <i data-lucide="folder-open" class="w-7 h-7 text-amber-600"></i>
                    Dokumen Saya
                </h2>
                <p class="text-gray-600 mt-1">Upload dan kelola dokumen pribadi Anda</p>
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
                    <p class="text-xs text-amber-700 mt-1">
                        Dokumen yang Anda upload akan tersimpan dan dapat diakses oleh admin sekolah untuk keperluan
                        verifikasi.
                    </p>
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