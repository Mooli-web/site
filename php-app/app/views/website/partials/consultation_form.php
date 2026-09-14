<form id="consultation-form"
      hx-post="/consultation/submit/"
      hx-target="#consultation-form"
      hx-swap="outerHTML"
      class="space-y-5">

  <?php if (!empty($errors['_'])): ?>
  <div class="rounded-xl bg-red-50 ring-1 ring-red-200 px-4 py-3 text-sm text-red-700">
    <?= e($errors['_'] ?? '') ?>
  </div>
  <?php endif; ?>

  <div class="grid sm:grid-cols-2 gap-5">
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1.5">نام</label>
      <input type="text" name="first_name" value="<?= e((($form['first_name'] ?? '') ?? '')) ?>"
             class="w-full rounded-xl border-gray-200 bg-blush-50/40 px-4 py-3 text-gray-800 ring-1 ring-blush-100 focus:ring-2 focus:ring-blush-400 focus:bg-white outline-none transition"
             placeholder="نام">
      <?php if (!empty($errors['first_name'])): ?><p class="mt-1.5 text-xs text-red-600"><?= e($errors['first_name'] ?? '') ?></p><?php endif; ?>
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1.5">نام خانوادگی</label>
      <input type="text" name="last_name" value="<?= e((($form['last_name'] ?? '') ?? '')) ?>"
             class="w-full rounded-xl border-gray-200 bg-blush-50/40 px-4 py-3 text-gray-800 ring-1 ring-blush-100 focus:ring-2 focus:ring-blush-400 focus:bg-white outline-none transition"
             placeholder="نام خانوادگی">
      <?php if (!empty($errors['last_name'])): ?><p class="mt-1.5 text-xs text-red-600"><?= e($errors['last_name'] ?? '') ?></p><?php endif; ?>
    </div>
  </div>

  <div>
    <label class="block text-sm font-medium text-gray-700 mb-1.5">شماره موبایل</label>
    <input type="text" name="mobile" inputmode="numeric" dir="ltr" value="<?= e((($form['mobile'] ?? '') ?? '')) ?>"
           class="w-full rounded-xl border-gray-200 bg-blush-50/40 px-4 py-3 text-gray-800 ring-1 ring-blush-100 focus:ring-2 focus:ring-blush-400 focus:bg-white outline-none transition text-right"
           placeholder="۰۹۱۲۱۲۳۴۵۶۷">
    <?php if (!empty($errors['mobile'])): ?><p class="mt-1.5 text-xs text-red-600"><?= e($errors['mobile'] ?? '') ?></p><?php endif; ?>
  </div>

  <div>
    <label class="block text-sm font-medium text-gray-700 mb-1.5">پیام شما <span class="text-gray-400">(اختیاری)</span></label>
    <textarea name="message" rows="3"
              class="w-full rounded-xl border-gray-200 bg-blush-50/40 px-4 py-3 text-gray-800 ring-1 ring-blush-100 focus:ring-2 focus:ring-blush-400 focus:bg-white outline-none transition resize-none"
              placeholder="درباره چه خدمتی می‌خواهید مشاوره بگیرید؟"><?= e((($form['message'] ?? '') ?? '')) ?></textarea>
  </div>

  <button type="submit"
          class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-l from-blush-500 to-blush-600 px-6 py-3.5 text-base font-bold text-white shadow-soft hover:from-blush-600 hover:to-blush-700 transition">
    <span>درخواست مشاوره رایگان</span>
    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h2.28a1 1 0 01.95.68l1.2 3.6a1 1 0 01-.27 1.05l-1.4 1.4a16 16 0 006.27 6.27l1.4-1.4a1 1 0 011.05-.27l3.6 1.2a1 1 0 01.68.95V19a2 2 0 01-2 2A16 16 0 013 5z"/></svg>
  </button>
  <p class="text-center text-xs text-gray-400">کارشناسان ما در اولین فرصت با شما تماس می‌گیرند.</p>
</form>
