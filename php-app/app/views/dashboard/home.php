<?php View::$page['title'] = 'داشبورد — پنل مدیریت'; ?>
<div x-data="{ sidebar: false }" class="min-h-screen lg:flex">

  <!-- ===== سایدبار ===== -->
  <?= view('dashboard/partials/sidebar', ['active' => "home", 'new_consultations_badge' => $stats['consultations_new']]) ?>

  <!-- ===== محتوای اصلی ===== -->
  <div class="flex-1 min-w-0">
    <!-- هدر -->
    <header class="h-16 bg-white border-b border-blush-50 flex items-center justify-between px-4 sm:px-6 sticky top-0 z-20">
      <div class="flex items-center gap-3">
        <button @click="sidebar=true" class="lg:hidden inline-flex w-9 h-9 items-center justify-center rounded-lg text-gray-600 hover:bg-gray-50">
          <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <div>
          <h1 class="text-lg font-bold text-gray-800">مدیریت نوبت‌ها</h1>
          <p class="text-xs text-gray-400"><?= e($today_fa) ?></p>
        </div>
      </div>
    </header>

    <main class="p-4 sm:p-6 space-y-6">
      <!-- ===== کارت‌های آماری ===== -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <?= view('dashboard/partials/stat_card', ['label' => "نوبت‌های امروز", 'value' => $stats['today'], 'color' => "blush", 'icon' => "calendar"]) ?>
        <?= view('dashboard/partials/stat_card', ['label' => "نوبت‌های فردا", 'value' => $stats['tomorrow'], 'color' => "sand", 'icon' => "clock"]) ?>
        <?= view('dashboard/partials/stat_card', ['label' => "هفته جاری", 'value' => $stats['week'], 'color' => "blush", 'icon' => "week"]) ?>
        <?= view('dashboard/partials/stat_card', ['label' => "در انتظار تأیید", 'value' => $stats['pending'], 'color' => "amber", 'icon' => "bell"]) ?>
      </div>
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <?= view('dashboard/partials/stat_card', ['label' => "کل نوبت‌ها", 'value' => $stats['total'], 'color' => "gray", 'icon' => "list"]) ?>
        <?= view('dashboard/partials/stat_card', ['label' => "کل مشتریان", 'value' => $stats['customers'], 'color' => "gray", 'icon' => "users"]) ?>
        <?= view('dashboard/partials/stat_card', ['label' => "درآمد امروز (تومان)", 'value' => $stats['revenue_today'], 'color' => "green", 'icon' => "cash", 'money' => true]) ?>
        <a href="/dashboard/consultations/?status=new" class="block">
          <?= view('dashboard/partials/stat_card', ['label' => "مشاوره‌های جدید", 'value' => $stats['consultations_new'], 'color' => "blush", 'icon' => "bell"]) ?>
        </a>
      </div>

      <!-- ===== فیلترها ===== -->
      <div class="rounded-2xl bg-white p-4 sm:p-5 shadow-soft ring-1 ring-blush-50">
        <form id="filter-form"
              hx-get="/dashboard/"
              hx-target="#appointments-container"
              hx-swap="innerHTML"
              hx-push-url="false"
              class="space-y-4">

          <!-- بازه‌های سریع -->
          <div class="flex flex-wrap gap-2">
            <?php $r = $filters['range']; ?>
            <button type="submit" name="range" value=""
                    class="rounded-full px-4 py-1.5 text-sm font-medium transition <?php if (empty($r)): ?>bg-blush-600 text-white<?php else: ?>bg-gray-50 text-gray-600 hover:bg-gray-100<?php endif; ?>">همه</button>
            <button type="submit" name="range" value="today"
                    class="rounded-full px-4 py-1.5 text-sm font-medium transition <?php if ($r == 'today'): ?>bg-blush-600 text-white<?php else: ?>bg-gray-50 text-gray-600 hover:bg-gray-100<?php endif; ?>">امروز</button>
            <button type="submit" name="range" value="tomorrow"
                    class="rounded-full px-4 py-1.5 text-sm font-medium transition <?php if ($r == 'tomorrow'): ?>bg-blush-600 text-white<?php else: ?>bg-gray-50 text-gray-600 hover:bg-gray-100<?php endif; ?>">فردا</button>
            <button type="submit" name="range" value="week"
                    class="rounded-full px-4 py-1.5 text-sm font-medium transition <?php if ($r == 'week'): ?>bg-blush-600 text-white<?php else: ?>bg-gray-50 text-gray-600 hover:bg-gray-100<?php endif; ?>">هفته جاری</button>
            
          </div>

          <div class="grid sm:grid-cols-3 gap-3">
            <!-- جستجو -->
            <div class="sm:col-span-2 relative">
              <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="M21 21l-4-4"/></svg>
              </span>
              <input type="text" name="q" value="<?= e($filters['q']) ?>"
                     hx-get="/dashboard/" hx-target="#appointments-container" hx-swap="innerHTML"
                     hx-trigger="keyup changed delay:400ms" hx-include="#filter-form"
                     placeholder="جستجو با نام، شماره موبایل یا کد پیگیری..."
                     class="w-full rounded-xl border border-gray-300 pr-10 pl-4 py-2.5 text-sm focus:border-blush-400 focus:ring-2 focus:ring-blush-100 outline-none transition">
            </div>
            <!-- وضعیت -->
            <select name="status"
                    hx-get="/dashboard/" hx-target="#appointments-container" hx-swap="innerHTML" hx-include="#filter-form"
                    class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:border-blush-400 focus:ring-2 focus:ring-blush-100 outline-none transition bg-white">
              <option value="">همه وضعیت‌ها</option>
              <?php $loop_i = 0; $loop_n = count($status_choices); foreach ($status_choices as $val => $label): $loop_i++; $loop_last = $loop_i === $loop_n; ?>
              <option value="<?= e($val) ?>" <?php if ($filters['status'] == $val): ?>selected<?php endif; ?>><?= e($label) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </form>
      </div>

      <!-- ===== جدول نوبت‌ها ===== -->
      <div id="appointments-container">
        <?= view('dashboard/partials/appointments_table', get_defined_vars()) ?>
      </div>
    </main>
  </div>
</div>

