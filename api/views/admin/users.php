<section class="rounded-2xl bg-white p-6 shadow-md">
  <div class="flex items-center justify-between gap-4">
    <h2 class="text-base font-semibold text-gray-900">Users</h2>
    <a href="<?= e(web_url('/admin/users/create')) ?>" class="rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-white">Create user</a>
  </div>
  <div class="mt-4 overflow-x-auto">
    <table class="w-full min-w-[40rem] border-collapse text-left text-sm">
      <thead>
        <tr class="border-b border-gray-200 bg-gray-100 text-xs font-semibold uppercase tracking-wide text-gray-600">
          <th class="px-4 py-3">ID</th>
          <th class="px-4 py-3">Name</th>
          <th class="px-4 py-3">Email</th>
          <th class="px-4 py-3">Role</th>
          <th class="px-4 py-3 text-right">Balance</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php if (empty($users)): ?>
          <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No users found.</td></tr>
        <?php else: ?>
          <?php foreach ($users as $u): ?>
            <tr class="hover:bg-gray-50">
              <td class="px-4 py-3">#<?= e($u->id) ?></td>
              <td class="px-4 py-3 font-medium"><a class="text-orange-600 hover:text-orange-700" href="<?= e(web_url('/admin/users/' . $u->id)) ?>"><?= e($u->name) ?></a></td>
              <td class="px-4 py-3 text-gray-600"><?= e($u->email) ?></td>
              <td class="px-4 py-3"><?= e($u->role) ?></td>
              <td class="px-4 py-3 text-right"><?= e(format_naira($u->wallet_balance)) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
