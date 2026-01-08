<?php
// Form Section: Dokumen - Using Shared Component
$p = $pendaftar;
$docs = $dokumen ?? [];

// Load document config from model
require_once APPROOT . '/app/models/DokumenConfig_model.php';
$dokumenConfigModel = new DokumenConfig_model();
$dokumenConfig = $dokumenConfigModel->getDokumenPSB();

// Index uploaded docs by jenis_dokumen
$uploadedDocs = [];
foreach ($docs as $doc) {
    $uploadedDocs[$doc['jenis_dokumen']] = $doc;
}

// Variables for shared component
$nisn = $p['nisn'] ?? '';
$namaSiswa = $p['nama_lengkap'] ?? '';
$idRef = $p['id_pendaftar'];
$context = 'psb';
$readOnly = false;

// Include shared component
include APPROOT . '/app/views/components/dokumen_upload.php';
?>