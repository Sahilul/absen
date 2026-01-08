<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $data['judul'] ?? 'Jadwal Pelajaran'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print {
                display: none;
            }

            .page-break {
                page-break-before: always;
            }

            body {
                -webkit-print-color-adjust: exact;
            }
        }

        .table-border {
            border: 1px solid #000;
            border-collapse: collapse;
        }

        .table-border th,
        .table-border td {
            border: 1px solid #000;
            padding: 4px;
        }
    </style>
</head>

<body class="bg-white p-8">

    <div class="no-print fixed top-4 right-4 flex gap-2">
        <button onclick="window.print()"
            class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 font-bold">
            <i class="fas fa-print"></i> Cetak / Simpan PDF
        </button>
        <button onclick="window.close()"
            class="bg-gray-500 text-white px-4 py-2 rounded shadow hover:bg-gray-600 font-bold">
            Tutup
        </button>
    </div>

    <?php
    $pengaturan = $data['pengaturan'] ?? [];
    $tp = $data['tp_aktif'] ?? [];
    $hariList = explode(',', $pengaturan['hari_aktif'] ?? 'Senin,Selasa,Rabu,Kamis,Jumat,Sabtu');
    $jamList = $data['jam_list'] ?? [];
    // Filter jam aktif only
    $jamPelajaran = array_filter($jamList, function ($j) {
        return !$j['is_istirahat']; });

    // Prepare Data
    // $jadwalMap[id_kelas][hari][id_jam] = $item
    $jadwalMap = [];
    foreach ($data['jadwal_semua'] as $row) {
        $jadwalMap[$row['id_kelas']][$row['hari']][$row['id_jam']] = $row;
    }

    // Helper function for Roman sorting
    function romanToIntCetak($roman)
    {
        $romans = ['VII' => 7, 'VIII' => 8, 'IX' => 9, 'X' => 10, 'XI' => 11, 'XII' => 12, 'I' => 1, 'II' => 2, 'III' => 3, 'IV' => 4, 'V' => 5, 'VI' => 6];
        foreach ($romans as $r => $v) {
            if (strpos($roman, $r) === 0)
                return $v;
        }
        return 999;
    }

    $kelasList = $data['kelas_list'];
    usort($kelasList, function ($a, $b) {
        return romanToIntCetak($a['nama_kelas']) - romanToIntCetak($b['nama_kelas']);
    });
    ?>

    <?php foreach ($kelasList as $index => $kelas): ?>
        <div class="<?= $index > 0 ? 'page-break' : ''; ?> mb-8">
            <!-- Header Kop -->
            <div class="text-center border-b-2 border-black pb-4 mb-4">
                <h1 class="text-2xl font-bold uppercase"><?= $pengaturan['nama_madrasah'] ?? 'SEKOLAH'; ?></h1>
                <h2 class="text-xl font-bold">JADWAL PELAJARAN TAHUN <?= $tp['tahun_pelajaran'] ?? ''; ?></h2>
                <h3 class="text-lg font-semibold">KELAS: <?= htmlspecialchars($kelas['nama_kelas']); ?></h3>
            </div>

            <!-- Table -->
            <table class="w-full table-border text-xs">
                <thead>
                    <tr class="bg-gray-200 text-center">
                        <th class="w-16">Jam Ke</th>
                        <th class="w-20">Waktu</th>
                        <?php foreach ($hariList as $hari): ?>
                            <th><?= $hari; ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($jamPelajaran as $jam): ?>
                        <tr>
                            <td class="text-center font-bold"><?= $jam['jam_ke']; ?></td>
                            <td class="text-center">
                                <?= substr($jam['waktu_mulai'], 0, 5) . ' - ' . substr($jam['waktu_selesai'], 0, 5); ?>
                            </td>
                            <?php foreach ($hariList as $hari): ?>
                                <td class="text-center align-top h-12">
                                    <?php
                                    $item = $jadwalMap[$kelas['id_kelas']][$hari][$jam['id_jam']] ?? null;
                                    if ($item):
                                        ?>
                                        <div class="font-bold text-sm"><?= htmlspecialchars($item['nama_mapel']); ?></div>
                                        <div class="text-[10px] italic"><?= htmlspecialchars($item['nama_guru']); ?></div>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>

                        <!-- Check global istirahat slot from jam_pelajaran table if any -->
                        <!-- But user uses custom table. For simplicty, we don't inject custom breaks here yet -->
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="mt-4 flex justify-between text-sm">
                <div>
                    <!-- Keterangan codes if needed -->
                </div>
                <div class="text-center mr-8">
                    <p><?= $pengaturan['tempat_cetak'] ?? 'Kota'; ?>, <?= date('d F Y'); ?></p>
                    <p>Kepala Madrasah</p>
                    <br><br><br>
                    <p class="font-bold underline"><?= $pengaturan['nama_kepala_madrasah'] ?? '________________'; ?></p>
                    <p>NIP. <?= $pengaturan['nip_kepala_madrasah'] ?? '................'; ?></p>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

</body>

</html>