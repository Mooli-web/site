# کلینیک زیبایی — سیستم نوبت‌دهی (Beauty Clinic Appointment System)

سیستم نوبت‌دهی آنلاین کلینیک زیبایی با تقویم شمسی (جلالی)، کاملاً فارسی و RTL.

## استک فنی
- **Backend:** Django 5 + Django Ninja
- **Frontend:** HTMX + Tailwind CSS + Alpine.js (همگی **محلی**، بدون CDN)
- **دیتابیس:** PostgreSQL (پیش‌فرض) یا SQLite برای توسعه
- **تقویم:** شمسی/جلالی با `django-jalali` و `jdatetime`
- **بدون ثبت‌نام مشتری:** فقط نام، نام خانوادگی و شماره موبایل

## ساختار
```
config/            تنظیمات، urls، Ninja API
apps/
  core/            مدل پایه، Customer، ClinicSettings، rate limiting، context processor
  appointments/    Service/Category، WorkingHour، Holiday، Appointment + منطق اسلات‌ها
  website/         صفحه اصلی + فلوی رزرو مشتری (HTMX)
  dashboard/       پنل منشی/مدیریت
templates/         قالب‌های website و dashboard
static/            css (tailwind ساخته‌شده + input)، js (htmx، alpine)، fonts (Vazirmatn)، img
vendor/tailwindcss باینری standalone برای ساخت CSS (بدون Node)
```

## راه‌اندازی
```bash
pip install -r requirements.txt
cp .env.example .env          # و در صورت نیاز مقادیر را ویرایش کنید
python manage.py migrate
python manage.py seed_demo    # داده‌های نمونه (دسته‌ها، خدمات، ساعات کاری)
python manage.py createsuperuser
python manage.py runserver
```

### ساخت مجدد CSS (پس از تغییر کلاس‌های Tailwind)
```bash
chmod +x vendor/tailwindcss      # یک‌بار (در صورت از دست رفتن مجوز اجرا)
./build_css.sh                   # یا: ./build_css.sh --watch
```

## آدرس‌ها
| مسیر | توضیح |
|---|---|
| `/` | صفحه اصلی مشتریان |
| `/about/` | درباره ما |
| `/contact/` | تماس با ما (فرم پیام با rate limiting) |
| `/before-after/` | گالری قبل و بعد (اسلایدر مقایسه‌ای Alpine.js) |
| `/faq/` | سوالات متداول (آکاردئون Alpine.js) |
| `/consultation/` | درخواست مشاوره رایگان (فرم HTMX) |
| `/booking/` | فلوی رزرو نوبت (خدمت → روز → ساعت → اطلاعات) |
| `/dashboard/` | پنل منشی (نیازمند کاربر staff) |
| `/dashboard/consultations/` | مدیریت درخواست‌های مشاوره (منشی) |
| `/dashboard/login/` | ورود منشی |
| `/admin/` | پنل ادمین جنگو (تنظیمات پیشرفته) |
| `/api/docs` | مستندات API (Django Ninja) |

## درگاه پرداخت زرین‌پال (Sandbox)
پس از ثبت نوبت، کاربر به صفحه‌ی پرداخت هدایت می‌شود:

```
ثبت نوبت → /payment/<code>/        صفحه پرداخت (خلاصه + دکمه)
          → /payment/<code>/start/    PaymentRequest و هدایت به درگاه
          ← /payment/<code>/callback/ بازگشت، Verify و به‌روزرسانی وضعیت
          → /payment/<code>/result/   نتیجه (موفق/ناموفق)
```

- مرچنت‌آیدی در `.env` تنظیم می‌شود: `ZARINPAL_MERCHANT_ID`
- حالت Sandbox/Production با `ZARINPAL_SANDBOX` (پیش‌فرض True → `sandbox.zarinpal.com`)
- فیلدهای پرداخت روی `Appointment`: `status`, `authority`, `ref_id`, `paid_at`
- پرداخت موفق → وضعیت `paid`؛ لغو/ناموفق → `payment_failed` (و اسلات دوباره آزاد می‌شود)
- کلاینت در `apps/payments/zarinpal.py` (با `requests`, REST v4) — idempotent (code 101)

