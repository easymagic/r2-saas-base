<?php $old = isset($old) && is_array($old) ? $old : []; ?>
<div class="mx-auto max-w-2xl">
  <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-md sm:p-8">
    <h2 class="text-base font-semibold text-gray-900">Product request</h2>
    <p class="mt-1 text-sm text-gray-500">Paste the product URL, describe what you need, and attach screenshots.</p>

    <form class="mt-6 space-y-6" action="<?= e(web_url('/orders')) ?>" method="post" enctype="multipart/form-data">
      <div>
        <label for="link" class="block text-sm font-medium text-gray-700">Product link</label>
        <input id="link" name="link" type="url" required value="<?= e(isset($old['link']) ? $old['link'] : '') ?>" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="https://..." />
      </div>
      <div>
        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
        <textarea id="description" name="description" rows="4" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Size, color, quantity…"><?= e(isset($old['description']) ? $old['description'] : '') ?></textarea>
      </div>
      <div>
        <span class="block text-sm font-medium text-gray-700">Screenshots</span>
        <p class="mt-1 text-xs text-gray-500">Screenshot 1 is required</p>
        <div class="mt-3 grid gap-3 sm:grid-cols-3">
          <label class="flex min-h-[7rem] cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 px-3 py-4 text-center text-sm hover:border-orange-400">
            Shot 1 *
            <input name="screen_shot1" type="file" accept="image/*" required class="mt-2 text-xs" />
          </label>
          <label class="flex min-h-[7rem] cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 px-3 py-4 text-center text-sm hover:border-orange-400">
            Shot 2
            <input name="screen_shot2" type="file" accept="image/*" class="mt-2 text-xs" />
          </label>
          <label class="flex min-h-[7rem] cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 px-3 py-4 text-center text-sm hover:border-orange-400">
            Shot 3
            <input name="screen_shot3" type="file" accept="image/*" class="mt-2 text-xs" />
          </label>
        </div>
      </div>
      <div>
        <label for="total_amount_usd" class="block text-sm font-medium text-gray-700">Amount (USD)</label>
        <input id="total_amount_usd" name="total_amount_usd" type="text" inputmode="decimal" required value="<?= e(isset($old['total_amount_usd']) ? $old['total_amount_usd'] : '') ?>" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="50" />
      </div>
      <div class="flex flex-wrap gap-3">
        <button type="submit" class="rounded-lg bg-blue-900 px-4 py-2 text-sm font-semibold text-white shadow-md hover:bg-blue-950">Submit order</button>
        <a href="<?= e(web_url('/orders')) ?>" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">Cancel</a>
      </div>
    </form>
  </section>
</div>
