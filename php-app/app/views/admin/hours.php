<?php View::$page['title'] = 'ساعات کاری'; $heading = 'ساعات کاری و تعطیلات'; require __DIR__ . '/_shell_top.php'; require __DIR__ . '/_form_bits.php'; ?>
<div class="grid gap-6 lg:grid-cols-2">
  <div class="space-y-4">
    <?= card_open('بازه‌های کاری هفته') ?>
      <?php $byDay = []; foreach ($hours as $h) $byDay[$h['weekday']][] = $h; ?>
      <div class="divide-y divide-gray-50 text-sm">
        <?php foreach ($weekdays as $i => $name): ?>
        <div class="flex items-start justify-between py-2.5 gap-3">
          <span class="w-20 font-medium text-gray-700"><?= e($name) ?></span>
          <div class="flex-1 flex flex-wrap gap-2">
            <?php foreach ($byDay[$i] ?? [] as $h): ?>
            <span class="inline-flex items-center gap-2 rounded-full bg-blush-50 px-3 py-1 text-xs text-blush-700 ring-1 ring-blush-100" dir="ltr"><?= fa_digits($h['start_time']) ?>–<?= fa_digits($h['end_time']) ?>
              <form method="post" action="/admin/hours/<?= $h['id'] ?>/delete/" class="inline"><?= Csrf::field() ?><button class="text-red-500 hover:text-red-700" title="حذف">×</button></form></span>
            <?php endforeach; if (empty($byDay[$i])): ?><span class="text-xs text-gray-400">تعطیل</span><?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <form method="post" action="/admin/hours/save/" class="rounded-2xl bg-white p-5 shadow-soft ring-1 ring-blush-50 grid grid-cols-3 gap-3 items-end">
      <?= Csrf::field() ?>
      <label class="block col-span-3 sm:col-span-1"><span class="mb-1.5 block text-sm font-medium text-gray-700">روز</span>
        <select name="weekday" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm"><?php foreach ($weekdays as $i => $n): ?><option value="<?= $i ?>"><?= e($n) ?></option><?php endforeach; ?></select></label>
      <?= f_input('start_time', 'از', '09:00', 'time', ['attrs' => 'dir="ltr" required']) ?>
      <?= f_input('end_time', 'تا', '18:00', 'time', ['attrs' => 'dir="ltr" required']) ?>
      <div class="col-span-3"><?= f_submit('افزودن بازه') ?></div>
    </form>
  </div>
  <div class="space-y-4">
    <?= card_open('تعطیلات') ?>
      <ul class="divide-y divide-gray-50 text-sm">
        <?php foreach ($holidays as $h): ?>
        <li class="flex items-center justify-between py-2.5"><span><?= Jalali::pretty($h['date'], true) ?> <span class="text-xs text-gray-400"><?= e($h['reason']) ?></span></span><?= f_delete("/admin/holidays/{$h['id']}/delete/") ?></li>
        <?php endforeach; if (!$holidays): ?><li class="py-6 text-center text-gray-400">تعطیلی ثبت نشده است.</li><?php endif; ?>
      </ul>
    </div>
    <form method="post" action="/admin/holidays/save/" class="rounded-2xl bg-white p-5 shadow-soft ring-1 ring-blush-50 space-y-3">
      <?= Csrf::field() ?>
      <?= f_input('date', 'تاریخ شمسی (مثلاً ۱۴۰۵-۰۱-۱۳)', '', 'text', ['attrs' => 'dir="ltr" placeholder="1405-01-13" required']) ?>
      <?= f_input('reason', 'علت (اختیاری)', '') ?>
      <?= f_submit('ثبت تعطیلی') ?>
    </form>
  </div>
</div>
<?php require __DIR__ . '/_shell_bottom.php'; ?>
