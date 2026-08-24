<section class="mx-auto max-w-2xl rounded-2xl bg-white p-6 shadow-md">
  <h2 class="text-base font-semibold text-gray-900">New user</h2>
  <form method="post" action="<?= e(web_url('/admin/users')) ?>" class="mt-6 space-y-4">
    <div>
      <label class="block text-sm font-medium text-gray-700">Name</label>
      <input name="name" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700">Email</label>
      <input name="email" type="email" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700">Password</label>
      <input name="password" type="password" required minlength="8" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
    </div>
    <div class="grid gap-4 sm:grid-cols-2">
      <div>
        <label class="block text-sm font-medium text-gray-700">Phone</label>
        <input name="phone" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Country code</label>
        <input name="country_code" value="+234" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
      </div>
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700">Delivery address</label>
      <textarea name="delivery_address" rows="2" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
    </div>
    <div class="grid gap-4 sm:grid-cols-2">
      <div>
        <label class="block text-sm font-medium text-gray-700">Role</label>
        <select name="role" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
          <option value="customer">customer</option>
          <option value="agent">agent</option>
          <option value="staff">staff</option>
          <option value="admin">admin</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Status</label>
        <select name="status" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
          <option value="active">active</option>
          <option value="inactive">inactive</option>
        </select>
      </div>
    </div>
    <button type="submit" class="rounded-lg bg-blue-900 px-4 py-2 text-sm font-semibold text-white">Create user</button>
  </form>
</section>
