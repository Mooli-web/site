<?php
/**
 * ویوهای بخش عمومی سایت + فلوی رزرو (پورت apps/website/views.py)
 */
final class WebsiteController
{
    private const ICON = [
        'skin' => '<svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M9 10h.01M15 10h.01M9 15c1 1 5 1 6 0"/></svg>',
        'laser' => '<svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M13 2L3 14h7l-1 8 10-12h-7l1-8z"/></svg>',
        'hair' => '<svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4c4 4 4 12 0 16M12 4c4 4 4 12 0 16M20 4c-4 4-4 12 0 16"/></svg>',
        'nail' => '<svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><rect x="8" y="3" width="8" height="18" rx="4"/><path stroke-linecap="round" d="M8 9h8"/></svg>',
    ];

    public function home(Request $req): Response
    {
        $features = [
            ['title' => 'مراقبت پوست', 'desc' => 'پاکسازی، آبرسانی و جوان‌سازی پوست با جدیدترین متدهای روز دنیا.', 'icon' => self::ICON['skin'], 'color' => 'from-blush-400 to-blush-500'],
            ['title' => 'لیزر و زیبایی', 'desc' => 'حذف موهای زائد و درمان‌های لیزری ایمن و مؤثر زیر نظر متخصص.', 'icon' => self::ICON['laser'], 'color' => 'from-sand-400 to-sand-500'],
            ['title' => 'خدمات مو', 'desc' => 'رنگ، کراتینه، کوتاهی و مراقبت تخصصی مو توسط استایلیست‌های حرفه‌ای.', 'icon' => self::ICON['hair'], 'color' => 'from-blush-400 to-sand-400'],
            ['title' => 'ناخن و میکاپ', 'desc' => 'مانیکور، پدیکور و میکاپ تخصصی برای روزهای خاص شما.', 'icon' => self::ICON['nail'], 'color' => 'from-sand-400 to-blush-400'],
        ];
        $reasons = [
            ['title' => 'تیم متخصص و مجرب', 'desc' => 'کادری از بهترین متخصصان حوزه پوست، مو و زیبایی در کنار شما.'],
            ['title' => 'تجهیزات مدرن و بهداشتی', 'desc' => 'استفاده از به‌روزترین دستگاه‌ها با رعایت کامل اصول بهداشتی.'],
            ['title' => 'رزرو آنلاین بدون ثبت‌نام', 'desc' => 'تنها با نام و شماره موبایل، در چند ثانیه نوبت خود را رزرو کنید.'],
            ['title' => 'محیطی آرام و لوکس', 'desc' => 'فضایی دلنشین برای اینکه لحظات مراقبت از خود را به‌آرامی سپری کنید.'],
        ];
        return html(render('website/home', compact('features', 'reasons')));
    }

    public function about(Request $req): Response
    {
        $values = [
            ['title' => 'تخصص و تجربه', 'desc' => 'بیش از یک دهه تجربه در حوزه‌ی پوست، مو و زیبایی با کادری مجرب.'],
            ['title' => 'بهداشت و ایمنی', 'desc' => 'رعایت کامل پروتکل‌های بهداشتی و استفاده از تجهیزات استریل.'],
            ['title' => 'رضایت مشتری', 'desc' => 'تمرکز ما بر آرامش، اعتماد و رضایت کامل مراجعان است.'],
            ['title' => 'فناوری روز', 'desc' => 'بهره‌گیری از به‌روزترین دستگاه‌ها و متدهای علمی دنیا.'],
        ];
        $stats = [
            ['num' => '۱۲+', 'label' => 'سال تجربه'], ['num' => '۲۰٬۰۰۰+', 'label' => 'مشتری راضی'],
            ['num' => '۱۵+', 'label' => 'متخصص حرفه‌ای'], ['num' => '۴۰+', 'label' => 'خدمت تخصصی'],
        ];
        return html(render('website/about', compact('values', 'stats')));
    }

    // ------------------------------------------------------------ تماس با ما
    public function contact(Request $req): Response
    {
        return html(render('website/contact', ['form' => [], 'errors' => []]));
    }

    public function contactSubmit(Request $req): Response
    {
        if (!RateLimit::hit('contact', $req->ip())) return $this->rateLimited();
        $f = ['name' => trim($req->input('name')), 'mobile' => trim($req->input('mobile')),
              'subject' => trim($req->input('subject')), 'message' => trim($req->input('message'))];
        $errors = [];
        if ($f['name'] === '') $errors['name'] = 'وارد کردن نام الزامی است.';
        if (mb_strlen($f['name']) > 120) $errors['name'] = 'نام بیش از حد طولانی است.';
        $mobile = normalize_mobile($f['mobile']);
        if (!$mobile) $errors['mobile'] = 'شماره موبایل باید با ۰۹ شروع شده و ۱۱ رقم باشد.';
        if ($f['message'] === '') $errors['message'] = 'لطفاً متن پیام را وارد کنید.';
        if (mb_strlen($f['message']) > 2000) $errors['message'] = 'پیام بیش از حد طولانی است.';
        if ($errors) return html(view('website/partials/contact_form', ['form' => $f, 'errors' => $errors]));

        DB::insert('contact_messages', ['name' => $f['name'], 'mobile' => $mobile, 'subject' => mb_substr($f['subject'], 0, 150), 'message' => $f['message']]);
        return html(view('website/partials/contact_success', ['name' => $f['name']]));
    }

    // ------------------------------------------------------------ گالری / سوالات
    public function beforeAfter(Request $req): Response
    {
        $items = DB::all('SELECT * FROM before_after WHERE is_active = 1 ORDER BY display_order, created_at DESC');
        return html(render('website/before_after', compact('items')));
    }

