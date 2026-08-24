<div class="grid gap-6 lg:grid-cols-2">
  <section class="rounded-2xl bg-white p-6 shadow-md">
    <h2 class="text-base font-semibold text-gray-900">Order summary</h2>
    <ul class="mt-4 divide-y divide-gray-100 text-sm">
      <?php foreach ($lines as $row): ?>
        <?php $line = $row['line']; $product = $row['product']; ?>
        <li class="flex justify-between py-3">
          <span><?= e($product ? $product->name : 'Product #' . $line->product_id) ?> × <?= e($line->qty) ?></span>
          <span><?= e(format_naira($line->price_total)) ?></span>
        </li>
      <?php endforeach; ?>
    </ul>
    <dl class="mt-4 space-y-2 border-t border-gray-200 pt-4 text-sm">
      <div class="flex justify-between"><dt class="text-gray-600">Subtotal</dt><dd><?= e(format_naira($totals['subtotal'])) ?></dd></div>
      <div class="flex justify-between"><dt class="text-gray-600">Shipping</dt><dd><?= e(format_naira($totals['shipping'])) ?></dd></div>
      <div class="flex justify-between"><dt class="text-gray-600">Service charge</dt><dd><?= e(format_naira($totals['service'])) ?></dd></div>
      <div class="flex justify-between font-semibold"><dt>Total</dt><dd><?= e(format_naira($totals['total'])) ?></dd></div>
    </dl>
  </section>

  <section class="rounded-2xl bg-white p-6 shadow-md">
    <h2 class="text-base font-semibold text-gray-900">Checkout details</h2>
    <form method="post" action="<?= e(web_url('/cart/checkout')) ?>" class="mt-4 space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700">Full name</label>
        <input name="customer_name" required value="<?= e($old['customer_name']) ?>" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Email</label>
        <input name="customer_email" type="email" required value="<?= e($old['customer_email']) ?>" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Delivery address</label>
        <textarea name="customer_address" required rows="3" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"><?= e($old['customer_address']) ?></textarea>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Payment method</label>
        <select name="type" id="checkout-type" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
          <option value="wallet" <?= $old['type'] === 'wallet' ? 'selected' : '' ?>>Wallet</option>
          <option value="card" <?= $old['type'] === 'card' ? 'selected' : '' ?>>Card (Paystack)</option>
          <option value="bnpl" <?= $old['type'] === 'bnpl' ? 'selected' : '' ?>>Buy now, pay later</option>
        </select>
      </div>
      <div id="installments-wrap">
        <label class="block text-sm font-medium text-gray-700">Number of installments (BNPL)</label>
        <input name="number_of_installment" type="number" min="2" max="12" value="<?= e($old['number_of_installment']) ?>" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
      </div>
      <button type="submit" class="w-full rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-white">Place order</button>
    </form>
  </section>
</div>
<script>
  (function () {
    var type = document.getElementById('checkout-type');
    var wrap = document.getElementById('installments-wrap');
    function sync() { wrap.style.display = type.value === 'bnpl' ? 'block' : 'none'; }
    type.addEventListener('change', sync);
    sync();
  })();
</script>
