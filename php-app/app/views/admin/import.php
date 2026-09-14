<?php View::$page['title'] = 'انتقال داده'; $heading = 'انتقال داده از نسخهٔ جنگو'; require __DIR__ . '/_shell_top.php'; require __DIR__ . '/_form_bits.php'; ?>
<div class="grid gap-6 lg:grid-cols-2">
  <form method="post" enctype="multipart/form-data" class="rounded-2xl bg-white p-5 shadow-soft ring-1 ring-blush-50 space-y-4">
    <?= Csrf::field() ?>
    <h2 class="font-bold text-gray-800">وارد کردن فایل JSON</h2>
    <ol class="list-decimal pr-5 text-sm leading-7 text-gray-600">
      <li>روی سرور جنگو: <code dir="ltr" class="rounded bg-gray-100 px-1.5 text-xs">python manage.py export_for_php --out export.json</code></li>
      <li>فایل <code>export.json</code> را این‌جا انتخاب و ارسال کنید.</li>
      <li>تصاویر گالری را از <code dir="ltr" class="text-xs">media/before_after/</code> جنگو به <code dir="ltr" class="text-xs">public/media/before_after/</code> کپی کنید.</li>
      <li>کاربران پنل باید دوباره از بخش «کاربران» با رمز جدید ساخته شوند (هش رمزها قابل انتقال نیست).</li>
    </ol>
    <?= f_input('file', 'فایل export.json', '', 'file', ['attrs' => 'accept="application/json,.json" required']) ?>
    <?php if ($counts['appointments'] || $counts['customers']): ?>
    <label class="flex items-start gap-2 text-sm text-gray-700"><input type="checkbox" name="wipe" value="1" class="mt-1 rounded border border-gray-300 text-blush-600">
      <span>قبل از ورود، داده‌های فعلی (<?= fa_digits($counts['appointments']) ?> نوبت، <?= fa_digits($counts['customers']) ?> مشتری، خدمات نمونه و …) پاک شود.<br><span class="text-xs text-red-600">برگشت‌ناپذیر — قبلش از «بکاپ» یک نسخه بگیرید.</span></span></label>
    <?php endif; ?>
    <?= f_submit('شروع انتقال') ?>
  </form>
  <?php if (!empty($result)): ?>
  <?= card_open('نتیجهٔ آخرین انتقال') ?>
    <table class="w-full text-sm"><tbody class="divide-y divide-gray-50">
      <?php $labels = ['clinic_settings' => 'تنظیمات کلینیک', 'customers' => 'مشتریان', 'service_categories' => 'دسته‌ها', 'services' => 'خدمات', 'working_hours' => 'بازه‌های کاری', 'holidays' => 'تعطیلات', 'appointments' => 'نوبت‌ها', 'appointments_skipped' => 'نوبت‌های ردشده (تکراری/ناقص)', 'contact_messages' => 'پیام‌ها', 'before_after' => 'گالری', 'faqs' => 'سوالات', 'consultation_requests' => 'مشاوره‌ها']; ?>
      <?php foreach ($result as $k => $v): ?><tr><td class="py-2 text-gray-600"><?= e($labels[$k] ?? $k) ?></td><td class="py-2 text-left font-bold"><?= fa_digits($v) ?></td></tr><?php endforeach; ?>
    </tbody></table>
  </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/_shell_bottom.php'; ?>
