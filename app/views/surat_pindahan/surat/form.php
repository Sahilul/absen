<?php
// app/views/surat_pindahan/surat/form.php
$isEdit = isset($data['surat']);
$s = $data['surat'] ?? [];
?>
<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
    <div class="max-w-6xl mx-auto">
        <div class="flex items-center gap-4 mb-6">
            <a href="<?= BASEURL; ?>/suratPindahan/surat"
                class="p-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 transition shadow-sm">
                <i data-lucide="arrow-left" class="w-5 h-5 text-gray-700"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800"><?= $data['judul']; ?></h1>
                <p class="text-sm text-gray-500">Buat atau edit surat keterangan penerimaan siswa pindahan</p>
            </div>
        </div>

        <form action="<?= BASEURL; ?>/suratPindahan/simpanSurat" method="POST" class="space-y-6">
            <input type="hidden" name="id_surat" value="<?= $s['id_surat'] ?? ''; ?>">

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <!-- Kolom Kiri: Detail Surat -->
                <div class="space-y-6">
                    <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
                        <div
                            class="flex items-center gap-2 mb-6 text-emerald-700 bg-emerald-50 p-3 rounded-lg border border-emerald-100">
                            <i data-lucide="file-text" class="w-5 h-5"></i>
                            <h3 class="font-bold">Detail Surat</h3>
                        </div>

                        <div class="mb-5">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Pilih Lembaga <span
                                    class="text-red-500">*</span></label>
                            <select name="id_lembaga"
                                class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 bg-gray-50 text-gray-900 focus:bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100 transition-all duration-200 font-medium"
                                required>
                                <option value="">-- Pilih Lembaga --</option>
                                <?php foreach ($data['lembaga_list'] as $l): ?>
                                    <option value="<?= $l['id_lembaga']; ?>"
                                        <?= (isset($s['id_lembaga']) && $s['id_lembaga'] == $l['id_lembaga']) ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($l['nama_lembaga']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Kelola lembaga &amp; kop surat di Panel Surat Tugas
                                &rarr; Kelola Lembaga.</p>
                        </div>

                        <div class="mb-5">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Nomor Surat <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="nomor_surat"
                                class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 bg-gray-50 text-gray-900 focus:bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100 transition-all duration-200 placeholder-gray-400 font-medium"
                                required value="<?= htmlspecialchars($s['nomor_surat'] ?? ''); ?>"
                                placeholder="Contoh: 095/YPPS-MA/2/2026">
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-5">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Tanggal Surat <span
                                        class="text-red-500">*</span></label>
                                <input type="date" name="tanggal_surat"
                                    class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 bg-gray-50 text-gray-900 focus:bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100 transition-all duration-200 font-medium"
                                    required value="<?= htmlspecialchars($s['tanggal_surat'] ?? date('Y-m-d')); ?>">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Kota Surat</label>
                                <input type="text" name="kota_surat"
                                    class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 bg-gray-50 text-gray-900 focus:bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100 transition-all duration-200 placeholder-gray-400 font-medium"
                                    value="<?= htmlspecialchars($s['kota_surat'] ?? ''); ?>"
                                    placeholder="Kosongkan = kota lembaga">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Diterima di Kelas</label>
                                <input type="text" name="diterima_di_kelas"
                                    class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 bg-gray-50 text-gray-900 focus:bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100 transition-all duration-200 placeholder-gray-400 font-medium"
                                    value="<?= htmlspecialchars($s['diterima_di_kelas'] ?? ''); ?>"
                                    placeholder="Contoh: VII (Tujuh)">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Tahun Pelajaran</label>
                                <input type="text" name="tahun_pelajaran"
                                    class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 bg-gray-50 text-gray-900 focus:bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100 transition-all duration-200 placeholder-gray-400 font-medium"
                                    value="<?= htmlspecialchars($s['tahun_pelajaran'] ?? ''); ?>"
                                    placeholder="Contoh: 2026/2027">
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
                        <div
                            class="flex items-center gap-2 mb-6 text-emerald-700 bg-emerald-50 p-3 rounded-lg border border-emerald-100">
                            <i data-lucide="sticky-note" class="w-5 h-5"></i>
                            <h3 class="font-bold">Keterangan Tambahan</h3>
                        </div>
                        <textarea name="keterangan" rows="3"
                            class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 bg-gray-50 text-gray-900 focus:bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100 transition-all duration-200 placeholder-gray-400 font-medium"
                            placeholder="Opsional, ditambahkan setelah paragraf penerimaan"><?= htmlspecialchars($s['keterangan'] ?? ''); ?></textarea>
                    </div>
                </div>

                <!-- Kolom Kanan: Data Siswa -->
                <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
                    <div
                        class="flex items-center gap-2 mb-6 text-emerald-700 bg-emerald-50 p-3 rounded-lg border border-emerald-100">
                        <i data-lucide="user-round-plus" class="w-5 h-5"></i>
                        <h3 class="font-bold">Data Siswa Pindahan</h3>
                    </div>

                    <div class="mb-5">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Nama Siswa <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="nama_siswa"
                            class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 bg-gray-50 text-gray-900 focus:bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100 transition-all duration-200 placeholder-gray-400 font-medium"
                            required value="<?= htmlspecialchars($s['nama_siswa'] ?? ''); ?>"
                            placeholder="Nama lengkap siswa">
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-5">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir"
                                class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 bg-gray-50 text-gray-900 focus:bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100 transition-all duration-200 placeholder-gray-400 font-medium"
                                value="<?= htmlspecialchars($s['tempat_lahir'] ?? ''); ?>" placeholder="Contoh: Kediri">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir"
                                class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 bg-gray-50 text-gray-900 focus:bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100 transition-all duration-200 font-medium"
                                value="<?= htmlspecialchars($s['tanggal_lahir'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-5">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Jenis Kelamin</label>
                            <select name="jenis_kelamin"
                                class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 bg-gray-50 text-gray-900 focus:bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100 transition-all duration-200 font-medium">
                                <option value="">-- Pilih --</option>
                                <option value="L" <?= (($s['jenis_kelamin'] ?? '') === 'L') ? 'selected' : ''; ?>>Laki-laki
                                </option>
                                <option value="P" <?= (($s['jenis_kelamin'] ?? '') === 'P') ? 'selected' : ''; ?>>
                                    Perempuan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">NISN</label>
                            <input type="text" name="nisn"
                                class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 bg-gray-50 text-gray-900 focus:bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100 transition-all duration-200 placeholder-gray-400 font-medium"
                                value="<?= htmlspecialchars($s['nisn'] ?? ''); ?>" placeholder="10 digit NISN">
                        </div>
                    </div>

                    <div class="mb-5">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Asal Sekolah</label>
                        <input type="text" name="asal_sekolah"
                            class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 bg-gray-50 text-gray-900 focus:bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100 transition-all duration-200 placeholder-gray-400 font-medium"
                            value="<?= htmlspecialchars($s['asal_sekolah'] ?? ''); ?>"
                            placeholder="Contoh: MAN 4 Kediri">
                    </div>

                    <div class="mb-5">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Nama Orang Tua / Wali</label>
                        <input type="text" name="nama_orang_tua"
                            class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 bg-gray-50 text-gray-900 focus:bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100 transition-all duration-200 placeholder-gray-400 font-medium"
                            value="<?= htmlspecialchars($s['nama_orang_tua'] ?? ''); ?>"
                            placeholder="Nama orang tua / wali murid">
                    </div>

                    <div class="mb-2">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Alamat Siswa</label>
                        <textarea name="alamat_siswa" rows="3"
                            class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 bg-gray-50 text-gray-900 focus:bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100 transition-all duration-200 placeholder-gray-400 font-medium"
                            placeholder="Alamat lengkap siswa"><?= htmlspecialchars($s['alamat_siswa'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-4">
                <a href="<?= BASEURL; ?>/suratPindahan/surat"
                    class="px-6 py-3 border-2 border-gray-300 text-gray-700 font-bold rounded-xl hover:bg-gray-100 transition">Batal</a>
                <button type="submit"
                    class="px-8 py-3 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 transition shadow-xl shadow-emerald-200 hover:shadow-emerald-300 transform hover:-translate-y-0.5">
                    <?= $isEdit ? 'Update Surat' : 'Terbitkan Surat'; ?>
                </button>
            </div>
        </form>
    </div>
</main>
