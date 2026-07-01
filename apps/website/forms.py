"""
فرم ثبت نوبت توسط مشتری (بدون ثبت‌نام).
"""
import re

from django import forms


class BookingForm(forms.Form):
    first_name = forms.CharField(
        label="نام",
        max_length=60,
        error_messages={"required": "وارد کردن نام الزامی است."},
    )
    last_name = forms.CharField(
        label="نام خانوادگی",
        max_length=60,
        error_messages={"required": "وارد کردن نام خانوادگی الزامی است."},
    )
    mobile = forms.CharField(
        label="شماره موبایل",
        max_length=11,
        error_messages={"required": "وارد کردن شماره موبایل الزامی است."},
    )
    customer_note = forms.CharField(label="یادداشت", required=False, max_length=500)

    # فیلدهای مخفی که توسط فلوی رزرو پر می‌شوند
    service = forms.IntegerField(widget=forms.HiddenInput)
    date = forms.CharField(widget=forms.HiddenInput)   # 1403-04-08
    start = forms.CharField(widget=forms.HiddenInput)  # HH:MM

    def clean_mobile(self):
        mobile = self.cleaned_data["mobile"].strip()
        # تبدیل ارقام فارسی/عربی به انگلیسی
        trans = str.maketrans("۰۱۲۳۴۵۶۷۸۹٠١٢٣٤٥٦٧٨٩", "01234567890123456789")
        mobile = mobile.translate(trans)
        mobile = re.sub(r"\D", "", mobile)
        if not re.fullmatch(r"09\d{9}", mobile):
            raise forms.ValidationError("شماره موبایل باید با ۰۹ شروع شده و ۱۱ رقم باشد.")
        return mobile

    def clean_first_name(self):
        return self.cleaned_data["first_name"].strip()

    def clean_last_name(self):
        return self.cleaned_data["last_name"].strip()


class ContactForm(forms.Form):
    """فرم تماس با ما."""

    name = forms.CharField(
        label="نام و نام خانوادگی",
        max_length=120,
        error_messages={"required": "وارد کردن نام الزامی است."},
    )
    mobile = forms.CharField(
        label="شماره موبایل",
        max_length=11,
        error_messages={"required": "وارد کردن شماره موبایل الزامی است."},
    )
    subject = forms.CharField(label="موضوع", required=False, max_length=150)
    message = forms.CharField(
        label="پیام",
        max_length=2000,
        error_messages={"required": "لطفاً متن پیام را وارد کنید."},
    )

    def clean_mobile(self):
        mobile = self.cleaned_data["mobile"].strip()
        trans = str.maketrans("۰۱۲۳۴۵۶۷۸۹٠١٢٣٤٥٦٧٨٩", "01234567890123456789")
        mobile = mobile.translate(trans)
        mobile = re.sub(r"\D", "", mobile)
        if not re.fullmatch(r"09\d{9}", mobile):
            raise forms.ValidationError("شماره موبایل باید با ۰۹ شروع شده و ۱۱ رقم باشد.")
        return mobile

    def clean_name(self):
        return self.cleaned_data["name"].strip()


class ConsultationForm(forms.Form):
    """فرم درخواست مشاوره رایگان."""

    first_name = forms.CharField(
        label="نام",
        max_length=60,
        error_messages={"required": "وارد کردن نام الزامی است."},
    )
    last_name = forms.CharField(
        label="نام خانوادگی",
        max_length=60,
        error_messages={"required": "وارد کردن نام خانوادگی الزامی است."},
    )
    mobile = forms.CharField(
        label="شماره موبایل",
        max_length=11,
        error_messages={"required": "وارد کردن شماره موبایل الزامی است."},
    )
    message = forms.CharField(label="پیام", required=False, max_length=1000)

    def clean_mobile(self):
        mobile = self.cleaned_data["mobile"].strip()
        trans = str.maketrans("۰۱۲۳۴۵۶۷۸۹٠١٢٣٤٥٦٧٨٩", "01234567890123456789")
        mobile = mobile.translate(trans)
        mobile = re.sub(r"\D", "", mobile)
        if not re.fullmatch(r"09\d{9}", mobile):
            raise forms.ValidationError("شماره موبایل باید با ۰۹ شروع شده و ۱۱ رقم باشد.")
        return mobile

    def clean_first_name(self):
        return self.cleaned_data["first_name"].strip()

    def clean_last_name(self):
        return self.cleaned_data["last_name"].strip()
