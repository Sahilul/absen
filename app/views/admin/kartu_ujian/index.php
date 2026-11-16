<?php
// File: app/views/admin/kartu_ujian/index.php
// v1.37.0 - Dashboard Kartu Ujian (admin)
$kartuList = $data['kartu_list'] ?? [];
$lembagaList = $data['lembaga_list'] ?? [];
$kelasList = $data['kelas_list'] ?? [];
$e = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
?>
<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6" x-data="{ editId: 0, editNama: '' }">
    <?php Flasher::flash(); ?>

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Kartu Ujian</h2>
        <p class="text-gray-600 mt-1">Cetak kartu peserta ujian &amp; kartu meja — kop otomatis sesuai lembaga (MTs/MA) — <strong><?= $e($_SESSION['nama_semester_aktif'] ?? ''); ?></strong></p>
    </div>

    <!-- Form tambah / edit -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
        <form method="POST" action="<?= BASEURL; ?>/kartuUjian/simpan" class="flex flex-col sm:flex-row gap-3 sm:items-end">
            <input type="hidden" name="id_kartu_ujian" :value="editId">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1" x-text="editId ? 'Ubah Nama Ujian' : 'Nama Ujian Baru'"></label>
                <input type="text" name="nama_ujian" x-model="editNama" required maxlength="200"
                    placeholder="contoh: Sumatif Tengah Semester Ganjil 2026/2027"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg flex items-center justify-center">
                <i data-lucide="save" class="w-4 h-4 mr-2"></i><span x-text="editId ? 'Simpan Perubahan' : 'Tambah Ujian'"></span>
            </button>
            <button type="button" x-show="editId" x-cloak @click="editId = 0; editNama = ''"
                class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2 px-4 rounded-lg">Batal</button>
        </form>
    </div>

    <?php if (empty($lembagaList)): ?>
        <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-lg p-4 mb-6 text-sm">
            Belum ada data lembaga aktif. Atur kop &amp; kepala di menu Pengaturan Lembaga agar kartu memakai kop yang benar.
        </div>
    <?php endif; ?>

    <!-- Daftar ujian -->
    <?php if (empty($kartuList)): ?>
        <div class="bg-white rounded-xl border border-dashed border-gray-300 p-10 text-center text-gray-500">
            <i data-lucide="id-card" class="w-10 h-10 mx-auto mb-2 text-gray-400"></i>
            Belum ada ujian. Tambahkan nama ujian di atas.
        </div>
    <?php endif; ?>

    <div class="space-y-4">
        <?php foreach ($kartuList as $k): ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-800"><?= $e($k['nama_ujian']); ?></h3>
                        <p class="text-xs text-gray-500 mt-0.5"><?= $e(trim(($k['nama_tp'] ?? '') . ' ' . ($k['semester'] ?? ''))); ?></p>
                    </div>

                    <form method="GET" action="<?= BASEURL; ?>/kartuUjian/cetak/<?= (int) $k['id_kartu_ujian']; ?>" target="_blank"
                        class="flex flex-col sm:flex-row gap-2 sm:items-center">
                        <select name="filter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            <option value="all">Semua siswa</option>
                            <?php if ($lembagaList): ?>
                                <optgroup label="Per Lembaga">
                                    <?php foreach ($lembagaList as $l): ?>
                                        <option value="lembaga:<?= (int) $l['id_lembaga']; ?>"><?= $e($l['nama_lembaga']); ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endif; ?>
                            <?php if ($kelasList): ?>
                                <optgroup label="Per Kelas">
                                    <?php foreach ($kelasList as $kl): ?>
                                        <option value="kelas:<?= (int) $kl['id_kelas']; ?>">Kelas <?= $e($kl['nama_kelas']); ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endif; ?>
                        </select>
                        <button type="submit" name="jenis" value="kartu"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium py-2 px-3 rounded-lg flex items-center justify-center">
                            <i data-lucide="id-card" class="w-4 h-4 mr-1.5"></i>Kartu Ujian
                        </button>
                        <button type="submit" name="jenis" value="meja"
                            class="bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium py-2 px-3 rounded-lg flex items-center justify-center">
                            <i data-lucide="armchair" class="w-4 h-4 mr-1.5"></i>Kartu Meja
                        </button>
                    </form>

                    <div class="flex gap-2">
                        <button type="button" title="Ubah nama"
                            @click="editId = <?= (int) $k['id_kartu_ujian']; ?>; editNama = <?= $e(json_encode($k['nama_ujian'])); ?>; window.scrollTo({top:0,behavior:'smooth'})"
                            class="p-2 rounded-lg text-amber-600 hover:bg-amber-50">
                            <i data-lucide="pencil" class="w-4 h-4"></i>
                        </button>
                        <form method="POST" action="<?= BASEURL; ?>/kartuUjian/hapus/<?= (int) $k['id_kartu_ujian']; ?>"
                            onsubmit="return confirm('Hapus ujian ini? Kartu yang sudah dicetak tidak bisa diverifikasi lagi.');">
                            <button type="submit" title="Hapus" class="p-2 rounded-lg text-red-600 hover:bg-red-50">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>
