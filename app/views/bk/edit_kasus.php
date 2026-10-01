<?php
$kasus = $data['kasus'] ?? [];
$guruBKList = $data['guru_bk_list'] ?? [];
?>
<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gradient-to-br from-secondary-50 to-secondary-100 p-4 sm:p-6">
    <div class="mb-6">
        <a href="<?= BASEURL; ?>/bk/detailKasus/<?= $kasus['id_kasus'] ?>" class="text-sm text-primary-600 hover:underline flex items-center gap-1 mb-2">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Detail
        </a>
        <h2 class="text-2xl sm:text-3xl font-bold text-secondary-800 flex items-center">
            <i data-lucide="pencil" class="w-7 h-7 sm:w-8 sm:h-8 mr-2 sm:mr-3 text-amber-500"></i>
            Edit Kasus BK
        </h2>
    </div>

    <?php Flasher::flash(); ?>

    <div class="bg-white rounded-xl shadow-sm border overflow-hidden max-w-3xl">
        <div class="px-4 sm:px-6 py-4 border-b bg-gray-50">
            <h3 class="text-sm font-bold text-secondary-800">Siswa: <?= htmlspecialchars($kasus['nama_siswa']) ?> — <?= htmlspecialchars($kasus['nama_kelas']) ?></h3>
        </div>
        <form method="POST" action="<?= BASEURL; ?>/bk/prosesEditKasus" class="p-4 sm:p-6 space-y-5">
            <input type="hidden" name="id_kasus" value="<?= $kasus['id_kasus'] ?>">

            <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Kategori</label>
                <select name="kategori" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <?php foreach (['pelanggaran','akademik','pribadi','sosial','karir'] as $kat): ?>
                        <option value="<?= $kat ?>" <?= $kasus['kategori'] === $kat ? 'selected' : '' ?>><?= ucfirst($kat) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Judul Kasus</label>
                <input type="text" name="judul" required maxlength="255" value="<?= htmlspecialchars($kasus['judul']) ?>"
                       class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Deskripsi</label>
                <textarea name="deskripsi" rows="4"
                          class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"><?= htmlspecialchars($kasus['deskripsi']) ?></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-secondary-700 mb-1">Tingkat</label>
                    <select name="tingkat" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <?php foreach (['ringan','sedang','berat'] as $t): ?>
                            <option value="<?= $t ?>" <?= $kasus['tingkat'] === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-secondary-700 mb-1">Poin</label>
                    <input type="number" name="poin" value="<?= $kasus['poin'] ?>" min="0" max="100"
                           class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-secondary-700 mb-1">Status</label>
                    <select name="status" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <?php foreach (['baru','proses','selesai','dirujuk'] as $st): ?>
                            <option value="<?= $st ?>" <?= $kasus['status'] === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-secondary-700 mb-1">Guru BK</label>
                    <select name="id_guru_bk" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">-- Belum ditentukan --</option>
                        <?php foreach ($guruBKList as $g): ?>
                            <option value="<?= $g['id_guru'] ?>" <?= ($kasus['id_guru_bk'] ?? 0) == $g['id_guru'] ? 'selected' : '' ?>><?= htmlspecialchars($g['nama_guru']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Tindak Lanjut</label>
                <textarea name="tindak_lanjut" rows="3"
                          class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"><?= htmlspecialchars($kasus['tindak_lanjut'] ?? '') ?></textarea>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="btn-primary px-6 py-2.5 text-sm rounded-xl flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i> Simpan Perubahan
                </button>
                <a href="<?= BASEURL; ?>/bk/detailKasus/<?= $kasus['id_kasus'] ?>" class="px-6 py-2.5 text-sm text-secondary-600 hover:text-secondary-800 border border-gray-200 rounded-xl">
                    Batal
                </a>
            </div>
        </form>
    </div>
</main>
