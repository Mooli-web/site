"""
پنل ادمین محتوای وب‌سایت (گالری قبل/بعد، سوالات متداول، درخواست‌های مشاوره).
طراحی‌شده برای ادمین‌های غیرفنی: برچسب‌های فارسی، پیش‌نمایش تصاویر و راهنمای روان.
"""
from django.contrib import admin
from django.utils.html import format_html

from apps.website.models import BeforeAfter, ConsultationRequest, FAQ


def _thumb(image, label):
    """ساخت تگ تصویر کوچک برای نمایش در ادمین (یا متن جایگزین)."""
    if image:
        return format_html(
            '<img src="{}" style="height:56px;width:56px;object-fit:cover;'
            'border-radius:8px;border:1px solid #eee;" alt="{}" />',
            image.url,
            label,
        )
    return format_html('<span style="color:#aaa;">— بدون تصویر —</span>')


@admin.register(BeforeAfter)
class BeforeAfterAdmin(admin.ModelAdmin):
    list_display = (
        "title",
        "before_thumb",
        "after_thumb",
        "display_order",
        "is_active",
        "created_at",
    )
    list_display_links = ("title",)
    list_editable = ("display_order", "is_active")
    list_filter = ("is_active", "created_at")
    search_fields = ("title", "description")
    readonly_fields = ("before_preview", "after_preview", "created_at", "updated_at")
    ordering = ("display_order", "-created_at")

    fieldsets = (
        ("مشخصات نمونه", {
            "fields": ("title", "description"),
            "description": "عنوان و توضیح کوتاهی برای این نمونه‌ی «قبل و بعد» وارد کنید.",
        }),
        ("تصویرها", {
            "fields": ("before_image", "before_preview", "after_image", "after_preview"),
            "description": "هر دو عکس «قبل» و «بعد» را آپلود کنید. برای نتیجه‌ی بهتر، "
                           "بهتر است هر دو عکس اندازه (ابعاد) یکسانی داشته باشند.",
        }),
        ("تنظیمات نمایش", {
            "fields": ("display_order", "is_active"),
        }),
        ("اطلاعات سیستمی", {
            "fields": ("created_at", "updated_at"),
            "classes": ("collapse",),
        }),
    )

    @admin.display(description="قبل")
    def before_thumb(self, obj):
        return _thumb(obj.before_image, "قبل")

    @admin.display(description="بعد")
    def after_thumb(self, obj):
        return _thumb(obj.after_image, "بعد")

    @admin.display(description="پیش‌نمایش تصویر قبل")
    def before_preview(self, obj):
        return _thumb(obj.before_image, "قبل")

    @admin.display(description="پیش‌نمایش تصویر بعد")
    def after_preview(self, obj):
        return _thumb(obj.after_image, "بعد")


@admin.register(FAQ)
class FAQAdmin(admin.ModelAdmin):
    list_display = ("question", "display_order", "is_active", "created_at")
    list_display_links = ("question",)
    list_editable = ("display_order", "is_active")
    list_filter = ("is_active", "created_at")
    search_fields = ("question", "answer")
    ordering = ("display_order", "-created_at")
    readonly_fields = ("created_at", "updated_at")


@admin.register(ConsultationRequest)
class ConsultationRequestAdmin(admin.ModelAdmin):
    list_display = (
        "full_name",
        "mobile",
        "status",
        "short_message",
        "created_at",
    )
    list_filter = ("status", "created_at")
    search_fields = ("first_name", "last_name", "mobile", "message")
    list_editable = ("status",)
    readonly_fields = (
        "first_name", "last_name", "mobile", "message", "created_at", "updated_at",
    )
    ordering = ("-created_at",)

    fieldsets = (
        ("اطلاعات درخواست‌کننده", {
            "fields": ("first_name", "last_name", "mobile", "message"),
        }),
        ("وضعیت پیگیری", {
            "fields": ("status",),
            "description": "وضعیت این درخواست را پس از تماس با کاربر به‌روزرسانی کنید.",
        }),
        ("اطلاعات سیستمی", {
            "fields": ("created_at", "updated_at"),
            "classes": ("collapse",),
        }),
    )

    def has_add_permission(self, request):
        # درخواست‌ها فقط از طریق فرم سایت ثبت می‌شوند.
        return False

    @admin.display(description="نام و نام خانوادگی")
    def full_name(self, obj):
        return obj.full_name

    @admin.display(description="پیام")
    def short_message(self, obj):
        if not obj.message:
            return "—"
        return obj.message[:40] + ("…" if len(obj.message) > 40 else "")
