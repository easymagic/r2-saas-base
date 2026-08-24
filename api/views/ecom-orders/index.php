<section class="rounded-2xl bg-white p-6 shadow-md">
  <div class="overflow-x-auto">
    <table class="w-full min-w-[48rem] border-collapse text-left text-sm">
      <thead>
        <tr class="border-b border-gray-200 bg-gray-100 text-xs font-semibold uppercase text-gray-600">
          <th class="px-4 py-3">Order</th>
          <th class="px-4 py-3">Type</th>
          <th class="px-4 py-3">Payment</th>
          <th class="px-4 py-3">Delivery</th>
          <th class="px-4 py-3 text-right">Total</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php if (empty($orders)): ?>
          <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No store orders yet.</td></tr>
        <?php else: ?>
          <?php foreach ($orders as $order): ?>
            <tr class="hover:bg-gray-50">
              <td class="px-4 py-3">
                <a class="font-medium text-orange-600" href="<?= e(web_url('/ecom-orders/' . $order->id)) ?>">#<?= e($order->id) ?></a>
                <p class="text-xs text-gray-500"><?= e($order->reference) ?></p>
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
