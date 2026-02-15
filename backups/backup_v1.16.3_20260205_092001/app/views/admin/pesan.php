<?php // Admin - Kirim Pesan WhatsApp Massal ?>

<!-- Page Content -->
<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gradient-to-br from-gray-50 to-gray-100 p-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div class="flex items-center gap-4">
            <div class="bg-gradient-to-br from-green-500 to-green-600 p-4 rounded-xl shadow-lg">
                <i data-lucide="send" class="w-7 h-7 text-white"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Kirim Pesan WhatsApp</h1>
                <p class="text-sm text-gray-500 mt-1">Kirim pesan massal ke guru, siswa, atau orang tua</p>
            </div>
        </div>

        <a href="<?= BASEURL ?>/admin/antrianWa"
            class="px-5 py-2.5 bg-white hover:bg-gray-50 text-gray-700 rounded-xl text-sm font-medium flex items-center gap-2 shadow-sm border border-gray-200 transition-all w-fit">
            <i data-lucide="list" class="w-4 h-4"></i>
            <span>Lihat Antrian</span>
        </a>
    </div>

    <!-- Flasher -->
    <?php Flasher::flash(); ?>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Guru -->
        <div
            class="group bg-white p-6 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-blue-200">
            <div class="flex items-center justify-between mb-4">
                <div
                    class="bg-gradient-to-br from-blue-500 to-blue-600 p-3 rounded-xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <i data-lucide="users" class="w-6 h-6 text-white"></i>
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-gray-500 mb-1">Total Guru</p>
                    <p
                        class="text-3xl font-extrabold bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent">
                        <?= $data['total_guru'] ?? 0 ?>
                    </p>
                </div>
            </div>
            <div class="text-xs text-gray-500">Dengan nomor WhatsApp</div>
        </div>

        <!-- Total Siswa -->
        <div
            class="group bg-white p-6 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-purple-200">
            <div class="flex items-center justify-between mb-4">
                <div
                    class="bg-gradient-to-br from-purple-500 to-purple-600 p-3 rounded-xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <i data-lucide="graduation-cap" class="w-6 h-6 text-white"></i>
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-gray-500 mb-1">Total Siswa</p>
                    <p
                        class="text-3xl font-extrabold bg-gradient-to-r from-purple-600 to-purple-800 bg-clip-text text-transparent">
                        <?= $data['total_siswa'] ?? 0 ?>
                    </p>
                </div>
            </div>
            <div class="text-xs text-gray-500">Dengan nomor WhatsApp</div>
        </div>

        <!-- Total Orang Tua -->
        <div
            class="group bg-white p-6 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-green-200">
            <div class="flex items-center justify-between mb-4">
                <div
                    class="bg-gradient-to-br from-green-500 to-green-600 p-3 rounded-xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <i data-lucide="home" class="w-6 h-6 text-white"></i>
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-gray-500 mb-1">Orang Tua</p>
                    <p
                        class="text-3xl font-extrabold bg-gradient-to-r from-green-600 to-green-800 bg-clip-text text-transparent">
                        <?= $data['total_ortu'] ?? 0 ?>
                    </p>
                </div>
            </div>
            <div class="text-xs text-gray-500">Ayah + Ibu dengan nomor</div>
        </div>

        <!-- Antrian Pending -->
        <div
            class="group bg-white p-6 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-yellow-200">
            <div class="flex items-center justify-between mb-4">
                <div
                    class="bg-gradient-to-br from-yellow-500 to-yellow-600 p-3 rounded-xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <i data-lucide="clock" class="w-6 h-6 text-white"></i>
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-gray-500 mb-1">Antrian</p>
                    <p
                        class="text-3xl font-extrabold bg-gradient-to-r from-yellow-600 to-yellow-800 bg-clip-text text-transparent">
                        <?= $data['queue_pending'] ?? 0 ?>
                    </p>
                </div>
            </div>
            <div class="text-xs text-gray-500">Pesan menunggu kirim</div>
        </div>
    </div>

    <!-- Form Kirim Pesan -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-green-50 to-emerald-50">
            <div class="flex items-center gap-3">
                <div class="bg-white p-2 rounded-lg shadow-sm">
                    <i data-lucide="edit-3" class="w-5 h-5 text-green-600"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-800">Tulis Pesan Baru</h2>
                    <p class="text-sm text-gray-500">Pilih target penerima dan tulis pesan Anda</p>
                </div>
            </div>
        </div>

        <form action="<?= BASEURL ?>/admin/prosesKirimPesanWa" method="POST" class="p-6">
            <!-- Target Selection -->
            <div class="grid md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Target Penerima <span class="text-red-500">*</span>
                    </label>
                    <select name="target_type" id="target_type" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white text-gray-900 text-sm">
                        <option value="">-- Pilih Target --</option>
                        <option value="semua_guru">👨‍🏫 Semua Guru</option>
                        <option value="semua_siswa">👨‍🎓 Semua Siswa</option>
                        <option value="semua_ortu">👪 Semua Orang Tua</option>
                        <option value="kelas_siswa">📚 Siswa Per Kelas</option>
                        <option value="kelas_ortu">📚 Orang Tua Per Kelas</option>
                    </select>
                </div>

                <div id="kelas_filter" class="hidden">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Pilih Kelas
                    </label>
                    <select name="id_kelas" id="id_kelas"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white text-gray-900 text-sm">
                        <option value="">-- Semua Kelas --</option>
                        <?php foreach ($data['kelas'] ?? [] as $k): ?>
                            <option value="<?= $k['id_kelas'] ?>"><?= htmlspecialchars($k['nama_kelas']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Preview Penerima -->
            <div id="preview_penerima"
                class="mb-6 p-4 bg-gradient-to-r from-indigo-50 to-blue-50 rounded-xl border border-indigo-200 hidden">
                <div class="flex items-center gap-3 text-sm text-indigo-800">
                    <div class="bg-white p-2 rounded-lg shadow-sm">
                        <i data-lucide="users" class="w-4 h-4 text-indigo-600"></i>
                    </div>
                    <span>Estimasi penerima: <strong id="jumlah_penerima" class="text-lg">0</strong> nomor
                        WhatsApp</span>
                </div>
            </div>

            <!-- Pesan -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Isi Pesan <span class="text-red-500">*</span>
                </label>
                <textarea name="pesan" id="pesan" rows="8" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white text-gray-900 resize-none text-sm"
                    placeholder="Tulis pesan Anda di sini...

Contoh:
Assalamualaikum Bapak/Ibu,

Kami informasikan bahwa besok Senin 15 Januari 2026 akan diadakan rapat wali murid.

Terima kasih.
- Sekolah"></textarea>
                <p class="text-xs text-gray-500 mt-2">
                    💡 Gunakan <code class="bg-gray-100 px-1 rounded">*teks*</code> untuk <strong>bold</strong> dan
                    <code class="bg-gray-100 px-1 rounded">_teks_</code> untuk <em>italic</em>
                </p>
            </div>

            <!-- Info -->
            <div class="mb-6 p-5 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-xl">
                <div class="flex gap-4">
                    <div class="bg-white p-2 rounded-lg shadow-sm flex-shrink-0">
                        <i data-lucide="info" class="w-5 h-5 text-green-600"></i>
                    </div>
                    <div class="text-sm text-green-800">
                        <p class="font-bold">Pesan akan masuk antrian</p>
                        <p class="text-green-700 mt-1">
                            Pesan dikirim bertahap dengan jeda 10 detik untuk mencegah blokir WhatsApp.
                            Monitor progress di <a href="<?= BASEURL ?>/admin/antrianWa"
                                class="underline font-medium">Antrian WA</a>.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex justify-end">
                <button type="submit"
                    class="px-8 py-3 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white rounded-xl text-sm font-semibold flex items-center gap-2 shadow-lg hover:shadow-xl transition-all">
                    <i data-lucide="send" class="w-5 h-5"></i>
                    <span>Kirim ke Antrian</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Recent Queue -->
    <?php if (!empty($data['recent_queue'])): ?>
        <div class="mt-8 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-bold text-gray-800 flex items-center gap-2">
                    <i data-lucide="clock" class="w-5 h-5 text-gray-500"></i>
                    Pengiriman Terakhir
                </h3>
                <a href="<?= BASEURL ?>/admin/antrianWa" class="text-sm text-green-600 hover:text-green-800 font-medium">
                    Lihat semua →
                </a>
            </div>
            <div class="divide-y divide-gray-100 max-h-72 overflow-y-auto">
                <?php foreach (array_slice($data['recent_queue'], 0, 10) as $q): ?>
                    <div class="p-4 flex items-center gap-4 hover:bg-gray-50 transition-colors">
                        <span class="px-3 py-1.5 rounded-full text-xs font-semibold
                    <?php
                    switch ($q['status']) {
                        case 'pending':
                            echo 'bg-yellow-100 text-yellow-800';
                            break;
                        case 'sent':
                            echo 'bg-green-100 text-green-800';
                            break;
                        case 'failed':
                            echo 'bg-red-100 text-red-800';
                            break;
                        default:
                            echo 'bg-gray-100 text-gray-800';
                    }
                    ?>">
                            <?= ucfirst($q['status']) ?>
                        </span>
                        <span class="text-gray-700 font-mono text-xs bg-gray-100 px-2 py-1 rounded"><?= $q['no_wa'] ?></span>
                        <span
                            class="text-xs text-gray-500 truncate max-w-xs hidden sm:block"><?= htmlspecialchars(mb_substr($q['pesan'], 0, 50)) ?>...</span>
                        <span class="text-gray-400 text-xs ml-auto"><?= date('d/m H:i', strtotime($q['created_at'])) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</main>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const targetType = document.getElementById('target_type');
        const kelasFilter = document.getElementById('kelas_filter');
        const previewPenerima = document.getElementById('preview_penerima');
        const jumlahPenerima = document.getElementById('jumlah_penerima');

        const counts = {
            semua_guru: <?= $data['total_guru'] ?? 0 ?>,
            semua_siswa: <?= $data['total_siswa_wa'] ?? 0 ?>,
            semua_ortu: <?= $data['total_ortu'] ?? 0 ?>,
            kelas_siswa: 0,
            kelas_ortu: 0
        };

        targetType.addEventListener('change', function () {
            const value = this.value;

            // Show/hide kelas filter
            if (value === 'kelas_siswa' || value === 'kelas_ortu') {
                kelasFilter.classList.remove('hidden');
            } else {
                kelasFilter.classList.add('hidden');
            }

            // Show preview
            if (value && counts[value] !== undefined) {
                previewPenerima.classList.remove('hidden');
                jumlahPenerima.textContent = counts[value];
            } else if (value === 'kelas_siswa' || value === 'kelas_ortu') {
                previewPenerima.classList.remove('hidden');
                jumlahPenerima.textContent = 'Per kelas';
            } else {
                previewPenerima.classList.add('hidden');
            }
        });
    });

    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>