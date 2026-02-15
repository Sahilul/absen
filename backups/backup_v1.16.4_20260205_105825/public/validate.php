<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Validator - Absensi App</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
        }
        .validator-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px 20px;
            text-align: center;
        }
        .card-header h1 {
            font-size: 24px;
            margin-bottom: 6px;
        }
        .card-header p {
            opacity: 0.9;
            font-size: 13px;
        }
        .card-body {
            padding: 25px 20px;
        }
        .result-box {
            text-align: center;
            padding: 25px 15px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        .result-box.valid {
            background: #d4edda;
            border: 2px solid #28a745;
        }
        .result-box.invalid {
            background: #f8d7da;
            border: 2px solid #dc3545;
        }
        .result-box.pending {
            background: #fff3cd;
            border: 2px solid #ffc107;
        }
        .result-icon {
            font-size: 48px;
            margin-bottom: 12px;
        }
        .result-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 8px;
        }
        .result-message {
            font-size: 14px;
            color: #666;
        }
        .doc-details {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .doc-details h3 {
            color: #667eea;
            margin-bottom: 12px;
            font-size: 16px;
        }
        .detail-row {
            display: flex;
            flex-direction: column;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
            gap: 4px;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #555;
            font-size: 13px;
        }
        .detail-value {
            color: #333;
            font-size: 14px;
            word-break: break-word;
        }
        .btn-primary {
            background: #667eea;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            width: 100%;
            transition: background 0.3s;
        }
        .btn-primary:hover {
            background: #5568d3;
        }
        .warning-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 12px;
            border-radius: 6px;
            margin-top: 15px;
            font-size: 13px;
            color: #856404;
        }
        .warning-box ul {
            margin-top: 8px;
            margin-left: 18px;
        }
        
        /* Responsive tablets */
        @media (min-width: 640px) {
            .card-header h1 {
                font-size: 28px;
            }
            .card-body {
                padding: 30px;
            }
            .result-box {
                padding: 30px 20px;
            }
            .result-icon {
                font-size: 56px;
            }
            .result-title {
                font-size: 24px;
            }
            .result-message {
                font-size: 15px;
            }
            .doc-details {
                padding: 20px;
            }
            .doc-details h3 {
                font-size: 18px;
            }
            .detail-row {
                flex-direction: row;
                justify-content: space-between;
                gap: 15px;
            }
            .detail-label {
                font-size: 14px;
                flex-shrink: 0;
                min-width: 140px;
            }
            .detail-value {
                font-size: 14px;
                text-align: right;
                flex: 1;
            }
        }
        
        /* Responsive desktop */
        @media (min-width: 768px) {
            body {
                padding: 20px;
            }
            .card-header {
                padding: 30px;
            }
            .card-body {
                padding: 40px;
            }
            .result-box {
                padding: 30px;
                margin-bottom: 30px;
            }
            .result-icon {
                font-size: 64px;
            }
            .detail-label {
                min-width: 160px;
            }
        }
    </style>
