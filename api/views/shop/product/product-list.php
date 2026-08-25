<?php 
  /**
   * @var array $products
   * @var callable $product_component
   */
  if (empty($products)): ?>
  <section class="rounded-2xl bg-white p-12 text-center shadow-md">
    <p class="text-gray-500">No products available yet. Check back soon.</p>
  </section>
<?php else: ?>
  <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
    <?php foreach ($products as $product): ?>
      <?php 
       $product_component($product);
      ?>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
