<?php View::$page['title'] = '' . ($clinic['name']) . ' — رزرو آنلاین نوبت زیبایی'; ?>
<!-- ============ HERO ============ -->
<section class="relative overflow-hidden">
  <!-- پس‌زمینه ملایم -->
  <div class="absolute inset-0 -z-10 bg-gradient-to-b from-blush-50 via-white to-sand-50"></div>
  <div class="absolute -z-10 top-24 -left-24 w-72 h-72 bg-blush-200/50 blur-3xl rounded-full"></div>
  <div class="absolute -z-10 bottom-0 -right-16 w-72 h-72 bg-sand-200/50 blur-3xl rounded-full"></div>

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-28 md:pt-36 pb-16 md:pb-24">
    <div class="grid items-center gap-12 lg:grid-cols-2">

      <!-- متن -->
      <div class="text-center lg:text-right">
        <span class="anim-up d1 inline-flex items-center gap-2 rounded-full bg-white/70 backdrop-blur px-4 py-1.5 text-sm font-medium text-blush-700 shadow-soft ring-1 ring-blush-100">
          <span class="w-2 h-2 rounded-full bg-blush-500 animate-pulse"></span>
          نوبت‌دهی آنلاین، سریع و آسان
        </span>

        <h1 class="anim-up d2 mt-5 sm:mt-6 text-[2rem] sm:text-5xl md:text-6xl font-bold leading-tight text-gray-800">
          زیبایی شما،<br>
          <span class="gradient-text">رسالت ماست</span>
        </h1>

        <p class="anim-up d3 mt-5 sm:mt-6 text-base sm:text-lg leading-7 sm:leading-8 text-gray-600 max-w-xl mx-auto lg:mx-0">
          در <?= e($clinic['name']) ?> با بهره‌گیری از جدیدترین تجهیزات و تیمی از متخصصان مجرب،
          خدمات تخصصی پوست، مو و زیبایی را در فضایی آرام و لوکس تجربه کنید.
        </p>

        <!-- دکمه‌های CTA -->
        <div class="anim-up d4 mt-9 flex flex-col sm:flex-row items-center gap-4 justify-center lg:justify-start">
          <a href="/booking/"
             class="group inline-flex items-center justify-center gap-2 rounded-full bg-gradient-to-l from-blush-500 to-blush-600 px-7 sm:px-8 py-3.5 sm:py-4 text-base sm:text-lg font-bold text-white shadow-soft hover:from-blush-600 hover:to-blush-700 hover:scale-[1.03] transition-all w-full sm:w-auto">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M3 11h18M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"/></svg>
            رزرو نوبت
            <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7M4 12h16"/></svg>
          </a>
          <a href="#services"
             class="inline-flex items-center justify-center gap-2 rounded-full bg-white px-7 sm:px-8 py-3.5 sm:py-4 text-base sm:text-lg font-bold text-blush-700 ring-1 ring-blush-200 hover:bg-blush-50 transition w-full sm:w-auto">
            مشاهده خدمات
          </a>
        </div>

        <!-- آمار اعتماد -->
        <div class="anim-up d4 mt-10 flex items-center gap-5 sm:gap-8 justify-center lg:justify-start">
          <div>
            <div class="text-xl sm:text-2xl font-bold text-blush-700">+۵٬۰۰۰</div>
            <div class="text-xs sm:text-sm text-gray-500">مشتری راضی</div>
          </div>
          <div class="w-px h-9 sm:h-10 bg-blush-100"></div>
          <div>
            <div class="text-xl sm:text-2xl font-bold text-blush-700">+۱۵</div>
            <div class="text-xs sm:text-sm text-gray-500">متخصص مجرب</div>
          </div>
          <div class="w-px h-9 sm:h-10 bg-blush-100"></div>
          <div>
            <div class="text-xl sm:text-2xl font-bold text-blush-700">۱۰ سال</div>
            <div class="text-xs sm:text-sm text-gray-500">تجربه</div>
          </div>
        </div>
      </div>

      <!-- تصویر hero -->
      <div class="anim-up d3 relative">
        <div class="absolute inset-0 -z-10 blob-anim bg-gradient-to-br from-blush-200 to-sand-200 scale-110"></div>
        <img src="/static/img/hero.png" alt="فضای کلینیک زیبایی"
             class="relative w-full h-[340px] sm:h-[420px] md:h-[520px] object-cover rounded-[2rem] sm:rounded-[2.5rem] shadow-soft ring-4 ring-white">
        <!-- کارت شناور -->
        <div class="absolute -bottom-5 right-6 flex items-center gap-3 rounded-2xl bg-white/95 backdrop-blur px-4 py-3 shadow-soft ring-1 ring-blush-100">
          <span class="inline-flex w-10 h-10 items-center justify-center rounded-full bg-green-100 text-green-600">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
          </span>
          <div>
            <div class="text-sm font-bold text-gray-800">رزرو تأیید شد</div>
            <div class="text-xs text-gray-500">بدون نیاز به ثبت‌نام</div>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ============ خدمات ============ -->
