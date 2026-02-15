<?php
// File: app/views/jadwal/jam_form.php
$jam = $data['jam'] ?? null;
$pengaturan = $data['pengaturan'] ?? [];
$isEdit = !empty($jam);
?>

<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
    <div class="max-w-xl mx-auto">
        <div class="mb-6">
            <a href="<?= BASEURL; ?>/jadwal/jamPelajaran"
                class="text-indigo-600 hover:text-indigo-800 text-sm flex items-center gap-1">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali
            </a>
            <h1 class="text-2xl font-bold text-gray-800 mt-2"><?= $isEdit ? 'Edit' : 'Tambah'; ?> Jam Pelajaran</h1>
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-6">
            <form action="<?= BASEURL; ?>/jadwal/simpanJam" method="POST">
                <?php if ($isEdit): ?>
                    <input type="hidden" name="id_jam" value="<?= $jam['id_jam']; ?>">
                <?php endif; ?>

                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="is_istirahat" name="is_istirahat"
                            class="w-4 h-4 text-amber-600 rounded" <?= ($jam['is_istirahat'] ?? 0) ? 'checked' : ''; ?>>
                        <label for="is_istirahat" class="text-sm font-medium text-gray-700">Ini adalah waktu
                            istirahat</label>
                    </div>

                    <div id="jamKeField">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jam Ke</label>
                        <input type="number" name="jam_ke" min="1" max="20"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500"
                            value="<?= $jam['jam_ke'] ?? ''; ?>">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Waktu Mulai</label>
                            <input type="time" name="waktu_mulai" required
                                class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500"
                                value="<?= $jam['waktu_mulai'] ?? '07:00'; ?>">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Waktu Selesai</label>
                            <input type="time" name="waktu_selesai" required
                                class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500"
                                value="<?= $jam['waktu_selesai'] ?? '07:45'; ?>">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
                        <input type="number" name="urutan" min="1"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500"
                            value="<?= $jam['urutan'] ?? '1'; ?>">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                        <input type="text" name="keterangan"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500"
                            value="<?= htmlspecialchars($jam['keterangan'] ?? ''); ?>" placeholder="Opsional">
                    </div>

                    <div class="flex gap-2 pt-4">
                        <a href="<?= BASEURL; ?>/jadwal/jamPelajaran"
                            class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium text-center transition">
                            Batal
                        </a>
                        <button type="submit"
                            class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                            Simpan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
    document.getElementById('is_istirahat').addEventListener('change', function () {
        const jamKeField = document.getElementById('jamKeField');
        if (this.checked) {
            jamKeField.style.display = 'none';
        } else {
            jamKeField.style.display = 'block';
        }
    });

    // Trigger on load
    if (document.getElementById('is_istirahat').checked) {
        document.getElementById('jamKeField').style.display = 'none';
    }
</script>