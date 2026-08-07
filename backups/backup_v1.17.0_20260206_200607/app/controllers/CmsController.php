<?php

class CmsController extends Controller
{
    protected $data = [];
    private $isAdmin = false;
    private $isAdminCms = false;

    public function __construct()
    {
        // Cek Login
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASEURL . '/auth/login');
            exit;
        }

        // Cek akses: admin OR guru/wali_kelas dengan fungsi admin_cms
        $this->isAdmin = ($_SESSION['role'] ?? '') === 'admin';

        $role = $_SESSION['role'] ?? '';
        if (in_array($role, ['guru', 'wali_kelas']) && isset($_SESSION['id_ref'])) {
            require_once APPROOT . '/app/models/GuruFungsi_model.php';
            $guruFungsiModel = new GuruFungsi_model();
            $id_tp = $_SESSION['id_tp_aktif'] ?? 0;
            $this->isAdminCms = $guruFungsiModel->isAdminCMS($_SESSION['id_ref'], $id_tp);
        }

        if (!$this->isAdmin && !$this->isAdminCms) {
            Flasher::setFlash('Akses Ditolak', 'Anda tidak memiliki akses ke halaman CMS', 'error');
            header('Location: ' . BASEURL);
            exit;
        }

        // Load Cms_model
        $this->model('Cms_model');

        // Store role for sidebar selection
        $this->data['user_role'] = $role;

        // Use CMS Sidebar only for full admin, otherwise use role-specific sidebar
        if ($this->isAdmin) {
            $this->data['use_cms_sidebar'] = true;
            $this->data['is_cms_admin'] = true;
            $this->data['is_full_admin'] = true;
        } else {
            // admin_cms users use their normal sidebar (guru/walikelas)
            $this->data['use_cms_sidebar'] = false;
            $this->data['is_cms_admin'] = false;
            $this->data['is_full_admin'] = false;
        }
    }

    /**
     * Middleware: Hanya admin penuh yang bisa akses (bukan admin_cms)
     */
    private function requireFullAdmin()
    {
        if (!$this->isAdmin) {
            Flasher::setFlash('Akses Ditolak', 'Fitur ini hanya untuk Administrator', 'error');
            header('Location: ' . BASEURL . '/cms/posts');
            exit;
        }
    }

    public function index()
    {
        // Redirect berdasarkan role
        if ($this->isAdmin) {
            header('Location: ' . BASEURL . '/cms/identitas');
        } else {
            header('Location: ' . BASEURL . '/cms/posts');
        }
        exit;
    }

    /**
     * Halaman Pengaturan Identitas Website (Admin Only)
     */
    public function identitas()
    {
        $this->requireFullAdmin();

        $this->data['judul'] = 'Identitas Website';
        $this->data['settings'] = $this->model('Cms_model')->getAllSettings();

        $this->view('templates/header', $this->data);
        $this->view('admin/cms/identitas', $this->data);
        $this->view('templates/footer');
    }

    /**
     * Proses Simpan Identitas
     */
    public function simpanIdentitas()
    {
        $this->requireFullAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/cms/identitas');
            exit;
        }

        $data = [
            'yayasan_name' => $_POST['yayasan_name'] ?? '',
            'school_name' => $_POST['school_name'] ?? '',
            'welcome_title' => $_POST['welcome_title'] ?? '',
            'welcome_text' => $_POST['welcome_text'] ?? '',
            'address' => $_POST['address'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'email' => $_POST['email'] ?? '',
            'facebook' => $_POST['facebook'] ?? '',
            'instagram' => $_POST['instagram'] ?? '',
            'youtube' => $_POST['youtube'] ?? '',
            'maps_embed' => $_POST['maps_embed'] ?? '',
            'home_welcome_enable' => $_POST['home_welcome_enable'] ?? '0'
        ];

        // Handle File Upload (Welcome Image)
        if (isset($_FILES['welcome_image']) && $_FILES['welcome_image']['error'] === UPLOAD_ERR_OK) {
            $uploaded = $this->handleImageUpload($_FILES['welcome_image'], 'welcome');
            if ($uploaded) {
                $data['welcome_image'] = $uploaded;
            }
        }

        // Loop save
        $success = true;
        foreach ($data as $key => $val) {
            if (!$this->model('Cms_model')->updateSetting($key, $val)) {
                $success = false;
            }
        }

        if ($success) {
            Flasher::setFlash('Identitas website berhasil disimpan', 'success');
        } else {
            Flasher::setFlash('Gagal menyimpan beberapa pengaturan', 'warning');
        }

        header('Location: ' . BASEURL . '/cms/identitas');
        exit;
    }

    /**
     * Helper Upload Image
     */
    private function handleImageUpload($file, $prefix)
    {
        // __DIR__ = app/controllers, dirname(2x) = project root (absen)
        $targetDir = dirname(dirname(__DIR__)) . '/public/img/cms/';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($ext, $allowed))
            return false;

        $filename = $prefix . '_' . time() . '.' . $ext;
        $targetFile = $targetDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            return $filename;
        }
        return false;
    }

    // --- SLIDER MANAGEMENT ---

    public function sliders()
    {
        $this->requireFullAdmin();

        $this->data['judul'] = 'Slider Gambar';
        $this->data['sliders'] = $this->model('Cms_model')->getAllSliders();

        $this->view('templates/header', $this->data);
        $this->view('admin/cms/sliders', $this->data);
        $this->view('templates/footer');
    }

    public function tambahSlider()
    {
        $this->requireFullAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/cms/sliders');
            exit;
        }

        $imageName = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploaded = $this->handleImageUpload($_FILES['image'], 'slide');
            if ($uploaded) {
                $imageName = $uploaded;
            } else {
                Flasher::setFlash('Gagal upload gambar slider', 'danger');
                header('Location: ' . BASEURL . '/cms/sliders');
                exit;
            }
        } else {
            Flasher::setFlash('Gambar slider wajib diupload', 'danger');
            header('Location: ' . BASEURL . '/cms/sliders');
            exit;
        }

        $data = [
            'title' => $_POST['title'] ?? '',
            'description' => $_POST['description'] ?? '',
            'link_url' => $_POST['link_url'] ?? '',
            'button_text' => $_POST['button_text'] ?? 'Selengkapnya',
            'order_index' => $_POST['order_index'] ?? 0,
            'image' => $imageName
        ];

        if ($this->model('Cms_model')->addSlider($data)) {
            Flasher::setFlash('Slider berhasil ditambahkan', 'success');
        } else {
            Flasher::setFlash('Gagal menambahkan slider', 'danger');
        }

        header('Location: ' . BASEURL . '/cms/sliders');
        exit;
    }

    public function hapusSlider($id)
    {
        $this->requireFullAdmin();

        // TODO: Hapus file gambar juga dari server (optional but recommended)
        if ($this->model('Cms_model')->deleteSlider($id)) {
            Flasher::setFlash('Slider berhasil dihapus', 'success');
        } else {
            Flasher::setFlash('Gagal menghapus slider', 'danger');
        }
        header('Location: ' . BASEURL . '/cms/sliders');
        exit;
    }

    public function toggleSlider($id, $status)
    {
        $this->requireFullAdmin();

        // status: 1 or 0
        $this->model('Cms_model')->toggleSlider($id, $status);
        header('Location: ' . BASEURL . '/cms/sliders');
        exit;
    }

    // --- POPUP MANAGEMENT ---

    public function popups()
    {
        $this->requireFullAdmin();

        $this->data['judul'] = 'Popup Informasi';
        $this->data['popups'] = $this->model('Cms_model')->getAllPopups();

        $this->view('templates/header', $this->data);
        $this->view('admin/cms/popups', $this->data);
        $this->view('templates/footer');
    }

    public function tambahPopup()
    {
        $this->requireFullAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/cms/popups');
            exit;
        }

        $imageName = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploaded = $this->handleImageUpload($_FILES['image'], 'popup');
            if ($uploaded) {
                $imageName = $uploaded;
            }
        }

        $data = [
            'title' => $_POST['title'] ?? '',
            'content' => $_POST['content'] ?? '',
            'link_url' => $_POST['link_url'] ?? '',
            'start_date' => $_POST['start_date'] ?? date('Y-m-d'),
            'end_date' => $_POST['end_date'] ?? date('Y-m-d', strtotime('+7 days')),
            'frequency' => $_POST['frequency'] ?? 'once',
            'image' => $imageName
        ];

        if ($this->model('Cms_model')->addPopup($data)) {
            Flasher::setFlash('Popup berhasil ditambahkan', 'success');
        } else {
            Flasher::setFlash('Gagal menambahkan popup', 'danger');
        }

        header('Location: ' . BASEURL . '/cms/popups');
        exit;
    }

    public function hapusPopup($id)
    {
        $this->requireFullAdmin();

        if ($this->model('Cms_model')->deletePopup($id)) {
            Flasher::setFlash('Popup berhasil dihapus', 'success');
        } else {
            Flasher::setFlash('Gagal menghapus popup', 'danger');
        }
        header('Location: ' . BASEURL . '/cms/popups');
        exit;
    }

    public function togglePopup($id, $status)
    {
        $this->requireFullAdmin();

        $this->model('Cms_model')->togglePopup($id, $status);
        header('Location: ' . BASEURL . '/cms/popups');
        exit;
    }

    public function editPopup($id)
    {
        $this->requireFullAdmin();

        $this->data['judul'] = 'Edit Popup';
        $this->data['popup'] = $this->model('Cms_model')->getPopupById($id);

        if (!$this->data['popup']) {
            Flasher::setFlash('Popup tidak ditemukan', 'danger');
            header('Location: ' . BASEURL . '/cms/popups');
            exit;
        }

        $this->view('templates/header', $this->data);
        $this->view('admin/cms/edit_popup', $this->data);
        $this->view('templates/footer');
    }

    public function updatePopup()
    {
        $this->requireFullAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/cms/popups');
            exit;
        }

        $imageName = $_POST['old_image'] ?? '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploaded = $this->handleImageUpload($_FILES['image'], 'popup');
            if ($uploaded) {
                $imageName = $uploaded;
            }
        }

        $data = [
            'id' => $_POST['id'],
            'title' => $_POST['title'] ?? '',
            'content' => $_POST['content'] ?? '',
            'link_url' => $_POST['link_url'] ?? '',
            'start_date' => $_POST['start_date'],
            'end_date' => $_POST['end_date'],
            'frequency' => $_POST['frequency'] ?? 'once',
            'image' => $imageName
        ];

        if ($this->model('Cms_model')->updatePopup($data)) {
            Flasher::setFlash('Popup berhasil diperbarui', 'success');
        } else {
            Flasher::setFlash('Gagal memperbarui popup', 'danger');
        }

        header('Location: ' . BASEURL . '/cms/popups');
        exit;
    }

    // --- MENUS MANAGEMENT ---

    public function menus()
    {
        $this->requireFullAdmin();

        $this->data['judul'] = 'Menu Navigasi';
        $menus = $this->model('Cms_model')->getAllMenus();

        // Sort Hierarchically for Table View (Max Depth 2 recommended for UI)
        $sorted = [];
        $parents = array_filter($menus, function ($m) {
            return $m['parent_id'] == 0;
        });

        foreach ($parents as $p) {
            $p['depth'] = 0;
            $sorted[] = $p;

            // Level 1 Children
            $children = array_filter($menus, function ($m) use ($p) {
                return $m['parent_id'] == $p['id'];
            });
            foreach ($children as $c) {
                $c['depth'] = 1;
                $sorted[] = $c;

                // Level 2 Grandchildren
                $grandChildren = array_filter($menus, function ($m) use ($c) {
                    return $m['parent_id'] == $c['id'];
                });
                foreach ($grandChildren as $gc) {
                    $gc['depth'] = 2;
                    $sorted[] = $gc;
                }
            }
        }

        $this->data['menus'] = $sorted;
        $this->data['parents'] = $parents; // Only top level parents for dropdown

        $this->view('templates/header', $this->data);
        $this->view('admin/cms/menus', $this->data);
        $this->view('templates/footer');
    }

    public function tambahMenu()
    {
        $this->requireFullAdmin();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->model('Cms_model')->addMenu($_POST) > 0) {
                Flasher::setFlash('Menu berhasil ditambahkan', 'success');
            } else {
                Flasher::setFlash('Gagal menambahkan menu', 'danger');
            }
            header('Location: ' . BASEURL . '/cms/menus');
            exit;
        }
    }

    public function hapusMenu($id)
    {
        $this->requireFullAdmin();

        if ($this->model('Cms_model')->deleteMenu($id) > 0) {
            Flasher::setFlash('Menu berhasil dihapus', 'success');
        } else {
            Flasher::setFlash('Gagal menghapus menu', 'danger');
        }
        header('Location: ' . BASEURL . '/cms/menus');
        exit;
    }

    public function toggleMenu($id, $status)
    {
        $this->requireFullAdmin();

        $this->model('Cms_model')->toggleMenu($id, $status);
        header('Location: ' . BASEURL . '/cms/menus');
        exit;
    }

    public function editMenu($id)
    {
        $this->requireFullAdmin();

        $this->data['judul'] = 'Edit Menu';
        $this->data['menu'] = $this->model('Cms_model')->getMenuById($id);
        $this->data['parents'] = array_filter($this->model('Cms_model')->getAllMenus(), function ($m) use ($id) {
            return $m['parent_id'] == 0 && $m['id'] != $id; // Exclude self from parent options
        });

        if (!$this->data['menu']) {
            Flasher::setFlash('Menu tidak ditemukan', 'danger');
            header('Location: ' . BASEURL . '/cms/menus');
            exit;
        }

        $this->view('templates/header', $this->data);
        $this->view('admin/cms/edit_menu', $this->data);
        $this->view('templates/footer');
    }

    public function updateMenu()
    {
        $this->requireFullAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/cms/menus');
            exit;
        }

        $data = [
            'id' => $_POST['id'],
            'label' => $_POST['label'],
            'url' => $_POST['url'],
            'parent_id' => $_POST['parent_id'] ?? 0,
            'type' => $_POST['type'] ?? 'link',
            'order_index' => $_POST['order_index'] ?? 0
        ];

        if ($this->model('Cms_model')->updateMenu($data)) {
            Flasher::setFlash('Menu berhasil diperbarui', 'success');
        } else {
            Flasher::setFlash('Gagal memperbarui menu', 'danger');
        }

        header('Location: ' . BASEURL . '/cms/menus');
        exit;
    }

    // ========== POSTS MANAGEMENT ==========

    /**
     * Helper: Load sidebar based on user role
     */
    private function loadSidebar()
    {
        $role = $this->data['user_role'] ?? '';
        if ($this->isAdmin) {
            $this->view('templates/sidebar_cms', $this->data);
        } elseif ($role === 'wali_kelas') {
            $this->view('templates/sidebar_walikelas', $this->data);
        } else {
            $this->view('templates/sidebar_guru', $this->data);
        }
    }

    public function posts()
    {
        $this->data['judul'] = 'Berita & Halaman';
        $this->data['posts'] = $this->model('Cms_model')->getAllPosts();

        $this->view('templates/header', $this->data);
        $this->loadSidebar();
        $this->view('admin/cms/posts', $this->data);
        $this->view('templates/footer');
    }

    public function tambahPost()
    {
        $this->data['judul'] = 'Tambah Artikel';

        $this->view('templates/header', $this->data);
        $this->loadSidebar();
        $this->view('admin/cms/tambah_post', $this->data);
        $this->view('templates/footer');
    }

    public function simpanPost()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/cms/posts');
            exit;
        }

        $model = $this->model('Cms_model');
        $slug = $model->generateSlug($_POST['title']);

        $imageName = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploaded = $this->handleImageUpload($_FILES['image'], 'post');
            if ($uploaded) {
                $imageName = $uploaded;
            }
        }

        $data = [
            'title' => $_POST['title'],
            'slug' => $slug,
            'content' => $_POST['content'],
            'type' => $_POST['type'] ?? 'news',
            'image' => $imageName,
            'author_id' => $_SESSION['id_user'] ?? null,
            'is_published' => isset($_POST['is_published']) ? 1 : 0,
            'published_at' => $_POST['published_at'] ?: date('Y-m-d H:i:s')
        ];

        if ($model->addPost($data)) {
            Flasher::setFlash('Artikel berhasil ditambahkan', 'success');
        } else {
            Flasher::setFlash('Gagal menambahkan artikel', 'danger');
        }

        header('Location: ' . BASEURL . '/cms/posts');
        exit;
    }

    public function editPost($id)
    {
        $this->data['judul'] = 'Edit Artikel';
        $this->data['post'] = $this->model('Cms_model')->getPostById($id);

        if (!$this->data['post']) {
            Flasher::setFlash('Artikel tidak ditemukan', 'danger');
            header('Location: ' . BASEURL . '/cms/posts');
            exit;
        }

        $this->view('templates/header', $this->data);
        $this->loadSidebar();
        $this->view('admin/cms/edit_post', $this->data);
        $this->view('templates/footer');
    }

    public function updatePost()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/cms/posts');
            exit;
        }

        $model = $this->model('Cms_model');
        $slug = $model->generateSlug($_POST['title'], $_POST['id']);

        $imageName = $_POST['old_image'] ?? '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploaded = $this->handleImageUpload($_FILES['image'], 'post');
            if ($uploaded) {
                $imageName = $uploaded;
            }
        }

        $data = [
            'id' => $_POST['id'],
            'title' => $_POST['title'],
            'slug' => $slug,
            'content' => $_POST['content'],
            'type' => $_POST['type'] ?? 'news',
            'image' => $imageName,
            'is_published' => isset($_POST['is_published']) ? 1 : 0,
            'published_at' => $_POST['published_at'] ?: date('Y-m-d H:i:s')
        ];

        if ($model->updatePost($data)) {
            Flasher::setFlash('Artikel berhasil diperbarui', 'success');
        } else {
            Flasher::setFlash('Gagal memperbarui artikel', 'danger');
        }

        header('Location: ' . BASEURL . '/cms/posts');
        exit;
    }

    public function hapusPost($id)
    {
        if ($this->model('Cms_model')->deletePost($id)) {
            Flasher::setFlash('Artikel berhasil dihapus', 'success');
        } else {
            Flasher::setFlash('Gagal menghapus artikel', 'danger');
        }
        header('Location: ' . BASEURL . '/cms/posts');
        exit;
    }

    public function togglePost($id, $status)
    {
        $this->model('Cms_model')->togglePost($id, $status);
        header('Location: ' . BASEURL . '/cms/posts');
        exit;
    }

    // ========== INSTITUTIONS MANAGEMENT ==========

    public function institutions()
    {
        $this->requireFullAdmin();

        $this->data['judul'] = 'Kelola Lembaga';
        $this->data['institutions'] = $this->model('Cms_model')->getActiveInstitutionsWithCount(false);
        $this->data['classes'] = $this->model('Cms_model')->getAllClasses();
        $this->data['tahun_pelajaran'] = $this->model('Cms_model')->getAllTahunPelajaran();

        $this->view('templates/header', $this->data);
        $this->view('admin/cms/institutions', $this->data);
        $this->view('templates/footer');
    }

    public function tambahLembaga()
    {
        $this->requireFullAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/cms/institutions');
            exit;
        }

        $selectedClasses = null;
        if (isset($_POST['selected_classes']) && is_array($_POST['selected_classes'])) {
            $selectedClasses = json_encode(array_map('intval', $_POST['selected_classes']));
        }

        $data = [
            'name' => $_POST['name'],
            'short_name' => $_POST['short_name'] ?? null,
            'icon' => $_POST['icon'] ?? 'school',
            'color' => $_POST['color'] ?? 'blue',
            'count_mode' => $_POST['count_mode'] ?? 'manual',
            'manual_count' => (int) ($_POST['manual_count'] ?? 0),
            'selected_classes' => $selectedClasses,
            'target_tp_id' => !empty($_POST['target_tp_id']) ? (int) $_POST['target_tp_id'] : null,
            'description' => $_POST['description'] ?? null,
            'order_index' => (int) ($_POST['order_index'] ?? 1)
        ];

        if ($this->model('Cms_model')->addInstitution($data)) {
            Flasher::setFlash('Lembaga berhasil ditambahkan', 'success');
        } else {
            Flasher::setFlash('Gagal menambahkan lembaga', 'danger');
        }

        header('Location: ' . BASEURL . '/cms/institutions');
        exit;
    }

    public function updateLembaga()
    {
        $this->requireFullAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/cms/institutions');
            exit;
        }

        $selectedClasses = null;
        if (isset($_POST['selected_classes']) && is_array($_POST['selected_classes'])) {
            $selectedClasses = json_encode(array_map('intval', $_POST['selected_classes']));
        }

        $data = [
            'id' => $_POST['id'],
            'name' => $_POST['name'],
            'short_name' => $_POST['short_name'] ?? null,
            'icon' => $_POST['icon'] ?? 'school',
            'color' => $_POST['color'] ?? 'blue',
            'count_mode' => $_POST['count_mode'] ?? 'manual',
            'manual_count' => (int) ($_POST['manual_count'] ?? 0),
            'selected_classes' => $selectedClasses,
            'target_tp_id' => !empty($_POST['target_tp_id']) ? (int) $_POST['target_tp_id'] : null,
            'description' => $_POST['description'] ?? null,
            'order_index' => (int) ($_POST['order_index'] ?? 1)
        ];

        if ($this->model('Cms_model')->updateInstitution($data)) {
            Flasher::setFlash('Lembaga berhasil diperbarui', 'success');
        } else {
            Flasher::setFlash('Gagal memperbarui lembaga', 'danger');
        }

        header('Location: ' . BASEURL . '/cms/institutions');
        exit;
    }

    public function hapusLembaga($id)
    {
        $this->requireFullAdmin();

        if ($this->model('Cms_model')->deleteInstitution($id)) {
            Flasher::setFlash('Lembaga berhasil dihapus', 'success');
        } else {
            Flasher::setFlash('Gagal menghapus lembaga', 'danger');
        }
        header('Location: ' . BASEURL . '/cms/institutions');
        exit;
    }

    public function toggleLembaga($id, $status)
    {
        $this->requireFullAdmin();

        $this->model('Cms_model')->toggleInstitution($id, $status);
        header('Location: ' . BASEURL . '/cms/institutions');
        exit;
    }
}
