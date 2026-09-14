<?= view('website/partials/stepper', ['active' => 4, 'oob' => true]) ?>

<div class="anim-up">
  <div class="flex items-center justify-between mb-1">
    <h2 class="text-xl font-bold text-gray-800">۴. اطلاعات خود را وارد کنید</h2>
    <?= view('website/partials/back_button', ['url' => '/booking/slots/', 'qs' => 'service=' . $service['id'] . '&date=' . $date_iso]) ?>
  </div>

  <div class="grid gap-6 md:grid-cols-5">
    <!-- خلاصه‌ی رزرو -->
    <div class="md:col-span-2">
      <div class="rounded-3xl bg-gradient-to-br from-blush-50 to-sand-50 p-5 shadow-soft ring-1 ring-blush-100 sticky top-24">
        <h3 class="font-bold text-gray-800 mb-4">خلاصه‌ی نوبت</h3>
        <dl class="space-y-3 text-sm">
          <div class="flex justify-between gap-2"><dt class="text-gray-500">خدمت</dt><dd class="font-medium text-gray-800 text-left"><?= e($service['title']) ?></dd></div>
          <div class="flex justify-between gap-2"><dt class="text-gray-500">تاریخ</dt><dd class="font-medium text-gray-800 text-left"><?= e($pretty_date) ?></dd></div>
          <div class="flex justify-between gap-2"><dt class="text-gray-500">ساعت</dt><dd class="font-bold text-blush-700 text-left"><?= e($start_fa) ?></dd></div>
          <div class="flex justify-between gap-2"><dt class="text-gray-500">مدت</dt><dd class="font-medium text-gray-800 text-left"><?= e($service['duration_minutes']) ?> دقیقه</dd></div>
          <div class="border-t border-blush-200 pt-3 flex justify-between gap-2"><dt class="text-gray-600 font-bold">مبلغ</dt><dd class="font-bold text-blush-700 text-left"><?= e(money($service['price'])) ?> تومان</dd></div>
        </dl>
      </div>
    </div>

    <!-- فرم -->
    <div class="md:col-span-3">
      <form hx-post="/booking/submit/" hx-target="#booking-body" hx-swap="innerHTML"
            class="rounded-3xl bg-white p-6 shadow-soft ring-1 ring-blush-50 space-y-5">

        <?php if (!empty($errors['_'])): ?>
        <div class="rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
          <p><?= e($errors['_']) ?></p>
        </div>
        <?php endif; ?>

        <input type="hidden" name="service" value="<?= e($service['id']) ?>">
        <input type="hidden" name="date" value="<?= e($date_iso) ?>">
        <input type="hidden" name="start" value="<?= e($start) ?>">

        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">نام <span class="text-red-500">*</span></label>
            <input type="text" name="first_name" required value="<?= e((($form['first_name'] ?? '') ?? '')) ?>"
                   class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-blush-400 focus:ring-2 focus:ring-blush-100 outline-none transition" placeholder="مثلاً: زهرا">
            <?php if (!empty($errors['first_name'])): ?><p class="mt-1 text-xs text-red-600"><?= e($errors['first_name'] ?? '') ?></p><?php endif; ?>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">نام خانوادگی <span class="text-red-500">*</span></label>
            <input type="text" name="last_name" required value="<?= e((($form['last_name'] ?? '') ?? '')) ?>"
                   class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-blush-400 focus:ring-2 focus:ring-blush-100 outline-none transition" placeholder="مثلاً: محمدی">
            <?php if (!empty($errors['last_name'])): ?><p class="mt-1 text-xs text-red-600"><?= e($errors['last_name'] ?? '') ?></p><?php endif; ?>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">شماره موبایل <span class="text-red-500">*</span></label>
          <input type="tel" name="mobile" required inputmode="numeric" dir="ltr" value="<?= e((($form['mobile'] ?? '') ?? '')) ?>"
                 class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-left focus:border-blush-400 focus:ring-2 focus:ring-blush-100 outline-none transition" placeholder="09xxxxxxxxx">
          <?php if (!empty($errors['mobile'])): ?><p class="mt-1 text-xs text-red-600"><?= e($errors['mobile'] ?? '') ?></p><?php endif; ?>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">یادداشت (اختیاری)</label>
          <textarea name="customer_note" rows="2"
                    class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-blush-400 focus:ring-2 focus:ring-blush-100 outline-none transition resize-none" placeholder="توضیحات تکمیلی..."><?= e((($form['customer_note'] ?? '') ?? '')) ?></textarea>
        </div>

        <button type="submit"
                class="w-full inline-flex items-center justify-center gap-2 rounded-full bg-gradient-to-l from-blush-500 to-blush-600 px-6 py-3.5 text-base font-bold text-white shadow-soft hover:from-blush-600 hover:to-blush-700 transition">
          تأیید و ثبت نوبت
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        </button>
        <p class="text-center text-xs text-gray-400">با ثبت نوبت، نام و شماره شما نزد کلینیک ثبت می‌شود.</p>
      </form>
    </div>
  </div>
</div>
