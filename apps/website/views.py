"""
ویوهای بخش عمومی سایت (مشتریان): صفحه اصلی + فلوی کامل رزرو نوبت.

فلوی رزرو (کلینیک ساده، بدون انتخاب متخصص) با HTMX:
  ۱) انتخاب خدمت → ۲) تقویم شمسی (انتخاب روز) → ۳) اسلات‌های خالی
  → ۴) فرم نام/موبایل → موفقیت
"""
import jdatetime
from django.http import Http404, HttpResponse
from django.shortcuts import get_object_or_404, render
from django.urls import reverse
from django.utils.safestring import mark_safe
from django.views.decorators.csrf import ensure_csrf_cookie
from django.views.decorators.http import require_GET, require_POST

from apps.appointments.calendar_utils import to_fa_digits, build_month
from apps.appointments.models import Service, ServiceCategory
from apps.appointments.services import (
    BookingError,
    create_appointment,
    get_available_slots,
)
from apps.core.models import ClinicSettings, ContactMessage
from apps.core.ratelimit import rate_limit
from apps.website.forms import BookingForm, ConsultationForm, ContactForm
from apps.website.models import BeforeAfter, ConsultationRequest, FAQ

_ICON = {
    "skin": '<svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M9 10h.01M15 10h.01M9 15c1 1 5 1 6 0"/></svg>',
    "laser": '<svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M13 2L3 14h7l-1 8 10-12h-7l1-8z"/></svg>',
    "hair": '<svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4c4 4 4 12 0 16M12 4c4 4 4 12 0 16M20 4c-4 4-4 12 0 16"/></svg>',
    "nail": '<svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><rect x="8" y="3" width="8" height="18" rx="4"/><path stroke-linecap="round" d="M8 9h8"/></svg>',
}

_FA_WEEKDAYS = {
    "Saturday": "شنبه", "Sunday": "یکشنبه", "Monday": "دوشنبه",
    "Tuesday": "سه‌شنبه", "Wednesday": "چهارشنبه",
    "Thursday": "پنجشنبه", "Friday": "جمعه",
}


def _pretty_date(jdate, with_weekday=False):
    name = jdate.j_months_fa[jdate.month - 1]
    base = f"{to_fa_digits(jdate.day)} {name} {to_fa_digits(jdate.year)}"
    if with_weekday:
        wd = _FA_WEEKDAYS.get(jdate.strftime("%A"), "")
        return f"{wd} {base}"
    return base


def _parse_jdate(date_iso):
    try:
        y, m, d = (int(x) for x in date_iso.split("-"))
        return jdatetime.date(y, m, d)
    except (ValueError, TypeError, AttributeError):
        raise Http404("تاریخ نامعتبر است.")


# --------------------------------------------------------------------------- #
# صفحه اصلی                                                                    #
# --------------------------------------------------------------------------- #
def home(request):
    features = [
        {"title": "مراقبت پوست", "desc": "پاکسازی، آبرسانی و جوان‌سازی پوست با جدیدترین متدهای روز دنیا.",
         "icon": mark_safe(_ICON["skin"]), "color": "from-blush-400 to-blush-500"},
        {"title": "لیزر و زیبایی", "desc": "حذف موهای زائد و درمان‌های لیزری ایمن و مؤثر زیر نظر متخصص.",
         "icon": mark_safe(_ICON["laser"]), "color": "from-sand-400 to-sand-500"},
        {"title": "خدمات مو", "desc": "رنگ، کراتینه، کوتاهی و مراقبت تخصصی مو توسط استایلیست‌های حرفه‌ای.",
         "icon": mark_safe(_ICON["hair"]), "color": "from-blush-400 to-sand-400"},
        {"title": "ناخن و میکاپ", "desc": "مانیکور، پدیکور و میکاپ تخصصی برای روزهای خاص شما.",
         "icon": mark_safe(_ICON["nail"]), "color": "from-sand-400 to-blush-400"},
    ]
    reasons = [
        {"title": "تیم متخصص و مجرب", "desc": "کادری از بهترین متخصصان حوزه پوست، مو و زیبایی در کنار شما."},
        {"title": "تجهیزات مدرن و بهداشتی", "desc": "استفاده از به‌روزترین دستگاه‌ها با رعایت کامل اصول بهداشتی."},
        {"title": "رزرو آنلاین بدون ثبت‌نام", "desc": "تنها با نام و شماره موبایل، در چند ثانیه نوبت خود را رزرو کنید."},
        {"title": "محیطی آرام و لوکس", "desc": "فضایی دلنشین برای اینکه لحظات مراقبت از خود را به‌آرامی سپری کنید."},
    ]
    return render(request, "website/home.html", {"features": features, "reasons": reasons})


