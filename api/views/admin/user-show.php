<section class="mx-auto max-w-2xl rounded-2xl bg-white p-6 shadow-md">
  <form method="post" action="<?= e(web_url('/admin/users/' . $target->id)) ?>" class="space-y-4">
    <div>
      <label class="block text-sm font-medium text-gray-700">Name</label>
      <input name="name" required value="<?= e($target->name) ?>" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700">Email</label>
      <input value="<?= e($target->email) ?>" disabled class="mt-1 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm" />
    </div>
    <div class="grid gap-4 sm:grid-cols-2">
      <div>
        <label class="block text-sm font-medium text-gray-700">Phone</label>
        <input name="phone" value="<?= e($target->phone) ?>" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Country code</label>
        <input name="country_code" value="<?= e($target->country_code) ?>" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
      </div>
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700">Delivery address</label>
      <textarea name="delivery_address" rows="2" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"><?= e($target->delivery_address) ?></textarea>
    </div>
    <div class="grid gap-4 sm:grid-cols-2">
      <div>
        <label class="block text-sm font-medium text-gray-700">Role</label>
        <select name="role" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
          <?php foreach (['customer', 'agent', 'staff', 'admin', 'super-admin'] as $role): ?>
            <option value="<?= e($role) ?>" <?= $target->role === $role ? 'selected' : '' ?>><?= e($role) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Status</label>
        <select name="status" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
          <?php foreach (['active', 'inactive'] as $status): ?>
            <option value="<?= e($status) ?>" <?= $target->status === $status ? 'selected' : '' ?>><?= e($status) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="flex flex-wrap gap-3 pt-2">
      <button type="submit" class="rounded-lg bg-blue-900 px-4 py-2 text-sm font-semibold text-white">Save</button>
      <a href="<?= e(web_url('/admin/users')) ?>" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700">Back</a>
    </div>
  </form>
  <form method="post" action="<?= e(web_url('/admin/users/' . $target->id . '/delete')) ?>" class="mt-6" onsubmit="return confirm('Delete this user?');">
    <button type="submit" class="text-sm font-semibold text-red-600 hover:text-red-700">Delete user</button>
  </form>
</section>
