<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Tugas - <?= htmlspecialchars($data['surat']['nomor_surat'] ?? ''); ?></title>
    <style>
        @page {
            margin: 2cm;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                -webkit-print-color-adjust: exact;
            }
        }

        body {
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
            line-height: 1.6;
            color: #000;
            max-width: 21cm;
            margin: 0 auto;
            padding: 2cm;
            background: #fff;
        }

        .header {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 16pt;
            letter-spacing: 2px;
        }

        .header h2 {
            margin: 5px 0;
            font-size: 14pt;
        }

        .header p {
            margin: 3px 0;
            font-size: 10pt;
        }

        .title {
            text-align: center;
            margin: 30px 0;
        }

        .title h3 {
            margin: 0;
            text-decoration: underline;
            font-size: 14pt;
            letter-spacing: 3px;
        }

        .title p {
            margin: 5px 0;
            font-size: 11pt;
        }

        .content {
            margin: 20px 0;
            text-align: justify;
        }

        .content p {
            margin: 10px 0;
            text-indent: 1.5cm;
        }

        .content table {
            margin: 15px 0;
            width: 100%;
        }

        .content table td {
            padding: 3px 10px;
            vertical-align: top;
        }

        .content table td:first-child {
            width: 150px;
        }

        .signature {
            margin-top: 40px;
            text-align: right;
            padding-right: 50px;
        }

        .signature .box {
            display: inline-block;
            text-align: center;
            min-width: 200px;
        }

        .signature .name {
            margin-top: 80px;
            font-weight: bold;
            text-decoration: underline;
        }

        .signature .nip {
            margin-top: 3px;
        }
    </style>
</head>

<body>
    <?php
    $surat = $data['surat'] ?? [];
    $pengaturan = $data['pengaturan'] ?? [];
    $namaMadrasah = $pengaturan['nama_madrasah'] ?? 'MADRASAH TSANAWIYAH';
    $alamatMadrasah = $pengaturan['alamat_madrasah'] ?? '';
    $telepon = $pengaturan['telepon'] ?? '';
    $namaKepala = $pengaturan['nama_kepala_madrasah'] ?? '';
    $nipKepala = $pengaturan['nip_kepala_madrasah'] ?? '';
    $tempatCetak = $pengaturan['tempat_cetak'] ?? 'Kota';
    ?>

    <div class="no-print" style="position:fixed; top:20px; right:20px;">
        <button onclick="window.print()"
            style="background:#4f46e5; color:#fff; padding:10px 20px; border:none; border-radius:5px; cursor:pointer; font-weight:bold;">
            🖨️ Cetak / Save PDF
        </button>
        <button onclick="window.close()"
            style="background:#6b7280; color:#fff; padding:10px 20px; border:none; border-radius:5px; cursor:pointer; margin-left:5px;">
            ✕ Tutup
        </button>
    </div>

    <div class="header">
        <h1><?= htmlspecialchars(strtoupper($namaMadrasah)); ?></h1>
        <?php if ($alamatMadrasah): ?>
            <p><?= htmlspecialchars($alamatMadrasah); ?></p>
        <?php endif; ?>
        <?php if ($telepon): ?>
            <p>Telp: <?= htmlspecialchars($telepon); ?></p>
        <?php endif; ?>
    </div>

    <div class="title">
        <h3>SURAT TUGAS</h3>
        <p>Nomor: <?= htmlspecialchars($surat['nomor_surat'] ?? '-'); ?></p>
    </div>

    <div class="content">
        <p>Yang bertanda tangan di bawah ini, Kepala <?= htmlspecialchars($namaMadrasah); ?>, dengan ini memberikan
            tugas kepada:</p>

        <table>
            <tr>
                <td>Nama</td>
                <td>: <?= htmlspecialchars($surat['nama_guru'] ?? '-'); ?></td>
            </tr>
            <tr>
                <td>NIP</td>
                <td>: <?= htmlspecialchars($surat['nip'] ?? '-'); ?></td>
            </tr>
            <?php if (!empty($surat['pangkat_golongan'])): ?>
                <tr>
                    <td>Pangkat/Gol.</td>
                    <td>: <?= htmlspecialchars($surat['pangkat_golongan']); ?></td>
                </tr>
            <?php endif; ?>
            <tr>
                <td>Jabatan</td>
                <td>: Guru <?= htmlspecialchars($namaMadrasah); ?></td>
            </tr>
        </table>

        <p>Untuk:</p>
        <p style="text-indent:0; margin-left:1.5cm;"><?= nl2br(htmlspecialchars($surat['perihal'] ?? '-')); ?></p>

        <?php if (!empty($surat['isi_tugas'])): ?>
            <p style="text-indent:0; margin-left:1.5cm;"><?= nl2br(htmlspecialchars($surat['isi_tugas'])); ?></p>
        <?php endif; ?>

        <?php if (!empty($surat['tempat_tugas'])): ?>
            <p>Tempat pelaksanaan: <?= htmlspecialchars($surat['tempat_tugas']); ?></p>
        <?php endif; ?>

        <?php if (!empty($surat['tanggal_mulai'])): ?>
            <p>Waktu pelaksanaan: <?= date('d F Y', strtotime($surat['tanggal_mulai'])); ?>
                <?php if (!empty($surat['tanggal_selesai']) && $surat['tanggal_selesai'] != $surat['tanggal_mulai']): ?>
                    s.d. <?= date('d F Y', strtotime($surat['tanggal_selesai'])); ?>
                <?php endif; ?>
            </p>
        <?php endif; ?>

        <p>Demikian surat tugas ini dibuat untuk dapat dipergunakan sebagaimana mestinya.</p>
    </div>

    <div class="signature">
        <div class="box">
            <p><?= htmlspecialchars($tempatCetak); ?>,
                <?= date('d F Y', strtotime($surat['tanggal_surat'] ?? 'now')); ?></p>
            <p>Kepala Madrasah</p>
            <p class="name"><?= htmlspecialchars($namaKepala ?: '____________________'); ?></p>
            <p class="nip">NIP. <?= htmlspecialchars($nipKepala ?: '............................'); ?></p>
        </div>
    </div>

</body>

</html>