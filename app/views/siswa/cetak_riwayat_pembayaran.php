<?php
// Generate kop HTML from uploaded image
$kopHTML = '';
if (!empty($data['pengaturan']['kop_rapor'])) {
    $kopPath = 'public/img/kop/' . $data['pengaturan']['kop_rapor'];
    if (file_exists($kopPath)) {
        $imageData = base64_encode(file_get_contents($kopPath));
        $imageType = pathinfo($kopPath, PATHINFO_EXTENSION);
        $imageSrc = 'data:image/' . $imageType . ';base64,' . $imageData;
        $kopHTML = '<img src="' . $imageSrc . '" style="max-width: 100%; height: auto; max-height: 110px;">';
    }
}
// Helper untuk format tanggal Indonesia
function formatTanggalIndonesia($tanggal)
{
    $bulan = [
        1 => 'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    ];
    $timestamp = strtotime($tanggal);
    $tgl = date('d', $timestamp);
    $bln = $bulan[(int) date('m', $timestamp)];
    $thn = date('Y', $timestamp);
    return $tgl . ' ' . $bln . ' ' . $thn;
}
// Set timezone ke WIB (Asia/Jakarta)
date_default_timezone_set('Asia/Jakarta');
$tanggalCetak = formatTanggalIndonesia($data['tanggal_cetak'] ?? date('Y-m-d'));
$waktuCetak = $data['waktu_cetak'] ?? (date('H:i') . ' WIB');
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pembayaran - <?= $data['nama_siswa']; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            color: #333;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 14pt;
            color: #1e40af;
            margin: 15px 0 5px 0;
            font-weight: bold;
        }

        .info-box {
            background: #f3f4f6;
            padding: 8px;
            border-radius: 6px;
            margin-bottom: 15px;
            border-left: 4px solid #2563eb;
        }

        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 4px;
        }

        .info-label {
            display: table-cell;
            width: 130px;
            font-weight: bold;
            color: #374151;
            font-size: 9pt;
        }

        .info-value {
            display: table-cell;
            color: #1f2937;
            font-size: 9pt;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table.data-table th {
            background: #1e40af;
            color: white;
            padding: 6px 4px;
            text-align: left;
            font-size: 9pt;
            font-weight: bold;
            border: 1px solid #1e3a8a;
        }

        table.data-table td {
            padding: 4px;
            border: 1px solid #e5e7eb;
            font-size: 9pt;
        }

        table.data-table tbody tr:nth-child(even) {
            background: #f9fafb;
        }

        table.data-table tbody tr:hover {
            background: #f3f4f6;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #e5e7eb;
            display: table;
            width: 100%;
        }

        .print-info {
            margin-top: 20px;
            padding: 10px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            font-size: 9pt;
            color: #6b7280;
        }

        .qr-code-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            text-align: center;
            padding: 10px;
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
        }

        .qr-code-container img {
            width: 150px;
            height: 150px;
            display: block;
            margin: 0 auto 5px;
        }

        .qr-code-text {
            font-size: 7pt;
            color: #6b7280;
            margin-top: 5px;
        }

        @media print {
            body {
                padding: 15px;
            }

            .qr-code-container {
                position: absolute;
            }
        }
    </style>
</head>

<body>
    <!-- Header with Kop -->
    <div class="header">
        <?php if (!empty($kopHTML)): ?>
            <?= $kopHTML ?>
        <?php endif; ?>
        <h1>RIWAYAT PEMBAYARAN SISWA</h1>
    </div>
    <!-- Info Siswa -->
    <div class="info-box">
        <div class="info-row">
            <span class="info-label">Nama Siswa</span>
            <span class="info-value">: <?= htmlspecialchars($data['nama_siswa']); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">NISN</span>
            <span class="info-value">: <?= htmlspecialchars($data['nisn']); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Kelas</span>
            <span class="info-value">: <?= htmlspecialchars($data['nama_kelas']); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Semester</span>
            <span class="info-value">: <?= htmlspecialchars($data['nama_semester'] ?? '-'); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Tanggal Cetak</span>
            <span class="info-value">: <?= $tanggalCetak . ' - ' . $waktuCetak; ?></span>
        </div>
    </div>
    <!-- Tabel Riwayat Pembayaran -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 18%;">Tanggal</th>
                <th style="width: 30%;">Tagihan</th>
                <th style="width: 15%;">Jumlah</th>
                <th style="width: 13%;">Metode</th>
                <th style="width: 15%;">Keterangan</th>
                <th style="width: 15%;">Diterima Oleh</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['riwayat_pembayaran'])): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px; color: #6b7280;">
                        Tidak ada riwayat pembayaran untuk periode ini
                    </td>
                </tr>
            <?php else: ?>
                <?php $no = 1; ?>
                <?php foreach ($data['riwayat_pembayaran'] as $row): ?>
                    <tr>
                        <td style="text-align: center;"><?= $no++; ?></td>
                        <td><?= formatTanggalIndonesia($row['tanggal']); ?></td>
                        <td><?= htmlspecialchars($row['nama_tagihan']); ?></td>
                        <td>Rp<?= number_format($row['jumlah'], 0, ',', '.'); ?></td>
                        <td><?= htmlspecialchars($row['metode']); ?></td>
                        <td><?= !empty($row['keterangan']) ? htmlspecialchars($row['keterangan']) : '-'; ?></td>
                        <td><?= htmlspecialchars($row['petugas_input'] ?? 'Sistem'); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    <!-- Print Info -->
    <div class="print-info">
        <strong>Informasi Dokumen:</strong><br>
        Dokumen ini dicetak pada <?= $tanggalCetak . ' pukul ' . $waktuCetak; ?> oleh
        <?= htmlspecialchars($data['nama_siswa']); ?><br>
        Dokumen ini sah dan terverifikasi dengan QR Code di pojok kanan bawah halaman
    </div>

    <!-- QR Code Container (akan diisi oleh PDFQRHelper) -->
    <div class="qr-code-container">
        <!-- QR Code akan disisipkan di sini -->
    </div>
</body>

</html>