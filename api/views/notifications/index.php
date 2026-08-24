<section class="rounded-2xl bg-white p-6 shadow-md">
  <div class="flex items-center justify-between gap-4">
    <h2 class="text-base font-semibold text-gray-900">Notifications</h2>
  </div>
  <ul class="mt-4 divide-y divide-gray-100">
    <?php if (empty($notifications)): ?>
      <li class="py-8 text-center text-sm text-gray-500">No notifications.</li>
    <?php else: ?>
      <?php foreach ($notifications as $n): ?>
        <li class="flex flex-col gap-3 py-4 sm:flex-row sm:items-start sm:justify-between">
          <div>
            <p class="font-medium text-gray-900"><?= e($n->title) ?></p>
            <p class="mt-1 text-sm text-gray-600"><?= e($n->message) ?></p>
            <p class="mt-2 text-xs text-gray-400"><?= e(format_date($n->created_at)) ?></p>
          </div>
          <div class="flex flex-wrap gap-2">
            <form method="post" action="<?= e(web_url('/notifications/' . $n->id . '/read')) ?>"><button type="submit" class="text-xs font-semibold text-green-700">Mark read</button></form>
            <form method="post" action="<?= e(web_url('/notifications/' . $n->id . '/delete')) ?>"><button type="submit" class="text-xs font-semibold text-red-700">Delete</button></form>
          </div>
        </li>
      <?php endforeach; ?>
    <?php endif; ?>
  </ul>
</section>
