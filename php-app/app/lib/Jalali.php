<?php
/**
 * تقویم شمسی (جلالی) — پورت الگوریتم jalaali-js (دقیق، با جدول سال‌های کبیسه).
 * بدون وابستگی خارجی.
 */
final class Jalali
{
    public const MONTHS = [
        'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
        'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند',
    ];
    /** شنبه=۰ … جمعه=۶ */
    public const WEEKDAYS = ['شنبه', 'یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه'];
    public const WEEK_HEADERS = ['ش', 'ی', 'د', 'س', 'چ', 'پ', 'ج'];

    private const BREAKS = [-61, 9, 38, 199, 426, 686, 756, 818, 1111, 1181, 1210,
        1635, 2060, 2097, 2192, 2262, 2324, 2394, 2456, 3178];

    // ------------------------------------------------------------------ //
    // تبدیل‌ها
    // ------------------------------------------------------------------ //
    /** @return array{0:int,1:int,2:int} [jy, jm, jd] */
    public static function fromGregorian(int $gy, int $gm, int $gd): array
    {
        return self::d2j(self::g2d($gy, $gm, $gd));
    }

    /** @return array{0:int,1:int,2:int} [gy, gm, gd] */
    public static function toGregorian(int $jy, int $jm, int $jd): array
    {
        return self::d2g(self::j2d($jy, $jm, $jd));
    }

    public static function isValid(int $jy, int $jm, int $jd): bool
    {
        return $jy >= -61 && $jy <= 3177 && $jm >= 1 && $jm <= 12
            && $jd >= 1 && $jd <= self::monthLength($jy, $jm);
    }

    public static function isLeap(int $jy): bool
    {
        return self::jalCal($jy)['leap'] === 0;
    }

    public static function monthLength(int $jy, int $jm): int
    {
        if ($jm <= 6) return 31;
        if ($jm <= 11) return 30;
        return self::isLeap($jy) ? 30 : 29;
    }

    // ------------------------------------------------------------------ //
    // کمکی‌های سطح بالا
    // ------------------------------------------------------------------ //
    /** تاریخ امروز شمسی بر اساس تایم‌زون فعلی PHP. */
    public static function today(): array
    {
        $now = new DateTimeImmutable('now');
        return self::fromGregorian((int) $now->format('Y'), (int) $now->format('n'), (int) $now->format('j'));
    }

    /** رشته‌ی ISO شمسی مثل 1403-04-08 */
    public static function iso(int $jy, int $jm, int $jd): string
    {
        return sprintf('%04d-%02d-%02d', $jy, $jm, $jd);
    }

