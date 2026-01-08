<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <?php
  $namaAplikasi = htmlspecialchars($data['pengaturan_app']['nama_aplikasi'] ?? 'Smart Absensi');
  $logoApp = $data['pengaturan_app']['logo'] ?? '';

  // Cek apakah file logo ada - gunakan absolute path
  $baseDir = dirname(dirname(dirname(__DIR__))); // Path ke root folder absen
  $logoPath = $baseDir . '/public/img/app/' . $logoApp;
  $logoExists = !empty($logoApp) && file_exists($logoPath);
  ?>
  <title><?= $namaAplikasi; ?> - Login</title>

  <!-- Favicon - Menggunakan logo sebagai favicon -->
  <?php if ($logoExists): ?>
    <link rel="icon" type="image/png" href="<?= BASEURL; ?>/public/img/app/<?= htmlspecialchars($logoApp); ?>">
    <link rel="shortcut icon" href="<?= BASEURL; ?>/public/img/app/<?= htmlspecialchars($logoApp); ?>">
  <?php endif; ?>

  <!-- Tailwind -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Fonts & Icons -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <script src="https://unpkg.com/lucide@latest"></script>

  <style>
    :root {
      --radius-md: .8rem;
      --radius-xl: 1.1rem;
      --shadow-card: 0 10px 24px rgba(2, 8, 23, .08);
      --shadow-strong: 0 18px 40px rgba(2, 8, 23, .14);
    }

    body {
      font-family: 'Inter', sans-serif;
    }

    /* Card kaca */
    .glass {
      background: rgba(255, 255, 255, .78);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, .45);
      border-radius: var(--radius-xl);
      box-shadow: var(--shadow-card);
    }

    /* Input / Select */
    .input-modern {
      width: 100%;
      border: 1px solid rgb(226, 232, 240);
      background: #fff;
      border-radius: var(--radius-md);
      padding: .68rem .9rem;
      outline: none;
      transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
    }

    .input-modern:focus {
      border-color: #0ea5e9;
      box-shadow: 0 0 0 4px rgba(14, 165, 233, .15);
      background: #fff;
    }

    /* Field dengan ikon kiri */
    .field {
      position: relative;
    }

    .field-icon {
      position: absolute;
      left: .75rem;
      top: 50%;
      transform: translateY(-50%);
      pointer-events: none;
      color: #94a3b8;
    }

    /* PENTING: padding untuk input & select agar teks tidak menabrak ikon */
    .field input,
    .field select,
    .field textarea {
      padding-left: 2.6rem;
      /* ~pl-10 */
    }

    /* Select: hilangkan panah default & kasih caret sendiri di kanan */
    .field select {
      appearance: none;
      -webkit-appearance: none;
      -moz-appearance: none;
      padding-right: 2.6rem;
      /* ruang untuk caret kanan */
      background-color: #fff;
    }

    .select-caret {
      position: absolute;
      right: .6rem;
      top: 50%;
      transform: translateY(-50%);
      pointer-events: none;
      color: #94a3b8;
    }

    /* Tombol utama */
    .btn-primary {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: .5rem;
      width: 100%;
      padding: .85rem 1rem;
      border-radius: .9rem;
      color: #fff;
      font-weight: 700;
      background: #4f46e5;
      /* indigo-600 */
      box-shadow: 0 10px 22px rgba(79, 70, 229, .22);
      transition: transform .15s ease, box-shadow .15s ease, background .15s ease;
    }

    .btn-primary:hover {
      background: #4338ca;
      box-shadow: 0 16px 30px rgba(67, 56, 202, .26);
      transform: translateY(-1px);
    }

    .btn-primary:active {
      transform: translateY(0);
    }

    .btn-primary:disabled {
      opacity: .75;
      cursor: not-allowed;
    }

    /* Toggle visibility */
    .toggle-visibility {
      position: absolute;
      right: .4rem;
      top: 50%;
      transform: translateY(-50%);
      padding: .4rem .5rem;
      border-radius: .5rem;
      color: #64748b;
    }

    /* Caps Lock hint */
    .caps-hint {
      font-size: .78rem;
      color: #ef4444;
      display: none;
      margin-top: .3rem;
    }

    /* Badge brand */
    .brand-badge {
      background: linear-gradient(135deg, #22c55e, #0ea5e9);
      color: #fff;
      border-radius: 1rem;
      padding: .7rem;
      box-shadow: 0 10px 24px rgba(2, 8, 23, .2);
    }

    /* Responsive: mobile viewport */
    @media (max-width: 640px) {
      main {
        padding: 1rem 0.75rem;
        /* Kurangi padding horizontal */
        align-items: flex-start;
        /* Align ke atas agar bisa scroll */
      }

      .glass {
        padding: 1.5rem;
        /* Kurangi padding card */
        margin-top: 1rem;
        /* Beri jarak dari atas */
        margin-bottom: 1rem;
        /* Beri jarak dari bawah */
      }

      .brand-badge {
        padding: 0.5rem;
      }

      .brand-badge i {
        width: 1.75rem !important;
        height: 1.75rem !important;
      }

      h1 {
        font-size: 1.5rem !important;
      }

      .input-modern {
        padding: 0.6rem 0.75rem;
        font-size: 16px;
        /* Prevent zoom on iOS */
      }

      .field input,
      .field select {
        padding-left: 2.5rem;
      }

      .btn-primary {
        padding: 0.75rem 0.875rem;
      }

      /* Ensure scrollability */
      body {
        overflow-y: auto;
        min-height: 100vh;
        height: auto;
      }
    }
  </style>
</head>

<body class="min-h-screen bg-gradient-to-br from-indigo-50 via-sky-50 to-emerald-50 relative overflow-y-auto">

  <!-- Dekorasi blur -->
  <div class="pointer-events-none absolute -top-24 -left-24 w-80 h-80 rounded-full bg-indigo-200/50 blur-3xl"></div>
  <div
    class="pointer-events-none absolute -bottom-24 -right-24 w-[28rem] h-[28rem] rounded-full bg-sky-200/50 blur-3xl">
  </div>

  <main class="relative z-10 flex items-center justify-center min-h-screen px-4 py-8">
    <div class="w-full max-w-md">
      <!-- Flash message -->
      <div class="mb-4">
        <?php Flasher::flash(); ?>
      </div>

      <!-- Kartu login -->
      <div class="glass p-8 space-y-7">
        <!-- Header -->
        <div class="text-center">
          <div class="flex justify-center mb-4">
            <?php if ($logoExists): ?>
              <div class="brand-badge p-2 bg-white rounded-xl shadow-lg">
                <img src="<?= BASEURL; ?>/public/img/app/<?= htmlspecialchars($logoApp); ?>" alt="<?= $namaAplikasi; ?>"
                  class="w-12 h-12 object-contain">
              </div>
            <?php else: ?>
              <div class="brand-badge">
                <i data-lucide="book-user" class="w-8 h-8"></i>
              </div>
            <?php endif; ?>
          </div>
          <h1 class="text-2xl font-bold text-slate-800"><?= $namaAplikasi; ?></h1>
          <p class="text-slate-500 mt-1">Silakan login ke akun Anda</p>
        </div>

        <!-- Form -->
        <form action="<?= BASEURL; ?>/auth/prosesLogin" method="POST" class="space-y-5" id="loginForm"
          autocomplete="on">
          <!-- Username -->
          <div>
            <label for="username" class="text-sm font-semibold text-slate-700">Username</label>
            <div class="field mt-1">
              <span class="field-icon">
                <i data-lucide="user" class="w-5 h-5"></i>
              </span>
              <input id="username" name="username" type="text" required class="input-modern"
                placeholder="Masukkan username" autofocus />
            </div>
          </div>

          <!-- Password -->
          <div>
            <label for="password" class="text-sm font-semibold text-slate-700">Password</label>
            <div class="field mt-1">
              <span class="field-icon">
                <i data-lucide="lock" class="w-5 h-5"></i>
              </span>
              <input id="password" name="password" type="password" required class="input-modern pr-10"
                placeholder="Masukkan password" />
              <button type="button" class="toggle-visibility hover:bg-slate-100"
                aria-label="Tampilkan/sembunyikan password" onclick="togglePassword()">
                <i data-lucide="eye" id="eye-open" class="w-5 h-5"></i>
                <i data-lucide="eye-off" id="eye-closed" class="w-5 h-5 hidden"></i>
              </button>
            </div>
            <div id="caps-hint" class="caps-hint">Caps Lock aktif</div>
          </div>

          <!-- Semester -->
          <div>
            <label for="id_semester" class="text-sm font-semibold text-slate-700">Pilih Sesi</label>
            <div class="field mt-1">
              <span class="field-icon">
                <i data-lucide="calendar" class="w-5 h-5"></i>
              </span>
              <select id="id_semester" name="id_semester" required class="input-modern">
                <?php if (empty($data['daftar_semester'])): ?>
                  <option disabled selected>Belum ada data semester</option>
                <?php else: ?>
                  <?php
                  // LOGIKA DEFAULT SEMESTER:
                  // 1. Pertama cek apakah ada semester dengan is_default = 1 (diset admin)
                  // 2. Jika tidak ada, gunakan logika tanggal sebagai fallback
                
                  $defaultSemesterId = null;

                  // Cek semester yang di-set admin sebagai default
                  foreach ($data['daftar_semester'] as $smt) {
                    if (!empty($smt['is_default']) && $smt['is_default'] == 1) {
                      $defaultSemesterId = (int) $smt['id_semester'];
                      break;
                    }
                  }

                  // Fallback: gunakan logika tanggal jika tidak ada yang di-set admin
                  if ($defaultSemesterId === null) {
                    $bulanSekarang = (int) date('m');
                    $tahunSekarang = (int) date('Y');

                    if ($bulanSekarang >= 7 && $bulanSekarang <= 12) {
                      $tahunPelajaranAwal = $tahunSekarang;
                      $tahunPelajaranAkhir = $tahunSekarang + 1;
                      $semesterTarget = 'Ganjil';
                    } else {
                      $tahunPelajaranAwal = $tahunSekarang - 1;
                      $tahunPelajaranAkhir = $tahunSekarang;
                      $semesterTarget = 'Genap';
                    }
                    $tahunPelajaranString = $tahunPelajaranAwal . '/' . $tahunPelajaranAkhir;
                  }

                  $firstSelected = false;
                  ?>

                  <?php foreach ($data['daftar_semester'] as $smt): ?>
                    <?php
                    $selected = '';
                    $semId = (int) $smt['id_semester'];

                    // Cek apakah semester ini yang di-set admin sebagai default
                    if ($defaultSemesterId !== null && $semId === $defaultSemesterId) {
                      $selected = 'selected';
                      $firstSelected = true;
                    }
                    // Fallback ke logika tanggal jika tidak ada default dari admin
                    elseif ($defaultSemesterId === null && !$firstSelected) {
                      $namaTp = $smt['nama_tp'] ?? '';
                      $semester = $smt['semester'] ?? '';

                      $isMatch = (strpos($namaTp, $tahunPelajaranString) !== false) &&
                        (stripos($semester, $semesterTarget) !== false);

                      if ($isMatch) {
                        $selected = 'selected';
                        $firstSelected = true;
                      }
                    }
                    ?>
                    <option value="<?= $semId; ?>" <?= $selected; ?>>
                      <?= htmlspecialchars(($smt['nama_tp'] ?? 'TP') . ' - ' . ($smt['semester'] ?? 'Semester')); ?>
                    </option>
                  <?php endforeach; ?>

                  <?php
                  // Jika masih belum ada yang terpilih, pilih yang pertama
                  if (!$firstSelected && !empty($data['daftar_semester'])) {
                    echo '<script>document.querySelector("#id_semester option").selected = true;</script>';
                  }
                  ?>
                <?php endif; ?>
              </select>
              <!-- caret kanan custom -->
              <span class="select-caret">
                <i data-lucide="chevron-down" class="w-5 h-5"></i>
              </span>
            </div>

            <!-- Info semester yang dipilih otomatis -->
            <div class="mt-2 text-xs text-slate-600 bg-blue-50 rounded-lg p-2">
              <i data-lucide="info" class="w-3 h-3 inline mr-1"></i>
              <?php if ($defaultSemesterId !== null): ?>
                Default: Diatur oleh Admin
              <?php else: ?>
                Auto: Berdasarkan tanggal
              <?php endif; ?>
            </div>
          </div>

          <!-- Submit -->
          <div class="pt-1">
            <button type="submit" class="btn-primary" id="submitBtn">
              <i data-lucide="log-in" class="w-5 h-5"></i>
              <span>Login</span>
            </button>
          </div>
        </form>

        <?php if (defined('GOOGLE_OAUTH_ENABLED') && GOOGLE_OAUTH_ENABLED): ?>
          <!-- Divider -->
          <div class="relative">
            <div class="absolute inset-0 flex items-center">
              <div class="w-full border-t border-slate-200"></div>
            </div>
            <div class="relative flex justify-center text-xs uppercase">
              <span class="bg-white px-2 text-slate-500">Atau</span>
            </div>
          </div>

          <!-- Google Login Button -->
          <div>
            <button type="button" onclick="loginWithGoogle()"
              class="flex items-center justify-center gap-3 w-full px-4 py-3 border border-slate-300 rounded-lg bg-white hover:bg-slate-50 transition-colors duration-200 shadow-sm hover:shadow group">
              <svg class="w-5 h-5" viewBox="0 0 24 24">
                <path fill="#4285F4"
                  d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                <path fill="#34A853"
                  d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                <path fill="#FBBC05"
                  d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                <path fill="#EA4335"
                  d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
              </svg>
              <span class="font-semibold text-slate-700 group-hover:text-slate-900">Login dengan Google</span>
            </button>
          </div>
        <?php endif; ?>

        <!-- Footer kecil -->
        <?php
        $versionFile = dirname(dirname(dirname(__DIR__))) . '/version.json';
        $versionData = file_exists($versionFile) ? json_decode(file_get_contents($versionFile), true) : [];
        $appVersion = $versionData['version'] ?? '1.0.0';
        ?>
        <div class="text-center text-xs text-slate-500">
          &copy; <?= date('Y'); ?> <?= $namaAplikasi; ?> v<?= $appVersion; ?> • Semua hak dilindungi
        </div>
      </div>
    </div>
  </main>

  <script>
    // Icon init
    if (typeof lucide !== 'undefined') { lucide.createIcons(); }

    // Toggle show/hide password
    function togglePassword() {
      const pwd = document.getElementById('password');
      const eyeOpen = document.getElementById('eye-open');
      const eyeClosed = document.getElementById('eye-closed');
      const nowText = pwd.getAttribute('type') === 'password' ? 'text' : 'password';
      pwd.setAttribute('type', nowText);
      eyeOpen.classList.toggle('hidden');
      eyeClosed.classList.toggle('hidden');
    }

    // Caps Lock hint
    const pwdInput = document.getElementById('password');
    const capsHint = document.getElementById('caps-hint');
    ['keydown', 'keyup'].forEach(evt => {
      pwdInput.addEventListener(evt, (e) => {
        const caps = e.getModifierState && e.getModifierState('CapsLock');
        capsHint.style.display = caps ? 'block' : 'none';
      });
    });

    // Loading state on submit
    const form = document.getElementById('loginForm');
    const submitBtn = document.getElementById('submitBtn');
    form.addEventListener('submit', function () {
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i data-lucide="loader-2" class="w-5 h-5 animate-spin"></i><span>Memproses...</span>';
      if (typeof lucide !== 'undefined') { lucide.createIcons(); }
    });

    // Login dengan Google - kirim semester yang dipilih
    function loginWithGoogle() {
      const semesterSelect = document.getElementById('id_semester');
      const selectedSemester = semesterSelect.value;

      if (!selectedSemester) {
        alert('Silakan pilih semester terlebih dahulu');
        semesterSelect.focus();
        return;
      }

      // Redirect ke Google login dengan parameter semester
      window.location.href = '<?= BASEURL; ?>/auth/googleLogin?semester=' + selectedSemester;
    }
  </script>
</body>

</html>