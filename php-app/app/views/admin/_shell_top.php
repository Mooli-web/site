<?php /* پوستهٔ مشترک صفحات تنظیمات: $heading, $active, $flash, $flash_err */ ?>
<div x-data="{ sidebar: false }" class="min-h-screen lg:flex">
  <?= view('dashboard/partials/sidebar', ['active' => 'admin']) ?>
  <div class="flex-1 min-w-0">
    <header class="h-16 bg-white border-b border-blush-50 flex items-center justify-between px-4 sm:px-6 sticky top-0 z-20">
      <div class="flex items-center gap-3">
        <button @click="sidebar=true" class="lg:hidden inline-flex w-9 h-9 items-center justify-center rounded-lg text-gray-600 hover:bg-gray-50">
          <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <div>
          <h1 class="text-lg font-bold text-gray-800"><?= e($heading) ?></h1>
          <p class="text-xs text-gray-400">تنظیمات و محتوای سایت</p>
        </div>
      </div>
      <a href="/admin/" class="text-xs text-blush-700 hover:underline">همهٔ بخش‌ها</a>
    </header>
    <main class="p-4 sm:p-6 space-y-6">
      <?php
        $tabs = ['settings' => 'کلینیک', 'categories' => 'دسته‌ها', 'services' => 'خدمات', 'hours' => 'ساعات کاری', 'faqs' => 'سوالات', 'gallery' => 'گالری', 'messages' => 'پیام‌ها', 'customers' => 'مشتریان', 'users' => 'کاربران', 'import' => 'انتقال داده'];
      ?>
      <nav class="flex flex-wrap gap-2 text-xs">
        <?php foreach ($tabs as $k => $lbl): ?>
        <a href="/admin/<?= $k ?>/" class="rounded-full px-3 py-1.5 ring-1 <?= $active === $k ? 'bg-blush-600 text-white ring-blush-600' : 'bg-white text-gray-600 ring-gray-200 hover:bg-blush-50' ?>"><?= $lbl ?></a>
        <?php endforeach; ?>
      </nav>
      <?php if (!empty($flash)): ?>
      <div class="rounded-2xl bg-green-50 px-4 py-3 text-sm text-green-700 ring-1 ring-green-200"><?= e($flash) ?></div>
      <?php endif; ?>
      <?php if (!empty($flash_err)): ?>
      <div class="rounded-2xl bg-red-50 px-4 py-3 text-sm text-red-700 ring-1 ring-red-200"><?= e($flash_err) ?></div>
      <?php endif; ?>
