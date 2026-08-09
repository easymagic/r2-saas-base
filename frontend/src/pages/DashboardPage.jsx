import { Link } from 'react-router-dom';
import { UserHeader } from '../components/layout/UserHeader.jsx';
import { Button } from '../components/ui/Button.jsx';
import { useSyncedWalletBalance } from '../hooks/useSyncedWalletBalance.js';
import { getStoredUser } from '../lib/authSession.js';
import { initialsFromName } from '../lib/userDisplay.js';

function LimitedDashboard({ user }) {
  const actions = [
    {
      title: 'Orders',
      body: 'View your order history and fulfillment status.',
      to: '/orders',
      cta: 'View orders',
    },
    {
      title: 'Profile',
      body: 'Update your name, email, and phone details.',
      to: '/profile',
      cta: 'Edit profile',
    },
    {
      title: 'Create order',
      body: 'Submit a new product request for fulfillment.',
      to: '/create-order',
      cta: 'Create order',
    },
    {
      title: 'Wallet',
      body: 'Top up your wallet and view transaction activity.',
      to: '/wallet',
      cta: 'Top up wallet',
    },
  ];

  return (
    <>
      <UserHeader title="Dashboard" subtitle="Your orders, profile, and wallet" />
      <main className="flex-1 p-4 sm:p-6 lg:p-8">
        <section className="grid gap-4 sm:grid-cols-2">
          {actions.map((item) => (
            <Link
              key={item.to}
              to={item.to}
              className="rounded-2xl bg-white p-6 shadow-md transition hover:bg-orange-50/40 focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-500"
            >
              <h2 className="text-base font-semibold text-gray-900">{item.title}</h2>
              <p className="mt-2 text-sm text-gray-600">{item.body}</p>
              <span className="mt-4 inline-block text-sm font-semibold text-orange-600">{item.cta}</span>
            </Link>
          ))}
        </section>
        <section className="mt-6 rounded-2xl bg-white p-6 shadow-md">
          <p className="text-sm text-gray-500">
            Signed in as <span className="font-medium text-gray-900">{user?.name || user?.email || 'User'}</span>.
          </p>
        </section>
      </main>
    </>
  );
}

function AdminDashboardLanding({ user }) {
  const [balanceLabel] = useSyncedWalletBalance();

  return (
    <>
      <UserHeader
        title="Dashboard"
        subtitle="Wallet, shortcuts, and recent orders"
        right={
          <>
            <span className="hidden rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600 sm:inline">
              Balance: {balanceLabel}
            </span>
            <Link
              to="/profile"
              className="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-900 text-xs font-semibold text-white ring-2 ring-transparent transition hover:ring-orange-400/50 focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-500"
              aria-label="Edit profile"
              title="My profile"
            >
              {initialsFromName(user?.name)}
            </Link>
          </>
        }
      />
      <main className="flex-1 space-y-6 p-4 sm:p-6 lg:p-8">
        <section className="rounded-2xl bg-blue-900 p-6 text-white shadow-lg sm:p-8" aria-labelledby="wallet-heading">
          <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
              <p id="wallet-heading" className="text-sm font-medium text-blue-200">
                Wallet balance
              </p>
              <p className="mt-2 text-3xl font-semibold tracking-tight sm:text-4xl">{balanceLabel}</p>
              <p className="mt-2 text-sm text-blue-200">
                This is the money available for operational wallet activity.
              </p>
            </div>
            <div className="flex flex-wrap gap-3">
              <Button as={Link} to="/create-order" variant="orange" className="focus:ring-offset-blue-900">
                Create order
              </Button>
              <Button as={Link} to="/wallet" variant="ghost" className="focus:ring-offset-2 focus:ring-offset-blue-900">
                Top-up wallet
              </Button>
            </div>
          </div>
        </section>

        <section className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4" aria-label="Quick actions">
          {[
            { title: 'Create order', body: 'Add a product request for fulfillment.', to: '/create-order', cta: 'Create order' },
            { title: 'Orders', body: 'Search, filter, assign, and open orders.', to: '/orders', cta: 'View orders' },
            { title: 'Top-up approvals', body: 'Review pending manual wallet credits.', to: '/admin/topups', cta: 'Review queue' },
            { title: 'Users', body: 'Manage platform accounts and agents.', to: '/admin/users', cta: 'Manage users' },
          ].map((item) => (
            <Link
              key={item.to}
              to={item.to}
              className="rounded-2xl bg-white p-6 shadow-md transition hover:bg-orange-50/40 focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-500"
            >
              <h2 className="text-sm font-semibold text-gray-900">{item.title}</h2>
              <p className="mt-2 text-sm text-gray-600">{item.body}</p>
              <span className="mt-4 inline-block text-sm font-semibold text-orange-600">{item.cta}</span>
            </Link>
          ))}
        </section>
      </main>
    </>
  );
}

export function DashboardPage() {
  const user = getStoredUser();
  const isAdmin = String(user?.role || '').toLowerCase() === 'admin';

  if (!isAdmin) {
    return <LimitedDashboard user={user} />;
  }

  return <AdminDashboardLanding user={user} />;
}
