<?php View::$page['title'] = 'مشتریان'; $heading = 'مشتریان'; require __DIR__ . '/_shell_top.php'; require __DIR__ . '/_form_bits.php'; ?>
<form method="get" class="flex gap-2"><input type="text" name="q" value="<?= e($q) ?>" placeholder="نام یا شماره موبایل…" class="w-full max-w-sm rounded-xl border border-gray-300 px-4 py-2.5 text-sm"><button class="rounded-xl bg-blush-600 px-4 text-sm text-white">جستجو</button></form>
<div class="rounded-2xl bg-white shadow-soft ring-1 ring-blush-50 overflow-x-auto">
  <table class="w-full text-sm">
    <thead class="bg-gray-50 text-xs text-gray-500"><tr><th class="px-4 py-3 text-right">نام</th><th class="px-4 py-3">موبایل</th><th class="px-4 py-3">نوبت‌ها</th><th class="px-4 py-3">ثبت‌نام</th><th class="px-4 py-3">وضعیت</th><th></th></tr></thead>
    <tbody class="divide-y divide-gray-50">
      <?php foreach ($items as $c): ?>
      <tr class="<?= $c['is_blocked'] ? 'opacity-60' : '' ?>">
        <td class="px-4 py-3 font-medium text-gray-800"><?= e(trim($c['first_name'] . ' ' . $c['last_name'])) ?></td>
        <td class="px-4 py-3 text-center" dir="ltr"><?= fa_digits($c['mobile']) ?></td>
        <td class="px-4 py-3 text-center"><?= fa_digits($c['n']) ?></td>
        <td class="px-4 py-3 text-center text-gray-500"><?= created_fa($c['created_at'])['date'] ?></td>
        <td class="px-4 py-3 text-center"><?= $c['is_blocked'] ? '<span class="text-xs text-red-600">مسدود</span>' : '<span class="text-xs text-green-600">فعال</span>' ?></td>
        <td class="px-4 py-3 text-left"><form method="post" action="/admin/customers/<?= $c['id'] ?>/block/"><?= Csrf::field() ?><button class="text-xs <?= $c['is_blocked'] ? 'text-green-700' : 'text-red-600' ?> hover:underline"><?= $c['is_blocked'] ? 'رفع مسدودی' : 'مسدود کردن' ?></button></form></td>
      </tr>
      <?php endforeach; if (!$items): ?><tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">مشتری‌ای یافت نشد.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
<?php require __DIR__ . '/_shell_bottom.php'; ?>
