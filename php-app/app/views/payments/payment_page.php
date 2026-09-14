<?php View::$page['title'] = 'پرداخت نوبت — ' . ((($clinic['name'] ?? '') !== '' ? $clinic['name'] : "کلینیک زیبایی")) . ''; ?>
<section class="relative min-h-screen pt-24 md:pt-28 pb-16">
  <div class="absolute inset-0 -z-10 bg-gradient-to-b from-blush-50 via-white to-sand-50"></div>

  <div class="max-w-lg mx-auto px-4 sm:px-6">

    <div class="text-center mb-8">
      <span class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-blush-400 to-sand-400 text-white shadow-soft">
        <svg class="w-9 h-9" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h2m4 0h4M5 6h14a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2z"/></svg>
      </span>
      <h1 class="mt-5 text-2xl md:text-3xl font-bold text-gray-800">تکمیل پرداخت</h1>
      <p class="mt-2 text-gray-600">برای نهایی شدن رزرو، لطفاً مبلغ نوبت را پرداخت کنید.</p>
    </div>

    <?php if (!empty($error)): ?><div class="mb-5 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700"><?= e($error) ?></div><?php endif; ?>

    <!-- خلاصه نوبت -->
    <div class="rounded-3xl bg-white p-6 shadow-soft ring-1 ring-blush-50">
      <div class="flex items-center justify-between pb-4 border-b border-blush-50">
        <h2 class="font-bold text-gray-800">جزئیات نوبت</h2>
        <span class="rounded-lg bg-gray-50 px-2.5 py-1 text-xs font-bold text-gray-600" dir="ltr"><?= e($appt['tracking_code']) ?></span>
      </div>

      <dl class="space-y-3.5 py-5 text-sm">
        <div class="flex justify-between gap-2"><dt class="text-gray-500">نام</dt><dd class="font-medium text-gray-800 text-left"><?= e($appt['customer']['full_name']) ?></dd></div>
        <div class="flex justify-between gap-2"><dt class="text-gray-500">خدمت</dt><dd class="font-medium text-gray-800 text-left"><?= e($appt['service']['title']) ?></dd></div>
        <div class="flex justify-between gap-2"><dt class="text-gray-500">تاریخ</dt><dd class="font-medium text-gray-800 text-left"><?= e($date_fa) ?></dd></div>
        <div class="flex justify-between gap-2"><dt class="text-gray-500">ساعت</dt><dd class="font-bold text-blush-700 text-left"><?= e($start_fa) ?></dd></div>
      </dl>

      <div class="flex items-center justify-between rounded-2xl bg-gradient-to-l from-blush-50 to-sand-50 px-5 py-4 ring-1 ring-blush-100">
        <span class="font-bold text-gray-700">مبلغ قابل پرداخت</span>
        <span class="text-xl font-bold text-blush-700"><?= e(money($appt['price'])) ?> <span class="text-sm font-medium">تومان</span></span>
      </div>

      <!-- دکمه پرداخت -->
      <form method="post" action="<?= url('/payment/' . $appt['tracking_code'] . '/start/') ?>" class="mt-6">
        <?= Csrf::field() ?>
        <button type="submit"
                class="w-full inline-flex items-center justify-center gap-2 rounded-full bg-gradient-to-l from-blush-500 to-blush-600 px-6 py-4 text-base font-bold text-white shadow-soft hover:from-blush-600 hover:to-blush-700 transition">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M5 6h14a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2z"/></svg>
          پرداخت با زرین‌پال
        </button>
      </form>

      <!-- نشان درگاه امن -->
      <div class="mt-4 flex items-center justify-center gap-2 text-xs text-gray-400">
        <svg class="w-4 h-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
        پرداخت امن از طریق درگاه زرین‌پال<?php if (!empty($sandbox_note)): ?> (آزمایشی)<?php endif; ?>
      </div>
    </div>

    <p class="mt-6 text-center text-xs text-gray-400">
      <a href="/" class="hover:text-blush-600 transition">← انصراف و بازگشت به سایت</a>
    </p>
  </div>
</section>

