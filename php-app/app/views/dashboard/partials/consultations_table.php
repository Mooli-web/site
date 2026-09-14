<div class="rounded-2xl bg-white shadow-soft ring-1 ring-blush-50 overflow-hidden">
  <div class="flex flex-wrap items-center justify-between gap-2 px-4 sm:px-5 py-4 border-b border-blush-50">
    <div class="flex items-center gap-2">
      <h2 class="font-bold text-gray-800">درخواست‌های مشاوره</h2>
      <span class="rounded-full bg-blush-50 px-2.5 py-0.5 text-xs font-bold text-blush-700"><?= e(count($consultations)) ?></span>
    </div>
    <?php if (!empty($applied)): ?>
    <div class="flex flex-wrap gap-1.5">
      <?php $loop_i = 0; $loop_n = count($applied); foreach ($applied as $a): $loop_i++; $loop_last = $loop_i === $loop_n; ?>
      <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-[11px] text-gray-600"><?= e($a) ?></span>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <?php if (!empty($consultations)): ?>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="text-right text-xs text-gray-400 border-b border-blush-50">
          <th class="px-4 py-3 font-medium">درخواست‌کننده</th>
          <th class="px-4 py-3 font-medium">پیام</th>
          <th class="px-4 py-3 font-medium">تاریخ ثبت</th>
          <th class="px-4 py-3 font-medium">وضعیت</th>
          <th class="px-4 py-3 font-medium text-center">عملیات</th>
        </tr>
      </thead>
      <tbody id="consultations-tbody" class="divide-y divide-blush-50">
        <?php $loop_i = 0; $loop_n = count($consultations); foreach ($consultations as $item): $loop_i++; $loop_last = $loop_i === $loop_n; ?>
          <?= view('dashboard/partials/consultation_row', ['item' => $item, 'status_choices' => $status_choices]) ?>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php else: ?>
  <div class="p-12 text-center">
    <span class="inline-flex w-14 h-14 items-center justify-center rounded-full bg-blush-50 text-blush-400">
      <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h8M8 14h5M21 12a9 9 0 01-9 9c-1.6 0-3.1-.4-4.4-1.1L3 21l1.1-4.6A9 9 0 1121 12z"/></svg>
    </span>
    <p class="mt-4 text-gray-600 font-medium">درخواستی یافت نشد</p>
    <p class="mt-1 text-sm text-gray-400">با فیلترهای انتخاب‌شده درخواستی موجود نیست.</p>
  </div>
  <?php endif; ?>
</div>
