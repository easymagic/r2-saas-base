<section class="mx-auto max-w-2xl rounded-2xl bg-white p-6 shadow-md">
  <form method="post" action="<?= e(web_url('/profile')) ?>" class="space-y-4">
    <div>
      <label class="block text-sm font-medium text-gray-700">Name</label>
      <input name="name" required value="<?= e($user->name) ?>" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700">Email</label>
      <input value="<?= e($user->email) ?>" disabled class="mt-1 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-500" />
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700">Phone</label>
      <input name="phone" value="<?= e($user->phone) ?>" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700">Delivery address</label>
      <textarea name="delivery_address" rows="3" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"><?= e($user->delivery_address) ?></textarea>
    </div>
    <button type="submit" class="rounded-lg bg-blue-900 px-4 py-2 text-sm font-semibold text-white">Save profile</button>
  </form>

  <hr class="my-8 border-gray-200" />

  <h2 class="text-base font-semibold text-gray-900">Change password</h2>
  <form method="post" action="<?= e(web_url('/profile/change-password')) ?>" class="mt-4 space-y-4">
    <div>
      <label class="block text-sm font-medium text-gray-700">Current password</label>
      <input name="old_password" type="password" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700">New password</label>
      <input name="new_password" type="password" required minlength="8" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700">Confirm password</label>
      <input name="confirm_password" type="password" required minlength="8" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
    </div>
    <button type="submit" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800">Update password</button>
  </form>
</section>
