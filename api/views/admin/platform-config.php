<section class="rounded-2xl bg-white p-6 shadow-md space-y-6">
  <div>
    <h2 class="text-base font-semibold text-gray-900">Update setting</h2>
    <form method="post" action="<?= e(web_url('/admin/platform-config')) ?>" class="mt-4 flex flex-wrap items-end gap-3">
      <div>
        <label class="block text-sm font-medium text-gray-700">Key</label>
        <input name="setting_name" required class="mt-1 rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="SERVICE_CHARGE" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Value</label>
        <input name="setting_value" required class="mt-1 rounded-lg border border-gray-300 px-3 py-2 text-sm" />
      </div>
      <button type="submit" class="rounded-lg bg-blue-900 px-4 py-2 text-sm font-semibold text-white">Save</button>
    </form>
  </div>

  <div class="overflow-x-auto">
    <table class="w-full min-w-[40rem] border-collapse text-left text-sm">
      <thead>
        <tr class="border-b border-gray-200 bg-gray-100 text-xs font-semibold uppercase text-gray-600">
          <th class="px-4 py-3">Key</th>
          <th class="px-4 py-3">Value</th>
          <th class="px-4 py-3">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php if (empty($configs)): ?>
          <tr><td colspan="3" class="px-4 py-8 text-center text-gray-500">No settings.</td></tr>
        <?php else: ?>
          <?php foreach ($configs as $cfg): ?>
            <tr>
              <td class="px-4 py-3 font-medium"><?= e($cfg->setting_key) ?></td>
              <td class="px-4 py-3 text-gray-600"><?= e($cfg->setting_value) ?></td>
              <td class="px-4 py-3">
                <form method="post" action="<?= e(web_url('/admin/platform-config/' . $cfg->id . '/delete')) ?>" onsubmit="return confirm('Delete setting?');">
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
