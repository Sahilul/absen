<?php
// File: app/views/admin/kartu_ujian/cetak_kartu.php
// v1.37.0 - Cetak Kartu Ujian: 8 kartu per A4 (2 x 4)
// Isi: kop lembaga, nama ujian, nama, NISN, kelas, QR verifikasi, mengetahui kepala + nama kepala.
$ujian = $data['ujian'];
$peserta = $data['peserta'];
$e = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $e($data['judul']); ?></title>
    <script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
    <style>
        @page { size: A4 portrait; margin: 8mm; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, Helvetica, sans-serif; color: #000; background: #e5e7eb; }
        .toolbar { position: sticky; top: 0; z-index: 10; background: #fff; border-bottom: 1px solid #d1d5db; padding: 10px 16px; display: flex; gap: 10px; align-items: center; }
        .toolbar h1 { font-size: 15px; flex: 1; }
        .toolbar button, .toolbar a { background: #16a34a; color: #fff; border: 0; border-radius: 6px; padding: 8px 14px; font-size: 13px; cursor: pointer; text-decoration: none; }
        .toolbar a { background: #64748b; }
        .page { width: 194mm; margin: 10px auto; background: #fff; display: grid; grid-template-columns: 1fr 1fr; grid-auto-rows: 69mm; gap: 2mm; padding: 0; page-break-after: always; break-after: page; }
        .page:last-child { page-break-after: auto; break-after: auto; }
        .kartu { border: 1px solid #000; padding: 2mm 2.5mm; display: flex; flex-direction: column; overflow: hidden; }
        .kop img { width: 100%; height: auto; display: block; }
        .kop-teks { text-align: center; font-weight: bold; font-size: 11pt; border-bottom: 2px solid #000; padding-bottom: 1mm; }
        .judul { text-align: center; margin: 1.2mm 0 1mm; }
        .judul .t1 { font-size: 9pt; font-weight: bold; letter-spacing: .5px; }
        .judul .t2 { font-size: 8pt; font-weight: bold; text-transform: uppercase; }
        table.info { width: 100%; font-size: 8.5pt; border-collapse: collapse; }
        table.info td { padding: .4mm 0; vertical-align: top; }
        table.info td.l { width: 13mm; }
        table.info td.c { width: 3mm; }
        table.info td.v { font-weight: bold; }
        .bawah { margin-top: auto; display: flex; justify-content: space-between; align-items: flex-end; }
        .qr { width: 16mm; height: 16mm; }
        .qr img, .qr svg { width: 16mm !important; height: 16mm !important; display: block; }
        .ttd { text-align: center; font-size: 7.5pt; min-width: 45mm; }
        .ttd .ruang { height: 9mm; }
        .ttd .nama { font-weight: bold; text-decoration: underline; }
        .kosong { text-align: center; padding: 40px; font-size: 14px; }
        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .page { margin: 0; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <h1>Kartu Ujian — <?= $e($ujian['nama_ujian']); ?> (<?= count($peserta); ?> siswa)</h1>
        <a href="<?= BASEURL; ?>/kartuUjian">Kembali</a>
        <button onclick="window.print()">Cetak</button>
    </div>

    <?php if (empty($peserta)): ?>
        <div class="kosong">Tidak ada siswa untuk filter ini.</div>
    <?php endif; ?>

    <?php foreach (array_chunk($peserta, 8) as $halaman): ?>
        <div class="page">
            <?php foreach ($halaman as $p): $l = $p['lembaga']; ?>
                <div class="kartu">
                    <div class="kop">
                        <?php if (!empty($l['kop_url'])): ?>
                            <img src="<?= $e($l['kop_url']); ?>" alt="Kop">
                        <?php else: ?>
                            <div class="kop-teks"><?= $e($l['nama_lembaga'] ?? 'Madrasah'); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="judul">
                        <div class="t1">KARTU PESERTA UJIAN</div>
                        <div class="t2"><?= $e($ujian['nama_ujian']); ?></div>
                    </div>
                    <table class="info">
                        <tr><td class="l">Nama</td><td class="c">:</td><td class="v"><?= $e($p['nama_siswa']); ?></td></tr>
                        <tr><td class="l">NISN</td><td class="c">:</td><td class="v"><?= $e($p['nisn'] ?: '-'); ?></td></tr>
                        <tr><td class="l">Kelas</td><td class="c">:</td><td class="v"><?= $e($p['nama_kelas']); ?></td></tr>
                    </table>
                    <div class="bawah">
                        <div class="qr" data-qr="<?= $e($p['verify_url']); ?>"></div>
                        <div class="ttd">
                            <div>Mengetahui,</div>
                            <div>Kepala <?= $e($l['nama_lembaga'] ?? 'Madrasah'); ?></div>
                            <div class="ruang"></div>
                            <div class="nama"><?= $e($l['nama_kepala'] ?? '........................'); ?></div>
                            <?php if (!empty($l['nip_kepala'])): ?>
                                <div>NIP. <?= $e($l['nip_kepala']); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>

    <script>
        document.querySelectorAll('.qr[data-qr]').forEach(function (el) {
            try {
                var qr = qrcode(0, 'M');
                qr.addData(el.getAttribute('data-qr'));
                qr.make();
                el.innerHTML = qr.createSvgTag({ cellSize: 2, margin: 0 });
            } catch (err) { console.error(err); }
        });
    </script>
</body>
</html>
