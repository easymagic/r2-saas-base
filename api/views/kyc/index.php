<?php
$canEdit = empty($kyc) || (int) $kyc->approved === 0;
$isUpdate = !empty($kyc) && (int) $kyc->approved === 0;
?>
<section class="max-w-2xl rounded-2xl bg-white p-6 shadow-md">
  <?php if (!empty($kyc)): ?>
    <div class="mb-6 rounded-lg bg-gray-50 p-4 text-sm">
      <p><strong>Status:</strong>
        <?php if ((int) $kyc->approved === 1): ?>
          <span class="text-green-700">Approved</span>
        <?php elseif ((int) $kyc->approved === 0): ?>
          <span class="text-red-700">Rejected</span><?php if ($kyc->reject_reason !== ''): ?> — <?= e($kyc->reject_reason) ?><?php endif; ?>
        <?php else: ?>
          <span class="text-yellow-700">Pending review</span>
        <?php endif; ?>
      </p>
    </div>
  <?php endif; ?>

  <?php if (!$canEdit): ?>
    <p class="text-gray-600">Your KYC is approved. Contact support if you need changes.</p>
  <?php else: ?>
    <form method="post" action="<?= e(web_url($isUpdate ? '/kyc/update' : '/kyc')) ?>" enctype="multipart/form-data" class="space-y-4">
      <?php if ($isUpdate): ?>
        <input type="hidden" name="kyc_id" value="<?= e($kyc->id) ?>" />
      <?php endif; ?>
      <div>
        <label class="block text-sm font-medium text-gray-700">NIN</label>
        <input name="nin" required value="<?= e($kyc ? $kyc->nin : '') ?>" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Store name</label>
        <input name="store_name" required value="<?= e($kyc ? $kyc->store_name : '') ?>" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Description</label>
        <textarea name="description" required rows="4" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"><?= e($kyc ? $kyc->description : '') ?></textarea>
      </div>
      <?php for ($i = 1; $i <= 3; $i++): ?>
        <?php $field = 'document' . $i; ?>
        <div>
          <label class="block text-sm font-medium text-gray-700">Document <?= $i ?> (optional)</label>
          <?php if ($kyc && $kyc->$field !== ''): ?>
            <p class="text-xs text-gray-500"><a class="text-orange-600" href="<?= e(upload_url($kyc->$field)) ?>" target="_blank">View current file</a></p>
          <?php endif; ?>
          <input type="file" name="document<?= $i ?>" accept="image/*,.pdf" class="mt-1 w-full text-sm" />
        </div>
      <?php endfor; ?>
      <button type="submit" class="rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-white"><?= $isUpdate ? 'Resubmit' : 'Submit KYC' ?></button>
    </form>
  <?php endif; ?>
</section>
