<?php
/**
 * پنل منشی/مدیریت نوبت‌ها (پورت apps/dashboard/views.py)
 */
final class DashboardController
{
    private function guard(): ?Response
    {
        return Auth::check() ? null : redirect(url('/dashboard/login/'));
    }

    public function loginForm(Request $req): Response
    {
        if (Auth::check()) return redirect(url('/dashboard/'));
        return html(render('dashboard/login', ['error' => null], 'layout/dashboard'));
    }

    public function login(Request $req): Response
    {
        if (!RateLimit::hit('login', $req->ip(), 10, 600)) {
            return html(render('dashboard/login', ['error' => 'تلاش‌های زیاد. چند دقیقه دیگر دوباره امتحان کنید.'], 'layout/dashboard'), 429);
        }
        if (Auth::attempt((string) $req->input('username'), (string) $req->input('password'))) {
            return redirect(url('/dashboard/'));
        }
        return html(render('dashboard/login', ['error' => 'نام کاربری یا رمز عبور نادرست است.'], 'layout/dashboard'));
    }

    public function logout(Request $req): Response
    {
        Auth::logout();
        return redirect(url('/dashboard/login/'));
    }

    // ------------------------------------------------------------ نوبت‌ها
    public function home(Request $req): Response
    {
        if ($g = $this->guard()) return $g;
        $today = Jalali::today();
        $tIso = Jalali::iso(...$today);
        $tomIso = Jalali::iso(...Jalali::addDays($today, 1));
        $weekIso = Jalali::iso(...Jalali::addDays($today, 7));

        $stats = [
            'today' => DB::val("SELECT COUNT(*) FROM appointments WHERE date = ? AND status != 'cancelled'", [$tIso]),
            'tomorrow' => DB::val("SELECT COUNT(*) FROM appointments WHERE date = ? AND status != 'cancelled'", [$tomIso]),
            'week' => DB::val("SELECT COUNT(*) FROM appointments WHERE date >= ? AND date <= ? AND status != 'cancelled'", [$tIso, $weekIso]),
            'pending' => DB::val("SELECT COUNT(*) FROM appointments WHERE status = 'pending'"),
            'total' => DB::val('SELECT COUNT(*) FROM appointments'),
            'customers' => DB::val('SELECT COUNT(*) FROM customers'),
            'revenue_today' => DB::val("SELECT COALESCE(SUM(price),0) FROM appointments WHERE date = ? AND status = 'paid'", [$tIso]),
            'consultations_new' => DB::val("SELECT COUNT(*) FROM consultation_requests WHERE status = 'new'"),
        ];

        $where = ['1=1']; $p = []; $applied = [];
        $range = $req->q('range', ''); $status = $req->q('status', ''); $q = trim($req->q('q', '')); $date = trim($req->q('date', ''));
        if ($range === 'today') { $where[] = 'a.date = ?'; $p[] = $tIso; $applied[] = 'امروز'; }
        elseif ($range === 'tomorrow') { $where[] = 'a.date = ?'; $p[] = $tomIso; $applied[] = 'فردا'; }
        elseif ($range === 'week') { $where[] = 'a.date >= ? AND a.date <= ?'; array_push($p, $tIso, $weekIso); $applied[] = 'هفته جاری'; }
        if ($date && ($j = Jalali::parse($date))) { $where[] = 'a.date = ?'; $p[] = Jalali::iso(...$j); $applied[] = Jalali::pretty($j); }
        if ($status && isset(Models::APPT_STATUS[$status])) { $where[] = 'a.status = ?'; $p[] = $status; $applied[] = Models::APPT_STATUS[$status]; }
        if ($q !== '') {
            $where[] = '(c.first_name LIKE ? OR c.last_name LIKE ? OR c.mobile LIKE ? OR a.tracking_code LIKE ?)';
            $like = '%' . en_digits($q) . '%'; array_push($p, $like, $like, $like, $like); $applied[] = "جستجو: $q";
        }
        $rows = DB::all('SELECT a.id FROM appointments a JOIN customers c ON c.id = a.customer_id WHERE ' . implode(' AND ', $where)
            . ' ORDER BY a.date DESC, a.start_time DESC LIMIT 200', $p);
        $appointments = array_map(fn($r) => Models::appointment((int) $r['id']), $rows);

        $ctx = [
            'stats' => $stats, 'appointments' => $appointments, 'status_choices' => Models::APPT_STATUS,
            'filters' => ['range' => $range, 'status' => $status, 'q' => $q, 'date' => $date],
            'applied' => $applied, 'today_fa' => Jalali::pretty($today, true),
        ];
        if ($req->isHtmx()) return html(view('dashboard/partials/appointments_table', $ctx));
        return html(render('dashboard/home', $ctx, 'layout/dashboard'));
    }

