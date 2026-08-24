<div class="grid gap-6 lg:grid-cols-3">
  <section class="rounded-2xl bg-white p-6 shadow-md lg:col-span-2">
    <dl class="grid gap-4 sm:grid-cols-2 text-sm">
      <div><dt class="text-gray-500">Reference</dt><dd class="font-medium"><?= e($order->reference) ?></dd></div>
      <div><dt class="text-gray-500">User ID</dt><dd><?= e($order->user_id) ?></dd></div>
      <div><dt class="text-gray-500">Type</dt><dd><?= e(strtoupper($order->type)) ?></dd></div>
      <div><dt class="text-gray-500">Agent ID</dt><dd><?= e($order->agent_id ?: '—') ?></dd></div>
      <div><dt class="text-gray-500">Payment</dt><dd><span class="rounded-full px-2 py-0.5 text-xs font-medium <?= e(status_badge_class($order->payment_status)) ?>"><?= e($order->payment_status) ?></span></dd></div>
      <div><dt class="text-gray-500">Delivery</dt><dd><span class="rounded-full px-2 py-0.5 text-xs font-medium <?= e(status_badge_class($order->delivery_status)) ?>"><?= e($order->delivery_status) ?></span></dd></div>
      <div><dt class="text-gray-500">Customer</dt><dd><?= e($order->customer_name) ?></dd></div>
      <div><dt class="text-gray-500">Email</dt><dd><?= e($order->customer_email) ?></dd></div>
      <div class="sm:col-span-2"><dt class="text-gray-500">Address</dt><dd><?= e($order->customer_address) ?></dd></div>
      <div><dt class="text-gray-500">Total</dt><dd class="font-bold text-orange-600"><?= e(format_naira($order->total_amount)) ?></dd></div>
    </dl>

    <h3 class="mt-8 text-sm font-semibold text-gray-900">Order items</h3>
    <div class="mt-3 overflow-x-auto">
      <table class="w-full border-collapse text-left text-sm">
        <thead>
          <tr class="border-b border-gray-200 text-xs uppercase text-gray-600">
            <th class="px-2 py-2">ID</th>
            <th class="px-2 py-2">Product</th>
            <th class="px-2 py-2">Merchant</th>
            <th class="px-2 py-2">Qty</th>
            <th class="px-2 py-2 text-right">Amount</th>
            <th class="px-2 py-2">Settled</th>
            <th class="px-2 py-2">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php if (empty($items)): ?>
            <tr><td colspan="7" class="py-4 text-gray-500">No items.</td></tr>
          <?php else: ?>
            <?php foreach ($items as $item): ?>
              <tr>
                <td class="px-2 py-2">#<?= e($item->id) ?></td>
                <td class="px-2 py-2">#<?= e($item->product_id) ?></td>
                <td class="px-2 py-2">#<?= e($item->merchant_id) ?></td>
                <td class="px-2 py-2"><?= e($item->qty) ?></td>
                <td class="px-2 py-2 text-right"><?= e(format_naira($item->total_line_amount)) ?></td>
                <td class="px-2 py-2"><?= (int) $item->settled === 1 ? 'Yes' : 'No' ?></td>
                <td class="px-2 py-2">
                  <?php if ((int) $item->settled !== 1): ?>
                    <form method="post" action="<?= e(web_url('/admin/ecom-orders/' . $order->id . '/items/' . $item->id . '/settle')) ?>">
                      <button type="submit" class="text-xs font-semibold text-green-700">Settle</button>
                    </form>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <?php if (!empty($order->schedules)): ?>
      <h3 class="mt-8 text-sm font-semibold text-gray-900">BNPL schedule</h3>
      <div class="mt-3 overflow-x-auto">
        <table class="w-full border-collapse text-left text-sm">
          <thead>
            <tr class="border-b border-gray-200 text-xs uppercase text-gray-600">
              <th class="py-2">Due</th>
              <th class="py-2">Amount</th>
              <th class="py-2">Status</th>
              <th class="py-2">Attempts</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <?php foreach ($order->schedules as $schedule): ?>
              <tr>
                <td class="py-2"><?= e(format_date($schedule->expected_payment_date)) ?></td>
                <td class="py-2"><?= e(format_naira($schedule->installment_amount)) ?></td>
                <td class="py-2"><?= e($schedule->payment_status) ?></td>
                <td class="py-2"><?= e($schedule->number_of_attempts) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>

  <section class="space-y-6">
    <div class="rounded-2xl bg-white p-6 shadow-md">
      <h3 class="text-sm font-semibold text-gray-900">Assign agent</h3>
      <form method="post" action="<?= e(web_url('/admin/ecom-orders/' . $order->id . '/assign-agent')) ?>" class="mt-3 space-y-3">
        <select name="agent_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
          <option value="">Select agent</option>
          <?php foreach ($agents as $agent): ?>
            <option value="<?= e($agent->id) ?>" <?= (int) $order->agent_id === (int) $agent->id ? 'selected' : '' ?>><?= e($agent->name) ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="w-full rounded-lg bg-blue-900 px-3 py-2 text-sm font-semibold text-white">Assign</button>
      </form>
    </div>
    <div class="rounded-2xl bg-white p-6 shadow-md">
      <h3 class="text-sm font-semibold text-gray-900">Delivery status</h3>
      <form method="post" action="<?= e(web_url('/admin/ecom-orders/' . $order->id . '/delivery')) ?>" class="mt-3 space-y-3">
        <select name="delivery_status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
          <?php foreach (['pending', 'picked-up', 'on-the-way', 'delivered'] as $status): ?>
            <option value="<?= e($status) ?>" <?= $order->delivery_status === $status ? 'selected' : '' ?>><?= e($status) ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="w-full rounded-lg bg-blue-900 px-3 py-2 text-sm font-semibold text-white">Update</button>
      </form>
    </div>
    <a href="<?= e(web_url('/admin/ecom-orders')) ?>" class="block text-center text-sm font-semibold text-orange-600">← Back to list</a>
  </section>
</div>
