"""
پیکربندی Gunicorn برای اجرای «کلینیک زیبایی» در محیط production.

اجرا:
    gunicorn -c deploy/gunicorn.conf.py config.wsgi:application

نکته: متغیرهای محیطی (SECRET_KEY, DEBUG=False, ALLOWED_HOSTS, DB_* و ...)
باید پیش از اجرا تنظیم شده باشند (مثلاً از طریق فایل .env یا systemd).
"""
import multiprocessing
import os

# ---- شبکه ---------------------------------------------------------------- #
# پشت nginx به‌صورت سوکت یونیکس (سریع‌تر و امن‌تر) یا TCP محلی bind شود.
bind = os.environ.get("GUNICORN_BIND", "unix:/run/beauty-clinic/gunicorn.sock")

# ---- کارگرها ------------------------------------------------------------- #
# قاعده‌ی رایج: (2 × CPU) + 1
workers = int(os.environ.get("GUNICORN_WORKERS", multiprocessing.cpu_count() * 2 + 1))
worker_class = "sync"
threads = int(os.environ.get("GUNICORN_THREADS", 2))

# هر کارگر پس از این تعداد درخواست restart می‌شود (جلوگیری از نشت حافظه)
max_requests = 1000
max_requests_jitter = 100

# ---- timeoutها ----------------------------------------------------------- #
timeout = 60
graceful_timeout = 30
keepalive = 5

# ---- لاگ ----------------------------------------------------------------- #
accesslog = os.environ.get("GUNICORN_ACCESS_LOG", "-")  # stdout
errorlog = os.environ.get("GUNICORN_ERROR_LOG", "-")    # stderr
loglevel = os.environ.get("GUNICORN_LOG_LEVEL", "info")

# ---- اعتماد به هدرهای پروکسی (nginp) ------------------------------------- #
# تا X-Forwarded-* از nginx محلی پذیرفته شود.
forwarded_allow_ips = os.environ.get("GUNICORN_FORWARDED_ALLOW_IPS", "127.0.0.1")

# نام فرایند
proc_name = "beauty-clinic"
