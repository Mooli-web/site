"""
ویوهای پرداخت زرین‌پال (Sandbox/Production).

فلو:
  1) payment_page    : نمایش صفحه‌ی پرداخت برای یک نوبت (با کد پیگیری)
  2) payment_start   : درخواست پرداخت از زرین‌پال و هدایت کاربر به درگاه
  3) payment_callback: بازگشت از درگاه، تأیید تراکنش و به‌روزرسانی وضعیت نوبت
"""
from django.contrib import messages
from django.shortcuts import get_object_or_404, redirect, render
from django.urls import reverse
from django.utils import timezone
from django.views.decorators.http import require_POST

from apps.appointments.calendar_utils import to_fa_digits
from apps.appointments.models import Appointment
from apps.payments.zarinpal import ZarinPal, ZarinPalError


def _date_fa(appt):
    d = appt.date
    return f"{to_fa_digits(d.day)} {d.j_months_fa[d.month - 1]} {to_fa_digits(d.year)}"


def payment_page(request, tracking_code):
    """صفحه‌ی پرداخت برای یک نوبت."""
    appt = get_object_or_404(Appointment, tracking_code=tracking_code)

    # اگر قبلاً پرداخت شده، مستقیم به صفحه‌ی نتیجه برو
    if appt.is_paid:
        return redirect("payments:result", tracking_code=appt.tracking_code)

    return render(request, "payments/payment_page.html", {
        "appt": appt,
        "date_fa": _date_fa(appt),
        "start_fa": to_fa_digits(appt.start_time.strftime("%H:%M")),
    })


@require_POST
def payment_start(request, tracking_code):
    """درخواست پرداخت از زرین‌پال و هدایت به درگاه."""
    appt = get_object_or_404(Appointment, tracking_code=tracking_code)

    if appt.is_paid:
        return redirect("payments:result", tracking_code=appt.tracking_code)

    callback_url = request.build_absolute_uri(
        reverse("payments:callback", args=[appt.tracking_code])
    )
    zp = ZarinPal()
    try:
        result = zp.payment_request(
            amount=appt.price,
            description=f"رزرو نوبت {appt.service.title} — کد {appt.tracking_code}",
            callback_url=callback_url,
            mobile=appt.customer.mobile,
        )
    except ZarinPalError as exc:
        messages.error(request, f"خطا در اتصال به درگاه پرداخت: {exc}")
        return redirect("payments:pay", tracking_code=appt.tracking_code)

    # ذخیره‌ی authority و قراردادن نوبت در حالت «در انتظار پرداخت»
    appt.authority = result.authority
    appt.status = Appointment.Status.WAITING_PAYMENT
    appt.save(update_fields=["authority", "status", "updated_at"])

    return redirect(result.pay_url)


def payment_callback(request, tracking_code):
    """بازگشت از درگاه: تأیید تراکنش و به‌روزرسانی وضعیت نوبت."""
    appt = get_object_or_404(Appointment, tracking_code=tracking_code)

    # اگر قبلاً پرداخت شده، نتیجه را نشان بده (idempotent)
    if appt.is_paid:
        return redirect("payments:result", tracking_code=appt.tracking_code)

    status = request.GET.get("Status", "")
    authority = request.GET.get("Authority", "")

    # کاربر پرداخت را لغو کرده یا بازگشت ناموفق
    if status != "OK" or not authority:
        appt.status = Appointment.Status.PAYMENT_FAILED
        appt.save(update_fields=["status", "updated_at"])
        return redirect("payments:result", tracking_code=appt.tracking_code)

    zp = ZarinPal()
    try:
        verify = zp.verify(amount=appt.price, authority=authority)
    except ZarinPalError as exc:
        messages.error(request, f"خطا در تأیید پرداخت: {exc}")
        return redirect("payments:result", tracking_code=appt.tracking_code)

    if verify.ok:
        appt.status = Appointment.Status.PAID
        appt.ref_id = verify.ref_id or ""
        appt.paid_at = timezone.now()
        appt.save(update_fields=["status", "ref_id", "paid_at", "updated_at"])
    else:
        appt.status = Appointment.Status.PAYMENT_FAILED
        appt.save(update_fields=["status", "updated_at"])

    return redirect("payments:result", tracking_code=appt.tracking_code)


def payment_result(request, tracking_code):
    """نمایش نتیجه‌ی پرداخت (موفق یا ناموفق)."""
    appt = get_object_or_404(Appointment, tracking_code=tracking_code)
    return render(request, "payments/payment_result.html", {
        "appt": appt,
        "date_fa": _date_fa(appt),
        "start_fa": to_fa_digits(appt.start_time.strftime("%H:%M")),
        "success": appt.is_paid,
    })
