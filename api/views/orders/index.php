<section class="rounded-2xl bg-white p-6 shadow-md">
  <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <h2 class="text-base font-semibold text-gray-900">Your orders</h2>
    <a href="<?= e(web_url('/orders/create')) ?>" class="inline-flex rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-white shadow-md hover:bg-orange-600">Create order</a>
  </div>
  <div class="mt-4 overflow-x-auto">
    <table class="w-full min-w-[40rem] border-collapse text-left text-sm">
      <thead>
        <tr class="border-b border-gray-200 bg-gray-100 text-xs font-semibold uppercase tracking-wide text-gray-600">
          <th class="px-4 py-3">Order</th>
          <th class="px-4 py-3">Status</th>
          <th class="px-4 py-3 text-right">USD</th>
          <th class="px-4 py-3 text-right">NGN</th>
          <th class="px-4 py-3">Date</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php if (empty($orders)): ?>
          <tr><td colspan="5" class="px-4 py-10 text-center text-gray-500">No orders found.</td></tr>
        <?php else: ?>
          <?php foreach ($orders as $order): ?>
            <tr class="hover:bg-gray-50">
              <td class="px-4 py-3 font-medium text-gray-900">
                <a class="hover:text-orange-600" href="<?= e(web_url('/orders/' . $order->id)) ?>">#<?= e($order->id) ?></a>
                <div class="text-xs text-gray-500 truncate max-w-[14rem]"><?= e($order->reference) ?></div>
              </td>
              <td class="px-4 py-3">
                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium <?= e(status_badge_class($order->status)) ?>"><?= e($order->status) ?></span>
              </td>
              <td class="px-4 py-3 text-right tabular-nums"><?= e(format_dollar($order->total_amount_usd)) ?></td>
              <td class="px-4 py-3 text-right tabular-nums"><?= e(format_naira($order->grand_total_naira)) ?></td>
              <td class="px-4 py-3 text-gray-600"><?= e(format_date($order->created_at)) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
