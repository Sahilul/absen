<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Rekap Dokumen Siswa</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10pt;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h2 {
            margin: 0;
        }

        .header p {
            margin: 5px 0;
            font-size: 9pt;
            color: #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #333;
            padding: 6px;
            font-size: 9pt;
        }

        th {
            background-color: #f0f0f0;
        }

        .center {
            text-align: center;
        }

        .status-ok {
            color: green;
            font-weight: bold;
        }

        .status-no {
            color: red;
            font-weight: bold;
        }

        .summary {
            margin-bottom: 15px;
            font-size: 9pt;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>Rekap Kelengkapan Dokumen Siswa</h2>
        <p>Kelas: <?= htmlspecialchars($nama_kelas); ?> | Tanggal: <?= date('d-m-Y'); ?></p>
    </div>

    <div class="summary">
        <strong>Total Siswa:</strong> <?= count($siswa); ?> |
        <strong>Lengkap:</strong> <?= $stats['lengkap']; ?> |
        <strong>Belum Lengkap:</strong> <?= $stats['belum']; ?>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="10%">NISN</th>
                <th width="25%">Nama Siswa</th>
                <th width="10%">Kelas</th>
                <th width="10%">Progress</th>
                <?php foreach ($dokumen_config as $cfg): ?>
                    <th><?= htmlspecialchars($cfg['nama']); ?></th>
                <?php endforeach; ?>
                <th width="10%">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($siswa as $idx => $s):
                $progress = ($total_dokumen > 0) ? round(($s['jumlah_upload'] / $total_dokumen) * 100) : 0;
                $isComplete = ($s['jumlah_upload'] >= $total_dokumen);

                // Get documents for this student
                // Note: accessing via model in loop might be slow but simple for now. 
                // Better: AdminController should prepare this data.
                // Assuming $s['dokumen_status'] contains [jenis => bool] map if optimized, 
                // BUT current getSiswaWithDocumentStatus only returns counts.
                // We need to fetch details for PDF or modify model.
                // For performance, we'll check logic in controller.
                ?>
                <tr>
                    <td class="center"><?= $idx + 1; ?></td>
                    <td class="center"><?= htmlspecialchars($s['nisn']); ?></td>
                    <td><?= htmlspecialchars($s['nama_siswa']); ?></td>
                    <td class="center"><?= htmlspecialchars($s['nama_kelas']); ?></td>
                    <td class="center"><?= $progress; ?>%</td>

                    <?php foreach ($dokumen_config as $cfg):
                        $uploaded = isset($s['dokumen_uploaded'][$cfg['kode']]);
                        ?>
                        <td class="center">
                            <?= $uploaded ? '<span class="status-ok">v</span>' : '<span class="status-no">-</span>'; ?>
                        </td>
                    <?php endforeach; ?>

                    <td class="center">
                        <?= $isComplete ? '<span class="status-ok">Lengkap</span>' : '<span class="status-no">Belum</span>'; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>

</html>