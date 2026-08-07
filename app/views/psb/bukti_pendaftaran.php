<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Pendaftaran - <?= htmlspecialchars($data['pendaftar']['no_pendaftaran']); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #333;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
        }

        .header {
            text-align: center;
            border-bottom: 3px double #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 18pt;
            margin-bottom: 5px;
        }

        .header h2 {
            font-size: 14pt;
            font-weight: normal;
            color: #555;
        }

        .no-daftar {
            background: #f5f5f5;
            text-align: center;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
        }

        .no-daftar span {
            font-size: 24pt;
            font-family: monospace;
            font-weight: bold;
            letter-spacing: 2px;
        }

        .section {
            margin-bottom: 20px;
        }

        .section-title {
            font-weight: bold;
            background: #f0f0f0;
            padding: 8px 10px;
            margin-bottom: 10px;
            border-left: 4px solid #0ea5e9;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table td {
            padding: 6px 10px;
            vertical-align: top;
        }

        table td:first-child {
            width: 180px;
            color: #666;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10pt;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }

        .note {
            background: #fff8dc;
            border: 1px solid #f0e68c;
            padding: 10px;
            margin-top: 20px;
            font-size: 10pt;
        }

        @media print {
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <?php $p = $data['pendaftar']; ?>

    <div class="container">
        <!-- Print Button -->
        <div class="no-print" style="text-align: right; margin-bottom: 15px;">
            <button onclick="window.print()"
                style="padding: 10px 20px; cursor: pointer; background: #0ea5e9; color: white; border: none; border-radius: 5px;">
                🖨️ Cetak
            </button>
            <button onclick="window.close()"
                style="padding: 10px 20px; cursor: pointer; margin-left: 5px; background: #eee; border: 1px solid #ccc; border-radius: 5px;">
                Tutup
            </button>
        </div>

        <div class="header">
            <h1>BUKTI PENDAFTARAN SISWA BARU</h1>
            <h2><?= htmlspecialchars($p['nama_lembaga']); ?> - <?= htmlspecialchars($p['nama_periode']); ?></h2>
        </div>

        <div class="no-daftar">
            <p style="font-size: 10pt; color: #666; margin-bottom: 5px;">Nomor Pendaftaran</p>
            <span><?= htmlspecialchars($p['no_pendaftaran']); ?></span>
        </div>

        <div class="section">
            <div class="section-title">DATA PRIBADI</div>
            <table>
                <tr>
                    <td>Nama Lengkap</td>
                    <td>: <b><?= htmlspecialchars($p['nama_lengkap']); ?></b></td>
                </tr>
                <tr>
                    <td>NISN</td>
                    <td>: <?= htmlspecialchars($p['nisn'] ?: '-'); ?></td>
                </tr>
                <tr>
                    <td>Jenis Kelamin</td>
                    <td>: <?= $p['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?></td>
                </tr>
                <tr>
                    <td>Tempat, Tgl Lahir</td>
                    <td>: <?= htmlspecialchars($p['tempat_lahir'] ?: '-'); ?>,
                        <?= $p['tanggal_lahir'] ? date('d/m/Y', strtotime($p['tanggal_lahir'])) : '-'; ?></td>
                </tr>
                <tr>
                    <td>Agama</td>
                    <td>: <?= htmlspecialchars($p['agama'] ?: '-'); ?></td>
                </tr>
                <tr>
                    <td>Alamat</td>
                    <td>: <?= htmlspecialchars($p['alamat'] ?: '-'); ?></td>
                </tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">KONTAK</div>
            <table>
                <tr>
                    <td>No. HP/WA</td>
                    <td>: <?= htmlspecialchars($p['no_hp'] ?: '-'); ?></td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td>: <?= htmlspecialchars($p['email'] ?: '-'); ?></td>
                </tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">ORANG TUA</div>
            <table>
                <tr>
                    <td>Nama Ayah</td>
                    <td>: <?= htmlspecialchars($p['nama_ayah'] ?: '-'); ?></td>
                </tr>
                <tr>
                    <td>Nama Ibu</td>
                    <td>: <?= htmlspecialchars($p['nama_ibu'] ?: '-'); ?></td>
                </tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">PENDAFTARAN</div>
            <table>
                <tr>
                    <td>Jalur Pendaftaran</td>
                    <td>: <b><?= htmlspecialchars($p['nama_jalur']); ?></b></td>
                </tr>
                <tr>
                    <td>Asal Sekolah</td>
                    <td>: <?= htmlspecialchars($p['asal_sekolah'] ?: '-'); ?></td>
                </tr>
                <tr>
                    <td>Tahun Lulus</td>
                    <td>: <?= $p['tahun_lulus'] ?: '-'; ?></td>
                </tr>
                <tr>
                    <td>Tanggal Daftar</td>
                    <td>: <?= date('d/m/Y H:i', strtotime($p['tanggal_daftar'])); ?></td>
                </tr>
            </table>
        </div>

        <div class="note">
            <b>Catatan Penting:</b><br>
            1. Simpan bukti pendaftaran ini dengan baik.<br>
            2. Cek status pendaftaran secara berkala melalui website.<br>
            3. Siapkan dokumen asli untuk verifikasi.<br>
            4. Hubungi panitia jika ada pertanyaan.
        </div>

        <div class="footer">
            Dicetak pada: <?= date('d/m/Y H:i'); ?> | Nomor: <?= htmlspecialchars($p['no_pendaftaran']); ?>
        </div>
    </div>
</body>

</html>