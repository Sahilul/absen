<?php // Admin - Form Kirim Pesan ?>
<div class="p-4 sm:p-6">
    <!-- Header Section -->
    <div class="flex items-center gap-4 mb-6">
        <a href="<?= BASEURL ?>/admin/pesan" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
            <i data-lucide="arrow-left" class="w-5 h-5 text-gray-600"></i>
        </a>
        <div class="flex items-center gap-3">
            <div
                class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                <i data-lucide="send" class="w-6 h-6 text-white"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Tulis Pesan Baru</h1>
                <p class="text-sm text-gray-500">Kirim pesan ke guru atau siswa</p>
            </div>
        </div>
    </div>

    <!-- Flasher -->
    <?php Flasher::flash(); ?>

    <!-- Form -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
        <form action="<?= BASEURL ?>/admin/prosesKirimPesan" method="POST" enctype="multipart/form-data"
            class="p-6 space-y-6">

            <!-- Target Penerima -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Kirim Ke <span
                        class="text-red-500">*</span></label>
                <select name="target_type" id="target_type" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                    onchange="toggleTargetId(this.value)">
                    <option value="">-- Pilih Penerima --</option>
                    <option value="semua_guru">📚 Semua Guru</option>
                    <option value="semua_siswa">🎓 Semua Siswa</option>
                    <option value="kelas">🏫 Siswa Per Kelas</option>
                    <option value="guru_individual">👤 Guru Tertentu</option>
                    <option value="siswa_individual">👤 Siswa Tertentu</option>
                </select>
            </div>

            <!-- Target ID (Kelas/Guru/Siswa) - Hidden by default -->
            <div id="target_kelas_wrapper" class="hidden">
                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Kelas <span
                        class="text-red-500">*</span></label>
                <select name="target_kelas" id="target_kelas"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                    <option value="">-- Pilih Kelas --</option>
                    <?php foreach ($data['kelas'] ?? [] as $kelas): ?>
                        <option value="<?= $kelas['id_kelas'] ?>"><?= htmlspecialchars($kelas['nama_kelas']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="target_guru_wrapper" class="hidden">
                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Guru <span
                        class="text-red-500">*</span></label>
                <select name="target_guru" id="target_guru"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                    <option value="">-- Pilih Guru --</option>
                    <?php foreach ($data['guru'] ?? [] as $guru): ?>
                        <option value="<?= $guru['id_guru'] ?>"><?= htmlspecialchars($guru['nama_guru']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="target_siswa_wrapper" class="hidden">
                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Siswa <span
                        class="text-red-500">*</span></label>
                <select name="target_siswa" id="target_siswa"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                    <option value="">-- Pilih Siswa --</option>
                    <?php foreach ($data['siswa'] ?? [] as $siswa): ?>
                        <option value="<?= $siswa['id_siswa'] ?>"><?= htmlspecialchars($siswa['nama_siswa']) ?> -
                            <?= htmlspecialchars($siswa['nama_kelas'] ?? '') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Judul -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Judul Pesan <span
                        class="text-red-500">*</span></label>
                <input type="text" name="judul" required maxlength="255"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                    placeholder="Masukkan judul pesan...">
            </div>

            <!-- Isi Pesan -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Isi Pesan <span
                        class="text-red-500">*</span></label>
                <textarea name="isi" rows="8" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors resize-y"
                    placeholder="Tulis isi pesan di sini..."></textarea>
            </div>

            <!-- Lampiran -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Lampiran (Opsional)</label>
                <div
                    class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-indigo-400 transition-colors">
                    <input type="file" name="lampiran" id="lampiran" class="hidden"
                        accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.zip">
                    <label for="lampiran" class="cursor-pointer">
                        <i data-lucide="upload-cloud" class="w-10 h-10 text-gray-400 mx-auto mb-2"></i>
                        <p class="text-sm text-gray-600">Klik untuk upload atau drag & drop</p>
                        <p class="text-xs text-gray-400 mt-1">PDF, DOC, XLS, JPG, PNG, ZIP (Max 10MB)</p>
                    </label>
                    <p id="file_name" class="text-sm text-indigo-600 mt-2 hidden"></p>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex items-center gap-4 pt-4 border-t border-gray-200">
                <button type="submit"
                    class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white rounded-lg font-medium shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    Kirim Pesan
                </button>
                <a href="<?= BASEURL ?>/admin/pesan"
                    class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleTargetId(value) {
        document.getElementById('target_kelas_wrapper').classList.add('hidden');
        document.getElementById('target_guru_wrapper').classList.add('hidden');
        document.getElementById('target_siswa_wrapper').classList.add('hidden');

        if (value === 'kelas') {
            document.getElementById('target_kelas_wrapper').classList.remove('hidden');
        } else if (value === 'guru_individual') {
            document.getElementById('target_guru_wrapper').classList.remove('hidden');
        } else if (value === 'siswa_individual') {
            document.getElementById('target_siswa_wrapper').classList.remove('hidden');
        }
    }

    document.getElementById('lampiran').addEventListener('change', function (e) {
        const fileName = e.target.files[0]?.name;
        const fileNameEl = document.getElementById('file_name');
        if (fileName) {
            fileNameEl.textContent = '📎 ' + fileName;
            fileNameEl.classList.remove('hidden');
        } else {
            fileNameEl.classList.add('hidden');
        }
    });
</script>