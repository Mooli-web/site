<?php View::$page['title'] = 'سوالات متداول'; $heading = 'سوالات متداول'; require __DIR__ . '/_shell_top.php'; require __DIR__ . '/_form_bits.php'; ?>
<div class="grid gap-6 lg:grid-cols-3">
  <div class="lg:col-span-2 rounded-2xl bg-white shadow-soft ring-1 ring-blush-50 divide-y divide-gray-50">
    <?php foreach ($items as $it): ?>
    <div class="p-4 flex items-start justify-between gap-3">
      <div><div class="font-medium text-gray-800"><?= $it['is_active'] ? '' : '<span class="text-xs text-gray-400">(غیرفعال)</span> ' ?><?= e($it['question']) ?></div><div class="text-xs text-gray-500 mt-1 line-clamp-2"><?= e($it['answer']) ?></div></div>
      <div class="whitespace-nowrap text-left space-x-3 space-x-reverse"><a href="?edit=<?= $it['id'] ?>" class="text-xs text-blush-700 hover:underline">ویرایش</a> <?= f_delete("/admin/faqs/{$it['id']}/delete/") ?></div>
    </div>
    <?php endforeach; if (!$items): ?><div class="p-8 text-center text-gray-400">سوالی ثبت نشده است.</div><?php endif; ?>
  </div>
  <form method="post" action="/admin/faqs/save/" class="rounded-2xl bg-white p-5 shadow-soft ring-1 ring-blush-50 space-y-4 h-fit">
    <?= Csrf::field() ?><input type="hidden" name="id" value="<?= $edit['id'] ?? 0 ?>">
    <h2 class="font-bold text-gray-800"><?= $edit ? 'ویرایش سوال' : 'سوال جدید' ?></h2>
    <?= f_input('question', 'پرسش', $edit['question'] ?? '', 'text', ['attrs' => 'required']) ?>
    <?= f_textarea('answer', 'پاسخ', $edit['answer'] ?? '', 5) ?>
    <?= f_input('display_order', 'ترتیب نمایش', $edit['display_order'] ?? 0, 'number') ?>
    <?= f_check('is_active', 'فعال', $edit ? $edit['is_active'] : true) ?>
    <div class="flex items-center gap-3"><?= f_submit() ?><?php if ($edit): ?><a href="/admin/faqs/" class="text-xs text-gray-500">انصراف</a><?php endif; ?></div>
  </form>
</div>
<?php require __DIR__ . '/_shell_bottom.php'; ?>
