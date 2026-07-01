"""
ویوهای پنل منشی/مدیریت کلینیک.

ورود با username/password جنگو. تمام ویوها نیازمند کاربر staff هستند.
آدرس پایه: /dashboard/
"""
import jdatetime
from django.contrib import messages
from django.contrib.auth import authenticate, login, logout
from django.contrib.auth.decorators import login_required, user_passes_test
from django.db.models import Count, Q, Sum
from django.http import Http404, HttpResponse
from django.shortcuts import get_object_or_404, redirect, render
from django.urls import reverse
from django.views.decorators.http import require_POST

from apps.appointments.calendar_utils import to_fa_digits
from apps.appointments.models import Appointment, Service
from apps.core.models import Customer
from apps.website.models import ConsultationRequest

# روزهای هفته فارسی
_FA_WEEKDAYS = {
    "Saturday": "شنبه", "Sunday": "یکشنبه", "Monday": "دوشنبه",
    "Tuesday": "سه‌شنبه", "Wednesday": "چهارشنبه",
    "Thursday": "پنجشنبه", "Friday": "جمعه",
}


def _staff_required(view):
    """فقط کاربران staff (منشی/مدیر) اجازه دسترسی دارند."""
    return login_required(
        user_passes_test(lambda u: u.is_staff, login_url="dashboard:login")(view),
        login_url="dashboard:login",
    )


def _pretty_date(jdate, weekday=True):
    name = jdate.j_months_fa[jdate.month - 1]
    base = f"{to_fa_digits(jdate.day)} {name} {to_fa_digits(jdate.year)}"
    if weekday:
        wd = _FA_WEEKDAYS.get(jdate.strftime("%A"), "")
        return f"{wd} {base}"
    return base


# --------------------------------------------------------------------------- #
# احراز هویت                                                                   #
# --------------------------------------------------------------------------- #
def login_view(request):
    if request.user.is_authenticated and request.user.is_staff:
        return redirect("dashboard:home")

    error = None
    if request.method == "POST":
        username = request.POST.get("username", "").strip()
        password = request.POST.get("password", "")
        user = authenticate(request, username=username, password=password)
        if user is not None and user.is_staff:
            login(request, user)
            return redirect("dashboard:home")
        error = "نام کاربری یا رمز عبور نادرست است، یا دسترسی شما کافی نیست."

    return render(request, "dashboard/login.html", {"error": error})


@login_required(login_url="dashboard:login")
def logout_view(request):
    logout(request)
    return redirect("dashboard:login")


# --------------------------------------------------------------------------- #
# داشبورد اصلی + لیست نوبت‌ها                                                  #
# --------------------------------------------------------------------------- #
@_staff_required
def home(request):
    """صفحه‌ی اصلی داشبورد: آمار خلاصه + لیست نوبت‌ها با فیلتر."""
    today = jdatetime.date.today()
    tomorrow = today + jdatetime.timedelta(days=1)
    week_end = today + jdatetime.timedelta(days=7)

    base_qs = Appointment.objects.select_related("customer", "service")

    # ---------- آمار خلاصه ----------
    active_filter = ~Q(status=Appointment.Status.CANCELLED)
    stats = {
        "today": base_qs.filter(date=today).filter(active_filter).count(),
        "tomorrow": base_qs.filter(date=tomorrow).filter(active_filter).count(),
        "week": base_qs.filter(date__gte=today, date__lte=week_end).filter(active_filter).count(),
        "pending": base_qs.filter(status=Appointment.Status.PENDING).count(),
        "total": base_qs.count(),
        "customers": Customer.objects.count(),
        "revenue_today": base_qs.filter(
            date=today, status=Appointment.Status.PAID
        ).aggregate(s=Sum("price"))["s"] or 0,
        "consultations_new": ConsultationRequest.objects.filter(
            status=ConsultationRequest.Status.NEW
        ).count(),
    }

    # ---------- فیلترها ----------
    qs = base_qs
    quick = request.GET.get("range", "")        # today / tomorrow / week / all
    status = request.GET.get("status", "")
    search = request.GET.get("q", "").strip()
    date_filter = request.GET.get("date", "").strip()  # 1405-04-13

    applied = []

    if quick == "today":
        qs = qs.filter(date=today); applied.append("امروز")
    elif quick == "tomorrow":
        qs = qs.filter(date=tomorrow); applied.append("فردا")
    elif quick == "week":
        qs = qs.filter(date__gte=today, date__lte=week_end); applied.append("هفته جاری")

    if date_filter:
        try:
            y, m, d = (int(x) for x in date_filter.split("-"))
            qs = qs.filter(date=jdatetime.date(y, m, d))
            applied.append(_pretty_date(jdatetime.date(y, m, d), weekday=False))
        except (ValueError, TypeError):
            pass

    if status and status in Appointment.Status.values:
        qs = qs.filter(status=status)
        applied.append(dict(Appointment.Status.choices)[status])

    if search:
        qs = qs.filter(
            Q(customer__first_name__icontains=search)
            | Q(customer__last_name__icontains=search)
            | Q(customer__mobile__icontains=search)
            | Q(tracking_code__icontains=search)
        )
        applied.append(f"جستجو: {search}")

    qs = qs.order_by("-date", "-start_time")[:200]

    context = {
        "stats": stats,
        "appointments": qs,
        "status_choices": Appointment.Status.choices,
        "filters": {
            "range": quick, "status": status, "q": search, "date": date_filter,
        },
        "applied": applied,
        "today_fa": _pretty_date(today),
    }
    # درخواست HTMX → فقط جدول را بازگردان
    if request.headers.get("HX-Request"):
        return render(request, "dashboard/partials/appointments_table.html", context)
    return render(request, "dashboard/home.html", context)


