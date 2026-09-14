<?php View::$page['title'] = 'صفحه پیدا نشد — ' . ((($clinic['name'] ?? '') !== '' ? $clinic['name'] : "کلینیک زیبایی")) . ''; ?>
<section class="relative min-h-[70vh] flex items-center justify-center overflow-hidden">
  <div class="absolute inset-0 -z-10 bg-gradient-to-b from-blush-50 via-white to-sand-50"></div>
  <div class="absolute -z-10 top-24 -left-24 w-72 h-72 bg-blush-200/40 blur-3xl rounded-full"></div>
  <div class="text-center px-6 pt-24">
    <div class="text-7xl md:text-8xl font-bold gradient-text">۴۰۴</div>
    <h1 class="mt-4 text-2xl md:text-3xl font-bold text-gray-800">این صفحه پیدا نشد</h1>
    <p class="mt-3 text-gray-600 max-w-md mx-auto">متأسفیم! صفحه‌ای که دنبالش بودید وجود ندارد یا جابه‌جا شده است.</p>
    <a href="/"
       class="mt-8 inline-flex items-center gap-2 rounded-full bg-gradient-to-l from-blush-500 to-blush-600 px-7 py-3.5 text-base font-bold text-white shadow-soft hover:from-blush-600 hover:to-blush-700 transition">
      بازگشت به خانه
      <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7M4 12h16"/></svg>
    </a>
  </div>
</section>