<section id="services" class="py-14 sm:py-20">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center max-w-2xl mx-auto">
      <span class="text-sm font-bold text-blush-600">خدمات ما</span>
      <h2 class="mt-2 text-3xl md:text-4xl font-bold text-gray-800">مراقبت تخصصی برای شما</h2>
      <p class="mt-4 text-gray-600">مجموعه‌ای کامل از خدمات زیبایی با بالاترین استانداردهای کیفیت و بهداشت.</p>
    </div>

    <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
      <?php $loop_i = 0; $loop_n = count($features); foreach ($features as $s): $loop_i++; $loop_last = $loop_i === $loop_n; ?>
      <div class="group rounded-3xl bg-white p-7 shadow-soft ring-1 ring-blush-50 hover:-translate-y-1.5 hover:ring-blush-200 transition-all">
        <div class="inline-flex w-14 h-14 items-center justify-center rounded-2xl bg-gradient-to-br <?= e($s['color']) ?> text-white shadow-soft">
          <?= $s['icon'] ?>
        </div>
        <h3 class="mt-5 text-lg font-bold text-gray-800"><?= e($s['title']) ?></h3>
        <p class="mt-2 text-sm leading-7 text-gray-600"><?= e($s['desc']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ چرا ما ============ -->
<section id="why" class="py-14 sm:py-20 bg-gradient-to-b from-sand-50 to-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid items-center gap-12 lg:grid-cols-2">
      <div class="relative">
        <img src="/static/img/treatment.png" alt="خدمات زیبایی"
             class="w-full h-[360px] md:h-[460px] object-cover rounded-[2.5rem] shadow-soft ring-4 ring-white">
      </div>
      <div>
        <span class="text-sm font-bold text-blush-600">چرا <?= e($clinic['name']) ?>؟</span>
        <h2 class="mt-2 text-3xl md:text-4xl font-bold text-gray-800">تجربه‌ای متفاوت از زیبایی</h2>
        <ul class="mt-8 space-y-5">
          <?php $loop_i = 0; $loop_n = count($reasons); foreach ($reasons as $item): $loop_i++; $loop_last = $loop_i === $loop_n; ?>
          <li class="flex items-start gap-4">
            <span class="mt-0.5 inline-flex w-10 h-10 shrink-0 items-center justify-center rounded-xl bg-blush-100 text-blush-600">
              <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </span>
            <div>
              <h4 class="font-bold text-gray-800"><?= e($item['title']) ?></h4>
              <p class="text-sm text-gray-600 mt-1"><?= e($item['desc']) ?></p>
            </div>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- ============ CTA پایانی ============ -->
<section class="py-14 sm:py-20">
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="relative overflow-hidden rounded-[2rem] sm:rounded-[2.5rem] bg-gradient-to-l from-blush-600 to-blush-500 px-6 sm:px-8 py-12 sm:py-14 md:py-16 text-center shadow-soft">
      <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full"></div>
      <div class="absolute -bottom-12 -left-8 w-52 h-52 bg-white/10 rounded-full"></div>
      <h2 class="relative text-2xl sm:text-3xl md:text-4xl font-bold text-white">همین حالا نوبت خود را رزرو کنید</h2>
      <p class="relative mt-4 text-blush-50 max-w-xl mx-auto text-sm sm:text-base">بدون نیاز به ثبت‌نام؛ تنها با وارد کردن نام و شماره موبایل، در چند ثانیه نوبت بگیرید.</p>
      <a href="/booking/"
         class="relative mt-7 sm:mt-8 inline-flex items-center gap-2 rounded-full bg-white px-7 sm:px-9 py-3.5 sm:py-4 text-base sm:text-lg font-bold text-blush-700 shadow-soft hover:scale-[1.03] transition">
        رزرو نوبت آنلاین
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7M4 12h16"/></svg>
      </a>
    </div>
  </div>
</section>