## نکات معماری
- کلینیک **ساده** است: نوبت فقط به خدمت و زمان وابسته است (**بدون فیلد متخصص/دکتر**).
- ساعات کاری و تعطیلات **سراسری کلینیک** هستند؛ هر اسلات زمانی ظرفیت یک نوبت دارد.
- جلوگیری از رزرو همزمان در دو لایه: محاسبه اسلات‌های خالی + `UniqueConstraint` دیتابیس.
- مشتری **امکان لغو نوبت ندارد**؛ مدیریت وضعیت فقط در پنل منشی.
- امنیت: `@ensure_csrf_cookie` + هدر CSRF برای HTMX، rate limiting روی ثبت نوبت (۵ تلاش / ۱۰ دقیقه).

## استقرار در Production (Deployment)

پروژه برای انتشار آماده است. در حالت `DEBUG=False` تمام تنظیمات امنیتی به‌صورت خودکار فعال می‌شوند.

### ۱) آماده‌سازی محیط
```bash
python -m venv .venv && source .venv/bin/activate
pip install -r requirements.txt
cp .env.example .env
python manage.py generate_secret_key      # کلید امن تولید و در .env قرار دهید
```
در `.env` حتماً تنظیم کنید: `DEBUG=False`, `ALLOWED_HOSTS`, `CSRF_TRUSTED_ORIGINS`،
اطلاعات PostgreSQL و مرچنت‌آیدی واقعی زرین‌پال (`ZARINPAL_SANDBOX=False`).

### ۲) دیپلوی (همه‌ی مراحل یک‌جا)
```bash
bash deploy/deploy.sh
```
این اسکریپت: نصب وابستگی‌ها، ساخت CSS، `check --deploy`، `migrate`، `collectstatic`
و restart سرویس را انجام می‌دهد.

### ۳) اجرای Gunicorn
```bash
gunicorn -c deploy/gunicorn.conf.py config.wsgi:application
```

### ۴) systemd + Nginx
فایل‌های نمونه در پوشه‌ی `deploy/`:
- `deploy/gunicorn.conf.py` — پیکربندی Gunicorn (workerها، timeout، لاگ)
- `deploy/beauty-clinic.socket` و `deploy/beauty-clinic.service` — سرویس systemd
- `deploy/nginx.conf` — reverse-proxy با TLS، سرو static/media، کش و gzip

```bash
sudo cp deploy/beauty-clinic.{socket,service} /etc/systemd/system/
sudo systemctl daemon-reload && sudo systemctl enable --now beauty-clinic.socket
sudo cp deploy/nginx.conf /etc/nginx/sites-available/beauty-clinic
sudo ln -s /etc/nginx/sites-available/beauty-clinic /etc/nginx/sites-enabled/
sudo certbot --nginx -d clinic.example.com        # دریافت گواهی TLS رایگان
sudo nginx -t && sudo systemctl reload nginx
```

### تنظیمات امنیتی فعال در production (`DEBUG=False`)
- `SECURE_SSL_REDIRECT` — هدایت اجباری HTTP → HTTPS
- `SECURE_HSTS_SECONDS` + `INCLUDE_SUBDOMAINS` + `PRELOAD` — HSTS
- `SESSION_COOKIE_SECURE` / `CSRF_COOKIE_SECURE` — کوکی فقط روی HTTPS
- `SECURE_CONTENT_TYPE_NOSNIFF`, `X_FRAME_OPTIONS=DENY` (ضد clickjacking)
- `SECURE_PROXY_SSL_HEADER` — تشخیص HTTPS از هدر nginx
- خروجی `python manage.py check --deploy` بدون هیچ هشدار.

### فایل‌های static و media
- **static:** با WhiteNoise جمع‌آوری می‌شوند (هش‌گذاری/manifest برای cache-busting + فشرده‌سازی gzip/brotli). storage سفارشی در `apps/core/storage.py` مرجع‌های شکسته‌ی پکیج‌های شخص‌ثالث (jquery-ui) را تحمل می‌کند.
- **media:** فایل‌سیستم؛ در production توسط nginx سرو می‌شود (`/media/`).

### لاگ‌ها
لاگ‌ها در `logs/app.log` (با چرخش خودکار، حداکثر ۵ فایل ۵ مگابایتی) و هم‌زمان روی console.
