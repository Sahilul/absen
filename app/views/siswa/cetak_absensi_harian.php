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
function formatTanggalIndonesia($tanggal) {
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $timestamp = strtotime($tanggal);
    $tgl = date('d', $timestamp);
    $bln = $bulan[(int)date('m', $timestamp)];
    $thn = date('Y', $timestamp);
    return $tgl . ' ' . $bln . ' ' . $thn;
}

// Set timezone ke WIB (Asia/Jakarta)
date_default_timezone_set('Asia/Jakarta');

// Format tanggal dan waktu cetak dengan WIB
$tanggalCetak = formatTanggalIndonesia(date('Y-m-d'));
$waktuCetak = date('H:i') . ' WIB';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Absensi Harian - <?= $data['nama_siswa']; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.4;
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
        
        .info-row:last-child {
            margin-bottom: 0;
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
        
        .stats-box {
            display: table;
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        
        .stat-item {
            display: table-cell;
            width: 25%;
            padding: 8px;
            text-align: center;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
        }
        
        .stat-item:first-child {
            border-left: 3px solid #10b981;
        }
        
        .stat-item:nth-child(2) {
            border-left: 3px solid #3b82f6;
        }
        
        .stat-item:nth-child(3) {
            border-left: 3px solid #f59e0b;
        }
        
        .stat-item:last-child {
            border-left: 3px solid #ef4444;
        }
        
        .stat-number {
            font-size: 18pt;
            font-weight: bold;
            display: block;
            margin-bottom: 3px;
        }
        
        .stat-label {
            font-size: 8pt;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-item:first-child .stat-number { color: #10b981; }
        .stat-item:nth-child(2) .stat-number { color: #3b82f6; }
        .stat-item:nth-child(3) .stat-number { color: #f59e0b; }
        .stat-item:last-child .stat-number { color: #ef4444; }
        
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
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
        
        table.data-table th.center {
            text-align: center;
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
        
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 9pt;
            font-weight: bold;
            text-align: center;
            min-width: 60px;
        }
        
        .status-H {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-I {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .status-S {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-A {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #e5e7eb;
            display: table;
            width: 100%;
        }
        
        .signature-box {
            display: table-cell;
            width: 40%;
            text-align: center;
            padding: 10px;
        }
        
        .signature-box.right {
            text-align: right;
            float: right;
        }
        
        .signature-line {
            margin-top: 60px;
            border-top: 1px solid #333;
            display: inline-block;
            min-width: 200px;
            padding-top: 5px;
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
        <h1>LAPORAN ABSENSI HARIAN SISWA</h1>
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
            <span class="info-value">: <?= htmlspecialchars($data['nama_semester']); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Tanggal Cetak</span>
            <span class="info-value">: <?= $tanggalCetak . ' - ' . $waktuCetak; ?></span>
        </div>
    </div>
    
    <!-- Statistics -->
    <div class="stats-box">
        <div class="stat-item">
            <span class="stat-number"><?= $data['total_hadir']; ?></span>
            <span class="stat-label">Hadir</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?= $data['total_izin']; ?></span>
            <span class="stat-label">Izin</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?= $data['total_sakit']; ?></span>
            <span class="stat-label">Sakit</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?= $data['total_alpha']; ?></span>
            <span class="stat-label">Alpha</span>
        </div>
    </div>
    
    <!-- Tabel Data -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 15%;">Tanggal</th>
                <th style="width: 25%;">Mata Pelajaran</th>
                <th style="width: 25%;">Guru Pengajar</th>
                <th style="width: 12%;" class="center">Status</th>
                <th style="width: 18%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['absensi_harian'])): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px; color: #6b7280;">
                        Tidak ada data absensi untuk periode ini
                    </td>
                </tr>
            <?php else: ?>
                <?php $no = 1; ?>
                <?php foreach ($data['absensi_harian'] as $absen): ?>
                    <tr>
                        <td style="text-align: center;"><?= $no++; ?></td>
                        <td><?= formatTanggalIndonesia($absen['tanggal']); ?></td>
                        <td><?= htmlspecialchars($absen['nama_mapel']); ?></td>
                        <td><?= htmlspecialchars($absen['nama_guru']); ?></td>
                        <td style="text-align: center;">
                            <?php
                            $statusLabels = [
                                'H' => 'Hadir',
                                'I' => 'Izin',
                                'S' => 'Sakit',
                                'A' => 'Alpha'
                            ];
                            $statusLabel = $statusLabels[$absen['status_kehadiran']] ?? 'Unknown';
                            ?>
                            <span class="status-badge status-<?= $absen['status_kehadiran']; ?>">
                                <?= $statusLabel; ?>
                            </span>
                        </td>
                        <td><?= !empty($absen['keterangan']) ? htmlspecialchars($absen['keterangan']) : '-'; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    
    <!-- Print Info -->
    <div class="print-info">
        <strong>Informasi Dokumen:</strong><br>
        Dokumen ini dicetak pada <?= $tanggalCetak . ' pukul ' . $waktuCetak; ?> oleh <?= htmlspecialchars($data['nama_siswa']); ?><br>
        Dokumen ini sah dan terverifikasi dengan QR Code di pojok kanan bawah halaman
    </div>
    
    <!-- QR Code Container (akan diisi oleh PDFQRHelper) -->
    <div class="qr-code-container">
        <!-- QR Code akan disisipkan di sini -->
    </div>
</body>
</html>
