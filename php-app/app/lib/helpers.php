<?php
/**
 * توابع کمکی سراسری.
 */

/** فرار HTML */
function e(mixed $v): string
{
    return htmlspecialchars((string) ($v ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** تبدیل ارقام انگلیسی به فارسی */
function fa_digits(mixed $v): string
{
    return strtr((string) $v, ['0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
        '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹']);
}

/** تبدیل ارقام فارسی/عربی به انگلیسی */
function en_digits(string $v): string
{
    return strtr($v, [
        '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
    ]);
}

/** قالب‌بندی مبلغ: 1200000 → «۱٬۲۰۰٬۰۰۰» */
function money(mixed $v): string
{
    return fa_digits(number_format((float) $v, 0, '.', '٬'));
}

/** نرمال‌سازی و اعتبارسنجی شماره موبایل ایران؛ در صورت نامعتبر null */
function normalize_mobile(?string $m): ?string
{
    $m = preg_replace('/\D/', '', en_digits(trim((string) $m)));
    return preg_match('/^09\d{9}$/', $m) ? $m : null;
}

/** ساخت آدرس مطلق بر اساس BASE_URL یا هدرهای درخواست */
function absolute_url(string $path): string
{
    $base = rtrim(config('BASE_URL', ''), '/');
    if ($base === '') {
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
        $base = ($https ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    }
    return $base . '/' . ltrim($path, '/');
}

/** خواندن تنظیمات از .env (یک‌بار بارگذاری) */
function config(string $key, mixed $default = null): mixed
{
    static $env = null;
    if ($env === null) {
        $env = [];
        $file = BASE_PATH . '/.env';
        if (is_file($file)) {
            foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                $line = trim($line);
                if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) continue;
                [$k, $v] = explode('=', $line, 2);
                $v = trim($v);
                if ((str_starts_with($v, '"') && str_ends_with($v, '"')) || (str_starts_with($v, "'") && str_ends_with($v, "'"))) {
                    $v = substr($v, 1, -1);
                }
                $env[trim($k)] = $v;
            }
        }
    }
    $v = $_ENV[$key] ?? getenv($key) ?: ($env[$key] ?? null);
    if ($v === null || $v === '') return $default;
    if (is_bool($default)) return in_array(strtolower((string) $v), ['1', 'true', 'yes', 'on'], true);
    if (is_int($default)) return (int) $v;
    return $v;
}

/** «۹ ساعت پیش» نیست؛ فقط تاریخ/ساعت ثبت را شمسی نشان می‌دهد */
function created_fa(?string $ts): array
{
    if (!$ts) return ['date' => '', 'time' => ''];
    $dt = new DateTimeImmutable($ts, new DateTimeZone('UTC'));
    $dt = $dt->setTimezone(new DateTimeZone(date_default_timezone_get()));
    return ['date' => Jalali::numeric(Jalali::fromDateTime($dt)), 'time' => $dt->format('H:i')];
}

/** رندر یک قالب PHP و بازگرداندن خروجی به‌صورت رشته */
function view(string $name, array $vars = []): string
{
    $vars += View::shared();
    extract($vars, EXTR_SKIP);
    ob_start();
    try {
        require BASE_PATH . '/app/views/' . $name . '.php';
    } catch (Throwable $ex) {
        ob_end_clean();
        throw $ex;
    }
    return ob_get_clean();
}

/** رندر قالب داخل یک layout */
function render(string $name, array $vars = [], string $layout = 'layout/site'): string
{
    $vars += View::shared();
    $content = view($name, $vars);
    return view($layout, View::$page + $vars + ['content' => $content]);
}

/** پاسخ HTML */
function html(string $body, int $status = 200, array $headers = []): Response
{
    return new Response($body, $status, ['Content-Type' => 'text/html; charset=utf-8'] + $headers);
}

function redirect(string $to, int $status = 302): Response
{
    return new Response('', $status, ['Location' => $to]);
}

/** هدایت سمت کلاینت برای HTMX */
function hx_redirect(string $to): Response
{
    return new Response('', 204, ['HX-Redirect' => $to]);
}

function not_found(string $msg = 'صفحه پیدا نشد'): Response
{
    return html(render('website/404', ['title' => $msg]), 404);
}

/** کوتاه‌کننده‌ی url() برای ساخت لینک‌ها */
function url(string $path = ''): string
{
    return '/' . ltrim($path, '/');
}

/** اسلاگ ساده‌ی یونیکد */
function slugify(string $s): string
{
    $s = mb_strtolower(trim($s));
    $s = preg_replace('/[^\p{L}\p{N}]+/u', '-', $s);
    return trim($s, '-') ?: bin2hex(random_bytes(4));
}
