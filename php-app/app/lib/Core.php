<?php
/**
 * هسته‌ی سبک اپلیکیشن: Request/Response، Router، DB (PDO SQLite)، Session، CSRF، Auth، RateLimit، View.
 * بدون هیچ وابستگی خارجی — برای اجرا روی ارزان‌ترین هاست‌های اشتراکی PHP.
 */

final class Request
{
    public string $method;
    public string $path;
    public array $get;
    public array $post;
    public array $files;
    public array $headers;

    public function __construct()
    {
        $this->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        // پشتیبانی از نصب در زیرپوشه (اگر public/ روت دامنه نباشد)
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\');
        if ($base !== '' && str_starts_with($path, $base)) $path = substr($path, strlen($base)) ?: '/';
        $this->path = '/' . trim($path, '/');
        if ($this->path !== '/') $this->path .= '/';   // نرمال‌سازی: همیشه اسلش انتهایی
        $this->get = $_GET;
        $this->post = $_POST;
        $this->files = $_FILES;
        $this->headers = [];
        foreach ($_SERVER as $k => $v) {
            if (str_starts_with($k, 'HTTP_')) $this->headers[strtolower(str_replace('_', '-', substr($k, 5)))] = $v;
        }
    }

    public function isHtmx(): bool { return ($this->headers['hx-request'] ?? '') === 'true'; }
    public function header(string $k): ?string { return $this->headers[strtolower($k)] ?? null; }
    public function input(string $k, mixed $d = ''): mixed { return $this->post[$k] ?? $this->get[$k] ?? $d; }
    public function q(string $k, mixed $d = ''): mixed { return $this->get[$k] ?? $d; }
    public function ip(): string
    {
        $xff = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
        if ($xff) return trim(explode(',', $xff)[0]);
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}

final class Response
{
    public function __construct(public string $body = '', public int $status = 200, public array $headers = []) {}

    public function send(): void
    {
        Session::patchCookieHeaders();
        http_response_code($this->status);
        foreach ($this->headers as $k => $v) header("$k: $v");
        echo $this->body;
    }
}

final class Router
{
    private array $routes = [];

    public function add(string $method, string $pattern, callable $handler): void
    {
        $regex = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', rtrim($pattern, '/')) . '/?$#u';
        $this->routes[] = [$method, $regex, $handler];
    }
    public function get(string $p, callable $h): void { $this->add('GET', $p, $h); }
    public function post(string $p, callable $h): void { $this->add('POST', $p, $h); }

    public function dispatch(Request $req): Response
    {
        $allowed = false;
        foreach ($this->routes as [$method, $regex, $handler]) {
            if (!preg_match($regex, $req->path, $m)) continue;
            $allowed = true;
            if ($method !== $req->method) continue;
            $params = array_filter($m, 'is_string', ARRAY_FILTER_USE_KEY);
            return $handler($req, ...array_values($params));
        }
        if ($allowed) return html('Method Not Allowed', 405);
        return not_found();
    }
}

final class DB
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            $path = config('DB_PATH', BASE_PATH . '/storage/db/clinic.sqlite');
            $fresh = !is_file($path);
            self::$pdo = new PDO('sqlite:' . $path, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            self::$pdo->exec('PRAGMA foreign_keys = ON');
            self::$pdo->exec('PRAGMA journal_mode = WAL');
            self::$pdo->exec('PRAGMA busy_timeout = 5000');
            Schema::migrate(self::$pdo, $fresh);
        }
        return self::$pdo;
    }

    public static function q(string $sql, array $p = []): PDOStatement
    {
        $st = self::pdo()->prepare($sql);
        $st->execute($p);
        return $st;
    }
    public static function all(string $sql, array $p = []): array { return self::q($sql, $p)->fetchAll(); }
    public static function one(string $sql, array $p = []): ?array { $r = self::q($sql, $p)->fetch(); return $r === false ? null : $r; }
    public static function val(string $sql, array $p = []): mixed { return self::q($sql, $p)->fetchColumn(); }
    public static function exec(string $sql, array $p = []): int { return self::q($sql, $p)->rowCount(); }
    public static function lastId(): int { return (int) self::pdo()->lastInsertId(); }
    public static function tx(callable $fn): mixed
    {
        $pdo = self::pdo();
        $pdo->beginTransaction();
        try { $r = $fn(); $pdo->commit(); return $r; }
        catch (Throwable $e) { if ($pdo->inTransaction()) $pdo->rollBack(); throw $e; }
    }

    /** درج ساده: insert('table', ['col'=>val]) */
    public static function insert(string $table, array $data): int
    {
        $cols = implode(',', array_map(fn($c) => "\"$c\"", array_keys($data)));
        $ph = implode(',', array_fill(0, count($data), '?'));
        self::q("INSERT INTO $table ($cols) VALUES ($ph)", array_values($data));
        return self::lastId();
    }

    public static function update(string $table, array $data, string $where, array $wp = []): int
    {
        $set = implode(',', array_map(fn($c) => "\"$c\" = ?", array_keys($data)));
        return self::exec("UPDATE $table SET $set, updated_at = CURRENT_TIMESTAMP WHERE $where", [...array_values($data), ...$wp]);
    }
}

