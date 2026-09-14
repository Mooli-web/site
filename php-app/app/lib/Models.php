<?php
/**
 * دسترسی به داده‌ها + منطق دامنه‌ی نوبت‌دهی (پورت apps/appointments/services.py).
 */

final class BookingError extends RuntimeException {}

final class Models
{
    public const APPT_STATUS = [
        'waiting_payment' => 'در انتظار پرداخت',
        'paid' => 'پرداخت شده',
        'payment_failed' => 'پرداخت ناموفق',
        'pending' => 'در انتظار تأیید',
        'confirmed' => 'تأیید شده',
        'done' => 'انجام شده',
        'cancelled' => 'لغو شده',
        'no_show' => 'عدم حضور',
    ];
    public const CONS_STATUS = [
        'new' => 'تماس گرفته نشده',
        'in_progress' => 'در حال پیگیری',
        'done' => 'انجام شده',
    ];

    // ---------------------------------------------------------------- //
    public static function clinic(): array
    {
        return DB::one('SELECT * FROM clinic_settings WHERE id = 1') ?? ['name' => 'کلینیک زیبایی', 'phone' => '', 'address' => '', 'about' => '', 'instagram' => '',
            'slot_step_minutes' => 30, 'max_advance_days' => 30, 'min_advance_hours' => 2];
    }

    public static function categoriesWithServices(): array
    {
        $cats = DB::all('SELECT * FROM service_categories WHERE is_active = 1 ORDER BY "order", title');
        $services = DB::all('SELECT * FROM services WHERE is_active = 1 ORDER BY "order", title');
        $byCat = [];
        foreach ($services as $s) $byCat[$s['category_id']][] = $s;
        foreach ($cats as &$c) $c['services'] = $byCat[$c['id']] ?? [];
        return $cats;
    }

    public static function service(int $id): ?array
    {
        return DB::one('SELECT * FROM services WHERE id = ? AND is_active = 1', [$id]);
    }

    public static function appointment(int $id): ?array
    {
        return self::hydrate(DB::one('SELECT * FROM appointments WHERE id = ?', [$id]));
    }

    public static function appointmentByCode(string $code): ?array
    {
        return self::hydrate(DB::one('SELECT * FROM appointments WHERE tracking_code = ?', [$code]));
    }

    /** الحاق اطلاعات مشتری و خدمت به نوبت */
    public static function hydrate(?array $a): ?array
    {
        if (!$a) return null;
        $a['customer'] = DB::one('SELECT * FROM customers WHERE id = ?', [$a['customer_id']]) ?? [];
        $a['customer']['full_name'] = trim(($a['customer']['first_name'] ?? '') . ' ' . ($a['customer']['last_name'] ?? ''));
        $a['service'] = DB::one('SELECT * FROM services WHERE id = ?', [$a['service_id']]) ?? [];
        $a['status_label'] = self::APPT_STATUS[$a['status']] ?? $a['status'];
        $a['is_paid'] = $a['status'] === 'paid';
        $a['date_fa'] = Jalali::numeric($a['date']);
        return $a;
    }

    // ---------------------------------------------------------------- //
    // اسلات‌ها
    // ---------------------------------------------------------------- //
    private static function addMinutes(string $t, int $m): string
    {
        [$h, $i] = array_map('intval', explode(':', $t));
        $total = $h * 60 + $i + $m;
        return sprintf('%02d:%02d', intdiv($total, 60), $total % 60);
    }

    public static function isHoliday(array $j): bool
    {
        return (bool) DB::val('SELECT COUNT(*) FROM holidays WHERE date = ?', [Jalali::iso(...$j)]);
    }

