<?php

class Cms_model
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
        $this->ensureTablesExist();
    }

    /**
     * Pastikan semua tabel CMS ada
     */
    private function ensureTablesExist()
    {
        // 1. CMS Settings
        $this->db->query("CREATE TABLE IF NOT EXISTS `cms_settings` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `setting_key` varchar(50) NOT NULL,
            `setting_value` text,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `setting_key` (`setting_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $this->db->execute();

        // 2. CMS Sliders (Hero Section)
        $this->db->query("CREATE TABLE IF NOT EXISTS `cms_sliders` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `title` varchar(100) DEFAULT NULL,
            `description` text,
            `image` varchar(255) NOT NULL,
            `link_url` varchar(255) DEFAULT NULL,
            `button_text` varchar(50) DEFAULT 'Selengkapnya',
            `order_index` int(11) DEFAULT 0,
            `is_active` tinyint(1) DEFAULT 1,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $this->db->execute();

        // 3. CMS Popups (Modal Info)
        $this->db->query("CREATE TABLE IF NOT EXISTS `cms_popups` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `title` varchar(100) DEFAULT NULL,
            `image` varchar(255) DEFAULT NULL,
            `content` text,
            `link_url` varchar(255) DEFAULT NULL,
            `is_active` tinyint(1) DEFAULT 1,
            `start_date` datetime DEFAULT NULL,
            `end_date` datetime DEFAULT NULL,
            `frequency` enum('once','always','daily') DEFAULT 'once',
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $this->db->execute();

        // 4. CMS Menus
        $this->db->query("CREATE TABLE IF NOT EXISTS `cms_menus` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `label` varchar(50) NOT NULL,
            `url` varchar(255) DEFAULT '#',
            `parent_id` int(11) DEFAULT 0,
            `type` enum('page','link','custom') DEFAULT 'link',
            `order_index` int(11) DEFAULT 0,
            `is_active` tinyint(1) DEFAULT 1,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $this->db->execute();

        // 5. CMS Posts (Pages & News)
        $this->db->query("CREATE TABLE IF NOT EXISTS `cms_posts` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `title` varchar(255) NOT NULL,
            `slug` varchar(255) NOT NULL,
            `content` longtext,
            `type` enum('page','news','announcement') DEFAULT 'news',
            `image` varchar(255) DEFAULT NULL,
            `author_id` int(11) DEFAULT NULL,
            `view_count` int(11) DEFAULT 0,
            `is_published` tinyint(1) DEFAULT 1,
            `published_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `slug` (`slug`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $this->db->execute();

        // 6. CMS Institutions (Lembaga Yayasan)
        $this->db->query("CREATE TABLE IF NOT EXISTS `cms_institutions` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(100) NOT NULL,
            `short_name` varchar(50) DEFAULT NULL,
            `icon` varchar(50) DEFAULT 'school',
            `color` varchar(20) DEFAULT 'blue',
            `count_mode` enum('manual','auto') DEFAULT 'manual',
            `manual_count` int(11) DEFAULT 0,
            `selected_classes` text DEFAULT NULL,
            `description` varchar(255) DEFAULT NULL,
            `order_index` int(11) DEFAULT 1,
            `is_active` tinyint(1) DEFAULT 1,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $this->db->execute();

        // Seed Default Data
        $this->seedDefaultData();
    }

    /**
     * Isi data default jika tabel kosong
     */
    private function seedDefaultData()
    {
        // Default Settings
        $this->db->query("SELECT COUNT(*) as count FROM cms_settings");
        if ($this->db->single()['count'] == 0) {
            $defaults = [
                'yayasan_name' => 'Yayasan Pendidikan Smart Absensi',
                'school_name' => 'SMK Smart Absensi',
                'address' => 'Jl. Pendidikan No. 123',
                'phone' => '081234567890',
                'email' => 'info@smartabsensi.com',
                'facebook' => 'https://facebook.com',
                'instagram' => 'https://instagram.com',
                'youtube' => 'https://youtube.com',
                'maps_embed' => '',
                'welcome_title' => 'Selamat Datang di Portal Resmi',
                'welcome_text' => 'Kami berkomitmen mencetak generasi unggul berprestasi dan berakhlak mulia.',
                'welcome_image' => 'default_welcome.jpg'
            ];

            foreach ($defaults as $key => $val) {
                $this->db->query("INSERT INTO cms_settings (setting_key, setting_value) VALUES (:key, :val)");
                $this->db->bind('key', $key);
                $this->db->bind('val', $val);
                $this->db->execute();
            }
        }

        // Default Menu (Home & Login)
        $this->db->query("SELECT COUNT(*) as count FROM cms_menus");
        if ($this->db->single()['count'] == 0) {
            $menus = [
                ['label' => 'Beranda', 'url' => '/', 'order_index' => 1],
                ['label' => 'Profil', 'url' => '#', 'order_index' => 2],
                ['label' => 'Berita', 'url' => '/news', 'order_index' => 3],
                ['label' => 'Kontak', 'url' => '/contact', 'order_index' => 4],
            ];

            foreach ($menus as $menu) {
                $this->db->query("INSERT INTO cms_menus (label, url, order_index) VALUES (:label, :url, :order)");
                $this->db->bind('label', $menu['label']);
                $this->db->bind('url', $menu['url']);
                $this->db->bind('order', $menu['order_index']);
                $this->db->execute();
            }
        }
    }

    // --- Methods untuk Settings ---

    public function getAllSettings()
    {
        $this->db->query("SELECT * FROM cms_settings");
        $result = $this->db->resultSet();
        $settings = [];
        foreach ($result as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }

    public function updateSetting($key, $value)
    {
        $this->db->query("INSERT INTO cms_settings (setting_key, setting_value) VALUES (:key, :val1) 
                          ON DUPLICATE KEY UPDATE setting_value = :val2");
        $this->db->bind(':key', $key);
        $this->db->bind(':val1', $value);
        $this->db->bind(':val2', $value);
        return $this->db->execute();
    }

    // --- Sliders Methods ---
    public function getAllSliders()
    {
        $this->db->query("SELECT * FROM cms_sliders ORDER BY order_index ASC");
        return $this->db->resultSet();
    }

    public function getActiveSliders()
    {
        $this->db->query("SELECT * FROM cms_sliders WHERE is_active = 1 ORDER BY order_index ASC");
        return $this->db->resultSet();
    }

    public function addSlider($data)
    {
        $this->db->query("INSERT INTO cms_sliders (title, description, image, link_url, button_text, order_index, is_active) 
                          VALUES (:title, :desc, :img, :url, :btn, :order, 1)");
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':desc', $data['description']);
        $this->db->bind(':img', $data['image']);
        $this->db->bind(':url', $data['link_url'] ?? '#');
        $this->db->bind(':btn', $data['button_text'] ?? 'Selengkapnya');
        $this->db->bind(':order', $data['order_index'] ?? 0);
        return $this->db->execute();
    }

    public function deleteSlider($id)
    {
        $this->db->query("DELETE FROM cms_sliders WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function toggleSlider($id, $status)
    {
        $this->db->query("UPDATE cms_sliders SET is_active = :status WHERE id = :id");
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    // --- Popups Methods ---
    public function getAllPopups()
    {
        $this->db->query("SELECT * FROM cms_popups ORDER BY created_at DESC");
        return $this->db->resultSet();
    }

    public function getActivePopups()
    {
        $today = date('Y-m-d');
        $this->db->query("SELECT * FROM cms_popups WHERE is_active = 1 AND start_date <= :today1 AND end_date >= :today2 ORDER BY created_at DESC");
        $this->db->bind(':today1', $today);
        $this->db->bind(':today2', $today);
        return $this->db->resultSet();
    }

    public function addPopup($data)
    {
        $this->db->query("INSERT INTO cms_popups (title, image, content, link_url, is_active, start_date, end_date, frequency) 
                          VALUES (:title, :img, :content, :url, 1, :start, :end, :freq)");
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':img', $data['image']);
        $this->db->bind(':content', $data['content']);
        $this->db->bind(':url', $data['link_url'] ?? '#');
        $this->db->bind(':start', $data['start_date'] ?? date('Y-m-d'));
        $this->db->bind(':end', $data['end_date'] ?? date('Y-m-d', strtotime('+7 days')));
        $this->db->bind(':freq', $data['frequency'] ?? 'once');
        return $this->db->execute();
    }

    public function deletePopup($id)
    {
        $this->db->query("DELETE FROM cms_popups WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function getPopupById($id)
    {
        $this->db->query("SELECT * FROM cms_popups WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function updatePopup($data)
    {
        $sql = "UPDATE cms_popups SET title = :title, content = :content, 
                link_url = :url, start_date = :start, end_date = :end, frequency = :freq";

        if (!empty($data['image'])) {
            $sql .= ", image = :img";
        }
        $sql .= " WHERE id = :id";

        $this->db->query($sql);
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':content', $data['content']);
        $this->db->bind(':url', $data['link_url'] ?? '#');
        $this->db->bind(':start', $data['start_date']);
        $this->db->bind(':end', $data['end_date']);
        $this->db->bind(':freq', $data['frequency']);
        $this->db->bind(':id', $data['id']);

        if (!empty($data['image'])) {
            $this->db->bind(':img', $data['image']);
        }

        return $this->db->execute();
    }

    public function togglePopup($id, $status)
    {
        $this->db->query("UPDATE cms_popups SET is_active = :status WHERE id = :id");
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    // --- Menus Methods ---

    public function getAllMenus()
    {
        $this->db->query("SELECT * FROM cms_menus ORDER BY order_index ASC");
        return $this->db->resultSet();
    }

    public function getActiveMenus() // Keep flat for backward compat or raw access
    {
        $this->db->query("SELECT * FROM cms_menus WHERE is_active = 1 ORDER BY parent_id ASC, order_index ASC");
        return $this->db->resultSet();
    }

    public function getActiveMenuTree()
    {
        $elements = $this->getActiveMenus();
        return $this->buildTree($elements);
    }

    // Helper Function to Build Tree
    private function buildTree(array $elements, $parentId = 0)
    {
        $branch = array();
        foreach ($elements as $element) {
            if ($element['parent_id'] == $parentId) {
                $children = $this->buildTree($elements, $element['id']);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[] = $element;
            }
        }
        return $branch;
    }

    public function addMenu($data)
    {
        $this->db->query("INSERT INTO cms_menus (label, url, parent_id, type, order_index, is_active) 
                          VALUES (:label, :url, :parent, :type, :order, 1)");
        $this->db->bind(':label', $data['label']);
        $this->db->bind(':url', $data['url']);
        $this->db->bind(':parent', !empty($data['parent_id']) ? $data['parent_id'] : 0);
        $this->db->bind(':type', $data['type'] ?? 'link');
        $this->db->bind(':order', $data['order_index'] ?? 0);
        return $this->db->execute();
    }

    public function deleteMenu($id)
    {
        $this->db->query("DELETE FROM cms_menus WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function toggleMenu($id, $status)
    {
        $this->db->query("UPDATE cms_menus SET is_active = :status WHERE id = :id");
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function getMenuById($id)
    {
        $this->db->query("SELECT * FROM cms_menus WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function updateMenu($data)
    {
        $this->db->query("UPDATE cms_menus SET label = :label, url = :url, parent_id = :parent, 
                          type = :type, order_index = :order WHERE id = :id");
        $this->db->bind(':label', $data['label']);
        $this->db->bind(':url', $data['url']);
        $this->db->bind(':parent', $data['parent_id'] ?? 0);
        $this->db->bind(':type', $data['type'] ?? 'link');
        $this->db->bind(':order', $data['order_index'] ?? 0);
        $this->db->bind(':id', $data['id']);
        return $this->db->execute();
    }

    // ========== POSTS MANAGEMENT ==========

    public function getAllPosts($type = null)
    {
        $sql = "SELECT p.*, u.nama_lengkap as author_name 
                FROM cms_posts p 
                LEFT JOIN users u ON p.author_id = u.id_user";
        if ($type) {
            $sql .= " WHERE p.type = :type";
        }
        $sql .= " ORDER BY p.created_at DESC";

        $this->db->query($sql);
        if ($type) {
            $this->db->bind(':type', $type);
        }
        return $this->db->resultSet();
    }

    public function getPostById($id)
    {
        $this->db->query("SELECT * FROM cms_posts WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function getPostBySlug($slug)
    {
        $this->db->query("SELECT * FROM cms_posts WHERE slug = :slug AND is_published = 1");
        $this->db->bind(':slug', $slug);
        return $this->db->single();
    }

    public function getPublishedPosts($type = 'news', $limit = 10)
    {
        $this->db->query("SELECT * FROM cms_posts 
                          WHERE type = :type AND is_published = 1 
                          ORDER BY published_at DESC 
                          LIMIT :limit");
        $this->db->bind(':type', $type);
        $this->db->bind(':limit', (int) $limit);
        return $this->db->resultSet();
    }

    public function addPost($data)
    {
        $this->db->query("INSERT INTO cms_posts (title, slug, content, type, image, author_id, is_published, published_at) 
                          VALUES (:title, :slug, :content, :type, :image, :author, :published, :pub_date)");
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':slug', $data['slug']);
        $this->db->bind(':content', $data['content']);
        $this->db->bind(':type', $data['type'] ?? 'news');
        $this->db->bind(':image', $data['image'] ?? null);
        $this->db->bind(':author', $data['author_id'] ?? null);
        $this->db->bind(':published', $data['is_published'] ?? 1);
        $this->db->bind(':pub_date', $data['published_at'] ?? date('Y-m-d H:i:s'));
        return $this->db->execute();
    }

    public function updatePost($data)
    {
        $sql = "UPDATE cms_posts SET title = :title, slug = :slug, content = :content, 
                type = :type, is_published = :published, published_at = :pub_date";
        if (!empty($data['image'])) {
            $sql .= ", image = :image";
        }
        $sql .= " WHERE id = :id";

        $this->db->query($sql);
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':slug', $data['slug']);
        $this->db->bind(':content', $data['content']);
        $this->db->bind(':type', $data['type'] ?? 'news');
        $this->db->bind(':published', $data['is_published'] ?? 1);
        $this->db->bind(':pub_date', $data['published_at'] ?? date('Y-m-d H:i:s'));
        $this->db->bind(':id', $data['id']);
        if (!empty($data['image'])) {
            $this->db->bind(':image', $data['image']);
        }
        return $this->db->execute();
    }

    public function deletePost($id)
    {
        $this->db->query("DELETE FROM cms_posts WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function togglePost($id, $status)
    {
        $this->db->query("UPDATE cms_posts SET is_published = :status WHERE id = :id");
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function incrementViewCount($id)
    {
        $this->db->query("UPDATE cms_posts SET view_count = view_count + 1 WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function generateSlug($title, $excludeId = null)
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $slug = trim($slug, '-');

        // Check uniqueness
        $sql = "SELECT COUNT(*) as cnt FROM cms_posts WHERE slug = :slug";
        if ($excludeId) {
            $sql .= " AND id != :exclude";
        }
        $this->db->query($sql);
        $this->db->bind(':slug', $slug);
        if ($excludeId) {
            $this->db->bind(':exclude', $excludeId);
        }
        $result = $this->db->single();

        if ($result['cnt'] > 0) {
            $slug .= '-' . time();
        }
        return $slug;
    }

    // ========== INSTITUTIONS MANAGEMENT ==========

    public function getAllInstitutions()
    {
        $this->db->query("SELECT * FROM cms_institutions ORDER BY order_index ASC, id ASC");
        return $this->db->resultSet();
    }

    public function getInstitutionById($id)
    {
        $this->db->query("SELECT * FROM cms_institutions WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function addInstitution($data)
    {
        $this->db->query("INSERT INTO cms_institutions (name, short_name, icon, color, count_mode, manual_count, selected_classes, target_tp_id, description, order_index) 
                          VALUES (:name, :short, :icon, :color, :mode, :manual, :classes, :tp, :desc, :order)");
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':short', $data['short_name'] ?? null);
        $this->db->bind(':icon', $data['icon'] ?? 'school');
        $this->db->bind(':color', $data['color'] ?? 'blue');
        $this->db->bind(':mode', $data['count_mode'] ?? 'manual');
        $this->db->bind(':manual', $data['manual_count'] ?? 0);
        $this->db->bind(':classes', $data['selected_classes'] ?? null);
        $this->db->bind(':tp', $data['target_tp_id'] ?? null);
        $this->db->bind(':desc', $data['description'] ?? null);
        $this->db->bind(':order', $data['order_index'] ?? 1);
        return $this->db->execute();
    }

    public function updateInstitution($data)
    {
        $this->db->query("UPDATE cms_institutions SET 
                          name = :name, short_name = :short, icon = :icon, color = :color, 
                          count_mode = :mode, manual_count = :manual, selected_classes = :classes, target_tp_id = :tp,
                          description = :desc, order_index = :order
                          WHERE id = :id");
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':short', $data['short_name'] ?? null);
        $this->db->bind(':icon', $data['icon'] ?? 'school');
        $this->db->bind(':color', $data['color'] ?? 'blue');
        $this->db->bind(':mode', $data['count_mode'] ?? 'manual');
        $this->db->bind(':manual', $data['manual_count'] ?? 0);
        $this->db->bind(':classes', $data['selected_classes'] ?? null);
        $this->db->bind(':tp', $data['target_tp_id'] ?? null);
        $this->db->bind(':desc', $data['description'] ?? null);
        $this->db->bind(':order', $data['order_index'] ?? 1);
        $this->db->bind(':id', $data['id']);
        return $this->db->execute();
    }

    public function deleteInstitution($id)
    {
        $this->db->query("DELETE FROM cms_institutions WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function toggleInstitution($id, $status)
    {
        $this->db->query("UPDATE cms_institutions SET is_active = :status WHERE id = :id");
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function getActiveInstitutionsWithCount($activeOnly = true)
    {
        if ($activeOnly) {
            $this->db->query("SELECT * FROM cms_institutions WHERE is_active = 1 ORDER BY order_index ASC");
        } else {
            $this->db->query("SELECT * FROM cms_institutions ORDER BY order_index ASC");
        }
        $institutions = $this->db->resultSet();

        // Get current tahun pelajaran (from active semester)
        $this->db->query("SELECT tp.id_tp FROM semester JOIN tp ON semester.id_tp = tp.id_tp WHERE semester.is_active = 'ya' LIMIT 1");
        $tp = $this->db->single();
        $id_tp = $tp ? $tp['id_tp'] : 0;

        // Fallback: If no active semester, use latest TP
        if ($id_tp == 0) {
            $this->db->query("SELECT id_tp FROM tp ORDER BY tgl_mulai DESC LIMIT 1");
            $latestTp = $this->db->single();
            $id_tp = $latestTp ? $latestTp['id_tp'] : 0;
        }

        foreach ($institutions as &$inst) {
            $inst['detail_counts'] = [];

            // Determine TP ID for this institution
            $current_tp_id = (!empty($inst['target_tp_id']) && $inst['target_tp_id'] > 0) ? $inst['target_tp_id'] : $id_tp;

            if ($inst['count_mode'] == 'auto' && !empty($inst['selected_classes'])) {
                $classIds = json_decode($inst['selected_classes'], true);
                if (!empty($classIds) && is_array($classIds)) {
                    // Build safe IN clause with sanitized IDs
                    $safeIds = array_map('intval', $classIds);
                    $idList = implode(',', $safeIds);

                    $this->db->query("SELECT COUNT(DISTINCT kk.id_siswa) as total 
                                      FROM keanggotaan_kelas kk 
                                      JOIN siswa s ON kk.id_siswa = s.id_siswa 
                                      WHERE kk.id_kelas IN ($idList) 
                                      AND kk.id_tp = :id_tp 
                                      AND s.status_siswa = 'aktif'");
                    $this->db->bind(':id_tp', $current_tp_id);

                    $result = $this->db->single();
                    $inst['student_count'] = $result ? (int) $result['total'] : 0;

                    // GET DETAIL PER CLASS
                    $this->db->query("SELECT k.nama_kelas, COUNT(DISTINCT kk.id_siswa) as count 
                                      FROM kelas k
                                      LEFT JOIN keanggotaan_kelas kk ON k.id_kelas = kk.id_kelas AND kk.id_tp = :id_tp
                                      LEFT JOIN siswa s ON kk.id_siswa = s.id_siswa AND s.status_siswa = 'aktif'
                                      WHERE k.id_kelas IN ($idList)
                                      GROUP BY k.id_kelas, k.nama_kelas
                                      ORDER BY k.jenjang, k.nama_kelas");
                    $this->db->bind(':id_tp', $current_tp_id);
                    $inst['detail_counts'] = $this->db->resultSet();
                } else {
                    $inst['student_count'] = 0;
                }
            } else {
                $inst['student_count'] = (int) $inst['manual_count'];
            }
        }

        return $institutions;
    }

    public function getAllClasses()
    {
        $this->db->query("SELECT id_kelas, nama_kelas, jenjang FROM kelas ORDER BY jenjang ASC, nama_kelas ASC");
        return $this->db->resultSet();
    }

    public function getAllTahunPelajaran()
    {
        $this->db->query("SELECT * FROM tp ORDER BY tgl_mulai DESC");
        return $this->db->resultSet();
    }

    public function getStudentCountByClasses($classIds, $id_tp = null)
    {
        if (empty($classIds))
            return 0;

        if (!$id_tp) {
            $this->db->query("SELECT tp.id_tp FROM semester JOIN tp ON semester.id_tp = tp.id_tp WHERE semester.is_active = 'ya' LIMIT 1");
            $tp = $this->db->single();
            $id_tp = $tp ? $tp['id_tp'] : 0;
        }

        // Build safe IN clause with sanitized IDs
        $safeIds = array_map('intval', $classIds);
        $idList = implode(',', $safeIds);

        $this->db->query("SELECT COUNT(DISTINCT kk.id_siswa) as total 
                          FROM keanggotaan_kelas kk 
                          JOIN siswa s ON kk.id_siswa = s.id_siswa 
                          WHERE kk.id_kelas IN ($idList) 
                          AND kk.id_tp = :id_tp 
                          AND s.status_siswa = 'aktif'");
        $this->db->bind(':id_tp', $id_tp);

        $result = $this->db->single();
        return $result ? (int) $result['total'] : 0;
    }
}
