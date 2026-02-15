<?php

class NewsController extends Controller
{
    public function index()
    {
        $model = $this->model('Cms_model');

        $data['judul'] = 'Berita & Artikel';
        $data['settings'] = $model->getAllSettings();

        // Merge with Pengaturan Aplikasi (for Logo, etc)
        $appSettings = $this->model('PengaturanAplikasi_model')->getPengaturan();
        if ($appSettings) {
            $data['settings'] = array_merge($data['settings'], $appSettings);
        }

        $data['menus'] = $model->getActiveMenuTree();
        $data['posts'] = $model->getPublishedPosts('news', 20);

        $this->view('news/index', $data);
    }

    public function detail($slug)
    {
        $model = $this->model('Cms_model');
        $post = $model->getPostBySlug($slug);

        if (!$post) {
            header('Location: ' . BASEURL . '/news');
            exit;
        }

        // Increment view count
        $model->incrementViewCount($post['id']);

        $data['judul'] = $post['title'];
        $data['post'] = $post;
        $data['settings'] = $model->getAllSettings();
        $data['menus'] = $model->getActiveMenuTree();

        // Merge with Pengaturan Aplikasi (for Logo, etc)
        $appSettings = $this->model('PengaturanAplikasi_model')->getPengaturan();
        if ($appSettings) {
            $data['settings'] = array_merge($data['settings'], $appSettings);
        }

        $data['related'] = $model->getPublishedPosts('news', 3);

        $this->view('news/detail', $data);
    }
}
