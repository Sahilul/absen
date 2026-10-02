<?php

class FotoController extends Controller
{
    /**
     * Serve foto siswa via local streaming proxy.
     * Prevents SSL certificate issues and ISP blocks on direct r2.dev domains.
     * Caches images locally in /tmp/foto_cache/ for fast delivery.
     *
     * URL: /foto/siswa/{id_or_nisn}
     */
    public function siswa($idOrNisn = null)
    {
        if (empty($idOrNisn)) {
            $this->servePlaceholder();
            return;
        }

        // Query by id_siswa or nisn
        $siswaModel = $this->model('Siswa_model');
        $siswa = is_numeric($idOrNisn) ? $siswaModel->getSiswaById($idOrNisn) : null;
        if (!$siswa) {
            $siswa = $siswaModel->getSiswaByNisn($idOrNisn);
        }

        if (!$siswa || empty($siswa['foto'])) {
            $this->servePlaceholder();
            return;
        }

        $fotoUrl = $siswa['foto'];

        // If stored as local relative path
        if (strpos($fotoUrl, 'http://') !== 0 && strpos($fotoUrl, 'https://') !== 0) {
            $localPath = APPROOT . '/' . ltrim($fotoUrl, '/');
            if (file_exists($localPath)) {
                $this->outputImage(file_get_contents($localPath), 'image/jpeg');
                return;
            }
        }

        // Cache setup
        $cacheDir = APPROOT . '/tmp/foto_cache';
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }

        $cacheKey = md5($fotoUrl);
        $cacheFile = $cacheDir . '/' . $cacheKey . '.jpg';

        // Serve from cache if fresh (7 days)
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < 86400 * 7)) {
            $this->outputImageFile($cacheFile);
            return;
        }

        // Fetch from R2 using cURL
        $ch = curl_init($fotoUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_USERAGENT => 'SabilillahApp/1.0',
        ]);
        $imageData = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE) ?: 'image/jpeg';
        curl_close($ch);

        if ($httpCode === 200 && !empty($imageData)) {
            @file_put_contents($cacheFile, $imageData);
            $this->outputImage($imageData, $contentType);
            return;
        }

        // If fetch failed but we have stale cache, serve it
        if (file_exists($cacheFile)) {
            $this->outputImageFile($cacheFile);
            return;
        }

        $this->servePlaceholder();
    }

    /**
     * Serve gambar soal STS via local streaming proxy.
     * URL: /foto/soal/{base64url_object_key}
     * Mencegah blokir ISP terhadap domain r2.dev langsung.
     */
    public function soal($token = null)
    {
        if (empty($token)) {
            $this->servePlaceholder();
            return;
        }

        // Decode base64url → object key
        $b64 = strtr($token, '-_', '+/');
        $pad = strlen($b64) % 4;
        if ($pad) {
            $b64 .= str_repeat('=', 4 - $pad);
        }
        $key = base64_decode($b64, true);

        if ($key === false || $key === '') {
            $this->servePlaceholder();
            return;
        }

        // Hanya izinkan prefix soal-sts/ dan cegah path traversal
        if (strpos($key, 'soal-sts/') !== 0 || strpos($key, '..') !== false) {
            $this->servePlaceholder();
            return;
        }

        if (!defined('R2_PUBLIC_URL') || R2_PUBLIC_URL === '') {
            $this->servePlaceholder();
            return;
        }

        $url = rtrim(R2_PUBLIC_URL, '/') . '/' . $key;
        $this->serveR2Image($url);
    }

    /**
     * Fetch gambar dari R2 dengan cache lokal, lalu kirim ke client.
     */
    private function serveR2Image($url)
    {
        $cacheDir = APPROOT . '/tmp/foto_cache';
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }

        $cacheKey = md5($url);
        $cacheFile = $cacheDir . '/' . $cacheKey . '.img';

        // Serve dari cache jika masih segar (7 hari)
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < 86400 * 7)) {
            $this->outputImageFile($cacheFile);
            return;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_USERAGENT => 'SabilillahApp/1.0',
        ]);
        $imageData = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE) ?: 'image/jpeg';
        curl_close($ch);

        if ($httpCode === 200 && !empty($imageData)) {
            @file_put_contents($cacheFile, $imageData);
            $this->outputImage($imageData, $contentType);
            return;
        }

        // Jika fetch gagal tapi ada cache lama, pakai itu
        if (file_exists($cacheFile)) {
            $this->outputImageFile($cacheFile);
            return;
        }

        $this->servePlaceholder();
    }

    private function outputImage($data, $contentType = 'image/jpeg')
    {
        $etag = '"' . md5($data) . '"';
        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) === $etag) {
            header('HTTP/1.1 304 Not Modified');
            exit;
        }

        header('Content-Type: ' . $contentType);
        header('Content-Length: ' . strlen($data));
        header('Cache-Control: public, max-age=604800, immutable');
        header('ETag: ' . $etag);
        echo $data;
        exit;
    }

    private function outputImageFile($filePath)
    {
        $etag = '"' . md5_file($filePath) . '"';
        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) === $etag) {
            header('HTTP/1.1 304 Not Modified');
            exit;
        }

        $contentType = 'image/jpeg';
        if (function_exists('getimagesize')) {
            $info = @getimagesize($filePath);
            if ($info && !empty($info['mime'])) {
                $contentType = $info['mime'];
            }
        }

        header('Content-Type: ' . $contentType);
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: public, max-age=604800, immutable');
        header('ETag: ' . $etag);
        readfile($filePath);
        exit;
    }

    private function servePlaceholder()
    {
        header('HTTP/1.0 404 Not Found');
        header('Content-Type: image/svg+xml');
        echo '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 1 0-16 0"/></svg>';
        exit;
    }
}
