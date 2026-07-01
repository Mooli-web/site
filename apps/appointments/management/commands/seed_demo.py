"""
ساخت داده‌های نمونه برای تست فلوی رزرو (کلینیک ساده).
اجرا:  python manage.py seed_demo
"""
from datetime import time

from django.core.management.base import BaseCommand
from django.utils.text import slugify

from apps.appointments.models import Service, ServiceCategory, WorkingHour
from apps.core.models import ClinicSettings


class Command(BaseCommand):
    help = "ساخت داده‌های نمونه (دسته‌بندی‌ها، خدمات و ساعات کاری کلینیک)"

    def handle(self, *args, **options):
        clinic = ClinicSettings.load()
        clinic.name = "کلینیک زیبایی رُز"
        clinic.phone = "۰۲۱-۸۸۸۸۸۸۸۸"
        clinic.address = "تهران، خیابان ولیعصر، نبش کوچه بهار، پلاک ۱۲۰"
        clinic.about = "ارائه خدمات تخصصی پوست، مو و زیبایی با جدیدترین تجهیزات روز دنیا."
        clinic.instagram = "@rose.clinic"
        clinic.slot_step_minutes = 30
        clinic.max_advance_days = 30
        clinic.min_advance_hours = 2
        clinic.save()

        data = {
            "پوست و زیبایی": [
                ("پاکسازی پوست", 45, 350000),
                ("جوان‌سازی صورت", 60, 800000),
                ("میکرونیدلینگ", 60, 1200000),
            ],
            "لیزر": [
                ("لیزر موهای زائد (نواحی کوچک)", 30, 250000),
                ("لیزر موهای زائد (کل بدن)", 90, 1500000),
            ],
            "مو": [
                ("کوتاهی و حالت‌دهی", 45, 200000),
                ("رنگ و مش", 120, 950000),
                ("کراتینه", 120, 1800000),
            ],
            "ناخن و میکاپ": [
                ("مانیکور", 45, 300000),
                ("میکاپ عروس", 120, 2500000),
            ],
        }

        order = 0
        for cat_title, services in data.items():
            cat, _ = ServiceCategory.objects.get_or_create(
                title=cat_title,
                defaults={"slug": slugify(cat_title, allow_unicode=True), "order": order},
            )
            order += 1
            for s_order, (title, dur, price) in enumerate(services):
                Service.objects.get_or_create(
                    title=title,
                    defaults={
                        "category": cat,
                        "slug": slugify(title, allow_unicode=True),
                        "duration_minutes": dur,
                        "price": price,
                        "order": s_order,
                    },
                )

        # ساعات کاری کلینیک: شنبه(۰) تا چهارشنبه(۴)، ۹–۱۳ و ۱۵–۱۸
        if not WorkingHour.objects.exists():
            for wd in [0, 1, 2, 3, 4]:
                WorkingHour.objects.create(weekday=wd, start_time=time(9, 0), end_time=time(13, 0))
                WorkingHour.objects.create(weekday=wd, start_time=time(15, 0), end_time=time(18, 0))

        self.stdout.write(self.style.SUCCESS(
            f"✓ داده‌های نمونه ساخته شد: "
            f"{ServiceCategory.objects.count()} دسته، "
            f"{Service.objects.count()} خدمت، "
            f"{WorkingHour.objects.count()} بازه‌ی کاری."
        ))
