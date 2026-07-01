"""
پیکربندی مسیرهای اصلی پروژه.
"""
from django.conf import settings
from django.conf.urls.static import static
from django.contrib import admin
from django.urls import include, path

from config.api import api

urlpatterns = [
    path("admin/", admin.site.urls),
    path("api/", api.urls),
    path("dashboard/", include("apps.dashboard.urls")),
    path("payment/", include("apps.payments.urls")),
    path("", include("apps.website.urls")),
]

if settings.DEBUG:
    urlpatterns += static(settings.MEDIA_URL, document_root=settings.MEDIA_ROOT)
    urlpatterns += static(settings.STATIC_URL, document_root=settings.STATIC_ROOT)

# فارسی‌سازی پنل ادمین
admin.site.site_header = "مدیریت کلینیک زیبایی"
admin.site.site_title = "کلینیک زیبایی"
admin.site.index_title = "پنل مدیریت"
