"""
تنظیمات پروژه «کلینیک زیبایی» — Beauty Clinic Appointment System
Django 5 + Django Ninja + HTMX/Tailwind/Alpine + PostgreSQL + تقویم شمسی (جلالی)
"""
from pathlib import Path

from decouple import Csv, config

BASE_DIR = Path(__file__).resolve().parent.parent

# --------------------------------------------------------------------------- #
# Security                                                                     #
# --------------------------------------------------------------------------- #
SECRET_KEY = config("SECRET_KEY", default="insecure-dev-key")
DEBUG = config("DEBUG", default=True, cast=bool)
ALLOWED_HOSTS = config("ALLOWED_HOSTS", default="127.0.0.1,localhost", cast=Csv())

# دامنه‌هایی که برای ارسال فرم‌های POST (CSRF) قابل اعتمادند.
# در production باید با https دامنه‌ی واقعی پر شود، مثلاً:
#   CSRF_TRUSTED_ORIGINS=https://clinic.example.com,https://www.clinic.example.com
CSRF_TRUSTED_ORIGINS = config(
    "CSRF_TRUSTED_ORIGINS", default="", cast=Csv()
)

# --------------------------------------------------------------------------- #
# Applications                                                                 #
# --------------------------------------------------------------------------- #
DJANGO_APPS = [
    "django.contrib.admin",
    "django.contrib.auth",
    "django.contrib.contenttypes",
    "django.contrib.sessions",
    "django.contrib.messages",
    "django.contrib.staticfiles",
]

THIRD_PARTY_APPS = [
    "ninja",
    "django_jalali",
]

LOCAL_APPS = [
    "apps.core",
    "apps.appointments",
    "apps.payments",
    "apps.dashboard",
    "apps.website",
]

INSTALLED_APPS = DJANGO_APPS + THIRD_PARTY_APPS + LOCAL_APPS

MIDDLEWARE = [
    "django.middleware.security.SecurityMiddleware",
    # WhiteNoise: سرو فایل‌های static به‌صورت فشرده و کش‌شده در production
    # (بدون نیاز به nginx برای static؛ ولی nginx همچنان برای پروکسی توصیه می‌شود)
    "whitenoise.middleware.WhiteNoiseMiddleware",
    "django.contrib.sessions.middleware.SessionMiddleware",
    "django.middleware.locale.LocaleMiddleware",
    "django.middleware.common.CommonMiddleware",
    "django.middleware.csrf.CsrfViewMiddleware",
    "django.contrib.auth.middleware.AuthenticationMiddleware",
    "django.contrib.messages.middleware.MessageMiddleware",
    "django.middleware.clickjacking.XFrameOptionsMiddleware",
]

ROOT_URLCONF = "config.urls"

TEMPLATES = [
    {
        "BACKEND": "django.template.backends.django.DjangoTemplates",
        "DIRS": [BASE_DIR / "templates"],
        "APP_DIRS": True,
        "OPTIONS": {
            "context_processors": [
                "django.template.context_processors.debug",
                "django.template.context_processors.request",
                "django.contrib.auth.context_processors.auth",
                "django.contrib.messages.context_processors.messages",
                "apps.core.context_processors.clinic",
            ],
        },
    },
]

WSGI_APPLICATION = "config.wsgi.application"
ASGI_APPLICATION = "config.asgi.application"

# --------------------------------------------------------------------------- #
# Database                                                                     #
# --------------------------------------------------------------------------- #
if config("USE_SQLITE", default=False, cast=bool):
    DATABASES = {
        "default": {
            "ENGINE": "django.db.backends.sqlite3",
            "NAME": BASE_DIR / "db.sqlite3",
        }
    }
else:
    DATABASES = {
        "default": {
            "ENGINE": "django.db.backends.postgresql",
            "NAME": config("DB_NAME", default="beauty_clinic"),
            "USER": config("DB_USER", default="postgres"),
            "PASSWORD": config("DB_PASSWORD", default="postgres"),
            "HOST": config("DB_HOST", default="127.0.0.1"),
            "PORT": config("DB_PORT", default="5432"),
        }
    }

# --------------------------------------------------------------------------- #
# Password validation                                                          #
# --------------------------------------------------------------------------- #
AUTH_PASSWORD_VALIDATORS = [
    {"NAME": "django.contrib.auth.password_validation.UserAttributeSimilarityValidator"},
    {"NAME": "django.contrib.auth.password_validation.MinimumLengthValidator"},
    {"NAME": "django.contrib.auth.password_validation.CommonPasswordValidator"},
    {"NAME": "django.contrib.auth.password_validation.NumericPasswordValidator"},
]

# --------------------------------------------------------------------------- #
# Internationalization — فارسی + RTL + تقویم شمسی                              #
# --------------------------------------------------------------------------- #
LANGUAGE_CODE = "fa-ir"
TIME_ZONE = config("TIME_ZONE", default="Asia/Tehran")
USE_I18N = True
USE_TZ = True

LANGUAGES = [
    ("fa", "فارسی"),
]

LOCALE_PATHS = [BASE_DIR / "locale"]

# --------------------------------------------------------------------------- #
# Static & Media                                                               #
# --------------------------------------------------------------------------- #
STATIC_URL = "static/"
STATICFILES_DIRS = [BASE_DIR / "static"]
STATIC_ROOT = BASE_DIR / "staticfiles"

MEDIA_URL = "media/"
MEDIA_ROOT = BASE_DIR / "media"

