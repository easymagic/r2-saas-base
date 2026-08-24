<div class="grid gap-6 lg:grid-cols-2">
  <section class="rounded-2xl bg-white p-6 shadow-md">
    <h2 class="text-base font-semibold text-gray-900">Create category</h2>
    <form method="post" action="<?= e(web_url('/admin/categories')) ?>" enctype="multipart/form-data" class="mt-4 space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700">Name</label>
        <input name="name" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Parent category</label>
        <select name="parent_id" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
          <option value="0">None (top level)</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= e($cat->id) ?>"><?= e($cat->name) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Description</label>
        <textarea name="description" required rows="3" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Slug (optional)</label>
        <input name="slug" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="auto-generated from name" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Image</label>
        <input type="file" name="image" required accept="image/*" class="mt-1 w-full text-sm" />
      </div>
      <button type="submit" class="rounded-lg bg-blue-900 px-4 py-2 text-sm font-semibold text-white">Create</button>
    </form>
  </section>

  <section class="rounded-2xl bg-white p-6 shadow-md lg:col-span-2">
    <h2 class="text-base font-semibold text-gray-900">Categories</h2>
    <div class="mt-4 overflow-x-auto">
      <table class="w-full min-w-[40rem] border-collapse text-left text-sm">
        <thead>
          <tr class="border-b border-gray-200 bg-gray-100 text-xs font-semibold uppercase text-gray-600">
            <th class="px-4 py-3">ID</th>
            <th class="px-4 py-3">Name</th>
            <th class="px-4 py-3">Slug</th>
            <th class="px-4 py-3">Active</th>
            <th class="px-4 py-3">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php if (empty($categories)): ?>
            <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No categories yet.</td></tr>
          <?php else: ?>
            <?php foreach ($categories as $cat): ?>
              <tr>
                <td class="px-4 py-3">#<?= e($cat->id) ?></td>
                <td class="px-4 py-3 font-medium"><?= e($cat->name) ?></td>
                <td class="px-4 py-3 text-gray-600"><?= e($cat->slug) ?></td>
                <td class="px-4 py-3"><?= (int) $cat->active === 1 ? 'Yes' : 'No' ?></td>
                <td class="px-4 py-3 space-x-3">
                  <a class="text-xs font-semibold text-orange-600" href="<?= e(web_url('/admin/categories/' . $cat->id . '/edit')) ?>">Edit</a>
                  <form method="post" action="<?= e(web_url('/admin/categories/' . $cat->id . '/delete')) ?>" class="inline" onsubmit="return confirm('Delete category?');">
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
</div>
