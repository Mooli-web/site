<div id="booking-stepper" class="flex items-center justify-center" <?php if (!empty($oob)): ?>hx-swap-oob="true"<?php endif; ?>>
  <ol class="flex items-center w-full max-w-xl">
    <?php $loop_i = 0; $loop_n = count(str_split("1234")); foreach (str_split("1234") as $i): $loop_i++; $loop_last = $loop_i === $loop_n; ?>
      <?php $num = $loop_i; ?>
      <li class="flex items-center <?php if (empty($loop_last)): ?>flex-1<?php endif; ?>">
        <div class="flex flex-col items-center gap-1.5">
          <span class="flex items-center justify-center w-9 h-9 rounded-full text-sm font-bold transition
            <?php if ($num < $active): ?>bg-blush-600 text-white
            <?php elseif ($num == $active): ?>bg-blush-600 text-white ring-4 ring-blush-100
            <?php else: ?>bg-white text-gray-400 ring-1 ring-gray-200<?php endif; ?>">
            <?php if ($num < $active): ?>
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            <?php else: ?><?= e($num) ?><?php endif; ?>
          </span>
          <span class="text-[11px] font-medium <?php if ($num <= $active): ?>text-blush-700<?php else: ?>text-gray-400<?php endif; ?> hidden sm:block">
            <?php if ($num == 1): ?>خدمت<?php elseif ($num == 2): ?>روز<?php elseif ($num == 3): ?>ساعت<?php else: ?>اطلاعات<?php endif; ?>
          </span>
        </div>
        <?php if (empty($loop_last)): ?>
        <div class="flex-1 h-0.5 mx-2 mb-5 <?php if ($num < $active): ?>bg-blush-500<?php else: ?>bg-gray-200<?php endif; ?>"></div>
        <?php endif; ?>
      </li>
      
    <?php endforeach; ?>
  </ol>
</div>
