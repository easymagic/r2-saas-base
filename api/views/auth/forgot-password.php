<main class="flex min-h-screen flex-col items-center justify-center p-4 sm:p-6">
  <section class="w-full max-w-md rounded-2xl border border-white/70 bg-white/95 p-6 shadow-xl sm:p-8">
    <h2 class="text-lg font-semibold text-gray-900">Forgot password</h2>
    <form class="mt-6 space-y-4" method="post" action="<?= e(web_url('/forgot-password')) ?>">
      <div>
        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
        <input id="email" name="email" type="email" required value="<?= e($email) ?>" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
      </div>
      <button type="submit" class="w-full rounded-lg bg-blue-900 px-4 py-2 text-sm font-semibold text-white">Send reset OTP</button>
    </form>
    <p class="mt-6 text-center text-sm"><a href="<?= e(web_url('/login')) ?>" class="text-orange-600 hover:text-orange-700">Back to sign in</a></p>
  </section>
</main>
