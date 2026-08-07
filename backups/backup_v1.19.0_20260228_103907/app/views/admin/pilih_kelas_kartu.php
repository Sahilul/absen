<?php
// File: app/views/admin/pilih_kelas_kartu.php
$kelasList = $data['kelas_list'] ?? [];

// Helper untuk sorting kelas berdasarkan romawi
function romanToIntPilih($roman)
{
    $romans = [
        'VII' => 7,
        'VIII' => 8,
        'IX' => 9,
        'X' => 10,
        'XI' => 11,
        'XII' => 12,
        'I' => 1,
        'II' => 2,
        'III' => 3,
        'IV' => 4,
        'V' => 5,
        'VI' => 6
    ];
    foreach ($romans as $r => $v) {
        if (strpos($roman, $r) === 0)
            return $v;
    }
    return 999;
}
usort($kelasList, function ($a, $b) {
    return romanToIntPilih($a['nama_kelas']) - romanToIntPilih($b['nama_kelas']);
});
?>

<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Cetak Kartu Login Siswa</h1>
            <p class="text-gray-600">Pilih kelas untuk mencetak kartu login</p>
        </div>

        <?php if (class_exists('Flasher'))
            Flasher::flash(); ?>

        <?php if (empty($kelasList)): ?>
            <div class="bg-white rounded-xl shadow-sm border p-8 text-center text-gray-500">
                <i data-lucide="alert-circle" class="w-12 h-12 mx-auto mb-3 text-gray-300"></i>
                <p>Tidak ada kelas tersedia</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($kelasList as $k): ?>
                    <a href="<?= BASEURL; ?>/admin/cetakKartuLogin/<?= $k['id_kelas']; ?>"
                        class="bg-white rounded-xl shadow-sm border p-5 hover:shadow-md hover:border-indigo-300 transition group">
                        <div class="flex items-center gap-4">
                            <div class="bg-indigo-100 group-hover:bg-indigo-200 p-3 rounded-xl transition">
                                <i data-lucide="id-card" class="w-6 h-6 text-indigo-600"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800 group-hover:text-indigo-700 transition">
                                    <?= htmlspecialchars($k['nama_kelas']); ?>
                                </h3>
                                <p class="text-sm text-gray-500">Klik untuk cetak kartu</p>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>