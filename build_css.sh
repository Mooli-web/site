#!/usr/bin/env bash
# ساخت فایل Tailwind CSS محلی (بدون نیاز به Node یا اینترنت).
# استفاده:  ./build_css.sh           (یک‌بار build)
#           ./build_css.sh --watch   (حالت watch هنگام توسعه)
set -e
cd "$(dirname "$0")"
chmod +x vendor/tailwindcss 2>/dev/null || true
ARGS="-c tailwind.config.js -i static/css/input.css -o static/css/tailwind.css"
if [ "$1" == "--watch" ]; then
  ./vendor/tailwindcss $ARGS --watch
else
  ./vendor/tailwindcss $ARGS --minify
  echo "✓ static/css/tailwind.css ساخته شد."
fi
