<tr id="appt-row-<?= e($appt['id']) ?>" <?php if (!empty($swapped)): ?>hx-swap-oob="true"<?php endif; ?>
    x-data="{ menu: false, confirmDel: false }" class="hover:bg-blush-50/30 transition">
  <!-- مشتری -->
  <td class="px-4 py-3.5">
    <div class="font-medium text-gray-800"><?= e($appt['customer']['full_name']) ?></div>
    <div class="text-xs text-gray-400" dir="ltr"><?= e($appt['customer']['mobile']) ?></div>
  </td>
  <!-- خدمت -->
  <td class="px-4 py-3.5">
    <div class="text-gray-700"><?= e($appt['service']['title']) ?></div>
    <div class="text-xs text-gray-400"><?= e($appt['service']['duration_minutes']) ?> دقیقه · <?= e(money($appt['price'])) ?> ت</div>
  </td>
  <!-- تاریخ و ساعت -->
  <td class="px-4 py-3.5 whitespace-nowrap">
    <div class="text-gray-700"><?= e($appt['date_fa']) ?></div>
    <div class="text-xs text-gray-400" dir="ltr"><?= fa_digits(substr((string)$appt['start_time'], 0, 5)) ?> - <?= fa_digits(substr((string)$appt['end_time'], 0, 5)) ?></div>
  </td>
  <!-- کد پیگیری -->
  <td class="px-4 py-3.5">
    <span class="rounded-lg bg-gray-50 px-2 py-1 text-xs font-bold text-gray-600" dir="ltr"><?= e($appt['tracking_code']) ?></span>
  </td>
  <!-- وضعیت -->
  <td class="px-4 py-3.5">
    <?php if ($appt['status'] == 'paid'): ?><span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700 ring-1 ring-emerald-200"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg><?= e(($appt['status_label'] ?? '')) ?></span>
    <?php elseif ($appt['status'] == 'waiting_payment'): ?><span class="inline-flex items-center gap-1 rounded-full bg-yellow-50 px-2.5 py-0.5 text-xs font-medium text-yellow-700 ring-1 ring-yellow-200"><?= e(($appt['status_label'] ?? '')) ?></span>
    <?php elseif ($appt['status'] == 'payment_failed'): ?><span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-medium text-red-700 ring-1 ring-red-200"><?= e(($appt['status_label'] ?? '')) ?></span>
    <?php elseif ($appt['status'] == 'pending'): ?><span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700 ring-1 ring-amber-200"><?= e(($appt['status_label'] ?? '')) ?></span>
    <?php elseif ($appt['status'] == 'confirmed'): ?><span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700 ring-1 ring-blue-200"><?= e(($appt['status_label'] ?? '')) ?></span>
    <?php elseif ($appt['status'] == 'done'): ?><span class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-700 ring-1 ring-green-200"><?= e(($appt['status_label'] ?? '')) ?></span>
    <?php elseif ($appt['status'] == 'no_show'): ?><span class="inline-flex items-center gap-1 rounded-full bg-orange-50 px-2.5 py-0.5 text-xs font-medium text-orange-700 ring-1 ring-orange-200"><?= e(($appt['status_label'] ?? '')) ?></span>
    <?php else: ?><span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-500 ring-1 ring-gray-200"><?= e(($appt['status_label'] ?? '')) ?></span>
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
             class="absolute left-0 mt-1 w-40 rounded-xl bg-white shadow-soft ring-1 ring-blush-100 p-1 z-30">
          <?php $loop_i = 0; $loop_n = count($status_choices); foreach ($status_choices as $val => $label): $loop_i++; $loop_last = $loop_i === $loop_n; ?>
          <button type="button"
                  hx-post="<?= url('/dashboard/appointments/' . $appt['id'] . '/status/') ?>"
                  hx-vals='{"status": "<?= e($val) ?>"}'
                  hx-target="#appt-row-<?= e($appt['id']) ?>"
                  hx-swap="outerHTML"
                  @click="menu=false"
                  class="w-full text-right rounded-lg px-3 py-2 text-xs hover:bg-blush-50 <?php if ($appt['status'] == $val): ?>text-blush-700 font-bold bg-blush-50<?php else: ?>text-gray-600<?php endif; ?>">
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
          <p class="text-xs text-gray-600 mb-2">این نوبت حذف شود؟</p>
          <div class="flex gap-2">
            <button type="button"
                    hx-post="<?= url('/dashboard/appointments/' . $appt['id'] . '/delete/') ?>"
                    hx-target="#appt-row-<?= e($appt['id']) ?>"
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
