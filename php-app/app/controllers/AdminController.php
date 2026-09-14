<?php
/**
 * پنل تنظیمات — جایگزین ادمین جنگو (تنظیمات کلینیک، خدمات، ساعات کاری، تعطیلات، سوالات، گالری، کاربران، بکاپ).
 */
final class AdminController
{
    private function guard(): ?Response
    {
        return Auth::check() ? null : redirect(url('/dashboard/login/'));
    }

    private function page(string $view, array $ctx = [], string $active = ''): Response
    {
        return html(render('admin/' . $view, $ctx + ['active' => $active, 'flash' => Session::flash('ok'), 'flash_err' => Session::flash('err')], 'layout/dashboard'));
    }

    // ------------------------------------------------------------ راه‌اندازی اولیه
    public function setup(Request $req): Response
    {
        if (DB::val('SELECT COUNT(*) FROM users')) return redirect(url('/'));
        $error = null;
        if ($req->method === 'POST') {
            $u = trim((string) $req->input('username')); $p = (string) $req->input('password');
            if (!preg_match('/^[A-Za-z0-9_.-]{3,40}$/', $u)) $error = 'نام کاربری باید ۳ تا ۴۰ کاراکتر انگلیسی باشد.';
            elseif (mb_strlen($p) < 8) $error = 'رمز عبور حداقل ۸ کاراکتر باشد.';
            else {
                DB::insert('users', ['username' => $u, 'password_hash' => password_hash($p, PASSWORD_DEFAULT)]);
                if ($req->input('seed')) Models::seedDemo();
                DB::update('clinic_settings', ['name' => mb_substr(trim((string) $req->input('clinic_name')) ?: 'کلینیک زیبایی', 0, 120)], 'id = 1');
                Auth::attempt($u, $p);
                return redirect(url('/admin/'));
            }
        }
        return html(render('admin/setup', ['error' => $error], 'layout/dashboard'));
    }

    public function index(Request $req): Response
    {
        if ($g = $this->guard()) return $g;
        return $this->page('index', [
            'counts' => [
                'services' => DB::val('SELECT COUNT(*) FROM services'),
                'categories' => DB::val('SELECT COUNT(*) FROM service_categories'),
                'hours' => DB::val('SELECT COUNT(*) FROM working_hours'),
                'faqs' => DB::val('SELECT COUNT(*) FROM faqs'),
                'gallery' => DB::val('SELECT COUNT(*) FROM before_after'),
                'messages' => DB::val('SELECT COUNT(*) FROM contact_messages WHERE is_read = 0'),
                'customers' => DB::val('SELECT COUNT(*) FROM customers'),
            ],
        ], 'index');
    }

    // ------------------------------------------------------------ تنظیمات کلینیک
    public function settings(Request $req): Response
    {
        if ($g = $this->guard()) return $g;
        return $this->page('settings', ['s' => Models::clinic()], 'settings');
    }

    public function settingsSave(Request $req): Response
    {
        if ($g = $this->guard()) return $g;
        DB::update('clinic_settings', [
            'name' => mb_substr(trim($req->input('name')) ?: 'کلینیک زیبایی', 0, 120),
            'phone' => mb_substr(trim($req->input('phone')), 0, 20),
            'address' => trim($req->input('address')),
            'about' => trim($req->input('about')),
            'instagram' => mb_substr(trim($req->input('instagram')), 0, 120),
            'slot_step_minutes' => max(5, (int) $req->input('slot_step_minutes', 30)),
            'max_advance_days' => max(1, (int) $req->input('max_advance_days', 30)),
            'min_advance_hours' => max(0, (int) $req->input('min_advance_hours', 2)),
        ], 'id = 1');
        Session::flash('ok', 'تنظیمات ذخیره شد.');
        return redirect(url('/admin/settings/'));
    }

    // ------------------------------------------------------------ دسته‌بندی‌ها
    public function categories(Request $req): Response
    {
        if ($g = $this->guard()) return $g;
        $edit = $req->q('edit') ? DB::one('SELECT * FROM service_categories WHERE id = ?', [(int) $req->q('edit')]) : null;
        return $this->page('categories', ['items' => DB::all('SELECT c.*, (SELECT COUNT(*) FROM services s WHERE s.category_id = c.id) AS n FROM service_categories c ORDER BY "order", title'), 'edit' => $edit], 'categories');
    }