# ذخیره‌سازی فایل‌ها:
#   - static: WhiteNoise با هش‌گذاری و فشرده‌سازی (cache-busting + gzip/brotli)
#   - media : ذخیره‌سازی استاندارد فایل‌سیستم
STORAGES = {
    "default": {
        "BACKEND": "django.core.files.storage.FileSystemStorage",
    },
    "staticfiles": {
        "BACKEND": "apps.core.storage.WhiteNoiseStaticFilesStorage",
    },
}

DEFAULT_AUTO_FIELD = "django.db.models.BigAutoField"

# --------------------------------------------------------------------------- #
# Cache (برای rate limiting و موارد دیگر)                                      #
# --------------------------------------------------------------------------- #
CACHES = {
    "default": {
        "BACKEND": "django.core.cache.backends.locmem.LocMemCache",
        "LOCATION": "beauty-clinic-cache",
    }
}

# --------------------------------------------------------------------------- #
# Auth redirects (staff dashboard)                                            #
# --------------------------------------------------------------------------- #
LOGIN_URL = "dashboard:login"
LOGIN_REDIRECT_URL = "dashboard:home"
LOGOUT_REDIRECT_URL = "dashboard:login"

# --------------------------------------------------------------------------- #
# Project-specific settings                                                    #
# --------------------------------------------------------------------------- #
# نام و مشخصات کلینیک (در صورت نبود نمونه‌ای از مدل ClinicSettings)
CLINIC_NAME = "کلینیک زیبایی"

# --------------------------------------------------------------------------- #
# ZarinPal payment gateway                                                     #
# --------------------------------------------------------------------------- #
ZARINPAL_MERCHANT_ID = config(
    "ZARINPAL_MERCHANT_ID", default="00000000-0000-0000-0000-000000000000"
)
ZARINPAL_SANDBOX = config("ZARINPAL_SANDBOX", default=True, cast=bool)
# واحد مبلغ ارسالی به زرین‌پال: «تومان». قیمت خدمات نیز به تومان ذخیره می‌شود.
ZARINPAL_CURRENCY = "IRT"

# --------------------------------------------------------------------------- #
# Logging                                                                      #
# --------------------------------------------------------------------------- #
LOG_DIR = BASE_DIR / "logs"
LOG_DIR.mkdir(exist_ok=True)

LOGGING = {
    "version": 1,
    "disable_existing_loggers": False,
    "formatters": {
        "verbose": {
            "format": "[{asctime}] {levelname} {name}: {message}",
            "style": "{",
        },
    },
    "handlers": {
        "console": {
            "class": "logging.StreamHandler",
            "formatter": "verbose",
        },
        "file": {
            "class": "logging.handlers.RotatingFileHandler",
            "filename": LOG_DIR / "app.log",
            "maxBytes": 5 * 1024 * 1024,  # 5MB
            "backupCount": 5,
            "formatter": "verbose",
        },
    },
    "root": {
        "handlers": ["console", "file"],
        "level": "INFO",
    },
    "loggers": {
        "django": {
            "handlers": ["console", "file"],
            "level": config("DJANGO_LOG_LEVEL", default="INFO"),
            "propagate": False,
        },
        "django.security": {
            "handlers": ["console", "file"],
            "level": "WARNING",
            "propagate": False,
        },
        "apps": {
            "handlers": ["console", "file"],
            "level": "INFO",
            "propagate": False,
        },
    },
}

# --------------------------------------------------------------------------- #
# Production hardening                                                          #
# این تنظیمات فقط زمانی اعمال می‌شوند که DEBUG=False باشد (یعنی محیط production).#
# پشت یک reverse-proxy با TLS (nginx) قرار می‌گیرد.                            #
# --------------------------------------------------------------------------- #
if not DEBUG:
    # هدر پروکسی برای تشخیص HTTPS (nginx باید X-Forwarded-Proto را ست کند)
    SECURE_PROXY_SSL_HEADER = ("HTTP_X_FORWARDED_PROTO", "https")

    # هدایت اجباری همه‌ی درخواست‌ها به HTTPS
    SECURE_SSL_REDIRECT = config("SECURE_SSL_REDIRECT", default=True, cast=bool)

    # کوکی‌ها فقط روی اتصال امن ارسال شوند
    SESSION_COOKIE_SECURE = True
    CSRF_COOKIE_SECURE = True

    # محافظت در برابر دسترسی جاوااسکریپت به کوکی‌ها
    SESSION_COOKIE_HTTPONLY = True
    CSRF_COOKIE_HTTPONLY = False  # HTMX باید بتواند توکن را بخواند

    # SameSite برای کاهش CSRF
    SESSION_COOKIE_SAMESITE = "Lax"
    CSRF_COOKIE_SAMESITE = "Lax"

    # HSTS — مرورگر را وادار به استفاده‌ی همیشگی از HTTPS می‌کند
    SECURE_HSTS_SECONDS = config("SECURE_HSTS_SECONDS", default=31536000, cast=int)
    SECURE_HSTS_INCLUDE_SUBDOMAINS = True
    SECURE_HSTS_PRELOAD = True

    # هدرهای امنیتی اضافی
    SECURE_CONTENT_TYPE_NOSNIFF = True
    SECURE_REFERRER_POLICY = "same-origin"
    X_FRAME_OPTIONS = "DENY"

    # مدت اعتبار session (۲ هفته) و بستن مرورگر
    SESSION_COOKIE_AGE = 60 * 60 * 24 * 14
