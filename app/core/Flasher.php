<?php

// File: app/core/Flasher.php
class Flasher {

    /**
     * setFlash dapat dipanggil dengan 2 atau 3 argumen:
     * - setFlash(pesan, tipe)
     * - setFlash(judul, pesan, tipe)
     */
    public static function setFlash(...$args)
    {
        $judul = null; $pesan = null; $tipe = null;
        if (count($args) >= 3) {
            [$judul, $pesan, $tipe] = $args;
        } elseif (count($args) === 2) {
            [$pesan, $tipe] = $args;
        } else {
            return;
        }

        $_SESSION['flash'] = [
            'judul' => $judul,
            'pesan' => $pesan,
            'tipe'  => $tipe
        ];
    }

    public static function flash()
    {
        if (isset($_SESSION['flash'])) {
            // Dinamis berdasarkan tipe
            $tipe = $_SESSION['flash']['tipe'] ?? 'info';
            
            switch ($tipe) {
                case 'success':
                    $tipeAlert = 'green';
                    $defaultJudul = 'Berhasil!';
                    $iconLucide = 'check-circle';
                    break;
                case 'danger':
                case 'error':
                    $tipeAlert = 'red';
                    $defaultJudul = 'Gagal!';
                    $iconLucide = 'x-circle';
                    break;
                case 'warning':
                    $tipeAlert = 'yellow';
                    $defaultJudul = 'Perhatian!';
                    $iconLucide = 'alert-triangle';
                    break;
                case 'info':
                default:
                    $tipeAlert = 'blue';
                    $defaultJudul = 'Informasi';
                    $iconLucide = 'info';
                    break;
            }
            
            $judul = $_SESSION['flash']['judul'] ?: $defaultJudul;
            
            echo '<div class="bg-' . $tipeAlert . '-100 border border-' . $tipeAlert . '-400 text-' . $tipeAlert . '-700 px-4 py-3 rounded-lg relative mb-4 flex items-start gap-3 animate-slide-up" role="alert">
                    <i data-lucide="' . $iconLucide . '" class="w-5 h-5 mt-0.5 flex-shrink-0"></i>
                    <div class="flex-1">
                        <strong class="font-bold block">' . htmlspecialchars($judul) . '</strong>
                        <span class="block">' . htmlspecialchars($_SESSION['flash']['pesan']) . '</span>
                    </div>
                  </div>';
            
            // Hapus session setelah ditampilkan
            unset($_SESSION['flash']);
        }
    }
}