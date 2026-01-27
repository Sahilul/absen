<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($data['judul']) ?></title>

    <?php if (!empty($data['settings']['logo'])): ?>
        <link rel="icon" type="image/png" href="<?= BASEURL ?>/public/img/app/<?= $data['settings']['logo'] ?>">
        <link rel="shortcut icon" href="<?= BASEURL ?>/public/img/app/<?= $data['settings']['logo'] ?>">
    <?php endif; ?>

    <!-- PWA Settings -->
    <link rel="manifest" href="<?= BASEURL ?>/manifest">
    <meta name="theme-color" content="#16a34a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Smart Absensi">
    <link rel="apple-touch-icon" href="<?= BASEURL ?>/public/img/app/logo_1767425774.png">

    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('<?= BASEURL ?>/public/service-worker.js')
                    .then(function (registration) {
                        console.log('SW registered: ', registration.scope);
                    }, function (err) {
                        console.log('SW registration failed: ', err);
                    });
            });
        }
    </script>

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
                            900: '#14532d',
                        },
                        secondary: {
                            900: '#0f172a', /* slate-900 */
                            800: '#1e293b',
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

        .glass-nav {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800 antialiased" x-data="{ mobileMenuOpen: false }">
    <!-- Navbar -->
    <nav class="fixed w-full z-50 transition-all duration-300 glass-nav border-b border-white/20 shadow-sm">
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
        <div x-show="mobileMenuOpen" x-transition
            class="md:hidden bg-white border-t border-slate-100 absolute w-full shadow-lg z-50" x-cloak>
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
                                    class="block px-3 py-2 text-slate-600 font-medium hover:bg-slate-50 rounded-lg">
                                    <?= htmlspecialchars($menu['label']); ?>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <a href="<?= BASEURL ?>/auth/login"
                        class="block px-3 py-2 bg-primary-50 text-primary-700 font-bold rounded-lg mt-4 text-center">
                        Login Aplikasi
                    </a>
                </div>
            </div>
    </nav>

    <!-- Hero Slider -->
    <section class="relative pt-20 md:pt-0 md:h-[700px] overflow-hidden bg-slate-900" x-data="{ 
                activeSlide: 0, 
                slides: <?= count($data['sliders']) ?>,
                autoplay() { setInterval(() => { this.activeSlide = (this.activeSlide + 1) % this.slides }, 5000) }
             }" x-init="if(slides > 1) autoplay()">

        <?php if (!empty($data['sliders'])): ?>
            <?php foreach ($data['sliders'] as $index => $slider): ?>
                <div class="relative md:absolute md:inset-0 md:pt-20 transition-opacity duration-1000 ease-in-out"
                    x-show="activeSlide === <?= $index ?>" x-transition:enter="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="opacity-100" x-transition:leave-end="opacity-0"
                    :class="activeSlide !== <?= $index ?> ? 'hidden md:block' : ''">

                    <!-- Background Image - Desktop -->
                    <div class="hidden md:block absolute inset-0 bg-cover bg-center"
                        style="background-image: url('<?= BASEURL ?>/public/img/cms/<?= $slider['image'] ?>')"></div>

                    <!-- Image - Mobile (full width, preserved aspect ratio) -->
                    <img src="<?= BASEURL ?>/public/img/cms/<?= $slider['image'] ?>"
                        alt="<?= htmlspecialchars($slider['title']) ?>" class="md:hidden w-full h-auto object-contain">
                    <!-- Gradient Overlay -->
                    <div
                        class="absolute inset-0 bg-gradient-to-t from-slate-900/90 via-slate-900/40 to-transparent md:bg-gradient-to-r md:from-slate-900/90 md:via-slate-900/50 md:to-transparent">
                    </div>

                    <!-- Content -->
                    <div
                        class="absolute inset-0 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-end md:items-center pb-6 md:pb-0">
                        <div class="max-w-2xl text-white md:pt-20" style="text-shadow: 0 2px 4px rgba(0,0,0,0.8);">
                            <h2
                                class="text-sm md:text-6xl font-extrabold mb-0 md:mb-6 leading-none tracking-tight animate-fade-in-up">
                                <?= htmlspecialchars($slider['title']) ?>
                            </h2>
                            <p class="text-[10px] md:text-xl text-slate-300 mb-2 md:mb-8 leading-tight max-w-xl">
                                <?= htmlspecialchars($slider['description']) ?>
                            </p>
                            <?php if (!empty($slider['link_url']) && $slider['link_url'] != '#'): ?>
                                <a href="<?= $slider['link_url'] ?>"
                                    class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-500 text-white px-3 py-1.5 md:px-8 md:py-4 rounded-full text-[10px] md:text-base font-bold transition-all shadow-xl shadow-primary-900/50 hover:-translate-y-1">
                                    <?= $slider['button_text'] ?>
                                    <i data-lucide="arrow-right" class="w-4 h-4 md:w-5 md:h-5"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Slider Indicators -->
            <?php if (count($data['sliders']) > 1): ?>
                <div class="absolute bottom-10 left-0 right-0 z-10 flex justify-center gap-3">
                    <?php foreach ($data['sliders'] as $index => $slider): ?>
                        <button @click="activeSlide = <?= $index ?>" class="w-3 h-3 rounded-full transition-all duration-300"
                            :class="activeSlide === <?= $index ?> ? 'bg-white w-8' : 'bg-white/30 hover:bg-white/60'">
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- Default Hero if empty -->
            <div class="absolute inset-0 bg-slate-900 flex items-center justify-center">
                <div class="text-center text-white px-4">
                    <h2 class="text-4xl font-bold mb-4">Selamat Datang di
                        <?= htmlspecialchars($data['settings']['school_name']) ?>
                    </h2>
                    <p>Portal Pendidikan Berkualitas untuk Masa Depan Gemilang</p>
                </div>
            </div>
        <?php endif; ?>
    </section>

    <!-- Lembaga Kami Section -->
    <?php if (!empty($data['institutions'])): ?>
        <section id="lembaga" class="py-16 bg-white relative">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="text-center mb-12">
                    <span class="text-primary-600 font-bold tracking-wider uppercase text-sm">Unit Pendidikan</span>
                    <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mt-2">Lembaga Kami</h2>
                    <p class="text-slate-600 mt-4 max-w-2xl mx-auto">Yayasan kami memiliki beberapa lembaga pendidikan yang
                        siap mendidik generasi berkualitas.</p>
                </div>

                <!-- Institution Cards -->
                <!-- Logic: Grid 2 cols for few items, Auto-scrolling carousel for many -->
                <?php $institutionCount = count($data['institutions']); ?>
                <?php if ($institutionCount > 2): ?>
                    <!-- Auto-scrolling Carousel for many items -->
                    <div class="relative overflow-hidden" x-data="{
                        scrollEl: null,
                        init() {
                            this.scrollEl = this.$refs.carousel;
                            this.autoScroll();
                        },
                        autoScroll() {
                            setInterval(() => {
                                if (this.scrollEl) {
                                    const maxScroll = this.scrollEl.scrollWidth - this.scrollEl.clientWidth;
                                    if (this.scrollEl.scrollLeft >= maxScroll - 10) {
                                        this.scrollEl.scrollTo({ left: 0, behavior: 'smooth' });
                                    } else {
                                        this.scrollEl.scrollBy({ left: 300, behavior: 'smooth' });
                                    }
                                }
                            }, 3000);
                        }
                    }">
                        <div x-ref="carousel"
                            class="flex gap-4 overflow-x-auto snap-x snap-mandatory pb-4 scrollbar-hide md:grid md:grid-cols-3 lg:grid-cols-<?= min($institutionCount, 5) ?> md:overflow-visible md:pb-0 md:gap-6">
                        <?php else: ?>
                            <!-- Centered Grid for few items (2 columns on mobile) -->
                            <div class="grid grid-cols-2 md:flex md:justify-center gap-4 md:gap-6">
                            <?php endif; ?>
                            <?php
                            $colors = [
                                'blue' => ['bg' => 'bg-blue-500', 'light' => 'bg-blue-50', 'text' => 'text-blue-600'],
                                'green' => ['bg' => 'bg-green-500', 'light' => 'bg-green-50', 'text' => 'text-green-600'],
                                'orange' => ['bg' => 'bg-orange-500', 'light' => 'bg-orange-50', 'text' => 'text-orange-600'],
                                'purple' => ['bg' => 'bg-purple-500', 'light' => 'bg-purple-50', 'text' => 'text-purple-600'],
                                'pink' => ['bg' => 'bg-pink-500', 'light' => 'bg-pink-50', 'text' => 'text-pink-600'],
                                'teal' => ['bg' => 'bg-teal-500', 'light' => 'bg-teal-50', 'text' => 'text-teal-600'],
                                'indigo' => ['bg' => 'bg-indigo-500', 'light' => 'bg-indigo-50', 'text' => 'text-indigo-600'],
                                'red' => ['bg' => 'bg-red-500', 'light' => 'bg-red-50', 'text' => 'text-red-600']
                            ];
                            foreach ($data['institutions'] as $inst):
                                $color = $colors[$inst['color']] ?? $colors['blue'];
                                // Item class: wider for carousel, flexible for grid
                                $itemClass = ($institutionCount > 2)
                                    ? 'min-w-[75vw] sm:min-w-[280px] md:min-w-0 flex-shrink-0 snap-center'
                                    : 'md:w-[320px]';
                                ?>
                                <div
                                    class="<?= $itemClass ?> group text-center p-6 rounded-2xl <?= $color['light'] ?> border border-slate-100 hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                                    <!-- Icon -->
                                    <div
                                        class="<?= $color['bg'] ?> w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg group-hover:scale-110 transition-transform">
                                        <i data-lucide="<?= htmlspecialchars($inst['icon']) ?>" class="w-8 h-8 text-white"></i>
                                    </div>

                                    <!-- Count -->
                                    <div class="text-4xl md:text-5xl font-bold <?= $color['text'] ?> mb-2"
                                        x-data="{ count: 0, target: <?= (int) $inst['student_count'] ?> }" x-init="
                                let start = 0;
                                const duration = 2000;
                                const step = target / (duration / 16);
                                const interval = setInterval(() => {
                                    start += step;
                                    if (start >= target) {
                                        count = target;
                                        clearInterval(interval);
                                    } else {
                                        count = Math.floor(start);
                                    }
                                }, 16);
                             " x-text="count.toLocaleString('id-ID')">0</div>

                                    <!-- Label -->
                                    <h4 class="font-bold text-slate-800 text-lg">
                                        <?= htmlspecialchars($inst['short_name'] ?: $inst['name']) ?>
                                    </h4>
                                    <?php if (!empty($inst['description'])): ?>
                                        <p class="text-slate-500 text-sm mt-1 line-clamp-1">
                                            <?= htmlspecialchars($inst['description']) ?>
                                        </p>
                                    <?php else: ?>
                                        <p class="text-slate-500 text-sm mt-1">Siswa Aktif</p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if ($institutionCount > 2): ?>
                        </div>
                    <?php endif; ?>
                </div>
        </section>
    <?php endif; ?>

    <!-- Sambutan Section -->
    <?php if (($data['settings']['home_welcome_enable'] ?? '1') == '1'): ?>
        <section id="sambutan" class="py-20 bg-white relative overflow-hidden">
            <div class="absolute top-0 right-0 opacity-5">
                <i data-lucide="quote" class="w-96 h-96"></i>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="grid md:grid-cols-2 gap-12 md:gap-20 items-center">
                    <!-- Image -->
                    <div class="relative group">
                        <div
                            class="absolute -inset-4 bg-primary-100/50 rounded-3xl transform -rotate-3 transition-transform group-hover:rotate-0">
                        </div>
                        <img src="<?= !empty($data['settings']['welcome_image']) ? BASEURL . '/public/img/cms/' . $data['settings']['welcome_image'] : 'https://placehold.co/600x800/e2e8f0/64748b?text=Foto+Kepsek' ?>"
                            alt="Kepala Sekolah" class="relative rounded-2xl shadow-2xl w-full object-cover h-[500px]">
                    </div>

                    <!-- Text -->
                    <div>
                        <span class="text-primary-600 font-bold tracking-wider uppercase text-sm">Sambutan Kepala
                            Sekolah</span>
                        <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mt-2 mb-6">
                            <?= htmlspecialchars($data['settings']['welcome_title'] ?? 'Sambutan Kepala Sekolah') ?>
                        </h2>
                        <div class="prose prose-lg text-slate-600 leading-relaxed text-justify">
                            <?= nl2br(htmlspecialchars($data['settings']['welcome_text'] ?? 'Selamat datang di website resmi kami...')) ?>
                        </div>

                        <div class="mt-8 flex items-center gap-4">
                            <div class="h-px bg-slate-200 flex-1"></div>
                            <i data-lucide="graduation-cap" class="text-primary-500 w-8 h-8"></i>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Berita Section -->
    <?php if (!empty($data['posts'])): ?>
        <section id="berita" class="py-20 bg-slate-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="text-center mb-12">
                    <span class="text-primary-600 font-bold tracking-wider uppercase text-sm">Informasi Terkini</span>
                    <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mt-2">Berita & Pengumuman</h2>
                    <p class="text-slate-600 mt-4 max-w-2xl mx-auto">Ikuti berita terbaru dan pengumuman penting dari
                        sekolah kami.</p>
                </div>

                <!-- News Grid -->
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach ($data['posts'] as $post): ?>
                        <article class="bg-white rounded-2xl shadow-lg overflow-hidden group hover:shadow-xl transition-shadow">
                            <!-- Image -->
                            <a href="<?= BASEURL ?>/news/detail/<?= $post['slug'] ?>"
                                class="block relative h-48 bg-slate-100 overflow-hidden">
                                <?php if (!empty($post['image'])): ?>
                                    <img src="<?= BASEURL ?>/public/img/cms/<?= $post['image'] ?>"
                                        alt="<?= htmlspecialchars($post['title']) ?>"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                <?php else: ?>
                                    <div
                                        class="w-full h-full flex items-center justify-center bg-gradient-to-br from-primary-100 to-primary-200">
                                        <i data-lucide="newspaper" class="w-12 h-12 text-primary-400"></i>
                                    </div>
                                <?php endif; ?>

                                <!-- Date Badge -->
                                <div class="absolute top-4 left-4 bg-white/90 backdrop-blur-sm px-3 py-1.5 rounded-lg shadow">
                                    <span
                                        class="text-xs font-bold text-primary-600"><?= date('d M Y', strtotime($post['published_at'])) ?></span>
                                </div>

                                <!-- Type Badge -->
                                <div
                                    class="absolute top-4 right-4 px-2 py-1 rounded-full shadow text-xs font-bold
                                    <?= $post['type'] == 'news' ? 'bg-blue-500 text-white' : 'bg-orange-500 text-white' ?>">
                                    <?= $post['type'] == 'news' ? 'Berita' : 'Pengumuman' ?>
                                </div>
                            </a>

                            <!-- Content -->
                            <div class="p-6">
                                <h3
                                    class="font-bold text-lg text-slate-800 mb-3 line-clamp-2 group-hover:text-primary-600 transition-colors">
                                    <a href="<?= BASEURL ?>/news/detail/<?= $post['slug'] ?>">
                                        <?= htmlspecialchars($post['title']) ?>
                                    </a>
                                </h3>
                                <p class="text-slate-600 text-sm line-clamp-3 mb-4">
                                    <?= htmlspecialchars(strip_tags(substr($post['content'] ?? '', 0, 150))) ?>...
                                </p>

                                <a href="<?= BASEURL ?>/news/detail/<?= $post['slug'] ?>"
                                    class="inline-flex items-center gap-2 text-primary-600 font-semibold text-sm hover:gap-3 transition-all">
                                    Baca Selengkapnya
                                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <!-- View All Button -->
                <div class="text-center mt-12">
                    <a href="<?= BASEURL ?>/news"
                        class="inline-flex items-center gap-2 bg-white border-2 border-primary-600 text-primary-600 px-8 py-3 rounded-full font-bold hover:bg-primary-600 hover:text-white transition-all shadow-lg">
                        <span>Lihat Semua Berita</span>
                        <i data-lucide="arrow-right" class="w-5 h-5"></i>
                    </a>
                </div>
            </div>
        </section>
    <?php endif; ?>

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
                        <span class="text-xl font-bold"><?= htmlspecialchars($data['settings']['school_name']) ?></span>
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

            <?php
            $versionFile = dirname(dirname(dirname(__DIR__))) . '/version.json';
            $versionData = file_exists($versionFile) ? json_decode(file_get_contents($versionFile), true) : [];
            $appVersion = $versionData['version'] ?? '1.0.0';
            $namaApp = htmlspecialchars($data['settings']['nama_aplikasi'] ?? 'Smart Absensi');
            ?>

            <div class="border-t border-white/10 pt-8 text-center text-slate-500 text-sm">
                &copy; <?= date('Y') ?> <?= htmlspecialchars($data['settings']['yayasan_name']) ?>. All rights reserved.
                Powered by <?= $namaApp; ?> v<?= $appVersion; ?>.
            </div>

            <!-- Visitor Counter (Compact) -->
            <?php if (!empty($data['visitor_stats'])): ?>
                <div class="flex flex-wrap justify-center gap-2 mt-4 text-xs">
                    <div class="bg-white/5 rounded px-2 py-1 flex items-center gap-1 border border-white/10">
                        <i data-lucide="users" class="w-3 h-3 text-primary-400"></i>
                        <span class="text-slate-500">Total:</span>
                        <span
                            class="font-semibold text-slate-300"><?= number_format($data['visitor_stats']['total_visitors'] ?? 0, 0, ',', '.') ?></span>
                    </div>
                    <div class="bg-white/5 rounded px-2 py-1 flex items-center gap-1 border border-white/10">
                        <i data-lucide="calendar" class="w-3 h-3 text-blue-400"></i>
                        <span class="text-slate-500">Hari ini:</span>
                        <span
                            class="font-semibold text-slate-300"><?= number_format($data['visitor_stats']['today_visitors'] ?? 0, 0, ',', '.') ?></span>
                    </div>
                    <div class="bg-white/5 rounded px-2 py-1 flex items-center gap-1 border border-white/10">
                        <span class="relative flex h-2 w-2">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                        </span>
                        <span class="text-slate-500">Online:</span>
                        <span
                            class="font-semibold text-green-400"><?= number_format($data['visitor_stats']['online'] ?? 0, 0, ',', '.') ?></span>
                    </div>
                    <div class="bg-white/5 rounded px-2 py-1 flex items-center gap-1 border border-white/10">
                        <i data-lucide="eye" class="w-3 h-3 text-purple-400"></i>
                        <span class="text-slate-500">Hits:</span>
                        <span
                            class="font-semibold text-slate-300"><?= number_format($data['visitor_stats']['total_hits'] ?? 0, 0, ',', '.') ?></span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </footer>

    <!-- Popup Modal -->
    <?php if (!empty($data['popups']) && is_array($data['popups']) && isset($data['popups'][0])):
        $popup = $data['popups'][0]; // Ambil popup terbaru yang aktif
        if (is_array($popup) && isset($popup['id'])):
            ?>
            <div x-data="{ open: false }" x-init="
            const lastShown = localStorage.getItem('popup_<?= $popup['id'] ?>');
            const freq = '<?= $popup['frequency'] ?>';
            const now = new Date().getTime();
            
            let shouldShow = true;
            if(freq === 'once' && lastShown) shouldShow = false;
            if(freq === 'daily' && lastShown) {
                const oneDay = 24 * 60 * 60 * 1000;
                if(now - parseInt(lastShown) < oneDay) shouldShow = false;
            }

            if(shouldShow) {
                setTimeout(() => open = true, 1000);
            }
         " x-show="open" class="fixed inset-0 z-[100]" style="display: none;" x-cloak>

                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/80 backdrop-blur-sm transition-opacity" x-show="open"
                    x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    @click="open = false; localStorage.setItem('popup_<?= $popup['id'] ?>', new Date().getTime())"></div>

                <!-- Dialog -->
                <div class="fixed inset-0 z-10 overflow-y-auto p-4 sm:p-6 md:p-8">
                    <div class="flex min-h-full items-center justify-center">
                        <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all w-full max-w-2xl mx-auto"
                            x-show="open" x-transition:enter="ease-out duration-300"
                            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave="ease-in duration-200"
                            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                            <!-- Close Button -->
                            <div class="absolute right-3 top-3 z-20">
                                <button
                                    @click="open = false; localStorage.setItem('popup_<?= $popup['id'] ?>', new Date().getTime())"
                                    class="bg-white/90 hover:bg-white rounded-full p-2 text-slate-700 shadow-lg transition-all hover:scale-110">
                                    <i data-lucide="x" class="w-5 h-5"></i>
                                </button>
                            </div>

                            <div class="bg-white">
                                <?php if (!empty($popup['image'])): ?>
                                    <img src="<?= BASEURL ?>/public/img/cms/<?= $popup['image'] ?>"
                                        class="w-full h-auto object-contain" alt="<?= htmlspecialchars($popup['title']) ?>">
                                <?php endif; ?>

                                <div class="p-6 md:p-8 text-center">
                                    <h3 class="text-xl md:text-2xl font-bold text-slate-900 mb-3">
                                        <?= htmlspecialchars($popup['title']) ?>
                                    </h3>
                                    <p class="text-slate-600 mb-6 leading-relaxed">
                                        <?= nl2br(htmlspecialchars($popup['content'])) ?>
                                    </p>

                                    <?php if (!empty($popup['link_url']) && $popup['link_url'] != '#'): ?>
                                        <a href="<?= $popup['link_url'] ?>"
                                            class="inline-block bg-primary-600 hover:bg-primary-700 text-white font-bold py-3 px-8 rounded-full shadow-lg transition-transform hover:-translate-y-1">
                                            Lihat Detail
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; // is_array popup ?>
    <?php endif; // !empty popups ?>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>