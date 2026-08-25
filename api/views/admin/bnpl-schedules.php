<section class="rounded-2xl bg-white p-6 shadow-md">
  <form method="get" action="<?= e(web_url('/admin/bnpl-schedules')) ?>" class="mb-6 flex flex-wrap gap-3">
    <input type="number" name="order_id" value="<?= e($filters['order_id'] ?? '') ?>" placeholder="Ecom order ID" class="w-36 rounded-lg border border-gray-300 px-3 py-2 text-sm" />
    <select name="payment_status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
      <option value="">All statuses</option>
      <?php foreach (['pending', 'paid', 'failed'] as $status): ?>
        <option value="<?= e($status) ?>" <?= ($filters['payment_status'] ?? '') === $status ? 'selected' : '' ?>><?= e($status) ?></option>
      <?php endforeach; ?>
    </select>
    <input type="text" name="reference" value="<?= e($filters['reference'] ?? '') ?>" placeholder="Reference" class="min-w-[12rem] flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm" />
    <button type="submit" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700">Filter</button>
  </form>

  <div class="overflow-x-auto">
    <table class="w-full min-w-[56rem] border-collapse text-left text-sm">
      <thead>
        <tr class="border-b border-gray-200 bg-gray-100 text-xs font-semibold uppercase text-gray-600">
          <th class="px-4 py-3">ID</th>
          <th class="px-4 py-3">Order</th>
          <th class="px-4 py-3">Due</th>
          <th class="px-4 py-3 text-right">Amount</th>
          <th class="px-4 py-3">Status</th>
          <th class="px-4 py-3">Attempts</th>
          <th class="px-4 py-3">Reference</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php if (empty($schedules)): ?>
          <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">No BNPL schedules.</td></tr>
        <?php else: ?>
          <?php foreach ($schedules as $schedule): ?>
            <tr class="hover:bg-gray-50">
              <td class="px-4 py-3">#<?= e($schedule->id) ?></td>
              <td class="px-4 py-3">
                <a class="font-medium text-orange-600" href="<?= e(web_url('/admin/ecom-orders/' . $schedule->order_id)) ?>">#<?= e($schedule->order_id) ?></a>
              </td>
              <td class="px-4 py-3"><?= e(format_date($schedule->expected_payment_date)) ?></td>
              <td class="px-4 py-3 text-right"><?= e(format_naira($schedule->installment_amount)) ?></td>
              <td class="px-4 py-3"><span class="rounded-full px-2 py-0.5 text-xs font-medium <?= e(status_badge_class($schedule->payment_status)) ?>"><?= e($schedule->payment_status) ?></span></td>
              <td class="px-4 py-3"><?= e($schedule->number_of_attempts) ?></td>
              <td class="px-4 py-3 text-gray-600"><?= e($schedule->reference) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
