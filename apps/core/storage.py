"""
ذخیره‌سازی سفارشی فایل‌های static برای production.

مبتنی بر WhiteNoise با هش‌گذاری (manifest) و فشرده‌سازی gzip/brotli.

نکته: برخی پکیج‌های شخص‌ثالث (مانند jquery-ui همراه django-jalali) در فایل‌های
CSS خود به تصاویری ارجاع می‌دهند که در بسته‌ی پکیج وجود ندارند. در حالت پیش‌فرض
این موضوع باعث خطای MissingFileError و توقف کامل collectstatic می‌شود.

این storage:
  - manifest_strict = False  → در زمان اجرا مرجع‌های نامعتبر باعث خطا نشوند.
  - post_process بازنویسی‌شده → مرجع‌های شکسته در CSS را فقط هشدار می‌دهد و
    رد می‌شود؛ سایر فایل‌ها به‌درستی هش و فشرده می‌شوند.
"""
from whitenoise.storage import (
    CompressedManifestStaticFilesStorage,
    MissingFileError,
)


class WhiteNoiseStaticFilesStorage(CompressedManifestStaticFilesStorage):
    # مرجع‌های نامعتبر در زمان اجرا (lookup) باعث خطا نشوند.
    manifest_strict = False

    def post_process(self, *args, **kwargs):
        """هنگام پردازش، مرجع‌های گمشده‌ی پکیج‌های شخص‌ثالث را نادیده بگیر."""
        for name, hashed_name, processed in super().post_process(*args, **kwargs):
            if isinstance(processed, Exception):
                # مرجع شکسته در یک فایل CSS شخص‌ثالث → فقط هشدار، نه توقف.
                if isinstance(processed, (MissingFileError, ValueError)):
                    yield name, name, False
                    continue
            yield name, hashed_name, processed