    public function categorySave(Request $req): Response
    {
        if ($g = $this->guard()) return $g;
        $id = (int) $req->input('id');
        $title = mb_substr(trim($req->input('title')), 0, 80);
        if ($title === '') { Session::flash('err', 'عنوان الزامی است.'); return redirect(url('/admin/categories/')); }
        $data = ['title' => $title, 'description' => trim($req->input('description')), 'order' => (int) $req->input('order'), 'is_active' => $req->input('is_active') ? 1 : 0];
        try {
            if ($id) DB::update('service_categories', $data, 'id = ?', [$id]);
            else DB::insert('service_categories', $data + ['slug' => slugify($title) . '-' . bin2hex(random_bytes(2))]);
            Session::flash('ok', 'ذخیره شد.');
        } catch (PDOException $e) { Session::flash('err', 'عنوان تکراری است.'); }
        return redirect(url('/admin/categories/'));
    }

    public function categoryDelete(Request $req, string $id): Response
    {
        if ($g = $this->guard()) return $g;
        try { DB::exec('DELETE FROM service_categories WHERE id = ?', [(int) $id]); Session::flash('ok', 'حذف شد.'); }
        catch (PDOException $e) { Session::flash('err', 'این دسته دارای خدمت است؛ ابتدا خدمات آن را حذف یا منتقل کنید.'); }
        return redirect(url('/admin/categories/'));
    }

    // ------------------------------------------------------------ خدمات
    public function services(Request $req): Response
    {
        if ($g = $this->guard()) return $g;
        $edit = $req->q('edit') ? DB::one('SELECT * FROM services WHERE id = ?', [(int) $req->q('edit')]) : null;
        return $this->page('services', [
            'items' => DB::all('SELECT s.*, c.title AS category_title FROM services s JOIN service_categories c ON c.id = s.category_id ORDER BY c."order", s."order", s.title'),
            'categories' => DB::all('SELECT * FROM service_categories ORDER BY "order", title'), 'edit' => $edit,
        ], 'services');
    }

    public function serviceSave(Request $req): Response
    {
        if ($g = $this->guard()) return $g;
        $id = (int) $req->input('id');
        $title = mb_substr(trim($req->input('title')), 0, 120);
        $cat = (int) $req->input('category_id');
        if ($title === '' || !$cat) { Session::flash('err', 'عنوان و دسته‌بندی الزامی است.'); return redirect(url('/admin/services/')); }
        $data = ['category_id' => $cat, 'title' => $title, 'description' => trim($req->input('description')),
            'duration_minutes' => max(5, (int) en_digits($req->input('duration_minutes', 30))),
            'price' => max(0, (int) preg_replace('/\D/', '', en_digits($req->input('price', 0)))),
            'order' => (int) $req->input('order'), 'is_active' => $req->input('is_active') ? 1 : 0];
        if ($id) DB::update('services', $data, 'id = ?', [$id]);
        else DB::insert('services', $data + ['slug' => slugify($title) . '-' . bin2hex(random_bytes(2))]);
        Session::flash('ok', 'ذخیره شد.');
        return redirect(url('/admin/services/'));
    }

    public function serviceDelete(Request $req, string $id): Response
    {
        if ($g = $this->guard()) return $g;
        try { DB::exec('DELETE FROM services WHERE id = ?', [(int) $id]); Session::flash('ok', 'حذف شد.'); }
        catch (PDOException $e) { DB::update('services', ['is_active' => 0], 'id = ?', [(int) $id]); Session::flash('err', 'این خدمت نوبت ثبت‌شده دارد؛ به‌جای حذف، غیرفعال شد.'); }
        return redirect(url('/admin/services/'));
    }

    // ------------------------------------------------------------ ساعات کاری و تعطیلات
    public function hours(Request $req): Response
    {
        if ($g = $this->guard()) return $g;
        return $this->page('hours', [
            'hours' => DB::all('SELECT * FROM working_hours ORDER BY weekday, start_time'),
            'holidays' => DB::all('SELECT * FROM holidays ORDER BY date DESC LIMIT 100'),
            'weekdays' => Jalali::WEEKDAYS,
        ], 'hours');
    }

