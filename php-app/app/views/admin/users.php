<?php View::$page['title'] = 'کاربران پنل'; $heading = 'کاربران پنل'; require __DIR__ . '/_shell_top.php'; require __DIR__ . '/_form_bits.php'; ?>
<div class="grid gap-6 lg:grid-cols-3">
  <div class="lg:col-span-2 rounded-2xl bg-white shadow-soft ring-1 ring-blush-50 divide-y divide-gray-50">
    <?php foreach ($items as $u): ?>
    <div class="p-4 flex flex-wrap items-center justify-between gap-3 text-sm" x-data="{ edit: false }">
      <div><span class="font-medium text-gray-800" dir="ltr"><?= e($u['username']) ?></span> <span class="text-xs text-gray-400 mr-2">از <?= created_fa($u['created_at'])['date'] ?></span></div>
      <div class="whitespace-nowrap space-x-3 space-x-reverse">
        <button type="button" @click="edit = !edit" class="text-xs text-blush-700 hover:underline">تغییر رمز</button>
        <?php if ($u['id'] != $auth_user['id']): ?><?= f_delete("/admin/users/{$u['id']}/delete/") ?><?php endif; ?>
      </div>
      <form x-show="edit" x-cloak method="post" action="/admin/users/save/" class="basis-full flex gap-2 items-center mt-2">
        <?= Csrf::field() ?><input type="hidden" name="id" value="<?= $u['id'] ?>"><input type="hidden" name="username" value="<?= e($u['username']) ?>">
        <input type="password" name="password" dir="ltr" placeholder="رمز جدید (حداقل ۸)" class="rounded-xl border border-gray-300 px-3 py-2 text-sm"><button class="rounded-xl bg-blush-600 px-3 py-2 text-xs text-white">ذخیره</button>
      </form>
    </div>
    <?php endforeach; ?>
  </div>
  <form method="post" action="/admin/users/save/" class="rounded-2xl bg-white p-5 shadow-soft ring-1 ring-blush-50 space-y-4 h-fit">
    <?= Csrf::field() ?><input type="hidden" name="id" value="0">
    <h2 class="font-bold text-gray-800">کاربر جدید</h2>
    <?= f_input('username', 'نام کاربری (انگلیسی)', '', 'text', ['attrs' => 'dir="ltr" required']) ?>
    <?= f_input('password', 'رمز عبور', '', 'password', ['attrs' => 'dir="ltr" required']) ?>
    <?= f_submit('افزودن') ?>
  </form>
</div>
<?php require __DIR__ . '/_shell_bottom.php'; ?>
