<?php View::$page['title'] = 'گالری قبل/بعد'; $heading = 'گالری قبل و بعد'; require __DIR__ . '/_shell_top.php'; require __DIR__ . '/_form_bits.php'; ?>
<div class="grid gap-6 lg:grid-cols-3">
  <div class="lg:col-span-2 grid gap-4 sm:grid-cols-2">
    <?php foreach ($items as $it): ?>
    <div class="rounded-2xl bg-white p-3 shadow-soft ring-1 ring-blush-50">
      <div class="grid grid-cols-2 gap-2">
        <img src="<?= e($it['before_image']) ?>" class="aspect-square w-full rounded-xl object-cover" alt="قبل">
        <img src="<?= e($it['after_image']) ?>" class="aspect-square w-full rounded-xl object-cover" alt="بعد">
      </div>
      <div class="mt-3 flex items-start justify-between gap-2">
        <div><div class="text-sm font-medium text-gray-800"><?= $it['is_active'] ? '' : '<span class="text-xs text-gray-400">(غیرفعال)</span> ' ?><?= e($it['title']) ?></div><div class="text-xs text-gray-400"><?= e($it['description']) ?></div></div>
        <div class="whitespace-nowrap space-x-3 space-x-reverse"><a href="?edit=<?= $it['id'] ?>" class="text-xs text-blush-700 hover:underline">ویرایش</a> <?= f_delete("/admin/gallery/{$it['id']}/delete/") ?></div>
      </div>
    </div>
    <?php endforeach; if (!$items): ?><div class="sm:col-span-2 rounded-2xl bg-white p-8 text-center text-gray-400 shadow-soft">تصویری ثبت نشده است.</div><?php endif; ?>
  </div>
  <form method="post" action="/admin/gallery/save/" enctype="multipart/form-data" class="rounded-2xl bg-white p-5 shadow-soft ring-1 ring-blush-50 space-y-4 h-fit">
    <?= Csrf::field() ?><input type="hidden" name="id" value="<?= $edit['id'] ?? 0 ?>">
    <h2 class="font-bold text-gray-800"><?= $edit ? 'ویرایش مورد' : 'مورد جدید' ?></h2>
    <?= f_input('title', 'عنوان', $edit['title'] ?? '', 'text', ['attrs' => 'required']) ?>
    <?= f_input('description', 'توضیح کوتاه', $edit['description'] ?? '') ?>
    <?= f_input('before_image', 'تصویر قبل' . ($edit ? ' (خالی = بدون تغییر)' : ''), '', 'file', ['attrs' => 'accept="image/jpeg,image/png,image/webp"' . ($edit ? '' : ' required'), 'help' => "JPG/PNG/WebP، حداکثر ۵ مگابایت (محدودیت سرور: $max_upload)"]) ?>
    <?= f_input('after_image', 'تصویر بعد' . ($edit ? ' (خالی = بدون تغییر)' : ''), '', 'file', ['attrs' => 'accept="image/jpeg,image/png,image/webp"' . ($edit ? '' : ' required')]) ?>
    <?= f_input('display_order', 'ترتیب نمایش', $edit['display_order'] ?? 0, 'number') ?>
    <?= f_check('is_active', 'فعال', $edit ? $edit['is_active'] : true) ?>
    <div class="flex items-center gap-3"><?= f_submit() ?><?php if ($edit): ?><a href="/admin/gallery/" class="text-xs text-gray-500">انصراف</a><?php endif; ?></div>
  </form>
</div>
<?php require __DIR__ . '/_shell_bottom.php'; ?>
