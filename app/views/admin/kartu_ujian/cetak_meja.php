<?php
// File: app/views/admin/kartu_ujian/cetak_meja.php
// v1.37.1 - Cetak Kartu Meja: 6 kartu per A4 (2 x 3), huruf besar, ringkas.
// Isi: kop lembaga, nama ujian, nama, NISN, kelas.
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
    <style>
        @page { size: A4 portrait; margin: 8mm; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, Helvetica, sans-serif; color: #000; background: #e5e7eb; }
        .toolbar { position: sticky; top: 0; z-index: 10; background: #fff; border-bottom: 1px solid #d1d5db; padding: 10px 16px; display: flex; gap: 10px; align-items: center; }
        .toolbar h1 { font-size: 15px; flex: 1; }
        .toolbar button, .toolbar a { background: #16a34a; color: #fff; border: 0; border-radius: 6px; padding: 8px 14px; font-size: 13px; cursor: pointer; text-decoration: none; }
        .toolbar a { background: #64748b; }
        .page { width: 194mm; margin: 10px auto; background: #fff; display: grid; grid-template-columns: 1fr 1fr; grid-auto-rows: 90mm; gap: 3mm; page-break-after: always; break-after: page; }
        .page:last-child { page-break-after: auto; break-after: auto; }
        .meja { border: 2px solid #000; padding: 3mm; display: flex; flex-direction: column; overflow: hidden; }
        .kop img { width: 100%; height: auto; display: block; }
        .kop-teks { text-align: center; font-weight: bold; font-size: 14pt; border-bottom: 2px solid #000; padding-bottom: 2mm; }
        .judul { text-align: center; margin: 2mm 0; }
        .judul .t1 { font-size: 13pt; font-weight: bold; letter-spacing: 1px; }
        .judul .t2 { font-size: 11pt; font-weight: bold; text-transform: uppercase; margin-top: 1mm; }
        .isi { flex: 1; display: flex; flex-direction: column; justify-content: center; text-align: center; gap: 1.5mm; }
        .nama { font-size: 20pt; font-weight: bold; line-height: 1.2; text-transform: uppercase; word-break: break-word; }
        .lbl { font-size: 10pt; color: #333; }
        .val { font-size: 16pt; font-weight: bold; }
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
        <h1>Kartu Meja — <?= $e($ujian['nama_ujian']); ?> (<?= count($peserta); ?> siswa)</h1>
        <a href="<?= BASEURL; ?>/kartuUjian">Kembali</a>
        <button onclick="window.print()">Cetak</button>
    </div>

    <?php if (empty($peserta)): ?>
        <div class="kosong">Tidak ada siswa untuk filter ini.</div>
    <?php endif; ?>

    <?php foreach (array_chunk($peserta, 6) as $halaman): ?>
        <div class="page">
            <?php foreach ($halaman as $p): $l = $p['lembaga']; ?>
                <div class="meja">
                    <div class="kop">
                        <?php if (!empty($l['kop_url'])): ?>
                            <img src="<?= $e($l['kop_url']); ?>" alt="Kop">
                        <?php else: ?>
                            <div class="kop-teks"><?= $e($l['nama_lembaga'] ?? 'Madrasah'); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="judul">
                        <div class="t1">KARTU MEJA PESERTA</div>
                        <div class="t2"><?= $e($ujian['nama_ujian']); ?></div>
                    </div>
                    <div class="isi">
                        <div class="nama"><?= $e($p['nama_siswa']); ?></div>
                        <div><span class="lbl">NISN:</span> <span class="val"><?= $e($p['nisn'] ?: '-'); ?></span></div>
                        <div><span class="lbl">Kelas:</span> <span class="val"><?= $e($p['nama_kelas']); ?></span></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</body>
</html>
