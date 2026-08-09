import { useState } from 'react';
import { NavLink, Outlet, useNavigate } from 'react-router-dom';
import { cn } from '../../lib/cn.js';
import { Button } from '../ui/Button.jsx';
import { clearAuthUser, getStoredUser } from '../../lib/authSession.js';
import { logoutFromApi } from '../../lib/userApi.js';

const customerNav = [
  { to: '/profile', label: 'Profile' },
  { to: '/create-order', label: 'Create order' },
  { to: '/orders', label: 'Orders' },
  { to: '/notifications', label: 'Notifications' },
  { to: '/wallet', label: 'Wallet' },
];

const adminNav = [
  { to: '/admin', label: 'Dashboard', end: true },
  { to: '/orders', label: 'Orders' },
  { to: '/admin/topups', label: 'Top-up approvals' },
  { to: '/admin/batches', label: 'Batches' },
  { to: '/admin/users', label: 'Users' },
  { to: '/admin/platform-config', label: 'Platform config' },
  { to: '/admin/settings', label: 'Settings' },
  { to: '/notifications', label: 'Notifications' },
  { to: '/wallet', label: 'Wallet' },
];

export function AppLayout() {
  const [open, setOpen] = useState(false);
  const navigate = useNavigate();
  const user = getStoredUser();
  const isAdmin = String(user?.role || '').toLowerCase() === 'admin';
  const navItems = isAdmin ? adminNav : customerNav;

  async function handleSignOut() {
    setOpen(false);
    const u = getStoredUser();
    if (u?.token) {
      try {
        await logoutFromApi(u);
      } catch {
        /* still clear local session */
      }
    }
    clearAuthUser();
    navigate('/login');
  }

  return (
    <div className="app-shell flex min-h-screen font-sans text-gray-900 antialiased">
      {open && (
        <button
          type="button"
          className="fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-[1px] lg:hidden"
          aria-label="Close menu"
          onClick={() => setOpen(false)}
        />
      )}
      <aside
        className={cn(
          'fixed left-0 top-0 z-50 flex h-full w-64 flex-col border-r border-slate-200/80 bg-white/95 shadow-lg shadow-slate-900/5 backdrop-blur transition-transform duration-200 lg:static lg:translate-x-0 lg:shadow-none',
          open ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
        )}
        aria-label="Main navigation"
      >
        <div className="flex h-16 items-center gap-2 border-b border-slate-100 px-6">
          <span className="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-900 text-sm font-bold text-white shadow-md shadow-blue-900/25">
            BF
          </span>
          <div className="min-w-0">
            <p className="truncate text-sm font-semibold text-gray-900">BorderlessFetch</p>
            <p className="text-xs text-slate-500">{isAdmin ? 'Admin' : 'Fulfillment'}</p>
          </div>
        </div>
        <div className="border-b border-slate-100 px-4 pb-4 pt-2">
          <div className="rounded-xl border border-orange-200/70 bg-[linear-gradient(145deg,#fff7ed_0%,#ffffff_55%,#eff6ff_100%)] p-4 shadow-sm">
            <p className="text-xs font-semibold uppercase tracking-wide text-orange-800">Your account</p>
            <p className="mt-1 truncate text-sm font-medium text-gray-800">{user?.name || 'Member'}</p>
            <p className="mt-0.5 truncate text-xs text-slate-500">{user?.email || 'Update profile details'}</p>
            <Button
              as={NavLink}
              to="/profile"
              variant="orange"
              className="mt-3 w-full"
              onClick={() => setOpen(false)}
            >
              Edit profile
            </Button>
            {!isAdmin ? (
              <Button
                as={NavLink}
                to="/create-order"
                variant="secondary"
                className="mt-2 w-full"
                onClick={() => setOpen(false)}
              >
                Create order
              </Button>
            ) : null}
          </div>
        </div>
        <nav className="flex flex-1 flex-col gap-1 overflow-y-auto p-4">
          {navItems.map((item) => (
            <NavLink
              key={item.to}
              to={item.to}
              end={item.end}
              onClick={() => setOpen(false)}
              className={({ isActive }) =>
                cn(
                  'rounded-xl px-3 py-2 text-sm font-medium transition-colors',
                  isActive
                    ? 'bg-blue-900 text-white shadow-sm shadow-blue-900/20'
                    : 'text-gray-700 hover:bg-slate-100'
                )
              }
            >
              {item.label}
            </NavLink>
          ))}
        </nav>
        <div className="border-t border-slate-100 p-4">
          <button
            type="button"
            onClick={handleSignOut}
            className="flex w-full rounded-xl px-3 py-2 text-left text-sm text-slate-600 transition hover:bg-slate-100"
          >
            Sign out
          </button>
        </div>
      </aside>

      <div className="flex min-w-0 flex-1 flex-col">
        <Outlet context={{ openMobileNav: () => setOpen(true) }} />
      </div>
    </div>
  );
}
