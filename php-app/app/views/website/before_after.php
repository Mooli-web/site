<?php View::$page['title'] = 'گالری قبل و بعد — ' . ($clinic['name']) . ''; ?>
<?php View::$page['description'] = 'نمونه‌هایی از نتایج درمان‌ها و خدمات ' . ($clinic['name']) . ' به‌صورت تصاویر قبل و بعد.'; ?>
<!-- ============ HERO ============ -->
<section class="relative overflow-hidden">
  <div class="absolute inset-0 -z-10 bg-gradient-to-b from-blush-50 via-white to-sand-50"></div>
  <div class="absolute -z-10 top-24 -left-24 w-72 h-72 bg-blush-200/40 blur-3xl rounded-full"></div>
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pt-28 sm:pt-32 md:pt-40 pb-10 text-center">
    <span class="inline-flex items-center gap-2 rounded-full bg-white/70 backdrop-blur px-4 py-1.5 text-sm font-medium text-blush-700 shadow-soft ring-1 ring-blush-100">
      نمونه کارها
    </span>
    <h1 class="mt-6 text-[2rem] sm:text-4xl md:text-5xl font-bold leading-tight text-gray-800">
      گالری <span class="gradient-text">قبل و بعد</span>
    </h1>
    <p class="mt-6 text-lg leading-8 text-gray-600 max-w-2xl mx-auto">
      نتایج واقعی درمان‌ها و خدمات ما را ببینید. روی دستگیره‌ی وسط هر تصویر بکشید
      یا دکمه‌ها را بزنید تا تفاوت «قبل» و «بعد» را مقایسه کنید.
    </p>
  </div>
</section>

<!-- ============ گالری ============ -->
<section class="pb-20 pt-4">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <?php if (!empty($items)): ?>
    <div class="grid gap-6 sm:gap-8 md:grid-cols-2 lg:grid-cols-3">
      <?php $loop_i = 0; $loop_n = count($items); foreach ($items as $item): $loop_i++; $loop_last = $loop_i === $loop_n; ?>
      <article class="rounded-3xl bg-white p-4 shadow-soft ring-1 ring-blush-50 hover:ring-blush-200 transition">

        <!-- کامپوننت مقایسه‌ای (اسلایدر کشویی محلی با Alpine.js) -->
        <div x-data="{
                pos: 50,
                dragging: false,
                setFromClient(clientX, el) {
                  const rect = el.getBoundingClientRect();
                  let p = ((clientX - rect.left) / rect.width) * 100;
                  this.pos = Math.min(100, Math.max(0, p));
                }
             }"
             class="group relative select-none overflow-hidden rounded-2xl aspect-[4/3] cursor-ew-resize"
             @mousedown="dragging = true; setFromClient($event.clientX, $el)"
             @mousemove="dragging && setFromClient($event.clientX, $el)"
             @mouseup.window="dragging = false"
             @mouseleave="dragging = false"
             @touchstart="dragging = true; setFromClient($event.touches[0].clientX, $el)"
             @touchmove.prevent="dragging && setFromClient($event.touches[0].clientX, $el)"
             @touchend.window="dragging = false">

          <!-- تصویر «بعد» (لایه زیرین، کامل) -->
          <img src="<?= e($item['after_image']) ?>" alt="بعد — <?= e($item['title']) ?>"
               draggable="false"
               class="absolute inset-0 w-full h-full object-cover pointer-events-none">
          <span class="absolute top-3 left-3 z-20 rounded-full bg-green-500/90 px-3 py-1 text-xs font-bold text-white shadow">بعد</span>

          <!-- تصویر «قبل» (لایه رویی، با clip متغیر) -->
          <div class="absolute inset-0 overflow-hidden pointer-events-none"
               :style="'width: ' + pos + '%'">
            <img src="<?= e($item['before_image']) ?>" alt="قبل — <?= e($item['title']) ?>"
                 draggable="false"
                 class="absolute inset-0 h-full object-cover"
                 :style="'width: ' + (100 / (pos/100)) + '%; max-width:none;'"
                 style="width: 200%;">
            <span class="absolute top-3 right-3 z-20 rounded-full bg-blush-600/90 px-3 py-1 text-xs font-bold text-white shadow">قبل</span>
          </div>

          <!-- خط و دستگیره‌ی کشویی -->
          <div class="absolute inset-y-0 z-30 w-0.5 bg-white shadow pointer-events-none"
               :style="'right: ' + pos + '%; transform: translateX(50%);'">
            <span class="absolute top-1/2 right-1/2 translate-x-1/2 -translate-y-1/2 inline-flex w-9 h-9 items-center justify-center rounded-full bg-white shadow-soft ring-1 ring-blush-100">
              <svg class="w-5 h-5 text-blush-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7l-5 5 5 5M16 7l5 5-5 5"/></svg>
            </span>
          </div>

          <!-- دکمه‌های سریع قبل/بعد -->
          <div class="absolute bottom-3 inset-x-3 z-30 flex items-center justify-center gap-2">
            <button type="button" @click="pos = 100"
                    class="rounded-full bg-white/90 px-4 py-1.5 text-xs font-bold text-blush-700 shadow hover:bg-white transition">قبل</button>
            <button type="button" @click="pos = 50"
                    class="rounded-full bg-white/90 px-4 py-1.5 text-xs font-bold text-gray-600 shadow hover:bg-white transition">مقایسه</button>
            <button type="button" @click="pos = 0"
                    class="rounded-full bg-white/90 px-4 py-1.5 text-xs font-bold text-green-700 shadow hover:bg-white transition">بعد</button>
          </div>
        </div>

        <!-- عنوان و توضیح -->
        <div class="px-2 pt-4 pb-2">
          <h3 class="text-lg font-bold text-gray-800"><?= e($item['title']) ?></h3>
          <?php if (!empty($item['description'])): ?>
          <p class="mt-1.5 text-sm leading-7 text-gray-600"><?= e($item['description']) ?></p>
          <?php endif; ?>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="max-w-md mx-auto text-center py-16">
      <div class="inline-flex w-16 h-16 items-center justify-center rounded-full bg-blush-50 text-blush-400 mb-5">
        <svg class="w-9 h-9" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 15l-5-5L5 21"/></svg>
      </div>
      <h2 class="text-xl font-bold text-gray-800">هنوز نمونه‌ای ثبت نشده است</h2>
      <p class="mt-2 text-gray-600">به‌زودی نمونه‌هایی از نتایج کار ما در این بخش قرار می‌گیرد.</p>
    </div>
    <?php endif; ?>

    <!-- CTA -->
    <div class="mt-14 text-center">
      <a href="/booking/"
         class="inline-flex items-center gap-2 rounded-full bg-gradient-to-l from-blush-500 to-blush-600 px-7 sm:px-8 py-3.5 sm:py-4 text-base sm:text-lg font-bold text-white shadow-soft hover:from-blush-600 hover:to-blush-700 transition">
        رزرو نوبت
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7M4 12h16"/></svg>
      </a>
    </div>
  </div>
</section>


