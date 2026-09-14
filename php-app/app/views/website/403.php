<?php View::$page['title'] = 'درخواست نامعتبر'; ?>
<section class="mx-auto max-w-lg px-4 py-24 text-center">
  <h1 class="text-2xl font-bold text-gray-800">درخواست نامعتبر (۴۰۳)</h1>
  <?php if (!empty($no_cookie)): ?>
  <p class="mt-3 text-sm text-gray-500">مرورگر شما کوکی سایت را ارسال نکرد. اگر سایت را داخل یک قاب (iframe) می‌بینید، آن را در یک تب جداگانه باز کنید یا کوکی‌ها را برای این سایت فعال کنید.</p>
  <?php else: ?>
  <p class="mt-3 text-sm text-gray-500">اعتبار فرم منقضی شده است. لطفاً صفحه را تازه کنید و دوباره تلاش کنید.</p>
  <?php endif; ?>
  <a href="/" class="mt-6 inline-flex rounded-full bg-blush-600 px-6 py-3 text-sm text-white">بازگشت به خانه</a>
</section>
