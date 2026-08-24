<?php
use SnappyOrder\Data\SnappyOrderRepositoryInterface;
$statuses = SnappyOrderRepositoryInterface::ALLOWED_STATUSES;
$isAdmin = !empty($user) && method_exists($user, 'isAdmin') && $user->isAdmin();
?>
<div class="mx-auto max-w-4xl space-y-6">
  <section class="rounded-2xl bg-white p-6 shadow-md">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
      <div>
        <p class="text-sm text-gray-500">Reference</p>
        <p class="font-medium text-gray-900"><?= e($order->reference) ?></p>
        <p class="mt-3 text-sm text-gray-500">Status</p>
        <span class="mt-1 inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium <?= e(status_badge_class($order->status)) ?>"><?= e($order->status) ?></span>
      </div>
      <div class="space-y-1 text-sm text-gray-700 sm:text-right">
        <p>Amount: <strong><?= e(format_dollar($order->total_amount_usd)) ?></strong></p>
        <p>Grand total: <strong><?= e(format_naira($order->grand_total_naira)) ?></strong></p>
        <p>Created: <?= e(format_date($order->created_at)) ?></p>
      </div>
    </div>
    <div class="mt-6 border-t border-gray-100 pt-4">
      <p class="text-sm font-medium text-gray-700">Product link</p>
      <a class="mt-1 break-all text-sm text-orange-600 hover:text-orange-700" href="<?= e($order->link) ?>" target="_blank" rel="noopener"><?= e($order->link) ?></a>
      <p class="mt-4 text-sm font-medium text-gray-700">Description</p>
      <p class="mt-1 whitespace-pre-wrap text-sm text-gray-600"><?= e($order->description) ?></p>
    </div>
    <div class="mt-6 flex flex-wrap gap-3">
      <?php if ($order->status === 'pending'): ?>
        <form method="post" action="<?= e(web_url('/orders/' . $order->id . '/pay')) ?>">
          <button type="submit" class="rounded-lg bg-blue-900 px-4 py-2 text-sm font-semibold text-white shadow-md hover:bg-blue-950">Pay from wallet</button>
        </form>
      <?php endif; ?>
      <a href="<?= e(web_url('/orders')) ?>" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">Back to orders</a>
    </div>
  </section>

  <?php if ($isAdmin): ?>
    <section class="rounded-2xl bg-white p-6 shadow-md space-y-6">
      <div>
        <h2 class="text-base font-semibold text-gray-900">Update status</h2>
        <form class="mt-4 flex flex-wrap items-end gap-3" method="post" action="<?= e(web_url('/orders/' . $order->id . '/status')) ?>">
          <div>
            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
            <select id="status" name="status" class="mt-1 rounded-lg border border-gray-300 px-3 py-2 text-sm">
              <?php foreach ($statuses as $status): ?>
                <option value="<?= e($status) ?>" <?= $order->status === $status ? 'selected' : '' ?>><?= e($status) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label for="pickup_otp_code" class="block text-sm font-medium text-gray-700">Pickup OTP</label>
            <input id="pickup_otp_code" name="pickup_otp_code" class="mt-1 rounded-lg border border-gray-300 px-3 py-2 text-sm" />
          </div>
          <button type="submit" class="rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-white">Save status</button>
        </form>
      </div>
      <div>
        <h2 class="text-base font-semibold text-gray-900">Adjust price (USD)</h2>
        <form class="mt-4 flex flex-wrap items-end gap-3" method="post" action="<?= e(web_url('/orders/' . $order->id . '/price')) ?>">
          <input name="price" type="number" step="0.01" min="0.01" value="<?= e($order->total_amount_usd) ?>" class="rounded-lg border border-gray-300 px-3 py-2 text-sm" />
          <button type="submit" class="rounded-lg bg-blue-900 px-4 py-2 text-sm font-semibold text-white">Update price</button>
        </form>
      </div>
      <div>
        <h2 class="text-base font-semibold text-gray-900">Assign agent</h2>
        <form class="mt-4 flex flex-wrap items-end gap-3" method="post" action="<?= e(web_url('/orders/' . $order->id . '/assign-agent')) ?>">
          <select name="agent_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="">Select agent</option>
            <?php foreach ($agents as $agent): ?>
              <option value="<?= e($agent->id) ?>" <?= (int) $order->agent_id === (int) $agent->id ? 'selected' : '' ?>><?= e($agent->name) ?> (<?= e($agent->email) ?>)</option>
            <?php endforeach; ?>
          </select>
          <button type="submit" class="rounded-lg bg-blue-900 px-4 py-2 text-sm font-semibold text-white">Assign</button>
        </form>
      </div>
      <div>
        <h2 class="text-base font-semibold text-gray-900">Batch</h2>
        <div class="mt-4 flex flex-wrap gap-3">
          <form method="post" action="<?= e(web_url('/orders/' . $order->id . '/assign-batch')) ?>" class="flex flex-wrap items-end gap-2">
            <select name="batch_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
              <option value="">Select batch</option>
              <?php foreach ($batches as $batch): ?>
                <option value="<?= e($batch->id) ?>" <?= (int) $order->batch_id === (int) $batch->id ? 'selected' : '' ?>><?= e($batch->name) ?></option>
              <?php endforeach; ?>
            </select>
            <button type="submit" class="rounded-lg bg-blue-900 px-4 py-2 text-sm font-semibold text-white">Assign batch</button>
          </form>
          <?php if ((int) $order->batch_id > 0): ?>
            <form method="post" action="<?= e(web_url('/orders/' . $order->id . '/unassign-batch')) ?>">
              <button type="submit" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700">Unassign batch</button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <section class="rounded-2xl bg-white p-6 shadow-md">
    <h2 class="text-base font-semibold text-gray-900">Messages</h2>
    <div class="mt-4 space-y-3">
      <?php if (empty($threads)): ?>
        <p class="text-sm text-gray-500">No messages yet.</p>
      <?php else: ?>
        <?php foreach ($threads as $thread): ?>
          <article class="rounded-xl border border-gray-100 bg-gray-50 px-4 py-3 text-sm">
            <p class="whitespace-pre-wrap text-gray-800"><?= e($thread->message) ?></p>
            <p class="mt-2 text-xs text-gray-500"><?= e(format_date(isset($thread->created_at) ? $thread->created_at : '')) ?></p>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    <form class="mt-6 space-y-3" method="post" action="<?= e(web_url('/orders/' . $order->id . '/thread')) ?>" enctype="multipart/form-data">
      <div>
        <label for="message" class="block text-sm font-medium text-gray-700">New message</label>
        <textarea id="message" name="message" rows="3" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
      </div>
      <div>
        <label for="attachment_url" class="block text-sm font-medium text-gray-700">Attachment (optional)</label>
        <input id="attachment_url" name="attachment_url" type="file" accept="image/*" class="mt-1 text-sm" />
      </div>
      <button type="submit" class="rounded-lg bg-blue-900 px-4 py-2 text-sm font-semibold text-white">Send</button>
    </form>
  </section>
</div>