    /**
     * اسلات‌های خالی کلینیک برای یک خدمت در یک روز شمسی.
     * @return array<int, array{start:string,end:string}>
     */
    public static function availableSlots(array $service, array $j, ?array $clinic = null): array
    {
        $clinic ??= self::clinic();
        if (self::isHoliday($j)) return [];

        $weekday = Jalali::weekday($j);
        $working = DB::all('SELECT * FROM working_hours WHERE weekday = ? AND is_active = 1 ORDER BY start_time', [$weekday]);
        if (!$working) return [];

        $duration = (int) $service['duration_minutes'];
        $step = max((int) $clinic['slot_step_minutes'], 5);

        $busy = DB::all("SELECT start_time, end_time FROM appointments WHERE date = ? AND status NOT IN ('cancelled','payment_failed')", [Jalali::iso(...$j)]);
        $earliest = (new DateTimeImmutable())->modify('+' . (int) $clinic['min_advance_hours'] . ' hours');

        $slots = [];
        foreach ($working as $wh) {
            $cursor = substr($wh['start_time'], 0, 5);
            $whEnd = substr($wh['end_time'], 0, 5);
            while (true) {
                $slotEnd = self::addMinutes($cursor, $duration);
                if ($slotEnd > $whEnd) break;
                if (Jalali::toDateTime($j, $cursor) < $earliest) { $cursor = self::addMinutes($cursor, $step); continue; }
                $overlaps = false;
                foreach ($busy as $b) {
                    if ($cursor < substr($b['end_time'], 0, 5) && $slotEnd > substr($b['start_time'], 0, 5)) { $overlaps = true; break; }
                }
                if (!$overlaps) $slots[] = ['start' => $cursor, 'end' => $slotEnd];
                $cursor = self::addMinutes($cursor, $step);
            }
        }
        return $slots;
    }

    /** ساختار یک ماه شمسی برای رندر تقویم (پورت calendar_utils.build_month) */
    public static function buildMonth(int $year, int $month, array $today, array $min, array $max): array
    {
        $first = [$year, $month, 1];
        $startWd = Jalali::weekday($first);
        $total = Jalali::monthLength($year, $month);
        $cells = array_fill(0, $startWd, null);
        $todayIso = Jalali::iso(...$today);
        $minIso = Jalali::iso(...$min);
        $maxIso = Jalali::iso(...$max);
        for ($d = 1; $d <= $total; $d++) {
            $iso = Jalali::iso($year, $month, $d);
            $cells[] = [
                'day' => $d, 'day_fa' => fa_digits($d), 'iso' => $iso,
                'is_today' => $iso === $todayIso, 'is_past' => $iso < $todayIso,
                'selectable' => $iso >= $minIso && $iso <= $maxIso,
            ];
        }
        while (count($cells) % 7 !== 0) $cells[] = null;
        [$py, $pm] = $month > 1 ? [$year, $month - 1] : [$year - 1, 12];
        [$ny, $nm] = $month < 12 ? [$year, $month + 1] : [$year + 1, 1];
        return [
            'year' => $year, 'month' => $month, 'month_name' => Jalali::MONTHS[$month - 1],
            'year_fa' => fa_digits($year), 'headers' => Jalali::WEEK_HEADERS,
            'weeks' => array_chunk($cells, 7),
            'prev' => ['year' => $py, 'month' => $pm, 'enabled' => [$py, $pm] >= [$min[0], $min[1]]],
            'next' => ['year' => $ny, 'month' => $nm, 'enabled' => [$ny, $nm] <= [$max[0], $max[1]]],
        ];
    }

    // ---------------------------------------------------------------- //
    // ثبت نوبت
    // ---------------------------------------------------------------- //
    public static function createAppointment(string $first, string $last, string $mobile, array $service, array $j, string $start, string $note = ''): array
    {
        return DB::tx(function () use ($first, $last, $mobile, $service, $j, $start, $note) {
            $clinic = self::clinic();
            $available = [];
            foreach (self::availableSlots($service, $j, $clinic) as $s) $available[$s['start']] = $s;
            if (!isset($available[$start])) {
                throw new BookingError('این زمان دیگر در دسترس نیست. لطفاً زمان دیگری انتخاب کنید.');
            }
            $slot = $available[$start];

            $customer = DB::one('SELECT * FROM customers WHERE mobile = ?', [$mobile]);
            if (!$customer) {
                $cid = DB::insert('customers', ['first_name' => $first, 'last_name' => $last, 'mobile' => $mobile]);
                $customer = ['id' => $cid, 'is_blocked' => 0];
            } elseif ($customer['first_name'] !== $first || $customer['last_name'] !== $last) {
                DB::update('customers', ['first_name' => $first, 'last_name' => $last], 'id = ?', [$customer['id']]);
            }
            if (!empty($customer['is_blocked'])) {
                throw new BookingError('امکان رزرو برای این شماره وجود ندارد. لطفاً با کلینیک تماس بگیرید.');
            }

            try {
                $id = DB::insert('appointments', [
                    'customer_id' => $customer['id'], 'service_id' => $service['id'],
                    'date' => Jalali::iso(...$j), 'start_time' => $slot['start'], 'end_time' => $slot['end'],
                    'price' => (int) $service['price'], 'customer_note' => $note,
                    'status' => 'waiting_payment', 'tracking_code' => self::trackingCode(),
                ]);
            } catch (PDOException $e) {
                throw new BookingError('متأسفانه این زمان همین‌الان رزرو شد. لطفاً زمان دیگری انتخاب کنید.');
            }
            return self::appointment($id);
        });
    }