    public function faq(Request $req): Response
    {
        $faqs = DB::all('SELECT * FROM faqs WHERE is_active = 1 ORDER BY display_order, created_at DESC');
        return html(render('website/faq', compact('faqs')));
    }

    // ------------------------------------------------------------ مشاوره
    public function consultation(Request $req): Response
    {
        return html(render('website/consultation', ['form' => [], 'errors' => []]));
    }

    public function consultationSubmit(Request $req): Response
    {
        if (!RateLimit::hit('consultation', $req->ip())) return $this->rateLimited();
        $f = ['first_name' => trim($req->input('first_name')), 'last_name' => trim($req->input('last_name')),
              'mobile' => trim($req->input('mobile')), 'message' => trim($req->input('message'))];
        $errors = [];
        if ($f['first_name'] === '') $errors['first_name'] = 'وارد کردن نام الزامی است.';
        if ($f['last_name'] === '') $errors['last_name'] = 'وارد کردن نام خانوادگی الزامی است.';
        $mobile = normalize_mobile($f['mobile']);
        if (!$mobile) $errors['mobile'] = 'شماره موبایل باید با ۰۹ شروع شده و ۱۱ رقم باشد.';
        if ($errors) return html(view('website/partials/consultation_form', ['form' => $f, 'errors' => $errors]));

        DB::insert('consultation_requests', ['first_name' => mb_substr($f['first_name'], 0, 60), 'last_name' => mb_substr($f['last_name'], 0, 60),
            'mobile' => $mobile, 'message' => mb_substr($f['message'], 0, 1000)]);
        return html(view('website/partials/consultation_success', ['name' => $f['first_name']]));
    }

    // ------------------------------------------------------------ فلوی رزرو
    public function booking(Request $req): Response
    {
        return html(render('website/booking', ['categories' => Models::categoriesWithServices()]));
    }

    public function bookingCalendar(Request $req): Response
    {
        $service = Models::service((int) $req->q('service'));
        if (!$service) return not_found();
        $clinic = Models::clinic();
        $today = Jalali::today();
        $max = Jalali::addDays($today, (int) $clinic['max_advance_days']);
        $year = (int) $req->q('year', $today[0]);
        $month = (int) $req->q('month', $today[1]);
        if ($month < 1 || $month > 12 || $year < 1300 || $year > 1500) [$year, $month] = [$today[0], $today[1]];
        $month = Models::buildMonth($year, $month, $today, $today, $max);
        return html(view('website/partials/step_calendar', compact('service', 'month')));
    }

    public function bookingSlots(Request $req): Response
    {
        $service = Models::service((int) $req->q('service'));
        $j = Jalali::parse($req->q('date'));
        if (!$service || !$j) return not_found('تاریخ نامعتبر است.');
        return html(view('website/partials/step_slots', [
            'service' => $service, 'date_iso' => Jalali::iso(...$j),
            'pretty_date' => Jalali::pretty($j, true), 'slots' => Models::availableSlots($service, $j),
        ]));
    }

    public function bookingConfirm(Request $req): Response
    {
        $service = Models::service((int) $req->q('service'));
        $j = Jalali::parse($req->q('date'));
        if (!$service || !$j) return not_found('تاریخ نامعتبر است.');
        $start = preg_match('/^\d{2}:\d{2}$/', $req->q('start')) ? $req->q('start') : '';
        return html(view('website/partials/step_form', [
            'service' => $service, 'date_iso' => Jalali::iso(...$j), 'start' => $start,
            'start_fa' => fa_digits($start), 'pretty_date' => Jalali::pretty($j),
            'form' => [], 'errors' => [],
        ]));
    }

    public function bookingSubmit(Request $req): Response
    {
        if (!RateLimit::hit('booking', $req->ip())) return $this->rateLimited();
        $f = [
            'first_name' => trim($req->input('first_name')), 'last_name' => trim($req->input('last_name')),
            'mobile' => trim($req->input('mobile')), 'customer_note' => mb_substr(trim($req->input('customer_note')), 0, 500),
            'service' => (int) $req->input('service'), 'date' => trim($req->input('date')), 'start' => trim($req->input('start')),
        ];
        $errors = [];
        if ($f['first_name'] === '') $errors['first_name'] = 'وارد کردن نام الزامی است.';
        if ($f['last_name'] === '') $errors['last_name'] = 'وارد کردن نام خانوادگی الزامی است.';
        $mobile = normalize_mobile($f['mobile']);
        if (!$mobile) $errors['mobile'] = 'شماره موبایل باید با ۰۹ شروع شده و ۱۱ رقم باشد.';
        $service = Models::service($f['service']);
        $j = Jalali::parse($f['date']);
        if (!$service) $errors['_'] = 'خدمت نامعتبر است.';
        elseif (!$j) $errors['_'] = 'تاریخ نامعتبر است.';

        if (!$errors) {
            try {
                $appt = Models::createAppointment(mb_substr($f['first_name'], 0, 60), mb_substr($f['last_name'], 0, 60), $mobile, $service, $j, $f['start'], $f['customer_note']);
                return hx_redirect(url('/payment/' . $appt['tracking_code'] . '/'));
            } catch (BookingError $e) {
                $errors['_'] = $e->getMessage();
            }
        }
        return html(view('website/partials/step_form', [
            'service' => $service ?: ['id' => $f['service'], 'title' => '', 'duration_minutes' => 0, 'price' => 0],
            'date_iso' => $f['date'], 'start' => $f['start'], 'start_fa' => fa_digits($f['start']),
            'pretty_date' => $j ? Jalali::pretty($j) : '', 'form' => $f, 'errors' => $errors,
        ]));
    }

    private function rateLimited(): Response
    {
        return html(view('website/partials/rate_limited', ['window_minutes' => 10]), 429);
    }
}
