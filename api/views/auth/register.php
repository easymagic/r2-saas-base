<?php $old = isset($old) && is_array($old) ? $old : []; ?>
<main class="flex min-h-screen flex-col items-center justify-center p-4 sm:p-6">
  <div class="mb-8 text-center">
    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-900 text-lg font-bold text-white shadow-lg shadow-blue-900/30">BF</div>
    <h1 class="mt-4 text-2xl font-semibold tracking-tight text-gray-900">Create account</h1>
  </div>

  <section class="w-full max-w-lg rounded-2xl border border-white/70 bg-white/95 p-6 shadow-xl sm:p-8">
    <form class="space-y-4" action="<?= e(web_url('/register')) ?>" method="post">
      <div>
        <label for="name" class="block text-sm font-medium text-gray-700">Full name</label>
        <input id="name" name="name" required value="<?= e(isset($old['name']) ? $old['name'] : '') ?>" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500" />
      </div>
      <div>
        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
        <input id="email" name="email" type="email" required value="<?= e(isset($old['email']) ? $old['email'] : '') ?>" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500" />
      </div>
      <div class="grid gap-4 sm:grid-cols-2">
        <div>
          <label for="country_code" class="block text-sm font-medium text-gray-700">Country code</label>
          <input id="country_code" name="country_code" value="<?= e(isset($old['country_code']) ? $old['country_code'] : '+234') ?>" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>
        <div>
          <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
          <input id="phone" name="phone" required value="<?= e(isset($old['phone']) ? $old['phone'] : '') ?>" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>
      </div>
      <div>
        <label for="delivery_address" class="block text-sm font-medium text-gray-700">Delivery address</label>
        <textarea id="delivery_address" name="delivery_address" rows="2" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"><?= e(isset($old['delivery_address']) ? $old['delivery_address'] : '') ?></textarea>
      </div>
      <div>
        <label for="social_security_number" class="block text-sm font-medium text-gray-700">ID / SSN</label>
        <input id="social_security_number" name="social_security_number" required value="<?= e(isset($old['social_security_number']) ? $old['social_security_number'] : '') ?>" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500" />
      </div>
      <div>
        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
        <input id="password" name="password" type="password" required minlength="8" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500" />
      </div>
      <button type="submit" class="w-full rounded-lg bg-blue-900 px-4 py-2 text-sm font-semibold text-white shadow-md hover:bg-blue-950">Register</button>
    </form>
    <p class="mt-6 text-center text-sm text-gray-500">
      Already have an account?
      <a class="font-semibold text-orange-600 hover:text-orange-700" href="<?= e(web_url('/login')) ?>">Sign in</a>
    </p>
  </section>
</main>
