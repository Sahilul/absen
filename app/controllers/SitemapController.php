<?php

class SitemapController extends Controller
{
    public function index()
    {
        // Set Header XML
        header("Content-Type: application/xml; charset=utf-8");

        // Load Model
        $cmsModel = $this->model('Cms_model');

        // Get Data
        $posts = $cmsModel->getAllPosts('news'); // Ambil berita
        $announcements = $cmsModel->getAllPosts('announcement'); // Ambil pengumuman
        $pages = $cmsModel->getAllPosts('page'); // Ambil halaman statis
        $settings = $cmsModel->getAllSettings(); // Untuk last mod home

        // Base URL
        $baseUrl = BASEURL; // constants already available
        if (substr($baseUrl, -1) !== '/') {
            $baseUrl .= '/';
        }

        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        // 1. Static Pages (Home, News, Contact, Login)
        $this->addUrl($baseUrl, date('c'), '1.0', 'daily');
        $this->addUrl($baseUrl . 'news', date('c'), '0.8', 'daily');
        $this->addUrl($baseUrl . 'contact', date('c'), '0.5', 'monthly');
        $this->addUrl($baseUrl . 'auth/login', date('c'), '0.5', 'monthly');

        // 2. Dynamic Posts (Berita)
        foreach ($posts as $post) {
            if ($post['is_published']) {
                $url = $baseUrl . 'news/detail/' . $post['slug'];
                $updated = date('c', strtotime($post['updated_at']));
                $this->addUrl($url, $updated, '0.8', 'weekly');
            }
        }

        // 3. Dynamic Announcements
        foreach ($announcements as $ann) {
            if ($ann['is_published']) {
                $url = $baseUrl . 'news/detail/' . $ann['slug'];
                $updated = date('c', strtotime($ann['updated_at']));
                $this->addUrl($url, $updated, '0.7', 'weekly');
            }
        }

        // 4. Custom Pages (Using CMS Pages if logic exists for different route, but usually news/detail handles all types)
        // If 'page' type also uses news/detail route:
        foreach ($pages as $page) {
            if ($page['is_published']) {
                $url = $baseUrl . 'news/detail/' . $page['slug'];
                $updated = date('c', strtotime($page['updated_at']));
                $this->addUrl($url, $updated, '0.6', 'monthly');
            }
        }

        echo '</urlset>';
    }

    private function addUrl($url, $lastMod, $priority, $changeFreq)
    {
        echo '<url>';
        echo '<loc>' . htmlspecialchars($url) . '</loc>';
        echo '<lastmod>' . $lastMod . '</lastmod>';
        echo '<priority>' . $priority . '</priority>';
        echo '<changefreq>' . $changeFreq . '</changefreq>';
        echo '</url>';
    }
}