    public function hourSave(Request $req): Response
    {
        if ($g = $this->guard()) return $g;
        $wd = (int) $req->input('weekday'); $s = en_digits(trim($req->input('start_time'))); $e = en_digits(trim($req->input('end_time')));
        if ($wd < 0 || $wd > 6 || !preg_match('/^\d{2}:\d{2}$/', $s) || !preg_match('/^\d{2}:\d{2}$/', $e) || $e <= $s) {
            Session::flash('err', 'بازه‌ی زمانی نامعتبر است.');
        } else {
            try { DB::insert('working_hours', ['weekday' => $wd, 'start_time' => $s, 'end_time' => $e]); Session::flash('ok', 'بازه اضافه شد.'); }
            catch (PDOException $ex) { Session::flash('err', 'این بازه قبلاً ثبت شده است.'); }
        }
        return redirect(url('/admin/hours/'));
    }

    public function hourDelete(Request $req, string $id): Response
    {
        if ($g = $this->guard()) return $g;
        DB::exec('DELETE FROM working_hours WHERE id = ?', [(int) $id]);
        return redirect(url('/admin/hours/'));
    }

    public function holidaySave(Request $req): Response
    {
        if ($g = $this->guard()) return $g;
        $j = Jalali::parse(en_digits(str_replace('/', '-', trim($req->input('date')))));
        if (!$j) Session::flash('err', 'تاریخ نامعتبر است (نمونه: ۱۴۰۴-۰۱-۱۳).');
        else {
            try { DB::insert('holidays', ['date' => Jalali::iso(...$j), 'reason' => mb_substr(trim($req->input('reason')), 0, 140)]); Session::flash('ok', 'تعطیلی ثبت شد.'); }
            catch (PDOException $ex) { Session::flash('err', 'این تاریخ قبلاً ثبت شده است.'); }
        }
        return redirect(url('/admin/hours/'));
    }

    public function holidayDelete(Request $req, string $id): Response
    {
        if ($g = $this->guard()) return $g;
        DB::exec('DELETE FROM holidays WHERE id = ?', [(int) $id]);
        return redirect(url('/admin/hours/'));
    }

    // ------------------------------------------------------------ سوالات متداول
    public function faqs(Request $req): Response
    {
        if ($g = $this->guard()) return $g;
        $edit = $req->q('edit') ? DB::one('SELECT * FROM faqs WHERE id = ?', [(int) $req->q('edit')]) : null;
        return $this->page('faqs', ['items' => DB::all('SELECT * FROM faqs ORDER BY display_order, created_at DESC'), 'edit' => $edit], 'faqs');
    }

    public function faqSave(Request $req): Response
    {
        if ($g = $this->guard()) return $g;
        $id = (int) $req->input('id');
        $q = mb_substr(trim($req->input('question')), 0, 255); $a = trim($req->input('answer'));
        if ($q === '' || $a === '') { Session::flash('err', 'پرسش و پاسخ الزامی است.'); return redirect(url('/admin/faqs/')); }
        $data = ['question' => $q, 'answer' => $a, 'display_order' => (int) $req->input('display_order'), 'is_active' => $req->input('is_active') ? 1 : 0];
        if ($id) DB::update('faqs', $data, 'id = ?', [$id]); else DB::insert('faqs', $data);
        Session::flash('ok', 'ذخیره شد.');
        return redirect(url('/admin/faqs/'));
    }

    public function faqDelete(Request $req, string $id): Response
    {
        if ($g = $this->guard()) return $g;
        DB::exec('DELETE FROM faqs WHERE id = ?', [(int) $id]);
        return redirect(url('/admin/faqs/'));
    }

    // ------------------------------------------------------------ گالری قبل/بعد
    public function gallery(Request $req): Response
    {
        if ($g = $this->guard()) return $g;
        $edit = $req->q('edit') ? DB::one('SELECT * FROM before_after WHERE id = ?', [(int) $req->q('edit')]) : null;
        return $this->page('gallery', ['items' => DB::all('SELECT * FROM before_after ORDER BY display_order, created_at DESC'), 'edit' => $edit,
            'max_upload' => ini_get('upload_max_filesize')], 'gallery');
    }

