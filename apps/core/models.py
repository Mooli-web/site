"""
مدل‌های پایه و مشترک پروژه.
شامل مدل abstract زمان‌دار، مشتری (بدون ثبت‌نام) و تنظیمات کلینیک.
"""
from django.core.validators import RegexValidator
from django.db import models
from django.utils.translation import gettext_lazy as _

# اعتبارسنجی شماره موبایل ایران: 09xxxxxxxxx
IRAN_MOBILE_VALIDATOR = RegexValidator(
    regex=r"^09\d{9}$",
    message=_("شماره موبایل باید با ۰۹ شروع شده و ۱۱ رقم باشد."),
)


class TimeStampedModel(models.Model):
    """مدل abstract با فیلدهای زمان ایجاد و به‌روزرسانی."""

    created_at = models.DateTimeField(_("تاریخ ایجاد"), auto_now_add=True)
    updated_at = models.DateTimeField(_("تاریخ به‌روزرسانی"), auto_now=True)

    class Meta:
        abstract = True


class Customer(TimeStampedModel):
    """
    مشتری/مراجعه‌کننده — بدون ثبت‌نام و بدون حساب کاربری.
    شناسایی فقط بر اساس نام، نام خانوادگی و شماره موبایل.
    """

    first_name = models.CharField(_("نام"), max_length=60)
    last_name = models.CharField(_("نام خانوادگی"), max_length=60)
    mobile = models.CharField(
        _("شماره موبایل"),
        max_length=11,
        unique=True,
        validators=[IRAN_MOBILE_VALIDATOR],
        help_text=_("نمونه: ۰۹۱۲۱۲۳۴۵۶۷"),
    )
    note = models.TextField(_("یادداشت"), blank=True)
    is_blocked = models.BooleanField(_("مسدود شده"), default=False)

    class Meta:
        verbose_name = _("مشتری")
        verbose_name_plural = _("مشتری‌ها")
        ordering = ("-created_at",)
        indexes = [
            models.Index(fields=["mobile"]),
            models.Index(fields=["last_name", "first_name"]),
        ]

    def __str__(self):
        return f"{self.full_name} ({self.mobile})"

    @property
    def full_name(self):
        return f"{self.first_name} {self.last_name}".strip()


class ClinicSettings(TimeStampedModel):
    """
    تنظیمات سراسری کلینیک (الگوی Singleton — فقط یک نمونه).
    شامل اطلاعات تماس، فاصله بین نوبت‌ها و سقف رزرو آینده.
    """

    name = models.CharField(_("نام کلینیک"), max_length=120, default="کلینیک زیبایی")
    phone = models.CharField(_("تلفن"), max_length=20, blank=True)
    address = models.TextField(_("آدرس"), blank=True)
    about = models.TextField(_("درباره ما"), blank=True)
    instagram = models.CharField(_("اینستاگرام"), max_length=120, blank=True)

    # تنظیمات نوبت‌دهی
    slot_step_minutes = models.PositiveIntegerField(
        _("گام زمانی اسلات‌ها (دقیقه)"),
        default=30,
        help_text=_("بازه زمانی بین اسلات‌های قابل رزرو."),
    )
    max_advance_days = models.PositiveIntegerField(
        _("حداکثر روز رزرو در آینده"),
        default=30,
        help_text=_("مشتری تا چند روز آینده می‌تواند نوبت بگیرد."),
    )
    min_advance_hours = models.PositiveIntegerField(
        _("حداقل فاصله رزرو (ساعت)"),
        default=2,
        help_text=_("رزرو باید حداقل چند ساعت قبل از زمان نوبت انجام شود."),
    )

    class Meta:
        verbose_name = _("تنظیمات کلینیک")
        verbose_name_plural = _("تنظیمات کلینیک")

    def __str__(self):
        return self.name

    def save(self, *args, **kwargs):
        # تضمین singleton بودن
        self.pk = 1
        super().save(*args, **kwargs)

    @classmethod
    def load(cls):
        obj, _created = cls.objects.get_or_create(pk=1)
        return obj


class ContactMessage(TimeStampedModel):
    """پیام ارسالی از فرم «تماس با ما» (بدون نیاز به ثبت‌نام)."""

    name = models.CharField(_("نام"), max_length=120)
    mobile = models.CharField(
        _("شماره موبایل"),
        max_length=11,
        validators=[IRAN_MOBILE_VALIDATOR],
    )
    subject = models.CharField(_("موضوع"), max_length=150, blank=True)
    message = models.TextField(_("پیام"))
    is_read = models.BooleanField(_("خوانده شده"), default=False)

    class Meta:
        verbose_name = _("پیام تماس")
        verbose_name_plural = _("پیام‌های تماس")
        ordering = ("-created_at",)

    def __str__(self):
        return f"{self.name} — {self.subject or 'بدون موضوع'}"
