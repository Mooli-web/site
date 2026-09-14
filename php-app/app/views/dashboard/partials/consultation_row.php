<tr id="cons-row-<?= e($item['id']) ?>" <?php if (!empty($swapped)): ?>hx-swap-oob="true"<?php endif; ?>
    x-data="{ menu: false, confirmDel: false }" class="hover:bg-blush-50/30 transition">
  <!-- درخواست‌کننده -->
  <td class="px-4 py-3.5">
    <div class="font-medium text-gray-800"><?= e(trim($item['first_name'] . ' ' . $item['last_name'])) ?></div>
    <div class="text-xs text-gray-400" dir="ltr"><?= e($item['mobile']) ?></div>
  </td>
  <!-- پیام -->
  <td class="px-4 py-3.5 max-w-xs">
    <div class="text-gray-600 text-sm line-clamp-2"><?= e((($item['message'] ?? '') !== '' ? $item['message'] : "—")) ?></div>
  </td>
  <!-- تاریخ ثبت -->
  <td class="px-4 py-3.5 whitespace-nowrap">
    <div class="text-gray-700 text-sm"><?= e(created_fa($item['created_at'])['date']) ?></div>
    <div class="text-xs text-gray-400" dir="ltr"><?= e(created_fa($item['created_at'])['time']) ?></div>
  </td>
  <!-- وضعیت -->
  <td class="px-4 py-3.5">
    <?php if ($item['status'] == 'new'): ?><span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700 ring-1 ring-amber-200"><?= e(($item['status_label'] ?? '')) ?></span>
    <?php elseif ($item['status'] == 'in_progress'): ?><span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700 ring-1 ring-blue-200"><?= e(($item['status_label'] ?? '')) ?></span>
    <?php elseif ($item['status'] == 'done'): ?><span class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-700 ring-1 ring-green-200"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg><?= e(($item['status_label'] ?? '')) ?></span>
    <?php else: ?><span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-500 ring-1 ring-gray-200"><?= e(($item['status_label'] ?? '')) ?></span>
    <?php endif; ?>
  </td>
  <!-- عملیات -->
  <td class="px-4 py-3.5">
    <div class="flex items-center justify-center gap-1.5 relative">
      <!-- منوی تغییر وضعیت -->
      <div class="relative" @click.outside="menu=false">
        <button @click="menu=!menu" type="button"
                class="inline-flex items-center gap-1 rounded-lg bg-blush-50 px-2.5 py-1.5 text-xs font-medium text-blush-700 hover:bg-blush-100 transition">
          تغییر وضعیت
          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div x-show="menu" x-cloak x-transition
             class="absolute left-0 mt-1 w-44 rounded-xl bg-white shadow-soft ring-1 ring-blush-100 p-1 z-30">
          <?php $loop_i = 0; $loop_n = count($status_choices); foreach ($status_choices as $val => $label): $loop_i++; $loop_last = $loop_i === $loop_n; ?>
          <button type="button"
                  hx-post="<?= url('/dashboard/consultations/' . $item['id'] . '/status/') ?>"
                  hx-vals='{"status": "<?= e($val) ?>"}'
                  hx-target="#cons-row-<?= e($item['id']) ?>"
                  hx-swap="outerHTML"
                  @click="menu=false"
                  class="w-full text-right rounded-lg px-3 py-2 text-xs hover:bg-blush-50 <?php if ($item['status'] == $val): ?>text-blush-700 font-bold bg-blush-50<?php else: ?>text-gray-600<?php endif; ?>">
            <?= e($label) ?>
          </button>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- حذف -->
      <div class="relative" @click.outside="confirmDel=false">
        <button @click="confirmDel=!confirmDel" type="button"
                class="inline-flex w-8 h-8 items-center justify-center rounded-lg text-gray-400 hover:bg-red-50 hover:text-red-600 transition" title="حذف">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.9 12a2 2 0 01-2 1.9H7.9a2 2 0 01-2-1.9L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16"/></svg>
        </button>
        <div x-show="confirmDel" x-cloak x-transition
             class="absolute left-0 mt-1 w-44 rounded-xl bg-white shadow-soft ring-1 ring-red-100 p-3 z-30">
          <p class="text-xs text-gray-600 mb-2">این درخواست حذف شود؟</p>
          <div class="flex gap-2">
            <button type="button"
                    hx-post="<?= url('/dashboard/consultations/' . $item['id'] . '/delete/') ?>"
                    hx-target="#cons-row-<?= e($item['id']) ?>"
                    hx-swap="outerHTML"
                    class="flex-1 rounded-lg bg-red-600 px-2 py-1.5 text-xs font-bold text-white hover:bg-red-700">حذف</button>
            <button @click="confirmDel=false" type="button"
                    class="flex-1 rounded-lg bg-gray-100 px-2 py-1.5 text-xs text-gray-600 hover:bg-gray-200">انصراف</button>
          </div>
        </div>
      </div>
    </div>
  </td>
</tr>
