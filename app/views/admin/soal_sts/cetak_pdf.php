<?php
// File: app/views/admin/soal_sts/cetak_pdf.php
// Template HTML untuk Dompdf - Cetak Soal STS
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
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
        }
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
        .exam-header {
            text-align: center;
            margin-bottom: 6px;
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
        .jawaban-line {
            margin-left: 18px;
            margin-top: 2px;
        }
        .jawaban-line .line {
            border-bottom: 1px dotted #000;
            height: 18px;
            margin-bottom: 1px;
        }
        .sub-instruction {
            font-size: 9pt;
            margin-bottom: 4px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <!-- Kop Rapor -->
    <?php if ($kopRapor && !empty($kopRapor['kop_rapor'])): ?>
        <div class="kop-container">
            <?php
            $kopPath = APPROOT . '/public/img/kop/' . $kopRapor['kop_rapor'];
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
        <h2>SOAL SUMATIF TENGAH SEMESTER (STS)</h2>
        <p>Tahun Pelajaran <?= htmlspecialchars($semester['nama_semester'] ?? ''); ?></p>
    </div>

    <div class="exam-info">
        <table>
            <tr>
                <td>Mata Pelajaran</td>
                <td>: <?= htmlspecialchars($pengaturan['nama_mapel']); ?></td>
                <td style="padding-left: 30px;">Kelas</td>
                <td>: <?= htmlspecialchars($kelas['nama_kelas']); ?></td>
            </tr>
            <tr>
                <td>Waktu</td>
                <td>: <?= $pengaturan['waktu_pengerjaan']; ?> Menit</td>
                <td style="padding-left: 30px;">Hari/Tanggal</td>
                <td>: ................................</td>
            </tr>
        </table>
    </div>

    <!-- Petunjuk Umum -->
    <?php if (!empty($pengaturan['petunjuk_umum'])): ?>
        <div class="petunjuk">
            <h4>Petunjuk Umum:</h4>
            <p><?= nl2br(htmlspecialchars($pengaturan['petunjuk_umum'])); ?></p>
        </div>
    <?php endif; ?>

    <!-- Pilihan Ganda -->
    <?php
    $sectionNum = 0;
    $romanNums = ['', 'I', 'II', 'III'];
    ?>
    <?php if (!empty($soal_pg)): ?>
        <?php $sectionNum++; ?>
        <div class="section-title"><?= $romanNums[$sectionNum]; ?>. Pilihan Ganda</div>
        <p class="sub-instruction">Pilihlah jawaban yang paling tepat!</p>
        <?php foreach ($soal_pg as $soal): ?>
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
    <?php if (!empty($soal_isian)): ?>
        <?php $sectionNum++; ?>
        <div class="section-title"><?= $romanNums[$sectionNum]; ?>. Isian Singkat</div>
        <p class="sub-instruction">Isilah titik-titik berikut dengan jawaban yang tepat!</p>
        <?php foreach ($soal_isian as $soal): ?>
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
    <?php if (!empty($soal_essay)): ?>
        <?php $sectionNum++; ?>
        <div class="section-title"><?= $romanNums[$sectionNum]; ?>. Essay</div>
        <p class="sub-instruction">Jawablah pertanyaan berikut dengan jelas dan tepat!</p>
        <?php foreach ($soal_essay as $soal): ?>
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
</body>
</html>
