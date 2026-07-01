"""
مدل‌های اپ نوبت‌دهی (کلینیک ساده — بدون مفهوم متخصص/دکتر):
- ServiceCategory : دسته‌بندی خدمات
- Service         : خدمت (مدت‌زمان و قیمت)
- WorkingHour     : ساعات کاری کلینیک به تفکیک روز هفته (سراسری)
- Holiday         : روزهای تعطیل کلینیک (تاریخ شمسی، سراسری)
- Appointment     : نوبت رزرو شده

تاریخ‌ها با jDateField (تقویم شمسی/جلالی) ذخیره و نمایش داده می‌شوند.
"""
from django.core.validators import MinValueValidator
from django.db import models
from django.utils.translation import gettext_lazy as _
from django_jalali.db import models as jmodels

from apps.core.models import Customer, TimeStampedModel


class Weekday(models.IntegerChoices):
    """روزهای هفته به ترتیب تقویم ایرانی (شنبه=۰، منطبق با jdatetime.weekday())."""
    SATURDAY = 0, _("شنبه")
    SUNDAY = 1, _("یک‌شنبه")
    MONDAY = 2, _("دوشنبه")
    TUESDAY = 3, _("سه‌شنبه")
    WEDNESDAY = 4, _("چهارشنبه")
    THURSDAY = 5, _("پنج‌شنبه")
    FRIDAY = 6, _("جمعه")


class ServiceCategory(TimeStampedModel):
    """دسته‌بندی خدمات (مثلاً پوست، مو، ناخن، لیزر)."""

    title = models.CharField(_("عنوان"), max_length=80, unique=True)
    slug = models.SlugField(_("اسلاگ"), max_length=90, unique=True, allow_unicode=True)
    description = models.TextField(_("توضیحات"), blank=True)
    icon = models.CharField(_("آیکون"), max_length=60, blank=True)
    order = models.PositiveIntegerField(_("ترتیب"), default=0)
    is_active = models.BooleanField(_("فعال"), default=True)

    class Meta:
        verbose_name = _("دسته‌بندی خدمات")
        verbose_name_plural = _("دسته‌بندی خدمات")
        ordering = ("order", "title")

    def __str__(self):
        return self.title


class Service(TimeStampedModel):
    """یک خدمت قابل ارائه با مدت‌زمان و قیمت."""

    category = models.ForeignKey(
        ServiceCategory,
        on_delete=models.PROTECT,
        related_name="services",
        verbose_name=_("دسته‌بندی"),
    )
    title = models.CharField(_("عنوان خدمت"), max_length=120)
    slug = models.SlugField(_("اسلاگ"), max_length=140, unique=True, allow_unicode=True)
    description = models.TextField(_("توضیحات"), blank=True)
    duration_minutes = models.PositiveIntegerField(
        _("مدت‌زمان (دقیقه)"), default=30, validators=[MinValueValidator(5)]
    )
    price = models.DecimalField(_("قیمت (تومان)"), max_digits=12, decimal_places=0, default=0)
    is_active = models.BooleanField(_("فعال"), default=True)
    order = models.PositiveIntegerField(_("ترتیب"), default=0)

    class Meta:
        verbose_name = _("خدمت")
        verbose_name_plural = _("خدمات")
        ordering = ("order", "title")

    def __str__(self):
        return self.title


class WorkingHour(TimeStampedModel):
    """ساعات کاری کلینیک در یک روز مشخص از هفته (سراسری — مستقل از پرسنل)."""

    weekday = models.IntegerField(_("روز هفته"), choices=Weekday.choices)
    start_time = models.TimeField(_("ساعت شروع"))
    end_time = models.TimeField(_("ساعت پایان"))
    is_active = models.BooleanField(_("فعال"), default=True)

    class Meta:
        verbose_name = _("ساعت کاری")
        verbose_name_plural = _("ساعات کاری")
        ordering = ("weekday", "start_time")
        constraints = [
            models.UniqueConstraint(
                fields=["weekday", "start_time", "end_time"],
                name="unique_working_hour_slot",
            ),
            models.CheckConstraint(
                check=models.Q(end_time__gt=models.F("start_time")),
                name="working_hour_end_after_start",
            ),
        ]

    def __str__(self):
        return f"{self.get_weekday_display()} {self.start_time}-{self.end_time}"


