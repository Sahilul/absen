<?php
$r2ok = $data['r2_configured'] ?? false;
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Storage</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@0.460.0/dist/umd/lucide.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>

<body class="bg-gray-50">
    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Pengaturan Storage</h2>
                <p class="text-gray-600 mt-1">Konfigurasi Cloudflare R2 untuk penyimpanan foto siswa</p>
            </div>
        </div>

        <!-- Status Card -->
        <div class="max-w-3xl mx-auto mb-6">
            <div class="rounded-xl border p-4 flex items-center gap-3 <?= $r2ok ? 'bg-emerald-50 border-emerald-200' : 'bg-amber-50 border-amber-200'; ?>">
                <i data-lucide="<?= $r2ok ? 'check-circle' : 'alert-triangle'; ?>"
                    class="w-6 h-6 <?= $r2ok ? 'text-emerald-600' : 'text-amber-600'; ?>"></i>
                <div>
                    <p class="font-medium <?= $r2ok ? 'text-emerald-800' : 'text-amber-800'; ?>">
                        <?= $r2ok ? 'R2 Storage Terkonfigurasi' : 'R2 Storage Belum Dikonfigurasi'; ?>
                    </p>
                    <p class="text-sm <?= $r2ok ? 'text-emerald-600' : 'text-amber-600'; ?>">
                        <?= $r2ok ? 'Foto siswa akan disimpan ke Cloudflare R2' : 'Isi kredensial di bawah untuk mengaktifkan upload foto'; ?>
                    </p>
                </div>
                <?php if ($r2ok): ?>
                    <button type="button" onclick="testConnection()"
                        class="ml-auto px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700 flex items-center gap-1">
                        <i data-lucide="wifi" class="w-4 h-4"></i> Test Koneksi
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Setup Guide -->
        <?php if (!$r2ok): ?>
        <div class="max-w-3xl mx-auto mb-6">
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-5">
                <h3 class="font-bold text-blue-800 flex items-center gap-2 mb-3">
                    <i data-lucide="book-open" class="w-5 h-5"></i> Cara Setup Cloudflare R2
                </h3>
                <ol class="text-sm text-blue-700 space-y-2 list-decimal list-inside">
                    <li>Buat akun di <a href="https://dash.cloudflare.com/sign-up" target="_blank" class="underline font-medium">dash.cloudflare.com</a> (gratis)</li>
                    <li>Buka menu <strong>R2 Object Storage</strong> di sidebar</li>
                    <li>Klik <strong>Create bucket</strong>, beri nama <code class="bg-blue-100 px-1 rounded">sabilillah</code></li>
                    <li>Di halaman bucket, tab <strong>Settings</strong> → scroll ke <strong>Public access</strong> → aktifkan <strong>R2.dev subdomain</strong></li>
                    <li>Salin URL publik yang muncul (format: <code class="bg-blue-100 px-1 rounded">https://pub-xxx.r2.dev</code>)</li>
                    <li>Kembali ke halaman R2 utama → klik <strong>Manage R2 API Tokens</strong></li>
                    <li>Klik <strong>Create API token</strong> → pilih <strong>Object Read & Write</strong> → scope ke bucket <code class="bg-blue-100 px-1 rounded">sabilillah</code></li>
                    <li>Salin <strong>Access Key ID</strong> dan <strong>Secret Access Key</strong></li>
                    <li><strong>Account ID</strong> ada di URL dashboard: <code class="bg-blue-100 px-1 rounded">dash.cloudflare.com/<strong>ACCOUNT_ID</strong>/r2</code></li>
                    <li>Isi semua field di bawah, lalu klik Simpan</li>
                </ol>
            </div>
        </div>
        <?php endif; ?>

        <!-- Form -->
        <div class="bg-white rounded-xl shadow-sm border overflow-hidden max-w-3xl mx-auto">
            <div class="p-6">
                <form action="<?= BASEURL; ?>/admin/simpanPengaturanStorage" method="POST">
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Account ID</label>
                            <input type="text" name="r2_account_id"
                                value="<?= htmlspecialchars($data['r2_account_id']); ?>"
                                placeholder="32 karakter hex dari URL dashboard"
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <p class="text-xs text-gray-400 mt-1">Dari URL: dash.cloudflare.com/<strong>ACCOUNT_ID</strong>/r2</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Access Key ID</label>
                            <input type="text" name="r2_access_key_id"
                                value="<?= htmlspecialchars($data['r2_access_key_id']); ?>"
                                placeholder="Access Key ID dari API Token"
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Secret Access Key</label>
                            <input type="password" name="r2_secret_access_key"
                                value="<?= htmlspecialchars($data['r2_secret_access_key']); ?>"
                                placeholder="Secret Access Key dari API Token"
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <p class="text-xs text-gray-400 mt-1">Disimpan terenkripsi di database</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Bucket</label>
                                <input type="text" name="r2_bucket"
                                    value="<?= htmlspecialchars($data['r2_bucket']); ?>"
                                    placeholder="sabilillah"
                                    class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Public URL</label>
                                <input type="url" name="r2_public_url"
                                    value="<?= htmlspecialchars($data['r2_public_url']); ?>"
                                    placeholder="https://pub-xxx.r2.dev"
                                    class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                                <p class="text-xs text-gray-400 mt-1">URL publik dari R2.dev subdomain</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <div class="flex justify-end">
                            <button type="submit"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 px-6 rounded-lg flex items-center gap-2">
                                <i data-lucide="save" class="w-4 h-4"></i> Simpan Pengaturan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Test Result -->
        <div id="test-result" class="max-w-3xl mx-auto mt-4 hidden">
            <div id="test-result-inner" class="rounded-xl border p-4 flex items-center gap-3"></div>
        </div>
    </main>

    <script>
        async function testConnection() {
            const resultDiv = document.getElementById('test-result');
            const inner = document.getElementById('test-result-inner');
            resultDiv.classList.remove('hidden');
            inner.className = 'rounded-xl border p-4 flex items-center gap-3 bg-gray-50 border-gray-200';
            inner.innerHTML = '<div class="animate-spin"><svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg></div><span class="text-gray-600">Menguji koneksi...</span>';

            try {
                const res = await fetch('<?= BASEURL; ?>/admin/testR2Connection');
                const data = await res.json();
                if (data.success) {
                    inner.className = 'rounded-xl border p-4 flex items-center gap-3 bg-emerald-50 border-emerald-200';
                    inner.innerHTML = '<i data-lucide="check-circle" class="w-5 h-5 text-emerald-600"></i><span class="text-emerald-700 font-medium">' + data.message + '</span>';
                } else {
                    inner.className = 'rounded-xl border p-4 flex items-center gap-3 bg-red-50 border-red-200';
                    inner.innerHTML = '<i data-lucide="x-circle" class="w-5 h-5 text-red-600"></i><span class="text-red-700 font-medium">' + data.message + '</span>';
                }
                lucide.createIcons();
            } catch (e) {
                inner.className = 'rounded-xl border p-4 flex items-center gap-3 bg-red-50 border-red-200';
                inner.innerHTML = '<span class="text-red-700">Error: ' + e.message + '</span>';
            }
        }

        document.addEventListener('DOMContentLoaded', () => lucide.createIcons());
    </script>
</body>
</html>