final class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) return;
        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
        session_set_cookie_params([
            'lifetime' => 60 * 60 * 24 * 14, 'path' => '/', 'httponly' => true,
            'secure' => $secure, 'samesite' => ($secure && config('COOKIE_SAMESITE')) ? (string) config('COOKIE_SAMESITE') : 'Lax',
        ]);
        $dir = BASE_PATH . '/storage/sessions';
        if (!is_dir($dir)) @mkdir($dir, 0700, true);
        if (is_writable($dir)) { session_save_path($dir); ini_set('session.gc_probability', '1'); ini_set('session.gc_divisor', '100'); ini_set('session.gc_maxlifetime', (string) (60 * 60 * 24 * 14)); }
        session_name('clinicsess');
        session_start();
    }
    /**
     * وقتی سایت داخل iframe یک دامنهٔ دیگر نمایش داده می‌شود (COOKIE_SAMESITE=None)، مرورگرهای جدید
     * کوکی شخص‌ثالث را فقط با ویژگی Partitioned (CHIPS) می‌پذیرند. PHP این ویژگی را نمی‌شناسد؛
     * پس هدر Set-Cookie سشن را بازنویسی می‌کنیم.
     */
    public static function patchCookieHeaders(): void
    {
        if (headers_sent() || strcasecmp((string) config('COOKIE_SAMESITE', ''), 'None') !== 0) return;
        $patched = [];
        foreach (headers_list() as $h) {
            if (stripos($h, 'Set-Cookie:') === 0 && stripos($h, 'SameSite=None') !== false && stripos($h, 'Partitioned') === false) {
                $patched[] = $h . '; Partitioned';
            }
        }
        if (!$patched) return;
        header_remove('Set-Cookie');
        foreach ($patched as $h) header($h, false);
    }
    public static function get(string $k, mixed $d = null): mixed { return $_SESSION[$k] ?? $d; }
    public static function set(string $k, mixed $v): void { $_SESSION[$k] = $v; }
    public static function forget(string $k): void { unset($_SESSION[$k]); }
    public static function flash(string $k, mixed $msg = null): mixed
    {
        if ($msg !== null) { $_SESSION['_flash'][$k] = $msg; return null; }
        $v = $_SESSION['_flash'][$k] ?? null;
        unset($_SESSION['_flash'][$k]);
        return $v;
    }
}

final class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['_csrf'])) $_SESSION['_csrf'] = bin2hex(random_bytes(20));
        return $_SESSION['_csrf'];
    }
    public static function check(Request $req): bool
    {
        $sent = $req->post['_csrf'] ?? $req->header('X-CSRFToken') ?? '';
        return $sent !== '' && hash_equals(self::token(), (string) $sent);
    }
    public static function field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . e(self::token()) . '">';
    }
}

final class Auth
{
    public static function user(): ?array
    {
        static $u = false;
        if ($u === false) {
            $id = Session::get('uid');
            $u = $id ? DB::one('SELECT * FROM users WHERE id = ? AND is_active = 1', [$id]) : null;
        }
        return $u;
    }
    public static function check(): bool { return self::user() !== null; }
    public static function attempt(string $username, string $password): bool
    {
        $u = DB::one('SELECT * FROM users WHERE username = ? AND is_active = 1', [trim($username)]);
        if (!$u || !password_verify($password, $u['password_hash'])) return false;
        session_regenerate_id(true);
        Session::set('uid', (int) $u['id']);
        return true;
    }
    public static function logout(): void
    {
        Session::forget('uid');
        session_regenerate_id(true);
    }
}

/** محدودسازی نرخ درخواست مبتنی بر جدول SQLite (بدون نیاز به Redis/Memcached). */
final class RateLimit
{
    /** true یعنی مجاز است. */
    public static function hit(string $key, string $ip, int $limit = 5, int $window = 600): bool
    {
        $now = time();
        DB::exec('DELETE FROM rate_limits WHERE expires_at < ?', [$now]);
        $row = DB::one('SELECT hits FROM rate_limits WHERE key = ? AND ip = ?', [$key, $ip]);
        if ($row === null) {
            DB::insert('rate_limits', ['key' => $key, 'ip' => $ip, 'hits' => 1, 'expires_at' => $now + $window]);
            return true;
        }
        if ((int) $row['hits'] >= $limit) return false;
        DB::exec('UPDATE rate_limits SET hits = hits + 1 WHERE key = ? AND ip = ?', [$key, $ip]);
        return true;
    }
}

final class View
{
    private static ?array $shared = null;
    /** متغیرهایی که قالب داخلی برای layout تنظیم می‌کند (title, description, extra_body) */
    public static array $page = [];

    /** متغیرهای مشترک همه‌ی قالب‌ها (معادل context processor جنگو) */
    public static function shared(): array
    {
        if (self::$shared === null) {
            self::$shared = [
                'clinic' => Models::clinic(),
                'now_year' => Jalali::today()[0],
                'csrf_token' => Csrf::token(),
                'auth_user' => Auth::user(),
            ];
        }
        return self::$shared;
    }
}
