"""
مسیرهای بخش عمومی سایت (مشتریان).
"""
from django.urls import path

from apps.website import views

app_name = "website"

urlpatterns = [
    path("", views.home, name="home"),
    path("about/", views.about, name="about"),
    path("contact/", views.contact, name="contact"),
    path("contact/submit/", views.contact_submit, name="contact_submit"),
    # بخش‌های جدید محتوا
    path("before-after/", views.before_after, name="before_after"),
    path("faq/", views.faq, name="faq"),
    path("consultation/", views.consultation, name="consultation"),
    path("consultation/submit/", views.consultation_submit, name="consultation_submit"),
    path("booking/", views.booking, name="booking"),
    # مراحل HTMX فلوی رزرو
    path("booking/calendar/", views.booking_calendar, name="booking_calendar"),
    path("booking/slots/", views.booking_slots, name="booking_slots"),
    path("booking/confirm/", views.booking_confirm, name="booking_confirm"),
    path("booking/submit/", views.booking_submit, name="booking_submit"),
]
