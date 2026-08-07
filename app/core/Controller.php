<?php

// File: app/core/Controller.php
// Ini adalah kelas Controller dasar.

class Controller {

    /**
     * v1.24.0 - Flag agar snippet CSRF hanya diinjeksi sekali per response.
     */
    private static $csrfInjected = false;

    /**
     * Method untuk memuat dan menampilkan file view.
     */
    public function view($view, $data = [])
    {
        // PERBAIKAN: Menggunakan APPROOT untuk path absolut yang pasti benar
        $path = APPROOT . '/app/views/' . $view . '.php';
        if (file_exists($path)) {
            // v1.24.0 - Buffer output view agar bisa injeksi snippet CSRF ke <head>
            ob_start();
            require_once $path;
            $html = ob_get_clean();

            if (!self::$csrfInjected && class_exists('Csrf') && stripos($html, '<head') !== false) {
                $injected = preg_replace('/<head\b[^>]*>/i', '$0' . "\n" . Csrf::headSnippet(), $html, 1, $count);
                if ($count > 0) {
                    $html = $injected;
                    self::$csrfInjected = true;
                }
            }

            echo $html;
        } else {
            die('View tidak ditemukan: ' . $view);
        }
    }

    /**
     * Method untuk memuat file model.
     */
    public function model($model)
    {
        // PERBAIKAN: Menggunakan APPROOT untuk path absolut
        if (file_exists(APPROOT . '/app/models/' . $model . '.php')) {
            require_once APPROOT . '/app/models/' . $model . '.php';
            return new $model();
        } else {
            die('Model tidak ditemukan: ' . $model);
        }
    }
}