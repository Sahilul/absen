<?php
$lembaga_list = $data['lembaga_list'] ?? [];
?>
<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-4 sm:p-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Generate Link Tamu</h2>
            <p class="text-gray-600 mt-1">Buat link undangan buku tamu</p>
        </div>
        <a href="<?= BASEURL ?>/bukuTamu"
            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl text-gray-700 flex items-center gap-2">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali
        </a>
    </div>

    <?php Flasher::flash(); ?>

    <div class="bg-white rounded-xl shadow-sm border p-6 max-w-xl">
        <form action="<?= BASEURL ?>/bukuTamu/prosesGenerateLink" method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Lembaga Tujuan *</label>
                <select name="id_lembaga" required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">-- Pilih Lembaga --</option>
                    <?php foreach ($lembaga_list as $lb): ?>
                        <option value="<?= $lb['id'] ?>"><?= htmlspecialchars($lb['nama_lembaga']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">No. WhatsApp Tamu *</label>
                <input type="text" name="no_wa_tamu" required placeholder="08123456789"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                <p class="text-xs text-gray-500 mt-1">Link akan dikirim ke nomor ini</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Tamu (opsional)</label>
                <input type="text" name="nama_tamu" placeholder="Nama tamu jika sudah diketahui"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Keperluan (opsional)</label>
                <textarea name="keperluan_prefill" rows="2" placeholder="Keperluan kunjungan jika sudah diketahui"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Berlaku Selama *</label>
                <select name="expiry_hours" required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="6">6 Jam</option>
                    <option value="12">12 Jam</option>
                    <option value="24" selected>24 Jam (1 Hari)</option>
                    <option value="72">3 Hari</option>
                    <option value="168">7 Hari</option>
                </select>
            </div>

            <div class="pt-4 border-t">
                <button type="submit"
                    class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-medium flex items-center justify-center gap-2">
                    <i data-lucide="send" class="w-5 h-5"></i>
                    Generate & Kirim via WA
                </button>
            </div>
        </form>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', () => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
</script>