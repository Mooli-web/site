<button type="button"
        hx-get="<?= e($url) ?><?php if (!empty($qs)): ?>?<?= e($qs) ?><?php endif; ?>"
        hx-target="#booking-body" hx-swap="innerHTML"
        class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-blush-600 transition">
  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
  بازگشت
</button>
