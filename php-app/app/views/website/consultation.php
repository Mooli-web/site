<?php View::$page['title'] = 'مشاوره رایگان — ' . ($clinic['name']) . ''; ?>
<?php View::$page['description'] = 'درخواست مشاوره رایگان از ' . ($clinic['name']) . '؛ شماره خود را ثبت کنید تا کارشناسان ما با شما تماس بگیرند.'; ?>
<!-- ============ HERO ============ -->
<section class="relative overflow-hidden">
  <div class="absolute inset-0 -z-10 bg-gradient-to-b from-blush-50 via-white to-sand-50"></div>
  <div class="absolute -z-10 top-24 -left-24 w-72 h-72 bg-blush-200/40 blur-3xl rounded-full"></div>
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pt-28 sm:pt-32 md:pt-40 pb-10 text-center">
    <span class="inline-flex items-center gap-2 rounded-full bg-white/70 backdrop-blur px-4 py-1.5 text-sm font-medium text-blush-700 shadow-soft ring-1 ring-blush-100">
      <span class="w-2 h-2 rounded-full bg-blush-500 animate-pulse"></span>
      کاملاً رایگان
    </span>
    <h1 class="mt-6 text-[2rem] sm:text-4xl md:text-5xl font-bold leading-tight text-gray-800">
      درخواست <span class="gradient-text">مشاوره رایگان</span>
    </h1>
    <p class="mt-6 text-lg leading-8 text-gray-600 max-w-2xl mx-auto">
      مطمئن نیستید کدام خدمت برای شما مناسب است؟ شماره‌تان را ثبت کنید؛
      کارشناسان ما رایگان با شما تماس می‌گیرند و راهنمایی‌تان می‌کنند.
    </p>
  </div>
</section>

<!-- ============ مزایا + فرم ============ -->
<section class="pb-20">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid gap-8 lg:grid-cols-5 items-start">

      <!-- مزایا -->
      <div class="lg:col-span-2 space-y-4 order-2 lg:order-1">
        <div class="rounded-3xl bg-white p-6 shadow-soft ring-1 ring-blush-50 flex items-start gap-4">
          <span class="inline-flex w-11 h-11 shrink-0 items-center justify-center rounded-2xl bg-blush-100 text-blush-600">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
          </span>
          <div>
            <h3 class="font-bold text-gray-800">بدون هزینه</h3>
            <p class="mt-1 text-sm text-gray-600 leading-7">مشاوره اولیه کاملاً رایگان است.</p>
          </div>
        </div>
        <div class="rounded-3xl bg-white p-6 shadow-soft ring-1 ring-blush-50 flex items-start gap-4">
          <span class="inline-flex w-11 h-11 shrink-0 items-center justify-center rounded-2xl bg-blush-100 text-blush-600">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2"/></svg>
          </span>
          <div>
            <h3 class="font-bold text-gray-800">تماس سریع</h3>
            <p class="mt-1 text-sm text-gray-600 leading-7">در کوتاه‌ترین زمان با شما تماس می‌گیریم.</p>
          </div>
        </div>
        <div class="rounded-3xl bg-white p-6 shadow-soft ring-1 ring-blush-50 flex items-start gap-4">
          <span class="inline-flex w-11 h-11 shrink-0 items-center justify-center rounded-2xl bg-blush-100 text-blush-600">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </span>
          <div>
            <h3 class="font-bold text-gray-800">بدون ثبت‌نام</h3>
            <p class="mt-1 text-sm text-gray-600 leading-7">فقط نام و شماره موبایل کافی است.</p>
          </div>
        </div>
      </div>

      <!-- فرم -->
      <div class="lg:col-span-3 order-1 lg:order-2">
        <div class="rounded-3xl bg-white p-6 sm:p-7 md:p-9 shadow-soft ring-1 ring-blush-50">
          <h2 class="text-xl font-bold text-gray-800 mb-1">فرم درخواست مشاوره</h2>
          <p class="text-sm text-gray-500 mb-6">اطلاعات زیر را پر کنید تا با شما تماس بگیریم.</p>
          <?= view('website/partials/consultation_form', get_defined_vars()) ?>
        </div>
      </div>

    </div>
  </div>
</section>


