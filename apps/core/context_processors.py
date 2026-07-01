"""
context processorهای سراسری.
اطلاعات کلینیک و سال جاری (شمسی) را در همه‌ی قالب‌ها در دسترس می‌گذارد.
"""
import jdatetime

from apps.core.models import ClinicSettings


def clinic(request):
    try:
        settings_obj = ClinicSettings.load()
    except Exception:
        # در صورتی که هنوز جدول ساخته نشده باشد (مثلاً قبل از migrate)
        settings_obj = None
    return {
        "clinic": settings_obj,
        "now_year": jdatetime.date.today().year,
    }