    /** تجزیه‌ی رشته‌ی ISO شمسی؛ در صورت نامعتبر بودن null. */
    public static function parse(?string $iso): ?array
    {
        if (!$iso || !preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', trim($iso), $m)) return null;
        $jy = (int) $m[1]; $jm = (int) $m[2]; $jd = (int) $m[3];
        return self::isValid($jy, $jm, $jd) ? [$jy, $jm, $jd] : null;
    }

    /** افزودن n روز به یک تاریخ شمسی. */
    public static function addDays(array $j, int $days): array
    {
        return self::d2j(self::j2d($j[0], $j[1], $j[2]) + $days);
    }

    /** روز هفته (شنبه=۰ … جمعه=۶) */
    public static function weekday(array $j): int
    {
        // jdn 0 = دوشنبه؛ (jdn + 2) % 7 → شنبه=0 (بررسی: 1403-01-01 چهارشنبه = 4)
        $jdn = self::j2d($j[0], $j[1], $j[2]);
        return (int) ((($jdn + 2) % 7 + 7) % 7);
    }

    /** DateTimeImmutable میلادی متناظر با تاریخ شمسی + ساعت "HH:MM" */
    public static function toDateTime(array $j, string $time = '00:00'): DateTimeImmutable
    {
        [$gy, $gm, $gd] = self::toGregorian($j[0], $j[1], $j[2]);
        [$h, $i] = array_map('intval', explode(':', $time) + [0, 0]);
        return (new DateTimeImmutable())->setDate($gy, $gm, $gd)->setTime($h, $i, 0);
    }

    /** تاریخ شمسی از یک DateTime/رشته‌ی میلادی */
    public static function fromDateTime(DateTimeInterface|string $dt): array
    {
        if (is_string($dt)) $dt = new DateTimeImmutable($dt);
        return self::fromGregorian((int) $dt->format('Y'), (int) $dt->format('n'), (int) $dt->format('j'));
    }

    /** نمایش زیبا: «۸ تیر ۱۴۰۳» یا با روز هفته */
    public static function pretty(array|string|null $j, bool $withWeekday = false): string
    {
        if (is_string($j)) $j = self::parse($j);
        if (!$j) return '';
        $s = fa_digits($j[2]) . ' ' . self::MONTHS[$j[1] - 1] . ' ' . fa_digits($j[0]);
        return $withWeekday ? self::WEEKDAYS[self::weekday($j)] . ' ' . $s : $s;
    }

    /** نمایش عددی «۱۴۰۳/۰۴/۰۸» */
    public static function numeric(array|string|null $j): string
    {
        if (is_string($j)) $j = self::parse($j);
        if (!$j) return '';
        return fa_digits(sprintf('%04d/%02d/%02d', $j[0], $j[1], $j[2]));
    }

    // ------------------------------------------------------------------ //
    // هسته‌ی الگوریتم (jalaali-js)
    // ------------------------------------------------------------------ //
    private static function jalCal(int $jy): array
    {
        $bl = count(self::BREAKS);
        $gy = $jy + 621;
        $leapJ = -14;
        $jp = self::BREAKS[0];
        if ($jy < $jp || $jy >= self::BREAKS[$bl - 1]) {
            throw new InvalidArgumentException("Invalid Jalali year $jy");
        }
        $jump = 0;
        for ($i = 1; $i < $bl; $i++) {
            $jm = self::BREAKS[$i];
            $jump = $jm - $jp;
            if ($jy < $jm) break;
            $leapJ += intdiv($jump, 33) * 8 + intdiv($jump % 33, 4);
            $jp = $jm;
        }
        $n = $jy - $jp;
        $leapJ += intdiv($n, 33) * 8 + intdiv(($n % 33) + 3, 4);
        if ($jump % 33 === 4 && $jump - $n === 4) $leapJ++;
        $leapG = intdiv($gy, 4) - intdiv((intdiv($gy, 100) + 1) * 3, 4) - 150;
        $march = 20 + $leapJ - $leapG;
        if ($jump - $n < 6) $n = $n - $jump + intdiv($jump + 4, 33) * 33;
        $leap = (($n + 1) % 33 - 1) % 4;
        if ($leap === -1) $leap = 4;
        return ['leap' => $leap, 'gy' => $gy, 'march' => $march];
    }

    private static function j2d(int $jy, int $jm, int $jd): int
    {
        $r = self::jalCal($jy);
        return self::g2d($r['gy'], 3, $r['march']) + ($jm - 1) * 31 - intdiv($jm, 7) * ($jm - 7) + $jd - 1;
    }

    private static function d2j(int $jdn): array
    {
        $gy = self::d2g($jdn)[0];
        $jy = $gy - 621;
        $r = self::jalCal($jy);
        $jdn1f = self::g2d($gy, 3, $r['march']);
        $k = $jdn - $jdn1f;
        if ($k >= 0) {
            if ($k <= 185) {
                $jm = 1 + intdiv($k, 31);
                $jd = ($k % 31) + 1;
                return [$jy, $jm, $jd];
            }
            $k -= 186;
        } else {
            $jy -= 1;
            $k += 179;
            if ($r['leap'] === 1) $k += 1;
        }
        $jm = 7 + intdiv($k, 30);
        $jd = ($k % 30) + 1;
        return [$jy, $jm, $jd];
    }

    private static function g2d(int $gy, int $gm, int $gd): int
    {
        $d = intdiv(($gy + intdiv($gm - 8, 6) + 100100) * 1461, 4)
            + intdiv(153 * (($gm + 9) % 12) + 2, 5)
            + $gd - 34840408;
        $d = $d - intdiv(intdiv($gy + 100100 + intdiv($gm - 8, 6), 100) * 3, 4) + 752;
        return $d;
    }

    private static function d2g(int $jdn): array
    {
        $j = 4 * $jdn + 139361631;
        $j = $j + intdiv(intdiv(4 * $jdn + 183187720, 146097) * 3, 4) * 4 - 3908;
        $i = intdiv($j % 1461, 4) * 5 + 308;
        $gd = intdiv($i % 153, 5) + 1;
        $gm = (intdiv($i, 153) % 12) + 1;
        $gy = intdiv($j, 1461) - 100100 + intdiv(8 - $gm, 6);
        return [$gy, $gm, $gd];
    }
}