    private static function trackingCode(): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        do {
            $c = 'BC';
            for ($i = 0; $i < 8; $i++) $c .= $alphabet[random_int(0, 35)];
        } while (DB::val('SELECT COUNT(*) FROM appointments WHERE tracking_code = ?', [$c]));
        return $c;
    }

    // ---------------------------------------------------------------- //
    // داده‌ی نمونه
    // ---------------------------------------------------------------- //
    public static function seedDemo(): void
    {
        DB::update('clinic_settings', [
            'name' => 'کلینیک زیبایی رُز', 'phone' => '۰۲۱-۸۸۸۸۸۸۸۸',
            'address' => 'تهران، خیابان ولیعصر، نبش کوچه بهار، پلاک ۱۲۰',
            'about' => 'ارائه خدمات تخصصی پوست، مو و زیبایی با جدیدترین تجهیزات روز دنیا.',
            'instagram' => '@rose.clinic',
        ], 'id = 1');
        $data = [
            'پوست و زیبایی' => [['پاکسازی پوست', 45, 350000], ['جوان‌سازی صورت', 60, 800000], ['میکرونیدلینگ', 60, 1200000]],
            'لیزر' => [['لیزر موهای زائد (نواحی کوچک)', 30, 250000], ['لیزر موهای زائد (کل بدن)', 90, 1500000]],
            'مو' => [['کوتاهی و حالت‌دهی', 45, 200000], ['رنگ و مش', 120, 950000], ['کراتینه', 120, 1800000]],
            'ناخن و میکاپ' => [['مانیکور', 45, 300000], ['میکاپ عروس', 120, 2500000]],
        ];
        $order = 0;
        foreach ($data as $cat => $services) {
            $cid = DB::val('SELECT id FROM service_categories WHERE title = ?', [$cat]);
            if (!$cid) $cid = DB::insert('service_categories', ['title' => $cat, 'slug' => slugify($cat), 'order' => $order]);
            $order++;
            foreach ($services as $i => [$title, $dur, $price]) {
                if (DB::val('SELECT COUNT(*) FROM services WHERE title = ?', [$title])) continue;
                DB::insert('services', ['category_id' => $cid, 'title' => $title, 'slug' => slugify($title),
                    'duration_minutes' => $dur, 'price' => $price, 'order' => $i]);
            }
        }
        if (!DB::val('SELECT COUNT(*) FROM working_hours')) {
            foreach ([0, 1, 2, 3, 4] as $wd) {
                DB::insert('working_hours', ['weekday' => $wd, 'start_time' => '09:00', 'end_time' => '13:00']);
                DB::insert('working_hours', ['weekday' => $wd, 'start_time' => '15:00', 'end_time' => '18:00']);
            }
        }
        if (!DB::val('SELECT COUNT(*) FROM faqs')) {
            $faqs = [
                ['آیا برای رزرو نوبت نیاز به ثبت‌نام دارم؟', 'خیر؛ تنها با وارد کردن نام و شماره موبایل می‌توانید نوبت بگیرید.'],
                ['چطور می‌توانم نوبتم را لغو یا جابه‌جا کنم؟', 'کافی است با شماره‌ی کلینیک تماس بگیرید و کد پیگیری خود را اعلام کنید.'],
                ['آیا پرداخت آنلاین امن است؟', 'بله؛ پرداخت از طریق درگاه رسمی زرین‌پال انجام می‌شود.'],
            ];
            foreach ($faqs as $i => [$q, $a]) DB::insert('faqs', ['question' => $q, 'answer' => $a, 'display_order' => $i]);
        }
    }
}

