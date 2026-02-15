<?php
// Generate kop HTML from uploaded image
$kopHTML = '';
if (!empty($pengaturan) && !empty($pengaturan['kop_rapor'])) {
    $kopPath = 'public/img/kop/' . $pengaturan['kop_rapor'];
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
    <title>Rekap Absensi - <?= htmlspecialchars($nama_siswa); ?></title>
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
            border-left: 3px solid #1976d2;
        }
        
        .stat-item:nth-child(2) {
            border-left: 3px solid #0288d1;
        }
        
        .stat-item:nth-child(3) {
            border-left: 3px solid #388e3c;
        }
        
        .stat-item:last-child {
            border-left: 3px solid #f57c00;
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
        
        .stat-item:first-child .stat-number { color: #1976d2; }
        .stat-item:nth-child(2) .stat-number { color: #0288d1; }
        .stat-item:nth-child(3) .stat-number { color: #388e3c; }
        .stat-item:last-child .stat-number { color: #f57c00; }
        
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
        
        .status-excellent {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-good {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .status-fair {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-poor {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .badge-hadir { 
            background: #d1fae5; 
            color: #065f46; 
            padding: 3px 8px; 
            border-radius: 10px;
            font-weight: bold;
            font-size: 9pt;
        }
        
        .badge-izin { 
            background: #dbeafe; 
            color: #1e40af; 
            padding: 3px 8px; 
            border-radius: 10px;
            font-weight: bold;
            font-size: 9pt;
        }
        
        .badge-sakit { 
            background: #fef3c7; 
            color: #92400e; 
            padding: 3px 8px; 
            border-radius: 10px;
            font-weight: bold;
            font-size: 9pt;
        }
        
        .badge-alpha { 
            background: #fee2e2; 
            color: #991b1b; 
            padding: 3px 8px; 
            border-radius: 10px;
            font-weight: bold;
            font-size: 9pt;
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
        <h1>REKAP ABSENSI PER MATA PELAJARAN</h1>
    </div>
    
    <!-- Info Siswa -->
    <div class="info-box">
        <div class="info-row">
            <span class="info-label">Nama Siswa</span>
            <span class="info-value">: <?= htmlspecialchars($nama_siswa); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">NISN</span>
            <span class="info-value">: <?= htmlspecialchars($nisn); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Kelas</span>
            <span class="info-value">: <?= htmlspecialchars($nama_kelas); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Semester</span>
            <span class="info-value">: <?= htmlspecialchars($nama_semester); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Tanggal Cetak</span>
            <span class="info-value">: <?= $tanggalCetak . ' - ' . $waktuCetak; ?></span>
        </div>
    </div>
    
    <!-- Statistics -->
    <div class="stats-box">
        <div class="stat-item">
            <span class="stat-number"><?= $total_subjects; ?></span>
            <span class="stat-label">Mata Pelajaran</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?= $total_meetings; ?></span>
            <span class="stat-label">Total Pertemuan</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?= $total_present; ?></span>
            <span class="stat-label">Total Kehadiran</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?= $overall_percentage; ?>%</span>
            <span class="stat-label">Persentase</span>
        </div>
    </div>
    
    <!-- Tabel Data -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 30%;">Mata Pelajaran</th>
                <th style="width: 10%;" class="center">Hadir</th>
                <th style="width: 10%;" class="center">Izin</th>
                <th style="width: 10%;" class="center">Sakit</th>
                <th style="width: 10%;" class="center">Alpha</th>
                <th style="width: 10%;" class="center">Total</th>
                <th style="width: 15%;" class="center">Kehadiran</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($rekap_per_mapel)): ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 20px; color: #6b7280;">
                        Tidak ada data rekap absensi untuk periode ini
                    </td>
                </tr>
            <?php else: ?>
                <?php 
                $no = 1;
                foreach ($rekap_per_mapel as $rekap) : 
                    $total = $rekap['hadir'] + $rekap['izin'] + $rekap['sakit'] + $rekap['alfa'];
                    $persentase = ($total > 0) ? round(($rekap['hadir'] / $total) * 100) : 0;
                    
                    // Determine status
                    $statusClass = 'status-poor';
                    $statusText = 'Kurang';
                    
                    if ($persentase >= 90) {
                        $statusClass = 'status-excellent';
                        $statusText = 'Excellent';
                    } elseif ($persentase >= 75) {
                        $statusClass = 'status-good';
                        $statusText = 'Baik';
                    } elseif ($persentase >= 60) {
                        $statusClass = 'status-fair';
                        $statusText = 'Cukup';
                    }
                ?>
                <tr>
                    <td style="text-align: center;"><?= $no++; ?></td>
                    <td style="padding-left: 8px;">
                        <?= htmlspecialchars($rekap['nama_mapel']); ?>
                    </td>
                    <td style="text-align: center;"><span class="badge-hadir"><?= $rekap['hadir']; ?></span></td>
                    <td style="text-align: center;"><span class="badge-izin"><?= $rekap['izin']; ?></span></td>
                    <td style="text-align: center;"><span class="badge-sakit"><?= $rekap['sakit']; ?></span></td>
                    <td style="text-align: center;"><span class="badge-alpha"><?= $rekap['alfa']; ?></span></td>
                    <td style="text-align: center;"><strong><?= $total; ?></strong></td>
                    <td style="text-align: center;">
                        <strong><?= $persentase; ?>%</strong><br>
                        <span class="status-badge <?= $statusClass; ?>"><?= $statusText; ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    
    <!-- Print Info -->
    <div class="print-info">
        <strong>Informasi Dokumen:</strong><br>
        Dokumen ini dicetak pada <?= $tanggalCetak . ' pukul ' . $waktuCetak; ?> oleh <?= htmlspecialchars($nama_siswa); ?><br>
        Dokumen ini sah dan terverifikasi dengan QR Code di pojok kanan bawah halaman
    </div>
    
    <!-- QR Code Container (akan diisi oleh PDFQRHelper) -->
    <div class="qr-code-container">
        <!-- QR Code akan disisipkan di sini -->
    </div>
</body>
</html>