    public function gallerySave(Request $req): Response
    {
        if ($g = $this->guard()) return $g;
        $id = (int) $req->input('id');
        $title = mb_substr(trim($req->input('title')), 0, 120);
        if ($title === '') { Session::flash('err', 'عنوان الزامی است.'); return redirect(url('/admin/gallery/')); }
        $data = ['title' => $title, 'description' => mb_substr(trim($req->input('description')), 0, 250),
            'display_order' => (int) $req->input('display_order'), 'is_active' => $req->input('is_active') ? 1 : 0];
        try {
            foreach (['before_image', 'after_image'] as $f) {
                if ($path = $this->upload($req, $f)) $data[$f] = $path;
            }
        } catch (RuntimeException $e) { Session::flash('err', $e->getMessage()); return redirect(url('/admin/gallery/')); }
        if ($id) DB::update('before_after', $data, 'id = ?', [$id]);
        else {
            if (empty($data['before_image']) || empty($data['after_image'])) { Session::flash('err', 'هر دو تصویر قبل و بعد الزامی است.'); return redirect(url('/admin/gallery/')); }
            DB::insert('before_after', $data);
        }
        Session::flash('ok', 'ذخیره شد.');
        return redirect(url('/admin/gallery/'));
    }

    public function galleryDelete(Request $req, string $id): Response
    {
        if ($g = $this->guard()) return $g;
        $row = DB::one('SELECT * FROM before_after WHERE id = ?', [(int) $id]);
        if ($row) {
            foreach (['before_image', 'after_image'] as $f) {
                $p = BASE_PATH . '/public' . $row[$f];
                if (str_starts_with($row[$f], '/media/') && is_file($p)) @unlink($p);
            }
            DB::exec('DELETE FROM before_after WHERE id = ?', [(int) $id]);
        }
        return redirect(url('/admin/gallery/'));
    }