# --------------------------------------------------------------------------- #
# صفحات ثابت: درباره ما و تماس با ما                                           #
# --------------------------------------------------------------------------- #
def about(request):
    """صفحه «درباره ما»."""
    values = [
        {"title": "تخصص و تجربه", "desc": "بیش از یک دهه تجربه در حوزه‌ی پوست، مو و زیبایی با کادری مجرب."},
        {"title": "بهداشت و ایمنی", "desc": "رعایت کامل پروتکل‌های بهداشتی و استفاده از تجهیزات استریل."},
        {"title": "رضایت مشتری", "desc": "تمرکز ما بر آرامش، اعتماد و رضایت کامل مراجعان است."},
        {"title": "فناوری روز", "desc": "بهره‌گیری از به‌روزترین دستگاه‌ها و متدهای علمی دنیا."},
    ]
    stats = [
        {"num": "۱۲+", "label": "سال تجربه"},
        {"num": "۲۰٬۰۰۰+", "label": "مشتری راضی"},
        {"num": "۱۵+", "label": "متخصص حرفه‌ای"},
        {"num": "۴۰+", "label": "خدمت تخصصی"},
    ]
    return render(request, "website/about.html", {"values": values, "stats": stats})


@ensure_csrf_cookie
def contact(request):
    """صفحه «تماس با ما» (فرم اولیه)."""
    return render(request, "website/contact.html", {"form": ContactForm()})


@require_POST
@rate_limit(key="contact", limit=5, window=600, methods=["POST"])
def contact_submit(request):
    """ثبت پیام تماس (HTMX POST). حداکثر ۵ پیام در ۱۰ دقیقه برای هر IP."""
    form = ContactForm(request.POST)
    if not form.is_valid():
        return render(request, "website/partials/contact_form.html", {"form": form})

    cd = form.cleaned_data
    ContactMessage.objects.create(
        name=cd["name"],
        mobile=cd["mobile"],
        subject=cd.get("subject", ""),
        message=cd["message"],
    )
    return render(request, "website/partials/contact_success.html", {"name": cd["name"]})


# --------------------------------------------------------------------------- #
# گالری قبل و بعد                                                              #
# --------------------------------------------------------------------------- #
def before_after(request):
    """صفحه‌ی گالری «قبل و بعد»."""
    items = BeforeAfter.objects.filter(is_active=True)
    return render(request, "website/before_after.html", {"items": items})


# --------------------------------------------------------------------------- #
# سوالات متداول                                                                #
# --------------------------------------------------------------------------- #
def faq(request):
    """صفحه‌ی پرسش‌های متداول (آکاردئون)."""
    faqs = FAQ.objects.filter(is_active=True)
    return render(request, "website/faq.html", {"faqs": faqs})


# --------------------------------------------------------------------------- #
# مشاوره رایگان                                                                #
# --------------------------------------------------------------------------- #
@ensure_csrf_cookie
def consultation(request):
    """صفحه‌ی درخواست مشاوره رایگان (فرم اولیه)."""
    return render(request, "website/consultation.html", {"form": ConsultationForm()})


@require_POST
@rate_limit(key="consultation", limit=5, window=600, methods=["POST"])
def consultation_submit(request):
    """ثبت درخواست مشاوره (HTMX POST). حداکثر ۵ درخواست در ۱۰ دقیقه برای هر IP."""
    form = ConsultationForm(request.POST)
    if not form.is_valid():
        return render(request, "website/partials/consultation_form.html", {"form": form})

    cd = form.cleaned_data
    ConsultationRequest.objects.create(
        first_name=cd["first_name"],
        last_name=cd["last_name"],
        mobile=cd["mobile"],
        message=cd.get("message", ""),
    )
    return render(
        request,
        "website/partials/consultation_success.html",
        {"name": cd["first_name"]},
    )


