<?php
// File: app/core/R2Storage.php
// Cloudflare R2 storage via S3-compatible API (pure PHP, no SDK)

class R2Storage
{
    private $accountId;
    private $accessKeyId;
    private $secretAccessKey;
    private $bucket;
    private $publicUrl;
    private $endpoint;
    private $region = 'auto';

    public function __construct()
    {
        $this->accountId = R2_ACCOUNT_ID;
        $this->accessKeyId = R2_ACCESS_KEY_ID;
        $this->secretAccessKey = R2_SECRET_ACCESS_KEY;
        $this->bucket = R2_BUCKET;
        $this->publicUrl = rtrim(R2_PUBLIC_URL, '/');
        $this->endpoint = "https://{$this->accountId}.r2.cloudflarestorage.com";
    }

    public function upload($fileContent, $key, $contentType = 'image/jpeg')
    {
        $url = "{$this->endpoint}/{$this->bucket}/{$key}";
        $headers = $this->signRequest('PUT', "/{$this->bucket}/{$key}", $contentType, $fileContent);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS => $fileContent,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'url' => $this->publicUrl . '/' . $key,
                'key' => $key,
            ];
        }

        return [
            'success' => false,
            'error' => $error ?: "HTTP {$httpCode}: {$response}",
        ];
    }

    public function uploadFile($filePath, $key, $contentType = null)
    {
        if (!file_exists($filePath)) {
            return ['success' => false, 'error' => 'File not found'];
        }

        if (!$contentType) {
            $contentType = mime_content_type($filePath) ?: 'application/octet-stream';
        }

        $fileContent = file_get_contents($filePath);
        return $this->upload($fileContent, $key, $contentType);
    }

    public function uploadBase64($base64Data, $key)
    {
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $matches)) {
            $ext = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
            $base64Data = preg_replace('/^data:image\/\w+;base64,/', '', $base64Data);
            $contentType = "image/{$matches[1]}";
        } else {
            $contentType = 'image/jpeg';
        }

        $fileContent = base64_decode($base64Data);
        if ($fileContent === false) {
            return ['success' => false, 'error' => 'Invalid base64 data'];
        }

        return $this->upload($fileContent, $key, $contentType);
    }

    public function delete($key)
    {
        $url = "{$this->endpoint}/{$this->bucket}/{$key}";
        $headers = $this->signRequest('DELETE', "/{$this->bucket}/{$key}");

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode >= 200 && $httpCode < 300;
    }

    public function getPublicUrl($key)
    {
        return $this->publicUrl . '/' . $key;
    }

    // --- AWS Signature V4 ---

    private function signRequest($method, $uri, $contentType = '', $payload = '')
    {
        $service = 's3';
        $timestamp = gmdate('Ymd\THis\Z');
        $datestamp = gmdate('Ymd');
        $payloadHash = hash('sha256', $payload);

        $canonicalHeaders = "content-type:{$contentType}\nhost:{$this->accountId}.r2.cloudflarestorage.com\nx-amz-content-sha256:{$payloadHash}\nx-amz-date:{$timestamp}\n";
        $signedHeaders = 'content-type;host;x-amz-content-sha256;x-amz-date';

        $canonicalRequest = implode("\n", [
            $method,
            $uri,
            '',
            $canonicalHeaders,
            $signedHeaders,
            $payloadHash,
        ]);

        $credentialScope = "{$datestamp}/{$this->region}/{$service}/aws4_request";
        $stringToSign = implode("\n", [
            'AWS4-HMAC-SHA256',
            $timestamp,
            $credentialScope,
            hash('sha256', $canonicalRequest),
        ]);

        $signingKey = $this->getSignatureKey($datestamp, $this->region, $service);
        $signature = hash_hmac('sha256', $stringToSign, $signingKey);

        $authorization = "AWS4-HMAC-SHA256 Credential={$this->accessKeyId}/{$credentialScope}, SignedHeaders={$signedHeaders}, Signature={$signature}";

        $headers = [
            "Authorization: {$authorization}",
            "x-amz-date: {$timestamp}",
            "x-amz-content-sha256: {$payloadHash}",
        ];

        if ($contentType) {
            $headers[] = "Content-Type: {$contentType}";
        }

        return $headers;
    }

    private function getSignatureKey($datestamp, $region, $service)
    {
        $kDate = hash_hmac('sha256', $datestamp, "AWS4{$this->secretAccessKey}", true);
        $kRegion = hash_hmac('sha256', $region, $kDate, true);
        $kService = hash_hmac('sha256', $service, $kRegion, true);
        return hash_hmac('sha256', 'aws4_request', $kService, true);
    }

    // --- Helper: resize + compress image ---

    public static function processImage($source, $maxWidth = 300, $maxHeight = 400, $quality = 85, $cropX = null, $cropY = null, $cropW = null, $cropH = null)
    {
        $info = null;
        $img = null;

        if (is_string($source) && file_exists($source)) {
            $info = getimagesize($source);
            $img = self::createImageFromFile($source, $info[2]);
        } elseif (is_string($source)) {
            $decoded = $source;
            if (preg_match('/^data:image\/\w+;base64,/', $source)) {
                $decoded = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $source));
            } elseif (base64_decode($source, true) !== false) {
                $decoded = base64_decode($source);
            }
            $img = imagecreatefromstring($decoded);
            if ($img) {
                $info = [imagesx($img), imagesy($img)];
            }
        }

        if (!$img) {
            return false;
        }

        $origW = $info[0] ?? imagesx($img);
        $origH = $info[1] ?? imagesy($img);

        // If crop coordinates provided, crop first
        if ($cropX !== null && $cropY !== null && $cropW !== null && $cropH !== null) {
            $cx = max(0, min((int)$cropX, $origW - 1));
            $cy = max(0, min((int)$cropY, $origH - 1));
            $cw = max(1, min((int)$cropW, $origW - $cx));
            $ch = max(1, min((int)$cropH, $origH - $cy));
            $cropped = imagecreatetruecolor($cw, $ch);
            imagecopyresampled($cropped, $img, 0, 0, $cx, $cy, $cw, $ch, $cw, $ch);
            imagedestroy($img);
            $img = $cropped;
            $origW = $cw;
            $origH = $ch;
        } else {
            // Auto-crop to 3:4 center
            $targetRatio = 3 / 4;
            $currentRatio = $origW / $origH;
            if ($currentRatio > $targetRatio) {
                $newW = (int)round($origH * $targetRatio);
                $cx = (int)round(($origW - $newW) / 2);
                $cropped = imagecreatetruecolor($newW, $origH);
                imagecopyresampled($cropped, $img, 0, 0, $cx, 0, $newW, $origH, $newW, $origH);
                imagedestroy($img);
                $img = $cropped;
                $origW = $newW;
            } elseif ($currentRatio < $targetRatio) {
                $newH = (int)round($origW / $targetRatio);
                $cy = (int)round(($origH - $newH) / 2);
                $cropped = imagecreatetruecolor($origW, $newH);
                imagecopyresampled($cropped, $img, 0, 0, 0, $cy, $origW, $newH, $origW, $newH);
                imagedestroy($img);
                $img = $cropped;
                $origH = $newH;
            }
        }

        // Resize to fit within maxWidth x maxHeight
        $ratio = min($maxWidth / $origW, $maxHeight / $origH, 1);
        $newW = (int) round($origW * $ratio);
        $newH = (int) round($origH * $ratio);

        $resized = imagecreatetruecolor($newW, $newH);
        imagecopyresampled($resized, $img, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
        imagedestroy($img);

        ob_start();
        imagejpeg($resized, null, $quality);
        $jpegData = ob_get_clean();
        imagedestroy($resized);

        return $jpegData;
    }

    private static function createImageFromFile($path, $type)
    {
        switch ($type) {
            case IMAGETYPE_JPEG: return imagecreatefromjpeg($path);
            case IMAGETYPE_PNG:  return imagecreatefrompng($path);
            case IMAGETYPE_GIF:  return imagecreatefromgif($path);
            case IMAGETYPE_WEBP: return imagecreatefromwebp($path);
            default: return imagecreatefromstring(file_get_contents($path));
        }
    }

    public static function isConfigured()
    {
        return defined('R2_ACCOUNT_ID')
            && R2_ACCOUNT_ID !== ''
            && defined('R2_ACCESS_KEY_ID')
            && R2_ACCESS_KEY_ID !== '';
    }
}
