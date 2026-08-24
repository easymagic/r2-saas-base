<input type="checkbox" id="mobile-nav" class="peer sr-only" />
<label for="mobile-nav" tabindex="0" class="fixed inset-0 z-40 bg-blue-900/50 opacity-0 pointer-events-none transition-opacity peer-checked:opacity-100 peer-checked:pointer-events-auto lg:hidden" aria-hidden="true"></label>

<aside class="fixed left-0 top-0 z-50 flex h-full w-64 -translate-x-full flex-col border-r border-gray-200 bg-white shadow-lg transition-transform duration-200 peer-checked:translate-x-0 lg:static lg:translate-x-0 lg:shadow-none" aria-label="Main navigation">
  <div class="flex h-16 items-center gap-2 border-b border-gray-100 px-6">
    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-900 text-sm font-bold text-white">BF</span>
    <div>
      <p class="text-sm font-semibold text-gray-900">BorderlessFetch</p>
      <p class="text-xs text-gray-500">Fulfillment</p>
    </div>
  </div>
  <nav class="flex flex-1 flex-col gap-1 p-4" aria-label="Workspace">
    <a class="<?= e(nav_active($nav, 'dashboard')) ?>" href="<?= e(web_url('/dashboard')) ?>">Dashboard</a>
    <a class="<?= e(nav_active($nav, 'shop')) ?>" href="<?= e(web_url('/shop')) ?>">Shop</a>
    <a class="<?= e(nav_active($nav, 'cart')) ?>" href="<?= e(web_url('/cart')) ?>">Cart</a>
    <a class="<?= e(nav_active($nav, 'ecom-orders')) ?>" href="<?= e(web_url('/ecom-orders')) ?>">Store orders</a>
    <a class="<?= e(nav_active($nav, 'create-order')) ?>" href="<?= e(web_url('/orders/create')) ?>">Create order</a>
    <a class="<?= e(nav_active($nav, 'orders')) ?>" href="<?= e(web_url('/orders')) ?>">Orders</a>
    <a class="<?= e(nav_active($nav, 'wallet')) ?>" href="<?= e(web_url('/wallet')) ?>">Wallet</a>
    <a class="<?= e(nav_active($nav, 'notifications')) ?>" href="<?= e(web_url('/notifications')) ?>">Notifications</a>
    <a class="<?= e(nav_active($nav, 'profile')) ?>" href="<?= e(web_url('/profile')) ?>">Profile</a>
    <a class="<?= e(nav_active($nav, 'kyc')) ?>" href="<?= e(web_url('/kyc')) ?>">Merchant KYC</a>
    <?php if (!empty($user) && method_exists($user, 'isAdmin') && $user->isAdmin()): ?>
      <a class="<?= e(nav_active($nav, 'admin')) ?>" href="<?= e(web_url('/admin')) ?>">Admin</a>
    <?php endif; ?>
  </nav>
  <div class="border-t border-gray-100 p-4">
    <form method="post" action="<?= e(web_url('/logout')) ?>">
      <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2 text-left text-sm text-gray-600 hover:bg-gray-50">Sign out</button>
    </form>
  </div>
</aside>
