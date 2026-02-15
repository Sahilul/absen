<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Rekap Dokumen Siswa</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 9pt;
            line-height: 1.3;
            padding: 15px;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .header h1 {
            font-size: 14pt;
            margin-bottom: 3px;
            text-transform: uppercase;
        }

        .header h2 {
            font-size: 11pt;
            font-weight: normal;
            margin-bottom: 2px;
        }

        .header p {
            font-size: 8pt;
            color: #666;
        }

        .info-section {
            margin: 10px 0;
            font-size: 8pt;
        }

        .info-section table {
            width: 100%;
        }

        .info-section td {
            padding: 2px 0;
        }

        .info-section td:first-child {
            width: 120px;
            font-weight: bold;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 8pt;
        }

        table.data-table th {
            background-color: #2d3748;
            color: white;
            padding: 6px 4px;
            text-align: left;
            border: 1px solid #1a202c;
            font-size: 8pt;
        }

        table.data-table td {
            padding: 4px;
            border: 1px solid #cbd5e0;
            vertical-align: top;
        }

        table.data-table tr:nth-child(even) {
            background-color: #f7fafc;
        }

        .text-center {
            text-align: center;
        }

        .dokumen-checklist {
            font-size: 7pt;
            line-height: 1.4;
        }

        .dokumen-item {
            margin: 2px 0;
            display: flex;
            align-items: flex-start;
        }

        .check-icon {
            display: inline-block;
            width: 11px;
            height: 11px;
            margin-right: 4px;
            text-align: center;
            line-height: 11px;
            font-weight: bold;
            border-radius: 2px;
            flex-shrink: 0;
            font-size: 9pt;
            font-family: Arial, sans-serif;
        }

        .check-yes {
            background-color: #48bb78;
            color: white;
        }

        .check-no {
            background-color: #e2e8f0;
            color: #718096;
            border: 1px solid #cbd5e0;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7pt;
            font-weight: bold;
        }

        .badge-info {
            background-color: #bee3f8;
            color: #2c5282;
        }

        .footer {
            margin-top: 15px;
            font-size: 7pt;
            color: #666;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 8px;
        }
    </style>
</head>

<body>
    <?php
    $siswaList = $data['siswa'] ?? [];
    $dokumenList = $data['dokumen_list'] ?? [];
    $totalDokumen = $data['total_dokumen'] ?? 0;
    $pengaturan = $data['pengaturan'] ?? [];
    $filters = $data['filters'] ?? [];

    $namaSekolah = $pengaturan['nama_aplikasi'] ?? 'Sekolah';
    $alamatSekolah = $pengaturan['alamat'] ?? '';

    // Calculate stats
    $totalSiswa = count($siswaList);
    $lengkap = 0;
    $belumLengkap = 0;

    foreach ($siswaList as $s) {
        if ($s['jumlah_upload'] >= $totalDokumen) {
            $lengkap++;
        } else {
            $belumLengkap++;
        }
    }
    ?>

    <!-- Header -->
    <div class="header">
        <h1><?= htmlspecialchars($namaSekolah); ?></h1>
        <?php if ($alamatSekolah): ?>
            <p><?= htmlspecialchars($alamatSekolah); ?></p>
        <?php endif; ?>
        <h2>Rekap Kelengkapan Dokumen Siswa</h2>
    </div>

    <!-- Info Section -->
    <div class="info-section">
        <table>
            <tr>
                <td>Tanggal Cetak</td>
                <td>: <?= date('d F Y, H:i'); ?> WIB</td>
                <td style="width: 120px; font-weight: bold;">Total Siswa</td>
                <td>: <?= $totalSiswa; ?> siswa</td>
            </tr>
            <?php if (!empty($data['nama_kelas'])): ?>
                <tr>
                    <td>Kelas</td>
                    <td>: <?= htmlspecialchars($data['nama_kelas']); ?></td>
                    <td>Dokumen Lengkap</td>
                    <td>: <?= $lengkap; ?> siswa</td>
                </tr>
            <?php else: ?>
                <tr>
                    <td></td>
                    <td></td>
                    <td>Dokumen Lengkap</td>
                    <td>: <?= $lengkap; ?> siswa</td>
                </tr>
            <?php endif; ?>
            <?php if (!empty($filters['search'])): ?>
                <tr>
                    <td>Pencarian</td>
                    <td>: <?= htmlspecialchars($filters['search']); ?></td>
                    <td>Belum Lengkap</td>
                    <td>: <?= $belumLengkap; ?> siswa</td>
                </tr>
            <?php else: ?>
                <tr>
                    <td></td>
                    <td></td>
                    <td>Belum Lengkap</td>
                    <td>: <?= $belumLengkap; ?> siswa</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- Data Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center" width="3%">No</th>
                <th width="10%">NISN</th>
                <th width="18%">Nama Siswa</th>
                <th width="8%">Kelas</th>
                <th width="61%">Dokumen</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($siswaList)): ?>
                <tr>
                    <td colspan="5" class="text-center">Tidak ada data siswa</td>
                </tr>
            <?php else: ?>
                <?php foreach ($siswaList as $index => $s): ?>
                    <tr>
                        <td class="text-center"><?= $index + 1; ?></td>
                        <td><?= htmlspecialchars($s['nisn']); ?></td>
                        <td><?= htmlspecialchars($s['nama_siswa']); ?></td>
                        <td>
                            <span class="badge badge-info"><?= htmlspecialchars($s['nama_kelas']); ?></span>
                        </td>
                        <td>
                            <div class="dokumen-checklist">
                                <?php
                                $dokumenDetail = $s['dokumen_detail'] ?? [];
                                $itemsPerColumn = ceil(count($dokumenDetail) / 2);
                                ?>
                                <table width="100%" style="border: none;">
                                    <tr>
                                        <td width="50%" style="border: none; padding: 0; vertical-align: top;">
                                            <?php for ($i = 0; $i < $itemsPerColumn && $i < count($dokumenDetail); $i++):
                                                $dok = $dokumenDetail[$i];
                                                ?>
                                                <div class="dokumen-item">
                                                    <span class="check-icon <?= $dok['uploaded'] ? 'check-yes' : 'check-no'; ?>">
                                                        <?= $dok['uploaded'] ? '&#10004;' : '-'; ?>
                                                    </span>
                                                    <span><?= htmlspecialchars($dok['nama']); ?></span>
                                                </div>
                                            <?php endfor; ?>
                                        </td>
                                        <td width="50%" style="border: none; padding: 0; vertical-align: top;">
                                            <?php for ($i = $itemsPerColumn; $i < count($dokumenDetail); $i++):
                                                $dok = $dokumenDetail[$i];
                                                ?>
                                                <div class="dokumen-item">
                                                    <span class="check-icon <?= $dok['uploaded'] ? 'check-yes' : 'check-no'; ?>">
                                                        <?= $dok['uploaded'] ? '&#10004;' : '-'; ?>
                                                    </span>
                                                    <span><?= htmlspecialchars($dok['nama']); ?></span>
                                                </div>
                                            <?php endfor; ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <p>Dicetak pada <?= date('d F Y, H:i:s'); ?> WIB | Dokumen ini digenerate otomatis oleh sistem</p>
    </div>
</body>

</html>