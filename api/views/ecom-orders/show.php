<div class="grid gap-6 lg:grid-cols-3">
  <section class="rounded-2xl bg-white p-6 shadow-md lg:col-span-2">
    <dl class="grid gap-4 sm:grid-cols-2 text-sm">
      <div><dt class="text-gray-500">Reference</dt><dd class="font-medium"><?= e($order->reference) ?></dd></div>
      <div><dt class="text-gray-500">Type</dt><dd class="font-medium"><?= e(strtoupper($order->type)) ?></dd></div>
      <div><dt class="text-gray-500">Payment</dt><dd><span class="rounded-full px-2 py-0.5 text-xs font-medium <?= e(status_badge_class($order->payment_status)) ?>"><?= e($order->payment_status) ?></span></dd></div>
      <div><dt class="text-gray-500">Delivery</dt><dd><span class="rounded-full px-2 py-0.5 text-xs font-medium <?= e(status_badge_class($order->delivery_status)) ?>"><?= e($order->delivery_status) ?></span></dd></div>
      <div><dt class="text-gray-500">Customer</dt><dd class="font-medium"><?= e($order->customer_name) ?></dd></div>
      <div><dt class="text-gray-500">Email</dt><dd><?= e($order->customer_email) ?></dd></div>
      <div class="sm:col-span-2"><dt class="text-gray-500">Address</dt><dd><?= e($order->customer_address) ?></dd></div>
      <div><dt class="text-gray-500">Total</dt><dd class="text-lg font-bold text-orange-600"><?= e(format_naira($order->total_amount)) ?></dd></div>
      <div><dt class="text-gray-500">Created</dt><dd><?= e(format_date($order->created_at)) ?></dd></div>
    </dl>

    <h3 class="mt-8 text-sm font-semibold text-gray-900">Items</h3>
    <div class="mt-3 overflow-x-auto">
      <table class="w-full border-collapse text-left text-sm">
        <thead>
          <tr class="border-b border-gray-200 text-xs uppercase text-gray-600">
            <th class="py-2">Product ID</th>
            <th class="py-2">Qty</th>
            <th class="py-2 text-right">Amount</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php if (empty($order->items)): ?>
            <tr><td colspan="3" class="py-4 text-gray-500">No line items.</td></tr>
          <?php else: ?>
            <?php foreach ($order->items as $item): ?>
              <tr>
                <td class="py-2">#<?= e($item->product_id) ?></td>
                <td class="py-2"><?= e($item->qty) ?></td>
                <td class="py-2 text-right"><?= e(format_naira($item->total_line_amount)) ?></td>
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
              <th class="py-2">Due date</th>
              <th class="py-2">Amount</th>
              <th class="py-2">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <?php foreach ($order->schedules as $schedule): ?>
              <tr>
                <td class="py-2"><?= e(format_date($schedule->expected_payment_date)) ?></td>
                <td class="py-2"><?= e(format_naira($schedule->installment_amount)) ?></td>
                <td class="py-2"><?= e($schedule->payment_status) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>

  <?php if (!empty($isAdmin)): ?>
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
    </section>
  <?php endif; ?>
</div>
