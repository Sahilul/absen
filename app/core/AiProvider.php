<?php
/**
 * File: app/core/AiProvider.php
 * Client untuk OpenAI-compatible API (endpoint /chat/completions).
 * Mendukung: OpenAI, OpenRouter, Groq, Ollama, LM Studio, vLLM, dll.
 *
 * Pattern cURL mengikuti app/core/Fonnte.php
 */
class AiProvider
{
    private $baseUrl;
    private $apiKey;
    private $model;
    private $temperature;

    public function __construct()
    {
        $this->baseUrl     = rtrim(getSystemSetting('ai_chat_base_url', 'https://api.openai.com/v1'), '/');
        $this->apiKey      = getSystemSetting('ai_chat_api_key', '');
        $this->model       = getSystemSetting('ai_chat_model', 'gpt-4o-mini');
        $this->temperature = (float) getSystemSetting('ai_chat_temperature', '0.7');
    }

    /**
     * Kirim chat completion request ke OpenAI-compatible endpoint.
     * Mendukung multimodal: jika message punya key 'attachment', content jadi array of parts.
     *
     * @param array  $messages     Array of ['role' => 'user'|'assistant', 'content' => '...', 'attachment' => '...' (optional JSON)]
     * @param string $systemPrompt System prompt (di-inject sebagai message pertama)
     * @return array ['status' => bool, 'reply' => string, 'error' => string|null]
     */
    public function chat(array $messages, string $systemPrompt): array
    {
        if (empty($this->apiKey)) {
            return ['status' => false, 'reply' => '', 'error' => 'API key belum dikonfigurasi'];
        }

        if (empty($this->baseUrl)) {
            return ['status' => false, 'reply' => '', 'error' => 'Base URL belum dikonfigurasi'];
        }

        // Validasi base URL: hanya http/https
        $scheme = parse_url($this->baseUrl, PHP_URL_SCHEME);
        if (!in_array($scheme, ['http', 'https'])) {
            return ['status' => false, 'reply' => '', 'error' => 'Base URL harus menggunakan http atau https'];
        }

        // Susun messages array dengan system prompt di awal
        $apiMessages = [];
        $apiMessages[] = ['role' => 'system', 'content' => $systemPrompt];
        foreach ($messages as $msg) {
            $content = $this->buildMessageContent($msg);
            $apiMessages[] = [
                'role'    => $msg['role'],
                'content' => $content
            ];
        }

        $url = $this->baseUrl . '/chat/completions';

        $payload = json_encode([
            'model'       => $this->model,
            'messages'    => $apiMessages,
            'temperature' => $this->temperature,
        ]);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
            ],
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        // Ukur latency
        $startTime = microtime(true);
        $response = curl_exec($ch);
        $latencyMs = (int) round((microtime(true) - $startTime) * 1000);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // Base usage info yang selalu dikembalikan
        $usageInfo = [
            'model'             => $this->model,
            'latency_ms'        => $latencyMs,
            'prompt_tokens'     => 0,
            'completion_tokens'  => 0,
            'total_tokens'      => 0,
        ];

        // cURL error (network, timeout, dll)
        if ($response === false) {
            error_log("[AiProvider] cURL error: {$curlError} | URL: {$url}");
            return ['status' => false, 'reply' => '', 'error' => 'Gagal menghubungi server AI: ' . $curlError, 'usage' => $usageInfo];
        }

        $data = json_decode($response, true);

        // Tangkap usage dari respons API (OpenAI-compatible)
        if (isset($data['usage'])) {
            $usageInfo['prompt_tokens']     = (int) ($data['usage']['prompt_tokens'] ?? 0);
            $usageInfo['completion_tokens']  = (int) ($data['usage']['completion_tokens'] ?? 0);
            $usageInfo['total_tokens']      = (int) ($data['usage']['total_tokens'] ?? 0);
        }
        // Tangkap model aktual jika dikembalikan API
        if (isset($data['model'])) {
            $usageInfo['model'] = $data['model'];
        }

        // HTTP error
        if ($httpCode < 200 || $httpCode >= 300) {
            $errorMsg = $data['error']['message'] ?? ($data['message'] ?? "HTTP {$httpCode}");
            error_log("[AiProvider] API error: HTTP {$httpCode} | {$errorMsg} | URL: {$url}");
            return ['status' => false, 'reply' => '', 'error' => 'Error dari AI: ' . $errorMsg, 'usage' => $usageInfo];
        }

        // Parse response
        if (!isset($data['choices'][0]['message']['content'])) {
            error_log("[AiProvider] Unexpected response format | URL: {$url} | Response: " . substr($response, 0, 500));
            return ['status' => false, 'reply' => '', 'error' => 'Format respons AI tidak dikenali', 'usage' => $usageInfo];
        }

