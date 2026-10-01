<?php
// File: app/controllers/AdminController.php - ALL SQL QUERIES FIXED
// v1.25.0 - Method dipecah ke trait di app/controllers/traits/ (perilaku tidak berubah)
require_once __DIR__ . '/traits/AdminDashboardTrait.php';
require_once __DIR__ . '/traits/AdminSiswaTrait.php';
require_once __DIR__ . '/traits/AdminGuruTrait.php';
require_once __DIR__ . '/traits/AdminAkademikTrait.php';
require_once __DIR__ . '/traits/AdminLaporanTrait.php';
require_once __DIR__ . '/traits/AdminPengaturanTrait.php';
require_once __DIR__ . '/traits/AdminPesanTrait.php';
require_once __DIR__ . '/traits/AdminWAGatewayTrait.php';
require_once __DIR__ . '/traits/AdminPembayaranTrait.php';
require_once __DIR__ . '/traits/AdminKesiswaanTrait.php';
require_once __DIR__ . '/traits/AdminBKTrait.php';

class AdminController extends Controller
{
    private $data = [];

    // v1.25.0 - Trait per domain (hasil pemecahan file 7794 baris)
    use AdminDashboardTrait;
    use AdminSiswaTrait;
    use AdminGuruTrait;
    use AdminAkademikTrait;
    use AdminLaporanTrait;
    use AdminPengaturanTrait;
    use AdminPesanTrait;
    use AdminWAGatewayTrait;
    use AdminPembayaranTrait;
    use AdminKesiswaanTrait;
    use AdminBKTrait;
}
