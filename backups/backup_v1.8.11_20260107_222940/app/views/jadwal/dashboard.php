<?php
// File: app/views/jadwal/dashboard.php
$total_jam = $data['total_jam'] ?? 0;
$total_guru_mapel = $data['total_guru_mapel'] ?? 0;
$total_jadwal = $data['total_jadwal'] ?? 0;
$pengaturan = $data['pengaturan'] ?? [];
?>

<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Dashboard Jadwal Pelajaran</h1>
        <p class="text-gray-600">Kelola jadwal pelajaran sekolah</p>
    </div>

    <?php if (class_exists('Flasher'))
        Flasher::flash(); ?>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Jam Pelajaran</p>
                    <h3 class="text-3xl font-bold text-indigo-600"><?= $total_jam; ?></h3>
                </div>
                <div class="bg-indigo-100 p-3 rounded-full">
                    <i data-lucide="clock" class="w-6 h-6 text-indigo-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Guru Dengan Mapel</p>
                    <h3 class="text-3xl font-bold text-green-600"><?= $total_guru_mapel; ?></h3>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <i data-lucide="users" class="w-6 h-6 text-green-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Jadwal Terisi</p>
                    <h3 class="text-3xl font-bold text-purple-600"><?= $total_jadwal; ?></h3>
                </div>
                <div class="bg-purple-100 p-3 rounded-full">
                    <i data-lucide="calendar" class="w-6 h-6 text-purple-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-xl shadow-sm border p-6 mb-8">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Menu Cepat</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="<?= BASEURL; ?>/jadwal/kelola"
                class="flex flex-col items-center p-4 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition">
                <i data-lucide="layout-grid" class="w-8 h-8 text-indigo-600 mb-2"></i>
                <span class="text-sm font-medium text-indigo-700">Kelola Jadwal</span>
            </a>
            <a href="<?= BASEURL; ?>/jadwal/jamPelajaran"
                class="flex flex-col items-center p-4 bg-green-50 hover:bg-green-100 rounded-lg transition">
                <i data-lucide="clock" class="w-8 h-8 text-green-600 mb-2"></i>
                <span class="text-sm font-medium text-green-700">Jam Pelajaran</span>
            </a>
            <a href="<?= BASEURL; ?>/jadwal/guruMapel"
                class="flex flex-col items-center p-4 bg-purple-50 hover:bg-purple-100 rounded-lg transition">
                <i data-lucide="book-user" class="w-8 h-8 text-purple-600 mb-2"></i>
                <span class="text-sm font-medium text-purple-700">Guru Mapel</span>
            </a>
            <a href="<?= BASEURL; ?>/jadwal/pengaturan"
                class="flex flex-col items-center p-4 bg-amber-50 hover:bg-amber-100 rounded-lg transition">
                <i data-lucide="settings" class="w-8 h-8 text-amber-600 mb-2"></i>
                <span class="text-sm font-medium text-amber-700">Pengaturan</span>
            </a>
        </div>
    </div>

    <!-- Current Settings -->
    <div class="bg-white rounded-xl shadow-sm border p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Pengaturan Saat Ini</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div class="bg-gray-50 rounded-lg p-3">
                <p class="text-gray-500">Durasi Jam</p>
                <p class="font-semibold text-gray-800"><?= $pengaturan['durasi_jam'] ?? '45'; ?> menit</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-3">
                <p class="text-gray-500">Jam Mulai</p>
                <p class="font-semibold text-gray-800"><?= $pengaturan['jam_mulai'] ?? '07:00'; ?></p>
            </div>
            <div class="bg-gray-50 rounded-lg p-3">
                <p class="text-gray-500">Jam Selesai</p>
                <p class="font-semibold text-gray-800"><?= $pengaturan['jam_selesai'] ?? '14:00'; ?></p>
            </div>
            <div class="bg-gray-50 rounded-lg p-3">
                <p class="text-gray-500">Hari Aktif</p>
                <p class="font-semibold text-gray-800">
                    <?= str_replace(',', ', ', $pengaturan['hari_aktif'] ?? 'Senin-Sabtu'); ?></p>
            </div>
        </div>
    </div>
</main>