<input type="checkbox" id="mobile-nav" class="peer sr-only" />
<label for="mobile-nav" tabindex="0" class="fixed inset-0 z-40 bg-blue-900/50 opacity-0 pointer-events-none transition-opacity peer-checked:opacity-100 peer-checked:pointer-events-auto lg:hidden" aria-hidden="true"></label>

<aside class="fixed left-0 top-0 z-50 flex h-full w-64 -translate-x-full flex-col border-r border-gray-200 bg-white shadow-lg transition-transform duration-200 peer-checked:translate-x-0 lg:static lg:translate-x-0 lg:shadow-none" aria-label="Admin navigation">
  <div class="flex h-16 items-center gap-2 border-b border-gray-100 px-6">
    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-900 text-sm font-bold text-white">AD</span>
    <div>
      <p class="text-sm font-semibold text-gray-900">Admin</p>
      <p class="text-xs text-gray-500">Operations</p>
    </div>
  </div>
  <nav class="flex flex-1 flex-col gap-1 p-4" aria-label="Admin workspace">
    <a class="<?= e(nav_active($nav, 'admin')) ?>" href="<?= e(web_url('/admin')) ?>">Overview</a>
    <a class="<?= e(nav_active($nav, 'orders')) ?>" href="<?= e(web_url('/orders')) ?>">Orders</a>
    <a class="<?= e(nav_active($nav, 'admin-topups')) ?>" href="<?= e(web_url('/admin/wallet/topups')) ?>">Wallet top-ups</a>
    <a class="<?= e(nav_active($nav, 'admin-batches')) ?>" href="<?= e(web_url('/admin/batches')) ?>">Batches</a>
    <a class="<?= e(nav_active($nav, 'admin-users')) ?>" href="<?= e(web_url('/admin/users')) ?>">Users</a>
    <a class="<?= e(nav_active($nav, 'admin-categories')) ?>" href="<?= e(web_url('/admin/categories')) ?>">Categories</a>
    <a class="<?= e(nav_active($nav, 'admin-products')) ?>" href="<?= e(web_url('/admin/products')) ?>">Products</a>
    <a class="<?= e(nav_active($nav, 'admin-ecom-orders')) ?>" href="<?= e(web_url('/admin/ecom-orders')) ?>">Ecom orders</a>
    <a class="<?= e(nav_active($nav, 'admin-kyc')) ?>" href="<?= e(web_url('/admin/kyc')) ?>">Merchant KYC</a>
    <a class="<?= e(nav_active($nav, 'admin-platform')) ?>" href="<?= e(web_url('/admin/platform-config')) ?>">Platform config</a>
    <a class="<?= e(nav_active($nav, 'admin-logs')) ?>" href="<?= e(web_url('/admin/logs')) ?>">Logs</a>
    <a class="<?= e(nav_active($nav, 'admin-proxy-logs')) ?>" href="<?= e(web_url('/admin/proxy-order-change-logs')) ?>">Order change logs</a>
    <a class="<?= e(nav_active($nav, 'admin-bnpl')) ?>" href="<?= e(web_url('/admin/bnpl-schedules')) ?>">BNPL schedules</a>
  </nav>
  <div class="border-t border-gray-100 p-4 space-y-1">
    <a class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm text-gray-600 hover:bg-gray-50" href="<?= e(web_url('/dashboard')) ?>">Customer view</a>
    <form method="post" action="<?= e(web_url('/logout')) ?>">
      <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2 text-left text-sm text-gray-600 hover:bg-gray-50">Sign out</button>
    </form>
  </div>
</aside>
