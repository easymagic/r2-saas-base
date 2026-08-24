<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= e(isset($title) ? $title . ' — BorderlessFetch' : 'BorderlessFetch') ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = { theme: { extend: { fontFamily: { sans: ['DM Sans', 'system-ui', 'sans-serif'] } } } };
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&display=swap" rel="stylesheet" />
</head>
<body class="min-h-screen bg-gray-50 font-sans text-gray-900 antialiased">
  <?php \Presentation\View\View::partial('partials/flash', ['flash' => isset($flash) ? $flash : null]); ?>
  <div class="flex min-h-screen">
    <?php
      $layoutNav = isset($layout_nav) ? $layout_nav : 'user';
      if ($layoutNav === 'admin') {
          \Presentation\View\View::partial('partials/admin-sidebar', ['nav' => isset($nav) ? $nav : '', 'user' => $user]);
      } else {
          \Presentation\View\View::partial('partials/user-sidebar', ['nav' => isset($nav) ? $nav : '', 'user' => $user]);
      }
    ?>
    <div class="flex min-w-0 flex-1 flex-col">
      <?php \Presentation\View\View::partial('partials/topbar', [
          'title' => isset($title) ? $title : '',
          'subtitle' => isset($subtitle) ? $subtitle : '',
          'user' => $user,
          'balance' => isset($balance) ? $balance : null,
      ]); ?>
      <main class="flex-1 p-4 sm:p-6 lg:p-8">
        <?= $content ?>
      </main>
    </div>
  </div>
</body>
</html>
