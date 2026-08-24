<main class="flex min-h-screen flex-col items-center justify-center p-4 sm:p-6">
  <section class="w-full max-w-md rounded-2xl border border-white/70 bg-white/95 p-6 shadow-xl sm:p-8">
    <h2 class="text-lg font-semibold text-gray-900">Verify email</h2>
    <p class="mt-1 text-sm text-gray-500">Enter the OTP sent to your inbox.</p>
    <form class="mt-6 space-y-4" action="<?= e(web_url('/register/verify-otp')) ?>" method="post">
      <div>
        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
        <input id="email" name="email" type="email" required value="<?= e($email) ?>" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500" />
      </div>
      <div>
        <label for="otp" class="block text-sm font-medium text-gray-700">OTP</label>
        <input id="otp" name="otp" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500" />
      </div>
      <button type="submit" class="w-full rounded-lg bg-blue-900 px-4 py-2 text-sm font-semibold text-white shadow-md hover:bg-blue-950">Verify</button>
    </form>
  </section>
</main>
