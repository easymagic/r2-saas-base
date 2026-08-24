<div class="grid gap-6 lg:grid-cols-2">
  <section class="rounded-2xl bg-white p-6 shadow-md">
    <?php if ($product->image_1 !== ''): ?>
      <img src="<?= e(upload_url($product->image_1)) ?>" alt="" class="w-full rounded-xl object-cover" />
    <?php endif; ?>
    <div class="mt-4 flex flex-wrap gap-2">
      <?php for ($i = 2; $i <= 4; $i++): ?>
        <?php $field = 'image_' . $i; if ($product->$field !== ''): ?>
          <img src="<?= e(upload_url($product->$field)) ?>" alt="" class="h-20 w-20 rounded-lg object-cover" />
        <?php endif; ?>
      <?php endfor; ?>
    </div>
  </section>
  <section class="rounded-2xl bg-white p-6 shadow-md">
    <h2 class="text-2xl font-bold text-gray-900"><?= e($product->name) ?></h2>
    <p class="mt-2 text-2xl font-bold text-orange-600"><?= e(format_naira($product->price)) ?></p>
    <?php if ((float) $product->old_price > (float) $product->price): ?>
      <p class="text-sm text-gray-400 line-through"><?= e(format_naira($product->old_price)) ?></p>
    <?php endif; ?>
    <p class="mt-4 text-sm text-gray-600"><?= e($product->description) ?></p>
    <p class="mt-2 text-sm text-gray-500"><?= e($product->stock_qty) ?> in stock</p>
    <form method="post" action="<?= e(web_url('/cart/add')) ?>" class="mt-6 flex flex-wrap items-end gap-3">
      <input type="hidden" name="product_id" value="<?= e($product->id) ?>" />
      <input type="hidden" name="redirect" value="/shop/products/<?= e($product->id) ?>" />
      <div>
        <label class="block text-sm font-medium text-gray-700">Quantity</label>
        <input type="number" name="qty" value="1" min="1" max="<?= e($product->stock_qty) ?>" class="mt-1 w-24 rounded-lg border border-gray-300 px-3 py-2 text-sm" />
      </div>
      <button type="submit" class="rounded-lg bg-orange-500 px-6 py-2 text-sm font-semibold text-white">Add to cart</button>
      <a href="<?= e(web_url('/cart')) ?>" class="rounded-lg border border-gray-300 px-6 py-2 text-sm font-semibold text-gray-700">View cart</a>
    </form>
  </section>
</div>
