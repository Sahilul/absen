<?php
/**
 * Pengaturan Mobile App
 * File: app/views/admin/pengaturan_mobile_app.php
 */
?>
<main class="flex-1 overflow-x-hidden overflow-y-auto bg-secondary-50 p-4 md:p-6">
    <!-- Header -->
    <div class="mb-4">
        <div class="bg-white shadow-sm rounded-xl p-4 md:p-5">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                    <h4 class="text-lg md:text-xl font-bold text-slate-800 mb-1">
                        <i data-lucide="smartphone" class="w-5 h-5 inline-block mr-2"></i>
                        Pengaturan Mobile App
                    </h4>
                    <p class="text-slate-500 text-sm">
                        Konfigurasi Firebase Cloud Messaging dan Google Sign-In untuk aplikasi mobile
                    </p>
                </div>
            </div>
        </div>
    </div>

    <?php Flasher::flash(); ?>

    <!-- Firebase Setup Guide -->
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-4">
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0">
                <i data-lucide="info" class="w-5 h-5 text-blue-500"></i>
            </div>
            <div>
                <h5 class="text-sm font-semibold text-blue-800 mb-2">Cara Setup Firebase</h5>
                <ol class="text-sm text-blue-700 space-y-1 list-decimal list-inside">
                    <li>Buka <a href="https://console.firebase.google.com/" target="_blank"
                            class="underline font-medium">Firebase Console</a></li>
                    <li>Pilih project → Project Settings → <strong>Service accounts</strong></li>
                    <li>Klik <strong>"Generate new private key"</strong></li>
                    <li>Copy-paste isi file JSON ke form "Import dari JSON" di bawah</li>
                </ol>
            </div>
        </div>
    </div>

    <!-- Import JSON Section -->
    <div class="bg-white shadow-sm rounded-xl p-5 md:p-6 mb-4">
        <h5 class="text-sm font-bold text-slate-700 mb-4">
            <i data-lucide="file-json" class="w-4 h-4 inline-block mr-1"></i>
            Import dari Service Account JSON
        </h5>
        <div class="mb-3">
            <textarea id="jsonImport" rows="4"
                class="w-full border border-slate-300 rounded-lg px-4 py-3 text-sm font-mono focus:ring-2 focus:ring-primary-200 focus:border-primary-400 transition-all"
                placeholder="Paste isi file service account JSON disini..."></textarea>
        </div>
        <button type="button" onclick="parseJson()"
            class="inline-flex items-center px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-all">
            <i data-lucide="wand-2" class="w-4 h-4 mr-2"></i>
            Import & Parse
        </button>
    </div>

    <!-- Settings Form -->
    <div class="bg-white shadow-sm rounded-xl p-5 md:p-6">
        <form action="<?= BASEURL ?>/admin/simpanPengaturanMobileApp" method="POST">

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Left Column: Firebase -->
                <div>
                    <h5 class="text-sm font-bold text-slate-700 mb-4 pb-2 border-b border-slate-200">
                        <i data-lucide="flame" class="w-4 h-4 inline-block mr-1 text-orange-500"></i>
                        Firebase Configuration
                    </h5>

                    <div class="space-y-4">
                        <div>
                            <label for="firebase_project_id" class="block text-sm font-semibold text-slate-700 mb-2">
                                Project ID <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="firebase_project_id" id="firebase_project_id"
                                value="<?= htmlspecialchars($data['firebase_project_id']) ?>"
                                class="w-full border border-slate-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-primary-200 focus:border-primary-400 transition-all"
                                placeholder="contoh: sabilillah-mobile">
                            <p class="text-xs text-slate-500 mt-1">Dari firebase console atau google-services.json</p>
                        </div>

                        <div>
                            <label for="firebase_client_email" class="block text-sm font-semibold text-slate-700 mb-2">
                                Client Email <span class="text-red-500">*</span>
                            </label>
                            <input type="email" name="firebase_client_email" id="firebase_client_email"
                                value="<?= htmlspecialchars($data['firebase_client_email']) ?>"
                                class="w-full border border-slate-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-primary-200 focus:border-primary-400 transition-all"
                                placeholder="firebase-adminsdk-xxxxx@project.iam.gserviceaccount.com">
                        </div>

                        <div>
                            <label for="firebase_private_key" class="block text-sm font-semibold text-slate-700 mb-2">
                                Private Key <span class="text-red-500">*</span>
                            </label>
                            <textarea name="firebase_private_key" id="firebase_private_key" rows="5"
                                class="w-full border border-slate-300 rounded-lg px-4 py-3 text-sm font-mono focus:ring-2 focus:ring-primary-200 focus:border-primary-400 transition-all"
                                placeholder="-----BEGIN PRIVATE KEY-----&#10;xxxxxxx&#10;-----END PRIVATE KEY-----"><?= htmlspecialchars($data['firebase_private_key']) ?></textarea>
                            <p class="text-xs text-slate-500 mt-1">Dari service account JSON (field: private_key)</p>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Google Sign-In & Settings -->
                <div>
                    <h5 class="text-sm font-bold text-slate-700 mb-4 pb-2 border-b border-slate-200">
                        <i data-lucide="key-round" class="w-4 h-4 inline-block mr-1 text-green-500"></i>
                        Google Sign-In (Opsional)
                    </h5>

                    <div class="space-y-4">
                        <div>
                            <label for="google_client_id" class="block text-sm font-semibold text-slate-700 mb-2">
                                Google Client ID
                            </label>
                            <input type="text" name="google_client_id"
                                value="<?= htmlspecialchars($data['google_client_id']) ?>"
                                class="w-full border border-slate-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-primary-200 focus:border-primary-400 transition-all"
                                placeholder="xxxxxx.apps.googleusercontent.com">
                            <p class="text-xs text-slate-500 mt-1">Untuk fitur login dengan Google</p>
                        </div>
                    </div>

                    <!-- Toggle -->
                    <div class="mt-6 p-4 bg-slate-50 rounded-lg border border-slate-200">
                        <h5 class="text-sm font-bold text-slate-700 mb-3">
                            <i data-lucide="settings" class="w-4 h-4 inline-block mr-1"></i>
                            Pengaturan Umum
                        </h5>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="mobile_app_enabled" <?= $data['mobile_app_enabled'] == '1' ? 'checked' : '' ?>
                                class="w-5 h-5 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                            <div>
                                <span class="text-sm font-medium text-slate-700">Aktifkan Mobile App API</span>
                                <p class="text-xs text-slate-500">Mematikan ini akan menonaktifkan semua endpoint API
                                    mobile</p>
                            </div>
                        </label>
                    </div>

                    <!-- Status -->
                    <div class="mt-4 p-4 bg-gradient-to-r from-slate-50 to-blue-50 rounded-lg border border-slate-200">
                        <h5 class="text-sm font-bold text-slate-700 mb-3">
                            <i data-lucide="activity" class="w-4 h-4 inline-block mr-1"></i>
                            Status Konfigurasi
                        </h5>
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <?php if (!empty($data['firebase_project_id']) && !empty($data['firebase_client_email']) && !empty($data['firebase_private_key'])): ?>
                                    <i data-lucide="check-circle" class="w-4 h-4 text-green-500"></i>
                                    <span class="text-sm text-green-700">Firebase: Terkonfigurasi</span>
                                <?php else: ?>
                                    <i data-lucide="x-circle" class="w-4 h-4 text-red-500"></i>
                                    <span class="text-sm text-red-700">Firebase: Belum dikonfigurasi</span>
                                <?php endif; ?>
                            </div>
                            <div class="flex items-center gap-2">
                                <?php if (!empty($data['google_client_id'])): ?>
                                    <i data-lucide="check-circle" class="w-4 h-4 text-green-500"></i>
                                    <span class="text-sm text-green-700">Google Sign-In: Terkonfigurasi</span>
                                <?php else: ?>
                                    <i data-lucide="minus-circle" class="w-4 h-4 text-amber-500"></i>
                                    <span class="text-sm text-amber-700">Google Sign-In: Tidak dikonfigurasi
                                        (opsional)</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-slate-200">
                <a href="<?= BASEURL ?>/admin/dashboard"
                    class="inline-flex items-center px-4 py-2.5 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-all">
                    <i data-lucide="x" class="w-4 h-4 mr-2"></i>
                    Batal
                </a>
                <button type="submit"
                    class="inline-flex items-center px-5 py-2.5 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-all shadow-sm">
                    <i data-lucide="save" class="w-4 h-4 mr-2"></i>
                    Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
</main>

<script>
    function parseJson() {
        const jsonText = document.getElementById('jsonImport').value.trim();

        if (!jsonText) {
            alert('Masukkan JSON terlebih dahulu');
            return;
        }

        try {
            const data = JSON.parse(jsonText);

            if (data.project_id) {
                document.getElementById('firebase_project_id').value = data.project_id;
            }
            if (data.client_email) {
                document.getElementById('firebase_client_email').value = data.client_email;
            }
            if (data.private_key) {
                document.getElementById('firebase_private_key').value = data.private_key;
            }

            // Clear import field
            document.getElementById('jsonImport').value = '';

            // Show success notification
            alert('Import berhasil! Silakan cek field yang sudah terisi dan klik Simpan.');
        } catch (e) {
            alert('JSON tidak valid: ' + e.message);
        }
    }
</script>