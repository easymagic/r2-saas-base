<div class="grid gap-6 lg:grid-cols-2">
  <section class="rounded-2xl bg-white p-6 shadow-md">
    <h2 class="text-base font-semibold text-gray-900">Create batch</h2>
    <form method="post" action="<?= e(web_url('/admin/batches')) ?>" class="mt-4 space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700">Name</label>
        <input name="name" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Description</label>
        <textarea name="description" rows="3" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
      </div>
      <button type="submit" class="rounded-lg bg-blue-900 px-4 py-2 text-sm font-semibold text-white">Create</button>
    </form>
  </section>

  <section class="rounded-2xl bg-white p-6 shadow-md lg:col-span-2">
    <h2 class="text-base font-semibold text-gray-900">Batches</h2>
    <div class="mt-4 overflow-x-auto">
      <table class="w-full min-w-[32rem] border-collapse text-left text-sm">
        <thead>
          <tr class="border-b border-gray-200 bg-gray-100 text-xs font-semibold uppercase text-gray-600">
            <th class="px-4 py-3">ID</th>
            <th class="px-4 py-3">Name</th>
            <th class="px-4 py-3">Description</th>
            <th class="px-4 py-3">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php if (empty($batches)): ?>
            <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">No batches.</td></tr>
          <?php else: ?>
            <?php foreach ($batches as $batch): ?>
              <tr>
                <td class="px-4 py-3">#<?= e($batch->id) ?></td>
                <td class="px-4 py-3 font-medium"><?= e($batch->name) ?></td>
                <td class="px-4 py-3 text-gray-600"><?= e($batch->description) ?></td>
                <td class="px-4 py-3">
                  <form method="post" action="<?= e(web_url('/admin/batches/' . $batch->id . '/delete')) ?>" onsubmit="return confirm('Delete batch?');">
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
