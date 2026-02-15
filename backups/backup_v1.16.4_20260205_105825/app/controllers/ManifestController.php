<?php

class ManifestController extends Controller
{
    public function index()
    {
        // Set Header JSON
        header('Content-Type: application/manifest+json; charset=utf-8');

        // Get Global Settings (avail from config.php)
        $settings = getPengaturanAplikasi();

        $appName = $settings['nama_aplikasi'] ?? 'Smart Absensi';
        $logo = $settings['logo'] ?? 'logo_default.png'; // Fallback if DB empty

        // Ensure logo path is correct
        $logoUrl = BASEURL . '/public/img/app/' . $logo;

        // Construct Manifest Array
        $manifest = [
            "short_name" => substr($appName, 0, 12), // Short name limited chars
            "name" => $appName,
            "icons" => [
                [
                    "src" => $logoUrl,
                    "type" => "image/png",
                    "sizes" => "192x192"
                ],
                [
                    "src" => $logoUrl,
                    "type" => "image/png",
                    "sizes" => "512x512"
                ]
            ],
            "start_url" => BASEURL . "/",
            "background_color" => "#ffffff",
            "display" => "standalone",
            "scope" => BASEURL . "/",
            "theme_color" => "#16a34a",
            "description" => "Aplikasi Absensi dan Akademik Sekolah"
        ];

        echo json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
