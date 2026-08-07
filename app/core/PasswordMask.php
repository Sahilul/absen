<?php

/**
 * File: app/core/PasswordMask.php
 * v1.26.0 - Helper masking password_plain untuk tampilan non-kartu.
 *
 * Latar belakang:
 * Kolom password_plain masih dibutuhkan untuk fitur cetak kartu login
 * (cetak_kartu_login.php / cetak_kartu_login_siswa.php). Namun menampilkannya
 * apa adanya di daftar admin / wali kelas membuka kredensial ke HTML sumber
 * dan ke orang di sekitar layar. Helper ini menyediakan masking konservatif:
 * kolom tetap ada, kartu tetap berfungsi, tetapi tampilan list/detail ditutup.
 *
 * Catatan: helper ini HANYA untuk tampilan. Ia tidak mengubah data di database
 * dan tidak boleh dipakai pada alur cetak kartu login.
 */
class PasswordMask
{
    /**
     * Masker string password untuk ditampilkan.
     * - Input kosong  -> string kosong (penanda "belum diset" ditangani pemanggil).
     * - Input terisi  -> serangkaian bullet, panjang tetap (tidak membocorkan panjang).
     *
     * @param string|null $plain Nilai password_plain mentah.
     * @param int $dots Jumlah bullet yang ditampilkan.
     * @return string
     */
    public static function mask($plain, $dots = 8)
    {
        if ($plain === null || $plain === '') {
            return '';
        }
        return str_repeat('•', max(4, (int) $dots));
    }

    /**
     * Apakah sebuah nilai password_plain layak dianggap "terisi"?
     * Dipakai agar penanda status akun (Aktif/Pending) tetap konsisten.
     *
     * @param mixed $plain
     * @return bool
     */
    public static function has($plain)
    {
        return is_string($plain) && trim($plain) !== '';
    }

    /**
     * Masker kolom password_plain pada sekumpulan baris data (list view).
     * Nilai diganti bullet sehingga plaintext tidak bocor ke HTML sumber
     * (termasuk ke atribut onclick json_encode pada tombol detail modal).
     *
     * @param mixed $rows Daftar baris asosiatif. Jika bukan array, dikembalikan apa adanya.
     * @param string $key Nama kolom yang dimasker.
     * @return mixed Baris dengan kolom ter-masker (jika ada).
     */
    public static function maskRows($rows, $key = 'password_plain')
    {
        if (!is_array($rows)) {
            return $rows;
        }
        foreach ($rows as $i => $row) {
            if (is_array($row) && array_key_exists($key, $row)) {
                $rows[$i][$key] = self::mask($row[$key]);
            }
        }
        return $rows;
    }
}
