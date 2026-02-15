<?php
// Header dengan gambar kop - SAMA PERSIS DENGAN RAPOR
$kopHTML = '';
if (!empty($pengaturan['kop_rapor'])) {
    $kopPath = 'public/img/kop/' . $pengaturan['kop_rapor'];
    if (file_exists($kopPath)) {
        $imageData = base64_encode(file_get_contents($kopPath));
        $imageType = pathinfo($kopPath, PATHINFO_EXTENSION);
        $imageSrc = 'data:image/' . $imageType . ';base64,' . $imageData;
        $kopHTML = '<img src="' . $imageSrc . '" style="max-width: 100%; height: auto; max-height: 120px;">';
    }
}

// TTD Kepala Madrasah - SAMA PERSIS DENGAN RAPOR
$ttdKepalaHTML = '';
if (!empty($pengaturan['ttd_kepala_madrasah'])) {
    $ttdPath = 'public/img/ttd/' . $pengaturan['ttd_kepala_madrasah'];
    if (file_exists($ttdPath)) {
        $imageData = base64_encode(file_get_contents($ttdPath));
        $imageType = pathinfo($ttdPath, PATHINFO_EXTENSION);
        $imageSrc = 'data:image/' . $imageType . ';base64,' . $imageData;
        $ttdKepalaHTML = '<img src="' . $imageSrc . '" style="max-width: 120px; height: auto; max-height: 50px;">';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Surat Keterangan Siswa Aktif</title>
    <style>
        @page { margin: 10mm 15mm; }
        body { 
            font-family: "Times New Roman", Times, serif; 
            font-size: 12pt;
            line-height: 1.1;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 5px;
        }

        .title {
            text-align: center;
            margin: 10px 0;
            font-weight: bold;
            font-size: 13pt;
        }
        .nomor-surat {
            text-align: center;
            font-size: 11pt;
            margin-bottom: 15px;
        }
        .content {
            font-size: 12pt;
            margin: 8px 0;
        }
        .data-table {
            width: 100%;
            margin: 5px 0 5px 20px;
            font-size: 11pt;
        }
        .data-table td {
            padding: 1px 0;
            vertical-align: top;
        }
        .data-table td:first-child {
            width: 150px;
        }
        .data-table td:nth-child(2) {
            width: 15px;
        }
        .ttd-section {
            margin-top: 20px;
            text-align: right;
            font-size: 10pt;
        }
        .ttd-content {
            display: inline-block;
            text-align: center;
            min-width: 180px;
        }
        .ttd-content p {
            margin: 3px 0;
        }
        .ttd-img {
            margin: 5px auto;
            height: 50px;
        }
        .ttd-name {
            font-weight: bold;
            text-decoration: underline;
            margin-top: 5px;
        }
        .ttd-nip {
            font-size: 9pt;
            margin-top: 2px;
        }
        .qr-code {
            text-align: center;
            margin: 10px auto;
        }
        .qr-code img {
            width: 80px;
            height: 80px;
        }
        .footer-note {
            position: fixed;
            bottom: 5mm;
            left: 15mm;
            right: 15mm;
            text-align: center;
            font-size: 8pt;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 5px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header"><?= $kopHTML ?></div>
    
    <div class="title">SURAT KETERANGAN SISWA AKTIF</div>
    <div class="nomor-surat">Nomor: <?= htmlspecialchars($nomor_surat) ?></div>
    
    <div class="content">Yang bertanda tangan di bawah ini:</div>
    
    <table class="data-table">
        <tr>
            <td>Nama</td>
            <td>:</td>
            <td><?= htmlspecialchars($pengaturan['nama_kepsek'] ?? '-') ?></td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td>:</td>
            <td>Kepala <?= htmlspecialchars($pengaturan['nama_sekolah'] ?? 'Madrasah') ?></td>
        </tr>
    </table>
    
    <div class="content">Dengan ini menerangkan bahwa:</div>
    
    <table class="data-table">
        <tr>
            <td>Nama Siswa</td>
            <td>:</td>
            <td><strong><?= htmlspecialchars($siswa['nama_siswa']) ?></strong></td>
        </tr>
        <tr>
            <td>NISN</td>
            <td>:</td>
            <td><?= htmlspecialchars($siswa['nisn']) ?></td>
        </tr>
        <tr>
            <td>Tempat, Tgl Lahir</td>
            <td>:</td>
            <td><?= htmlspecialchars($tempat_tgl_lahir) ?></td>
        </tr>
        <tr>
            <td>Kelas</td>
            <td>:</td>
            <td><?= htmlspecialchars($kelas) ?></td>
        </tr>
        <tr>
            <td>Tahun Pelajaran</td>
            <td>:</td>
            <td><?= htmlspecialchars($tahun_pelajaran) ?></td>
        </tr>
    </table>
    
    <div class="content">
        Adalah benar siswa aktif di <?= htmlspecialchars($pengaturan['nama_sekolah'] ?? 'Madrasah/Sekolah') ?> sampai saat surat ini dibuat.
    </div>
    
    <div class="content">
        Demikian surat keterangan ini dibuat agar dapat dipergunakan sebagaimana mestinya.
    </div>
    
    <div class="ttd-section">
        <div class="ttd-content">
            <p><?= htmlspecialchars($tempat_cetak) ?>, <?= htmlspecialchars($tanggal_cetak) ?></p>
            <p><strong>Kepala Madrasah,</strong></p>
            
            <?php if (!empty($qr_code_data_url)): ?>
            <div class="qr-code">
                <img src="<?= $qr_code_data_url ?>" alt="QR Validasi">
            </div>
            <?php endif; ?>
            
            <p class="ttd-name"><?= htmlspecialchars($pengaturan['nama_kepsek'] ?? '________________') ?></p>
            <?php if (!empty($pengaturan['nip_kepsek'])): ?>
            <p class="ttd-nip">NIP: <?= htmlspecialchars($pengaturan['nip_kepsek']) ?></p>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="footer-note">
        Dokumen ini telah ditandatangani secara elektronik menggunakan kode QR dan sah tanpa memerlukan tanda tangan basah.<br>
        Untuk memvalidasi keaslian dokumen ini, silakan pindai (scan) kode QR di atas.
    </div>
</body>
</html>
