<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Pengaturan AI Chat</h2>
            <p class="text-gray-600 mt-1">Konfigurasi asisten AI "Pak Admin" untuk guru dan staf</p>
        </div>
    </div>

    <!-- Status Card -->
    <?php $aiEnabled = ($data['ai_chat_enabled'] ?? '0') === '1'; ?>
    <div class="max-w-3xl mx-auto mb-6">
        <div class="rounded-xl border p-4 flex items-center gap-3 <?= $aiEnabled ? 'bg-emerald-50 border-emerald-200' : 'bg-amber-50 border-amber-200'; ?>">
            <i data-lucide="<?= $aiEnabled ? 'check-circle' : 'alert-triangle'; ?>"
                class="w-6 h-6 <?= $aiEnabled ? 'text-emerald-600' : 'text-amber-600'; ?>"></i>
            <div>
                <p class="font-medium <?= $aiEnabled ? 'text-emerald-800' : 'text-amber-800'; ?>">
                    <?= $aiEnabled ? 'AI Chat Aktif' : 'AI Chat Tidak Aktif'; ?>
                </p>
                <p class="text-sm <?= $aiEnabled ? 'text-emerald-600' : 'text-amber-600'; ?>">
                    <?= $aiEnabled ? 'Widget "Pak Admin" muncul di semua halaman (kecuali siswa)' : 'Aktifkan untuk menampilkan widget AI Chat'; ?>
                </p>
            </div>
            <?php if ($aiEnabled && !empty($data['ai_chat_api_key'])): ?>
                <button type="button" onclick="testAiConnection()"
                    class="ml-auto px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700 flex items-center gap-1">
                    <i data-lucide="wifi" class="w-4 h-4"></i> Test Koneksi
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Info Guide -->
    <div class="max-w-3xl mx-auto mb-6">
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-5">
            <h3 class="font-bold text-blue-800 flex items-center gap-2 mb-3">
                <i data-lucide="book-open" class="w-5 h-5"></i> Tentang AI Chat "Pak Admin"
            </h3>
            <ul class="text-sm text-blue-700 space-y-1.5 list-disc list-inside">
                <li>Widget chat muncul di <strong>semua halaman</strong> untuk guru, admin, wali kelas, dan kepala madrasah</li>
                <li>Siswa <strong>tidak dapat</strong> mengakses fitur ini</li>
                <li>Mendukung endpoint <strong>OpenAI-compatible</strong> (OpenAI, OpenRouter, Groq, Ollama, dll)</li>
                <li>Setiap user memiliki riwayat percakapan sendiri (terisolasi)</li>
                <li>Pak Admin mengenali identitas user (nama, role, jabatan) dari session</li>
            </ul>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden max-w-3xl mx-auto">
        <div class="p-6">
            <form action="<?= BASEURL; ?>/admin/simpanPengaturanAiChat" method="POST">
                <div class="space-y-6">

                    <!-- Toggle Enable -->
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                        <div>
                            <label class="text-sm font-semibold text-gray-800">Aktifkan AI Chat</label>
                            <p class="text-xs text-gray-500 mt-0.5">Tampilkan widget "Pak Admin" di semua halaman</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="ai_chat_enabled" value="1"
                                <?= $aiEnabled ? 'checked' : ''; ?>
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>

                    <!-- Nama & Label -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama AI</label>
                            <input type="text" name="ai_chat_name"
                                value="<?= htmlspecialchars($data['ai_chat_name'] ?? 'Pak Admin'); ?>"
                                placeholder="Pak Admin"
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <p class="text-xs text-gray-400 mt-1">Nama yang ditampilkan di header widget</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Label Tombol</label>
                            <input type="text" name="ai_chat_button_label"
                                value="<?= htmlspecialchars($data['ai_chat_button_label'] ?? 'Tanya Pak Admin'); ?>"
                                placeholder="Tanya Pak Admin"
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <p class="text-xs text-gray-400 mt-1">Teks pada tombol floating di pojok kanan bawah</p>
                        </div>
                    </div>

                    <!-- Divider: API Configuration -->
                    <div class="border-t pt-6">
                        <h3 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <i data-lucide="server" class="w-5 h-5 text-gray-500"></i>
                            Konfigurasi API
                        </h3>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Base URL</label>
                                <input type="url" name="ai_chat_base_url"
                                    value="<?= htmlspecialchars($data['ai_chat_base_url'] ?? 'https://api.openai.com/v1'); ?>"
                                    placeholder="https://api.openai.com/v1"
                                    class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <div class="mt-2 text-xs text-gray-400 space-y-0.5">
                                    <p><strong>Contoh Base URL:</strong></p>
                                    <p>• OpenAI: <code class="bg-gray-100 px-1 rounded">https://api.openai.com/v1</code></p>
                                    <p>• OpenRouter: <code class="bg-gray-100 px-1 rounded">https://openrouter.ai/api/v1</code></p>
                                    <p>• Groq: <code class="bg-gray-100 px-1 rounded">https://api.groq.com/openai/v1</code></p>
                                    <p>• Ollama: <code class="bg-gray-100 px-1 rounded">http://localhost:11434/v1</code></p>
                                    <p>• LM Studio: <code class="bg-gray-100 px-1 rounded">http://localhost:1234/v1</code></p>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">API Key</label>
                                <div class="relative">
                                    <input type="password" name="ai_chat_api_key" id="ai_chat_api_key"
                                        value="<?= htmlspecialchars($data['ai_chat_api_key'] ?? ''); ?>"
                                        placeholder="sk-..."
                                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 pr-20">
                                    <button type="button" onclick="toggleApiKey()"
                                        class="absolute right-2 top-1/2 -translate-y-1/2 text-xs text-gray-500 hover:text-gray-700 px-2 py-1 rounded bg-gray-100 hover:bg-gray-200">
                                        <span id="toggle-key-text">Tampilkan</span>
                                    </button>
                                </div>
                                <p class="text-xs text-gray-400 mt-1">Disimpan di database, hanya diakses server-side</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Model</label>
                                <input type="text" name="ai_chat_model"
                                    value="<?= htmlspecialchars($data['ai_chat_model'] ?? 'gpt-4o-mini'); ?>"
                                    placeholder="gpt-4o-mini"
                                    class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <p class="text-xs text-gray-400 mt-1">Contoh: gpt-4o-mini, gpt-4o, llama-3.1-8b-instant, deepseek-chat</p>
                            </div>
                        </div>
                    </div>

                    <!-- Divider: Behavior -->
                    <div class="border-t pt-6">
                        <h3 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <i data-lucide="sliders" class="w-5 h-5 text-gray-500"></i>
                            Perilaku AI
                        </h3>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Temperature: <span id="temp-value"><?= htmlspecialchars($data['ai_chat_temperature'] ?? '0.7'); ?></span>
                                </label>
                                <input type="range" name="ai_chat_temperature"
                                    min="0" max="2" step="0.1"
                                    value="<?= htmlspecialchars($data['ai_chat_temperature'] ?? '0.7'); ?>"
                                    oninput="document.getElementById('temp-value').textContent = this.value"
                                    class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600">
                                <div class="flex justify-between text-xs text-gray-400 mt-1">
                                    <span>0.0 (Fokus)</span>
                                    <span>1.0 (Seimbang)</span>
                                    <span>2.0 (Kreatif)</span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Max History (pesan)</label>
                                    <input type="number" name="ai_chat_max_history"
                                        value="<?= htmlspecialchars($data['ai_chat_max_history'] ?? '100'); ?>"
                                        min="1" max="500"
                                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <p class="text-xs text-gray-400 mt-1">Jumlah pesan terakhir yang dikirim ke AI</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Max Context (karakter)</label>
                                    <input type="number" name="ai_chat_max_context_chars"
                                        value="<?= htmlspecialchars($data['ai_chat_max_context_chars'] ?? '500000'); ?>"
                                        min="5000" max="2000000"
                                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <p class="text-xs text-gray-400 mt-1">Batas total karakter context. 500K ≈ 125K token</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Batas Harian (pesan/user)</label>
                                    <input type="number" name="ai_chat_daily_limit"
                                        value="<?= htmlspecialchars($data['ai_chat_daily_limit'] ?? '50'); ?>"
                                        min="0" max="1000"
                                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <p class="text-xs text-gray-400 mt-1">0 = unlimited. Reset setiap 00:00 WIB</p>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">System Prompt</label>
                                <textarea name="ai_chat_system_prompt" rows="5"
                                    placeholder="Instruksi untuk AI..."
                                    class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"><?= htmlspecialchars($data['ai_chat_system_prompt'] ?? ''); ?></textarea>
                                <p class="text-xs text-gray-400 mt-1">Instruksi dasar untuk AI. Konteks identitas user (nama, role, jabatan) akan ditambahkan otomatis.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Divider: File Upload -->
                    <div class="border-t pt-6">
                        <h3 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <i data-lucide="paperclip" class="w-5 h-5 text-gray-500"></i>
                            Upload File & Dokumen
                        </h3>

                        <div class="space-y-4">
                            <!-- Toggle File Upload -->
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                                <div>
                                    <label class="text-sm font-semibold text-gray-800">Aktifkan Upload File</label>
                                    <p class="text-xs text-gray-500 mt-0.5">User bisa mengirim gambar, PDF, Word, Excel ke AI</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="ai_chat_file_upload_enabled" value="1"
                                        <?= ($data['ai_chat_file_upload_enabled'] ?? '1') === '1' ? 'checked' : ''; ?>
                                        class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Max Ukuran File (MB)</label>
                                <input type="number" name="ai_chat_max_file_size_mb"
                                    value="<?= htmlspecialchars(round(($data['ai_chat_max_file_size'] ?? '10485760') / 1048576)); ?>"
                                    min="1" max="20"
                                    class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <p class="text-xs text-gray-400 mt-1">Ukuran maksimal file yang bisa diupload (1-20 MB)</p>
                            </div>
                        </div>
                    </div>

                    <!-- Divider: Harga Token (untuk estimasi biaya monitoring) -->
                    <div class="border-t pt-6">
                        <h3 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <i data-lucide="wallet" class="w-5 h-5 text-gray-500"></i>
                            Harga Token (Estimasi Biaya)
                        </h3>
                        <p class="text-xs text-gray-500 mb-4">Opsional. Isi harga per 1.000 token sesuai tarif provider AI Anda untuk menghitung estimasi biaya di halaman Monitoring AI. Kosongkan/0 jika tidak ingin menghitung biaya.</p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Harga Input Token / 1K (Rp)</label>
                                <input type="number" name="ai_chat_price_input_per_1k" step="0.01" min="0"
                                    value="<?= htmlspecialchars($data['ai_chat_price_input_per_1k'] ?? '0'); ?>"
                                    class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <p class="text-xs text-gray-400 mt-1">Contoh: tarif prompt token</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Harga Output Token / 1K (Rp)</label>
                                <input type="number" name="ai_chat_price_output_per_1k" step="0.01" min="0"
                                    value="<?= htmlspecialchars($data['ai_chat_price_output_per_1k'] ?? '0'); ?>"
                                    class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <p class="text-xs text-gray-400 mt-1">Contoh: tarif completion token</p>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="mt-8 pt-6 border-t border-gray-200">
                    <div class="flex justify-end">
                        <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-6 rounded-lg flex items-center gap-2 transition-colors">
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
    function toggleApiKey() {
        const input = document.getElementById('ai_chat_api_key');
        const text = document.getElementById('toggle-key-text');
        if (input.type === 'password') {
            input.type = 'text';
            text.textContent = 'Sembunyikan';
        } else {
            input.type = 'password';
            text.textContent = 'Tampilkan';
        }
    }

    async function testAiConnection() {
        const resultDiv = document.getElementById('test-result');
        const inner = document.getElementById('test-result-inner');
        resultDiv.classList.remove('hidden');
        inner.className = 'rounded-xl border p-4 flex items-center gap-3 bg-gray-50 border-gray-200';
        inner.innerHTML = '<div class="animate-spin"><svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg></div><span class="text-gray-600">Menguji koneksi AI...</span>';

        try {
            const res = await fetch('<?= BASEURL; ?>/admin/testAiConnection');
            const data = await res.json();
            if (data.success) {
                inner.className = 'rounded-xl border p-4 flex items-center gap-3 bg-emerald-50 border-emerald-200';
                inner.innerHTML = '<i data-lucide="check-circle" class="w-5 h-5 text-emerald-600"></i><span class="text-emerald-700 font-medium">' + data.message + '</span>';
            } else {
                inner.className = 'rounded-xl border p-4 flex items-center gap-3 bg-red-50 border-red-200';
                inner.innerHTML = '<i data-lucide="x-circle" class="w-5 h-5 text-red-600"></i><span class="text-red-700 font-medium">' + data.message + '</span>';
            }
            if (typeof lucide !== 'undefined') lucide.createIcons();
        } catch (e) {
            inner.className = 'rounded-xl border p-4 flex items-center gap-3 bg-red-50 border-red-200';
            inner.innerHTML = '<span class="text-red-700">Error: ' + e.message + '</span>';
        }
    }
</script>
