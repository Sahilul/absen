<?php
// File: app/views/admin/kartu_ujian/verifikasi.php
// v1.37.0 - Halaman publik verifikasi QR Kartu Ujian
$e = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
$valid = !empty($data['valid']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Verifikasi Kartu Ujian</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, -apple-system, 'Segoe UI', sans-serif; background: #f1f5f9; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 16px; }
        .card { background: #fff; border-radius: 16px; box-shadow: 0 8px 30px rgba(0,0,0,.08); max-width: 420px; width: 100%; overflow: hidden; }
        .head { padding: 24px; text-align: center; color: #fff; background: <?= $valid ? '#16a34a' : '#dc2626'; ?>; }
        .head .ic { font-size: 42px; line-height: 1; }
        .head h1 { font-size: 18px; margin-top: 8px; }
        .body { padding: 20px 24px; }
        .row { display: flex; justify-content: space-between; gap: 12px; padding: 10px 0; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
        .row:last-child { border-bottom: 0; }
        .row span:first-child { color: #64748b; }
        .row span:last-child { font-weight: 600; color: #0f172a; text-align: right; }
        .msg { color: #475569; font-size: 14px; text-align: center; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="card">
        <div class="head">
            <div class="ic"><?= $valid ? '&#10004;' : '&#10006;'; ?></div>
            <h1><?= $valid ? 'Kartu Ujian Valid' : 'Kartu Ujian Tidak Valid'; ?></h1>
        </div>
        <div class="body">
            <?php if ($valid): $s = $data['siswa']; $u = $data['ujian']; $l = $data['lembaga']; ?>
                <div class="row"><span>Ujian</span><span><?= $e($u['nama_ujian']); ?></span></div>
                <div class="row"><span>Nama</span><span><?= $e($s['nama_siswa']); ?></span></div>
                <div class="row"><span>NISN</span><span><?= $e($s['nisn'] ?: '-'); ?></span></div>
                <div class="row"><span>Kelas</span><span><?= $e($s['nama_kelas']); ?></span></div>
                <?php if ($l): ?>
                    <div class="row"><span>Lembaga</span><span><?= $e($l['nama_lembaga']); ?></span></div>
                <?php endif; ?>
                <div class="row"><span>Tahun Pelajaran</span><span><?= $e(trim(($u['nama_tp'] ?? '') . ' ' . ($u['semester'] ?? ''))); ?></span></div>
            <?php else: ?>
                <p class="msg">Kode QR tidak dikenali atau data kartu sudah tidak berlaku. Silakan hubungi panitia ujian.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
