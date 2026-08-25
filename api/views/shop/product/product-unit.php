<?php 
 use Product\Data\ProductEntity;
  /**
   * @ignore
   * @var ProductEntity $product
   */
?>
<article class="overflow-hidden rounded-2xl bg-white shadow-md">
        <a href="<?= e(web_url('/shop/products/' . $product->id)) ?>">
          <?php if ($product->image_1 !== ''): ?>
            <img src="<?= e(upload_url('api/' .  $product->image_1)) ?>" alt="" class="h-48 w-full object-cover" />
          <?php else: ?>
            <div class="flex h-48 items-center justify-center bg-gray-100 text-sm text-gray-400">No image</div>
          <?php endif; ?>
        </a>
        <div class="p-4">
          <h3 class="font-semibold text-gray-900"><a href="<?= e(web_url('/shop/products/' . $product->id)) ?>"><?= e($product->name) ?></a></h3>
          <p class="mt-1 text-lg font-bold text-orange-600"><?= e(format_naira($product->price)) ?></p>
          <?php if ((float) $product->old_price > (float) $product->price): ?>
            <p class="text-sm text-gray-400 line-through"><?= e(format_naira($product->old_price)) ?></p>
          <?php endif; ?>
          <form method="post" action="<?= e(web_url('/cart/add')) ?>" class="mt-4 flex gap-2">
            <input type="hidden" name="product_id" value="<?= e($product->id) ?>" />
            <input type="hidden" name="redirect" value="/shop" />
            <input type="number" name="qty" value="1" min="1" max="<?= e($product->stock_qty) ?>" class="w-16 rounded-lg border border-gray-300 px-2 py-1 text-sm" />
            <button type="submit" class="flex-1 rounded-lg bg-orange-500 px-3 py-2 text-sm font-semibold text-white">Add to cart</button>
          </form>
        </div>
</article>
