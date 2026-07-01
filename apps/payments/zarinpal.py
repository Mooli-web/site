"""
کلاینت سبک درگاه پرداخت زرین‌پال (با requests).

از نسخه‌ی REST v4 زرین‌پال استفاده می‌کند و هر دو حالت Sandbox و Production
را پشتیبانی می‌کند. در حالت Sandbox آدرس‌ها روی sandbox.zarinpal.com هستند.

مستندات: https://docs.zarinpal.com/paymentGateway/
"""
from __future__ import annotations

from dataclasses import dataclass

import requests
from django.conf import settings


class ZarinPalError(Exception):
    """خطای ارتباط یا منطقی هنگام کار با درگاه زرین‌پال."""


@dataclass
class PaymentRequestResult:
    authority: str
    pay_url: str


@dataclass
class VerifyResult:
    ok: bool
    ref_id: str | None
    code: int
    message: str
    already_verified: bool = False


class ZarinPal:
    """پوشش‌دهنده‌ی فراخوانی‌های REST زرین‌پال."""

    REQUEST_TIMEOUT = 15  # ثانیه

    def __init__(self, merchant_id: str | None = None, sandbox: bool | None = None):
        self.merchant_id = merchant_id or settings.ZARINPAL_MERCHANT_ID
        self.sandbox = settings.ZARINPAL_SANDBOX if sandbox is None else sandbox

        base = "https://sandbox.zarinpal.com" if self.sandbox else "https://payment.zarinpal.com"
        self.base = base
        self.request_url = f"{base}/pg/v4/payment/request.json"
        self.verify_url = f"{base}/pg/v4/payment/verify.json"
        self.startpay_url = f"{base}/pg/StartPay/"  # + authority

    # ------------------------------------------------------------------ #
    def payment_request(self, *, amount, description, callback_url,
                        mobile="", email="") -> PaymentRequestResult:
        """
        درخواست شروع پرداخت. amount به «تومان» است.
        خروجی: authority و آدرس هدایت کاربر به درگاه.
        """
        payload = {
            "merchant_id": self.merchant_id,
            "amount": int(amount),
            "currency": getattr(settings, "ZARINPAL_CURRENCY", "IRT"),
            "description": description,
            "callback_url": callback_url,
            "metadata": {k: v for k, v in (("mobile", mobile), ("email", email)) if v},
        }
        try:
            resp = requests.post(self.request_url, json=payload, timeout=self.REQUEST_TIMEOUT)
            data = resp.json()
        except requests.RequestException as exc:
            raise ZarinPalError(f"ارتباط با درگاه برقرار نشد: {exc}") from exc
        except ValueError as exc:
            raise ZarinPalError("پاسخ نامعتبر از درگاه دریافت شد.") from exc

        errors = data.get("errors")
        if errors:
            # errors می‌تواند dict یا list باشد
            msg = errors.get("message") if isinstance(errors, dict) else str(errors)
            raise ZarinPalError(msg or "خطای نامشخص از درگاه پرداخت.")

        result = data.get("data") or {}
        authority = result.get("authority")
        if not authority or result.get("code") != 100:
            raise ZarinPalError("دریافت کد پرداخت از درگاه ناموفق بود.")

        return PaymentRequestResult(
            authority=authority,
            pay_url=self.startpay_url + authority,
        )

    # ------------------------------------------------------------------ #
    def verify(self, *, amount, authority) -> VerifyResult:
        """
        تأیید پرداخت پس از بازگشت کاربر از درگاه.
        code == 100 → پرداخت موفق و تأیید شد.
        code == 101 → تراکنش قبلاً تأیید شده است (idempotent).
        """
        payload = {
            "merchant_id": self.merchant_id,
            "amount": int(amount),
            "authority": authority,
        }
        try:
            resp = requests.post(self.verify_url, json=payload, timeout=self.REQUEST_TIMEOUT)
            data = resp.json()
        except requests.RequestException as exc:
            raise ZarinPalError(f"ارتباط با درگاه برقرار نشد: {exc}") from exc
        except ValueError as exc:
            raise ZarinPalError("پاسخ نامعتبر از درگاه دریافت شد.") from exc

        errors = data.get("errors")
        result = data.get("data") or {}
        code = result.get("code", -1)

        if code in (100, 101):
            return VerifyResult(
                ok=True,
                ref_id=str(result.get("ref_id", "")),
                code=code,
                message="پرداخت با موفقیت تأیید شد.",
                already_verified=(code == 101),
            )

        # در صورت خطا
        if errors:
            msg = errors.get("message") if isinstance(errors, dict) else str(errors)
        else:
            msg = "پرداخت تأیید نشد."
        return VerifyResult(ok=False, ref_id=None, code=code, message=msg or "پرداخت تأیید نشد.")
