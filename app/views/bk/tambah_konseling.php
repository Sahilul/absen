<?php
$kasus = $data['kasus'] ?? [];
$nextKe = $data['next_ke'] ?? 1;
?>
<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gradient-to-br from-secondary-50 to-secondary-100 p-4 sm:p-6">
    <div class="mb-6">
        <a href="<?= BASEURL; ?>/bk/detailKasus/<?= $kasus['id_kasus'] ?>" class="text-sm text-primary-600 hover:underline flex items-center gap-1 mb-2">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Detail Kasus
        </a>
        <h2 class="text-2xl sm:text-3xl font-bold text-secondary-800 flex items-center">
            <i data-lucide="message-circle" class="w-7 h-7 sm:w-8 sm:h-8 mr-2 sm:mr-3 text-green-500"></i>
            Tambah Sesi Konseling
        </h2>
    </div>

    <div class="bg-white rounded-xl shadow-sm border overflow-hidden max-w-3xl mb-4">
        <div class="px-4 sm:px-6 py-3 border-b bg-blue-50">
            <p class="text-sm text-blue-800">
                <span class="font-semibold">Kasus:</span> <?= htmlspecialchars($kasus['judul']) ?> —
                <span class="font-semibold"><?= htmlspecialchars($kasus['nama_siswa']) ?></span> (<?= htmlspecialchars($kasus['nama_kelas']) ?>)
            </p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border overflow-hidden max-w-3xl">
        <div class="px-4 sm:px-6 py-4 border-b bg-gray-50">
            <h3 class="text-sm font-bold text-secondary-800">Konseling Ke-<?= $nextKe ?></h3>
        </div>
        <form method="POST" action="<?= BASEURL; ?>/bk/prosesTambahKonseling" class="p-4 sm:p-6 space-y-5">
            <input type="hidden" name="id_kasus" value="<?= $kasus['id_kasus'] ?>">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-secondary-700 mb-1">Jenis Konseling</label>
                    <select name="jenis" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="individual">Individual</option>
                        <option value="kelompok">Kelompok</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-secondary-700 mb-1">Tanggal</label>
                    <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>" required
                           class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-secondary-700 mb-1">Waktu Mulai</label>
                    <input type="time" name="waktu_mulai"
                           class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-secondary-700 mb-1">Waktu Selesai</label>
                    <input type="time" name="waktu_selesai"
                           class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-secondary-700 mb-1">Tempat</label>
                    <input type="text" name="tempat" placeholder="Ruang BK"
                           class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Catatan Konseling</label>
                <textarea name="catatan" rows="4" placeholder="Apa yang dibahas dalam sesi ini..."
                          class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Hasil</label>
                <textarea name="hasil" rows="3" placeholder="Kesimpulan atau hasil dari sesi konseling..."
                          class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Rencana Tindak Lanjut</label>
                <textarea name="rencana_tindak_lanjut" rows="3" placeholder="Langkah selanjutnya yang perlu dilakukan..."
                          class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"></textarea>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="btn-primary px-6 py-2.5 text-sm rounded-xl flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i> Simpan Konseling
                </button>
                <a href="<?= BASEURL; ?>/bk/detailKasus/<?= $kasus['id_kasus'] ?>" class="px-6 py-2.5 text-sm text-secondary-600 hover:text-secondary-800 border border-gray-200 rounded-xl">
                    Batal
                </a>
            </div>
        </form>
    </div>
</main>
