<?php View::$page['title'] = 'تماس با ما — ' . ($clinic['name']) . ''; ?>
<?php View::$page['description'] = 'راه‌های ارتباط با ' . ($clinic['name']) . '؛ تلفن، آدرس و فرم تماس برای پاسخ به سوالات و رزرو نوبت.'; ?>
<!-- ============ HERO ============ -->
<section class="relative overflow-hidden">
  <div class="absolute inset-0 -z-10 bg-gradient-to-b from-blush-50 via-white to-sand-50"></div>
  <div class="absolute -z-10 top-24 -right-24 w-72 h-72 bg-blush-200/40 blur-3xl rounded-full"></div>

  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pt-28 sm:pt-32 md:pt-40 pb-10 text-center">
    <span class="inline-flex items-center gap-2 rounded-full bg-white/70 backdrop-blur px-4 py-1.5 text-sm font-medium text-blush-700 shadow-soft ring-1 ring-blush-100">
      ارتباط با <?= e($clinic['name']) ?>
    </span>
    <h1 class="mt-6 text-[2rem] sm:text-4xl md:text-5xl font-bold leading-tight text-gray-800">
      <span class="gradient-text">تماس</span> با ما
    </h1>
    <p class="mt-6 text-lg leading-8 text-gray-600 max-w-2xl mx-auto">
      سوالی دارید یا می‌خواهید بیشتر بدانید؟ خوشحال می‌شویم از شما بشنویم. پیام خود را بفرستید یا مستقیم با ما تماس بگیرید.
    </p>
  </div>
</section>

<!-- ============ اطلاعات + فرم ============ -->
<section class="pb-20">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid gap-8 lg:grid-cols-5">

      <!-- اطلاعات تماس -->
      <div class="lg:col-span-2 space-y-4 order-2 lg:order-1">
        <div class="rounded-3xl bg-white p-6 shadow-soft ring-1 ring-blush-50 flex items-start gap-4">
          <span class="inline-flex w-12 h-12 shrink-0 items-center justify-center rounded-2xl bg-blush-100 text-blush-600">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h2.28a1 1 0 01.95.68l1.2 3.6a1 1 0 01-.27 1.05l-1.4 1.4a16 16 0 006.27 6.27l1.4-1.4a1 1 0 011.05-.27l3.6 1.2a1 1 0 01.68.95V19a2 2 0 01-2 2A16 16 0 013 5z"/></svg>
          </span>
          <div>
            <h3 class="font-bold text-gray-800">تلفن تماس</h3>
            <p class="mt-1 text-gray-600" dir="ltr"><?= e((($clinic['phone'] ?? '') !== '' ? $clinic['phone'] : "۰۲۱–۱۲۳۴۵۶۷۸")) ?></p>
          </div>
        </div>

        <div class="rounded-3xl bg-white p-6 shadow-soft ring-1 ring-blush-50 flex items-start gap-4">
          <span class="inline-flex w-12 h-12 shrink-0 items-center justify-center rounded-2xl bg-blush-100 text-blush-600">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/><circle cx="12" cy="11" r="3"/></svg>
          </span>
          <div>
            <h3 class="font-bold text-gray-800">آدرس</h3>
            <p class="mt-1 text-gray-600 leading-7"><?= e((($clinic['address'] ?? '') !== '' ? $clinic['address'] : "تهران، خیابان نمونه، پلاک ۱۲")) ?></p>
          </div>
        </div>

        <div class="rounded-3xl bg-white p-6 shadow-soft ring-1 ring-blush-50 flex items-start gap-4">
          <span class="inline-flex w-12 h-12 shrink-0 items-center justify-center rounded-2xl bg-blush-100 text-blush-600">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2"/></svg>
          </span>
          <div>
            <h3 class="font-bold text-gray-800">ساعات کاری</h3>
            <p class="mt-1 text-gray-600 leading-7">شنبه تا چهارشنبه: ۹ تا ۱۸<br>پنجشنبه: ۹ تا ۱۳</p>
          </div>
        </div>

        <?php if (!empty($clinic['instagram'])): ?>
        <div class="rounded-3xl bg-white p-6 shadow-soft ring-1 ring-blush-50 flex items-start gap-4">
          <span class="inline-flex w-12 h-12 shrink-0 items-center justify-center rounded-2xl bg-blush-100 text-blush-600">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1"/></svg>
          </span>
          <div>
            <h3 class="font-bold text-gray-800">اینستاگرام</h3>
            <p class="mt-1 text-gray-600" dir="ltr"><?= e($clinic['instagram']) ?></p>
          </div>
        </div>
        <?php endif; ?>
      </div>

      <!-- فرم تماس -->
      <div class="lg:col-span-3 order-1 lg:order-2">
        <div class="rounded-3xl bg-white p-6 sm:p-7 md:p-9 shadow-soft ring-1 ring-blush-50">
          <h2 class="text-xl font-bold text-gray-800 mb-1">پیام بفرستید</h2>
          <p class="text-sm text-gray-500 mb-6">فرم زیر را پر کنید؛ در اولین فرصت پاسخ شما را می‌دهیم.</p>
          <?= view('website/partials/contact_form', get_defined_vars()) ?>
        </div>
      </div>

    </div>
  </div>
</section>


