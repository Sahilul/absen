<?php
// File: app/views/jadwal/pengaturan.php
$pengaturan = $data['pengaturan'] ?? [];
$hariAktif = explode(',', $pengaturan['hari_aktif'] ?? 'Senin,Selasa,Rabu,Kamis,Jumat,Sabtu');
$semuaHari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
?>

<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Pengaturan Jadwal</h1>
            <p class="text-gray-600">Atur parameter default jadwal pelajaran</p>
        </div>

        <?php if (class_exists('Flasher'))
            Flasher::flash(); ?>

        <div class="bg-white rounded-xl shadow-sm border p-6">
            <form action="<?= BASEURL; ?>/jadwal/simpanPengaturan" method="POST">
                <div class="space-y-6">
                    <!-- Durasi Jam -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Durasi Per Jam (menit)</label>
                        <input type="number" name="durasi_jam" min="30" max="60"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500"
                            value="<?= $pengaturan['durasi_jam'] ?? '45'; ?>">
                    </div>

                    <!-- Jam Mulai & Selesai -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jam Mulai</label>
                            <input type="time" name="jam_mulai"
                                class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500"
                                value="<?= $pengaturan['jam_mulai'] ?? '07:00'; ?>">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jam Selesai</label>
                            <input type="time" name="jam_selesai"
                                class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500"
                                value="<?= $pengaturan['jam_selesai'] ?? '14:00'; ?>">
                        </div>
                    </div>

                    <!-- Lama Istirahat -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Lama Istirahat (menit)</label>
                        <input type="number" name="lama_istirahat" min="5" max="60"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500"
                            value="<?= $pengaturan['lama_istirahat'] ?? '15'; ?>">
                    </div>

                    <!-- Hari Aktif -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Hari Aktif</label>
                        <div class="flex flex-wrap gap-3">
                            <?php foreach ($semuaHari as $h): ?>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="hari_aktif[]" value="<?= $h; ?>"
                                        class="w-4 h-4 text-indigo-600 rounded" <?= in_array($h, $hariAktif) ? 'checked' : ''; ?>>
                                    <span class="text-sm"><?= $h; ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="flex gap-2 pt-4 border-t">
                        <a href="<?= BASEURL; ?>/jadwal"
                            class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium text-center transition">
                            Batal
                        </a>
                        <button type="submit"
                            class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                            Simpan Pengaturan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</main>