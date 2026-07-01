"""
ابزارهای تقویم شمسی (جلالی) برای نمایش ماه در فرانت‌اند.
"""
from __future__ import annotations

import jdatetime

# نام ماه‌های شمسی
PERSIAN_MONTHS = [
    "فروردین", "اردیبهشت", "خرداد", "تیر", "مرداد", "شهریور",
    "مهر", "آبان", "آذر", "دی", "بهمن", "اسفند",
]

# سرستون‌های تقویم به ترتیب شنبه تا جمعه
WEEK_HEADERS = ["ش", "ی", "د", "س", "چ", "پ", "ج"]

# ارقام فارسی
_FA_DIGITS = str.maketrans("0123456789", "۰۱۲۳۴۵۶۷۸۹")


def to_fa_digits(value) -> str:
    return str(value).translate(_FA_DIGITS)


def days_in_jmonth(year: int, month: int) -> int:
    """تعداد روزهای یک ماه شمسی."""
    if month <= 6:
        return 31
    if month <= 11:
        return 30
    # اسفند: ۲۹ یا ۳۰ (سال کبیسه)
    return 30 if jdatetime.date(year, 1, 1).isleap() else 29


def build_month(year: int, month: int, today: jdatetime.date,
                min_date: jdatetime.date, max_date: jdatetime.date) -> dict:
    """
    ساخت ساختار یک ماه شمسی برای رندر تقویم.

    خروجی شامل هفته‌هاست؛ هر روز یک دیکشنری با وضعیت‌های انتخاب‌پذیری.
    ستون اول = شنبه (مطابق jdatetime.weekday()).
    """
    first = jdatetime.date(year, month, 1)
    start_weekday = first.weekday()  # شنبه=۰
    total_days = days_in_jmonth(year, month)

    cells: list[dict | None] = [None] * start_weekday
    for day in range(1, total_days + 1):
        d = jdatetime.date(year, month, day)
        selectable = (min_date <= d <= max_date)
        cells.append({
            "day": day,
            "day_fa": to_fa_digits(day),
            "iso": d.isoformat(),               # مثلاً 1403-04-08 (برای ارسال به سرور)
            "is_today": d == today,
            "is_past": d < today,
            "selectable": selectable,
        })
    # تکمیل تا مضرب ۷
    while len(cells) % 7 != 0:
        cells.append(None)

    weeks = [cells[i:i + 7] for i in range(0, len(cells), 7)]

    # ماه قبل/بعد برای ناوبری
    prev_y, prev_m = (year, month - 1) if month > 1 else (year - 1, 12)
    next_y, next_m = (year, month + 1) if month < 12 else (year + 1, 1)

    # محدودسازی ناوبری به بازه‌ی مجاز
    min_ym = (min_date.year, min_date.month)
    max_ym = (max_date.year, max_date.month)

    return {
        "year": year,
        "month": month,
        "month_name": PERSIAN_MONTHS[month - 1],
        "year_fa": to_fa_digits(year),
        "headers": WEEK_HEADERS,
        "weeks": weeks,
        "prev": {"year": prev_y, "month": prev_m,
                 "enabled": (prev_y, prev_m) >= min_ym},
        "next": {"year": next_y, "month": next_m,
                 "enabled": (next_y, next_m) <= max_ym},
    }
