<?php
$kelasList = $data['kelas_list'] ?? [];
$siswaList = $data['siswa_list'] ?? [];
$selectedKelas = $data['selected_kelas'] ?? '';
?>
<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gradient-to-br from-secondary-50 to-secondary-100 p-4 sm:p-6">
    <div class="mb-6">
        <a href="<?= BASEURL; ?>/waliKelas/dashboard" class="text-sm text-primary-600 hover:underline flex items-center gap-1 mb-2">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali
        </a>
        <h2 class="text-2xl sm:text-3xl font-bold text-secondary-800 flex items-center">
            <i data-lucide="alert-triangle" class="w-7 h-7 sm:w-8 sm:h-8 mr-2 sm:mr-3 text-amber-500"></i>
            Lapor Kasus ke BK
        </h2>
        <p class="text-secondary-600 mt-1 text-sm">Laporkan kasus siswa untuk ditangani oleh guru BK</p>
    </div>

    <?php Flasher::flash(); ?>

    <div class="bg-white rounded-xl shadow-sm border overflow-hidden max-w-3xl">
        <div class="px-4 sm:px-6 py-4 border-b bg-amber-50">
            <div class="flex items-center gap-2">
                <i data-lucide="info" class="w-4 h-4 text-amber-600"></i>
                <p class="text-sm text-amber-800">Kasus yang dilaporkan akan masuk ke daftar kasus BK dengan status <strong>Baru</strong> dan menunggu guru BK mengambil penanganan.</p>
            </div>
        </div>
        <form method="POST" action="<?= BASEURL; ?>/waliKelas/prosesLaporKasusBK" class="p-4 sm:p-6 space-y-5">
            <?php if (!empty($kelasList)): ?>
                <input type="hidden" name="id_kelas" value="<?= $kelasList[0]['id_kelas'] ?>">
                <div>
                    <label class="block text-sm font-medium text-secondary-700 mb-1">Kelas</label>
                    <p class="text-sm font-semibold text-secondary-800 bg-gray-50 px-3 py-2.5 rounded-lg border"><?= htmlspecialchars($kelasList[0]['nama_kelas']) ?></p>
                </div>
            <?php endif; ?>

            <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Siswa <span class="text-red-500">*</span></label>
                <select name="id_siswa" required class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">-- Pilih Siswa --</option>
                    <?php foreach ($siswaList as $s): ?>
                        <option value="<?= $s['id_siswa'] ?>"><?= htmlspecialchars($s['nama']) ?> (<?= $s['nis'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Kategori <span class="text-red-500">*</span></label>
                <select name="kategori" required class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="pelanggaran">Pelanggaran</option>
                    <option value="akademik">Akademik</option>
                    <option value="pribadi">Pribadi</option>
                    <option value="sosial">Sosial</option>
                    <option value="karir">Karir</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Judul Kasus <span class="text-red-500">*</span></label>
                <input type="text" name="judul" required maxlength="255" placeholder="Ringkasan singkat kasus..."
                       class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Deskripsi / Kronologi</label>
                <textarea name="deskripsi" rows="4" placeholder="Jelaskan kronologi atau detail kasus..."
                          class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-secondary-700 mb-1">Tingkat</label>
                    <select name="tingkat" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="ringan">Ringan</option>
                        <option value="sedang">Sedang</option>
                        <option value="berat">Berat</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-secondary-700 mb-1">Poin Pelanggaran</label>
                    <input type="number" name="poin" value="0" min="0" max="100"
                           class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="btn-primary px-6 py-2.5 text-sm rounded-xl flex items-center gap-2">
                    <i data-lucide="send" class="w-4 h-4"></i> Laporkan ke BK
                </button>
                <a href="<?= BASEURL; ?>/waliKelas/dashboard" class="px-6 py-2.5 text-sm text-secondary-600 hover:text-secondary-800 border border-gray-200 rounded-xl">
                    Batal
                </a>
            </div>
        </form>
    </div>
</main>
