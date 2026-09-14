<?php View::$page['title'] = 'راه‌اندازی اولیه'; ?>
<div class="min-h-screen flex items-center justify-center p-4">
  <div class="w-full max-w-md rounded-3xl bg-white p-8 shadow-soft ring-1 ring-blush-50">
    <div class="text-center mb-6">
      <span class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-gradient-to-br from-blush-400 to-sand-400 text-white mb-3">
        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s-7-4.35-9.5-8.5C.9 9.6 2.3 6 5.5 6 7.4 6 8.7 7 12 9c3.3-2 4.6-3 6.5-3 3.2 0 4.6 3.6 3 6.5C19 16.65 12 21 12 21z"/></svg>
      </span>
      <h1 class="text-xl font-bold text-gray-800">خوش آمدید!</h1>
      <p class="text-sm text-gray-500 mt-1">برای شروع، حساب مدیر را بسازید.</p>
    </div>
    <?php if (!empty($error)): ?>
    <div class="mb-4 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700 ring-1 ring-red-200"><?= e($error) ?></div>
    <?php endif; ?>
    <form method="post" class="space-y-4">
      <?= Csrf::field() ?>
      <label class="block"><span class="mb-1.5 block text-sm font-medium text-gray-700">نام کلینیک</span>
        <input type="text" name="clinic_name" value="کلینیک زیبایی" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:border-blush-400 focus:ring-blush-200"></label>
      <label class="block"><span class="mb-1.5 block text-sm font-medium text-gray-700">نام کاربری مدیر</span>
        <input type="text" name="username" dir="ltr" required class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:border-blush-400 focus:ring-blush-200"></label>
      <label class="block"><span class="mb-1.5 block text-sm font-medium text-gray-700">رمز عبور (حداقل ۸ کاراکتر)</span>
        <input type="password" name="password" dir="ltr" required class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:border-blush-400 focus:ring-blush-200"></label>
      <label class="inline-flex items-center gap-2 text-sm text-gray-700"><input type="checkbox" name="seed" value="1" checked class="rounded border border-gray-300 text-blush-600"> داده‌های نمونه (خدمات، ساعات کاری، سوالات) اضافه شود</label>
      <button class="w-full rounded-full bg-blush-600 py-3 text-sm font-medium text-white shadow-soft hover:bg-blush-700 transition">ساخت حساب و ورود</button>
    </form>
  </div>
</div>
