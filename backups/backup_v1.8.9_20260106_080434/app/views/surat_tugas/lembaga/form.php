<?php
// app/views/surat_tugas/lembaga/form.php
// UPDATED: High Contrast Inputs
$isEdit = isset($data['lembaga']);
$l = $data['lembaga'] ?? [];
?>
<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center gap-4 mb-6">
            <a href="<?= BASEURL; ?>/suratTugas/lembaga"
                class="p-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 transition shadow-sm">
                <i data-lucide="arrow-left" class="w-5 h-5 text-gray-700"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800"><?= $data['judul']; ?></h1>
                <p class="text-sm text-gray-500">Lengkapi data di bawah ini dengan benar</p>
            </div>
        </div>

        <form action="<?= BASEURL; ?>/suratTugas/simpanLembaga" method="POST" enctype="multipart/form-data"
            class="bg-white rounded-xl shadow-md border border-gray-200 p-6 md:p-8">
            <input type="hidden" name="id_lembaga" value="<?= $l['id_lembaga'] ?? ''; ?>">

            <div
                class="flex items-center gap-2 mb-6 text-indigo-700 bg-indigo-50 p-3 rounded-lg border border-indigo-100">
                <i data-lucide="building-2" class="w-5 h-5"></i>
                <h3 class="text-lg font-bold">Identitas Lembaga</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Nama Lembaga <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="nama_lembaga"
                        class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 bg-gray-50 text-gray-900 focus:bg-white focus:border-indigo-600 focus:ring-4 focus:ring-indigo-100 transition-all duration-200 placeholder-gray-400 font-medium"
                        required value="<?= $l['nama_lembaga'] ?? ''; ?>" placeholder="Contoh: MTS NEGERI 1 MOJOKERTO">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Kota/Kabupaten <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="kota"
                        class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 bg-gray-50 text-gray-900 focus:bg-white focus:border-indigo-600 focus:ring-4 focus:ring-indigo-100 transition-all duration-200 placeholder-gray-400 font-medium"
                        required value="<?= $l['kota'] ?? ''; ?>" placeholder="Contoh: Mojokerto">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Alamat Lengkap <span
                            class="text-red-500">*</span></label>
                    <textarea name="alamat" rows="3"
                        class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 bg-gray-50 text-gray-900 focus:bg-white focus:border-indigo-600 focus:ring-4 focus:ring-indigo-100 transition-all duration-200 placeholder-gray-400 font-medium"
                        required placeholder="Jalan Raya..."><?= $l['alamat'] ?? ''; ?></textarea>
                </div>
            </div>

            <div
                class="flex items-center gap-2 mb-6 text-indigo-700 bg-indigo-50 p-3 rounded-lg border border-indigo-100 mt-8">
                <i data-lucide="user-check" class="w-5 h-5"></i>
                <h3 class="text-lg font-bold">Kepala Lembaga (Penanda Tangan)</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Nama Kepala <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="nama_kepala_lembaga"
                        class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 bg-gray-50 text-gray-900 focus:bg-white focus:border-indigo-600 focus:ring-4 focus:ring-indigo-100 transition-all duration-200 placeholder-gray-400 font-medium"
                        required value="<?= $l['nama_kepala_lembaga'] ?? ''; ?>">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">NIP (Opsional)</label>
                    <input type="text" name="nip_kepala"
                        class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 bg-gray-50 text-gray-900 focus:bg-white focus:border-indigo-600 focus:ring-4 focus:ring-indigo-100 transition-all duration-200 placeholder-gray-400 font-medium"
                        value="<?= $l['nip_kepala'] ?? ''; ?>">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Jabatan <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="jabatan_kepala"
                        class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 bg-gray-50 text-gray-900 focus:bg-white focus:border-indigo-600 focus:ring-4 focus:ring-indigo-100 transition-all duration-200 placeholder-gray-400 font-medium"
                        required value="<?= $l['jabatan_kepala'] ?? ''; ?>" placeholder="Contoh: Kepala Madrasah">
                </div>
            </div>

            <div
                class="flex items-center gap-2 mb-6 text-indigo-700 bg-indigo-50 p-3 rounded-lg border border-indigo-100 mt-8">
                <i data-lucide="file-image" class="w-5 h-5"></i>
                <h3 class="text-lg font-bold">Berkas Pendukung</h3>
            </div>

            <div
                class="mb-8 p-4 bg-gray-50 border-2 border-dashed border-gray-300 rounded-xl hover:bg-gray-100 transition group">
                <label class="block text-sm font-bold text-gray-700 mb-2 group-hover:text-indigo-600">Upload Kop Surat
                    (Image)</label>
                <div class="flex items-center gap-4">
                    <div class="flex-1">
                        <input type="file" name="kop_surat" accept="image/*"
                            class="w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 cursor-pointer">
                        <p class="text-xs text-gray-500 mt-2 font-medium">Format: PNG/JPG/JPEG. Jika tidak diupload,
                            akan menggunakan teks biasa.</p>
                    </div>
                    <?php if (!empty($l['kop_surat'])): ?>
                        <div class="p-2 border rounded-lg bg-white shadow-sm">
                            <span class="block text-xs font-bold text-gray-400 mb-1 text-center">Current:</span>
                            <img src="<?= BASEURL; ?>/public/uploads/kop_lembaga/<?= $l['kop_surat']; ?>"
                                class="h-16 object-contain">
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="flex justify-end gap-4 pt-6 border-t border-gray-200 mt-6">
                <a href="<?= BASEURL; ?>/suratTugas/lembaga"
                    class="px-6 py-2.5 border-2 border-gray-300 text-gray-700 font-bold rounded-lg hover:bg-gray-100 hover:border-gray-400 transition">Batal</a>
                <button type="submit"
                    class="px-8 py-2.5 bg-indigo-600 text-white font-bold rounded-lg hover:bg-indigo-700 shadow-lg shadow-indigo-200 hover:shadow-indigo-300 transition-all transform hover:-translate-y-0.5">
                    <?= $isEdit ? 'Simpan Perubahan' : 'Simpan Data'; ?>
                </button>
            </div>
        </form>
    </div>
</main>