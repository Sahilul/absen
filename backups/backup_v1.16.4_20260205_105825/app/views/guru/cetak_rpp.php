<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>RPP - <?= htmlspecialchars($rpp['nama_mapel'] ?? 'RPP') ?></title>
    <style>
        body { font-family: "Times New Roman", Times, serif; font-size: 12pt; line-height: 1.35; }
        .header { text-align: center; margin-bottom: 6px; }
        .kop { font-weight: 700; font-size: 12pt; }
        .subkop { font-size: 10pt; }
        .section { margin-top: 8px; margin-bottom: 6px; }
        .section-title { font-weight: 700; font-size: 12pt; margin-bottom: 4px; border-bottom: 1px solid #333; padding-bottom: 2px; }
        .field-group { margin-left: 10px; margin-bottom: 4px; }
        .field-label { font-weight: 600; color: #333; }
        .field-value { margin-top: 2px; padding-left: 8px; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .info-table td { padding: 4px 8px; border: 1px solid #bbb; }
        .info-table .label { background-color: #f3f4f6; font-weight: 600; width: 30%; }
        h2 { text-align: center; margin: 10px 0; font-size: 14pt; }
        .subtitle { text-align: center; margin-bottom: 8px; font-size: 11pt; }
        .footer-info { font-size: 10pt; margin-top: 12px; border-top: 1px solid #ccc; padding-top: 6px; }
        .qr-container { position: absolute; right: 14px; bottom: 14px; text-align: right; }
    </style>
</head>
<body>
    <!-- Header / Kop Surat -->
    <?php
    // Generate kop HTML from uploaded image (follow patterns from siswa/wali_kelas views)
    $kopHTML = '';
    if (!empty($pengaturan['kop_rapor'])) {
        $kopPath = 'public/img/kop/' . $pengaturan['kop_rapor'];
        if (file_exists($kopPath)) {
            $imageData = base64_encode(file_get_contents($kopPath));
            $imageType = pathinfo($kopPath, PATHINFO_EXTENSION);
            $imageSrc = 'data:image/' . $imageType . ';base64,' . $imageData;
            $kopHTML = '<img src="' . $imageSrc . '" style="max-width: 100%; height: auto; max-height: 110px;">';
        }
    }
    ?>
    <div class="header">
        <?php if (!empty($kopHTML)): ?>
            <?= $kopHTML ?>
        <?php else: ?>
            <div class="kop"><?= htmlspecialchars($pengaturan['nama_madrasah'] ?? 'NAMA MADRASAH'); ?></div>
            <div class="subkop"><?= htmlspecialchars($pengaturan['alamat_madrasah'] ?? 'Alamat Madrasah'); ?></div>
        <?php endif; ?>
    </div>

    <h2>RENCANA PELAKSANAAN PEMBELAJARAN (RPP)</h2>
    
    <!-- Info Dasar (dilebur sesuai permintaan) -->
    <?php $semesterNama = $_SESSION['nama_semester_aktif'] ?? ($rpp['nama_semester'] ?? '-'); ?>
    <table class="info-table">
        <tr>
            <td class="label">Mata Pelajaran</td>
            <td colspan="3"><?= htmlspecialchars(($rpp['nama_mapel'] ?? '-') . ' Kelas ' . ($rpp['nama_kelas'] ?? '-')) ?></td>
        </tr>
        <tr>
            <td class="label">Nama Madrasah</td>
            <td><?= htmlspecialchars($rpp['nama_madrasah'] ?? '-') ?></td>
            <td class="label">Alokasi Waktu</td>
            <td><?= htmlspecialchars($rpp['alokasi_waktu'] ?? '-') ?></td>
        </tr>
        <tr>
            <td class="label">Guru Pengajar</td>
            <td><?= htmlspecialchars($rpp['nama_guru'] ?? '-') ?></td>
            <td class="label">Semester</td>
            <td><?= htmlspecialchars($semesterNama) ?></td>
        </tr>
        <tr>
            <td class="label">Tempat & Tanggal</td>
            <td colspan="3">
                <?= htmlspecialchars($pengaturan['tempat_cetak'] ?? ($pengaturan['kota_madrasah'] ?? 'Tempat')) ?>,
                <?= htmlspecialchars($rpp['tanggal_rpp'] ?? ($rpp['created_at'] ?? date('Y-m-d'))) ?>
            </td>
        </tr>
        <tr>
            <td class="label">Kepala Madrasah</td>
            <td colspan="3"><?= htmlspecialchars($pengaturan['nama_kepala_madrasah'] ?? '-') ?></td>
        </tr>
    </table>

    <?php 
    // Decode field values
    $fieldValues = [];
    if (!empty($rpp['rpp_field_values'])) {
        $fieldValues = json_decode($rpp['rpp_field_values'], true) ?: [];
    }
    ?>

    <!-- Dynamic Sections -->
    <?php if (!empty($sections)): ?>
        <?php foreach ($sections as $idx => $section): ?>
            <div class="section">
                <div class="section-title"><?= chr(65 + $idx) ?>. <?= htmlspecialchars($section['nama_section']) ?></div>
                
                <?php if (!empty($section['fields'])): ?>
                    <?php foreach ($section['fields'] as $field): ?>
                        <?php 
                        $fieldKey = 'field_' . $field['id_field'];
                        $value = $fieldValues[$fieldKey] ?? '';
                        ?>
                        
                        <?php if (!empty($value)): ?>
                            <div class="field-group">
                                <span class="field-label"><?= htmlspecialchars($field['nama_field']) ?>:</span>
                                
                                <?php if ($field['tipe_input'] === 'file'): ?>
                                    <div class="field-value"><em>[File: <?= htmlspecialchars($value) ?>]</em></div>
                                <?php elseif ($field['tipe_input'] === 'textarea'): ?>
                                    <div class="field-value"><?= $value ?></div>
                                <?php else: ?>
                                    <div class="field-value"><?= nl2br(htmlspecialchars($value)) ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <!-- Fallback to legacy fields -->
        <div class="section">
            <div class="section-title">A. Identifikasi</div>
            <?php if (!empty($rpp['materi_pelajaran'])): ?>
                <div class="field-group">
                    <span class="field-label">Materi Pembelajaran:</span>
                    <div class="field-value"><?= nl2br(htmlspecialchars($rpp['materi_pelajaran'])) ?></div>
                </div>
            <?php endif; ?>
            <?php if (!empty($rpp['dimensi_profil_lulusan'])): ?>
                <div class="field-group">
                    <span class="field-label">Dimensi/Profil Lulusan:</span>
                    <div class="field-value"><?= nl2br(htmlspecialchars($rpp['dimensi_profil_lulusan'])) ?></div>
                </div>
            <?php endif; ?>
        </div>

        <div class="section">
            <div class="section-title">B. Desain Pembelajaran</div>
            <?php if (!empty($rpp['capaian_pembelajaran'])): ?>
                <div class="field-group">
                    <span class="field-label">Capaian Pembelajaran:</span>
                    <div class="field-value"><?= nl2br(htmlspecialchars($rpp['capaian_pembelajaran'])) ?></div>
                </div>
            <?php endif; ?>
            <?php if (!empty($rpp['tujuan_pembelajaran'])): ?>
                <div class="field-group">
                    <span class="field-label">Tujuan Pembelajaran:</span>
                    <div class="field-value"><?= nl2br(htmlspecialchars($rpp['tujuan_pembelajaran'])) ?></div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Footer: Informasi Dokumen -->
    <?php
        // WIB time (UTC+7) based on server time; adjust if needed
        $tz = new DateTimeZone('Asia/Jakarta');
        $dt = new DateTime('now', $tz);
        $tanggalWIB = $dt->format('d F Y');
        $jamWIB = $dt->format('H:i');
        $dicetakOleh = $_SESSION['user_nama_lengkap'] ?? ($_SESSION['nama_lengkap'] ?? 'Pengguna');
    ?>
    <div class="footer-info" style="border: 1px solid #bbb; padding: 8px; border-radius: 6px; background: #f9fafb;">
        <b>Informasi Dokumen:</b><br>
        Dokumen ini dicetak pada <?= htmlspecialchars($tanggalWIB) ?> pukul <?= htmlspecialchars($jamWIB) ?> WIB oleh <?= htmlspecialchars($dicetakOleh) ?>.<br>
        Dokumen ini sah dan terverifikasi dengan QR Code di pojok kanan bawah halaman.
    </div>
    <!-- QR akan disisipkan otomatis oleh PDFQRHelper di controller -->
</body>
</html>