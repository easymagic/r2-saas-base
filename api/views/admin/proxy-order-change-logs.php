<section class="rounded-2xl bg-white p-6 shadow-md">
  <form method="get" action="<?= e(web_url('/admin/proxy-order-change-logs')) ?>" class="mb-6 flex flex-wrap gap-3">
    <input type="number" name="snappy_order_id" value="<?= e($filters['snappy_order_id'] ?? '') ?>" placeholder="Order ID" class="w-32 rounded-lg border border-gray-300 px-3 py-2 text-sm" />
    <input type="text" name="field_name" value="<?= e($filters['field_name'] ?? '') ?>" placeholder="Field name" class="w-40 rounded-lg border border-gray-300 px-3 py-2 text-sm" />
    <input type="search" name="search" value="<?= e($filters['search'] ?? '') ?>" placeholder="Search values…" class="min-w-[14rem] flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm" />
    <button type="submit" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700">Filter</button>
  </form>

  <div class="overflow-x-auto">
    <table class="w-full min-w-[56rem] border-collapse text-left text-sm">
      <thead>
        <tr class="border-b border-gray-200 bg-gray-100 text-xs font-semibold uppercase text-gray-600">
          <th class="px-4 py-3">ID</th>
          <th class="px-4 py-3">Order</th>
          <th class="px-4 py-3">Field</th>
          <th class="px-4 py-3">Old value</th>
          <th class="px-4 py-3">New value</th>
          <th class="px-4 py-3">Date</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php if (empty($rows)): ?>
          <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">No change logs.</td></tr>
        <?php else: ?>
          <?php foreach ($rows as $row): ?>
            <tr class="align-top hover:bg-gray-50">
              <td class="px-4 py-3">#<?= e($row->id) ?></td>
              <td class="px-4 py-3">
                <a class="font-medium text-orange-600" href="<?= e(web_url('/orders/' . $row->snappy_order_id)) ?>">#<?= e($row->snappy_order_id) ?></a>
              </td>
              <td class="px-4 py-3 font-medium"><?= e($row->field_name) ?></td>
              <td class="px-4 py-3 max-w-xs truncate text-gray-600" title="<?= e($row->old_value) ?>"><?= e($row->old_value) ?></td>
              <td class="px-4 py-3 max-w-xs truncate text-gray-600" title="<?= e($row->new_value) ?>"><?= e($row->new_value) ?></td>
              <td class="px-4 py-3 text-gray-500"><?= e(format_date($row->created_at)) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
