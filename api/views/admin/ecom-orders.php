<section class="rounded-2xl bg-white p-6 shadow-md">
  <form method="get" action="<?= e(web_url('/admin/ecom-orders')) ?>" class="mb-6 flex flex-wrap gap-3">
    <select name="payment_status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
      <option value="">All payment statuses</option>
      <?php foreach (['pending', 'paid', 'part-paid', 'failed'] as $s): ?>
        <option value="<?= e($s) ?>" <?= ($filters['payment_status'] ?? '') === $s ? 'selected' : '' ?>><?= e($s) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="delivery_status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
      <option value="">All delivery statuses</option>
      <?php foreach (['pending', 'picked-up', 'on-the-way', 'delivered'] as $s): ?>
        <option value="<?= e($s) ?>" <?= ($filters['delivery_status'] ?? '') === $s ? 'selected' : '' ?>><?= e($s) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700">Filter</button>
  </form>

  <div class="overflow-x-auto">
    <table class="w-full min-w-[56rem] border-collapse text-left text-sm">
      <thead>
        <tr class="border-b border-gray-200 bg-gray-100 text-xs font-semibold uppercase text-gray-600">
          <th class="px-4 py-3">Order</th>
          <th class="px-4 py-3">Customer</th>
          <th class="px-4 py-3">Type</th>
          <th class="px-4 py-3">Payment</th>
          <th class="px-4 py-3">Delivery</th>
          <th class="px-4 py-3 text-right">Total</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php if (empty($orders)): ?>
          <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">No ecom orders.</td></tr>
        <?php else: ?>
          <?php foreach ($orders as $order): ?>
            <tr class="hover:bg-gray-50">
              <td class="px-4 py-3">
                <a class="font-medium text-orange-600" href="<?= e(web_url('/admin/ecom-orders/' . $order->id)) ?>">#<?= e($order->id) ?></a>
                <p class="text-xs text-gray-500"><?= e($order->reference) ?></p>
              </td>
              <td class="px-4 py-3">
                <p class="font-medium"><?= e($order->customer_name) ?></p>
                <p class="text-xs text-gray-500"><?= e($order->customer_email) ?></p>
              </td>
              <td class="px-4 py-3"><?= e(strtoupper($order->type)) ?></td>
              <td class="px-4 py-3"><span class="rounded-full px-2 py-0.5 text-xs font-medium <?= e(status_badge_class($order->payment_status)) ?>"><?= e($order->payment_status) ?></span></td>
              <td class="px-4 py-3"><span class="rounded-full px-2 py-0.5 text-xs font-medium <?= e(status_badge_class($order->delivery_status)) ?>"><?= e($order->delivery_status) ?></span></td>
              <td class="px-4 py-3 text-right"><?= e(format_naira($order->total_amount)) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
