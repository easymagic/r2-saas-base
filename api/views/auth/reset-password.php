<main class="flex min-h-screen flex-col items-center justify-center p-4 sm:p-6">
  <section class="w-full max-w-md rounded-2xl border border-white/70 bg-white/95 p-6 shadow-xl sm:p-8">
    <h2 class="text-lg font-semibold text-gray-900">Reset password</h2>
    <form class="mt-6 space-y-4" method="post" action="<?= e(web_url('/reset-password')) ?>">
      <div>
        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
        <input id="email" name="email" type="email" required value="<?= e($email) ?>" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
      </div>
      <div>
        <label for="otp" class="block text-sm font-medium text-gray-700">OTP</label>
        <input id="otp" name="otp" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
      </div>
      <div>
        <label for="password" class="block text-sm font-medium text-gray-700">New password</label>
        <input id="password" name="password" type="password" required minlength="8" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
      </div>
      <div>
        <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm password</label>
        <input id="confirm_password" name="confirm_password" type="password" required minlength="8" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
      </div>
      <button type="submit" class="w-full rounded-lg bg-blue-900 px-4 py-2 text-sm font-semibold text-white">Reset password</button>
    </form>
  </section>
</main>
