<?php View::$page['title'] = 'پیام‌های تماس'; $heading = 'پیام‌های تماس'; require __DIR__ . '/_shell_top.php'; require __DIR__ . '/_form_bits.php'; ?>
<div class="rounded-2xl bg-white shadow-soft ring-1 ring-blush-50 divide-y divide-gray-50">
  <?php foreach ($items as $m): $t = created_fa($m['created_at']); ?>
  <div class="p-4 <?= $m['is_read'] ? '' : 'bg-blush-50/40' ?>">
    <div class="flex flex-wrap items-center justify-between gap-2">
      <div class="text-sm"><span class="font-medium text-gray-800"><?= e($m['name']) ?></span> <span class="text-gray-400" dir="ltr"><?= fa_digits($m['mobile']) ?></span>
        <?php if ($m['subject']): ?><span class="mr-2 rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-600"><?= e($m['subject']) ?></span><?php endif; ?></div>
      <div class="text-xs text-gray-400"><?= $t['date'] ?> — <?= fa_digits($t['time']) ?>
        <?php if (!$m['is_read']): ?><form method="post" action="/admin/messages/<?= $m['id'] ?>/read/" class="inline mr-2"><?= Csrf::field() ?><button class="text-blush-700 hover:underline">خوانده شد</button></form><?php endif; ?>
        <span class="mr-2"><?= f_delete("/admin/messages/{$m['id']}/delete/") ?></span></div>
    </div>
    <p class="mt-2 text-sm leading-7 text-gray-700 whitespace-pre-line"><?= e($m['message']) ?></p>
  </div>
  <?php endforeach; if (!$items): ?><div class="p-8 text-center text-gray-400">پیامی وجود ندارد.</div><?php endif; ?>
</div>
<?php require __DIR__ . '/_shell_bottom.php'; ?>
