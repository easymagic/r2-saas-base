<?php if (!empty($flash) && is_array($flash)): ?>
  <?php
    $type = isset($flash['type']) ? $flash['type'] : 'info';
    $msg = isset($flash['message']) ? $flash['message'] : '';
    $classes = $type === 'success'
      ? 'border-green-200 bg-green-50 text-green-900'
      : ($type === 'error' ? 'border-red-200 bg-red-50 text-red-900' : 'border-slate-200 bg-white text-slate-800');
  ?>
  <?php if ($msg !== ''): ?>
    <div class="fixed right-4 top-4 z-[100] max-w-sm rounded-xl border px-4 py-3 text-sm shadow-lg <?= e($classes) ?>" role="status">
      <?= e($msg) ?>
    </div>
  <?php endif; ?>
<?php endif; ?>
