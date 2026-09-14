<?php View::$page['title'] = 'سوالات متداول — ' . ($clinic['name']) . ''; ?>
<?php View::$page['description'] = 'پاسخ پرسش‌های پرتکرار درباره خدمات، نوبت‌دهی و درمان‌های ' . ($clinic['name']) . '.'; ?>
<!-- ============ HERO ============ -->
<section class="relative overflow-hidden">
  <div class="absolute inset-0 -z-10 bg-gradient-to-b from-blush-50 via-white to-sand-50"></div>
  <div class="absolute -z-10 top-24 -right-24 w-72 h-72 bg-sand-200/40 blur-3xl rounded-full"></div>
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 pt-28 sm:pt-32 md:pt-40 pb-10 text-center">
    <span class="inline-flex items-center gap-2 rounded-full bg-white/70 backdrop-blur px-4 py-1.5 text-sm font-medium text-blush-700 shadow-soft ring-1 ring-blush-100">
      راهنما
    </span>
    <h1 class="mt-6 text-[2rem] sm:text-4xl md:text-5xl font-bold leading-tight text-gray-800">
      سوالات <span class="gradient-text">متداول</span>
    </h1>
    <p class="mt-6 text-lg leading-8 text-gray-600">
      پاسخ پرسش‌هایی که بیشتر از ما پرسیده می‌شود. اگر پاسخ سوال خود را پیدا نکردید،
      با ما <a href="/contact/" class="text-blush-600 font-medium hover:underline">تماس بگیرید</a>.
    </p>
  </div>
</section>

<!-- ============ آکاردئون ============ -->
<section class="pb-20">
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <?php if (!empty($faqs)): ?>
    <div x-data="{ open: null }" class="space-y-3">
      <?php $loop_i = 0; $loop_n = count($faqs); foreach ($faqs as $item): $loop_i++; $loop_last = $loop_i === $loop_n; ?>
      <div class="rounded-2xl bg-white shadow-soft ring-1 ring-blush-50 overflow-hidden transition"
           :class="open === <?= e($loop_i) ?> ? 'ring-blush-200' : ''">
        <button type="button"
                @click="open === <?= e($loop_i) ?> ? open = null : open = <?= e($loop_i) ?>"
                class="w-full flex items-center justify-between gap-4 text-right px-5 sm:px-6 py-5 hover:bg-blush-50/40 transition">
          <span class="font-bold text-gray-800 text-[15px] sm:text-base"><?= e($item['question']) ?></span>
          <span class="shrink-0 inline-flex w-8 h-8 items-center justify-center rounded-full bg-blush-100 text-blush-600 transition-transform duration-300"
                :class="open === <?= e($loop_i) ?> ? 'rotate-45' : ''">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
          </span>
        </button>
        <div x-show="open === <?= e($loop_i) ?>" x-cloak
             x-transition:enter="transition-all ease-out duration-300"
             x-transition:enter-start="opacity-0 max-h-0"
             x-transition:enter-end="opacity-100 max-h-[600px]"
             x-transition:leave="transition-all ease-in duration-200"
             x-transition:leave-start="opacity-100 max-h-[600px]"
             x-transition:leave-end="opacity-0 max-h-0"
             class="overflow-hidden">
          <div class="px-5 sm:px-6 pb-5 pt-0 text-gray-600 leading-8 border-t border-blush-50">
            <p class="pt-4"><?= nl2br(e($item['answer'])) ?></p>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="max-w-md mx-auto text-center py-16">
      <div class="inline-flex w-16 h-16 items-center justify-center rounded-full bg-blush-50 text-blush-400 mb-5">
        <svg class="w-9 h-9" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M9.5 9a2.5 2.5 0 115 0c0 1.5-2.5 2-2.5 3.5M12 17h.01"/></svg>
      </div>
      <h2 class="text-xl font-bold text-gray-800">هنوز سوالی ثبت نشده است</h2>
      <p class="mt-2 text-gray-600">به‌زودی پرسش‌های متداول در این بخش قرار می‌گیرد.</p>
    </div>
    <?php endif; ?>

    <!-- CTA -->
    <div class="mt-12 rounded-3xl bg-gradient-to-l from-blush-600 to-blush-500 px-6 py-10 text-center shadow-soft">
      <h2 class="text-2xl font-bold text-white">پاسخ سوالتان را پیدا نکردید؟</h2>
      <p class="mt-3 text-blush-50">می‌توانید یک مشاوره رایگان درخواست دهید تا کارشناسان ما با شما تماس بگیرند.</p>
      <a href="/consultation/"
         class="mt-6 inline-flex items-center gap-2 rounded-full bg-white px-7 py-3.5 text-base font-bold text-blush-700 shadow-soft hover:scale-[1.03] transition">
        درخواست مشاوره رایگان
      </a>
    </div>
  </div>
</section>


