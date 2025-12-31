<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($data['post']['title']) ?> -
        <?= htmlspecialchars($data['settings']['school_name'] ?? 'Sekolah') ?>
    </title>

    <?php if (!empty($data['settings']['logo'])): ?>
        <link rel="icon" type="image/png" href="<?= BASEURL ?>/public/img/app/<?= $data['settings']['logo'] ?>">
        <link rel="shortcut icon" href="<?= BASEURL ?>/public/img/app/<?= $data['settings']['logo'] ?>">
    <?php endif; ?>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                        }
                    },
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }

        .prose {
            max-width: none;
        }

        .prose p {
            margin-bottom: 1rem;
        }

        .prose h2,
        .prose h3 {
            font-weight: 700;
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800 antialiased" x-data="{ mobileMenuOpen: false }">
    <!-- Navbar -->
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <div class="flex items-center gap-3">
                    <?php if (!empty($data['settings']['logo'])): ?>
                        <img src="<?= BASEURL ?>/public/img/app/<?= $data['settings']['logo'] ?>" class="h-10 w-auto"
                            alt="Logo">
                    <?php else: ?>
                        <div class="bg-primary-600 p-2 rounded-lg text-white">
                            <i data-lucide="graduation-cap" class="w-6 h-6"></i>
                        </div>
                    <?php endif; ?>
                    <div class="block">
                        <h1 class="text-lg font-bold text-slate-900 leading-tight">
                            <?= htmlspecialchars($data['settings']['school_name'] ?? 'Smart Absensi') ?>
                        </h1>
                        <p class="text-xs text-primary-600 font-medium">
                            <?= htmlspecialchars($data['settings']['yayasan_name'] ?? '') ?>
                        </p>
                    </div>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-1">
                    <?php if (!empty($data['menus'])): ?>
                        <?php foreach ($data['menus'] as $menu): ?>
                            <?php if (!empty($menu['children'])): ?>
                                <!-- Dropdown Menu -->
                                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                                    <button @click="open = !open"
                                        class="flex items-center gap-1 px-4 py-2 text-slate-600 hover:text-primary-600 font-medium transition-colors focus:outline-none">
                                        <?= htmlspecialchars($menu['label']); ?>
                                        <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-200"
                                            :class="open ? 'rotate-180' : ''"></i>
                                    </button>
                                    <div x-show="open" x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 translate-y-2"
                                        x-transition:enter-end="opacity-100 translate-y-0"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100 translate-y-0"
                                        x-transition:leave-end="opacity-0 translate-y-2"
                                        class="absolute top-full left-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-slate-100 py-2 z-50"
                                        style="display: none;">
                                        <?php foreach ($menu['children'] as $child): ?>
                                            <a href="<?= $child['url']; ?>"
                                                class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 hover:text-primary-600 transition-colors">
                                                <?= htmlspecialchars($child['label']); ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <a href="<?= $menu['url']; ?>"
                                    class="px-4 py-2 text-slate-600 hover:text-primary-600 font-medium transition-colors">
                                    <?= htmlspecialchars($menu['label']); ?>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <a href="<?= BASEURL ?>/auth/login"
                        class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-2.5 rounded-full font-semibold shadow-lg shadow-primary-600/20 transition-all hover:scale-105 flex items-center gap-2">
                        <i data-lucide="log-in" class="w-4 h-4"></i>
                        Login Aplikasi
                    </a>
                </div>

                <!-- Mobile Button -->
                <div class="md:hidden flex items-center">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-slate-600 p-2">
                        <i data-lucide="menu" class="w-6 h-6" x-show="!mobileMenuOpen"></i>
                        <i data-lucide="x" class="w-6 h-6" x-show="mobileMenuOpen" x-cloak></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div x-show="mobileMenuOpen" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2" class="md:hidden bg-white border-b border-slate-100 absolute w-full shadow-lg z-50"
            x-cloak>
            <div class="px-4 py-4 space-y-3">
                <div class="space-y-2 overflow-y-auto max-h-[80vh]">
                    <?php if (!empty($data['menus'])): ?>
                        <?php foreach ($data['menus'] as $menu): ?>
                            <?php if (!empty($menu['children'])): ?>
                                <div x-data="{ expanded: false }">
                                    <button @click="expanded = !expanded"
                                        class="flex items-center justify-between w-full px-3 py-2 text-slate-600 font-medium hover:bg-slate-50 rounded-lg">
                                        <?= htmlspecialchars($menu['label']); ?>
                                        <i data-lucide="chevron-down" class="w-5 h-5 transition-transform"
                                            :class="expanded ? 'rotate-180' : ''"></i>
                                    </button>
                                    <div x-show="expanded" class="pl-4 border-l border-slate-100 ml-3 space-y-1 mt-1">
                                        <?php foreach ($menu['children'] as $child): ?>
                                            <a href="<?= $child['url']; ?>"
                                                class="block px-3 py-2 text-sm text-slate-500 hover:text-primary-600 hover:bg-slate-50 rounded-lg">
                                                <?= htmlspecialchars($child['label']); ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <a href="<?= $menu['url']; ?>"
                                    class="block px-3 py-2 text-slate-600 hover:bg-slate-50 hover:text-primary-600 rounded-lg font-medium">
                                    <?= htmlspecialchars($menu['label']); ?>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <a href="<?= BASEURL ?>/auth/login"
                    class="block w-full text-center mt-4 bg-primary-600 text-white px-6 py-3 rounded-xl font-bold shadow-lg shadow-primary-600/20">
                    Login Aplikasi
                </a>
            </div>
        </div>
    </nav>

    <!-- Article -->
    <main class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Breadcrumb -->
            <nav class="flex items-center gap-2 text-sm text-slate-500 mb-8">
                <a href="<?= BASEURL ?>" class="hover:text-primary-600">Beranda</a>
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                <a href="<?= BASEURL ?>/news" class="hover:text-primary-600">Berita</a>
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                <span class="text-slate-700 truncate max-w-xs"><?= htmlspecialchars($data['post']['title']) ?></span>
            </nav>

            <!-- Article Header -->
            <article class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <!-- Featured Image -->
                <?php if (!empty($data['post']['image'])): ?>
                    <div class="relative h-64 md:h-96 bg-slate-100">
                        <img src="<?= BASEURL ?>/public/img/cms/<?= $data['post']['image'] ?>"
                            alt="<?= htmlspecialchars($data['post']['title']) ?>" class="w-full h-full object-cover">
                    </div>
                <?php endif; ?>

                <div class="p-8 md:p-12">
                    <!-- Meta -->
                    <div class="flex flex-wrap items-center gap-4 text-sm text-slate-500 mb-6">
                        <span class="flex items-center gap-2">
                            <i data-lucide="calendar" class="w-4 h-4"></i>
                            <?= date('d F Y', strtotime($data['post']['published_at'])) ?>
                        </span>
                        <span class="flex items-center gap-2">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                            <?= number_format($data['post']['view_count']) ?> dilihat
                        </span>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-primary-100 text-primary-700">
                            <?= ucfirst($data['post']['type']) ?>
                        </span>
                    </div>

                    <!-- Title -->
                    <h1 class="text-2xl md:text-4xl font-bold text-slate-900 mb-8 leading-tight">
                        <?= htmlspecialchars($data['post']['title']) ?>
                    </h1>

                    <!-- Content -->
                    <div class="prose prose-lg text-slate-700 leading-relaxed">
                        <?= nl2br($data['post']['content']) ?>
                    </div>

                    <!-- Share -->
                    <div class="mt-12 pt-8 border-t border-slate-100">
                        <div class="flex items-center justify-between flex-wrap gap-4">
                            <span class="text-slate-500 font-medium">Bagikan artikel ini:</span>
                            <div class="flex items-center gap-3">
                                <a href="https://wa.me/?text=<?= urlencode($data['post']['title'] . ' - ' . BASEURL . '/news/' . $data['post']['slug']) ?>"
                                    target="_blank"
                                    class="bg-green-500 text-white p-2.5 rounded-full hover:bg-green-600 transition-colors">
                                    <i data-lucide="message-circle" class="w-5 h-5"></i>
                                </a>
                                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(BASEURL . '/news/' . $data['post']['slug']) ?>"
                                    target="_blank"
                                    class="bg-blue-600 text-white p-2.5 rounded-full hover:bg-blue-700 transition-colors">
                                    <i data-lucide="facebook" class="w-5 h-5"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </article>

            <!-- Back Button -->
            <div class="mt-8 text-center">
                <a href="<?= BASEURL ?>/news"
                    class="inline-flex items-center gap-2 text-primary-600 font-semibold hover:underline">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                    Kembali ke Daftar Berita
                </a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <!-- Footer -->
    <footer id="kontak" class="bg-slate-900 text-white pt-20 pb-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-12 mb-16">
                <!-- Brand -->
                <div>
                    <div class="flex items-center gap-3 mb-6">
                        <?php if (!empty($data['settings']['logo'])): ?>
                            <img src="<?= BASEURL ?>/public/img/app/<?= $data['settings']['logo'] ?>" class="h-10 w-auto"
                                alt="Logo">
                        <?php else: ?>
                            <div class="bg-primary-600 p-2 rounded-lg">
                                <i data-lucide="graduation-cap" class="w-6 h-6 text-white"></i>
                            </div>
                        <?php endif; ?>
                        <span
                            class="text-xl font-bold"><?= htmlspecialchars($data['settings']['school_name'] ?? 'Sekolah') ?></span>
                    </div>
                    <p class="text-slate-400 leading-relaxed mb-6">
                        Mewujudkan generasi yang cerdas, berkarakter, dan berdaya saing global melalui pendidikan
                        berkualitas.
                    </p>
                    <div class="flex gap-4">
                        <?php if (!empty($data['settings']['facebook'])): ?>
                            <a href="<?= $data['settings']['facebook'] ?>"
                                class="bg-white/10 p-2 rounded-full hover:bg-primary-600 transition-colors"><i
                                    data-lucide="facebook" class="w-5 h-5"></i></a>
                        <?php endif; ?>
                        <?php if (!empty($data['settings']['instagram'])): ?>
                            <a href="<?= $data['settings']['instagram'] ?>"
                                class="bg-white/10 p-2 rounded-full hover:bg-primary-600 transition-colors"><i
                                    data-lucide="instagram" class="w-5 h-5"></i></a>
                        <?php endif; ?>
                        <?php if (!empty($data['settings']['youtube'])): ?>
                            <a href="<?= $data['settings']['youtube'] ?>"
                                class="bg-white/10 p-2 rounded-full hover:bg-primary-600 transition-colors"><i
                                    data-lucide="youtube" class="w-5 h-5"></i></a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Contact -->
                <div>
                    <h4 class="text-lg font-bold mb-6">Hubungi Kami</h4>
                    <ul class="space-y-4 text-slate-400">
                        <li class="flex items-start gap-3">
                            <i data-lucide="map-pin" class="w-5 h-5 text-primary-500 mt-1 flex-shrink-0"></i>
                            <span><?= htmlspecialchars($data['settings']['address'] ?? '-') ?></span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i data-lucide="phone" class="w-5 h-5 text-primary-500 flex-shrink-0"></i>
                            <span><?= htmlspecialchars($data['settings']['phone'] ?? '-') ?></span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i data-lucide="mail" class="w-5 h-5 text-primary-500 flex-shrink-0"></i>
                            <span><?= htmlspecialchars($data['settings']['email'] ?? '-') ?></span>
                        </li>
                    </ul>
                </div>

                <!-- Maps -->
                <div class="bg-slate-800 rounded-xl overflow-hidden h-64 border border-white/10 relative">
                    <?php if (!empty($data['settings']['maps_embed'])): ?>
                        <iframe src="<?= $data['settings']['maps_embed'] ?>" width="100%" height="100%" style="border:0;"
                            allowfullscreen="" loading="lazy"></iframe>
                    <?php else: ?>
                        <div class="absolute inset-0 flex items-center justify-center text-slate-500">
                            <span>Peta lokasi belum diatur</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="border-t border-white/10 pt-8 text-center text-slate-500 text-sm">
                &copy; <?= date('Y') ?> <?= htmlspecialchars($data['settings']['yayasan_name'] ?? 'Sekolah') ?>. All
                rights reserved.
                Powered by Smart Absensi.
            </div>
        </div>
    </footer>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>