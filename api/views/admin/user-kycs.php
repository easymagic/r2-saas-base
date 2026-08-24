<section class="rounded-2xl bg-white p-6 shadow-md">
  <div class="flex flex-wrap gap-2 border-b border-gray-100 pb-4">
    <?php foreach (['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $key => $label): ?>
      <a href="<?= e(web_url('/admin/kyc?tab=' . $key)) ?>" class="rounded-lg px-4 py-2 text-sm font-semibold <?= $tab === $key ? 'bg-blue-900 text-white' : 'bg-gray-100 text-gray-700' ?>"><?= e($label) ?></a>
    <?php endforeach; ?>
  </div>

  <div class="mt-4 overflow-x-auto">
    <table class="w-full min-w-[40rem] border-collapse text-left text-sm">
      <thead>
        <tr class="border-b border-gray-200 bg-gray-100 text-xs font-semibold uppercase text-gray-600">
          <th class="px-4 py-3">ID</th>
          <th class="px-4 py-3">Store</th>
          <th class="px-4 py-3">User</th>
          <th class="px-4 py-3">NIN</th>
          <th class="px-4 py-3">Submitted</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php if (empty($records)): ?>
          <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No records in this tab.</td></tr>
        <?php else: ?>
          <?php foreach ($records as $kyc): ?>
            <tr class="hover:bg-gray-50">
              <td class="px-4 py-3"><a class="font-medium text-orange-600" href="<?= e(web_url('/admin/kyc/' . $kyc->id)) ?>">#<?= e($kyc->id) ?></a></td>
              <td class="px-4 py-3"><?= e($kyc->store_name) ?></td>
              <td class="px-4 py-3"><?= e(isset($userMap[(int) $kyc->user_id]) ? $userMap[(int) $kyc->user_id] : '#' . $kyc->user_id) ?></td>
              <td class="px-4 py-3"><?= e($kyc->nin) ?></td>
              <td class="px-4 py-3"><?= e(format_date($kyc->created_at)) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
