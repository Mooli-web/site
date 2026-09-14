<?php View::$page['title'] = 'دسته‌بندی خدمات'; $heading = 'دسته‌بندی خدمات'; require __DIR__ . '/_shell_top.php'; require __DIR__ . '/_form_bits.php'; ?>
<div class="grid gap-6 lg:grid-cols-3">
  <div class="lg:col-span-2 rounded-2xl bg-white shadow-soft ring-1 ring-blush-50 overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-gray-50 text-xs text-gray-500"><tr><th class="px-4 py-3 text-right">عنوان</th><th class="px-4 py-3">ترتیب</th><th class="px-4 py-3">خدمات</th><th class="px-4 py-3">فعال</th><th class="px-4 py-3"></th></tr></thead>
      <tbody class="divide-y divide-gray-50">
        <?php foreach ($items as $it): ?>
        <tr>
          <td class="px-4 py-3 font-medium text-gray-800"><?= e($it['title']) ?><div class="text-xs text-gray-400 font-normal"><?= e($it['description']) ?></div></td>
          <td class="px-4 py-3 text-center"><?= fa_digits($it['order']) ?></td>
          <td class="px-4 py-3 text-center"><?= fa_digits($it['n']) ?></td>
          <td class="px-4 py-3 text-center"><?= $it['is_active'] ? '✅' : '—' ?></td>
          <td class="px-4 py-3 text-left whitespace-nowrap space-x-3 space-x-reverse"><a href="?edit=<?= $it['id'] ?>" class="text-xs text-blush-700 hover:underline">ویرایش</a> <?= f_delete("/admin/categories/{$it['id']}/delete/") ?></td>
        </tr>
        <?php endforeach; if (!$items): ?><tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">دسته‌ای ثبت نشده است.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
  <form method="post" action="/admin/categories/save/" class="rounded-2xl bg-white p-5 shadow-soft ring-1 ring-blush-50 space-y-4 h-fit">
    <?= Csrf::field() ?><input type="hidden" name="id" value="<?= $edit['id'] ?? 0 ?>">
    <h2 class="font-bold text-gray-800"><?= $edit ? 'ویرایش دسته' : 'دستهٔ جدید' ?></h2>
    <?= f_input('title', 'عنوان', $edit['title'] ?? '', 'text', ['attrs' => 'required']) ?>
    <?= f_textarea('description', 'توضیح کوتاه', $edit['description'] ?? '', 2) ?>
    <?= f_input('order', 'ترتیب نمایش', $edit['order'] ?? 0, 'number') ?>
    <?= f_check('is_active', 'فعال', $edit ? $edit['is_active'] : true) ?>
    <div class="flex items-center gap-3"><?= f_submit() ?><?php if ($edit): ?><a href="/admin/categories/" class="text-xs text-gray-500">انصراف</a><?php endif; ?></div>
  </form>
</div>
<?php require __DIR__ . '/_shell_bottom.php'; ?>