    public function changeStatus(Request $req, string $id): Response
    {
        if ($g = $this->guard()) return $g;
        $appt = Models::appointment((int) $id);
        $new = (string) $req->input('status');
        if (!$appt) return not_found();
        if (!isset(Models::APPT_STATUS[$new])) return html('وضعیت نامعتبر', 400);
        DB::update('appointments', ['status' => $new], 'id = ?', [$appt['id']]);
        return html(view('dashboard/partials/appointment_row', ['appt' => Models::appointment($appt['id']), 'status_choices' => Models::APPT_STATUS, 'swapped' => true]));
    }

    public function deleteAppointment(Request $req, string $id): Response
    {
        if ($g = $this->guard()) return $g;
        DB::exec('DELETE FROM appointments WHERE id = ?', [(int) $id]);
        return html('');
    }

    // ------------------------------------------------------------ مشاوره‌ها
    public function consultations(Request $req): Response
    {
        if ($g = $this->guard()) return $g;
        $status = $req->q('status', ''); $q = trim($req->q('q', ''));
        $where = ['1=1']; $p = []; $applied = [];
        if ($status && isset(Models::CONS_STATUS[$status])) { $where[] = 'status = ?'; $p[] = $status; $applied[] = Models::CONS_STATUS[$status]; }
        if ($q !== '') { $where[] = '(first_name LIKE ? OR last_name LIKE ? OR mobile LIKE ?)'; $like = '%' . en_digits($q) . '%'; array_push($p, $like, $like, $like); $applied[] = "جستجو: $q"; }
        $counts = [
            'all' => DB::val('SELECT COUNT(*) FROM consultation_requests'),
            'new' => DB::val("SELECT COUNT(*) FROM consultation_requests WHERE status = 'new'"),
            'in_progress' => DB::val("SELECT COUNT(*) FROM consultation_requests WHERE status = 'in_progress'"),
            'done' => DB::val("SELECT COUNT(*) FROM consultation_requests WHERE status = 'done'"),
        ];
        $items = DB::all('SELECT * FROM consultation_requests WHERE ' . implode(' AND ', $where) . ' ORDER BY created_at DESC LIMIT 200', $p);
        $ctx = ['consultations' => $items, 'status_choices' => Models::CONS_STATUS, 'counts' => $counts,
            'filters' => ['status' => $status, 'q' => $q], 'applied' => $applied, 'today_fa' => Jalali::pretty(Jalali::today(), true)];
        if ($req->isHtmx()) return html(view('dashboard/partials/consultations_table', $ctx));
        return html(render('dashboard/consultations', $ctx, 'layout/dashboard'));
    }

    public function changeConsultationStatus(Request $req, string $id): Response
    {
        if ($g = $this->guard()) return $g;
        $new = (string) $req->input('status');
        if (!isset(Models::CONS_STATUS[$new])) return html('وضعیت نامعتبر', 400);
        DB::update('consultation_requests', ['status' => $new], 'id = ?', [(int) $id]);
        $item = DB::one('SELECT * FROM consultation_requests WHERE id = ?', [(int) $id]);
        if (!$item) return not_found();
        return html(view('dashboard/partials/consultation_row', ['item' => $item, 'status_choices' => Models::CONS_STATUS, 'swapped' => true]));
    }

    public function deleteConsultation(Request $req, string $id): Response
    {
        if ($g = $this->guard()) return $g;
        DB::exec('DELETE FROM consultation_requests WHERE id = ?', [(int) $id]);
        return html('');
    }
}
