<main class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-50 p-4 md:p-6">
    <!-- Header -->
    <div class="mb-6 bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <h1 class="text-xl md:text-2xl font-bold text-slate-800">Identitas Website</h1>
        <p class="text-slate-500 text-sm mt-1">Kelola informasi umum yang ditampilkan di website sekolah.</p>
    </div>

    <?php Flasher::flash(); ?>

    <form action="<?= BASEURL ?>/cms/simpanIdentitas" method="POST" enctype="multipart/form-data">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Kolom Kiri: Identitas & Kontak -->
            <div class="space-y-6">
                <!-- Identitas Umum -->
                <div class="bg-white shadow-sm rounded-2xl p-5 border border-slate-100">
                    <h5 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <div class="bg-blue-50 p-2 rounded-lg">
                            <i data-lucide="building-2" class="w-5 h-5 text-blue-600"></i>
                        </div>
                        Identitas Yayasan/Sekolah
                    </h5>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Yayasan</label>
                            <input type="text" name="yayasan_name"
                                value="<?= htmlspecialchars($data['settings']['yayasan_name'] ?? '') ?>"
                                class="w-full border border-slate-300 rounded-xl text-sm px-3 py-2.5 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Sekolah</label>
                            <input type="text" name="school_name"
                                value="<?= htmlspecialchars($data['settings']['school_name'] ?? '') ?>"
                                class="w-full border border-slate-300 rounded-xl text-sm px-3 py-2.5 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Kontak -->
                <div class="bg-white shadow-sm rounded-2xl p-5 border border-slate-100">
                    <h5 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <div class="bg-green-50 p-2 rounded-lg">
                            <i data-lucide="phone" class="w-5 h-5 text-green-600"></i>
                        </div>
                        Kontak & Lokasi
                    </h5>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Alamat Lengkap</label>
                            <textarea name="address" rows="3"
                                class="w-full border border-slate-300 rounded-xl text-sm px-3 py-2.5 focus:ring-blue-500 focus:border-blue-500"><?= htmlspecialchars($data['settings']['address'] ?? '') ?></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">No. Telp / WA</label>
                                <input type="text" name="phone"
                                    value="<?= htmlspecialchars($data['settings']['phone'] ?? '') ?>"
                                    class="w-full border border-slate-300 rounded-xl text-sm px-3 py-2.5 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Email</label>
                                <input type="email" name="email"
                                    value="<?= htmlspecialchars($data['settings']['email'] ?? '') ?>"
                                    class="w-full border border-slate-300 rounded-xl text-sm px-3 py-2.5 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Embed Google Maps (Iframe
                                Src)</label>
                            <input type="text" name="maps_embed"
                                value="<?= htmlspecialchars($data['settings']['maps_embed'] ?? '') ?>"
                                class="w-full border border-slate-300 rounded-xl text-sm px-3 py-2.5 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="https://www.google.com/maps/embed?pb=...">
                        </div>
                    </div>
                </div>

                <!-- Sosmed -->
                <div class="bg-white shadow-sm rounded-2xl p-5 border border-slate-100">
                    <h5 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <div class="bg-purple-50 p-2 rounded-lg">
                            <i data-lucide="share-2" class="w-5 h-5 text-purple-600"></i>
                        </div>
                        Media Sosial
                    </h5>

                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="bg-blue-50 p-2 rounded-lg">
                                <i data-lucide="facebook" class="w-5 h-5 text-blue-600"></i>
                            </div>
                            <input type="text" name="facebook"
                                value="<?= htmlspecialchars($data['settings']['facebook'] ?? '') ?>"
                                class="flex-1 border border-slate-300 rounded-xl text-sm px-3 py-2.5 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="URL Halaman Facebook">
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="bg-pink-50 p-2 rounded-lg">
                                <i data-lucide="instagram" class="w-5 h-5 text-pink-600"></i>
                            </div>
                            <input type="text" name="instagram"
                                value="<?= htmlspecialchars($data['settings']['instagram'] ?? '') ?>"
                                class="flex-1 border border-slate-300 rounded-xl text-sm px-3 py-2.5 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="URL Profil Instagram">
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="bg-red-50 p-2 rounded-lg">
                                <i data-lucide="youtube" class="w-5 h-5 text-red-600"></i>
                            </div>
                            <input type="text" name="youtube"
                                value="<?= htmlspecialchars($data['settings']['youtube'] ?? '') ?>"
                                class="flex-1 border border-slate-300 rounded-xl text-sm px-3 py-2.5 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="URL Channel YouTube">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Sambutan & Lainnya -->
            <div class="space-y-6">
                <!-- Sambutan -->
                <div class="bg-white shadow-sm rounded-2xl p-5 border border-slate-100">
                    <h5 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <div class="bg-orange-50 p-2 rounded-lg">
                            <i data-lucide="message-square" class="w-5 h-5 text-orange-600"></i>
                        </div>
                        Sambutan / Welcome Message
                    </h5>

                    <div class="space-y-4">
                        <div
                            class="flex items-center justify-between bg-slate-50 p-3 rounded-xl border border-slate-200 mb-4">
                            <div>
                                <label class="block font-semibold text-slate-700 text-sm">Tampilkan di Beranda</label>
                                <p class="text-xs text-slate-500">Aktifkan untuk menampilkan bagian sambutan ini di
                                    halaman depan.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="home_welcome_enable" value="1" class="sr-only peer"
                                    <?= ($data['settings']['home_welcome_enable'] ?? '1') == '1' ? 'checked' : '' ?>>
                                <div
                                    class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                                </div>
                            </label>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Judul Sambutan</label>
                            <input type="text" name="welcome_title"
                                value="<?= htmlspecialchars($data['settings']['welcome_title'] ?? '') ?>"
                                class="w-full border border-slate-300 rounded-xl text-sm px-3 py-2.5 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Isi Sambutan</label>
                            <textarea name="welcome_text" rows="6"
                                class="w-full border border-slate-300 rounded-xl text-sm px-3 py-2.5 focus:ring-blue-500 focus:border-blue-500"><?= htmlspecialchars($data['settings']['welcome_text'] ?? '') ?></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Gambar Sambutan (Foto Ketua
                                Yayasan/Kepsek)</label>
                            <?php if (!empty($data['settings']['welcome_image'])): ?>
                                <div class="mb-3">
                                    <img src="<?= BASEURL ?>/public/img/cms/<?= $data['settings']['welcome_image'] ?>"
                                        class="h-40 w-auto object-cover rounded-xl border border-slate-200">
                                </div>
                            <?php endif; ?>
                            <input type="file" name="welcome_image" accept="image/*"
                                class="w-full border border-slate-300 rounded-xl text-sm file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Floating Save Button -->
        <div class="fixed bottom-6 right-6 z-50">
            <button type="submit"
                class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg shadow-blue-600/30 transition-all hover:-translate-y-1">
                <i data-lucide="save" class="w-5 h-5"></i>
                Simpan Perubahan
            </button>
        </div>
    </form>
</main>

<script>
    lucide.createIcons();
</script>