        $reply = trim($data['choices'][0]['message']['content']);
        return ['status' => true, 'reply' => $reply, 'error' => null, 'usage' => $usageInfo];
    }

    /**
     * Generate judul pendek untuk percakapan berdasarkan pesan awal.
     * Mirip ChatGPT/Claude yang auto-generate judul.
     *
     * @param string $userMessage  Pesan pertama user
     * @param string $aiReply      Balasan pertama AI
     * @return string Judul singkat (3-6 kata)
     */
    /**
     * Generate judul + kembalikan usage data.
     * @return array ['title' => string, 'usage' => array|null]
     */
    public function generateTitleWithUsage(string $userMessage, string $aiReply): array
    {
        $prompt = 'Buatkan judul singkat (3-6 kata, bahasa Indonesia) untuk percakapan berikut. '
                . 'Langsung tulis judulnya saja tanpa tanda kutip, tanpa penjelasan, tanpa emoji.';

        $messages = [
            ['role' => 'user', 'content' => mb_substr($userMessage, 0, 300)],
            ['role' => 'assistant', 'content' => mb_substr($aiReply, 0, 300)],
            ['role' => 'user', 'content' => 'Buatkan judul singkat untuk percakapan di atas.'],
        ];

        $result = $this->chat($messages, $prompt);

        if ($result['status'] && !empty($result['reply'])) {
            $title = trim($result['reply'], "\"'\n\r\t ");
            $title = preg_replace('/\s+/', ' ', $title);
            if (mb_strlen($title) > 60) {
                $title = mb_substr($title, 0, 57) . '…';
            }
            return ['title' => $title, 'usage' => $result['usage'] ?? null, 'status' => true, 'error' => null];
        }

        return [
            'title' => $this->fallbackTitle($userMessage),
            'usage' => $result['usage'] ?? null,
            'status' => $result['status'],
            'error' => $result['error'] ?? null,
        ];
    }

    /**
     * Backward-compatible wrapper (returns string only).
     */
    public function generateTitle(string $userMessage, string $aiReply): string
    {
        $result = $this->generateTitleWithUsage($userMessage, $aiReply);
        return $result['title'];
    }

    /**
     * Fallback judul jika AI gagal generate.
     */
    private function fallbackTitle(string $message): string
    {
        $judul = preg_replace('/\s+/', ' ', trim($message));
        if (mb_strlen($judul) > 50) {
            $judul = mb_substr($judul, 0, 50);
            $lastSpace = mb_strrpos($judul, ' ');
            if ($lastSpace !== false && $lastSpace > 20) {
                $judul = mb_substr($judul, 0, $lastSpace);
            }
            $judul .= '…';
        }
        return $judul ?: 'Percakapan Baru';
    }

    /**
     * Test koneksi ke endpoint AI dengan pesan sederhana.
     *
     * @return array ['status' => bool, 'message' => string]
     */
    public function testConnection(): array
    {
        $result = $this->chat(
            [['role' => 'user', 'content' => 'Halo, ini test koneksi. Balas singkat saja.']],
            'Kamu adalah asisten AI. Balas dengan singkat untuk mengkonfirmasi koneksi berhasil.'
        );

        if ($result['status']) {
            return ['status' => true, 'message' => 'Koneksi berhasil! Respons: ' . mb_substr($result['reply'], 0, 100)];
        }

        return ['status' => false, 'message' => 'Gagal: ' . $result['error']];
    }

    // =========================================
    // MULTIMODAL HELPERS
    // =========================================

    /**
     * Build content untuk satu message — string biasa atau array multimodal.
     *
     * @param array $msg Message dengan optional 'attachment' key (JSON string)
     * @return string|array
     */
    private function buildMessageContent(array $msg)
    {
        $text = $msg['content'] ?? '';
        $attachmentJson = $msg['attachment'] ?? null;

        if (empty($attachmentJson) || $msg['role'] !== 'user') {
            return $text;
        }

        $attachment = is_array($attachmentJson) ? $attachmentJson : json_decode($attachmentJson, true);
        if (!$attachment || empty($attachment['filename'])) {
            return $text;
        }

        $mime = $attachment['mime_type'] ?? '';
        $filePath = $attachment['file_path'] ?? '';

        // Jika file tidak ada di disk, kirim text saja
        if (!$filePath || !file_exists($filePath)) {
            return $text;
        }

        // Image & PDF: kirim sebagai base64 vision content (Gemini support natively)
        if ($this->isVisionMime($mime)) {
            return $this->buildVisionContent($text, $filePath, $mime);
        }

        // Document (Word, Excel, CSV, TXT): extract text, prepend ke content
        $extractedText = $this->extractTextFromFile($filePath, $mime);
        if ($extractedText) {
            $label = $attachment['original_name'] ?? basename($filePath);
            $prefix = "📎 [File: {$label}]\n---\n{$extractedText}\n---\n\n";
            return $prefix . $text;
        }

        return $text;
    }

    /**
     * Cek apakah MIME type bisa dikirim sebagai vision content.
     */
    private function isVisionMime(string $mime): bool
    {
        $visionMimes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            'application/pdf',
        ];
        return in_array($mime, $visionMimes);
    }

    /**
     * Build multimodal content array untuk vision (image/PDF).
     *
     * @return array Array of content parts
     */
    private function buildVisionContent(string $text, string $filePath, string $mime): array
    {
        $base64 = base64_encode(file_get_contents($filePath));
        $dataUrl = "data:{$mime};base64,{$base64}";

        $parts = [];

        // Text part (jika ada)
        if (!empty(trim($text))) {
            $parts[] = ['type' => 'text', 'text' => $text];
        }

        // Image/PDF part
        $parts[] = [
            'type' => 'image_url',
            'image_url' => ['url' => $dataUrl]
        ];

        return $parts;
    }

    /**
     * Extract text dari file dokumen (Word, Excel, CSV, TXT).
     * Menggunakan PHP native — tanpa library eksternal untuk reading.
     *
     * @param string $filePath Path absolut ke file
     * @param string $mime     MIME type
     * @return string|null Extracted text atau null jika gagal
     */
    public function extractTextFromFile(string $filePath, string $mime): ?string
    {
        try {
            switch ($mime) {
                case 'text/plain':
                case 'text/csv':
                    $content = file_get_contents($filePath);
                    // Batasi 100K karakter
                    return mb_substr($content, 0, 100000);

                case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document': // .docx
                    return $this->extractDocxText($filePath);

                case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': // .xlsx
                    return $this->extractXlsxText($filePath);

                case 'application/msword': // .doc (legacy)
                    return '[Format .doc lama tidak didukung. Gunakan .docx]';

                case 'application/vnd.ms-excel': // .xls (legacy)
                    return '[Format .xls lama tidak didukung. Gunakan .xlsx]';

                default:
                    return null;
            }
        } catch (\Exception $e) {
            error_log("[AiProvider] extractTextFromFile error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Extract text dari file DOCX menggunakan ZipArchive (native PHP).
     * DOCX = ZIP berisi XML files.
     */
    private function extractDocxText(string $filePath): ?string
    {
        if (!class_exists('ZipArchive')) {
            return '[ZipArchive tidak tersedia di server]';
        }

        $zip = new \ZipArchive();
        if ($zip->open($filePath) !== true) {
            return null;
        }

        $content = $zip->getFromName('word/document.xml');
        $zip->close();

        if (!$content) {
            return null;
        }

        // Parse XML, extract text dari <w:t> tags
        $xml = simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOERROR);
        if (!$xml) {
            // Fallback: strip XML tags
            $text = strip_tags($content);
            return mb_substr(trim($text), 0, 100000);
        }

        $xml->registerXPathNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
        $paragraphs = $xml->xpath('//w:p');

        $lines = [];
        foreach ($paragraphs as $p) {
            $texts = $p->xpath('.//w:t');
            $line = '';
            foreach ($texts as $t) {
                $line .= (string) $t;
            }
            if (trim($line) !== '') {
                $lines[] = trim($line);
            }
        }

        $result = implode("\n", $lines);
        return mb_substr($result, 0, 100000);
    }

    /**
     * Extract text dari file XLSX menggunakan ZipArchive (native PHP).
     * XLSX = ZIP berisi XML files. Baca shared strings + sheet data.
     */
    private function extractXlsxText(string $filePath): ?string
    {
        if (!class_exists('ZipArchive')) {
            return '[ZipArchive tidak tersedia di server]';
        }

        $zip = new \ZipArchive();
        if ($zip->open($filePath) !== true) {
            return null;
        }

        // Baca shared strings
        $sharedStrings = [];
        $ssXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($ssXml) {
            $ss = simplexml_load_string($ssXml, 'SimpleXMLElement', LIBXML_NOERROR);
            if ($ss) {
                $ss->registerXPathNamespace('s', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
                foreach ($ss->xpath('//s:si') as $si) {
                    $texts = $si->xpath('.//s:t');
                    $val = '';
                    foreach ($texts as $t) {
                        $val .= (string) $t;
                    }
                    $sharedStrings[] = $val;
                }
            }
        }

        // Baca sheet1
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if (!$sheetXml) {
            return !empty($sharedStrings) ? implode("\n", $sharedStrings) : null;
        }

        $sheet = simplexml_load_string($sheetXml, 'SimpleXMLElement', LIBXML_NOERROR);
        if (!$sheet) {
            return null;
        }

        $sheet->registerXPathNamespace('s', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $rows = $sheet->xpath('//s:row');

        $lines = [];
        $rowCount = 0;
        foreach ($rows as $row) {
            if ($rowCount++ > 500) break; // Batasi 500 baris
            $cells = $row->xpath('.//s:c');
            $rowData = [];
            foreach ($cells as $cell) {
                $value = (string) ($cell->v ?? '');
                $type = (string) ($cell['t'] ?? '');
                // Jika type 's' = shared string reference
                if ($type === 's' && isset($sharedStrings[(int) $value])) {
                    $value = $sharedStrings[(int) $value];
                }
                $rowData[] = $value;
            }
            $lines[] = implode("\t", $rowData);
        }

        $result = implode("\n", $lines);
        return mb_substr($result, 0, 100000);
    }
}
