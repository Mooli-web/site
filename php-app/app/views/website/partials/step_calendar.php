<?= view('website/partials/stepper', ['active' => 2, 'oob' => true]) ?>

<div class="anim-up">
  <div class="flex items-center justify-between mb-1">
    <h2 class="text-xl font-bold text-gray-800">۲. روز موردنظر را انتخاب کنید</h2>
    <?= view('website/partials/back_button', ['url' => '/booking/', 'qs' => '']) ?>
  </div>

  <div class="mb-6 inline-flex items-center gap-2 rounded-full bg-blush-50 px-4 py-1.5 text-sm text-blush-700 ring-1 ring-blush-100">
    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
    خدمت: <strong><?= e($service['title']) ?></strong> · <?= e($service['duration_minutes']) ?> دقیقه
  </div>

  <div class="max-w-md mx-auto rounded-3xl bg-white p-5 sm:p-6 shadow-soft ring-1 ring-blush-50">
    <div class="flex items-center justify-between mb-5">
      <?php if (!empty($month['prev']['enabled'])): ?>
      <button type="button"
              hx-get="/booking/calendar/?service=<?= e($service['id']) ?>&year=<?= e($month['prev']['year']) ?>&month=<?= e($month['prev']['month']) ?>"
              hx-target="#booking-body" hx-swap="innerHTML"
              class="inline-flex w-9 h-9 items-center justify-center rounded-full text-blush-600 hover:bg-blush-50 transition">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
      </button>
      <?php else: ?>
      <span class="w-9 h-9 inline-flex items-center justify-center text-gray-200">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
      </span>
      <?php endif; ?>

      <div class="text-center font-bold text-gray-800"><?= e($month['month_name']) ?> <?= e($month['year_fa']) ?></div>

      <?php if (!empty($month['next']['enabled'])): ?>
      <button type="button"
              hx-get="/booking/calendar/?service=<?= e($service['id']) ?>&year=<?= e($month['next']['year']) ?>&month=<?= e($month['next']['month']) ?>"
              hx-target="#booking-body" hx-swap="innerHTML"
              class="inline-flex w-9 h-9 items-center justify-center rounded-full text-blush-600 hover:bg-blush-50 transition">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 5l-7 7 7 7"/></svg>
      </button>
      <?php else: ?>
      <span class="w-9 h-9 inline-flex items-center justify-center text-gray-200">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 5l-7 7 7 7"/></svg>
      </span>
      <?php endif; ?>
    </div>

    <div class="grid grid-cols-7 gap-1 mb-2">
      <?php $loop_i = 0; $loop_n = count($month['headers']); foreach ($month['headers'] as $h): $loop_i++; $loop_last = $loop_i === $loop_n; ?>
      <div class="text-center text-xs font-bold text-gray-400 py-1"><?= e($h) ?></div>
      <?php endforeach; ?>
    </div>

    <div class="grid grid-cols-7 gap-1">
      <?php $loop_i = 0; $loop_n = count($month['weeks']); foreach ($month['weeks'] as $week): $loop_i++; $loop_last = $loop_i === $loop_n; ?>
        <?php $loop_i = 0; $loop_n = count($week); foreach ($week as $cell): $loop_i++; $loop_last = $loop_i === $loop_n; ?>
          <?php if ($cell === null): ?>
            <div></div>
          <?php elseif (!empty($cell['selectable'])): ?>
            <button type="button"
                    hx-get="/booking/slots/?service=<?= e($service['id']) ?>&date=<?= e($cell['iso']) ?>"
                    hx-target="#booking-body" hx-swap="innerHTML"
                    class="relative aspect-square flex items-center justify-center rounded-xl text-sm font-medium
                           <?php if (!empty($cell['is_today'])): ?>bg-blush-50 text-blush-700 ring-1 ring-blush-300<?php else: ?>text-gray-700 hover:bg-blush-500 hover:text-white<?php endif; ?> transition">
              <?= e($cell['day_fa']) ?>
              <?php if (!empty($cell['is_today'])): ?><span class="absolute bottom-1 w-1 h-1 rounded-full bg-blush-500"></span><?php endif; ?>
            </button>
          <?php else: ?>
            <div class="aspect-square flex items-center justify-center rounded-xl text-sm text-gray-300 cursor-not-allowed"><?= e($cell['day_fa']) ?></div>
          <?php endif; ?>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </div>

    <p class="mt-4 text-center text-xs text-gray-400">روزهای کم‌رنگ قابل رزرو نیستند.</p>
  </div>
</div>
