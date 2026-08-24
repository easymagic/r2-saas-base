<header class="sticky top-0 z-30 flex h-16 items-center justify-between gap-4 border-b border-gray-200 bg-white/95 px-4 backdrop-blur sm:px-6 lg:px-8">
  <div class="flex items-center gap-3">
    <label for="mobile-nav" class="inline-flex cursor-pointer items-center justify-center rounded-lg border border-gray-200 bg-white p-2 text-gray-700 hover:bg-gray-50 lg:hidden">
      <span class="sr-only">Open menu</span>
      <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
    </label>
    <div>
      <h1 class="text-lg font-semibold text-gray-900"><?= e($title) ?></h1>
      <?php if (!empty($subtitle)): ?>
        <p class="hidden text-sm text-gray-500 sm:block"><?= e($subtitle) ?></p>
      <?php endif; ?>
    </div>
  </div>
  <div class="flex items-center gap-2 sm:gap-3">
    <?php if ($balance !== null): ?>
      <span class="hidden rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600 sm:inline">Balance: <?= e(format_naira($balance)) ?></span>
    <?php endif; ?>
    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-900 text-xs font-semibold text-white" title="<?= e(isset($user->name) ? $user->name : '') ?>">
      <?= e(user_initials(isset($user->name) ? $user->name : '')) ?>
    </span>
  </div>
</header>
