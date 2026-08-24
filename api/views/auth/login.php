<main class="flex min-h-screen flex-col items-center justify-center p-4 sm:p-6">
  <div class="mb-8 text-center">
    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-900 text-lg font-bold text-white shadow-lg shadow-blue-900/30">BF</div>
    <h1 class="mt-4 text-2xl font-semibold tracking-tight text-gray-900">BorderlessFetch</h1>
    <p class="mt-1 text-sm text-gray-500">Social commerce fulfillment</p>
  </div>

  <section class="w-full max-w-md rounded-2xl border border-white/70 bg-white/95 p-6 shadow-xl shadow-slate-900/10 backdrop-blur sm:p-8" aria-labelledby="signin-heading">
    <h2 id="signin-heading" class="text-lg font-semibold text-gray-900">Sign in</h2>
    <p class="mt-1 text-sm text-gray-500">Use your account email and password.</p>

    <form class="mt-6 space-y-4" action="<?= e(web_url('/login')) ?>" method="post">
      <div>
        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
        <input id="email" name="email" type="email" autocomplete="email" required value="<?= e($email) ?>" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="you@company.com" />
      </div>
      <div>
        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500" />
      </div>
      <button type="submit" class="w-full rounded-lg bg-blue-900 px-4 py-2 text-sm font-semibold text-white shadow-md hover:bg-blue-950 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Sign in</button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-500">
      No account?
      <a class="font-semibold text-orange-600 hover:text-orange-700" href="<?= e(web_url('/register')) ?>">Register</a>
      ·
      <a class="font-semibold text-orange-600 hover:text-orange-700" href="<?= e(web_url('/forgot-password')) ?>">Forgot password?</a>
    </p>
  </section>
</main>
