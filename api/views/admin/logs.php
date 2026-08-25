<section class="rounded-2xl bg-white p-6 shadow-md">
  <form method="get" action="<?= e(web_url('/admin/logs')) ?>" class="mb-6 flex flex-wrap gap-3">
    <select name="type" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
      <option value="">All types</option>
      <?php foreach (['success', 'error'] as $type): ?>
        <option value="<?= e($type) ?>" <?= ($filters['type'] ?? '') === $type ? 'selected' : '' ?>><?= e($type) ?></option>
      <?php endforeach; ?>
    </select>
    <input type="search" name="search" value="<?= e($filters['search'] ?? '') ?>" placeholder="Search title, payload, response…" class="min-w-[16rem] flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm" />
    <button type="submit" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700">Filter</button>
  </form>

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
              <td class="px-4 py-3"><a class="font-medium text-orange-600" href="<?= e(web_url('/admin/logs/' . $log->id)) ?>">#<?= e($log->id) ?></a></td>
              <td class="px-4 py-3"><span class="rounded-full px-2 py-0.5 text-xs font-medium <?= e(status_badge_class($log->type)) ?>"><?= e($log->type) ?></span></td>
              <td class="px-4 py-3 font-medium"><?= e($log->title) ?></td>
              <td class="px-4 py-3 max-w-md truncate text-gray-600" title="<?= e($log->payload) ?>"><?= e($log->payload) ?></td>
              <td class="px-4 py-3 text-gray-500"><?= e(format_date($log->created_at)) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
