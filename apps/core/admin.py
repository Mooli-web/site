from django.contrib import admin

from apps.core.models import ClinicSettings, ContactMessage, Customer


@admin.register(Customer)
class CustomerAdmin(admin.ModelAdmin):
    list_display = ("full_name", "mobile", "is_blocked", "created_at")
    list_filter = ("is_blocked", "created_at")
    search_fields = ("first_name", "last_name", "mobile")
    readonly_fields = ("created_at", "updated_at")
    ordering = ("-created_at",)


@admin.register(ClinicSettings)
class ClinicSettingsAdmin(admin.ModelAdmin):
    list_display = ("name", "phone", "slot_step_minutes", "max_advance_days")

    def has_add_permission(self, request):
        # تنها یک نمونه (singleton)
        return not ClinicSettings.objects.exists()

    def has_delete_permission(self, request, obj=None):
        return False


@admin.register(ContactMessage)
class ContactMessageAdmin(admin.ModelAdmin):
    list_display = ("name", "mobile", "subject", "is_read", "created_at")
    list_filter = ("is_read", "created_at")
    search_fields = ("name", "mobile", "subject", "message")
    readonly_fields = ("name", "mobile", "subject", "message", "created_at", "updated_at")
    list_editable = ("is_read",)
    ordering = ("-created_at",)

    def has_add_permission(self, request):
        return False