class Holiday(TimeStampedModel):
    """روز تعطیل کلینیک (تاریخ شمسی، سراسری)."""

    date = jmodels.jDateField(_("تاریخ (شمسی)"), unique=True)
    reason = models.CharField(_("علت"), max_length=140, blank=True)

    objects = jmodels.jManager()

    class Meta:
        verbose_name = _("تعطیلی")
        verbose_name_plural = _("تعطیلات")
        ordering = ("-date",)

    def __str__(self):
        return f"{self.date} ({self.reason})" if self.reason else str(self.date)


class Appointment(TimeStampedModel):
    """یک نوبت رزرو شده توسط مشتری (بدون فیلد متخصص/دکتر)."""

    class Status(models.TextChoices):
        WAITING_PAYMENT = "waiting_payment", _("در انتظار پرداخت")
        PAID = "paid", _("پرداخت شده")
        PAYMENT_FAILED = "payment_failed", _("پرداخت ناموفق")
        PENDING = "pending", _("در انتظار تأیید")
        CONFIRMED = "confirmed", _("تأیید شده")
        DONE = "done", _("انجام شده")
        CANCELLED = "cancelled", _("لغو شده")
        NO_SHOW = "no_show", _("عدم حضور")

    customer = models.ForeignKey(
        Customer, on_delete=models.PROTECT,
        related_name="appointments", verbose_name=_("مشتری"),
    )
    service = models.ForeignKey(
        Service, on_delete=models.PROTECT,
        related_name="appointments", verbose_name=_("خدمت"),
    )

    date = jmodels.jDateField(_("تاریخ (شمسی)"))
    start_time = models.TimeField(_("ساعت شروع"))
    end_time = models.TimeField(_("ساعت پایان"))

    status = models.CharField(
        _("وضعیت"), max_length=20,
        choices=Status.choices, default=Status.WAITING_PAYMENT,
    )
    price = models.DecimalField(_("قیمت (تومان)"), max_digits=12, decimal_places=0, default=0)
    customer_note = models.TextField(_("یادداشت مشتری"), blank=True)
    staff_note = models.TextField(_("یادداشت کلینیک"), blank=True)
    tracking_code = models.CharField(_("کد پیگیری"), max_length=12, unique=True, db_index=True)

    # ---------- فیلدهای پرداخت (زرین‌پال) ----------
    authority = models.CharField(
        _("کد Authority درگاه"), max_length=64, blank=True, db_index=True,
        help_text=_("شناسه تراکنش دریافتی از زرین‌پال هنگام شروع پرداخت."),
    )
    ref_id = models.CharField(
        _("کد رهگیری پرداخت (RefID)"), max_length=64, blank=True,
        help_text=_("شماره مرجع تراکنش موفق."),
    )
    paid_at = models.DateTimeField(_("زمان پرداخت"), null=True, blank=True)

    @property
    def is_paid(self):
        return self.status == self.Status.PAID

    objects = jmodels.jManager()

    class Meta:
        verbose_name = _("نوبت")
        verbose_name_plural = _("نوبت‌ها")
        ordering = ("-date", "-start_time")
        indexes = [
            models.Index(fields=["date"]),
            models.Index(fields=["status"]),
        ]
        constraints = [
            # هر اسلات زمانی کلینیک فقط یک نوبت فعال دارد.
            # نوبت‌های لغوشده یا با پرداخت ناموفق، اسلات را اشغال نمی‌کنند.
            models.UniqueConstraint(
                fields=["date", "start_time"],
                condition=~models.Q(status__in=["cancelled", "payment_failed"]),
                name="unique_active_slot",
            ),
            models.CheckConstraint(
                check=models.Q(end_time__gt=models.F("start_time")),
                name="appointment_end_after_start",
            ),
        ]

    def __str__(self):
        return f"{self.tracking_code} | {self.customer} | {self.date} {self.start_time}"

    def save(self, *args, **kwargs):
        if not self.tracking_code:
            self.tracking_code = self._generate_tracking_code()
        super().save(*args, **kwargs)

    @staticmethod
    def _generate_tracking_code():
        import secrets
        import string
        alphabet = string.ascii_uppercase + string.digits
        return "BC" + "".join(secrets.choice(alphabet) for _ in range(8))
