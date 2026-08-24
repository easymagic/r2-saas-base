<section class="rounded-2xl bg-white p-6 shadow-md">
  <div class="overflow-x-auto">
    <table class="w-full min-w-[48rem] border-collapse text-left text-sm">
      <thead>
        <tr class="border-b border-gray-200 bg-gray-100 text-xs font-semibold uppercase text-gray-600">
          <th class="px-4 py-3">ID</th>
          <th class="px-4 py-3">Type</th>
          <th class="px-4 py-3">Title</th>
          <th class="px-4 py-3">Payload</th>
          <th class="px-4 py-3">Date</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php if (empty($logs)): ?>
          <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No logs.</td></tr>
        <?php else: ?>
          <?php foreach ($logs as $log): ?>
            <tr class="align-top hover:bg-gray-50">
              <td class="px-4 py-3">#<?= e($log->id) ?></td>
              <td class="px-4 py-3"><?= e($log->type) ?></td>
              <td class="px-4 py-3"><?= e($log->title) ?></td>
              <td class="px-4 py-3 max-w-md truncate text-gray-600" title="<?= e($log->payload) ?>"><?= e($log->payload) ?></td>
              <td class="px-4 py-3 text-gray-500"><?= e(format_date($log->created_at)) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
