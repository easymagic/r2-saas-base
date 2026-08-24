<section class="max-w-2xl rounded-2xl bg-white p-6 shadow-md">
  <h2 class="text-base font-semibold text-gray-900">Edit category</h2>
  <form method="post" action="<?= e(web_url('/admin/categories/' . $category->id)) ?>" enctype="multipart/form-data" class="mt-4 space-y-4">
    <div>
      <label class="block text-sm font-medium text-gray-700">Name</label>
      <input name="name" required value="<?= e($category->name) ?>" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700">Parent category</label>
      <select name="parent_id" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
        <option value="0">None (top level)</option>
        <?php foreach ($categories as $cat): ?>
          <?php if ((int) $cat->id === (int) $category->id) continue; ?>
          <option value="<?= e($cat->id) ?>" <?= (int) $category->parent_id === (int) $cat->id ? 'selected' : '' ?>><?= e($cat->name) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700">Description</label>
      <textarea name="description" required rows="3" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"><?= e($category->description) ?></textarea>
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700">Slug</label>
      <input name="slug" value="<?= e($category->slug) ?>" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700">Active</label>
      <select name="active" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
        <option value="1" <?= (int) $category->active === 1 ? 'selected' : '' ?>>Yes</option>
        <option value="0" <?= (int) $category->active !== 1 ? 'selected' : '' ?>>No</option>
      </select>
    </div>
    <?php if ($category->image !== ''): ?>
      <div>
        <p class="text-sm text-gray-600">Current image</p>
        <img src="<?= e(upload_url($category->image)) ?>" alt="" class="mt-2 h-24 rounded-lg object-cover" />
      </div>
    <?php endif; ?>
    <div>
      <label class="block text-sm font-medium text-gray-700">Replace image (optional)</label>
      <input type="file" name="image" accept="image/*" class="mt-1 w-full text-sm" />
    </div>
    <div class="flex gap-3">
      <button type="submit" class="rounded-lg bg-blue-900 px-4 py-2 text-sm font-semibold text-white">Save</button>
      <a href="<?= e(web_url('/admin/categories')) ?>" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700">Back</a>
    </div>
  </form>
</section>