@_staff_required
@require_POST
def change_status(request, pk):
    """تغییر وضعیت یک نوبت (تأیید/انجام‌شده/عدم‌حضور/لغو)."""
    appointment = get_object_or_404(Appointment, pk=pk)
    new_status = request.POST.get("status", "")
    if new_status not in Appointment.Status.values:
        return HttpResponse("وضعیت نامعتبر", status=400)

    appointment.status = new_status
    appointment.save(update_fields=["status", "updated_at"])

    # بازگرداندن همان ردیف به‌روزشده (HTMX)
    return render(request, "dashboard/partials/appointment_row.html", {
        "appt": appointment,
        "status_choices": Appointment.Status.choices,
        "swapped": True,
    })


@_staff_required
@require_POST
def delete_appointment(request, pk):
    """حذف یک نوبت."""
    appointment = get_object_or_404(Appointment, pk=pk)
    appointment.delete()
    # پاسخ خالی → HTMX ردیف را حذف می‌کند
    return HttpResponse("")


# --------------------------------------------------------------------------- #
# درخواست‌های مشاوره رایگان                                                    #
# --------------------------------------------------------------------------- #
@_staff_required
def consultations(request):
    """لیست درخواست‌های مشاوره با فیلتر وضعیت و جستجو."""
    qs = ConsultationRequest.objects.all()

    status = request.GET.get("status", "")
    search = request.GET.get("q", "").strip()
    applied = []

    if status and status in ConsultationRequest.Status.values:
        qs = qs.filter(status=status)
        applied.append(dict(ConsultationRequest.Status.choices)[status])

    if search:
        qs = qs.filter(
            Q(first_name__icontains=search)
            | Q(last_name__icontains=search)
            | Q(mobile__icontains=search)
        )
        applied.append(f"جستجو: {search}")

    counts = {
        "all": ConsultationRequest.objects.count(),
        "new": ConsultationRequest.objects.filter(
            status=ConsultationRequest.Status.NEW).count(),
        "in_progress": ConsultationRequest.objects.filter(
            status=ConsultationRequest.Status.IN_PROGRESS).count(),
        "done": ConsultationRequest.objects.filter(
            status=ConsultationRequest.Status.DONE).count(),
    }

    qs = qs.order_by("-created_at")[:200]

    context = {
        "consultations": qs,
        "status_choices": ConsultationRequest.Status.choices,
        "counts": counts,
        "filters": {"status": status, "q": search},
        "applied": applied,
        "today_fa": _pretty_date(jdatetime.date.today()),
    }
    if request.headers.get("HX-Request"):
        return render(request, "dashboard/partials/consultations_table.html", context)
    return render(request, "dashboard/consultations.html", context)


@_staff_required
@require_POST
def change_consultation_status(request, pk):
    """تغییر وضعیت یک درخواست مشاوره."""
    obj = get_object_or_404(ConsultationRequest, pk=pk)
    new_status = request.POST.get("status", "")
    if new_status not in ConsultationRequest.Status.values:
        return HttpResponse("وضعیت نامعتبر", status=400)

    obj.status = new_status
    obj.save(update_fields=["status", "updated_at"])

    return render(request, "dashboard/partials/consultation_row.html", {
        "item": obj,
        "status_choices": ConsultationRequest.Status.choices,
        "swapped": True,
    })


@_staff_required
@require_POST
def delete_consultation(request, pk):
    """حذف یک درخواست مشاوره."""
    obj = get_object_or_404(ConsultationRequest, pk=pk)
    obj.delete()
    return HttpResponse("")
