<?php
/* توابع کوچک کمکی برای فرم‌های ادمین (فقط یک‌بار تعریف می‌شوند) */
if (!function_exists('f_input')) {
  function f_input(string $name, string $label, $value = '', string $type = 'text', array $opt = []): string {
    $extra = $opt['attrs'] ?? '';
    $help = isset($opt['help']) ? '<p class="mt-1 text-[11px] text-gray-400">' . e($opt['help']) . '</p>' : '';
    return '<label class="block"><span class="mb-1.5 block text-sm font-medium text-gray-700">' . e($label) . '</span>'
      . '<input type="' . $type . '" name="' . $name . '" value="' . e($value) . '" ' . $extra
      . ' class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-blush-400 focus:ring-blush-200"></label>' . $help;
  }
  function f_textarea(string $name, string $label, $value = '', int $rows = 3): string {
    return '<label class="block"><span class="mb-1.5 block text-sm font-medium text-gray-700">' . e($label) . '</span>'
      . '<textarea name="' . $name . '" rows="' . $rows . '" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-blush-400 focus:ring-blush-200">' . e($value) . '</textarea></label>';
  }
  function f_check(string $name, string $label, $checked = true): string {
    return '<label class="inline-flex items-center gap-2 text-sm text-gray-700"><input type="checkbox" name="' . $name . '" value="1" ' . ($checked ? 'checked' : '') . ' class="rounded border border-gray-300 text-blush-600 focus:ring-blush-200"> ' . e($label) . '</label>';
  }
  function f_submit(string $label = 'ذخیره'): string {
    return '<button type="submit" class="inline-flex items-center gap-2 rounded-full bg-blush-600 px-6 py-2.5 text-sm font-medium text-white shadow-soft hover:bg-blush-700 transition">' . e($label) . '</button>';
  }
  function f_delete(string $action, string $confirm = 'حذف شود؟'): string {
    return '<form method="post" action="' . e($action) . '" class="inline" onsubmit="return confirm(' . json_encode($confirm, JSON_UNESCAPED_UNICODE) . ')">' . Csrf::field()
      . '<button class="text-xs text-red-600 hover:underline">حذف</button></form>';
  }
  function card_open(string $title): string {
    return '<div class="rounded-2xl bg-white p-5 shadow-soft ring-1 ring-blush-50"><h2 class="mb-4 font-bold text-gray-800">' . e($title) . '</h2>';
  }
}
?>
