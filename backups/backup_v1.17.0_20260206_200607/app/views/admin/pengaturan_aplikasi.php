<?php
// Cek apakah file logo ada - gunakan absolute path
$logoFile = $data['pengaturan']['logo'] ?? '';
$baseDir = dirname(dirname(dirname(__DIR__))); // Path ke root folder absen
$logoPath = $baseDir . '/public/img/app/' . $logoFile;
$logoExists = !empty($logoFile) && file_exists($logoPath);
?>
<main class="flex-1 overflow-x-hidden overflow-y-auto bg-secondary-50 p-4 md:p-6">
    <!-- Header -->
    <div class="mb-4">
        <div class="bg-white shadow-sm rounded-xl p-4 md:p-5">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                    <h4 class="text-lg md:text-xl font-bold text-slate-800 mb-1">Pengaturan Aplikasi</h4>
                    <p class="text-slate-500 text-sm">
                        Kelola nama aplikasi dan logo
                    </p>
                </div>
            </div>
        </div>
    </div>

    <?php Flasher::flash(); ?>

    <!-- Form Pengaturan -->
    <div class="bg-white shadow-sm rounded-xl p-5 md:p-6">
        <form action="<?= BASEURL ?>/admin/simpanPengaturanAplikasi" method="POST" enctype="multipart/form-data">
            <div class="grid grid-cols-1 gap-6">

                <!-- Nama Aplikasi -->
                <div>
                    <label for="nama_aplikasi" class="block text-sm font-semibold text-slate-700 mb-2">
                        <i data-lucide="type" class="w-4 h-4 inline-block mr-1"></i>
                        Nama Aplikasi
                    </label>
                    <input type="text" id="nama_aplikasi" name="nama_aplikasi"
                        value="<?= htmlspecialchars($data['pengaturan']['nama_aplikasi'] ?? 'Smart Absensi') ?>"
                        class="w-full border border-slate-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-primary-200 focus:border-primary-400 transition-all"
                        placeholder="Contoh: Smart Absensi" required>
                    <p class="text-xs text-slate-500 mt-1">Nama ini akan ditampilkan di halaman login, sidebar, dan
                        title browser</p>
                </div>

                <!-- URL Website -->
                <div>
                    <label for="url_web" class="block text-sm font-semibold text-slate-700 mb-2">
                        <i data-lucide="link" class="w-4 h-4 inline-block mr-1"></i>
                        URL Website Absensi
                    </label>
                    <input type="url" id="url_web" name="url_web"
                        value="<?= htmlspecialchars($data['pengaturan']['url_web'] ?? 'http://localhost/absen') ?>"
                        class="w-full border border-slate-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-primary-200 focus:border-primary-400 transition-all"
                        placeholder="Contoh: https://absen.sekolah.sch.id" required>
                    <p class="text-xs text-slate-500 mt-1">URL ini akan digunakan untuk QR Code dan ditampilkan di Kartu
                        Login</p>
                </div>

                <!-- Logo Aplikasi -->
                <div>
                    <label for="logo" class="block text-sm font-semibold text-slate-700 mb-2">
                        <i data-lucide="image" class="w-4 h-4 inline-block mr-1"></i>
                        Logo Aplikasi
                    </label>

                    <?php if ($logoExists): ?>
                        <div class="mb-3 p-4 bg-slate-50 rounded-lg border border-slate-200">
                            <div class="flex items-center justify-center">
                                <img src="<?= BASEURL ?>/public/img/app/<?= htmlspecialchars($logoFile) ?>"
                                    alt="Logo Aplikasi" class="max-w-full h-auto max-h-24 object-contain">
                            </div>
                            <p class="text-xs text-slate-500 mt-2 text-center break-all"><?= htmlspecialchars($logoFile) ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="mb-3 p-4 bg-slate-50 rounded-lg border border-slate-200 border-dashed">
                            <div class="flex flex-col items-center justify-center py-4">
                                <i data-lucide="image-off" class="w-10 h-10 text-slate-300 mb-2"></i>
                                <p class="text-xs text-slate-400">Belum ada logo</p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <input type="file" id="logo" name="logo" accept="image/*"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-200 focus:border-primary-400 transition-all file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                    <p class="text-xs text-slate-500 mt-1">Format: PNG, JPG, SVG. Ukuran maksimal: 2MB. Logo juga
                        digunakan sebagai favicon di tab browser.</p>
                </div>

            </div>


            <!-- Preview Section -->
            <div class="mt-6 p-4 bg-gradient-to-r from-slate-50 to-blue-50 rounded-xl border border-slate-200">
                <h5 class="text-sm font-bold text-slate-700 mb-3">
                    <i data-lucide="eye" class="w-4 h-4 inline-block mr-1"></i>
                    Preview
                </h5>
                <div class="flex items-center gap-3 bg-white rounded-lg p-4 shadow-sm">
                    <?php if ($logoExists): ?>
                        <img src="<?= BASEURL ?>/public/img/app/<?= htmlspecialchars($logoFile) ?>" alt="Logo"
                            class="w-10 h-10 object-contain">
                    <?php else: ?>
                        <div
                            class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center">
                            <i data-lucide="graduation-cap" class="w-6 h-6 text-white"></i>
                        </div>
                    <?php endif; ?>
                    <div>
                        <h6 class="text-lg font-bold text-slate-800">
                            <?= htmlspecialchars($data['pengaturan']['nama_aplikasi'] ?? 'Smart Absensi') ?>
                        </h6>
                        <p class="text-xs text-slate-500">Sistem Informasi Absensi Digital</p>
                    </div>
                </div>
            </div>

            <!-- WA Gateway Settings -->
            <div class="mt-6 p-5 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border border-green-200">
                <div class="flex items-center gap-3 mb-4">
                    <div class="bg-green-100 p-2 rounded-lg">
                        <i data-lucide="message-circle" class="w-5 h-5 text-green-600"></i>
                    </div>
                    <div>
                        <h5 class="text-lg font-bold text-green-800">WhatsApp Gateway</h5>
                        <p class="text-sm text-green-600">Konfigurasi gateway untuk notifikasi WA</p>
                    </div>
                </div>

                <?php
                $currentProvider = $data['pengaturan']['wa_gateway_provider'] ?? 'fonnte';
                $providers = [
                    'fonnte' => ['name' => 'Fonnte', 'auth' => 'token', 'url' => 'https://api.fonnte.com/send'],
                    'gowa' => ['name' => 'Go-WhatsApp (Self-Hosted)', 'auth' => 'basic', 'url' => 'http://localhost:3000/send/message'],
                    'wablas' => ['name' => 'Wablas', 'auth' => 'token', 'url' => 'https://solo.wablas.com/api/send-message'],
                    'dripsender' => ['name' => 'Dripsender', 'auth' => 'token', 'url' => 'https://api.dripsender.id/send'],
                    'starsender' => ['name' => 'Starsender', 'auth' => 'token', 'url' => 'https://api.starsender.online/api/send'],
                    'onesender' => ['name' => 'OneSender 2.0', 'auth' => 'token', 'url' => 'https://api.onesender.id/api/v1/message/send-text'],
                ];
                ?>

                <div class="grid md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Provider Gateway</label>
                        <select name="wa_gateway_provider" id="wa_provider"
                            onchange="toggleAuthFields(); updateUrlFromProvider()"
                            class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-200 focus:border-green-400 bg-white">
                            <?php foreach ($providers as $key => $prov): ?>
                                <option value="<?= $key ?>" <?= $currentProvider === $key ? 'selected' : '' ?>
                                    data-auth="<?= $prov['auth'] ?>" data-url="<?= $prov['url'] ?>">
                                    <?= $prov['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">API URL</label>
                        <input type="text" name="wa_gateway_url" id="wa_url"
                            value="<?= htmlspecialchars($data['pengaturan']['wa_gateway_url'] ?? 'https://api.fonnte.com/send') ?>"
                            class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-200 focus:border-green-400 bg-white"
                            placeholder="https://api.fonnte.com/send">
                    </div>
                </div>

                <!-- Token Auth Fields -->
                <div id="token_auth_fields" class="grid md:grid-cols-1 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Token API</label>
                        <input type="password" name="wa_gateway_token" id="wa_token"
                            value="<?= htmlspecialchars($data['pengaturan']['wa_gateway_token'] ?? '') ?>"
                            class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-200 focus:border-green-400 bg-white"
                            placeholder="Masukkan token dari dashboard provider">
                    </div>
                </div>

                <!-- Basic Auth Fields (Go-WA) -->
                <div id="basic_auth_fields" class="grid md:grid-cols-2 gap-4 mb-4 hidden">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Username</label>
                        <input type="text" name="wa_gateway_username" id="wa_username"
                            value="<?= htmlspecialchars($data['pengaturan']['wa_gateway_username'] ?? '') ?>"
                            class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-200 focus:border-green-400 bg-white"
                            placeholder="Username untuk Basic Auth">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Password</label>
                        <input type="password" name="wa_gateway_password" id="wa_password"
                            value="<?= htmlspecialchars($data['pengaturan']['wa_gateway_password'] ?? '') ?>"
                            class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-200 focus:border-green-400 bg-white"
                            placeholder="Password untuk Basic Auth">
                    </div>
                </div>

                <p class="text-xs text-green-600 mt-2">
                    <i data-lucide="info" class="w-3 h-3 inline"></i>
                    Go-WhatsApp: Jalankan dengan <code class="bg-green-100 px-1 rounded">--basic-auth="user:pass"</code>
                </p>

                <!-- Test WA Section -->
                <div class="mt-4 pt-4 border-t border-green-200">
                    <h6 class="text-sm font-semibold text-slate-700 mb-3">Test Kirim Pesan</h6>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <input type="text" id="test_no_wa" placeholder="Nomor WA (08xxx)"
                            class="flex-1 px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-200 focus:border-green-400 bg-white">
                        <button type="button" onclick="testKirimWA()" id="btn_test_wa"
                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                            <i data-lucide="send" class="w-4 h-4"></i>
                            Kirim Test
                        </button>
                    </div>
                    <div id="test_result" class="mt-3 hidden"></div>
                </div>
                <!-- Template WA Group Section -->
                <div class="mt-6 pt-4 border-t border-green-200">
                    <h6 class="text-sm font-semibold text-slate-700 mb-3 block">Template Pesan WhatsApp ke Grup</h6>
                    <div class="mb-2">
                        <textarea name="wa_template_group_absensi" rows="6"
                            class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-200 focus:border-green-400 bg-white font-mono text-sm"
                            placeholder="Ketik template pesan di sini..."><?= htmlspecialchars($data['pengaturan']['wa_template_group_absensi'] ?? '') ?></textarea>
                    </div>
                    <div class="bg-yellow-50 p-3 rounded-lg border border-yellow-200 text-xs text-yellow-800">
                        <strong>Variabel yang tersedia:</strong>
                        <ul class="mt-1 list-disc list-inside grid grid-cols-2 gap-x-4">
                            <li>{{tanggal}} : Tanggal Absensi</li>
                            <li>{{waktu_cetak}} : Waktu Kirim</li>
                            <li>{{sekolah}} : Nama Sekolah</li>
                            <li>{{kelas}} : Nama Kelas</li>
                            <li>{{jumlah_siswa}} : Total Siswa</li>
                            <li>{{hadir}} : Jumlah Hadir</li>
                            <li>{{sakit}} : Jumlah Sakit</li>
                            <li>{{izin}} : Jumlah Izin</li>
                            <li>{{alpha}} : Jumlah Alpha</li>
                            <li>{{terlambat}} : Jumlah Terlambat</li>
                            <li>{{tapel}} : Tahun Pelajaran</li>
                            <li>{{semester}} : Semester</li>
                            <li>{{list_sakit}} : Daftar Nama Sakit</li>
                            <li>{{list_izin}} : Daftar Nama Izin</li>
                            <li>{{list_alpha}} : Daftar Nama Alpha</li>
                            <li>{{list_terlambat}} : Daftar Nama Terlambat</li>
                        </ul>
                    </div>
                </div>

            </div>

            <!-- Submit Button -->
            <div class="mt-6 flex flex-col sm:flex-row gap-3">
                <button type="submit"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Simpan Pengaturan
                </button>
                <a href="<?= BASEURL ?>/admin/dashboard"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-lg transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    Kembali
                </a>
            </div>
        </form>
    </div>

    <!-- Info Box -->
    <div class="mt-5 bg-blue-50 border border-blue-200 rounded-xl p-4">
        <div class="flex gap-3">
            <div class="shrink-0">
                <i data-lucide="info" class="w-5 h-5 text-blue-600"></i>
            </div>
            <div class="flex-1">
                <h6 class="font-semibold text-blue-900 text-sm mb-1">Informasi</h6>
                <ul class="text-xs text-blue-700 space-y-1 list-disc list-inside">
                    <li><strong>Nama Aplikasi:</strong> Ditampilkan di halaman login, sidebar, dan title browser</li>
                    <li><strong>Logo:</strong> Ditampilkan di halaman login, sidebar, dan sebagai favicon (icon tab
                        browser)</li>
                    <li>Rekomendasi: Gunakan gambar PNG dengan background transparan, rasio 1:1</li>
                    <li>Perubahan akan langsung terlihat setelah menyimpan dan me-refresh halaman</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- =============================================== -->
    <!-- SECTION: PENGATURAN DOKUMEN -->
    <!-- =============================================== -->
    <div class="mt-6 bg-white shadow-sm rounded-xl p-5 md:p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
            <div>
                <h4 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                    <i data-lucide="folder-cog" class="w-5 h-5 text-primary-600"></i>
                    Pengaturan Jenis Dokumen
                </h4>
                <p class="text-slate-500 text-sm mt-1">
                    Kelola jenis dokumen yang dapat diupload oleh siswa dan pendaftar PSB
                </p>
            </div>
            <button type="button" onclick="openDokumenModal()"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Tambah Dokumen
            </button>
        </div>

        <!-- Document Types Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-y border-slate-200">
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">#</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Nama Dokumen</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Kode</th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-600">Wajib PSB</th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-600">Wajib Siswa</th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-600">Status</th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php
                    $dokumenList = $data['dokumen_config'] ?? [];
                    $no = 1;
                    foreach ($dokumenList as $doc):
                        ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-slate-500"><?= $no++ ?></td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 bg-primary-100 rounded-lg flex items-center justify-center">
                                        <i data-lucide="<?= htmlspecialchars($doc['icon'] ?? 'file-text') ?>"
                                            class="w-4 h-4 text-primary-600"></i>
                                    </div>
                                    <span class="font-medium text-slate-800"><?= htmlspecialchars($doc['nama']) ?></span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <code
                                    class="text-xs bg-slate-100 px-2 py-1 rounded"><?= htmlspecialchars($doc['kode']) ?></code>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <button onclick="toggleDokumen(<?= $doc['id'] ?>, 'wajib_psb')"
                                    class="w-10 h-6 rounded-full transition-colors <?= $doc['wajib_psb'] ? 'bg-green-500' : 'bg-slate-300' ?>">
                                    <span
                                        class="block w-4 h-4 bg-white rounded-full shadow transform transition-transform <?= $doc['wajib_psb'] ? 'translate-x-5' : 'translate-x-1' ?>"></span>
                                </button>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <button onclick="toggleDokumen(<?= $doc['id'] ?>, 'wajib_siswa')"
                                    class="w-10 h-6 rounded-full transition-colors <?= $doc['wajib_siswa'] ? 'bg-green-500' : 'bg-slate-300' ?>">
                                    <span
                                        class="block w-4 h-4 bg-white rounded-full shadow transform transition-transform <?= $doc['wajib_siswa'] ? 'translate-x-5' : 'translate-x-1' ?>"></span>
                                </button>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <button onclick="toggleDokumen(<?= $doc['id'] ?>, 'aktif')"
                                    class="px-3 py-1 rounded-full text-xs font-medium <?= $doc['aktif'] ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500' ?>">
                                    <?= $doc['aktif'] ? 'Aktif' : 'Nonaktif' ?>
                                </button>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button onclick="editDokumen(<?= htmlspecialchars(json_encode($doc)) ?>)"
                                        class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="Edit">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </button>
                                    <button
                                        onclick="hapusDokumen(<?= $doc['id'] ?>, '<?= htmlspecialchars($doc['nama']) ?>')"
                                        class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Hapus">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($dokumenList)): ?>
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-400">
                                <i data-lucide="folder-x" class="w-10 h-10 mx-auto mb-2"></i>
                                <p>Belum ada jenis dokumen</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tambah/Edit Dokumen -->
    <div id="dokumenModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
            <div class="px-6 py-4 border-b flex items-center justify-between">
                <h5 id="dokumenModalTitle" class="text-lg font-bold text-slate-800">Tambah Jenis Dokumen</h5>
                <button onclick="closeDokumenModal()" class="p-2 hover:bg-slate-100 rounded-lg">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form id="dokumenForm" action="<?= BASEURL ?>/admin/simpanDokumenConfig" method="POST">
                <input type="hidden" name="id" id="dokumen_id">
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Kode Dokumen</label>
                        <input type="text" name="kode" id="dokumen_kode" required pattern="[a-z_]+"
                            title="Hanya huruf kecil dan underscore"
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-200"
                            placeholder="contoh: kartu_keluarga">
                        <p class="text-xs text-slate-500 mt-1">Huruf kecil dan underscore saja</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nama Dokumen</label>
                        <input type="text" name="nama" id="dokumen_nama" required
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-200"
                            placeholder="contoh: Kartu Keluarga (KK)">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Icon (Lucide)</label>
                        <input type="text" name="icon" id="dokumen_icon" value="file-text"
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-200"
                            placeholder="file-text">
                        <p class="text-xs text-slate-500 mt-1">Lihat: <a href="https://lucide.dev/icons" target="_blank"
                                class="text-primary-600 hover:underline">lucide.dev/icons</a></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Urutan</label>
                        <input type="number" name="urutan" id="dokumen_urutan" value="0" min="0"
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-200">
                    </div>
                    <div class="flex gap-6">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="wajib_psb" id="dokumen_wajib_psb" value="1"
                                class="w-4 h-4 rounded text-primary-600">
                            <span class="text-sm text-slate-700">Wajib PSB</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="wajib_siswa" id="dokumen_wajib_siswa" value="1"
                                class="w-4 h-4 rounded text-primary-600">
                            <span class="text-sm text-slate-700">Wajib Siswa</span>
                        </label>
                    </div>
                </div>
                <div class="px-6 py-4 border-t bg-slate-50 flex justify-end gap-3 rounded-b-2xl">
                    <button type="button" onclick="closeDokumenModal()"
                        class="px-4 py-2 text-slate-600 hover:bg-slate-200 rounded-lg">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
    // Initialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    // Toggle auth fields based on provider
    function toggleAuthFields() {
        const provider = document.getElementById('wa_provider');
        const selectedOption = provider.options[provider.selectedIndex];
        const authType = selectedOption.dataset.auth;

        const tokenFields = document.getElementById('token_auth_fields');
        const basicFields = document.getElementById('basic_auth_fields');

        if (authType === 'basic') {
            tokenFields.classList.add('hidden');
            basicFields.classList.remove('hidden');
        } else {
            tokenFields.classList.remove('hidden');
            basicFields.classList.add('hidden');
        }
    }

    // Update URL when provider changes
    function updateUrlFromProvider() {
        const provider = document.getElementById('wa_provider');
        const selectedOption = provider.options[provider.selectedIndex];
        const defaultUrl = selectedOption.dataset.url;
        const urlField = document.getElementById('wa_url');

        urlField.value = defaultUrl;
    }

    // Run on page load
    document.addEventListener('DOMContentLoaded', toggleAuthFields);

    // Test Kirim WA
    async function testKirimWA() {
        const noWa = document.getElementById('test_no_wa').value.trim();
        const provider = document.getElementById('wa_provider').value;
        const token = document.getElementById('wa_token')?.value.trim() || '';
        const username = document.getElementById('wa_username')?.value.trim() || '';
        const password = document.getElementById('wa_password')?.value.trim() || '';
        const url = document.getElementById('wa_url')?.value.trim() || '';
        const btn = document.getElementById('btn_test_wa');
        const result = document.getElementById('test_result');

        if (!noWa) {
            result.innerHTML = '<div class="p-3 bg-red-50 text-red-700 rounded-lg text-sm">Masukkan nomor WA terlebih dahulu</div>';
            result.classList.remove('hidden');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 animate-spin"></i> Mengirim...';
        result.innerHTML = '';
        result.classList.add('hidden');

        try {
            const response = await fetch('<?= BASEURL ?>/admin/testWaGateway', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `no_wa=${encodeURIComponent(noWa)}&provider=${encodeURIComponent(provider)}&token=${encodeURIComponent(token)}&username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}&url=${encodeURIComponent(url)}`
            });

            const data = await response.json();

            if (data.success) {
                result.innerHTML = '<div class="p-3 bg-green-50 text-green-700 rounded-lg text-sm flex items-center gap-2"><i data-lucide="check-circle" class="w-4 h-4"></i> ' + data.message + '</div>';
            } else {
                result.innerHTML = '<div class="p-3 bg-red-50 text-red-700 rounded-lg text-sm flex items-center gap-2"><i data-lucide="x-circle" class="w-4 h-4"></i> ' + data.message + '</div>';
            }
            result.classList.remove('hidden');
            lucide.createIcons();
        } catch (error) {
            result.innerHTML = '<div class="p-3 bg-red-50 text-red-700 rounded-lg text-sm">Terjadi kesalahan: ' + error.message + '</div>';
            result.classList.remove('hidden');
        }

        btn.disabled = false;
        btn.innerHTML = '<i data-lucide="send" class="w-4 h-4"></i> Kirim Test';
        lucide.createIcons();
    }

    // ==========================================
    // DOCUMENT CONFIG FUNCTIONS
    // ==========================================

    function openDokumenModal() {
        document.getElementById('dokumenModalTitle').textContent = 'Tambah Jenis Dokumen';
        document.getElementById('dokumenForm').reset();
        document.getElementById('dokumen_id').value = '';
        document.getElementById('dokumenModal').classList.remove('hidden');
        lucide.createIcons();
    }

    function closeDokumenModal() {
        document.getElementById('dokumenModal').classList.add('hidden');
    }

    function editDokumen(doc) {
        document.getElementById('dokumenModalTitle').textContent = 'Edit Jenis Dokumen';
        document.getElementById('dokumen_id').value = doc.id;
        document.getElementById('dokumen_kode').value = doc.kode;
        document.getElementById('dokumen_nama').value = doc.nama;
        document.getElementById('dokumen_icon').value = doc.icon || 'file-text';
        document.getElementById('dokumen_urutan').value = doc.urutan || 0;
        document.getElementById('dokumen_wajib_psb').checked = doc.wajib_psb == 1;
        document.getElementById('dokumen_wajib_siswa').checked = doc.wajib_siswa == 1;
        document.getElementById('dokumenModal').classList.remove('hidden');
        lucide.createIcons();
    }

    async function toggleDokumen(id, field) {
        try {
            const response = await fetch('<?= BASEURL ?>/admin/toggleDokumenConfig', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${id}&field=${field}`
            });
            const data = await response.json();
            if (data.success) {
                location.reload();
            } else {
                alert('Gagal: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            alert('Error: ' + error.message);
        }
    }

    async function hapusDokumen(id, nama) {
        if (!confirm(`Yakin ingin menghapus dokumen "${nama}"?`)) return;

        try {
            const response = await fetch('<?= BASEURL ?>/admin/hapusDokumenConfig', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${id}`
            });
            const data = await response.json();
            if (data.success) {
                location.reload();
            } else {
                alert('Gagal: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            alert('Error: ' + error.message);
        }
    }

    // Close modal on escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeDokumenModal();
        }
    });
</script>