"""
نقطه ورود Django Ninja API.
روترهای هر اپ در مراحل بعدی به این نمونه متصل می‌شوند.
"""
from ninja import NinjaAPI

api = NinjaAPI(
    title="Beauty Clinic API",
    version="1.0.0",
    description="API سیستم نوبت‌دهی کلینیک زیبایی",
)


@api.get("/health", tags=["system"])
def health(request):
    """بررسی سلامت سرویس."""
    return {"status": "ok"}


# روترها در مراحل بعد اضافه می‌شوند:
# from apps.appointments.api import router as appointments_router
# api.add_router("/appointments", appointments_router, tags=["appointments"])
