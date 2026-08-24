<div class="space-y-6">
  <section class="grid gap-4 sm:grid-cols-3">
    <article class="rounded-2xl bg-white p-6 shadow-md">
      <p class="text-sm text-gray-500">Orders</p>
      <p class="mt-2 text-3xl font-semibold text-gray-900"><?= e($order_count) ?></p>
    </article>
    <article class="rounded-2xl bg-white p-6 shadow-md">
      <p class="text-sm text-gray-500">Pending top-ups</p>
      <p class="mt-2 text-3xl font-semibold text-gray-900"><?= e($pending_topup_count) ?></p>
    </article>
    <article class="rounded-2xl bg-white p-6 shadow-md">
      <p class="text-sm text-gray-500">Users</p>
      <p class="mt-2 text-3xl font-semibold text-gray-900"><?= e($user_count) ?></p>
    </article>
  </section>

  <section class="rounded-2xl bg-white p-6 shadow-md">
    <div class="flex items-center justify-between">
      <h2 class="text-base font-semibold text-gray-900">Recent orders</h2>
      <a href="<?= e(web_url('/orders')) ?>" class="text-sm font-medium text-orange-600 hover:text-orange-700">View all</a>
    </div>
    <div class="mt-4 overflow-x-auto">
      <table class="w-full min-w-[36rem] border-collapse text-left text-sm">
        <thead>
          <tr class="border-b border-gray-200 bg-gray-100 text-xs font-semibold uppercase tracking-wide text-gray-600">
            <th class="px-4 py-3">Order</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3 text-right">Amount</th>
            <th class="px-4 py-3">Date</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php if (empty($recent_orders)): ?>
            <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">No orders.</td></tr>
          <?php else: ?>
            <?php foreach ($recent_orders as $order): ?>
              <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 font-medium"><a class="hover:text-orange-600" href="<?= e(web_url('/orders/' . $order->id)) ?>">#<?= e($order->id) ?></a></td>
                <td class="px-4 py-3"><span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium <?= e(status_badge_class($order->status)) ?>"><?= e($order->status) ?></span></td>
                <td class="px-4 py-3 text-right"><?= e(format_dollar($order->total_amount_usd)) ?></td>
                <td class="px-4 py-3 text-gray-600"><?= e(format_date($order->created_at)) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
</div>
