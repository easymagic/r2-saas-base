<div class="space-y-6">
  <section class="rounded-2xl bg-white p-6 shadow-md sm:flex sm:items-center sm:justify-between">
    <div>
      <h2 class="text-sm font-medium text-gray-500">Available balance</h2>
      <p class="mt-2 text-3xl font-semibold tracking-tight text-gray-900"><?= e(format_naira($balance)) ?></p>
    </div>
    <div class="mt-4 flex flex-wrap gap-2 sm:mt-0">
      <button type="button" onclick="document.getElementById('topup-online-modal').showModal()" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 shadow-sm hover:bg-gray-50">Pay online</button>
      <button type="button" onclick="document.getElementById('topup-modal').showModal()" class="rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-white shadow-md hover:bg-orange-600">Manual top-up</button>
    </div>
  </section>

  <?php if (!empty($pending_online)): ?>
    <section class="rounded-2xl border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-900">
      You have <?= count($pending_online) ?> pending online payment(s). Complete payment or wait for verification when you return to this page.
    </section>
  <?php endif; ?>

  <section class="rounded-2xl bg-white p-6 shadow-md">
    <h2 class="text-base font-semibold text-gray-900">Manual top-ups</h2>
    <div class="mt-4 overflow-x-auto">
      <table class="w-full min-w-[32rem] border-collapse text-left text-sm">
        <thead>
          <tr class="border-b border-gray-200 bg-gray-100 text-xs font-semibold uppercase tracking-wide text-gray-600">
            <th class="px-4 py-3">Reference</th>
            <th class="px-4 py-3 text-right">Amount</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3">Date</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php if (empty($manual_transactions)): ?>
            <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">No manual top-ups yet.</td></tr>
          <?php else: ?>
            <?php foreach ($manual_transactions as $tx): ?>
              <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 font-medium text-gray-900"><?= e($tx->reference) ?></td>
                <td class="px-4 py-3 text-right tabular-nums"><?= e(format_naira($tx->amount)) ?></td>
                <td class="px-4 py-3"><span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium <?= e(status_badge_class($tx->status)) ?>"><?= e($tx->status) ?></span></td>
                <td class="px-4 py-3 text-gray-600"><?= e(format_date($tx->created_at)) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>

  <section class="rounded-2xl bg-white p-6 shadow-md">
    <h2 class="text-base font-semibold text-gray-900">Online payments</h2>
    <div class="mt-4 overflow-x-auto">
      <table class="w-full min-w-[32rem] border-collapse text-left text-sm">
        <thead>
          <tr class="border-b border-gray-200 bg-gray-100 text-xs font-semibold uppercase tracking-wide text-gray-600">
            <th class="px-4 py-3">Reference</th>
            <th class="px-4 py-3 text-right">Amount</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3">Date</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php
            $online = array_merge($pending_online, $approved_online);
            usort($online, function ($a, $b) {
                return strcmp($b->created_at, $a->created_at);
            });
          ?>
          <?php if (empty($online)): ?>
            <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">No online payments yet.</td></tr>
          <?php else: ?>
            <?php foreach ($online as $tx): ?>
              <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 font-medium text-gray-900"><?= e($tx->reference) ?></td>
                <td class="px-4 py-3 text-right tabular-nums"><?= e(format_naira($tx->amount)) ?></td>
                <td class="px-4 py-3"><span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium <?= e(status_badge_class($tx->status)) ?>"><?= e($tx->status) ?></span></td>
                <td class="px-4 py-3 text-gray-600"><?= e(format_date($tx->created_at)) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
</div>

<dialog id="topup-modal" class="w-full max-w-md rounded-2xl border-0 p-0 shadow-2xl backdrop:bg-slate-900/40">
  <form method="post" action="<?= e(web_url('/wallet/top-up-manual')) ?>" enctype="multipart/form-data" class="p-6">
    <h3 class="text-lg font-semibold text-gray-900">Manual top-up</h3>
    <p class="mt-1 text-sm text-gray-500">Upload proof of payment for admin approval.</p>
    <div class="mt-4 space-y-4">
      <div>
        <label for="amount" class="block text-sm font-medium text-gray-700">Amount (NGN)</label>
        <input id="amount" name="amount" type="number" min="1" step="0.01" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500" />
      </div>
      <div>
        <label for="proof_of_payment_screenshot1" class="block text-sm font-medium text-gray-700">Proof of payment *</label>
        <input id="proof_of_payment_screenshot1" name="proof_of_payment_screenshot1" type="file" accept="image/*" required class="mt-1 w-full text-sm" />
      </div>
    </div>
    <div class="mt-6 flex justify-end gap-3">
      <button type="button" onclick="document.getElementById('topup-modal').close()" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700">Cancel</button>
      <button type="submit" class="rounded-lg bg-blue-900 px-4 py-2 text-sm font-semibold text-white">Submit</button>
    </div>
  </form>
</dialog>

<dialog id="topup-online-modal" class="w-full max-w-md rounded-2xl border-0 p-0 shadow-2xl backdrop:bg-slate-900/40">
  <form method="post" action="<?= e(web_url('/wallet/top-up-online')) ?>" class="p-6">
    <h3 class="text-lg font-semibold text-gray-900">Pay online</h3>
    <p class="mt-1 text-sm text-gray-500">You will be redirected to the payment provider.</p>
    <div class="mt-4">
      <label for="online_amount" class="block text-sm font-medium text-gray-700">Amount (NGN)</label>
      <input id="online_amount" name="amount" type="number" min="1" step="0.01" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500" />
    </div>
    <div class="mt-6 flex justify-end gap-3">
      <button type="button" onclick="document.getElementById('topup-online-modal').close()" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700">Cancel</button>
      <button type="submit" class="rounded-lg bg-blue-900 px-4 py-2 text-sm font-semibold text-white">Continue</button>
    </div>
  </form>
</dialog>
