<section class="max-w-4xl rounded-2xl bg-white p-6 shadow-md">
  <dl class="grid gap-4 sm:grid-cols-2 text-sm">
    <div><dt class="text-gray-500">ID</dt><dd class="font-medium">#<?= e($log->id) ?></dd></div>
    <div><dt class="text-gray-500">Type</dt><dd><span class="rounded-full px-2 py-0.5 text-xs font-medium <?= e(status_badge_class($log->type)) ?>"><?= e($log->type) ?></span></dd></div>
    <div class="sm:col-span-2"><dt class="text-gray-500">Title</dt><dd class="font-medium"><?= e($log->title) ?></dd></div>
    <div><dt class="text-gray-500">Created</dt><dd><?= e(format_date($log->created_at)) ?></dd></div>
    <div><dt class="text-gray-500">Updated</dt><dd><?= e(format_date($log->updated_at)) ?></dd></div>
  </dl>

  <h3 class="mt-8 text-sm font-semibold text-gray-900">Payload</h3>
  <pre class="mt-2 max-h-80 overflow-auto rounded-lg bg-gray-50 p-4 text-xs text-gray-800 whitespace-pre-wrap break-words"><?= e($log->payload) ?></pre>

  <h3 class="mt-6 text-sm font-semibold text-gray-900">Response</h3>
  <pre class="mt-2 max-h-80 overflow-auto rounded-lg bg-gray-50 p-4 text-xs text-gray-800 whitespace-pre-wrap break-words"><?= e($log->response) ?></pre>

  <a href="<?= e(web_url('/admin/logs')) ?>" class="mt-6 inline-block text-sm font-semibold text-orange-600">← Back to logs</a>
</section>