/** کلاینت REST v4 زرین‌پال (پورت apps/payments/zarinpal.py) */
final class ZarinPal
{
    private string $merchant;
    private bool $sandbox;
    private string $base;

    public function __construct()
    {
        $this->merchant = (string) config('ZARINPAL_MERCHANT_ID', '00000000-0000-0000-0000-000000000000');
        $this->sandbox = (bool) config('ZARINPAL_SANDBOX', true);
        $this->base = $this->sandbox ? 'https://sandbox.zarinpal.com' : 'https://payment.zarinpal.com';
    }

    public function isSandbox(): bool { return $this->sandbox; }

    /** @return array{authority:string,pay_url:string} */
    public function request(int $amount, string $description, string $callback, string $mobile = ''): array
    {
        $payload = [
            'merchant_id' => $this->merchant, 'amount' => $amount, 'currency' => 'IRT',
            'description' => $description, 'callback_url' => $callback,
            'metadata' => $mobile ? ['mobile' => $mobile] : new stdClass(),
        ];
        $data = $this->post('/pg/v4/payment/request.json', $payload);
        $result = $data['data'] ?? [];
        if (empty($result['authority']) || (int) ($result['code'] ?? 0) !== 100) {
            throw new RuntimeException('دریافت کد پرداخت از درگاه ناموفق بود.');
        }
        return ['authority' => $result['authority'], 'pay_url' => $this->base . '/pg/StartPay/' . $result['authority']];
    }

    /** @return array{ok:bool,ref_id:?string,code:int,message:string} */
    public function verify(int $amount, string $authority): array
    {
        $data = $this->post('/pg/v4/payment/verify.json', [
            'merchant_id' => $this->merchant, 'amount' => $amount, 'authority' => $authority,
        ], allowErrors: true);
        $result = $data['data'] ?? [];
        $code = (int) ($result['code'] ?? 0);
        if (!$code && !empty($data['errors'])) {
            $errs = $data['errors'];
            $code = (int) (is_array($errs) ? ($errs['code'] ?? 0) : 0);
            $msg = is_array($errs) ? ($errs['message'] ?? 'خطای نامشخص') : (string) $errs;
        } else {
            $msg = (string) ($result['message'] ?? '');
        }
        return ['ok' => in_array($code, [100, 101], true), 'ref_id' => isset($result['ref_id']) ? (string) $result['ref_id'] : null, 'code' => $code, 'message' => $msg];
    }

    private function post(string $path, array $payload, bool $allowErrors = false): array
    {
        // حالت شبیه‌سازی برای تست محلی بدون اینترنت (ZARINPAL_FAKE=true) — هرگز در سرور واقعی فعال نکنید
        if (config('ZARINPAL_FAKE', false)) {
            return str_contains($path, 'request')
                ? ['data' => ['code' => 100, 'authority' => 'A' . str_pad((string) time(), 35, '0', STR_PAD_LEFT)], 'errors' => []]
                : ['data' => ['code' => 100, 'ref_id' => random_int(100000, 999999), 'message' => 'Verified'], 'errors' => []];
        }
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
        $url = $this->base . $path;
        $raw = false; $err = '';
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST => true, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
                CURLOPT_POSTFIELDS => $json,
            ]);
            $raw = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);
        } else {
            $ctx = stream_context_create(['http' => ['method' => 'POST', 'header' => "Content-Type: application/json\r\nAccept: application/json\r\n", 'content' => $json, 'timeout' => 15, 'ignore_errors' => true]]);
            $raw = @file_get_contents($url, false, $ctx);
            $err = $raw === false ? (error_get_last()['message'] ?? 'unknown') : '';
        }
        if ($raw === false) throw new RuntimeException('ارتباط با درگاه برقرار نشد: ' . $err);
        $data = json_decode($raw, true);
        if (!is_array($data)) throw new RuntimeException('پاسخ نامعتبر از درگاه دریافت شد.');
        if (!$allowErrors && !empty($data['errors'])) {
            $errs = $data['errors'];
            throw new RuntimeException(is_array($errs) ? ($errs['message'] ?? 'خطای نامشخص از درگاه پرداخت.') : (string) $errs);
        }
        return $data;
    }
}
