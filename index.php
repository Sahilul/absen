<?php
ob_start();
header('Vary: User-Agent');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');

if (!empty($_GET['url'])) {
    require __DIR__ . '/public/index.php';
    ob_end_flush();
    exit;
}
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
<meta name="csrf-token" content="bf6168f48d6d53a3c0bf294188bf586f4d0d4faafaa747508bdf573e28fb72f7">
<script>
(function () {
  if (window.__ABSEN_CSRF__) return;
  window.__ABSEN_CSRF__ = true;
  var m = document.querySelector('meta[name="csrf-token"]');
  var TOKEN = m ? m.getAttribute('content') : '';
  window.CSRF_TOKEN = TOKEN;
  if (!TOKEN) return;

  function ensureField(form) {
    if (!form || form.__csrfAdded) return;
    var method = (form.getAttribute('method') || 'get').toLowerCase();
    if (method !== 'post') return;
    if (form.querySelector('input[name="csrf_token"]')) { form.__csrfAdded = true; return; }
    var i = document.createElement('input');
    i.type = 'hidden'; i.name = 'csrf_token'; i.value = TOKEN;
    form.appendChild(i);
    form.__csrfAdded = true;
  }

  // Cakup form yang disubmit lewat tombol / Enter (event delegated)
  document.addEventListener('submit', function (e) { ensureField(e.target); }, true);

  // Cakup form.submit() programatis (tidak memicu event submit)
  var origSubmit = HTMLFormElement.prototype.submit;
  HTMLFormElement.prototype.submit = function () {
    ensureField(this);
    return origSubmit.call(this);
  };

  // Patch fetch(): tambahkan header X-CSRF-Token utk method non-GET
  if (typeof window.fetch === 'function') {
    var origFetch = window.fetch;
    window.fetch = function (input, init) {
      try {
        var method = 'GET';
        if (init && init.method) method = init.method;
        else if (input && input.method) method = input.method;
        method = (method || 'GET').toUpperCase();
        if (method !== 'GET' && method !== 'HEAD') {
          init = init || {};
          var h = init.headers;
          if (window.Headers && h instanceof Headers) {
            if (!h.has('X-CSRF-Token')) h.set('X-CSRF-Token', TOKEN);
          } else if (Array.isArray(h)) {
            h.push(['X-CSRF-Token', TOKEN]);
          } else {
            h = h || {};
            var has = Object.keys(h).some(function (k) { return k.toLowerCase() === 'x-csrf-token'; });
            if (!has) h['X-CSRF-Token'] = TOKEN;
          }
          init.headers = h;
        }
      } catch (e) { /* biarkan request tetap jalan */ }
      return origFetch.call(this, input, init);
    };
  }

  // Patch XMLHttpRequest
  var origOpen = XMLHttpRequest.prototype.open;
  XMLHttpRequest.prototype.open = function (method) {
    this.__csrfMethod = (method || '').toUpperCase();
    return origOpen.apply(this, arguments);
  };
  var origSend = XMLHttpRequest.prototype.send;
  XMLHttpRequest.prototype.send = function () {
    if (this.__csrfMethod && this.__csrfMethod !== 'GET' && this.__csrfMethod !== 'HEAD') {
      try { this.setRequestHeader('X-CSRF-Token', TOKEN); } catch (e) {}
    }
    return origSend.apply(this, arguments);
  };
})();
</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SABILILLAH</title>

            <link rel="icon" type="image/png" href="https://sabilillah.id/public/img/app/logo_1777553637.png">
        <link rel="shortcut icon" href="https://sabilillah.id/public/img/app/logo_1777553637.png">
    
    <!-- PWA Settings -->
    <link rel="manifest" href="https://sabilillah.id/manifest">
    <meta name="theme-color" content="#16a34a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Smart Absensi">
    <link rel="apple-touch-icon" href="https://sabilillah.id/public/img/app/logo_1767425774.png">

    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('https://sabilillah.id/public/service-worker.js')
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
    <script src="https://unpkg.com/lucide@0.460.0/dist/umd/lucide.min.js"></script>

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
                                            <img src="https://sabilillah.id/public/img/app/logo_1777553637.png" class="h-10 w-auto"
                            alt="Logo">
                                        <div class="block">
                        <h1 class="text-lg font-bold text-slate-900 leading-tight">
                            SABILILLAH                        </h1>
                        <p class="text-xs text-primary-600 font-medium">
                            Yayasan Pondok Pesantren Sabilillah                        </p>
                    </div>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-1">
                                                                                                        <a href="/"
                                    class="px-4 py-2 text-slate-600 hover:text-primary-600 font-medium transition-colors">
                                    Beranda                                </a>
                                                                                                                <a href="#"
                                    class="px-4 py-2 text-slate-600 hover:text-primary-600 font-medium transition-colors">
                                    Profil                                </a>
                                                                                                                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                                    <button @click="open = !open"
                                        class="flex items-center gap-1 px-4 py-2 text-slate-600 hover:text-primary-600 font-medium transition-colors focus:outline-none">
                                        MTs                                        <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-200"
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
                                                                                    <a href="https://rapormts.sabilillah.id"
                                                class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 hover:text-primary-600 transition-colors">
                                                Rapor                                            </a>
                                                                                    <a href="https://sinora-mts.sabilillah.id/"
                                                class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 hover:text-primary-600 transition-colors">
                                                SiNora                                            </a>
                                                                            </div>
                                </div>
                                                                                                                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                                    <button @click="open = !open"
                                        class="flex items-center gap-1 px-4 py-2 text-slate-600 hover:text-primary-600 font-medium transition-colors focus:outline-none">
                                        MA                                        <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-200"
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
                                                                                    <a href="https://raporma.sabilillah.id"
                                                class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 hover:text-primary-600 transition-colors">
                                                Rapor                                            </a>
                                                                                    <a href="https://sinora-ma.sabilillah.id/"
                                                class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 hover:text-primary-600 transition-colors">
                                                SiNora                                            </a>
                                                                            </div>
                                </div>
                                                                                                                <a href="/news"
                                    class="px-4 py-2 text-slate-600 hover:text-primary-600 font-medium transition-colors">
                                    Berita                                </a>
                                                                                                                <a href="/contact"
                                    class="px-4 py-2 text-slate-600 hover:text-primary-600 font-medium transition-colors">
                                    Kontak                                </a>
                                                                        
                    <a href="https://sabilillah.id/auth/login"
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
                                                                                                        <a href="/"
                                    class="block px-3 py-2 text-slate-600 font-medium hover:bg-slate-50 rounded-lg">
                                    Beranda                                </a>
                                                                                                                <a href="#"
                                    class="block px-3 py-2 text-slate-600 font-medium hover:bg-slate-50 rounded-lg">
                                    Profil                                </a>
                                                                                                                <div x-data="{ expanded: false }">
                                    <button @click="expanded = !expanded"
                                        class="flex items-center justify-between w-full px-3 py-2 text-slate-600 font-medium hover:bg-slate-50 rounded-lg">
                                        MTs                                        <i data-lucide="chevron-down" class="w-5 h-5 transition-transform"
                                            :class="expanded ? 'rotate-180' : ''"></i>
                                    </button>
                                    <div x-show="expanded" class="pl-4 border-l border-slate-100 ml-3 space-y-1 mt-1">
                                                                                    <a href="https://rapormts.sabilillah.id"
                                                class="block px-3 py-2 text-sm text-slate-500 hover:text-primary-600 hover:bg-slate-50 rounded-lg">
                                                Rapor                                            </a>
                                                                                    <a href="https://sinora-mts.sabilillah.id/"
                                                class="block px-3 py-2 text-sm text-slate-500 hover:text-primary-600 hover:bg-slate-50 rounded-lg">
                                                SiNora                                            </a>
                                                                            </div>
                                </div>
                                                                                                                <div x-data="{ expanded: false }">
                                    <button @click="expanded = !expanded"
                                        class="flex items-center justify-between w-full px-3 py-2 text-slate-600 font-medium hover:bg-slate-50 rounded-lg">
                                        MA                                        <i data-lucide="chevron-down" class="w-5 h-5 transition-transform"
                                            :class="expanded ? 'rotate-180' : ''"></i>
                                    </button>
                                    <div x-show="expanded" class="pl-4 border-l border-slate-100 ml-3 space-y-1 mt-1">
                                                                                    <a href="https://raporma.sabilillah.id"
                                                class="block px-3 py-2 text-sm text-slate-500 hover:text-primary-600 hover:bg-slate-50 rounded-lg">
                                                Rapor                                            </a>
                                                                                    <a href="https://sinora-ma.sabilillah.id/"
                                                class="block px-3 py-2 text-sm text-slate-500 hover:text-primary-600 hover:bg-slate-50 rounded-lg">
                                                SiNora                                            </a>
                                                                            </div>
                                </div>
                                                                                                                <a href="/news"
                                    class="block px-3 py-2 text-slate-600 font-medium hover:bg-slate-50 rounded-lg">
                                    Berita                                </a>
                                                                                                                <a href="/contact"
                                    class="block px-3 py-2 text-slate-600 font-medium hover:bg-slate-50 rounded-lg">
                                    Kontak                                </a>
                                                                                            <a href="https://sabilillah.id/auth/login"
                        class="block px-3 py-2 bg-primary-50 text-primary-700 font-bold rounded-lg mt-4 text-center">
                        Login Aplikasi
                    </a>
                </div>
            </div>
    </nav>

    <!-- Hero Slider -->
    <section class="relative pt-20 md:pt-0 md:h-[700px] overflow-hidden bg-slate-900" x-data="{ 
                activeSlide: 0, 
                slides: 3,
                autoplay() { setInterval(() => { this.activeSlide = (this.activeSlide + 1) % this.slides }, 5000) }
             }" x-init="if(slides > 1) autoplay()">

                                    <div class="relative md:absolute md:inset-0 md:pt-20 transition-opacity duration-1000 ease-in-out"
                    x-show="activeSlide === 0" x-transition:enter="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="opacity-100" x-transition:leave-end="opacity-0"
                    :class="activeSlide !== 0 ? 'hidden md:block' : ''">

                    <!-- Background Image - Desktop -->
                    <div class="hidden md:block absolute inset-0 bg-cover bg-center"
                        style="background-image: url('https://sabilillah.id/public/img/cms/slide_1765891874.jpg')"></div>

                    <!-- Image - Mobile (full width, preserved aspect ratio) -->
                    <img src="https://sabilillah.id/public/img/cms/slide_1765891874.jpg"
                        alt="Dewan Asatidz" class="md:hidden w-full h-auto object-contain">
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
                                Dewan Asatidz                            </h2>
                            <p class="text-[10px] md:text-xl text-slate-300 mb-2 md:mb-8 leading-tight max-w-xl">
                                Dewan Asatidz Pondok Pesantren Sabilillah                            </p>
                                                    </div>
                    </div>
                </div>
                            <div class="relative md:absolute md:inset-0 md:pt-20 transition-opacity duration-1000 ease-in-out"
                    x-show="activeSlide === 1" x-transition:enter="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="opacity-100" x-transition:leave-end="opacity-0"
                    :class="activeSlide !== 1 ? 'hidden md:block' : ''">

                    <!-- Background Image - Desktop -->
                    <div class="hidden md:block absolute inset-0 bg-cover bg-center"
                        style="background-image: url('https://sabilillah.id/public/img/cms/slide_1768872093.jpg')"></div>

                    <!-- Image - Mobile (full width, preserved aspect ratio) -->
                    <img src="https://sabilillah.id/public/img/cms/slide_1768872093.jpg"
                        alt="Peringatan Isra&#039; Mi&#039;raj" class="md:hidden w-full h-auto object-contain">
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
                                Peringatan Isra&#039; Mi&#039;raj                            </h2>
                            <p class="text-[10px] md:text-xl text-slate-300 mb-2 md:mb-8 leading-tight max-w-xl">
                                Peringatan Isra&#039; Mi&#039;raj Nabi Muhammad SAW                            </p>
                                                    </div>
                    </div>
                </div>
                            <div class="relative md:absolute md:inset-0 md:pt-20 transition-opacity duration-1000 ease-in-out"
                    x-show="activeSlide === 2" x-transition:enter="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="opacity-100" x-transition:leave-end="opacity-0"
                    :class="activeSlide !== 2 ? 'hidden md:block' : ''">

                    <!-- Background Image - Desktop -->
                    <div class="hidden md:block absolute inset-0 bg-cover bg-center"
                        style="background-image: url('https://sabilillah.id/public/img/cms/slide_1769477158.jpg')"></div>

                    <!-- Image - Mobile (full width, preserved aspect ratio) -->
                    <img src="https://sabilillah.id/public/img/cms/slide_1769477158.jpg"
                        alt="Dewan Guru" class="md:hidden w-full h-auto object-contain">
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
                                Dewan Guru                            </h2>
                            <p class="text-[10px] md:text-xl text-slate-300 mb-2 md:mb-8 leading-tight max-w-xl">
                                Dewan Guru MTs Dan MA Sabilillah                            </p>
                                                    </div>
                    </div>
                </div>
            
            <!-- Slider Indicators -->
                            <div class="absolute bottom-10 left-0 right-0 z-10 flex justify-center gap-3">
                                            <button @click="activeSlide = 0" class="w-3 h-3 rounded-full transition-all duration-300"
                            :class="activeSlide === 0 ? 'bg-white w-8' : 'bg-white/30 hover:bg-white/60'">
                        </button>
                                            <button @click="activeSlide = 1" class="w-3 h-3 rounded-full transition-all duration-300"
                            :class="activeSlide === 1 ? 'bg-white w-8' : 'bg-white/30 hover:bg-white/60'">
                        </button>
                                            <button @click="activeSlide = 2" class="w-3 h-3 rounded-full transition-all duration-300"
                            :class="activeSlide === 2 ? 'bg-white w-8' : 'bg-white/30 hover:bg-white/60'">
                        </button>
                                    </div>
            
            </section>

    <!-- Lembaga Kami Section -->
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
                                                            <!-- Centered Grid for few items (2 columns on mobile) -->
                            <div class="grid grid-cols-2 md:flex md:justify-center gap-4 md:gap-6">
                                                                                        <div
                                    class="md:w-[320px] group text-center p-6 rounded-2xl bg-blue-50 border border-slate-100 hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                                    <!-- Icon -->
                                    <div
                                        class="bg-blue-500 w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg group-hover:scale-110 transition-transform">
                                        <i data-lucide="school" class="w-8 h-8 text-white"></i>
                                    </div>

                                    <!-- Count -->
                                    <div class="text-4xl md:text-5xl font-bold text-blue-600 mb-2"
                                        x-data="{ count: 0, target: 91 }" x-init="
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
                                        MTS                                    </h4>
                                                                            <p class="text-slate-500 text-sm mt-1">Siswa Aktif</p>
                                                                    </div>
                                                            <div
                                    class="md:w-[320px] group text-center p-6 rounded-2xl bg-teal-50 border border-slate-100 hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                                    <!-- Icon -->
                                    <div
                                        class="bg-teal-500 w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg group-hover:scale-110 transition-transform">
                                        <i data-lucide="school" class="w-8 h-8 text-white"></i>
                                    </div>

                                    <!-- Count -->
                                    <div class="text-4xl md:text-5xl font-bold text-teal-600 mb-2"
                                        x-data="{ count: 0, target: 61 }" x-init="
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
                                        MA                                    </h4>
                                                                            <p class="text-slate-500 text-sm mt-1">Siswa Aktif</p>
                                                                    </div>
                                                    </div>
                                        </div>
        </section>
    
    <!-- Sambutan Section -->
    
    <!-- Berita Section -->
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
                                            <article class="bg-white rounded-2xl shadow-lg overflow-hidden group hover:shadow-xl transition-shadow">
                            <!-- Image -->
                            <a href="https://sabilillah.id/news/detail/peringatan-isra-mi-raj-di-mts-ma-sabilillah-momentum-perkuat-ibadah-dan-karakter-generasi-muda"
                                class="block relative h-48 bg-slate-100 overflow-hidden">
                                                                    <img src="https://sabilillah.id/public/img/cms/post_1768611997.jpeg"
                                        alt="Peringatan Isra’ Mi’raj di MTs &amp; MA Sabilillah: Momentum Perkuat Ibadah dan Karakter Generasi Muda"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                
                                <!-- Date Badge -->
                                <div class="absolute top-4 left-4 bg-white/90 backdrop-blur-sm px-3 py-1.5 rounded-lg shadow">
                                    <span
                                        class="text-xs font-bold text-primary-600">17 Jan 2026</span>
                                </div>

                                <!-- Type Badge -->
                                <div
                                    class="absolute top-4 right-4 px-2 py-1 rounded-full shadow text-xs font-bold
                                    bg-blue-500 text-white">
                                    Berita                                </div>
                            </a>

                            <!-- Content -->
                            <div class="p-6">
                                <h3
                                    class="font-bold text-lg text-slate-800 mb-3 line-clamp-2 group-hover:text-primary-600 transition-colors">
                                    <a href="https://sabilillah.id/news/detail/peringatan-isra-mi-raj-di-mts-ma-sabilillah-momentum-perkuat-ibadah-dan-karakter-generasi-muda">
                                        Peringatan Isra’ Mi’raj di MTs &amp; MA Sabilillah: Momentum Perkuat Ibadah dan Karakter Generasi Muda                                    </a>
                                </h3>
                                <p class="text-slate-600 text-sm line-clamp-3 mb-4">
                                    Keluarga besar Madrasah Tsanawiyah (MTs) dan Madrasah Aliyah (MA) Sabilillah menggelar peringatan Isra’ Mi’raj Nabi Muhammad SAW 1447 H dengan ...
                                </p>

                                <a href="https://sabilillah.id/news/detail/peringatan-isra-mi-raj-di-mts-ma-sabilillah-momentum-perkuat-ibadah-dan-karakter-generasi-muda"
                                    class="inline-flex items-center gap-2 text-primary-600 font-semibold text-sm hover:gap-3 transition-all">
                                    Baca Selengkapnya
                                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </article>
                                            <article class="bg-white rounded-2xl shadow-lg overflow-hidden group hover:shadow-xl transition-shadow">
                            <!-- Image -->
                            <a href="https://sabilillah.id/news/detail/membaca-sejarah-pendidikan-ketika-pendidikan-berangkat-dari-kepedulian-sosial"
                                class="block relative h-48 bg-slate-100 overflow-hidden">
                                                                    <img src="https://sabilillah.id/public/img/cms/post_1766654350.jpeg"
                                        alt="MEMBACA SEJARAH PENDIDIKAN: KETIKA PENDIDIKAN BERANGKAT DARI KEPEDULIAN SOSIAL"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                
                                <!-- Date Badge -->
                                <div class="absolute top-4 left-4 bg-white/90 backdrop-blur-sm px-3 py-1.5 rounded-lg shadow">
                                    <span
                                        class="text-xs font-bold text-primary-600">25 Dec 2025</span>
                                </div>

                                <!-- Type Badge -->
                                <div
                                    class="absolute top-4 right-4 px-2 py-1 rounded-full shadow text-xs font-bold
                                    bg-blue-500 text-white">
                                    Berita                                </div>
                            </a>

                            <!-- Content -->
                            <div class="p-6">
                                <h3
                                    class="font-bold text-lg text-slate-800 mb-3 line-clamp-2 group-hover:text-primary-600 transition-colors">
                                    <a href="https://sabilillah.id/news/detail/membaca-sejarah-pendidikan-ketika-pendidikan-berangkat-dari-kepedulian-sosial">
                                        MEMBACA SEJARAH PENDIDIKAN: KETIKA PENDIDIKAN BERANGKAT DARI KEPEDULIAN SOSIAL                                    </a>
                                </h3>
                                <p class="text-slate-600 text-sm line-clamp-3 mb-4">
                                    Kegiatan Masa Ta’aruf Siswa Madrasah (MATSAMA) di MTs dan MA Sabilillah menjadi ruang awal yang penting untuk menanamkan pemaham...
                                </p>

                                <a href="https://sabilillah.id/news/detail/membaca-sejarah-pendidikan-ketika-pendidikan-berangkat-dari-kepedulian-sosial"
                                    class="inline-flex items-center gap-2 text-primary-600 font-semibold text-sm hover:gap-3 transition-all">
                                    Baca Selengkapnya
                                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </article>
                                            <article class="bg-white rounded-2xl shadow-lg overflow-hidden group hover:shadow-xl transition-shadow">
                            <!-- Image -->
                            <a href="https://sabilillah.id/news/detail/mengisi-libur-sekolah-dengan-ziarah-auliya-guru-mts-dan-ma-sabilillah-perkuat-spirit-keilmuan"
                                class="block relative h-48 bg-slate-100 overflow-hidden">
                                                                    <img src="https://sabilillah.id/public/img/cms/post_1766067406.jpeg"
                                        alt="Mengisi Libur Sekolah dengan Ziarah Auliya, Guru MTs dan MA Sabilillah Perkuat Spirit Keilmuan"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                
                                <!-- Date Badge -->
                                <div class="absolute top-4 left-4 bg-white/90 backdrop-blur-sm px-3 py-1.5 rounded-lg shadow">
                                    <span
                                        class="text-xs font-bold text-primary-600">18 Dec 2025</span>
                                </div>

                                <!-- Type Badge -->
                                <div
                                    class="absolute top-4 right-4 px-2 py-1 rounded-full shadow text-xs font-bold
                                    bg-blue-500 text-white">
                                    Berita                                </div>
                            </a>

                            <!-- Content -->
                            <div class="p-6">
                                <h3
                                    class="font-bold text-lg text-slate-800 mb-3 line-clamp-2 group-hover:text-primary-600 transition-colors">
                                    <a href="https://sabilillah.id/news/detail/mengisi-libur-sekolah-dengan-ziarah-auliya-guru-mts-dan-ma-sabilillah-perkuat-spirit-keilmuan">
                                        Mengisi Libur Sekolah dengan Ziarah Auliya, Guru MTs dan MA Sabilillah Perkuat Spirit Keilmuan                                    </a>
                                </h3>
                                <p class="text-slate-600 text-sm line-clamp-3 mb-4">
                                    Mengisi waktu libur sekolah, para guru MTs dan MA Sabilillah melaksanakan kegiatan ziarah ke makam para auliya pada hari ini. Kegiatan ini menjadi ...
                                </p>

                                <a href="https://sabilillah.id/news/detail/mengisi-libur-sekolah-dengan-ziarah-auliya-guru-mts-dan-ma-sabilillah-perkuat-spirit-keilmuan"
                                    class="inline-flex items-center gap-2 text-primary-600 font-semibold text-sm hover:gap-3 transition-all">
                                    Baca Selengkapnya
                                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </article>
                                    </div>

                <!-- View All Button -->
                <div class="text-center mt-12">
                    <a href="https://sabilillah.id/news"
                        class="inline-flex items-center gap-2 bg-white border-2 border-primary-600 text-primary-600 px-8 py-3 rounded-full font-bold hover:bg-primary-600 hover:text-white transition-all shadow-lg">
                        <span>Lihat Semua Berita</span>
                        <i data-lucide="arrow-right" class="w-5 h-5"></i>
                    </a>
                </div>
            </div>
        </section>
    
    <!-- Footer -->
    <footer id="kontak" class="bg-slate-900 text-white pt-20 pb-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-12 mb-16">
                <!-- Brand -->
                <div>
                    <div class="flex items-center gap-3 mb-6">
                                                    <img src="https://sabilillah.id/public/img/app/logo_1777553637.png" class="h-10 w-auto"
                                alt="Logo">
                                                <span class="text-xl font-bold">SABILILLAH</span>
                    </div>
                    <p class="text-slate-400 leading-relaxed mb-6">
                        Mewujudkan generasi yang cerdas, berkarakter, dan berdaya saing global melalui pendidikan
                        berkualitas.
                    </p>
                    <div class="flex gap-4">
                                                    <a href="https://facebook.com"
                                class="bg-white/10 p-2 rounded-full hover:bg-primary-600 transition-colors"><i
                                    data-lucide="facebook" class="w-5 h-5"></i></a>
                                                                            <a href="https://instagram.com"
                                class="bg-white/10 p-2 rounded-full hover:bg-primary-600 transition-colors"><i
                                    data-lucide="instagram" class="w-5 h-5"></i></a>
                                                                            <a href="https://youtube.com"
                                class="bg-white/10 p-2 rounded-full hover:bg-primary-600 transition-colors"><i
                                    data-lucide="youtube" class="w-5 h-5"></i></a>
                                            </div>
                </div>

                <!-- Contact -->
                <div>
                    <h4 class="text-lg font-bold mb-6">Hubungi Kami</h4>
                    <ul class="space-y-4 text-slate-400">
                        <li class="flex items-start gap-3">
                            <i data-lucide="map-pin" class="w-5 h-5 text-primary-500 mt-1 flex-shrink-0"></i>
                            <span>Watusari, Wotanmas Jedong, Ngoro, Mojokerto</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i data-lucide="phone" class="w-5 h-5 text-primary-500 flex-shrink-0"></i>
                            <span>085853704788</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i data-lucide="mail" class="w-5 h-5 text-primary-500 flex-shrink-0"></i>
                            <span>ppsabilillah@gmail.com</span>
                        </li>
                    </ul>
                </div>

                <!-- Maps -->
                <div class="bg-slate-800 rounded-xl overflow-hidden h-64 border border-white/10 relative">
                                            <iframe src="https://www.google.com/maps/embed/v1/place?q=mts%20%26%20ma%20sabilillah&key=AIzaSyBFw0Qbyq9zTFTd-tUY6dZWTgaQzuU17R8" width="100%" height="100%" style="border:0;"
                            allowfullscreen="" loading="lazy"></iframe>
                                    </div>
            </div>

            
            <div class="border-t border-white/10 pt-8 text-center text-slate-500 text-sm">
                &copy; 2026 Yayasan Pondok Pesantren Sabilillah. All rights reserved.
                Powered by Super App Sabilillah v1.27.1.
            </div>

            <!-- Visitor Counter (Compact) -->
                            <div class="flex flex-wrap justify-center gap-2 mt-4 text-xs">
                    <div class="bg-white/5 rounded px-2 py-1 flex items-center gap-1 border border-white/10">
                        <i data-lucide="users" class="w-3 h-3 text-primary-400"></i>
                        <span class="text-slate-500">Total:</span>
                        <span
                            class="font-semibold text-slate-300">98.660</span>
                    </div>
                    <div class="bg-white/5 rounded px-2 py-1 flex items-center gap-1 border border-white/10">
                        <i data-lucide="calendar" class="w-3 h-3 text-blue-400"></i>
                        <span class="text-slate-500">Hari ini:</span>
                        <span
                            class="font-semibold text-slate-300">86</span>
                    </div>
                    <div class="bg-white/5 rounded px-2 py-1 flex items-center gap-1 border border-white/10">
                        <span class="relative flex h-2 w-2">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                        </span>
                        <span class="text-slate-500">Online:</span>
                        <span
                            class="font-semibold text-green-400">3</span>
                    </div>
                    <div class="bg-white/5 rounded px-2 py-1 flex items-center gap-1 border border-white/10">
                        <i data-lucide="eye" class="w-3 h-3 text-purple-400"></i>
                        <span class="text-slate-500">Hits:</span>
                        <span
                            class="font-semibold text-slate-300">769.952</span>
                    </div>
                </div>
                    </div>
    </footer>

    <!-- Popup Modal -->
    
    <script>
        lucide.createIcons();
    </script>
</body>

</html>