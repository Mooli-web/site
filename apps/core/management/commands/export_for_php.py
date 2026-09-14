"""
خروجی گرفتن از همه‌ی داده‌های سایت جنگو به یک فایل JSON برای وارد کردن در نسخه‌ی PHP.

    python manage.py export_for_php --out export.json

فایل خروجی را در پنل PHP (/admin/import/) آپلود کنید. تصاویر گالری را جداگانه از
پوشه‌ی media/before_after/ به php-app/public/media/before_after/ کپی کنید.
"""
import json

from django.contrib.auth import get_user_model
from django.core.management.base import BaseCommand

from apps.appointments.models import Appointment, Holiday, Service, ServiceCategory, WorkingHour
from apps.core.models import ClinicSettings, ContactMessage, Customer
from apps.website.models import FAQ, BeforeAfter, ConsultationRequest


def ts(dt):
    return dt.strftime("%Y-%m-%d %H:%M:%S") if dt else None


def jdate(d):
    return f"{d.year:04d}-{d.month:02d}-{d.day:02d}" if d else None


def hm(t):
    return t.strftime("%H:%M") if t else None


class Command(BaseCommand):
    help = "خروجی JSON از داده‌ها برای نسخه‌ی PHP"

    def add_arguments(self, parser):
        parser.add_argument("--out", default="export.json")

    def handle(self, *args, **opts):
        s = ClinicSettings.load()
        data = {
            "version": 1,
            "clinic_settings": {
                "name": s.name, "phone": s.phone, "address": s.address, "about": s.about,
                "instagram": s.instagram, "slot_step_minutes": s.slot_step_minutes,
                "max_advance_days": s.max_advance_days, "min_advance_hours": s.min_advance_hours,
            },
            "users": [
                # رمزها قابل انتقال نیستند (فرمت هش جنگو)؛ فقط نام کاربری منتقل می‌شود
                {"id": u.id, "username": u.username, "is_active": u.is_active}
                for u in get_user_model().objects.filter(is_staff=True)
            ],
            "customers": [
                {"id": c.id, "first_name": c.first_name, "last_name": c.last_name, "mobile": c.mobile,
                 "note": c.note, "is_blocked": c.is_blocked, "created_at": ts(c.created_at)}
                for c in Customer.objects.all()
            ],
            "service_categories": [
                {"id": c.id, "title": c.title, "slug": c.slug, "description": c.description, "icon": c.icon,
                 "order": c.order, "is_active": c.is_active}
                for c in ServiceCategory.objects.all()
            ],
            "services": [
                {"id": x.id, "category_id": x.category_id, "title": x.title, "slug": x.slug,
                 "description": x.description, "duration_minutes": x.duration_minutes,
                 "price": int(x.price), "is_active": x.is_active, "order": x.order}
                for x in Service.objects.all()
            ],
            "working_hours": [
                {"weekday": w.weekday, "start_time": hm(w.start_time), "end_time": hm(w.end_time), "is_active": w.is_active}
                for w in WorkingHour.objects.all()
            ],
            "holidays": [{"date": jdate(h.date), "reason": h.reason} for h in Holiday.objects.all()],
            "appointments": [
                {"id": a.id, "customer_id": a.customer_id, "service_id": a.service_id, "date": jdate(a.date),
                 "start_time": hm(a.start_time), "end_time": hm(a.end_time), "status": a.status,
                 "price": int(a.price), "customer_note": a.customer_note, "staff_note": a.staff_note,
                 "tracking_code": a.tracking_code, "authority": a.authority, "ref_id": a.ref_id,
                 "paid_at": ts(a.paid_at), "created_at": ts(a.created_at)}
                for a in Appointment.objects.all()
            ],
            "contact_messages": [
                {"name": m.name, "mobile": m.mobile, "subject": m.subject, "message": m.message,
                 "is_read": m.is_read, "created_at": ts(m.created_at)}
                for m in ContactMessage.objects.all()
            ],
            "before_after": [
                {"title": b.title, "description": b.description,
                 "before_image": "/media/" + b.before_image.name if b.before_image else "",
                 "after_image": "/media/" + b.after_image.name if b.after_image else "",
                 "display_order": b.display_order, "is_active": b.is_active}
                for b in BeforeAfter.objects.all()
            ],
            "faqs": [
                {"question": f.question, "answer": f.answer, "display_order": f.display_order, "is_active": f.is_active}
                for f in FAQ.objects.all()
            ],
            "consultation_requests": [
                {"first_name": c.first_name, "last_name": c.last_name, "mobile": c.mobile, "message": c.message,
                 "status": c.status, "created_at": ts(c.created_at)}
                for c in ConsultationRequest.objects.all()
            ],
        }
        with open(opts["out"], "w", encoding="utf-8") as fh:
            json.dump(data, fh, ensure_ascii=False, indent=1)
        counts = {k: len(v) for k, v in data.items() if isinstance(v, list)}
        self.stdout.write(self.style.SUCCESS(f"✓ {opts['out']} نوشته شد: {counts}"))
