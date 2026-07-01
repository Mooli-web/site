from django.contrib import admin
from django_jalali.admin.filters import JDateFieldListFilter

from apps.appointments.models import (
    Appointment,
    Holiday,
    Service,
    ServiceCategory,
    WorkingHour,
)


@admin.register(ServiceCategory)
class ServiceCategoryAdmin(admin.ModelAdmin):
    list_display = ("title", "order", "is_active")
    list_editable = ("order", "is_active")
    prepopulated_fields = {"slug": ("title",)}
    search_fields = ("title",)


@admin.register(Service)
class ServiceAdmin(admin.ModelAdmin):
    list_display = ("title", "category", "duration_minutes", "price", "is_active", "order")
    list_filter = ("category", "is_active")
    list_editable = ("is_active", "order")
    prepopulated_fields = {"slug": ("title",)}
    search_fields = ("title", "description")


@admin.register(WorkingHour)
class WorkingHourAdmin(admin.ModelAdmin):
    list_display = ("get_weekday_display", "start_time", "end_time", "is_active")
    list_filter = ("weekday", "is_active")


@admin.register(Holiday)
class HolidayAdmin(admin.ModelAdmin):
    list_display = ("date", "reason")
    list_filter = (("date", JDateFieldListFilter),)
    search_fields = ("reason",)


@admin.register(Appointment)
class AppointmentAdmin(admin.ModelAdmin):
    list_display = (
        "tracking_code", "customer", "service",
        "date", "start_time", "status", "price", "ref_id",
    )
    list_filter = (("date", JDateFieldListFilter), "status", "service")
    search_fields = (
        "tracking_code", "ref_id", "authority",
        "customer__first_name", "customer__last_name", "customer__mobile",
    )
    readonly_fields = (
        "tracking_code", "authority", "ref_id", "paid_at",
        "created_at", "updated_at",
    )
    autocomplete_fields = ("customer", "service")
