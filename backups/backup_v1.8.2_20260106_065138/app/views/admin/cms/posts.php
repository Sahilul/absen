<main class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-50 p-4 md:p-6">
    <!-- Header -->
    <div class="mb-6 bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl md:text-2xl font-bold text-slate-800">Berita & Halaman</h1>
                <p class="text-slate-500 text-sm mt-1">Kelola artikel berita dan halaman statis website.</p>
            </div>
            <a href="<?= BASEURL ?>/cms/tambahPost"
                class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-medium transition-all shadow-lg shadow-blue-600/20 hover:-translate-y-0.5">
                <i data-lucide="plus-circle" class="w-5 h-5"></i>
                <span>Tulis Artikel Baru</span>
            </a>
        </div>
    </div>

    <?php Flasher::flash(); ?>

    <!-- Filter Tabs -->
    <div class="mb-6 flex flex-wrap gap-2">
        <a href="<?= BASEURL ?>/cms/posts"
            class="px-4 py-2 rounded-lg text-sm font-medium bg-blue-600 text-white">Semua</a>
        <span class="px-4 py-2 rounded-lg text-sm font-medium bg-slate-100 text-slate-600">
            Total: <?= count($data['posts']) ?> Artikel
        </span>
    </div>

    <!-- Posts Grid -->
    <?php if (empty($data['posts'])): ?>
        <div class="bg-white rounded-2xl p-10 text-center shadow-sm border border-slate-100">
            <div class="bg-blue-50 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                <i data-lucide="newspaper" class="w-8 h-8 text-blue-600"></i>
            </div>
            <h5 class="text-lg font-bold text-slate-800 mb-2">Belum ada artikel</h5>
            <p class="text-slate-500 mb-6 max-w-sm mx-auto">Mulai tulis berita atau buat halaman untuk website sekolah.</p>
            <a href="<?= BASEURL ?>/cms/tambahPost" class="text-blue-600 font-semibold hover:underline">
                + Tulis Artikel Sekarang
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($data['posts'] as $post):
                $isPublished = $post['is_published'] == 1;
                $typeColors = [
                    'news' => 'bg-blue-50 text-blue-700 border-blue-200',
                    'page' => 'bg-green-50 text-green-700 border-green-200',
                    'announcement' => 'bg-orange-50 text-orange-700 border-orange-200'
                ];
                $typeLabels = [
                    'news' => 'Berita',
                    'page' => 'Halaman',
                    'announcement' => 'Pengumuman'
                ];
                ?>
                <div
                    class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden group hover:shadow-md transition-shadow flex flex-col">
                    <!-- Image -->
                    <div class="relative h-40 bg-slate-100">
                        <?php if (!empty($post['image'])): ?>
                            <img src="<?= BASEURL ?>/public/img/cms/<?= $post['image'] ?>"
                                alt="<?= htmlspecialchars($post['title']) ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div
                                class="w-full h-full flex items-center justify-center bg-gradient-to-br from-slate-100 to-slate-200">
                                <i data-lucide="image" class="w-12 h-12 text-slate-300"></i>
                            </div>
                        <?php endif; ?>

                        <!-- Type Badge -->
                        <div class="absolute top-3 left-3">
                            <span
                                class="px-2.5 py-1 text-xs font-bold rounded-lg border <?= $typeColors[$post['type']] ?? $typeColors['news'] ?>">
                                <?= $typeLabels[$post['type']] ?? 'Berita' ?>
                            </span>
                        </div>

                        <!-- Status Badge -->
                        <div class="absolute top-3 right-3">
                            <span
                                class="px-2.5 py-1 text-xs font-bold rounded-lg <?= $isPublished ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600' ?>">
                                <?= $isPublished ? 'Publik' : 'Draft' ?>
                            </span>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-4 flex-1 flex flex-col">
                        <h5 class="font-bold text-slate-800 mb-2 line-clamp-2"><?= htmlspecialchars($post['title']) ?></h5>
                        <p class="text-slate-500 text-sm mb-3 line-clamp-2 flex-1">
                            <?= htmlspecialchars(strip_tags(substr($post['content'] ?? '', 0, 100))) ?>...
                        </p>

                        <div class="flex items-center gap-2 text-xs text-slate-400 mb-4">
                            <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                            <span><?= date('d M Y', strtotime($post['published_at'])) ?></span>
                            <span class="mx-1">•</span>
                            <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                            <span><?= $post['view_count'] ?> views</span>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                            <a href="<?= BASEURL ?>/cms/togglePost/<?= $post['id'] ?>/<?= $isPublished ? 0 : 1 ?>"
                                class="relative inline-flex h-6 w-11 cursor-pointer rounded-full border-2 border-transparent transition-colors <?= $isPublished ? 'bg-green-500' : 'bg-slate-300' ?>">
                                <span
                                    class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition <?= $isPublished ? 'translate-x-5' : 'translate-x-0' ?>"></span>
                            </a>

                            <div class="flex items-center gap-2">
                                <a href="<?= BASEURL ?>/cms/editPost/<?= $post['id'] ?>"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-500 hover:bg-blue-100 border border-blue-200 transition-colors"
                                    title="Edit">
                                    <i data-lucide="pencil" class="w-4 h-4"></i>
                                </a>
                                <a href="<?= BASEURL ?>/cms/hapusPost/<?= $post['id'] ?>"
                                    onclick="return confirm('Hapus artikel ini?')"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 border border-red-200 transition-colors"
                                    title="Hapus">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<script>
    lucide.createIcons();
</script>