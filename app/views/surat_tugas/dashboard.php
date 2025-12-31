<?php
// File: app/views/surat_tugas/dashboard.php
$stats = $data['stats'] ?? ['total_lembaga' => 0, 'total_surat' => 0];
?>

<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Dashboard Surat Tugas</h1>
            <p class="text-gray-600">Overview sistem manajemen surat tugas</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- Stats Lembaga -->
        <div class="bg-white rounded-xl shadow-sm border p-6 flex items-center">
            <div class="p-4 rounded-xl bg-purple-100 text-purple-600 mr-4">
                <i data-lucide="building-2" class="w-8 h-8"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Total Lembaga</p>
                <h3 class="text-2xl font-bold text-gray-800"><?= $stats['total_lembaga']; ?></h3>
                <a href="<?= BASEURL; ?>/suratTugas/lembaga"
                    class="text-sm text-purple-600 hover:underline mt-1 inline-block">Kelola Lembaga &rarr;</a>
            </div>
        </div>

        <!-- Stats Surat -->
        <div class="bg-white rounded-xl shadow-sm border p-6 flex items-center">
            <div class="p-4 rounded-xl bg-blue-100 text-blue-600 mr-4">
                <i data-lucide="file-check" class="w-8 h-8"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Total Surat Tugas</p>
                <h3 class="text-2xl font-bold text-gray-800"><?= $stats['total_surat']; ?></h3>
                <a href="<?= BASEURL; ?>/suratTugas/surat"
                    class="text-sm text-blue-600 hover:underline mt-1 inline-block">Lihat Daftar Surat &rarr;</a>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-xl shadow-sm border p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Aksi Cepat</h3>
        <div class="flex gap-4">
            <a href="<?= BASEURL; ?>/suratTugas/inputSurat"
                class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                <i data-lucide="plus" class="w-4 h-4"></i> Buat Surat Tugas Baru
            </a>
            <a href="<?= BASEURL; ?>/suratTugas/tambahLembaga"
                class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                <i data-lucide="building" class="w-4 h-4"></i> Tambah Lembaga
            </a>
        </div>
    </div>
</main>