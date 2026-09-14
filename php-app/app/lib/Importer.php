<?php
/**
 * وارد کردن خروجی JSON جنگو (دستور export_for_php) در پایگاه‌داده‌ی PHP.
 * شناسه‌ها حفظ نمی‌شوند؛ نگاشت id قدیمی → جدید داخلی انجام می‌شود. تکراری‌ها (بر اساس موبایل/عنوان/کد پیگیری) رد می‌شوند.
 */
final class Importer
{
    /** @return array<string,int> تعداد رکوردهای وارد شده به تفکیک جدول */
    public static function run(array $d, bool $wipe = false): array
    {
        return DB::tx(function () use ($d, $wipe) {
            $n = [];
            if ($wipe) {
                foreach (['appointments', 'consultation_requests', 'contact_messages', 'before_after', 'faqs', 'holidays', 'working_hours', 'services', 'service_categories', 'customers'] as $t) DB::exec("DELETE FROM $t");
            }
            if (!empty($d['clinic_settings'])) {
                $s = $d['clinic_settings'];
                DB::update('clinic_settings', [
                    'name' => (string) ($s['name'] ?: 'کلینیک زیبایی'), 'phone' => (string) $s['phone'], 'address' => (string) $s['address'],
                    'about' => (string) $s['about'], 'instagram' => (string) $s['instagram'],
                    'slot_step_minutes' => max(5, (int) $s['slot_step_minutes']), 'max_advance_days' => max(1, (int) $s['max_advance_days']),
                    'min_advance_hours' => max(0, (int) $s['min_advance_hours']),
                ], 'id = 1');
                $n['clinic_settings'] = 1;
            }

            // مشتریان
            $cust = []; $n['customers'] = 0;
            foreach ($d['customers'] ?? [] as $c) {
                $row = DB::one('SELECT id FROM customers WHERE mobile = ?', [$c['mobile']]);
                if ($row) { $cust[$c['id']] = (int) $row['id']; continue; }
                $cust[$c['id']] = DB::insert('customers', ['first_name' => $c['first_name'], 'last_name' => $c['last_name'], 'mobile' => $c['mobile'],
                    'note' => (string) ($c['note'] ?? ''), 'is_blocked' => (int) !empty($c['is_blocked']), 'created_at' => $c['created_at'] ?? gmdate('Y-m-d H:i:s')]);
                $n['customers']++;
            }
            // دسته‌ها
            $cat = []; $n['service_categories'] = 0;
            foreach ($d['service_categories'] ?? [] as $c) {
                $row = DB::one('SELECT id FROM service_categories WHERE title = ?', [$c['title']]);
                if ($row) { $cat[$c['id']] = (int) $row['id']; continue; }
                $cat[$c['id']] = DB::insert('service_categories', ['title' => $c['title'], 'slug' => self::slug($c['slug'] ?? $c['title'], 'service_categories'),
                    'description' => (string) $c['description'], 'icon' => (string) ($c['icon'] ?? ''), 'order' => (int) $c['order'], 'is_active' => (int) !empty($c['is_active'])]);
                $n['service_categories']++;
            }
            // خدمات
            $svc = []; $n['services'] = 0;
            foreach ($d['services'] ?? [] as $x) {
                if (!isset($cat[$x['category_id']])) continue;
                $row = DB::one('SELECT id FROM services WHERE title = ? AND category_id = ?', [$x['title'], $cat[$x['category_id']]]);
                if ($row) { $svc[$x['id']] = (int) $row['id']; continue; }
                $svc[$x['id']] = DB::insert('services', ['category_id' => $cat[$x['category_id']], 'title' => $x['title'], 'slug' => self::slug($x['slug'] ?? $x['title'], 'services'),
                    'description' => (string) $x['description'], 'duration_minutes' => max(5, (int) $x['duration_minutes']), 'price' => (int) $x['price'],
                    'is_active' => (int) !empty($x['is_active']), 'order' => (int) $x['order']]);
                $n['services']++;
            }
            // ساعات کاری و تعطیلات
            $n['working_hours'] = 0;
            foreach ($d['working_hours'] ?? [] as $w) {
                try { DB::insert('working_hours', ['weekday' => (int) $w['weekday'], 'start_time' => $w['start_time'], 'end_time' => $w['end_time'], 'is_active' => (int) !empty($w['is_active'])]); $n['working_hours']++; }
                catch (PDOException) {}
            }
            $n['holidays'] = 0;
            foreach ($d['holidays'] ?? [] as $h) {
                try { DB::insert('holidays', ['date' => $h['date'], 'reason' => (string) $h['reason']]); $n['holidays']++; } catch (PDOException) {}
            }
            // نوبت‌ها
            $n['appointments'] = 0; $n['appointments_skipped'] = 0;
            foreach ($d['appointments'] ?? [] as $a) {
                if (!isset($cust[$a['customer_id']], $svc[$a['service_id']])) { $n['appointments_skipped']++; continue; }
                try {
                    DB::insert('appointments', ['customer_id' => $cust[$a['customer_id']], 'service_id' => $svc[$a['service_id']], 'date' => $a['date'],
                        'start_time' => $a['start_time'], 'end_time' => $a['end_time'], 'status' => $a['status'], 'price' => (int) $a['price'],
                        'customer_note' => (string) $a['customer_note'], 'staff_note' => (string) $a['staff_note'], 'tracking_code' => $a['tracking_code'],
                        'authority' => (string) $a['authority'], 'ref_id' => (string) $a['ref_id'], 'paid_at' => $a['paid_at'], 'created_at' => $a['created_at'] ?? gmdate('Y-m-d H:i:s')]);
                    $n['appointments']++;
                } catch (PDOException) { $n['appointments_skipped']++; }
            }
            // بقیه
            $n['contact_messages'] = 0;
            foreach ($d['contact_messages'] ?? [] as $m) {
                DB::insert('contact_messages', ['name' => $m['name'], 'mobile' => $m['mobile'], 'subject' => (string) $m['subject'], 'message' => $m['message'],
                    'is_read' => (int) !empty($m['is_read']), 'created_at' => $m['created_at'] ?? gmdate('Y-m-d H:i:s')]); $n['contact_messages']++;
            }
            $n['before_after'] = 0;
            foreach ($d['before_after'] ?? [] as $b) {
                if (!$b['before_image'] || !$b['after_image']) continue;
                DB::insert('before_after', ['title' => $b['title'], 'description' => (string) $b['description'], 'before_image' => $b['before_image'], 'after_image' => $b['after_image'],
                    'display_order' => (int) $b['display_order'], 'is_active' => (int) !empty($b['is_active'])]); $n['before_after']++;
            }
            $n['faqs'] = 0;
            foreach ($d['faqs'] ?? [] as $f) {
                if (DB::one('SELECT id FROM faqs WHERE question = ?', [$f['question']])) continue;
                DB::insert('faqs', ['question' => $f['question'], 'answer' => $f['answer'], 'display_order' => (int) $f['display_order'], 'is_active' => (int) !empty($f['is_active'])]); $n['faqs']++;
            }
            $n['consultation_requests'] = 0;
            foreach ($d['consultation_requests'] ?? [] as $c) {
                DB::insert('consultation_requests', ['first_name' => $c['first_name'], 'last_name' => $c['last_name'], 'mobile' => $c['mobile'], 'message' => (string) $c['message'],
                    'status' => $c['status'] ?: 'new', 'created_at' => $c['created_at'] ?? gmdate('Y-m-d H:i:s')]); $n['consultation_requests']++;
            }
            return $n;
        });
    }

    private static function slug(string $base, string $table): string
    {
        $s = slugify($base) ?: bin2hex(random_bytes(3));
        $try = $s; $i = 2;
        while (DB::one("SELECT 1 FROM $table WHERE slug = ?", [$try])) $try = $s . '-' . $i++;
        return $try;
    }
}
