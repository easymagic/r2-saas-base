<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= e(!empty($title) ? $title . ' — BorderlessFetch' : 'BorderlessFetch') ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = { theme: { extend: { fontFamily: { sans: ['DM Sans', 'system-ui', 'sans-serif'] } } } };
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&display=swap" rel="stylesheet" />
</head>
<body class="min-h-screen font-sans text-gray-900 antialiased" style="background: radial-gradient(900px 420px at 15% 0%, rgba(30,58,95,.18), transparent 55%), radial-gradient(700px 360px at 90% 10%, rgba(234,88,12,.14), transparent 45%), linear-gradient(165deg,#f8fafc 0%,#e2e8f0 100%);">
  <?php \Presentation\View\View::partial('partials/flash', ['flash' => isset($flash) ? $flash : null]); ?>
  <?= $content ?>
</body>
</html>
