"""
منطق دامنه (business logic) نوبت‌دهی برای کلینیک ساده (بدون مفهوم متخصص):
- محاسبه‌ی اسلات‌های زمانی خالی کلینیک در یک روز مشخص
- اعتبارسنجی و ثبت نوبت

تمام تاریخ‌ها به‌صورت شمسی (jdatetime.date) پردازش می‌شوند.
ساعات کاری بر اساس روز هفته (شنبه=۰ ... جمعه=۶ — منطبق با jdatetime.weekday()) تعریف می‌شوند.
"""
from __future__ import annotations

from datetime import datetime, time, timedelta

import jdatetime
from django.db import transaction
from django.utils import timezone

from apps.appointments.models import Appointment, Holiday, Service, WorkingHour
from apps.core.models import ClinicSettings, Customer


def _combine(d: jdatetime.date, t: time) -> datetime:
    """ترکیب تاریخ شمسی و ساعت به datetime میلادی آگاه از تایم‌زون."""
    g = d.togregorian()
    naive = datetime(g.year, g.month, g.day, t.hour, t.minute)
    return timezone.make_aware(naive, timezone.get_current_timezone())


def _add_minutes(t: time, minutes: int) -> time:
    base = datetime(2000, 1, 1, t.hour, t.minute)
    return (base + timedelta(minutes=minutes)).time()


def is_holiday(d: jdatetime.date) -> bool:
    """آیا این روز تعطیل کلینیک است؟"""
    return Holiday.objects.filter(date=d).exists()


def get_available_slots(
    service: Service,
    d: jdatetime.date,
    clinic: ClinicSettings | None = None,
) -> list[dict]:
    """
    فهرست اسلات‌های خالی کلینیک برای یک خدمت در یک روز شمسی.

    خروجی: لیستی از دیکشنری‌ها:
        {"start": "09:00", "end": "09:30", "start_time": time, "end_time": time}
    اسلات‌های گذشته یا دارای تداخل با نوبت‌های فعال حذف می‌شوند.
    """
    clinic = clinic or ClinicSettings.load()

    if is_holiday(d):
        return []

    weekday = d.weekday()  # شنبه=۰ ... جمعه=۶
    working = WorkingHour.objects.filter(weekday=weekday, is_active=True).order_by("start_time")
    if not working.exists():
        return []

    duration = service.duration_minutes
    step = max(clinic.slot_step_minutes, 5)

    # نوبت‌های فعال موجود در این روز (هر اسلات کلینیک ظرفیت ۱ دارد).
    # نوبت‌های لغوشده یا با پرداخت ناموفق، اسلات را اشغال نمی‌کنند.
    busy = list(
        Appointment.objects.filter(date=d)
        .exclude(status__in=[
            Appointment.Status.CANCELLED,
            Appointment.Status.PAYMENT_FAILED,
        ])
        .values_list("start_time", "end_time")
    )

    earliest = timezone.now() + timedelta(hours=clinic.min_advance_hours)

    slots: list[dict] = []
    for wh in working:
        cursor = wh.start_time
        while True:
            slot_end = _add_minutes(cursor, duration)
            if slot_end > wh.end_time:
                break

            if _combine(d, cursor) < earliest:
                cursor = _add_minutes(cursor, step)
                continue

            overlaps = any(
                (cursor < b_end and slot_end > b_start) for b_start, b_end in busy
            )
            if not overlaps:
                slots.append({
                    "start": cursor.strftime("%H:%M"),
                    "end": slot_end.strftime("%H:%M"),
                    "start_time": cursor,
                    "end_time": slot_end,
                })
            cursor = _add_minutes(cursor, step)

    return slots


def has_any_availability(service: Service, d: jdatetime.date,
                         clinic: ClinicSettings | None = None) -> bool:
    """آیا روز موردنظر حداقل یک اسلات خالی دارد؟ (برای علامت‌گذاری در تقویم)"""
    return bool(get_available_slots(service, d, clinic))


class BookingError(Exception):
    """خطای منطقی هنگام ثبت نوبت (پیام فارسی قابل نمایش به کاربر)."""


@transaction.atomic
def create_appointment(
    *,
    first_name: str,
    last_name: str,
    mobile: str,
    service: Service,
    d: jdatetime.date,
    start: str,  # "HH:MM"
    customer_note: str = "",
) -> Appointment:
    """
    ثبت یک نوبت جدید همراه با ایجاد/یافتن مشتری.
    در صورت مشکل، BookingError با پیام فارسی پرتاب می‌شود.
    """
    clinic = ClinicSettings.load()

    available = {s["start"]: s for s in get_available_slots(service, d, clinic)}
    if start not in available:
        raise BookingError("این زمان دیگر در دسترس نیست. لطفاً زمان دیگری انتخاب کنید.")

    slot = available[start]

    customer, created = Customer.objects.get_or_create(
        mobile=mobile,
        defaults={"first_name": first_name, "last_name": last_name},
    )
    if not created and (customer.first_name != first_name or customer.last_name != last_name):
        customer.first_name = first_name
        customer.last_name = last_name
        customer.save(update_fields=["first_name", "last_name"])

    if customer.is_blocked:
        raise BookingError("امکان رزرو برای این شماره وجود ندارد. لطفاً با کلینیک تماس بگیرید.")

    try:
        appointment = Appointment.objects.create(
            customer=customer,
            service=service,
            date=d,
            start_time=slot["start_time"],
            end_time=slot["end_time"],
            price=service.price,
            customer_note=customer_note,
            # نوبت تا زمان پرداخت در حالت «در انتظار پرداخت» است
            status=Appointment.Status.WAITING_PAYMENT,
        )
    except Exception:
        raise BookingError("متأسفانه این زمان همین‌الان رزرو شد. لطفاً زمان دیگری انتخاب کنید.")

    return appointment
