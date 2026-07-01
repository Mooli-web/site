#!/usr/bin/env bash
# ============================================================================ #
#  اسکریپت دیپلوی «کلینیک زیبایی» — اجرای مراحل استاندارد انتشار.
#  پیش‌نیاز: محیط مجازی فعال و فایل .env با تنظیمات production آماده باشد.
#  اجرا:  bash deploy/deploy.sh
# ============================================================================ #
set -euo pipefail
cd "$(dirname "$0")/.."

echo "▸ نصب وابستگی‌ها..."
pip install -r requirements.txt

echo "▸ ساخت CSS (Tailwind، محلی و بدون CDN)..."
chmod +x vendor/tailwindcss 2>/dev/null || true
./vendor/tailwindcss -c tailwind.config.js -i static/css/input.css -o static/css/tailwind.css --minify

echo "▸ بررسی امنیتی production..."
python manage.py check --deploy

echo "▸ اعمال migrationها..."
python manage.py migrate --noinput

echo "▸ جمع‌آوری فایل‌های static (هش‌گذاری + فشرده‌سازی)..."
python manage.py collectstatic --noinput

echo "▸ راه‌اندازی مجدد سرویس‌ها (در صورت وجود systemd)..."
if command -v systemctl >/dev/null 2>&1; then
    sudo systemctl restart beauty-clinic.service || echo "  (سرویس systemd یافت نشد — از قلم افتاد)"
fi

echo "✓ دیپلوی با موفقیت انجام شد."
