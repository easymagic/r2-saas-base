<section class="rounded-2xl bg-white p-6 shadow-md">
  <div class="flex flex-wrap items-center justify-between gap-4">
    <h2 class="text-base font-semibold text-gray-900">Your cart</h2>
    <?php if (!empty($lines)): ?>
      <div class="flex gap-3">
        <a href="<?= e(web_url('/cart/checkout')) ?>" class="rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-white">Checkout</a>
        <form method="post" action="<?= e(web_url('/cart/clear')) ?>" onsubmit="return confirm('Clear cart?');">
          <button type="submit" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700">Clear</button>
        </form>
      </div>
    <?php endif; ?>
  </div>

  <?php if (empty($lines)): ?>
    <p class="mt-8 text-center text-gray-500">Your cart is empty. <a class="text-orange-600" href="<?= e(web_url('/shop')) ?>">Browse the shop</a></p>
  <?php else: ?>
    <div class="mt-4 overflow-x-auto">
      <table class="w-full min-w-[40rem] border-collapse text-left text-sm">
        <thead>
          <tr class="border-b border-gray-200 bg-gray-100 text-xs font-semibold uppercase text-gray-600">
            <th class="px-4 py-3">Product</th>
            <th class="px-4 py-3">Qty</th>
            <th class="px-4 py-3 text-right">Line total</th>
            <th class="px-4 py-3">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php foreach ($lines as $row): ?>
            <?php $line = $row['line']; $product = $row['product']; ?>
            <tr>
              <td class="px-4 py-3 font-medium"><?= e($product ? $product->name : 'Product #' . $line->product_id) ?></td>
              <td class="px-4 py-3"><?= e($line->qty) ?></td>
              <td class="px-4 py-3 text-right"><?= e(format_naira($line->price_total)) ?></td>
              <td class="px-4 py-3">
                <form method="post" action="<?= e(web_url('/cart/remove')) ?>">
                  <input type="hidden" name="product_id" value="<?= e($line->product_id) ?>" />
                  <button type="submit" class="text-xs font-semibold text-red-600">Remove</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <dl class="mt-6 ml-auto max-w-xs space-y-2 text-sm">
      <div class="flex justify-between"><dt class="text-gray-600">Subtotal</dt><dd><?= e(format_naira($totals['subtotal'])) ?></dd></div>
      <div class="flex justify-between"><dt class="text-gray-600">Shipping</dt><dd><?= e(format_naira($totals['shipping'])) ?></dd></div>
      <div class="flex justify-between"><dt class="text-gray-600">Service charge</dt><dd><?= e(format_naira($totals['service'])) ?></dd></div>
      <div class="flex justify-between border-t border-gray-200 pt-2 font-semibold"><dt>Total</dt><dd><?= e(format_naira($totals['total'])) ?></dd></div>
    </dl>
  <?php endif; ?>
</section>
