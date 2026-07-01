"""
مسیرهای پنل منشی/مدیریت.
"""
from django.urls import path

from apps.dashboard import views

app_name = "dashboard"

urlpatterns = [
    path("login/", views.login_view, name="login"),
    path("logout/", views.logout_view, name="logout"),
    path("", views.home, name="home"),
    path("appointments/<int:pk>/status/", views.change_status, name="change_status"),
    path("appointments/<int:pk>/delete/", views.delete_appointment, name="delete"),
    # درخواست‌های مشاوره
    path("consultations/", views.consultations, name="consultations"),
    path("consultations/<int:pk>/status/", views.change_consultation_status, name="consultation_status"),
    path("consultations/<int:pk>/delete/", views.delete_consultation, name="consultation_delete"),
]
