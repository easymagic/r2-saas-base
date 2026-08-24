<div class="grid gap-6 lg:grid-cols-3">
  <section class="rounded-2xl bg-white p-6 shadow-md lg:col-span-2">
    <dl class="grid gap-4 sm:grid-cols-2 text-sm">
      <div><dt class="text-gray-500">Store name</dt><dd class="font-medium"><?= e($kyc->store_name) ?></dd></div>
      <div><dt class="text-gray-500">User</dt><dd><?= e(isset($userMap[(int) $kyc->user_id]) ? $userMap[(int) $kyc->user_id] : '#' . $kyc->user_id) ?></dd></div>
      <div><dt class="text-gray-500">NIN</dt><dd><?= e($kyc->nin) ?></dd></div>
      <div><dt class="text-gray-500">Status</dt><dd>
        <?php if ((int) $kyc->approved === 1): ?>Approved
        <?php elseif ((int) $kyc->approved === 0): ?>Rejected
        <?php else: ?>Pending<?php endif; ?>
      </dd></div>
      <div class="sm:col-span-2"><dt class="text-gray-500">Description</dt><dd><?= e($kyc->description) ?></dd></div>
      <?php if ($kyc->reject_reason !== ''): ?>
        <div class="sm:col-span-2"><dt class="text-gray-500">Reject reason</dt><dd class="text-red-700"><?= e($kyc->reject_reason) ?></dd></div>
      <?php endif; ?>
    </dl>

    <h3 class="mt-8 text-sm font-semibold text-gray-900">Documents</h3>
    <div class="mt-3 grid gap-4 sm:grid-cols-2">
      <?php for ($i = 1; $i <= 5; $i++): ?>
        <?php $field = 'document' . $i; if ($kyc->$field !== ''): ?>
          <a href="<?= e(upload_url($kyc->$field)) ?>" target="_blank" class="block rounded-lg border border-gray-200 p-3 text-sm text-orange-600 hover:bg-gray-50">Document <?= $i ?></a>
        <?php endif; ?>
      <?php endfor; ?>
    </div>
  </section>

  <section class="space-y-6">
    <?php if ((int) $kyc->approved === -1): ?>
      <form method="post" action="<?= e(web_url('/admin/kyc/' . $kyc->id . '/approve')) ?>" class="rounded-2xl bg-white p-6 shadow-md">
        <button type="submit" class="w-full rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white">Approve KYC</button>
      </form>
      <form method="post" action="<?= e(web_url('/admin/kyc/' . $kyc->id . '/reject')) ?>" class="rounded-2xl bg-white p-6 shadow-md space-y-3">
        <label class="block text-sm font-medium text-gray-700">Reject reason</label>
        <textarea name="reject_reason" required rows="3" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
        <button type="submit" class="w-full rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white">Reject KYC</button>
      </form>
    <?php endif; ?>
    <a href="<?= e(web_url('/admin/kyc')) ?>" class="block text-center text-sm font-semibold text-orange-600">← Back to list</a>
  </section>
</div>
