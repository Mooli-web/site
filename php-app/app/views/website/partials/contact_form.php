<form id="contact-form"
      hx-post="/contact/submit/"
      hx-target="#contact-form"
      hx-swap="outerHTML"
      class="space-y-5">

  <?php if (!empty($errors['_'])): ?>
  <div class="rounded-xl bg-red-50 ring-1 ring-red-200 px-4 py-3 text-sm text-red-700">
    <?= e($errors['_'] ?? '') ?>
  </div>
  <?php endif; ?>

  <div class="grid sm:grid-cols-2 gap-5">
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1.5">نام و نام خانوادگی</label>
      <input type="text" name="name" value="<?= e((($form['name'] ?? '') ?? '')) ?>"
             class="w-full rounded-xl border-gray-200 bg-blush-50/40 px-4 py-3 text-gray-800 ring-1 ring-blush-100 focus:ring-2 focus:ring-blush-400 focus:bg-white outline-none transition"
             placeholder="مثلاً سارا محمدی">
      <?php if (!empty($errors['name'])): ?><p class="mt-1.5 text-xs text-red-600"><?= e($errors['name'] ?? '') ?></p><?php endif; ?>
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1.5">شماره موبایل</label>
      <input type="text" name="mobile" inputmode="numeric" dir="ltr" value="<?= e((($form['mobile'] ?? '') ?? '')) ?>"
             class="w-full rounded-xl border-gray-200 bg-blush-50/40 px-4 py-3 text-gray-800 ring-1 ring-blush-100 focus:ring-2 focus:ring-blush-400 focus:bg-white outline-none transition text-right"
             placeholder="۰۹۱۲۱۲۳۴۵۶۷">
      <?php if (!empty($errors['mobile'])): ?><p class="mt-1.5 text-xs text-red-600"><?= e($errors['mobile'] ?? '') ?></p><?php endif; ?>
    </div>
  </div>

  <div>
    <label class="block text-sm font-medium text-gray-700 mb-1.5">موضوع <span class="text-gray-400">(اختیاری)</span></label>
    <input type="text" name="subject" value="<?= e((($form['subject'] ?? '') ?? '')) ?>"
           class="w-full rounded-xl border-gray-200 bg-blush-50/40 px-4 py-3 text-gray-800 ring-1 ring-blush-100 focus:ring-2 focus:ring-blush-400 focus:bg-white outline-none transition"
           placeholder="درباره چه چیزی می‌خواهید صحبت کنید؟">
  </div>

  <div>
    <label class="block text-sm font-medium text-gray-700 mb-1.5">پیام شما</label>
    <textarea name="message" rows="4"
              class="w-full rounded-xl border-gray-200 bg-blush-50/40 px-4 py-3 text-gray-800 ring-1 ring-blush-100 focus:ring-2 focus:ring-blush-400 focus:bg-white outline-none transition resize-none"
              placeholder="متن پیام خود را بنویسید..."><?= e((($form['message'] ?? '') ?? '')) ?></textarea>
    <?php if (!empty($errors['message'])): ?><p class="mt-1.5 text-xs text-red-600"><?= e($errors['message'] ?? '') ?></p><?php endif; ?>
  </div>

  <button type="submit"
          class="group inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-l from-blush-500 to-blush-600 px-6 py-3.5 text-base font-bold text-white shadow-soft hover:from-blush-600 hover:to-blush-700 transition">
    <span class="htmx-indicator-hide">ارسال پیام</span>
    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
  </button>
</form>
