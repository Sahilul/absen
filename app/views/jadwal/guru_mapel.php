<?php
// File: app/views/jadwal/guru_mapel.php
$guruMapelList = $data['guru_mapel_list'] ?? [];
$guruList = $data['guru_list'] ?? [];
$mapelList = $data['mapel_list'] ?? [];
$jadwalDetail = $data['jadwal_detail'] ?? [];

// Group by guru
$guruMapelGrouped = [];
foreach ($guruMapelList as $gm) {
    if (!isset($guruMapelGrouped[$gm['id_guru']])) {
        $guruMapelGrouped[$gm['id_guru']] = [
            'nama_guru' => $gm['nama_guru'],
            'mapel' => []
        ];
    }
    $guruMapelGrouped[$gm['id_guru']]['mapel'][] = $gm['nama_mapel'];
}
?>

<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Guru - Mata Pelajaran</h1>
        <p class="text-gray-600">Kelola mapel yang diajar oleh masing-masing guru</p>
    </div>

    <?php if (class_exists('Flasher'))
        Flasher::flash(); ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Form Assign -->
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Assign Mapel ke Guru</h2>
            <form action="<?= BASEURL; ?>/jadwal/simpanGuruMapel" method="POST">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Guru</label>
                        <select name="id_guru" required
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                            <option value="">-- Pilih Guru --</option>
                            <?php foreach ($guruList as $g): ?>
                                <option value="<?= $g['id_guru']; ?>"><?= htmlspecialchars($g['nama_guru']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Mapel (bisa lebih dari
                            satu)</label>
                        <div class="grid grid-cols-2 gap-2 max-h-64 overflow-y-auto border rounded-lg p-3">
                            <?php foreach ($mapelList as $m): ?>
                                <label class="flex items-center gap-2 cursor-pointer p-1 hover:bg-gray-50 rounded">
                                    <input type="checkbox" name="id_mapel[]" value="<?= $m['id_mapel']; ?>"
                                        class="w-4 h-4 text-indigo-600 rounded">
                                    <span class="text-sm"><?= htmlspecialchars($m['nama_mapel']); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                        Simpan
                    </button>
                </div>
            </form>
        </div>

        <!-- List -->
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Daftar Guru - Mapel</h2>
            <?php if (empty($guruMapelGrouped)): ?>
                <p class="text-gray-500 text-center py-8">Belum ada data</p>
            <?php else: ?>
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    <?php foreach ($guruMapelGrouped as $idGuru => $data): ?>
                        <div class="border rounded-lg p-3">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-medium text-gray-800"><?= htmlspecialchars($data['nama_guru']); ?></h4>
                                    <div class="flex flex-wrap gap-1 mt-1">
                                        <?php foreach ($data['mapel'] as $m): ?>
                                            <span
                                                class="text-xs bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded"><?= htmlspecialchars($m); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>