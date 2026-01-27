<?php

class HomeController extends Controller
{
    public function index()
    {
        $model = $this->model('Cms_model');

        $data['settings'] = $model->getAllSettings();

        // Merge with Pengaturan Aplikasi (for Logo, etc)
        $appSettings = $this->model('PengaturanAplikasi_model')->getPengaturan();
        if ($appSettings) {
            $data['settings'] = array_merge($data['settings'], $appSettings);
        }

        $data['judul'] = $data['settings']['school_name'] ?? 'Smart Absensi';
        // Get Active Sliders & Popups
        $data['sliders'] = $model->getActiveSliders();
        $data['popups'] = $model->getActivePopups();
        $data['menus'] = $model->getActiveMenuTree();
        // Get Published Posts (News & Announcements)
        $data['posts'] = $model->getPublishedPostsMultiType(['news', 'announcement'], 6);
        // Get Active Institutions with Student Count
        $data['institutions'] = $model->getActiveInstitutionsWithCount();

        // Visitor Counter - Record visit and get stats
        $visitorModel = $this->model('Visitor_model');
        $visitorModel->recordVisit('/');
        $data['visitor_stats'] = $visitorModel->getVisitorStats();

        $this->view('home/index', $data);
    }
}
