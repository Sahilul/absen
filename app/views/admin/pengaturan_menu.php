<?php /* File: app/views/admin/pengaturan_menu.php */ ?>

<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gradient-to-br from-secondary-50 to-secondary-100 p-6">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-bold text-secondary-800 flex items-center">
                    <i data-lucide="settings" class="w-8 h-8 mr-3 text-primary-500"></i>
                    Pengaturan Menu
                </h2>
                <p class="text-secondary-600 mt-2">Kelola visibilitas menu untuk role Guru dan Wali Kelas</p>
            </div>
            <div class="hidden md:block">
                <div class="gradient-primary p-3 rounded-xl">
                    <i data-lucide="eye" class="w-6 h-6 text-white"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Flash Message -->
    <?php Flasher::flash(); ?>

    <!-- Form Pengaturan Menu -->
    <div class="glass-effect rounded-xl p-8 border border-white/20 shadow-lg">
        <form method="POST" action="<?= BASEURL; ?>/admin/simpanPengaturanMenu">

            <div class="space-y-6">
                <!-- Info Box -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 flex items-start">
                    <i data-lucide="info" class="w-5 h-5 text-blue-600 mr-3 mt-0.5 flex-shrink-0"></i>
                    <div class="text-sm text-blue-800">
                        <p class="font-medium mb-1">Tentang Pengaturan Menu</p>
                        <p>Nonaktifkan menu yang tidak diperlukan untuk menyederhanakan antarmuka bagi guru dan wali
                            kelas. Perubahan akan berlaku setelah user login ulang atau refresh halaman.</p>
                    </div>
                </div>

                <!-- Menu Input Nilai & Rapor -->
                <div class="border border-secondary-200 rounded-lg p-6 hover:border-primary-300 transition-colors">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center mb-2">
                                <i data-lucide="file-edit" class="w-5 h-5 text-warning-600 mr-2"></i>
                                <h3 class="text-lg font-semibold text-secondary-800">Menu Input Nilai & Rapor</h3>
                            </div>
                            <p class="text-secondary-600 text-sm mb-3">
                                Termasuk: Input Nilai (Harian, STS, SAS), Monitoring Nilai, Pengaturan Rapor, Cetak
                                Rapor
                            </p>
                            <div class="bg-secondary-50 rounded px-3 py-2 text-xs text-secondary-600">
                                <p><strong>Role yang terpengaruh:</strong> Guru, Wali Kelas</p>
                                <p class="mt-1"><strong>Menu yang di-hide:</strong> Section "Input Nilai" di dashboard
                                    guru, "Monitoring Nilai" dan "Cetak Rapor" di sidebar wali kelas</p>
                            </div>
                        </div>
                        <div class="ml-6">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="menu_input_nilai" value="1"
                                    <?= $data['menu_input_nilai_enabled'] ? 'checked' : ''; ?> class="sr-only peer">
                                <div
                                    class="w-14 h-7 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-primary-600">
                                </div>
                                <span class="ml-3 text-sm font-medium text-secondary-700 whitespace-nowrap">
                                    <?= $data['menu_input_nilai_enabled'] ? 'Aktif' : 'Nonaktif'; ?>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Menu Pembayaran -->
                <div class="border border-secondary-200 rounded-lg p-6 hover:border-primary-300 transition-colors">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center mb-2">
                                <i data-lucide="credit-card" class="w-5 h-5 text-success-600 mr-2"></i>
                                <h3 class="text-lg font-semibold text-secondary-800">Menu Pembayaran</h3>
                            </div>
                            <p class="text-secondary-600 text-sm mb-3">
                                Termasuk: Riwayat Pembayaran, Tagihan, Laporan Keuangan (khusus Wali Kelas)
                            </p>
                            <div class="bg-secondary-50 rounded px-3 py-2 text-xs text-secondary-600">
                                <p><strong>Role yang terpengaruh:</strong> Wali Kelas</p>
                                <p class="mt-1"><strong>Menu yang di-hide:</strong> Menu "Pembayaran" di sidebar wali
                                    kelas</p>
                            </div>
                        </div>
                        <div class="ml-6">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="menu_pembayaran" value="1"
                                    <?= $data['menu_pembayaran_enabled'] ? 'checked' : ''; ?> class="sr-only peer">
                                <div
                                    class="w-14 h-7 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-primary-600">
                                </div>
                                <span class="ml-3 text-sm font-medium text-secondary-700 whitespace-nowrap">
                                    <?= $data['menu_pembayaran_enabled'] ? 'Aktif' : 'Nonaktif'; ?>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Pengaturan Notifikasi WhatsApp -->
                <div class="border-2 border-green-300 rounded-lg p-6 bg-gradient-to-br from-green-50 to-emerald-50">
                    <div class="flex items-center mb-4">
                        <i data-lucide="message-circle" class="w-6 h-6 text-green-600 mr-2"></i>
                        <h3 class="text-lg font-semibold text-secondary-800">Pengaturan Notifikasi WhatsApp Otomatis
                        </h3>
                    </div>

                    <div class="space-y-4">
                        <!-- Notifikasi Absensi -->
                        <div class="flex items-center justify-between bg-white p-4 rounded-lg border border-green-100">
                            <div>
                                <h4 class="font-medium text-secondary-800">Notifikasi Absensi</h4>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="wa_notif_absensi_enabled" value="1"
                                    <?= $data['wa_notif_absensi_enabled'] ? 'checked' : ''; ?> class="sr-only peer">
                                <div
                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600">
                                </div>
                                <span class="ml-3 text-sm font-medium text-secondary-700">
                                    <?= $data['wa_notif_absensi_enabled'] ? 'On' : 'Off'; ?>
                                </span>
                            </label>
                        </div>

                        <!-- Notifikasi Pembayaran -->
                        <div class="flex items-center justify-between bg-white p-4 rounded-lg border border-green-100">
                            <div>
                                <h4 class="font-medium text-secondary-800">Notifikasi Pembayaran</h4>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="wa_notif_pembayaran_enabled" value="1"
                                    <?= $data['wa_notif_pembayaran_enabled'] ? 'checked' : ''; ?> class="sr-only peer">
                                <div
                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600">
                                </div>
                                <span class="ml-3 text-sm font-medium text-secondary-700">
                                    <?= $data['wa_notif_pembayaran_enabled'] ? 'On' : 'Off'; ?>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Google OAuth Configuration -->
                <div class="border-2 border-blue-300 rounded-lg p-6 bg-gradient-to-br from-blue-50 to-indigo-50">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <div class="flex items-center mb-2">
                                <svg class="w-6 h-6 mr-2" viewBox="0 0 24 24">
                                    <path fill="#4285F4"
                                        d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                                    <path fill="#34A853"
                                        d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                                    <path fill="#FBBC05"
                                        d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                                    <path fill="#EA4335"
                                        d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                                </svg>
                                <h3 class="text-lg font-semibold text-secondary-800">Google OAuth 2.0 (Single Sign-On)
                                </h3>
                            </div>
                            <p class="text-secondary-600 text-sm mb-3">
                                Aktifkan login dengan akun Google Workspace untuk guru dan siswa
                            </p>
                        </div>
                        <div class="ml-6">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="google_oauth_enabled" value="1"
                                    <?= $data['google_oauth_enabled'] ? 'checked' : ''; ?> class="sr-only peer">
                                <div
                                    class="w-14 h-7 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-blue-600">
                                </div>
                                <span class="ml-3 text-sm font-medium text-secondary-700 whitespace-nowrap">
                                    <?= $data['google_oauth_enabled'] ? 'Aktif' : 'Nonaktif'; ?>
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="space-y-4 mt-6">
                        <!-- Client ID -->
                        <div>
                            <label for="google_client_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i data-lucide="key" class="w-4 h-4 inline mr-1"></i>
                                Google Client ID
                            </label>
                            <input type="text" id="google_client_id" name="google_client_id"
                                value="<?= htmlspecialchars($data['google_client_id'], ENT_QUOTES, 'UTF-8'); ?>"
                                placeholder="123456789-abcdefg.apps.googleusercontent.com"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm">
                            <p class="text-xs text-gray-500 mt-1">Dari Google Cloud Console → Credentials → OAuth 2.0
                                Client ID</p>
                        </div>

                        <!-- Client Secret -->
                        <div>
                            <label for="google_client_secret" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i data-lucide="shield" class="w-4 h-4 inline mr-1"></i>
                                Google Client Secret
                            </label>
                            <input type="password" id="google_client_secret" name="google_client_secret"
                                value="<?= htmlspecialchars($data['google_client_secret'], ENT_QUOTES, 'UTF-8'); ?>"
                                placeholder="GOCSPX-xxxxxxxxxxxxxxxxxxxxx"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm">
                            <p class="text-xs text-gray-500 mt-1">Rahasia! Jangan bagikan ke siapapun</p>
                        </div>

                        <!-- Allowed Domain -->
                        <div>
                            <label for="google_allowed_domain" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i data-lucide="globe" class="w-4 h-4 inline mr-1"></i>
                                Domain yang Diizinkan
                            </label>
                            <input type="text" id="google_allowed_domain" name="google_allowed_domain"
                                value="<?= htmlspecialchars($data['google_allowed_domain'], ENT_QUOTES, 'UTF-8'); ?>"
                                placeholder="sabilillah.id"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <p class="text-xs text-gray-500 mt-1">Hanya email dengan domain ini yang bisa login (contoh:
                                @sabilillah.id)</p>
                        </div>

                        <!-- Setup Guide -->
                        <div class="bg-white rounded-lg p-4 border border-blue-200">
                            <p class="text-sm font-semibold text-gray-800 mb-2">
                                <i data-lucide="book-open" class="w-4 h-4 inline mr-1"></i>
                                Panduan Setup:
                            </p>
                            <ol class="text-xs text-gray-600 space-y-1 list-decimal list-inside">
                                <li>Buka <a href="https://console.cloud.google.com" target="_blank"
                                        class="text-blue-600 hover:underline">Google Cloud Console</a></li>
                                <li>Buat project baru atau pilih existing project</li>
                                <li>Enable <strong>Admin SDK API</strong> atau <strong>People API</strong></li>
                                <li>Buat <strong>OAuth 2.0 Client ID</strong> (Web application)</li>
                                <li>Authorized redirect URIs: <code
                                        class="bg-gray-100 px-1 rounded"><?= BASEURL; ?>/auth/googleCallback</code></li>
                                <li>Copy Client ID dan Client Secret, paste ke form ini</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <!-- Google Drive Storage -->
                <div class="border-2 border-green-300 rounded-lg p-6 bg-gradient-to-br from-green-50 to-emerald-50">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <div class="flex items-center mb-2">
                                <svg class="w-6 h-6 mr-2" viewBox="0 0 87.3 78" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="m6.6 66.85 3.85 6.65c.8 1.4 1.95 2.5 3.3 3.3l13.75-23.8h-27.5c0 1.55.4 3.1 1.2 4.5z"
                                        fill="#0066da" />
                                    <path
                                        d="m43.65 25-13.75-23.8c-1.35.8-2.5 1.9-3.3 3.3l-25.4 44a9.06 9.06 0 0 0 -1.2 4.5h27.5z"
                                        fill="#00ac47" />
                                    <path
                                        d="m73.55 76.8c1.35-.8 2.5-1.9 3.3-3.3l1.6-2.75 7.65-13.25c.8-1.4 1.2-2.95 1.2-4.5h-27.502l5.852 11.5z"
                                        fill="#ea4335" />
                                    <path
                                        d="m43.65 25 13.75-23.8c-1.35-.8-2.9-1.2-4.5-1.2h-18.5c-1.6 0-3.15.45-4.5 1.2z"
                                        fill="#00832d" />
                                    <path
                                        d="m59.8 53h-32.3l-13.75 23.8c1.35.8 2.9 1.2 4.5 1.2h50.8c1.6 0 3.15-.45 4.5-1.2z"
                                        fill="#2684fc" />
                                    <path
                                        d="m73.4 26.5-12.7-22c-.8-1.4-1.95-2.5-3.3-3.3l-13.75 23.8 16.15 28h27.45c0-1.55-.4-3.1-1.2-4.5z"
                                        fill="#ffba00" />
                                </svg>
                                <h3 class="text-lg font-semibold text-secondary-800">Google Drive Storage</h3>
                            </div>
                            <p class="text-secondary-600 text-sm mb-3">
                                Simpan dokumen siswa dan PSB ke Google Drive untuk storage tak terbatas
                            </p>
                        </div>
                        <div class="ml-6">
                            <?php if ($data['google_drive_connected']): ?>
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    <i data-lucide="check-circle" class="w-4 h-4 mr-1"></i> Terhubung
                                </span>
                            <?php else: ?>
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-600">
                                    <i data-lucide="x-circle" class="w-4 h-4 mr-1"></i> Belum Terhubung
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="space-y-4 mt-6">
                        <?php if ($data['google_drive_connected']): ?>
                            <!-- Connected: Show account and folder info -->
                            <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                                <div class="flex items-start gap-3">
                                    <div class="bg-green-100 p-2 rounded-lg">
                                        <i data-lucide="user-check" class="w-5 h-5 text-green-600"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-semibold text-green-800">Akun Terhubung</p>
                                        <?php if (!empty($data['google_drive_email'])): ?>
                                            <p class="text-sm text-green-700 font-medium">
                                                <?= htmlspecialchars($data['google_drive_email']); ?>
                                            </p>
                                        <?php else: ?>
                                            <p class="text-xs text-green-600">Akun Google tersambung</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Folder Info -->
                            <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                                <div class="flex items-start gap-3">
                                    <div class="bg-green-100 p-2 rounded-lg">
                                        <i data-lucide="folder-check" class="w-5 h-5 text-green-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-green-800">Folder Dokumen Tersedia</p>
                                        <p class="text-xs text-green-600">Dokumen akan disimpan ke folder yang dibuat
                                            otomatis di Google Drive</p>
                                        <?php if (!empty($data['google_drive_folder_id'])): ?>
                                            <a href="https://drive.google.com/drive/folders/<?= htmlspecialchars($data['google_drive_folder_id']); ?>"
                                                target="_blank"
                                                class="text-xs text-green-700 hover:underline inline-flex items-center mt-1">
                                                <i data-lucide="external-link" class="w-3 h-3 mr-1"></i>
                                                Buka folder di Google Drive
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Disconnect Button -->
                            <div class="flex items-center gap-4">
                                <a href="<?= BASEURL; ?>/admin/disconnectGoogleDrive"
                                    onclick="return confirm('Yakin ingin memutus koneksi Google Drive?')"
                                    class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors">
                                    <i data-lucide="unlink" class="w-4 h-4 mr-2"></i>
                                    Putus Koneksi
                                </a>
                                <span class="text-sm text-green-600">
                                    <i data-lucide="check" class="w-4 h-4 inline"></i>
                                    Drive siap digunakan untuk upload dokumen
                                </span>
                            </div>
                        <?php else: ?>
                            <!-- Not Connected: Show connect button -->
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                <div class="flex items-center gap-3">
                                    <div class="bg-gray-100 p-2 rounded-lg">
                                        <i data-lucide="folder-plus" class="w-5 h-5 text-gray-500"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800">Folder Otomatis Dibuat</p>
                                        <p class="text-xs text-gray-600">Saat Anda menghubungkan Drive, folder untuk dokumen
                                            siswa akan dibuat otomatis</p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <a href="<?= BASEURL; ?>/admin/connectGoogleDrive"
                                    class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">
                                    <i data-lucide="link" class="w-4 h-4 mr-2"></i>
                                    Hubungkan ke Google Drive
                                </a>
                                <span class="text-sm text-gray-500">
                                    Login dengan akun Google yang akan menyimpan file
                                </span>
                            </div>

                            <!-- Info Box -->
                            <div class="bg-white rounded-lg p-4 border border-green-200">
                                <p class="text-sm font-semibold text-gray-800 mb-2">
                                    <i data-lucide="info" class="w-4 h-4 inline mr-1"></i>
                                    Cara Kerja:
                                </p>
                                <ol class="text-xs text-gray-600 space-y-1 list-decimal list-inside">
                                    <li>Klik "Hubungkan ke Google Drive"</li>
                                    <li>Login dengan akun Google Anda</li>
                                    <li>Folder akan dibuat otomatis di Drive Anda</li>
                                    <li>Semua dokumen siswa akan tersimpan di folder tersebut</li>
                                </ol>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-8 pt-6 border-t border-secondary-200 flex justify-between items-center">
                <a href="<?= BASEURL; ?>/admin/dashboard"
                    class="text-secondary-600 hover:text-secondary-800 font-medium flex items-center">
                    <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                    Kembali ke Dashboard
                </a>
                <button type="submit" class="btn-primary">
                    <i data-lucide="save" class="w-4 h-4 mr-2"></i>
                    Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
</main>

<script>
    // Update toggle label saat diubah
    document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            const label = this.parentElement.querySelector('span');
            label.textContent = this.checked ? 'Aktif' : 'Nonaktif';
        });
    });
</script>