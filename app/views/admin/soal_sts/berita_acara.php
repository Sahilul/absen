<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita Acara & Daftar Hadir STS - <?= htmlspecialchars($data['kelas']['nama_kelas']); ?></title>
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm 10mm;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 9pt;
            line-height: 1.25;
            color: #000;
            background: #e5e7eb;
        }

        /* Each .page = 1 printed landscape A4 sheet */
        .page {
            width: 277mm;
            min-height: 190mm;
            max-height: 190mm;
            overflow: hidden;
            background: #fff;
            margin: 12px auto;
            padding: 8mm 10mm;
            box-shadow: 0 2px 8px rgba(0,0,0,0.12);
            display: flex;
            gap: 8mm;
            page-break-after: always;
        }
        .page:last-child { page-break-after: auto; }

        .col-left { width: 48%; display: flex; flex-direction: column; }
        .col-right { width: 52%; display: flex; flex-direction: column; }
        .col-divider { width: 0; border-left: 1px dashed #aaa; }

        /* Toolbar */
        .no-print {
            position: fixed; top: 8px; right: 8px; z-index: 1000;
            display: flex; gap: 6px; flex-wrap: wrap;
        }
        .no-print button, .no-print a, .no-print select {
            padding: 7px 14px; border: none; border-radius: 6px;
            font-size: 12px; font-family: Arial, sans-serif;
            cursor: pointer; text-decoration: none;
            display: inline-flex; align-items: center; gap: 4px;
        }
        .btn-print { background: #4f46e5; color: #fff; }
        .btn-back { background: #6b7280; color: #fff; }
        .no-print select { background: #fff; border: 1px solid #d1d5db; color: #374151; }

        @media print {
            body { background: #fff; }
            .page { margin: 0; box-shadow: none; width: auto; max-height: none; min-height: auto; }
            .no-print { display: none !important; }
            .col-divider { border-left-color: #ccc; }
        }

        /* ===== BERITA ACARA (left) ===== */
        .ba-title { text-align: center; margin-bottom: 6px; }
        .ba-title h2 { font-size: 11pt; text-decoration: underline; margin-bottom: 1px; }
        .ba-title h3 { font-size: 9.5pt; font-weight: normal; }
        .ba-subtitle { text-align: center; font-size: 9pt; font-weight: bold; margin-bottom: 8px; line-height: 1.3; }

        .ba-info td { padding: 1.5px 0; vertical-align: top; font-size: 8.5pt; }
        .ba-info td:first-child { width: 130px; white-space: nowrap; }
        .ba-info td:nth-child(2) { width: 10px; text-align: center; }

        .ba-body { font-size: 8.5pt; margin-top: 6px; }
        .ba-body p { text-align: justify; text-indent: 20px; margin-bottom: 4px; }
        .ba-body .amplop { margin-left: 20px; font-size: 8.5pt; }
        .ba-body .amplop td { padding: 1px 0; }

        .ba-notes { margin-top: 4px; font-size: 8.5pt; }
        .ba-notes ol { margin-left: 16px; }
        .ba-notes li { padding: 1px 0; }
        .note-line { display: block; border-bottom: 1px dotted #000; min-height: 14px; }

        .ba-sign { width: 100%; margin-top: auto; }
        .ba-sign td { width: 50%; text-align: center; vertical-align: top; padding: 2px 10px; font-size: 8.5pt; }
        .sign-space { height: 45px; }
        .sign-line { border-bottom: 1px solid #000; display: inline-block; min-width: 120px; }

        /* ===== DAFTAR HADIR (right) ===== */
        .dh-title { text-align: center; margin-bottom: 6px; }
        .dh-title h2 { font-size: 11pt; text-decoration: underline; margin-bottom: 1px; }
        .dh-title h3 { font-size: 9.5pt; font-weight: normal; }

        .dh-info td { padding: 1.5px 0; vertical-align: top; font-size: 8.5pt; }
        .dh-info td:first-child { width: 100px; white-space: nowrap; }
        .dh-info td:nth-child(2) { width: 10px; text-align: center; }

        .dh-table { width: 100%; border-collapse: collapse; font-size: 7.5pt; margin-top: 4px; }
        .dh-table th, .dh-table td { border: 1px solid #000; padding: 1px 3px; text-align: center; }
        .dh-table th { background: #f0f0f0; font-weight: bold; font-size: 7.5pt; }
        .dh-table td.nama { text-align: left; padding-left: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 160px; }
        .dh-table td.ttd { height: 14px; }

        .dh-sign { width: 100%; margin-top: auto; padding-top: 4px; }
        .dh-sign td { width: 50%; text-align: center; vertical-align: top; padding: 2px 10px; font-size: 8.5pt; }
    </style>
</head>
<body>
<?php
    $namaKelas = htmlspecialchars($data['kelas']['nama_kelas'] ?? '');
    $namaSekolah = htmlspecialchars($data['kop_rapor']['nama_madrasah'] ?? $data['kop_rapor']['nama_sekolah'] ?? 'MADRASAH TSANAWIYAH SABILILLAH');
    $alamatSekolah = htmlspecialchars($data['kop_rapor']['alamat_madrasah'] ?? $data['kop_rapor']['alamat_sekolah'] ?? '');
    $semesterText = htmlspecialchars($data['semester']['semester'] ?? '');
    $namaTp = htmlspecialchars($data['semester']['nama_tp'] ?? '');
    $siswaList = $data['siswa_list'] ?? [];
    $totalSiswa = count($siswaList);
    $mapelList = $data['mapel_list'] ?? [];
?>

<!-- Toolbar -->
<div class="no-print">
    <select id="mapelSelect" onchange="filterMapel()">
        <option value="all">Semua Mapel</option>
        <?php foreach ($mapelList as $idx => $m): ?>
        <option value="mapel-<?= $idx; ?>"><?= htmlspecialchars($m['nama_mapel']); ?></option>
        <?php endforeach; ?>
    </select>
    <button class="btn-print" onclick="window.print()">🖨️ Cetak</button>
    <a href="<?= BASEURL; ?>/soalSts/daftarMapel/<?= $data['id_kelas']; ?>" class="btn-back">← Kembali</a>
</div>

<?php foreach ($mapelList as $idx => $mapel): ?>
<div class="page mapel-page mapel-<?= $idx; ?>">

    <!-- ========== LEFT: BERITA ACARA ========== -->
    <div class="col-left">
        <div class="ba-title">
            <h2>BERITA ACARA</h2>
            <h3>SUMATIF TENGAH SEMESTER (STS)</h3>
        </div>
        <div class="ba-subtitle">
            <?= strtoupper($namaSekolah); ?><br>
            TAHUN PELAJARAN <?= $namaTp; ?>
        </div>

        <table class="ba-info">
            <tr><td>Hari / Tanggal</td><td>:</td><td>............... / ................................</td></tr>
            <tr><td>Mata Pelajaran</td><td>:</td><td><strong><?= htmlspecialchars($mapel['nama_mapel']); ?></strong></td></tr>
            <tr><td>Sekolah / Madrasah</td><td>:</td><td><?= $namaSekolah; ?></td></tr>
            <tr><td>Kelas / Ruang</td><td>:</td><td><?= $namaKelas; ?> / ..................</td></tr>
            <tr><td>Alamat</td><td>:</td><td><?= $alamatSekolah ?: '..............................................'; ?></td></tr>
            <tr><td>Jumlah Peserta</td><td>:</td><td><?= $totalSiswa; ?> siswa</td></tr>
            <tr><td style="padding-left:14px">a. Hadir</td><td>:</td><td>........... siswa</td></tr>
            <tr><td style="padding-left:14px">b. Tidak Hadir</td><td>:</td><td>........... siswa</td></tr>
        </table>

        <div class="ba-body">
            <p>Pada hari ini .................. tanggal .................. telah dilaksanakan Sumatif Tengah Semester (STS) <?= $semesterText; ?> Tahun Pelajaran <?= $namaTp; ?> di <?= $namaSekolah; ?> dengan tertib dan lancar.</p>
            <p style="text-indent:0">Amplop soal dalam keadaan :</p>
            <table class="amplop">
                <tr><td>☐ Tertutup / Tersegel</td></tr>
                <tr><td>☐ Terbuka</td></tr>
            </table>
            <p style="text-indent:0;margin-top:3px">Amplop dibuka pukul : .............. WIB</p>
        </div>

        <div class="ba-notes">
            <p><strong>Catatan :</strong></p>
            <ol>
                <li><span class="note-line">&nbsp;</span></li>
                <li><span class="note-line">&nbsp;</span></li>
                <li><span class="note-line">&nbsp;</span></li>
            </ol>
        </div>

        <table class="ba-sign">
            <tr><td>Pengawas I</td><td>Pengawas II</td></tr>
            <tr><td class="sign-space"></td><td class="sign-space"></td></tr>
            <tr><td><span class="sign-line">&nbsp;</span></td><td><span class="sign-line">&nbsp;</span></td></tr>
        </table>
    </div>

    <!-- Divider -->
    <div class="col-divider"></div>

    <!-- ========== RIGHT: DAFTAR HADIR ========== -->
    <div class="col-right">
        <div class="dh-title">
            <h2>DAFTAR HADIR PESERTA</h2>
        </div>

        <table class="dh-info" style="margin-bottom:4px;">
            <tr>
                <td>Hari, Tanggal</td><td>:</td><td>.................................</td>
                <td style="width:20px"></td>
                <td>Kelas</td><td>:</td><td><?= $namaKelas; ?></td>
            </tr>
            <tr>
                <td>Mata Pelajaran</td><td>:</td><td><strong><?= htmlspecialchars($mapel['nama_mapel']); ?></strong></td>
                <td></td>
                <td>Ruang</td><td>:</td><td>.................</td>
            </tr>
        </table>

        <table class="dh-table" style="flex:1;">
            <thead>
                <tr>
                    <th style="width:22px">No.</th>
                    <th style="width:70px">Nomor Peserta</th>
                    <th>N a m a</th>
                    <th style="width:80px">Tanda Tangan</th>
                </tr>
            </thead>
            <tbody>
                <?php for ($i = 0; $i < $totalSiswa; $i++): ?>
                <tr>
                    <td><?= $i + 1; ?>.</td>
                    <td></td>
                    <td class="nama"><?= isset($siswaList[$i]) ? htmlspecialchars($siswaList[$i]['nama_siswa']) : ''; ?></td>
                    <td class="ttd"></td>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    </div>

</div>
<?php endforeach; ?>

<script>
function filterMapel() {
    const val = document.getElementById('mapelSelect').value;
    document.querySelectorAll('.mapel-page').forEach(el => {
        el.style.display = (val === 'all' || el.classList.contains(val)) ? '' : 'none';
    });
}
</script>
</body>
</html>
