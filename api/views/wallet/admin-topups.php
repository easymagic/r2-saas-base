<?php
function wallet_tab_class($current, $tab) {
    return $current === $tab
        ? 'border-b-2 border-blue-900 text-blue-900 font-semibold'
        : 'text-gray-500 hover:text-gray-800';
}
?>
<section class="rounded-2xl bg-white p-6 shadow-md">
  <div class="flex flex-wrap gap-4 border-b border-gray-200 pb-4">
    <a class="pb-2 text-sm <?= e(wallet_tab_class($tab, 'pending')) ?>" href="<?= e(web_url('/admin/wallet/topups?tab=pending')) ?>">Pending</a>
    <a class="pb-2 text-sm <?= e(wallet_tab_class($tab, 'approved')) ?>" href="<?= e(web_url('/admin/wallet/topups?tab=approved')) ?>">Approved</a>
    <a class="pb-2 text-sm <?= e(wallet_tab_class($tab, 'rejected')) ?>" href="<?= e(web_url('/admin/wallet/topups?tab=rejected')) ?>">Rejected</a>
  </div>
  <div class="mt-4 overflow-x-auto">
    <table class="w-full min-w-[40rem] border-collapse text-left text-sm">
      <thead>
        <tr class="border-b border-gray-200 bg-gray-100 text-xs font-semibold uppercase tracking-wide text-gray-600">
          <th class="px-4 py-3">ID</th>
          <th class="px-4 py-3">User</th>
          <th class="px-4 py-3">Reference</th>
          <th class="px-4 py-3 text-right">Amount</th>
          <th class="px-4 py-3">Date</th>
          <?php if ($tab === 'pending'): ?><th class="px-4 py-3">Actions</th><?php endif; ?>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php if (empty($transactions)): ?>
          <tr><td colspan="<?= $tab === 'pending' ? 6 : 5 ?>" class="px-4 py-8 text-center text-gray-500">No <?= e($tab) ?> top-ups.</td></tr>
        <?php else: ?>
          <?php foreach ($transactions as $tx): ?>
            <?php $owner = isset($users_by_id[$tx->user_id]) ? $users_by_id[$tx->user_id] : null; ?>
            <tr class="hover:bg-gray-50">
              <td class="px-4 py-3">#<?= e($tx->id) ?></td>
              <td class="px-4 py-3">
                <?php if ($owner && !$owner->isEmpty()): ?>
                  <?= e($owner->name) ?><span class="block text-xs text-gray-500"><?= e($owner->email) ?></span>
                <?php else: ?>
                  User #<?= e($tx->user_id) ?>
                <?php endif; ?>
              </td>
              <td class="px-4 py-3"><?= e($tx->reference) ?></td>
              <td class="px-4 py-3 text-right"><?= e(format_naira($tx->amount)) ?></td>
              <td class="px-4 py-3 text-gray-600"><?= e(format_date($tx->created_at)) ?></td>
              <?php if ($tab === 'pending'): ?>
                <td class="px-4 py-3">
                  <div class="flex flex-wrap gap-2">
                    <form method="post" action="<?= e(web_url('/admin/wallet/topups/' . $tx->id . '/approve')) ?>">
                      <button type="submit" class="rounded-lg bg-green-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-green-700">Approve</button>
                    </form>
                    <form method="post" action="<?= e(web_url('/admin/wallet/topups/' . $tx->id . '/reject')) ?>">
                      <input type="hidden" name="reason" value="Rejected by admin" />
                      <button type="submit" class="rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700">Reject</button>
                    </form>
                  </div>
                </td>
              <?php endif; ?>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
