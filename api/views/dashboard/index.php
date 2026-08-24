<div class="space-y-6">
  <section class="rounded-2xl bg-blue-900 p-6 text-white shadow-lg sm:p-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <p class="text-sm font-medium text-blue-200">Wallet balance</p>
        <p class="mt-2 text-3xl font-semibold tracking-tight sm:text-4xl"><?= e(format_naira($balance)) ?></p>
        <p class="mt-2 text-sm text-blue-200">Available for new orders</p>
      </div>
      <div class="flex flex-wrap gap-3">
        <a href="<?= e(web_url('/orders/create')) ?>" class="inline-flex items-center justify-center rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-white shadow-md hover:bg-orange-600">Create order</a>
        <a href="<?= e(web_url('/wallet')) ?>" class="inline-flex items-center justify-center rounded-lg border border-white/30 bg-white/10 px-4 py-2 text-sm font-semibold text-white hover:bg-white/20">Top-up wallet</a>
      </div>
    </div>
  </section>

  <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    <article class="rounded-2xl bg-white p-6 shadow-md">
      <h2 class="text-sm font-semibold text-gray-900">Request a product</h2>
      <p class="mt-2 text-sm text-gray-600">Paste a product link, add notes, and upload screenshots.</p>
      <a href="<?= e(web_url('/orders/create')) ?>" class="mt-4 inline-block text-sm font-semibold text-orange-600 hover:text-orange-700">Start order →</a>
    </article>
    <article class="rounded-2xl bg-white p-6 shadow-md">
      <h2 class="text-sm font-semibold text-gray-900">Track fulfillment</h2>
      <p class="mt-2 text-sm text-gray-600">Statuses update as your request is processed.</p>
      <a href="<?= e(web_url('/orders')) ?>" class="mt-4 inline-block text-sm font-semibold text-orange-600 hover:text-orange-700">View orders →</a>
    </article>
    <article class="rounded-2xl bg-white p-6 shadow-md">
      <h2 class="text-sm font-semibold text-gray-900">Wallet</h2>
      <p class="mt-2 text-sm text-gray-600">Fund your balance with a manual top-up request.</p>
      <a href="<?= e(web_url('/wallet')) ?>" class="mt-4 inline-block text-sm font-semibold text-orange-600 hover:text-orange-700">Open wallet →</a>
    </article>
  </section>

  <section class="rounded-2xl bg-white p-6 shadow-md">
    <div class="flex items-center justify-between gap-4">
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
          <?php if (empty($orders)): ?>
            <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">No orders yet.</td></tr>
          <?php else: ?>
            <?php foreach ($orders as $order): ?>
              <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 font-medium text-gray-900">
                  <a class="hover:text-orange-600" href="<?= e(web_url('/orders/' . $order->id)) ?>">#<?= e($order->id) ?></a>
                </td>
                <td class="px-4 py-3">
                  <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium <?= e(status_badge_class($order->status)) ?>"><?= e($order->status) ?></span>
                </td>
                <td class="px-4 py-3 text-right tabular-nums text-gray-900"><?= e(format_dollar($order->total_amount_usd)) ?></td>
                <td class="px-4 py-3 text-gray-600"><?= e(format_date($order->created_at)) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
</div>
