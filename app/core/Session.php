<?php
// File: app/core/Session.php
// v1.25.0 - Helper terpusat untuk akses session.
//
// Konsolidasi kunci session duplikat:
//   - Kunci KANONIK : 'role', 'nama_lengkap'
//   - Kunci LEGACY  : 'user_role', 'user_nama_lengkap' (kompatibilitas mundur)
//
// Session::normalize() dipanggil sekali di front controller (public/index.php
// dan api/index.php) untuk menyinkronkan kedua pasangan kunci sehingga tidak
// ada lagi komponen yang membaca kunci yang belum terisi.

class Session
{
    /** Kunci kanonik => kunci legacy */
    private const ALIAS = [
        'role' => 'user_role',
        'nama_lengkap' => 'user_nama_lengkap',
    ];

    /**
     * Sinkronkan kunci kanonik dan legacy.
     * - Jika kunci legacy ada tapi kanonik tidak  -> kanonik diisi dari legacy.
     * - Jika kunci kanonik ada tapi legacy tidak  -> legacy diisi dari kanonik.
     * Dengan begitu session lama (hanya legacy) dan session baru sama-sama aman.
     */
    public static function normalize()
    {
        foreach (self::ALIAS as $canon => $legacy) {
            if (!isset($_SESSION[$canon]) && isset($_SESSION[$legacy])) {
                $_SESSION[$canon] = $_SESSION[$legacy];
            }
            if (isset($_SESSION[$canon]) && !isset($_SESSION[$legacy])) {
                $_SESSION[$legacy] = $_SESSION[$canon];
            }
        }
    }

    /**
     * Role pengguna yang sedang login ('admin', 'guru', 'siswa',
     * 'kepala_madrasah', 'wali_kelas') atau null bila belum login.
     */
    public static function role()
    {
        return $_SESSION['role'] ?? $_SESSION['user_role'] ?? null;
    }

    /**
     * Nama lengkap pengguna yang sedang login atau null bila belum login.
     */
    public static function namaLengkap()
    {
        return $_SESSION['nama_lengkap'] ?? $_SESSION['user_nama_lengkap'] ?? null;
    }

    /**
     * Cek apakah pengguna sudah login.
     */
    public static function isLoggedIn()
    {
        return !empty($_SESSION['user_id']);
    }
}
