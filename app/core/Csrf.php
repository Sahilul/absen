<?php

/**
 * File: app/core/Csrf.php
 * v1.24.0 - CSRF Protection Helper
 *
 * Menyediakan token CSRF per-session, validasi, serta snippet untuk injeksi
 * otomatis (meta + JS) ke dalam <head> halaman. Token disimpan di session agar
 * konsisten di semua tab. JS yang diinjeksi menangani:
 *   - Form statis & dinamis (dibuat lewat document.createElement)
 *   - fetch() dan XMLHttpRequest (header X-CSRF-Token)
 */
class Csrf
{
    const SESSION_KEY = 'csrf_token';
    const FIELD_NAME  = 'csrf_token';
    const HEADER      = 'X-CSRF-Token';
    const META_NAME   = 'csrf-token';

    /**
     * Ambil (atau buat) token CSRF untuk session aktif.
     */
    public static function token()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return '';
        }
        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::SESSION_KEY];
    }

    /**
     * Hidden input untuk disisipkan manual di dalam <form method="post">.
     */
    public static function field()
    {
        $t = htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8');
        return '<input type="hidden" name="' . self::FIELD_NAME . '" value="' . $t . '">';
    }

    /**
     * Meta tag untuk dibaca JavaScript.
     */
    public static function meta()
    {
        $t = htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8');
        return '<meta name="' . self::META_NAME . '" content="' . $t . '">';
    }

    /**
     * Ambil token yang dikirim request (POST field atau header).
     */
    private static function submittedToken()
    {
        if (!empty($_POST[self::FIELD_NAME])) {
            return (string) $_POST[self::FIELD_NAME];
        }
        $headerKey = 'HTTP_X_CSRF_TOKEN';
        if (!empty($_SERVER[$headerKey])) {
            return (string) $_SERVER[$headerKey];
        }
        return '';
    }

    /**
     * Validasi token pada request berjalan.
     */
    public static function validate()
    {
        $expected = self::token();
        $given = self::submittedToken();
        if ($expected === '' || $given === '') {
            return false;
        }
        return hash_equals($expected, $given);
    }

    /**
     * Deteksi apakah request berjalan adalah request AJAX/JSON.
     * Dipakai App.php untuk menentukan bentuk respons saat validasi CSRF gagal.
     */
    public static function isAjax()
    {
        $with = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
        if (strcasecmp($with, 'XMLHttpRequest') === 0) {
            return true;
        }
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        if (stripos($accept, 'application/json') !== false) {
            return true;
        }
        $ct = $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($ct, 'application/json') !== false) {
            return true;
        }
        return false;
    }

    /**
     * Snippet yang diinjeksi tepat setelah tag <head>.
     * Berisi meta token + JS proteksi (idempotent lewat guard window.__ABSEN_CSRF__).
     */
    public static function headSnippet()
    {
        $t = htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8');
        $meta = '<meta name="' . self::META_NAME . '" content="' . $t . '">';

        $js = <<<'JS'
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
JS;

        return $meta . "\n" . $js;
    }
}
