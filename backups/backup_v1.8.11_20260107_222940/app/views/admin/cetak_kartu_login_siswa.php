<?php
// File: app/views/admin/cetak_kartu_login_siswa.php
// Cetak Kartu Login Siswa - Modern Green Design
$pengaturan = $data['pengaturan'] ?? [];

// Nama aplikasi
$appName = $pengaturan['nama_aplikasi'] ?? 'SMART ABSENSI';
if (empty($appName))
    $appName = 'SMART ABSENSI';

// URL Aplikasi
$appUrl = $pengaturan['url_web'] ?? '';
// Bersihkan protokol untuk tampilan
$displayUrl = preg_replace('#^https?://#', '', $appUrl);
$displayUrl = rtrim($displayUrl, '/');

// Logo Logic
$logoApp = $pengaturan['logo'] ?? '';
$logoUrl = '';
$logoExists = false;

// Cek via path server
$baseDir = dirname(dirname(dirname(__DIR__))); // c:\laragon\www\absen
$logoPath = $baseDir . '/public/img/app/' . $logoApp;

if (!empty($logoApp) && file_exists($logoPath)) {
    $logoUrl = BASEURL . '/public/img/app/' . $logoApp;
    $logoExists = true;
} else {
    // Auto detect fallback
    $files = glob($baseDir . '/public/img/app/*.{png,jpg,jpeg}', GLOB_BRACE);
    if (!empty($files)) {
        $fileName = basename($files[0]);
        $logoUrl = BASEURL . '/public/img/app/' . $fileName;
        $logoExists = true;
    }
}

// Inisial untuk fallback
$initials = substr($appName, 0, 2);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Kartu Login - <?= htmlspecialchars($data['nama_kelas'] ?? 'Semua Kelas'); ?></title>
    <!-- Font Inter for Modern Look -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4;
            margin: 10mm;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                background: white !important;
            }

            .no-print {
                display: none !important;
            }

            .page-break {
                page-break-after: always;
            }

            .login-card {
                border: 1px solid #e5e7eb !important;
                page-break-inside: avoid;
            }
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
            background: #f3f4f6;
            padding: 20px;
            color: #1f2937;
        }

        .action-bar {
            background: white;
            padding: 15px 20px;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 5px solid #10B981;
        }

        .action-bar h1 {
            font-size: 20px;
            font-weight: 700;
            color: #111;
        }

        .action-bar .meta {
            font-size: 14px;
            color: #6b7280;
            margin-top: 2px;
        }

        .btn-print {
            background: #10B981;
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background 0.2s;
        }

        .btn-print:hover {
            background: #059669;
        }

        .cards-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            max-width: 210mm;
            margin: 0 auto;
        }

        /* Modern Card Design */
        .login-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            position: relative;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            display: flex;
            flex-direction: column;
            border: 1px solid #e5e7eb;
        }

        /* Green Header Stripe */
        .card-top-stripe {
            height: 8px;
            background: linear-gradient(90deg, #10B981, #059669);
            width: 100%;
        }

        .card-content {
            padding: 15px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            flex: 1;
        }

        /* Header Section: Logo + App Name */
        .card-app-header {
            display: flex;
            align-items: center;
            gap: 15px;
            border-bottom: 1px dashed #e5e7eb;
            padding-bottom: 15px;
        }

        .logo-box {
            width: 40px;
            height: 40px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            /* Clean white bg */
        }

        .logo-img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .logo-placeholder {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 800;
            color: #059669;
            background: #f0fdf4;
            border-radius: 50%;
            border: 2px solid #d1fae5;
        }

        .app-header-text {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .app-title {
            font-size: 14px;
            font-weight: 800;
            /* Extra Bold */
            color: #059669;
            /* Emerald 600 */
            text-transform: uppercase;
            line-height: 1.1;
            letter-spacing: 0.5px;
        }

        .app-url {
            font-size: 11px;
            color: #10B981;
            font-weight: 500;
            margin-top: 3px;
        }

        /* Student Info Section */
        .student-details {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .student-name {
            font-size: 16px;
            /* Adjusted for 3 cols */
            font-weight: 800;
            color: #111827;
            line-height: 1.2;
            margin-bottom: 2px;
        }

        /* Standardized Info Rows (NISN & Kelas) */
        .info-row {
            display: flex;
            align-items: center;
            font-size: 12px;
            color: #4b5563;
        }

        .info-label {
            width: 60px;
            font-weight: 500;
            color: #6b7280;
        }

        .info-val {
            font-weight: 700;
            color: #1f2937;
        }

        /* Credentials Box */
        .credentials-box {
            background: #ecfdf5;
            /* Emerald 50 */
            border: 1px solid #a7f3d0;
            border-radius: 12px;
            padding: 10px;
            margin-top: auto;
            /* Push to bottom */
        }

        .cred-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .cred-item:last-child {
            margin-bottom: 0;
        }

        .cred-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #059669;
            font-weight: 700;
        }

        .cred-val {
            font-family: 'Consolas', 'Monaco', monospace;
            font-size: 14px;
            /* Big Credentials */
            font-weight: 800;
            color: #047857;
            background: white;
            padding: 4px 12px;
            border-radius: 6px;
            border: 1px solid #d1fae5;
            min-width: 120px;
            text-align: center;
        }
    </style>
</head>

<body>

    <div class="action-bar no-print">
        <div>
            <h1>Cetak Kartu Login</h1>
            <div class="meta">Kelas: <?= htmlspecialchars($data['nama_kelas'] ?? 'Semua'); ?> &bull;
                <?= count($data['siswa_list']); ?> Siswa
            </div>
        </div>
        <button class="btn-print" onclick="window.print()">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                </path>
            </svg>
            Print Kartu
        </button>
    </div>

    <div class="cards-grid">
        <?php foreach ($data['siswa_list'] as $siswa): ?>
            <div class="login-card">
                <div class="card-top-stripe"></div>
                <div class="card-content">

                    <div class="card-app-header">
                        <div class="logo-box">
                            <?php if ($logoExists): ?>
                                <img src="<?= $logoUrl; ?>" alt="Logo" class="logo-img">
                            <?php else: ?>
                                <div class="logo-placeholder"><?= $initials; ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="app-header-text">
                            <div class="app-title"><?= htmlspecialchars($appName); ?></div>
                            <?php if ($displayUrl): ?>
                                <div class="app-url"><?= htmlspecialchars($displayUrl); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="student-details">
                        <div class="student-name"><?= htmlspecialchars($siswa['nama_siswa']); ?></div>

                        <div class="info-row">
                            <span class="info-label">NISN</span>
                            <span class="info-val">: <?= htmlspecialchars($siswa['nisn'] ?? '-'); ?></span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Kelas</span>
                            <span class="info-val">: <?= htmlspecialchars($siswa['nama_kelas'] ?? '-'); ?></span>
                        </div>
                    </div>

                    <div class="credentials-box">
                        <div class="cred-item">
                            <span class="cred-label">Username</span>
                            <div class="cred-val"><?= htmlspecialchars($siswa['username'] ?? $siswa['nisn']); ?></div>
                        </div>
                        <div class="cred-item">
                            <span class="cred-label">Password</span>
                            <div class="cred-val"><?= htmlspecialchars($siswa['password_plain'] ?? '-'); ?></div>
                        </div>
                    </div>

                </div>
            </div>
        <?php endforeach; ?>
    </div>

</body>

</html>