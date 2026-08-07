<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Login Siswa - <?= htmlspecialchars($data['nama_kelas']); ?></title>
    <?php
    // Get logo path
    $pengaturan = $data['pengaturan'] ?? [];
    $logo = $pengaturan['logo'] ?? '';
    $logoPath = BASEURL . '/public/img/app/' . $logo;
    ?>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding: 15px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .header h1 {
            font-size: 20px;
            color: #333;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 14px;
            color: #666;
        }

        .print-btn {
            background: #16a34a;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 10px;
        }

        .print-btn:hover {
            background: #15803d;
        }

        .cards-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            max-width: 900px;
            margin: 0 auto;
        }

        .login-card {
            background: white;
            color: #333;
            padding: 15px;
            border-radius: 10px;
            border: 2px solid #16a34a;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            page-break-inside: avoid;
        }

        .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .school-logo {
            width: 35px;
            height: 35px;
            border-radius: 6px;
            margin-right: 10px;
            object-fit: contain;
        }

        .school-name {
            font-size: 11px;
            font-weight: 600;
            line-height: 1.3;
            color: #16a34a;
        }

        .card-body {
            font-size: 12px;
        }

        .field {
            margin-bottom: 8px;
        }

        .field-label {
            font-size: 9px;
            text-transform: uppercase;
            color: #6b7280;
            letter-spacing: 0.5px;
        }

        .field-value {
            font-size: 13px;
            font-weight: 600;
            margin-top: 2px;
            color: #1f2937;
        }

        .field-value.small {
            font-size: 11px;
        }

        .credentials {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
        }

        .credential-box {
            background: #f3f4f6;
            padding: 8px;
            border-radius: 6px;
        }

        .credential-box .field-value {
            color: #16a34a;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
            font-size: 16px;
        }

        @media print {
            body {
                background: white;
                padding: 10px;
            }

            .header {
                display: none;
            }

            .print-btn {
                display: none;
            }

            .cards-container {
                gap: 8px;
            }

            .login-card {
                box-shadow: none;
            }
        }

        @page {
            size: A4 portrait;
            margin: 10mm;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Kartu Login Siswa</h1>
        <p><?= htmlspecialchars($data['nama_kelas']); ?> - Total: <?= count($data['siswa']); ?> siswa</p>
        <button class="print-btn" onclick="window.print()">🖨️ Cetak Kartu</button>
    </div>

    <?php if (empty($data['siswa'])): ?>
        <div class="no-data">
            <p>Tidak ada data siswa</p>
        </div>
    <?php else: ?>
        <div class="cards-container">
            <?php foreach ($data['siswa'] as $s): ?>
                <div class="login-card">
                    <div class="card-header">
                        <?php if (!empty($logo)): ?>
                            <img src="<?= $logoPath; ?>" alt="Logo" class="school-logo">
                        <?php else: ?>
                            <div class="school-logo"
                                style="background:#16a34a;display:flex;align-items:center;justify-content:center;color:white;font-size:18px;">
                                🎓</div>
                        <?php endif; ?>
                        <div class="school-name">
                            <?= htmlspecialchars($data['pengaturan']['nama_aplikasi'] ?? 'Smart Absensi'); ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="field">
                            <div class="field-label">Nama Siswa</div>
                            <div class="field-value"><?= htmlspecialchars($s['nama_siswa']); ?></div>
                        </div>
                        <div class="field">
                            <div class="field-label">NISN</div>
                            <div class="field-value"><?= htmlspecialchars($s['nisn'] ?? '-'); ?></div>
                        </div>
                        <div class="field">
                            <div class="field-label">Kelas</div>
                            <div class="field-value small"><?= htmlspecialchars($s['nama_kelas'] ?? $data['nama_kelas']); ?>
                            </div>
                        </div>
                        <div class="credentials">
                            <div class="credential-box">
                                <div class="field-label">Username</div>
                                <div class="field-value small"><?= htmlspecialchars($s['nisn'] ?? '-'); ?></div>
                            </div>
                            <div class="credential-box">
                                <div class="field-label">Password</div>
                                <div class="field-value small"><?= htmlspecialchars($s['password_plain'] ?? '-'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</body>

</html>