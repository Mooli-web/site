<?php View::$page['title'] = 'خدمات'; $heading = 'خدمات'; require __DIR__ . '/_shell_top.php'; require __DIR__ . '/_form_bits.php'; ?>
<div class="grid gap-6 lg:grid-cols-3">
  <div class="lg:col-span-2 rounded-2xl bg-white shadow-soft ring-1 ring-blush-50 overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-gray-50 text-xs text-gray-500"><tr><th class="px-4 py-3 text-right">خدمت</th><th class="px-4 py-3">دسته</th><th class="px-4 py-3">مدت</th><th class="px-4 py-3">قیمت</th><th class="px-4 py-3">فعال</th><th class="px-4 py-3"></th></tr></thead>
      <tbody class="divide-y divide-gray-50">
        <?php foreach ($items as $it): ?>
        <tr>
          <td class="px-4 py-3 font-medium text-gray-800"><?= e($it['title']) ?></td>
          <td class="px-4 py-3 text-center text-gray-500"><?= e($it['category_title']) ?></td>
          <td class="px-4 py-3 text-center"><?= fa_digits($it['duration_minutes']) ?> د</td>
          <td class="px-4 py-3 text-center"><?= money($it['price']) ?></td>
          <td class="px-4 py-3 text-center"><?= $it['is_active'] ? '✅' : '—' ?></td>
          <td class="px-4 py-3 text-left whitespace-nowrap space-x-3 space-x-reverse"><a href="?edit=<?= $it['id'] ?>" class="text-xs text-blush-700 hover:underline">ویرایش</a> <?= f_delete("/admin/services/{$it['id']}/delete/") ?></td>
        </tr>
        <?php endforeach; if (!$items): ?><tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">خدمتی ثبت نشده است.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
  <form method="post" action="/admin/services/save/" class="rounded-2xl bg-white p-5 shadow-soft ring-1 ring-blush-50 space-y-4 h-fit">
    <?= Csrf::field() ?><input type="hidden" name="id" value="<?= $edit['id'] ?? 0 ?>">
    <h2 class="font-bold text-gray-800"><?= $edit ? 'ویرایش خدمت' : 'خدمت جدید' ?></h2>
    <?php if (!$categories): ?><p class="text-xs text-red-600">ابتدا یک <a href="/admin/categories/" class="underline">دسته‌بندی</a> بسازید.</p><?php endif; ?>
    <label class="block"><span class="mb-1.5 block text-sm font-medium text-gray-700">دسته‌بندی</span>
      <select name="category_id" required class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:border-blush-400 focus:ring-blush-200">
        <?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>" <?= ($edit['category_id'] ?? 0) == $c['id'] ? 'selected' : '' ?>><?= e($c['title']) ?></option><?php endforeach; ?>
      </select></label>
    <?= f_input('title', 'عنوان', $edit['title'] ?? '', 'text', ['attrs' => 'required']) ?>
    <?= f_textarea('description', 'توضیح', $edit['description'] ?? '', 2) ?>
    <div class="grid grid-cols-2 gap-3">
      <?= f_input('duration_minutes', 'مدت (دقیقه)', $edit['duration_minutes'] ?? 30, 'number') ?>
      <?= f_input('price', 'قیمت (تومان)', $edit['price'] ?? 0, 'text', ['attrs' => 'dir="ltr" inputmode="numeric"']) ?>
    </div>
    <?= f_input('order', 'ترتیب نمایش', $edit['order'] ?? 0, 'number') ?>
    <?= f_check('is_active', 'فعال', $edit ? $edit['is_active'] : true) ?>
    <div class="flex items-center gap-3"><?= f_submit() ?><?php if ($edit): ?><a href="/admin/services/" class="text-xs text-gray-500">انصراف</a><?php endif; ?></div>
  </form>
</div>
<?php require __DIR__ . '/_shell_bottom.php'; ?>
