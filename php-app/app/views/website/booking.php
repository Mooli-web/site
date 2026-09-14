<?php View::$page['title'] = 'رزرو نوبت — ' . ((($clinic['name'] ?? '') !== '' ? $clinic['name'] : "کلینیک زیبایی")) . ''; ?>
<section class="relative min-h-screen pt-24 md:pt-28 pb-16">
  <div class="absolute inset-0 -z-10 bg-gradient-to-b from-blush-50 via-white to-sand-50"></div>

  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

    <!-- سربرگ -->
    <div class="text-center mb-7 sm:mb-8">
      <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-800">رزرو نوبت آنلاین</h1>
      <p class="mt-2.5 sm:mt-3 text-sm sm:text-base text-gray-600">در چند گام ساده، نوبت خود را رزرو کنید.</p>
    </div>

    <!-- مراحل (نشانگر پیشرفت) -->
    <?= view('website/partials/stepper', ['active' => 1]) ?>

    <!-- بدنه‌ی فلو: محتوای هر مرحله با HTMX اینجا جایگزین می‌شود -->
    <div id="booking-body" class="mt-8">

      <!-- ===== مرحله ۱: انتخاب خدمت ===== -->
      <div class="anim-up">
        <h2 class="text-xl font-bold text-gray-800 mb-1">۱. خدمت موردنظر را انتخاب کنید</h2>
        <p class="text-sm text-gray-500 mb-6">ابتدا نوع خدمتی که می‌خواهید را برگزینید.</p>

        <?php $loop_i = 0; $loop_n = count($categories); foreach ($categories as $category): $loop_i++; $loop_last = $loop_i === $loop_n; ?>
          <?php if (!empty($category['services'])): ?>
          <div class="mb-7">
            <h3 class="flex items-center gap-2 text-sm font-bold text-blush-700 mb-3">
              <span class="w-1.5 h-1.5 rounded-full bg-blush-500"></span>
              <?= e($category['title']) ?>
            </h3>
            <div class="grid gap-3 sm:grid-cols-2">
              <?php $loop_i = 0; $loop_n = count($category['services']); foreach ($category['services'] as $service): $loop_i++; $loop_last = $loop_i === $loop_n; ?>
                <?php if (!empty($service['is_active'])): ?>
                <button type="button"
                        hx-get="/booking/calendar/?service=<?= e($service['id']) ?>"
                        hx-target="#booking-body"
                        hx-swap="innerHTML"
                        class="group text-right rounded-2xl bg-white p-5 shadow-soft ring-1 ring-blush-50 hover:ring-blush-300 hover:-translate-y-0.5 transition-all">
                  <div class="flex items-start justify-between gap-3">
                    <div>
                      <div class="font-bold text-gray-800 group-hover:text-blush-700"><?= e($service['title']) ?></div>
                      <?php if (!empty($service['description'])): ?>
                      <div class="mt-1 text-xs text-gray-500 leading-6 line-clamp-2"><?= e($service['description']) ?></div>
                      <?php endif; ?>
                    </div>
                    <span class="shrink-0 inline-flex items-center justify-center w-9 h-9 rounded-xl bg-blush-50 text-blush-600 group-hover:bg-blush-500 group-hover:text-white transition">
                      <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l-7 7 7 7"/></svg>
                    </span>
                  </div>
                  <div class="mt-4 flex items-center gap-4 text-xs text-gray-600">
                    <span class="inline-flex items-center gap-1">
                      <svg class="w-4 h-4 text-blush-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 7v5l3 2"/></svg>
                      <?= e($service['duration_minutes']) ?> دقیقه
                    </span>
                    <span class="inline-flex items-center gap-1 font-bold text-blush-700">
                      <?= e(money($service['price'])) ?> تومان
                    </span>
                  </div>
                </button>
                <?php endif; ?>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>
        <?php endforeach; if ($loop_n === 0): ?>
          <?= view('website/partials/empty_state', ['msg' => "در حال حاضر خدمتی برای رزرو ثبت نشده است."]) ?>
        <?php endif; ?>
      </div>

    </div>
  </div>

  <!-- نشانگر بارگذاری سراسری HTMX -->
  <div id="htmx-indicator" class="htmx-indicator fixed inset-0 z-50 hidden items-center justify-center bg-white/50 backdrop-blur-sm">
    <div class="w-12 h-12 rounded-full border-4 border-blush-200 border-t-blush-600 animate-spin"></div>
  </div>
</section>



<script>
  // نمایش لودر هنگام درخواست‌های HTMX
  document.addEventListener('htmx:beforeRequest', () => {
    const i = document.getElementById('htmx-indicator');
    if (i) { i.classList.remove('hidden'); i.classList.add('flex'); }
  });
  document.addEventListener('htmx:afterRequest', () => {
    const i = document.getElementById('htmx-indicator');
    if (i) { i.classList.add('hidden'); i.classList.remove('flex'); }
  });
  // اسکرول نرم به بالای فلو پس از هر تعویض مرحله
  document.addEventListener('htmx:afterSwap', (e) => {
    if (e.target.id === 'booking-body') {
      document.getElementById('booking-body').scrollIntoView({behavior:'smooth', block:'start'});
    }
  });
</script>

