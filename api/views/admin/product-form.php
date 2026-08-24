<?php
$isEdit = !empty($product);
$old = isset($old) && is_array($old) ? $old : [];
$value = function ($field, $default = '') use ($product, $old, $isEdit) {
    if (!empty($old[$field])) {
        return $old[$field];
    }
    if ($isEdit && isset($product->$field)) {
        return $product->$field;
    }
    return $default;
};
$action = $isEdit
    ? web_url('/admin/products/' . $product->id)
    : web_url('/admin/products');
?>
<section class="max-w-3xl rounded-2xl bg-white p-6 shadow-md">
  <h2 class="text-base font-semibold text-gray-900"><?= $isEdit ? 'Edit product' : 'Create product' ?></h2>
  <form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" class="mt-4 space-y-4">
    <div class="grid gap-4 sm:grid-cols-2">
      <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-gray-700">Name</label>
        <input name="name" required value="<?= e($value('name')) ?>" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
      </div>
      <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-gray-700">Description</label>
        <textarea name="description" required rows="4" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"><?= e($value('description')) ?></textarea>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Price (₦)</label>
        <input name="price" type="number" step="0.01" min="0" required value="<?= e($value('price')) ?>" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Old price (₦)</label>
        <input name="old_price" type="number" step="0.01" min="0" value="<?= e($value('old_price', '0')) ?>" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Stock qty</label>
        <input name="stock_qty" type="number" min="0" required value="<?= e($value('stock_qty', '0')) ?>" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Slug (optional)</label>
        <input name="slug" value="<?= e($value('slug')) ?>" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Category</label>
        <select name="category_id" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
          <option value="">Select category</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= e($cat->id) ?>" <?= (string) $value('category_id') === (string) $cat->id ? 'selected' : '' ?>><?= e($cat->name) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Merchant (user)</label>
        <select name="user_id" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
          <option value="">Select merchant</option>
          <?php foreach ($users as $u): ?>
            <option value="<?= e($u->id) ?>" <?= (string) $value('user_id') === (string) $u->id ? 'selected' : '' ?>><?= e($u->name) ?> (<?= e($u->email) ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php if ($isEdit): ?>
        <div>
          <label class="block text-sm font-medium text-gray-700">Active</label>
          <select name="active" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="1" <?= (int) $product->active === 1 ? 'selected' : '' ?>>Yes</option>
            <option value="0" <?= (int) $product->active !== 1 ? 'selected' : '' ?>>No</option>
          </select>
        </div>
      <?php endif; ?>
    </div>
    <?php if ($isEdit && $product->image_1 !== ''): ?>
      <div>
        <p class="text-sm text-gray-600">Current primary image</p>
        <img src="<?= e(upload_url($product->image_1)) ?>" alt="" class="mt-2 h-32 rounded-lg object-cover" />
      </div>
    <?php endif; ?>
    <div>
      <label class="block text-sm font-medium text-gray-700">Primary image<?= $isEdit ? ' (optional replace)' : '' ?></label>
      <input type="file" name="image_1" <?= $isEdit ? '' : 'required' ?> accept="image/*" class="mt-1 w-full text-sm" />
    </div>
    <?php for ($i = 2; $i <= 4; $i++): ?>
      <div>
        <label class="block text-sm font-medium text-gray-700">Extra image <?= $i ?> (optional)</label>
        <input type="file" name="image_<?= $i ?>" accept="image/*" class="mt-1 w-full text-sm" />
      </div>
    <?php endfor; ?>
    <div class="flex gap-3">
      <button type="submit" class="rounded-lg bg-blue-900 px-4 py-2 text-sm font-semibold text-white"><?= $isEdit ? 'Save' : 'Create' ?></button>
      <a href="<?= e(web_url('/admin/products')) ?>" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700">Back</a>
    </div>
  </form>
</section>
