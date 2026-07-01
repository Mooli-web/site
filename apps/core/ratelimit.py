"""
محدودسازی نرخ درخواست (rate limiting) ساده مبتنی بر cache جنگو.
برای جلوگیری از سوءاستفاده از فرم ثبت نوبت (بدون احراز هویت).
"""
from functools import wraps

from django.core.cache import cache
from django.shortcuts import render


def get_client_ip(request):
    """استخراج IP کلاینت با در نظر گرفتن پراکسی."""
    xff = request.META.get("HTTP_X_FORWARDED_FOR")
    if xff:
        return xff.split(",")[0].strip()
    return request.META.get("REMOTE_ADDR", "0.0.0.0")


def rate_limit(key, limit=5, window=600, methods=None):
    """
    دکوریتور محدودسازی نرخ.

    پارامترها:
      key     : پیشوند کلید (برای تفکیک نقاط مختلف)
      limit   : حداکثر تعداد درخواست مجاز
      window  : بازه‌ی زمانی به ثانیه
      methods : فقط روی این متدها اعمال شود (پیش‌فرض: همه)
    در صورت عبور از حد مجاز، پاسخ ۴۲۹ با قالب فارسی برگردانده می‌شود.
    """
    def decorator(view_func):
        @wraps(view_func)
        def wrapper(request, *args, **kwargs):
            if methods and request.method not in methods:
                return view_func(request, *args, **kwargs)

            ip = get_client_ip(request)
            cache_key = f"rl:{key}:{ip}"
            count = cache.get(cache_key, 0)

            if count >= limit:
                response = render(
                    request,
                    "website/partials/rate_limited.html",
                    {"window_minutes": window // 60},
                    status=429,
                )
                return response

            # افزایش شمارنده با حفظ TTL پنجره
            try:
                added = cache.add(cache_key, 1, timeout=window)
                if not added:
                    cache.incr(cache_key)
            except ValueError:
                cache.set(cache_key, 1, timeout=window)

            return view_func(request, *args, **kwargs)

        return wrapper
    return decorator