</head>
<body>
    <div class="validator-card">
        <div class="card-header">
            <h1>🔍 QR Code Validator</h1>
            <p>Validasi keaslian dokumen PDF</p>
        </div>

        <div class="card-body">
            <?php
            // Helper: humanize snake_case to Title Case, with special mappings for roles/doc types
            if (!function_exists('humanize_label')) {
                function humanize_label($str, $map = []) {
                    if (!$str) return '';
                    $key = strtolower($str);
                    if (isset($map[$key])) return $map[$key];
                    $label = str_replace(['_', '-'], ' ', $key);
                    return ucwords($label);
                }
            }
            // Get URL parameters
            $docType = $_GET['type'] ?? null;
            $token = $_GET['token'] ?? null;

            // Fallback: support path-style URLs like /validate/<type>/<token> or /validate.php/<type>/<token>
            if (!$docType || !$token) {
                $pathInfo = $_SERVER['PATH_INFO'] ?? '';
                $uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
                $candidates = array_filter([$pathInfo, $uriPath]);
                foreach ($candidates as $p) {
                    if (preg_match('#/validate(?:\.php)?/([^/]+)/([A-Fa-f0-9]{32,128})#', $p, $m)) {
                        if (!$docType) $docType = $m[1];
                        if (!$token) $token = $m[2];
                        break;
                    }
                }
            }

            // Load DB + model for validation
            $APPROOT = dirname(__DIR__);
            require_once $APPROOT . '/config/database.php';
            require_once $APPROOT . '/app/core/Database.php';
            require_once $APPROOT . '/app/models/QRValidation_model.php';
            $qrModel = new QRValidation_model();
            // Don't call ensureTables() here to reduce overhead; assume created during generation

            if (!$docType || !$token) {
                // No parameters - show pending state
                ?>
                <div class="result-box pending">
                    <div class="result-icon">⏳</div>
                    <div class="result-title">Menunggu Scan</div>
                    <div class="result-message">Scan QR code pada dokumen PDF untuk memvalidasi</div>
                </div>

                <div class="warning-box">
                    <strong>⚠️ Endpoint Placeholder</strong><br>
                    Ini adalah contoh halaman validator. Untuk implementasi lengkap, tambahkan:
                    <ul style="margin-top: 10px; margin-left: 20px;">
                        <li>Database query untuk verify document</li>
                        <li>Token signature validation</li>
                        <li>Logging untuk audit trail</li>
                        <li>Rate limiting untuk security</li>
                    </ul>
                </div>
                <?php
            } else {
                // Production-grade validation
                $record = null;
                $isValid = false;
                $reason = '';
                if (preg_match('/^[A-Fa-f0-9]{64}$/', (string)$token)) {
                    $record = $qrModel->findByToken($token);
                    if (!$record) {
                        $reason = 'Token tidak ditemukan';
                    } else if ((int)$record['revoked'] === 1) {
                        $reason = 'Token sudah dicabut';
                    } else if (!empty($record['expires_at']) && strtotime($record['expires_at']) < time()) {
                        $reason = 'Token kadaluarsa';
                    } else {
                        // Anti-tamper minimal: regen expected hash signature test (cannot fully verify original doc now)
                        // Basic heuristic: doc_type must match parameter
                        if ($record['doc_type'] !== $docType) {
                            $reason = 'Tipe dokumen tidak cocok';
                        } else {
                            $isValid = true;
                            $reason = 'OK';
                        }
                    }
                } else {
                    $reason = 'Format token invalid';
                }

                // Log scan
                $qrModel->logScan($token, $isValid, $reason);
                
                // Get scan statistics
                $scanCount = $qrModel->getScanCount($token);
                $firstScan = $qrModel->getFirstScanDate($token);
                
                if ($isValid) {
                    // Friendly labels
                    $roleMap = [
                        'wali_kelas' => 'Wali Kelas',
                        'kepala_madrasah' => 'Kepala Madrasah',
                        'guru' => 'Guru',
                        'siswa' => 'Siswa',
                        'admin' => 'Admin',
                    ];
                    $docMap = [
                        'monitoring_absensi' => 'Monitoring Absensi',
                        'performa_siswa' => 'Performa Siswa',
                        'performa_guru' => 'Performa Guru',
                        'riwayat_jurnal' => 'Riwayat Jurnal',
                        'rincian_absen' => 'Rincian Absensi',
                        'pembayaran' => 'Pembayaran',
                        'invoice_pembayaran' => 'Invoice Pembayaran',
                        'rapor' => 'Rapor',
                        'rapor_sts' => 'Rapor STS',
                        'rapor_sas' => 'Rapor SAS',
                        'rapor_all_sts' => 'Rapor STS (Semua Siswa)',
                        'rapor_all_sas' => 'Rapor SAS (Semua Siswa)',
                        'monitoring_nilai' => 'Monitoring Nilai',
                        'nilai' => 'Nilai',
                        'mapel' => 'Mata Pelajaran',
                        'sksa' => 'Surat Keterangan Siswa Aktif (SKSA)',
                    ];
                    $docTypeNice = humanize_label($docType, $docMap);
                    
                    // Decode meta if present
                    $meta = [];
                    if (!empty($record['meta_json'])) {
                        $decoded = json_decode($record['meta_json'], true);
                        if (is_array($decoded)) { $meta = $decoded; }
                    }
                    ?>
                    <div class="result-box valid">
                        <div class="result-icon">✅</div>
                        <div class="result-title">Dokumen Valid</div>
                        <div class="result-message">Dokumen ini terverifikasi. Token: <?= htmlspecialchars(substr($token,0,12)) ?>…</div>
                    </div>

                    <div class="doc-details">
                        <h3>📄 Detail Dokumen</h3>
                        <div class="detail-row">
                            <span class="detail-label">Nama Dokumen:</span>
                            <span class="detail-value" style="font-weight: bold;"><?= htmlspecialchars($meta['nama_dokumen'] ?? $docTypeNice) ?></span>
                        </div>
                        
                        <?php if (!empty($meta['nomor_surat'])): ?>
                        <div class="detail-row">
                            <span class="detail-label">Nomor Surat:</span>
                            <span class="detail-value" style="font-weight: bold; color: #667eea;"><?= htmlspecialchars($meta['nomor_surat']) ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($meta['nama_siswa'])): ?>
                        <div class="detail-row">
                            <span class="detail-label">Nama Siswa:</span>
                            <span class="detail-value"><?= htmlspecialchars($meta['nama_siswa']) ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($meta['nisn'])): ?>
                        <div class="detail-row">
                            <span class="detail-label">NISN:</span>
                            <span class="detail-value"><?= htmlspecialchars($meta['nisn']) ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($meta['kelas'])): ?>
                        <div class="detail-row">
                            <span class="detail-label">Kelas:</span>
                            <span class="detail-value"><?= htmlspecialchars($meta['kelas']) ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($meta['tahun_pelajaran'])): ?>
                        <div class="detail-row">
                            <span class="detail-label">Tahun Pelajaran:</span>
                            <span class="detail-value"><?= htmlspecialchars($meta['tahun_pelajaran']) ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($meta['dicetak_oleh'])): ?>
                        <div class="detail-row">
                            <span class="detail-label">Dicetak Oleh:</span>
                            <span class="detail-value">
                                <?= htmlspecialchars($meta['dicetak_oleh']) ?>
                                <?php if (!empty($meta['jabatan_pencetak'])): ?>
                                    <br><small style="color: #666; font-size: 12px;"><?= htmlspecialchars($meta['jabatan_pencetak']) ?></small>
                                <?php endif; ?>
                            </span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($meta['mengetahui'])): ?>
                        <div class="detail-row">
                            <span class="detail-label">Mengetahui:</span>
                            <span class="detail-value">
                                <?= htmlspecialchars($meta['mengetahui']) ?>
                                <?php if (!empty($meta['jabatan_mengetahui'])): ?>
                                    <br><small style="color: #666; font-size: 12px;"><?= htmlspecialchars($meta['jabatan_mengetahui']) ?></small>
                                <?php endif; ?>
                            </span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="detail-row">
                            <span class="detail-label">Token:</span>
                            <span class="detail-value" style="font-family: monospace; font-size: 11px; word-break: break-all; color: #666;">
                                <?= htmlspecialchars($token) ?>
                            </span>
                        </div>
                        <?php if (!empty($meta['fingerprint'])): ?>
                        <div class="detail-row">
                            <span class="detail-label">Fingerprint:</span>
                            <span class="detail-value" style="font-family: monospace; font-size: 12px; word-break: break-all;">
                                <?= htmlspecialchars(substr($meta['fingerprint'],0,16)) ?>…
                            </span>
                        </div>
                        <?php endif; ?>
                        <?php
                        // Show rapor-specific fields if available
                        if (strpos($docType, 'rapor') === 0) {
                        ?>
                        <?php if (!empty($meta['semester'])): ?>
                        <div class="detail-row">
                            <span class="detail-label">Semester:</span>
                            <span class="detail-value"><?= htmlspecialchars($meta['semester']) ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($meta['jenis'])): ?>
                        <div class="detail-row">
                            <span class="detail-label">Jenis:</span>
                            <span class="detail-value"><?= htmlspecialchars(strtoupper($meta['jenis'])) ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($meta['rata_rata'])): ?>
                        <div class="detail-row">
                            <span class="detail-label">Rata-rata:</span>
                            <span class="detail-value"><?= htmlspecialchars(number_format((float)$meta['rata_rata'], 2)) ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($meta['jumlah_siswa'])): ?>
                        <div class="detail-row">
                            <span class="detail-label">Jumlah Siswa:</span>
                            <span class="detail-value"><?= (int)$meta['jumlah_siswa'] ?></span>
                        </div>
                        <?php endif; ?>
                        <?php } ?>
                        <div class="detail-row">
                            <span class="detail-label">Status:</span>
                            <span class="detail-value" style="color: #28a745; font-weight: bold;">✓ Terverifikasi</span>
                        </div>
                        <?php
                        // Timezone handling: show WIB (Asia/Jakarta)
                        $dtValidate = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
                        $printedAtLocal = null;
                        if (!empty($meta['tanggal_cetak'])) {
                            try {
                                $printedUtc = new DateTime($meta['tanggal_cetak'], new DateTimeZone('UTC'));
                                $printedUtc->setTimezone(new DateTimeZone('Asia/Jakarta'));
                                $printedAtLocal = $printedUtc->format('d F Y H:i:s');
                            } catch (Exception $e) {}
                        } elseif (!empty($meta['printed_at'])) {
                            try {
                                $printedUtc = new DateTime($meta['printed_at'], new DateTimeZone('UTC'));
                                $printedUtc->setTimezone(new DateTimeZone('Asia/Jakarta'));
                                $printedAtLocal = $printedUtc->format('d F Y H:i:s');
                            } catch (Exception $e) {}
                        }
                        ?>
                        <?php if ($printedAtLocal): ?>
                        <div class="detail-row">
                            <span class="detail-label">Tanggal Cetak:</span>
                            <span class="detail-value"><?= htmlspecialchars($printedAtLocal) ?> WIB</span>
                        </div>
                        <?php endif; ?>
                        <div class="detail-row">
                            <span class="detail-label">Divalidasi:</span>
                            <span class="detail-value"><?= $dtValidate->format('d F Y H:i:s') ?> WIB</span>
                        </div>
                        <?php if (!empty($meta['printed_by'])): ?>
                        <div class="detail-row">
                            <span class="detail-label">User ID:</span>
                            <?php $roleNice = !empty($meta['printed_role']) ? humanize_label($meta['printed_role'], $roleMap) : null; ?>
                            <span class="detail-value"><?= htmlspecialchars($meta['printed_by']) ?><?= $roleNice ? ' ('.htmlspecialchars($roleNice).')' : '' ?></span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Scan Statistics -->
                    <div class="doc-details" style="background: linear-gradient(135deg, #e8eaf6 0%, #f3e5f5 100%); border-left: 4px solid #667eea;">
                        <h3>📊 Statistik Validasi</h3>
                        <div class="detail-row">
                            <span class="detail-label">Total Scan:</span>
                            <span class="detail-value" style="color: #667eea; font-weight: bold; font-size: 18px;">
                                <?= $scanCount ?> kali
                            </span>
                        </div>
                        <?php if ($firstScan): 
                            try {
                                $firstScanUtc = new DateTime($firstScan, new DateTimeZone('UTC'));
                                $firstScanUtc->setTimezone(new DateTimeZone('Asia/Jakarta'));
                                $firstScanLocal = $firstScanUtc->format('d F Y H:i:s');
                            } catch (Exception $e) {
                                $firstScanLocal = $firstScan;
                            }
                        ?>
                        <div class="detail-row">
                            <span class="detail-label">Scan Pertama:</span>
                            <span class="detail-value"><?= htmlspecialchars($firstScanLocal) ?> WIB</span>
                        </div>
                        <?php endif; ?>
                        <div class="detail-row">
                            <span class="detail-label">Scan Terakhir:</span>
                            <span class="detail-value"><?= $dtValidate->format('d F Y H:i:s') ?> WIB</span>
                        </div>
                    </div>

                    <?php
                } else {
                    ?>
                    <div class="result-box invalid">
                        <div class="result-icon">❌</div>
                        <div class="result-title">Dokumen Tidak Valid</div>
                        <div class="result-message">Validasi gagal: <?= htmlspecialchars($reason) ?></div>
                    </div>

                    <div class="doc-details">
                        <h3>⚠️ Informasi Error</h3>
                        <div class="detail-row">
                            <span class="detail-label">Catatan:</span>
                            <span class="detail-value" style="color: #dc3545;"><?= htmlspecialchars($reason) ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Waktu Check:</span>
                            <span class="detail-value"><?= date('d F Y H:i:s') ?></span>
                        </div>
                    </div>

                    <button class="btn-primary" onclick="window.location.href='<?= $_SERVER['PHP_SELF'] ?>'">
                        Scan Ulang QR Code
                    </button>
                    <?php
                }
            }
            ?>
        </div>
    </div>

    <script>
        console.log('QR Validator Page');
        console.log('Doc Type:', '<?= $docType ?? "N/A" ?>');
        console.log('Token:', '<?= $token ? substr($token, 0, 16) . "..." : "N/A" ?>');
    </script>
</body>
</html>
