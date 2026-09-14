<?php View::$page['title'] = 'تنظیمات کلینیک'; $heading = 'تنظیمات کلینیک'; require __DIR__ . '/_shell_top.php'; require __DIR__ . '/_form_bits.php'; ?>
<form method="post" action="/admin/settings/save/" class="grid gap-6 lg:grid-cols-2">
  <?= Csrf::field() ?>
  <?= card_open('اطلاعات عمومی') ?>
    <div class="space-y-4">
      <?= f_input('name', 'نام کلینیک', $s['name']) ?>
      <?= f_input('phone', 'تلفن', $s['phone'], 'text', ['attrs' => 'dir="ltr"']) ?>
      <?= f_input('instagram', 'آیدی اینستاگرام (بدون @)', $s['instagram'], 'text', ['attrs' => 'dir="ltr"']) ?>
      <?= f_textarea('address', 'آدرس', $s['address'], 2) ?>
      <?= f_textarea('about', 'دربارهٔ ما (متن کوتاه)', $s['about'], 4) ?>
    </div>
  </div>
  <?= card_open('قواعد نوبت‌دهی') ?>
    <div class="space-y-4">
      <?= f_input('slot_step_minutes', 'گام زمانی اسلات‌ها (دقیقه)', $s['slot_step_minutes'], 'number', ['help' => 'مثلاً ۳۰ یعنی نوبت‌ها هر ۳۰ دقیقه پیشنهاد می‌شوند.']) ?>
      <?= f_input('max_advance_days', 'حداکثر روزهای قابل رزرو از امروز', $s['max_advance_days'], 'number') ?>
      <?= f_input('min_advance_hours', 'حداقل فاصله تا نوبت (ساعت)', $s['min_advance_hours'], 'number', ['help' => 'نوبت‌های نزدیک‌تر از این مقدار به مشتری نشان داده نمی‌شوند.']) ?>
    </div>
    <div class="mt-6"><?= f_submit() ?></div>
  </div>
</form>
<?php require __DIR__ . '/_shell_bottom.php'; ?>
