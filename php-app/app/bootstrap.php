<?php
/**
 * بارگذاری کتابخانه‌ها، تنظیمات پایه و تعریف مسیرها.
 */
require BASE_PATH . '/app/lib/helpers.php';
require BASE_PATH . '/app/lib/Jalali.php';
require BASE_PATH . '/app/lib/Core.php';
require BASE_PATH . '/app/lib/Schema.php';
require BASE_PATH . '/app/lib/Models.php';
require BASE_PATH . '/app/lib/Importer.php';
require BASE_PATH . '/app/controllers/WebsiteController.php';
require BASE_PATH . '/app/controllers/PaymentController.php';
require BASE_PATH . '/app/controllers/DashboardController.php';
require BASE_PATH . '/app/controllers/AdminController.php';

mb_internal_encoding('UTF-8');
date_default_timezone_set((string) config('TIME_ZONE', 'Asia/Tehran'));

$debug = (bool) config('DEBUG', false);
ini_set('display_errors', $debug ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', BASE_PATH . '/storage/logs/php-error.log');
error_reporting(E_ALL);

final class App
{
    private Router $router;

    public function __construct()
    {
        $this->router = new Router();
        $this->routes();
    }

    private function routes(): void
    {
        $r = $this->router;
        $w = new WebsiteController();
        $p = new PaymentController();
        $d = new DashboardController();
        $a = new AdminController();

        // ---- سایت عمومی ----
        $r->get('/', [$w, 'home']);
        $r->get('/about/', [$w, 'about']);
        $r->get('/contact/', [$w, 'contact']);
        $r->post('/contact/submit/', [$w, 'contactSubmit']);
        $r->get('/before-after/', [$w, 'beforeAfter']);
        $r->get('/faq/', [$w, 'faq']);
        $r->get('/consultation/', [$w, 'consultation']);
        $r->post('/consultation/submit/', [$w, 'consultationSubmit']);
        $r->get('/booking/', [$w, 'booking']);
        $r->get('/booking/calendar/', [$w, 'bookingCalendar']);
        $r->get('/booking/slots/', [$w, 'bookingSlots']);
        $r->get('/booking/confirm/', [$w, 'bookingConfirm']);
        $r->post('/booking/submit/', [$w, 'bookingSubmit']);
        $r->get('/api/health/', fn() => new Response(json_encode(['status' => 'ok']), 200, ['Content-Type' => 'application/json']));

        // ---- پرداخت ----
        $r->get('/payment/{code}/', [$p, 'page']);
        $r->post('/payment/{code}/start/', [$p, 'start']);
        $r->get('/payment/{code}/callback/', [$p, 'callback']);
        $r->get('/payment/{code}/result/', [$p, 'result']);

        // ---- داشبورد منشی ----
        $r->get('/dashboard/login/', [$d, 'loginForm']);
        $r->post('/dashboard/login/', [$d, 'login']);
        $r->get('/dashboard/logout/', [$d, 'logout']);
        $r->get('/dashboard/', [$d, 'home']);
        $r->post('/dashboard/appointments/{id}/status/', [$d, 'changeStatus']);
        $r->post('/dashboard/appointments/{id}/delete/', [$d, 'deleteAppointment']);
        $r->get('/dashboard/consultations/', [$d, 'consultations']);
        $r->post('/dashboard/consultations/{id}/status/', [$d, 'changeConsultationStatus']);
        $r->post('/dashboard/consultations/{id}/delete/', [$d, 'deleteConsultation']);

        // ---- پنل تنظیمات (جایگزین ادمین جنگو) ----
        $r->get('/admin/', [$a, 'index']);
        $r->get('/admin/settings/', [$a, 'settings']);
        $r->post('/admin/settings/save/', [$a, 'settingsSave']);
        $r->get('/admin/categories/', [$a, 'categories']);
        $r->post('/admin/categories/save/', [$a, 'categorySave']);
        $r->post('/admin/categories/{id}/delete/', [$a, 'categoryDelete']);
        $r->get('/admin/services/', [$a, 'services']);
        $r->post('/admin/services/save/', [$a, 'serviceSave']);
        $r->post('/admin/services/{id}/delete/', [$a, 'serviceDelete']);
        $r->get('/admin/hours/', [$a, 'hours']);
        $r->post('/admin/hours/save/', [$a, 'hourSave']);
        $r->post('/admin/hours/{id}/delete/', [$a, 'hourDelete']);
        $r->post('/admin/holidays/save/', [$a, 'holidaySave']);
        $r->post('/admin/holidays/{id}/delete/', [$a, 'holidayDelete']);
        $r->get('/admin/faqs/', [$a, 'faqs']);
        $r->post('/admin/faqs/save/', [$a, 'faqSave']);
        $r->post('/admin/faqs/{id}/delete/', [$a, 'faqDelete']);
        $r->get('/admin/gallery/', [$a, 'gallery']);
        $r->post('/admin/gallery/save/', [$a, 'gallerySave']);
        $r->post('/admin/gallery/{id}/delete/', [$a, 'galleryDelete']);
        $r->get('/admin/messages/', [$a, 'messages']);
        $r->post('/admin/messages/{id}/read/', [$a, 'messageRead']);
        $r->post('/admin/messages/{id}/delete/', [$a, 'messageDelete']);
        $r->get('/admin/customers/', [$a, 'customers']);
        $r->post('/admin/customers/{id}/block/', [$a, 'customerToggleBlock']);
        $r->get('/admin/users/', [$a, 'users']);
        $r->post('/admin/users/save/', [$a, 'userSave']);
        $r->post('/admin/users/{id}/delete/', [$a, 'userDelete']);
        $r->get('/admin/backup/', [$a, 'backup']);
        $r->get('/admin/import/', [$a, 'importForm']);
        $r->post('/admin/import/', [$a, 'importRun']);
    }

    public function run(Request $req): Response
    {
        try {
            Session::start();
            DB::pdo();
            // نصب اولیه: اگر هیچ کاربری وجود ندارد → صفحه‌ی راه‌اندازی
            // CSRF برای همه‌ی POST ها (callback درگاه GET است و مشمول نمی‌شود)
            if ($req->method === 'POST' && !Csrf::check($req)) {
                return html(render('website/403', ['no_cookie' => empty($_COOKIE['clinicsess'])]), 403);
            }
            if (str_starts_with($req->path, '/setup/') || !DB::val('SELECT COUNT(*) FROM users')) {
                return (new AdminController())->setup($req);
            }
            $res = $this->router->dispatch($req);
            // هدرهای امنیتی پایه
            $res->headers += [
                'X-Content-Type-Options' => 'nosniff',
                'X-Frame-Options' => 'SAMEORIGIN',
                'Referrer-Policy' => 'same-origin',
            ];
            return $res;
        } catch (Throwable $e) {
            error_log($e);
            if (config('DEBUG', false)) {
                return html('<pre dir="ltr" style="padding:2rem;font:14px/1.5 monospace">' . e($e) . '</pre>', 500);
            }
            try { return html(render('website/500'), 500); }
            catch (Throwable) { return html('خطای داخلی سرور', 500); }
        }
    }
}
