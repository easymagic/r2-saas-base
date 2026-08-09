import { useCallback, useEffect, useState } from 'react';
import { Link, useLocation, useNavigate, useOutletContext } from 'react-router-dom';
import { clearAuthUser, getStoredUser } from '../../lib/authSession.js';
import {
  fetchUnreadNotificationsFromApi,
  logoutFromApi,
  NOTIFICATIONS_CHANGED_EVENT,
} from '../../lib/userApi.js';
import { Button } from '../ui/Button.jsx';

function HeaderNotificationsBell() {
  const { pathname } = useLocation();
  const [unreadCount, setUnreadCount] = useState(0);

  useEffect(() => {
    const u = getStoredUser();
    if (!u?.token || u.id == null) {
      setUnreadCount(0);
      return;
    }
    let cancelled = false;

    function refreshUnread() {
      fetchUnreadNotificationsFromApi(u).then((r) => {
        if (cancelled || !r.ok) return;
        setUnreadCount(Array.isArray(r.notifications) ? r.notifications.length : 0);
      });
    }

    refreshUnread();
    window.addEventListener(NOTIFICATIONS_CHANGED_EVENT, refreshUnread);
    return () => {
      cancelled = true;
      window.removeEventListener(NOTIFICATIONS_CHANGED_EVENT, refreshUnread);
    };
  }, [pathname]);

  const label =
    unreadCount > 0 ? `Notifications, ${unreadCount} unread` : 'Notifications';

  return (
    <span className="relative inline-flex shrink-0">
      <Link
        to="/notifications"
        className="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-700 transition hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-500"
        aria-label={label}
        title="Notifications"
      >
        <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" aria-hidden>
          <path
            strokeLinecap="round"
            strokeLinejoin="round"
            d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"
          />
        </svg>
      </Link>
      {unreadCount > 0 ? (
        <span className="absolute -right-0.5 -top-0.5 flex h-[18px] min-w-[18px] items-center justify-center rounded-full bg-orange-500 px-1 text-[10px] font-bold leading-none text-white ring-2 ring-white">
          {unreadCount > 9 ? '9+' : unreadCount}
        </span>
      ) : null}
    </span>
  );
}

export function UserHeader({ title, subtitle, right, backTo, backLabel = 'Back' }) {
  const ctx = useOutletContext() || {};
  const openMobileNav = ctx.openMobileNav;
  const { pathname } = useLocation();
  const navigate = useNavigate();
  const isAdminArea = pathname.startsWith('/admin');
  const showProfileCta = pathname !== '/profile';
  const showTopUpCta = pathname !== '/wallet';
  const [loggingOut, setLoggingOut] = useState(false);

  const handleLogout = useCallback(async () => {
    setLoggingOut(true);
    const u = getStoredUser();
    if (u?.token) {
      await logoutFromApi(u);
    }
    clearAuthUser();
    navigate('/login', { replace: true });
  }, [navigate]);

  return (
    <header className="sticky top-0 z-30 flex h-16 items-center justify-between gap-4 border-b border-gray-200 bg-white/95 px-4 backdrop-blur sm:px-6 lg:px-8">
      <div className="flex min-w-0 items-center gap-3">
        {backTo ? (
          <Link
            to={backTo}
            className="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-700 transition hover:bg-gray-50"
            aria-label={backLabel}
            title={backLabel}
          >
            <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" aria-hidden>
              <path strokeLinecap="round" strokeLinejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
            </svg>
          </Link>
        ) : null}
        {openMobileNav && (
          <button
            type="button"
            onClick={openMobileNav}
            className="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white p-2 text-gray-700 hover:bg-gray-50 lg:hidden"
          >
            <span className="sr-only">Open menu</span>
            <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" aria-hidden>
              <path strokeLinecap="round" strokeLinejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
          </button>
        )}
        <div className="min-w-0">
          <h1 className="truncate text-lg font-semibold text-gray-900">{title}</h1>
          {subtitle && (
            <p className="hidden truncate text-sm text-gray-500 sm:block">{subtitle}</p>
          )}
        </div>
      </div>
      <div className="flex max-w-[min(100%,28rem)] shrink-0 flex-wrap items-center justify-end gap-2 sm:max-w-none sm:gap-3">
        <HeaderNotificationsBell />
        {isAdminArea ? (
          <span className="rounded-full bg-orange-100 px-3 py-1 text-xs font-medium text-orange-800">
            Admin
          </span>
        ) : null}
        {showProfileCta ? (
          <Button as={Link} to="/profile" variant="orange" className="px-3 py-1.5 text-xs sm:text-sm">
            My profile
          </Button>
        ) : null}
        {showTopUpCta ? (
          <Button as={Link} to="/wallet" variant="secondary" className="px-3 py-1.5 text-xs sm:text-sm">
            Top up
          </Button>
        ) : null}
        {right}
        <Button
          type="button"
          variant="secondary"
          className="px-3 py-1.5 text-xs sm:text-sm"
          disabled={loggingOut}
          onClick={handleLogout}
        >
          {loggingOut ? 'Signing out…' : 'Sign out'}
        </Button>
      </div>
    </header>
  );
}
