<?php View::$page['title'] = 'تنظیمات — پنل مدیریت'; $heading = 'تنظیمات و محتوا'; require __DIR__ . '/_shell_top.php'; ?>
<?php $cards = [
  ['settings', 'تنظیمات کلینیک', 'نام، تلفن، آدرس و قواعد نوبت‌دهی', null],
  ['categories', 'دسته‌بندی خدمات', 'گروه‌بندی خدمات در صفحهٔ رزرو', $counts['categories']],
  ['services', 'خدمات', 'عنوان، مدت و قیمت هر خدمت', $counts['services']],
  ['hours', 'ساعات کاری و تعطیلات', 'بازه‌های کاری هر روز هفته', $counts['hours']],
  ['faqs', 'سوالات متداول', 'پرسش و پاسخ صفحهٔ FAQ', $counts['faqs']],
  ['gallery', 'گالری قبل/بعد', 'تصاویر نتایج درمان', $counts['gallery']],
  ['messages', 'پیام‌های تماس', 'پیام‌های خوانده‌نشده', $counts['messages']],
  ['customers', 'مشتریان', 'لیست و مسدودسازی', $counts['customers']],
  ['users', 'کاربران پنل', 'دسترسی منشی/مدیر', null],
]; ?>
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
  <?php foreach ($cards as [$k, $t, $d, $n]): ?>
  <a href="/admin/<?= $k ?>/" class="rounded-2xl bg-white p-5 shadow-soft ring-1 ring-blush-50 hover:ring-blush-200 transition flex items-start justify-between gap-3">
    <div><div class="font-bold text-gray-800"><?= e($t) ?></div><div class="text-xs text-gray-400 mt-1"><?= e($d) ?></div></div>
    <?php if ($n !== null): ?><span class="rounded-full bg-blush-50 px-2.5 py-0.5 text-xs font-bold text-blush-700"><?= fa_digits($n) ?></span><?php endif; ?>
  </a>
  <?php endforeach; ?>
  <a href="/admin/import/" class="rounded-2xl bg-white p-5 shadow-soft ring-1 ring-blush-50 hover:ring-blush-200 transition">
    <div class="font-bold text-gray-800">انتقال داده از نسخهٔ جنگو</div><div class="text-xs text-gray-400 mt-1">وارد کردن فایل JSON خروجی export_for_php</div>
  </a>
  <a href="/admin/backup/" class="rounded-2xl bg-sand-50 p-5 shadow-soft ring-1 ring-sand-100 hover:ring-sand-300 transition">
    <div class="font-bold text-gray-800">دانلود بکاپ پایگاه داده</div><div class="text-xs text-gray-500 mt-1">فایل SQLite کامل — برای نگهداری در جای امن</div>
  </a>
</div>
<?php require __DIR__ . '/_shell_bottom.php'; ?>
