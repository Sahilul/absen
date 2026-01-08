<main class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-50 p-4 md:p-6">
    <!-- Header -->
    <div class="mb-6 bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <div class="flex items-center gap-4">
            <a href="<?= BASEURL ?>/cms/posts"
                class="text-slate-400 hover:text-slate-600 bg-slate-100 hover:bg-slate-200 p-2 rounded-lg transition-colors">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div>
                <h1 class="text-xl md:text-2xl font-bold text-slate-800">Tulis Artikel Baru</h1>
                <p class="text-slate-500 text-sm mt-1">Buat berita, halaman, atau pengumuman baru.</p>
            </div>
        </div>
    </div>

    <?php Flasher::flash(); ?>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <form action="<?= BASEURL ?>/cms/simpanPost" method="POST" enctype="multipart/form-data">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-5">
                    <!-- Title -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Judul Artikel</label>
                        <input type="text" name="title" required placeholder="Masukkan judul artikel..."
                            class="w-full border border-slate-300 rounded-xl py-3 px-4 text-sm focus:border-blue-500 focus:ring-blue-500 text-slate-800 text-lg font-semibold">
                    </div>

                    <!-- Content -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Konten</label>
                        <textarea name="content" id="editor" rows="15" placeholder="Tulis isi artikel di sini..."
                            class="w-full border border-slate-300 rounded-xl py-3 px-4 text-sm focus:border-blue-500 focus:ring-blue-500 text-slate-800"></textarea>
                        <p class="text-xs text-slate-500 mt-2">Anda bisa menggunakan format HTML sederhana.</p>
                    </div>
                </div>

                <!-- Sidebar Options -->
                <div class="space-y-5">
                    <!-- Publish Settings -->
                    <div class="bg-slate-50 rounded-xl p-5 border border-slate-100">
                        <h5 class="font-bold text-slate-700 mb-4">Pengaturan Publikasi</h5>

                        <!-- Type -->
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-slate-600 mb-2">Tipe Konten</label>
                            <select name="type"
                                class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500 text-slate-700">
                                <option value="news">📰 Berita</option>
                                <option value="page">📄 Halaman</option>
                                <option value="announcement">📢 Pengumuman</option>
                            </select>
                        </div>

                        <!-- Publish Date -->
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-slate-600 mb-2">Tanggal Terbit</label>
                            <input type="datetime-local" name="published_at" value="<?= date('Y-m-d\TH:i') ?>"
                                class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500 text-slate-700">
                        </div>

                        <!-- Status -->
                        <div class="flex items-center gap-3 py-2">
                            <input type="checkbox" name="is_published" id="is_published" checked
                                class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500">
                            <label for="is_published" class="text-sm font-medium text-slate-700">Langsung
                                Publikasikan</label>
                        </div>
                    </div>

                    <!-- Featured Image -->
                    <div class="bg-slate-50 rounded-xl p-5 border border-slate-100">
                        <h5 class="font-bold text-slate-700 mb-4">Gambar Unggulan</h5>
                        <div
                            class="border-2 border-dashed border-slate-300 rounded-xl p-6 text-center hover:border-blue-400 transition-colors">
                            <i data-lucide="image-plus" class="w-10 h-10 text-slate-400 mx-auto mb-3"></i>
                            <p class="text-sm text-slate-500 mb-3">Upload gambar artikel</p>
                            <input type="file" name="image" accept="image/jpeg,image/png,image/webp"
                                class="w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-600 file:font-medium hover:file:bg-blue-100 cursor-pointer">
                        </div>
                        <p class="text-xs text-slate-500 mt-2">Format: JPG, PNG, WEBP (maks 2MB)</p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-slate-100">
                <a href="<?= BASEURL ?>/cms/posts"
                    class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-600 font-medium hover:bg-slate-50 transition-colors">
                    Batal
                </a>
                <button type="submit"
                    class="px-6 py-2.5 rounded-xl bg-blue-600 text-white font-medium hover:bg-blue-700 shadow-lg shadow-blue-600/20 transition-all flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Simpan Artikel
                </button>
            </div>
        </form>
    </div>
</main>

<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        ClassicEditor
            .create(document.querySelector('#editor'), {
                toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'undo', 'redo'],
                heading: {
                    options: [
                        { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                        { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
                        { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' }
                    ]
                }
            })
            .then(editor => {
                editor.ui.view.editable.element.style.minHeight = '300px';
            })
            .catch(error => {
                console.error('CKEditor error:', error);
            });
    });
</script>