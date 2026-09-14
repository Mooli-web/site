<header x-data="{ open: false, scrolled: false }"
        @scroll.window="scrolled = (window.pageYOffset > 20)"
        :class="scrolled ? 'bg-white/90 shadow-soft backdrop-blur-md' : 'bg-transparent'"
        class="fixed top-0 inset-x-0 z-50 transition-all duration-300">
  <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between h-16 md:h-20">

      <!-- لوگو -->
      <a href="/" class="flex items-center gap-2 group">
        <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-br from-blush-400 to-sand-400 text-white shadow-soft">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21s-7-4.35-9.5-8.5C.9 9.6 2.3 6 5.5 6 7.4 6 8.7 7 12 9c3.3-2 4.6-3 6.5-3 3.2 0 4.6 3.6 3 6.5C19 16.65 12 21 12 21z"/>
          </svg>
        </span>
        <span class="text-lg md:text-xl font-bold text-blush-700 group-hover:text-blush-600 transition"><?= e($clinic['name']) ?></span>
      </a>

      <!-- منوی دسکتاپ -->
      <div class="hidden lg:flex items-center gap-6 text-[15px] font-medium text-gray-700">
        <a href="/" class="hover:text-blush-600 transition">خانه</a>
        <a href="/about/" class="hover:text-blush-600 transition">درباره ما</a>
        <a href="/#services" class="hover:text-blush-600 transition">خدمات</a>
        <a href="/before-after/" class="hover:text-blush-600 transition">گالری</a>
        <a href="/faq/" class="hover:text-blush-600 transition">سوالات متداول</a>
        <a href="/consultation/" class="hover:text-blush-600 transition">مشاوره رایگان</a>
        <a href="/contact/" class="hover:text-blush-600 transition">تماس با ما</a>
      </div>

      <!-- دکمه رزرو + همبرگری -->
      <div class="flex items-center gap-3">
        <a href="/booking/"
           class="hidden sm:inline-flex items-center gap-2 rounded-full bg-gradient-to-l from-blush-500 to-blush-600 px-5 py-2.5 text-sm font-bold text-white shadow-soft hover:from-blush-600 hover:to-blush-700 transition">
          رزرو نوبت
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7M4 12h16"/></svg>
        </a>
        <button @click="open = !open" class="lg:hidden inline-flex items-center justify-center w-10 h-10 rounded-lg text-gray-700 hover:bg-blush-50">
          <svg x-show="!open" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
          <svg x-show="open" x-cloak class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
    </div>

    <!-- منوی موبایل -->
    <div x-show="open" x-cloak x-transition class="lg:hidden pb-4">
      <div class="flex flex-col gap-1 rounded-2xl bg-white shadow-soft p-3 text-gray-700">
        <a @click="open=false" href="/" class="rounded-lg px-4 py-2.5 hover:bg-blush-50">خانه</a>
        <a @click="open=false" href="/about/" class="rounded-lg px-4 py-2.5 hover:bg-blush-50">درباره ما</a>
        <a @click="open=false" href="/#services" class="rounded-lg px-4 py-2.5 hover:bg-blush-50">خدمات</a>
        <a @click="open=false" href="/before-after/" class="rounded-lg px-4 py-2.5 hover:bg-blush-50">گالری قبل و بعد</a>
        <a @click="open=false" href="/faq/" class="rounded-lg px-4 py-2.5 hover:bg-blush-50">سوالات متداول</a>
        <a @click="open=false" href="/consultation/" class="rounded-lg px-4 py-2.5 hover:bg-blush-50">مشاوره رایگان</a>
        <a @click="open=false" href="/contact/" class="rounded-lg px-4 py-2.5 hover:bg-blush-50">تماس با ما</a>
        <a href="/booking/" class="mt-1 text-center rounded-full bg-blush-600 px-4 py-2.5 font-bold text-white">رزرو نوبت</a>
      </div>
    </div>
  </nav>
</header>
