<?php
// File: app/views/surat_tugas/form.php
$surat = $data['surat'] ?? null;
$guruList = $data['guru_list'] ?? [];
$pengaturan = $data['pengaturan'] ?? [];
$isEdit = !empty($surat);
?>

<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <a href="<?= BASEURL; ?>/admin/suratTugas"
                class="text-indigo-600 hover:text-indigo-800 text-sm flex items-center gap-1">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali
            </a>
            <h1 class="text-2xl font-bold text-gray-800 mt-2"><?= $isEdit ? 'Edit' : 'Buat'; ?> Surat Tugas</h1>
        </div>

        <?php if (class_exists('Flasher'))
            Flasher::flash(); ?>

        <div class="bg-white rounded-xl shadow-sm border p-6">
            <form action="<?= BASEURL; ?>/admin/simpanSuratTugas" method="POST" enctype="multipart/form-data">
                <?php if ($isEdit): ?>
                    <input type="hidden" name="id_surat" value="<?= $surat['id_surat']; ?>">
                <?php endif; ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Surat</label>
                        <input type="text" name="nomor_surat" required
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500"
                            value="<?= htmlspecialchars($surat['nomor_surat'] ?? $data['nomor_surat_auto'] ?? ''); ?>">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Surat</label>
                        <input type="date" name="tanggal_surat" required
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500"
                            value="<?= $surat['tanggal_surat'] ?? date('Y-m-d'); ?>">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Guru Yang Ditugaskan</label>
                        <select name="id_guru" required
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                            <option value="">-- Pilih Guru --</option>
                            <?php foreach ($guruList as $g): ?>
                                <option value="<?= $g['id_guru']; ?>" <?= ($surat['id_guru'] ?? '') == $g['id_guru'] ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($g['nama_guru']); ?>     <?= $g['nip'] ? '(' . $g['nip'] . ')' : ''; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Perihal</label>
                        <input type="text" name="perihal" required
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500"
                            value="<?= htmlspecialchars($surat['perihal'] ?? ''); ?>"
                            placeholder="Contoh: Tugas Menghadiri Workshop">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Isi Tugas</label>
                        <textarea name="isi_tugas" rows="4"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500"
                            placeholder="Jelaskan tugas yang diberikan..."><?= htmlspecialchars($surat['isi_tugas'] ?? ''); ?></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tempat Tugas</label>
                        <input type="text" name="tempat_tugas"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500"
                            value="<?= htmlspecialchars($surat['tempat_tugas'] ?? ''); ?>"
                            placeholder="Contoh: Aula Kemenag Kab. X">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                            <option value="draft" <?= ($surat['status'] ?? 'draft') == 'draft' ? 'selected' : ''; ?>>Draft
                            </option>
                            <option value="terbit" <?= ($surat['status'] ?? '') == 'terbit' ? 'selected' : ''; ?>>Terbit
                            </option>
                            <option value="selesai" <?= ($surat['status'] ?? '') == 'selesai' ? 'selected' : ''; ?>>Selesai
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500"
                            value="<?= $surat['tanggal_mulai'] ?? ''; ?>">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500"
                            value="<?= $surat['tanggal_selesai'] ?? ''; ?>">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan Tambahan</label>
                        <textarea name="keterangan" rows="2"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500"
                            placeholder="Opsional..."><?= htmlspecialchars($surat['keterangan'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="flex gap-2 pt-6 border-t mt-6">
                    <a href="<?= BASEURL; ?>/admin/suratTugas"
                        class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium text-center transition">
                        Batal
                    </a>
                    <button type="submit"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>