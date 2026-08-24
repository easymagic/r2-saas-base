<section class="rounded-2xl bg-white p-6 shadow-md">
  <div class="flex items-center justify-between gap-4">
    <h2 class="text-base font-semibold text-gray-900">Products</h2>
    <a href="<?= e(web_url('/admin/products/create')) ?>" class="rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-white">Create product</a>
  </div>
  <div class="mt-4 overflow-x-auto">
    <table class="w-full min-w-[48rem] border-collapse text-left text-sm">
      <thead>
        <tr class="border-b border-gray-200 bg-gray-100 text-xs font-semibold uppercase text-gray-600">
          <th class="px-4 py-3">ID</th>
          <th class="px-4 py-3">Name</th>
          <th class="px-4 py-3">Category</th>
          <th class="px-4 py-3">Price</th>
          <th class="px-4 py-3">Stock</th>
          <th class="px-4 py-3">Active</th>
          <th class="px-4 py-3">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php if (empty($products)): ?>
          <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">No products yet.</td></tr>
        <?php else: ?>
          <?php foreach ($products as $product): ?>
            <tr>
              <td class="px-4 py-3">#<?= e($product->id) ?></td>
              <td class="px-4 py-3 font-medium"><?= e($product->name) ?></td>
              <td class="px-4 py-3 text-gray-600"><?= e(isset($categoryMap[(int) $product->category_id]) ? $categoryMap[(int) $product->category_id] : '—') ?></td>
              <td class="px-4 py-3"><?= e(format_naira($product->price)) ?></td>
              <td class="px-4 py-3"><?= e($product->stock_qty) ?></td>
              <td class="px-4 py-3"><?= (int) $product->active === 1 ? 'Yes' : 'No' ?></td>
              <td class="px-4 py-3 space-x-3">
                <a class="text-xs font-semibold text-orange-600" href="<?= e(web_url('/admin/products/' . $product->id . '/edit')) ?>">Edit</a>
                <form method="post" action="<?= e(web_url('/admin/products/' . $product->id . '/delete')) ?>" class="inline" onsubmit="return confirm('Delete product?');">
                  <button type="submit" class="text-xs font-semibold text-red-600">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
