"""
مدل‌های محتوای وب‌سایت عمومی کلینیک:
- BeforeAfter        : گالری تصاویر قبل و بعد
- FAQ                : پرسش‌های متداول (آکاردئون)
- ConsultationRequest: درخواست مشاوره رایگان (فرم HTMX)

تصاویر در MEDIA_ROOT ذخیره می‌شوند. همه‌ی verbose_name و help_textها
فارسی و روان هستند تا ادمین‌های غیرفنی به‌راحتی کار کنند.
"""
from django.core.validators import RegexValidator
from django.db import models
from django.utils.translation import gettext_lazy as _

from apps.core.models import IRAN_MOBILE_VALIDATOR, TimeStampedModel


class BeforeAfter(TimeStampedModel):
    """یک نمونه‌کار «قبل و بعد» شامل دو تصویر و توضیحات."""

    title = models.CharField(
        _("عنوان"),
        max_length=120,
        help_text=_("یک عنوان کوتاه برای این نمونه؛ مثلاً «جوان‌سازی پوست» یا «کاشت ابرو»."),
    )
    description = models.CharField(
        _("توضیح کوتاه"),
        max_length=250,
        blank=True,
        help_text=_("یک یا دو جمله درباره‌ی این نمونه (اختیاری)."),
    )
    before_image = models.ImageField(
        _("تصویر قبل"),
        upload_to="before_after/",
        help_text=_("عکس «قبل از درمان» را اینجا آپلود کنید (فرمت JPG یا PNG)."),
    )
    after_image = models.ImageField(
        _("تصویر بعد"),
        upload_to="before_after/",
        help_text=_("عکس «بعد از درمان» را اینجا آپلود کنید (فرمت JPG یا PNG)."),
    )
    display_order = models.PositiveIntegerField(
        _("اولویت نمایش"),
        default=0,
        help_text=_("عدد کوچک‌تر بالاتر نمایش داده می‌شود (مثلاً ۱ اول، ۲ بعد از آن)."),
    )
    is_active = models.BooleanField(
        _("نمایش در سایت"),
        default=True,
        help_text=_("اگر تیک برداشته شود، این نمونه در سایت نمایش داده نمی‌شود."),
    )

    class Meta:
        verbose_name = _("نمونه قبل و بعد")
        verbose_name_plural = _("گالری قبل و بعد")
        ordering = ("display_order", "-created_at")

    def __str__(self):
        return self.title


class FAQ(TimeStampedModel):
    """یک پرسش متداول و پاسخ آن."""

    question = models.CharField(
        _("پرسش"),
        max_length=255,
        help_text=_("متن سوالی که کاربران معمولاً می‌پرسند."),
    )
    answer = models.TextField(
        _("پاسخ"),
        help_text=_("پاسخ کامل و روشن به این پرسش."),
    )
    display_order = models.PositiveIntegerField(
        _("اولویت نمایش"),
        default=0,
        help_text=_("عدد کوچک‌تر بالاتر نمایش داده می‌شود."),
    )
    is_active = models.BooleanField(
        _("نمایش در سایت"),
        default=True,
        help_text=_("اگر تیک برداشته شود، این پرسش در صفحه سوالات متداول نمایش داده نمی‌شود."),
    )

    class Meta:
        verbose_name = _("پرسش متداول")
        verbose_name_plural = _("پرسش‌های متداول")
        ordering = ("display_order", "-created_at")

    def __str__(self):
        return self.question


class ConsultationRequest(TimeStampedModel):
    """درخواست مشاوره‌ی رایگان ثبت‌شده توسط کاربر (بدون ثبت‌نام)."""

    class Status(models.TextChoices):
        NEW = "new", _("تماس گرفته نشده")
        IN_PROGRESS = "in_progress", _("در حال پیگیری")
        DONE = "done", _("انجام شده")

    first_name = models.CharField(_("نام"), max_length=60)
    last_name = models.CharField(_("نام خانوادگی"), max_length=60)
    mobile = models.CharField(
        _("شماره موبایل"),
        max_length=11,
        validators=[IRAN_MOBILE_VALIDATOR],
        help_text=_("نمونه: ۰۹۱۲۱۲۳۴۵۶۷"),
    )
    message = models.TextField(
        _("پیام"),
        blank=True,
        help_text=_("توضیح کوتاه درباره‌ی نیاز یا سوال کاربر (اختیاری)."),
    )
    status = models.CharField(
        _("وضعیت درخواست"),
        max_length=20,
        choices=Status.choices,
        default=Status.NEW,
    )

    class Meta:
        verbose_name = _("درخواست مشاوره")
        verbose_name_plural = _("درخواست‌های مشاوره")
        ordering = ("-created_at",)
        indexes = [
            models.Index(fields=["status"]),
            models.Index(fields=["-created_at"]),
        ]

    def __str__(self):
        return f"{self.full_name} ({self.mobile})"

    @property
    def full_name(self):
        return f"{self.first_name} {self.last_name}".strip()
