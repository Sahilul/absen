<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soal STS - <?= htmlspecialchars($data['pengaturan']['nama_mapel']); ?> - <?= htmlspecialchars($data['kelas']['nama_kelas']); ?></title>
    <style>
        @page {
            size: A4;
            margin: 0.8cm 1cm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.15;
            color: #000;
            background: #fff;
        }
        .no-print {
            position: fixed;
            top: 10px;
            right: 10px;
            z-index: 1000;
            display: flex;
            gap: 8px;
        }
        .no-print button, .no-print a {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-family: Arial, sans-serif;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .btn-print {
            background: #4f46e5;
            color: #fff;
        }
        .btn-pdf {
            background: #059669;
            color: #fff;
        }
        .btn-kunci {
            background: #d97706;
            color: #fff;
        }
        .btn-back {
            background: #6b7280;
            color: #fff;
        }
        @media print {
            .no-print { display: none !important; }
            body { background: #fff; }
        }

        /* Kop */
        .kop-container {
            text-align: center;
            margin-bottom: 4px;
        }
        .kop-container img {
            max-width: 100%;
            height: auto;
        }
        .kop-line {
            border-top: 3px double #000;
            margin: 3px 0 6px;
        }

        /* Header Soal */
        .exam-header {
            text-align: center;
            margin-bottom: 8px;
        }
        .exam-header h2 {
            font-size: 13pt;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 2px;
        }
        .exam-info {
            margin-bottom: 6px;
        }
        .exam-info table {
            margin: 0 auto;
            font-size: 10pt;
        }
        .exam-info td {
            padding: 1px 6px;
            text-align: left;
        }

        /* Petunjuk */
        .petunjuk {
            border: 1px solid #000;
            padding: 5px 8px;
            margin-bottom: 8px;
            font-size: 10pt;
        }
        .petunjuk h4 {
            margin-bottom: 2px;
            font-size: 10pt;
        }

        /* Soal */
        .section-title {
            font-size: 11pt;
            font-weight: bold;
            margin: 8px 0 4px;
            padding-bottom: 1px;
            border-bottom: 1px solid #000;
        }
        .soal-item {
            margin-bottom: 6px;
            page-break-inside: avoid;
        }
        .soal-item .nomor {
            font-weight: bold;
        }
        .soal-item .pertanyaan {
            margin-left: 18px;
            white-space: pre-line;
        }
        .soal-item .gambar-soal {
            margin: 2px 0 2px 18px;
            max-width: 180px;
            max-height: 120px;
        }
        .opsi-list {
            margin-left: 18px;
            list-style: none;
            padding: 0;
        }
        .opsi-list li {
            margin-bottom: 1px;
        }
        .opsi-list li.kunci {
            font-weight: bold;
            color: #059669;
        }

        /* Jawaban lines */
        .jawaban-line {
            margin-left: 18px;
            margin-top: 2px;
        }
        .jawaban-line .line {
            border-bottom: 1px dotted #000;
            height: 18px;
            margin-bottom: 1px;
        }
    </style>
</head>
<body>
    <?php
    $p = $data['pengaturan'];
    $kelas = $data['kelas'];
    $kop = $data['kop_rapor'];
    $semester = $data['semester'];
    $soalPG = $data['soal_pg'];
    $soalEssay = $data['soal_essay'];
    $soalIsian = $data['soal_isian'];
    $isKunci = isset($data['mode']) && $data['mode'] === 'kunci';
    ?>

    <!-- Action Buttons (no-print) -->
    <div class="no-print">
        <button class="btn-print" onclick="window.print()">🖨️ Print</button>
        <a class="btn-pdf" href="<?= BASEURL; ?>/soalSts/cetakPDF/<?= $kelas['id_kelas']; ?>/<?= $p['id_mapel']; ?>">📄 PDF</a>
        <?php if (!$isKunci): ?>
            <a class="btn-kunci" href="<?= BASEURL; ?>/soalSts/cetakKunci/<?= $kelas['id_kelas']; ?>/<?= $p['id_mapel']; ?>">🔑 Kunci</a>
        <?php endif; ?>
        <a class="btn-back" href="<?= BASEURL; ?>/soalSts/daftarSoal/<?= $kelas['id_kelas']; ?>/<?= $p['id_mapel']; ?>">← Kembali</a>
    </div>

    <!-- Kop Rapor -->
    <?php if ($kop && !empty($kop['kop_rapor'])): ?>
        <div class="kop-container">
            <?php
            $kopPath = APPROOT . '/public/img/kop/' . $kop['kop_rapor'];
            if (file_exists($kopPath)):
                $imageData = base64_encode(file_get_contents($kopPath));
                $mimeType = safeImageMime($kopPath);
            ?>
                <img src="data:<?= $mimeType; ?>;base64,<?= $imageData; ?>" alt="Kop Surat">
            <?php endif; ?>
        </div>
        <div class="kop-line"></div>
    <?php endif; ?>

    <!-- Header Soal -->
    <div class="exam-header">
        <?php if ($isKunci): ?>
            <h2>KUNCI JAWABAN</h2>
        <?php endif; ?>
        <h2>SOAL SUMATIF TENGAH SEMESTER (STS)</h2>
        <p>Tahun Pelajaran <?= htmlspecialchars($semester['nama_semester'] ?? ''); ?></p>
    </div>

    <div class="exam-info">
        <table>
            <tr>
                <td>Mata Pelajaran</td>
                <td>: <?= htmlspecialchars($p['nama_mapel']); ?></td>
                <td style="padding-left: 30px;">Kelas</td>
                <td>: <?= htmlspecialchars($kelas['nama_kelas']); ?></td>
            </tr>
            <tr>
                <td>Waktu</td>
                <td>: <?= $p['waktu_pengerjaan']; ?> Menit</td>
                <td style="padding-left: 30px;">Hari/Tanggal</td>
                <td>: ................................</td>
            </tr>
        </table>
    </div>

    <!-- Petunjuk Umum -->
    <?php if (!empty($p['petunjuk_umum']) && !$isKunci): ?>
        <div class="petunjuk">
            <h4>Petunjuk Umum:</h4>
            <p><?= nl2br(htmlspecialchars($p['petunjuk_umum'])); ?></p>
        </div>
    <?php endif; ?>

    <?php if ($isKunci): ?>
        <!-- ==================== MODE KUNCI JAWABAN ==================== -->
        <?php
        $sectionNum = 0;
        $romanNums = ['', 'I', 'II', 'III'];
        ?>
        <?php if (!empty($soalPG)): ?>
            <?php $sectionNum++; ?>
            <div class="section-title"><?= $romanNums[$sectionNum]; ?>. Pilihan Ganda</div>
            <table style="width: 100%; font-size: 11pt; border-collapse: collapse;">
                <tr>
                    <?php foreach ($soalPG as $idx => $soal): ?>
                        <td style="padding: 3px 10px; width: 20%;">
                            <?= $soal['nomor_soal']; ?>. <strong><?= $soal['kunci_jawaban'] ?? '-'; ?></strong>
                        </td>
                        <?php if (($idx + 1) % 5 == 0): ?>
                            </tr><tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tr>
            </table>
        <?php endif; ?>

    <?php else: ?>
        <!-- ==================== MODE SOAL ==================== -->
        <?php
        $sectionNum = 0;
        $romanNums = ['', 'I', 'II', 'III'];
        ?>

        <!-- Pilihan Ganda -->
        <?php if (!empty($soalPG)): ?>
            <?php $sectionNum++; ?>
            <div class="section-title"><?= $romanNums[$sectionNum]; ?>. Pilihan Ganda</div>
            <p style="font-size: 9pt; margin-bottom: 4px; font-style: italic;">Pilihlah jawaban yang paling tepat!</p>
            <?php foreach ($soalPG as $soal): ?>
                <div class="soal-item">
                    <span class="nomor"><?= $soal['nomor_soal']; ?>.</span>
                    <span class="pertanyaan"><?= htmlspecialchars($soal['pertanyaan']); ?></span>
                    <?php if (!empty($soal['gambar_soal'])): ?>
                        <?php $imgSrc = soalGambarPrintSrc($soal['gambar_soal']); ?>
                        <?php if ($imgSrc): ?>
                            <br><img src="<?= $imgSrc; ?>" class="gambar-soal" alt="Gambar soal">
                        <?php endif; ?>
                    <?php endif; ?>
                    <ul class="opsi-list">
                        <?php
                        $opsiFields = ['A' => 'opsi_a', 'B' => 'opsi_b', 'C' => 'opsi_c', 'D' => 'opsi_d', 'E' => 'opsi_e'];
                        foreach ($opsiFields as $label => $field):
                            if (!empty($soal[$field])):
                        ?>
                            <li><?= $label; ?>. <?= htmlspecialchars($soal[$field]); ?></li>
                        <?php
                            endif;
                        endforeach;
                        ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Isian Singkat -->
        <?php if (!empty($soalIsian)): ?>
            <?php $sectionNum++; ?>
            <div class="section-title"><?= $romanNums[$sectionNum]; ?>. Isian Singkat</div>
            <p style="font-size: 9pt; margin-bottom: 4px; font-style: italic;">Isilah titik-titik berikut dengan jawaban yang tepat!</p>
            <?php foreach ($soalIsian as $soal): ?>
                <div class="soal-item">
                    <span class="nomor"><?= $soal['nomor_soal']; ?>.</span>
                    <span class="pertanyaan"><?= htmlspecialchars($soal['pertanyaan']); ?></span>
                    <?php if (!empty($soal['gambar_soal'])): ?>
                        <?php $imgSrc = soalGambarPrintSrc($soal['gambar_soal']); ?>
                        <?php if ($imgSrc): ?>
                            <br><img src="<?= $imgSrc; ?>" class="gambar-soal" alt="Gambar soal">
                        <?php endif; ?>
                    <?php endif; ?>
                    <div class="jawaban-line">
                        <div class="line"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Essay -->
        <?php if (!empty($soalEssay)): ?>
            <?php $sectionNum++; ?>
            <div class="section-title"><?= $romanNums[$sectionNum]; ?>. Essay</div>
            <p style="font-size: 9pt; margin-bottom: 4px; font-style: italic;">Jawablah pertanyaan berikut dengan jelas dan tepat!</p>
            <?php foreach ($soalEssay as $soal): ?>
                <div class="soal-item">
                    <span class="nomor"><?= $soal['nomor_soal']; ?>.</span>
                    <span class="pertanyaan"><?= htmlspecialchars($soal['pertanyaan']); ?></span>
                    <?php if (!empty($soal['gambar_soal'])): ?>
                        <?php $imgSrc = soalGambarPrintSrc($soal['gambar_soal']); ?>
                        <?php if ($imgSrc): ?>
                            <br><img src="<?= $imgSrc; ?>" class="gambar-soal" alt="Gambar soal">
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>
