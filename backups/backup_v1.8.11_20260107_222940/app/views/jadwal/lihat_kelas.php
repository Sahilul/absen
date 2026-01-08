<?php
// File: app/views/jadwal/lihat_kelas.php
$kelasList = $data['kelas_list'] ?? [];
$pengaturan = $data['pengaturan'] ?? [];
$jamList = $data['jam_list'] ?? [];
$jadwalList = $data['jadwal_list'] ?? [];
$kelas = $data['kelas'] ?? null;
$idKelas = $data['id_kelas'] ?? null;
$tp = $data['tp_aktif'] ?? null;

$hariAktif = explode(',', $pengaturan['hari_aktif'] ?? 'Senin,Selasa,Rabu,Kamis,Jumat,Sabtu');
$jamAktif = array_filter($jamList, fn($j) => !$j['is_istirahat']);

// Group jadwal by hari and jam
$jadwalMap = [];
foreach ($jadwalList as $j) {
    $jadwalMap[$j['hari']][$j['id_jam']] = $j;
}
?>

<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Jadwal Per Kelas</h1>
            <p class="text-gray-600"><?= $tp ? htmlspecialchars($tp['tahun_pelajaran']) : ''; ?></p>
        </div>
        <div>
            <select onchange="location.href='<?= BASEURL; ?>/jadwal/lihatKelas/' + this.value"
                class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                <option value="">-- Pilih Kelas --</option>
                <?php foreach ($kelasList as $k): ?>
                    <option value="<?= $k['id_kelas']; ?>" <?= $idKelas == $k['id_kelas'] ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($k['nama_kelas']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <?php if (!$kelas): ?>
        <div class="bg-white rounded-xl shadow-sm border p-8 text-center text-gray-500">
            <i data-lucide="list" class="w-12 h-12 mx-auto mb-3 text-gray-300"></i>
            <p>Silakan pilih kelas untuk melihat jadwal</p>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
            <div class="bg-gradient-to-r from-indigo-500 to-purple-500 px-6 py-4">
                <h2 class="text-xl font-bold text-white"><?= htmlspecialchars($kelas['nama_kelas']); ?></h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="py-3 px-4 text-left font-semibold text-gray-600">Jam</th>
                            <th class="py-3 px-4 text-left font-semibold text-gray-600">Waktu</th>
                            <?php foreach ($hariAktif as $hari): ?>
                                <th class="py-3 px-4 text-center font-semibold text-gray-600"><?= $hari; ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php foreach ($jamAktif as $jam): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 px-4 font-medium">Jam <?= $jam['jam_ke']; ?></td>
                                <td class="py-3 px-4 text-gray-500 font-mono text-xs"><?= substr($jam['waktu_mulai'], 0, 5); ?>
                                    - <?= substr($jam['waktu_selesai'], 0, 5); ?></td>
                                <?php foreach ($hariAktif as $hari): ?>
                                    <td class="py-3 px-4 text-center">
                                        <?php if (isset($jadwalMap[$hari][$jam['id_jam']])):
                                            $j = $jadwalMap[$hari][$jam['id_jam']]; ?>
                                            <div class="bg-indigo-50 rounded-lg p-2">
                                                <div class="font-semibold text-indigo-700 text-sm">
                                                    <?= htmlspecialchars($j['nama_mapel']); ?></div>
                                                <div class="text-xs text-indigo-500"><?= htmlspecialchars($j['nama_guru']); ?></div>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-gray-300">-</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</main>