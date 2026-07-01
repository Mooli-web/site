"""
مسیرهای پرداخت زرین‌پال.
"""
from django.urls import path

from apps.payments import views

app_name = "payments"

urlpatterns = [
    path("<str:tracking_code>/", views.payment_page, name="pay"),
    path("<str:tracking_code>/start/", views.payment_start, name="start"),
    path("<str:tracking_code>/callback/", views.payment_callback, name="callback"),
    path("<str:tracking_code>/result/", views.payment_result, name="result"),
]