    /** آپلود امن تصویر؛ مسیر وب (/media/…) را برمی‌گرداند یا null اگر فایلی ارسال نشده. */
    private function upload(Request $req, string $field): ?string
    {
        $f = $req->files[$field] ?? null;
        if (!$f || ($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
        if ($f['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('خطا در آپلود فایل (حجم بیش از حد مجاز؟).');
        if ($f['size'] > 5 * 1024 * 1024) throw new RuntimeException('حجم تصویر حداکثر ۵ مگابایت باشد.');
        $info = @getimagesize($f['tmp_name']);
        $ext = match ($info['mime'] ?? '') { 'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', default => null };
        if (!$ext) throw new RuntimeException('فقط تصاویر JPG، PNG یا WebP مجاز است.');
        $dir = BASE_PATH . '/public/media/before_after';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $name = date('Ymd') . '-' . bin2hex(random_bytes(6)) . '.' . $ext;
        if (!move_uploaded_file($f['tmp_name'], "$dir/$name")) throw new RuntimeException('ذخیره‌ی تصویر ممکن نشد.');
        return "/media/before_after/$name";
    }

    // ------------------------------------------------------------ پیام‌های تماس
    public function messages(Request $req): Response
    {
        if ($g = $this->guard()) return $g;
        return $this->page('messages', ['items' => DB::all('SELECT * FROM contact_messages ORDER BY is_read, created_at DESC LIMIT 300')], 'messages');
    }

    public function messageRead(Request $req, string $id): Response
    {
        if ($g = $this->guard()) return $g;
        DB::update('contact_messages', ['is_read' => 1], 'id = ?', [(int) $id]);
        return redirect(url('/admin/messages/'));
    }

    public function messageDelete(Request $req, string $id): Response
    {
        if ($g = $this->guard()) return $g;
        DB::exec('DELETE FROM contact_messages WHERE id = ?', [(int) $id]);
        return redirect(url('/admin/messages/'));
    }

    // ------------------------------------------------------------ مشتریان
    public function customers(Request $req): Response
    {
        if ($g = $this->guard()) return $g;
        $q = trim($req->q('q', '')); $p = [];
        $sql = 'SELECT c.*, (SELECT COUNT(*) FROM appointments a WHERE a.customer_id = c.id) AS n FROM customers c';
        if ($q !== '') { $sql .= ' WHERE first_name LIKE ? OR last_name LIKE ? OR mobile LIKE ?'; $like = '%' . en_digits($q) . '%'; $p = [$like, $like, $like]; }
        $sql .= ' ORDER BY created_at DESC LIMIT 300';
        return $this->page('customers', ['items' => DB::all($sql, $p), 'q' => $q], 'customers');
    }

    public function customerToggleBlock(Request $req, string $id): Response
    {
        if ($g = $this->guard()) return $g;
        DB::exec('UPDATE customers SET is_blocked = 1 - is_blocked, updated_at = CURRENT_TIMESTAMP WHERE id = ?', [(int) $id]);
        return redirect(url('/admin/customers/'));
    }

    // ------------------------------------------------------------ کاربران پنل
    public function users(Request $req): Response
    {
        if ($g = $this->guard()) return $g;
        return $this->page('users', ['items' => DB::all('SELECT id, username, is_active, created_at FROM users ORDER BY id')], 'users');
    }

    public function userSave(Request $req): Response
    {
        if ($g = $this->guard()) return $g;
        $id = (int) $req->input('id'); $u = trim((string) $req->input('username')); $p = (string) $req->input('password');
        if (!preg_match('/^[A-Za-z0-9_.-]{3,40}$/', $u)) { Session::flash('err', 'نام کاربری نامعتبر است (۳ تا ۴۰ کاراکتر انگلیسی).'); return redirect(url('/admin/users/')); }
        if (!$id && mb_strlen($p) < 8) { Session::flash('err', 'رمز عبور حداقل ۸ کاراکتر باشد.'); return redirect(url('/admin/users/')); }
        $data = ['username' => $u];
        if ($p !== '') { if (mb_strlen($p) < 8) { Session::flash('err', 'رمز عبور حداقل ۸ کاراکتر باشد.'); return redirect(url('/admin/users/')); } $data['password_hash'] = password_hash($p, PASSWORD_DEFAULT); }
        try {
            if ($id) DB::update('users', $data, 'id = ?', [$id]); else DB::insert('users', $data);
            Session::flash('ok', 'ذخیره شد.');
        } catch (PDOException $e) { Session::flash('err', 'این نام کاربری قبلاً استفاده شده است.'); }
        return redirect(url('/admin/users/'));
    }

    public function userDelete(Request $req, string $id): Response
    {
        if ($g = $this->guard()) return $g;
        if ((int) $id === (int) Auth::user()['id']) Session::flash('err', 'نمی‌توانید حساب خودتان را حذف کنید.');
        else { DB::exec('DELETE FROM users WHERE id = ?', [(int) $id]); Session::flash('ok', 'حذف شد.'); }
        return redirect(url('/admin/users/'));
    }

    // ------------------------------------------------------------ وارد کردن داده از جنگو
    public function importForm(Request $req): Response
    {
        if ($g = $this->guard()) return $g;
        return $this->page('import', [
            'counts' => ['appointments' => DB::val('SELECT COUNT(*) FROM appointments'), 'customers' => DB::val('SELECT COUNT(*) FROM customers')],
            'result' => Session::flash('import_result'),
        ], 'import');
    }

    public function importRun(Request $req): Response
    {
        if ($g = $this->guard()) return $g;
        $f = $req->files['file'] ?? null;
        if (!$f || ($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) { Session::flash('err', 'فایل JSON انتخاب نشده یا آپلود ناموفق بود.'); return redirect(url('/admin/import/')); }
        $data = json_decode((string) file_get_contents($f['tmp_name']), true);
        if (!is_array($data) || ($data['version'] ?? 0) !== 1) { Session::flash('err', 'فایل معتبر نیست (باید خروجی export_for_php باشد).'); return redirect(url('/admin/import/')); }
        try {
            $result = Importer::run($data, (bool) $req->input('wipe'));
            Session::flash('import_result', $result);
            Session::flash('ok', 'داده‌ها با موفقیت وارد شدند.');
        } catch (Throwable $e) {
            error_log($e);
            Session::flash('err', 'خطا در وارد کردن: ' . $e->getMessage());
        }
        return redirect(url('/admin/import/'));
    }

    // ------------------------------------------------------------ بکاپ
    public function backup(Request $req): Response
    {
        if ($g = $this->guard()) return $g;
        $tmp = tempnam(sys_get_temp_dir(), 'bk');
        DB::pdo()->exec("VACUUM INTO '" . str_replace("'", "''", $tmp) . "'");
        $data = file_get_contents($tmp); @unlink($tmp);
        return new Response($data, 200, [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="clinic-backup-' . date('Ymd-His') . '.sqlite"',
        ]);
    }
}