# --------------------------------------------------------------------------- #
# فلوی رزرو                                                                    #
# --------------------------------------------------------------------------- #
@ensure_csrf_cookie
def booking(request):
    """مرحله ۱: انتخاب خدمت."""
    categories = (
        ServiceCategory.objects.filter(is_active=True)
        .prefetch_related("services")
        .order_by("order", "title")
    )
    return render(request, "website/booking.html", {"categories": categories})


@require_GET
def booking_calendar(request):
    """مرحله ۲: نمایش تقویم شمسی برای خدمت انتخاب‌شده (HTMX)."""
    service = get_object_or_404(Service, pk=request.GET.get("service"), is_active=True)
    clinic = ClinicSettings.load()

    today = jdatetime.date.today()
    min_date = today
    max_date = today + jdatetime.timedelta(days=clinic.max_advance_days)

    try:
        year = int(request.GET.get("year", today.year))
        month = int(request.GET.get("month", today.month))
        if not (1 <= month <= 12):
            raise ValueError
    except (TypeError, ValueError):
        year, month = today.year, today.month

    month_data = build_month(year, month, today, min_date, max_date)
    return render(request, "website/partials/step_calendar.html", {
        "service": service, "month": month_data,
    })


@require_GET
def booking_slots(request):
    """مرحله ۳: نمایش اسلات‌های خالی یک روز مشخص (HTMX)."""
    service = get_object_or_404(Service, pk=request.GET.get("service"), is_active=True)
    jdate = _parse_jdate(request.GET.get("date", ""))
    slots = get_available_slots(service, jdate)
    return render(request, "website/partials/step_slots.html", {
        "service": service,
        "date_iso": request.GET.get("date", ""),
        "pretty_date": _pretty_date(jdate, with_weekday=True),
        "slots": slots,
    })


@require_GET
def booking_confirm(request):
    """مرحله ۴: نمایش فرم نام/موبایل پس از انتخاب اسلات (HTMX)."""
    service = get_object_or_404(Service, pk=request.GET.get("service"), is_active=True)
    date_iso = request.GET.get("date", "")
    jdate = _parse_jdate(date_iso)
    start = request.GET.get("start", "")
    return render(request, "website/partials/step_form.html", {
        "service": service,
        "date_iso": date_iso,
        "start": start,
        "start_fa": to_fa_digits(start),
        "pretty_date": _pretty_date(jdate),
    })


@require_POST
@rate_limit(key="booking", limit=5, window=600, methods=["POST"])
def booking_submit(request):
    """ثبت نهایی نوبت (HTMX POST). محدودیت: حداکثر ۵ تلاش در ۱۰ دقیقه برای هر IP."""
    form = BookingForm(request.POST)
    if not form.is_valid():
        return _render_form_with_errors(request, form)

    cd = form.cleaned_data
    service = get_object_or_404(Service, pk=cd["service"], is_active=True)

    try:
        y, m, d = (int(x) for x in cd["date"].split("-"))
        jdate = jdatetime.date(y, m, d)
    except (ValueError, TypeError):
        form.add_error(None, "تاریخ نامعتبر است.")
        return _render_form_with_errors(request, form)

    try:
        appointment = create_appointment(
            first_name=cd["first_name"],
            last_name=cd["last_name"],
            mobile=cd["mobile"],
            service=service,
            d=jdate,
            start=cd["start"],
            customer_note=cd.get("customer_note", ""),
        )
    except BookingError as exc:
        form.add_error(None, str(exc))
        return _render_form_with_errors(request, form)

    # پس از ثبت موفق، کاربر را به صفحه‌ی پرداخت هدایت کن (HTMX redirect)
    pay_url = reverse("payments:pay", args=[appointment.tracking_code])
    response = HttpResponse(status=204)
    response["HX-Redirect"] = pay_url
    return response


def _render_form_with_errors(request, form):
    data = form.data
    service = Service.objects.filter(pk=data.get("service")).first()
    date_iso = data.get("date", "")
    pretty_date = ""
    try:
        y, m, d = (int(x) for x in date_iso.split("-"))
        pretty_date = _pretty_date(jdatetime.date(y, m, d))
    except (ValueError, TypeError):
        pass
    return render(request, "website/partials/step_form.html", {
        "service": service,
        "date_iso": date_iso,
        "start": data.get("start", ""),
        "start_fa": to_fa_digits(data.get("start", "")),
        "pretty_date": pretty_date,
        "form": form,
        "submitted_values": data,
    })
