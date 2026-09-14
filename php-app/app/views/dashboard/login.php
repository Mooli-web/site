<?php View::$page['title'] = 'ورود به پنل مدیریت'; ?>
<div class="min-h-screen flex items-center justify-center px-4 bg-gradient-to-br from-blush-50 via-white to-sand-50">
  <div class="w-full max-w-sm">
    <!-- لوگو -->
    <div class="text-center mb-8">
      <span class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-blush-400 to-sand-400 text-white shadow-soft">
        <svg class="w-9 h-9" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s-7-4.35-9.5-8.5C.9 9.6 2.3 6 5.5 6 7.4 6 8.7 7 12 9c3.3-2 4.6-3 6.5-3 3.2 0 4.6 3.6 3 6.5C19 16.65 12 21 12 21z"/></svg>
      </span>
      <h1 class="mt-4 text-xl font-bold text-gray-800">پنل مدیریت کلینیک</h1>
      <p class="mt-1 text-sm text-gray-500">برای ادامه وارد حساب خود شوید</p>
    </div>

    <form method="post" class="rounded-3xl bg-white p-7 shadow-soft ring-1 ring-blush-50 space-y-5">
      <?= Csrf::field() ?>
      <?php if (!empty($error)): ?>
      <div class="rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700"><?= e($error) ?></div>
      <?php endif; ?>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">نام کاربری</label>
        <input type="text" name="username" required autofocus
               class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:border-blush-400 focus:ring-2 focus:ring-blush-100 outline-none transition"
               placeholder="نام کاربری">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">رمز عبور</label>
        <input type="password" name="password" required
               class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:border-blush-400 focus:ring-2 focus:ring-blush-100 outline-none transition"
               placeholder="••••••••">
      </div>

      <button type="submit"
              class="w-full rounded-full bg-gradient-to-l from-blush-500 to-blush-600 px-6 py-3 text-sm font-bold text-white shadow-soft hover:from-blush-600 hover:to-blush-700 transition">
        ورود
      </button>
    </form>

    <p class="mt-6 text-center text-xs text-gray-400">
      <a href="/" class="hover:text-blush-600 transition">← بازگشت به سایت</a>
    </p>
  </div>
</div>

