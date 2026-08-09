import { useCallback, useEffect, useMemo, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { UserHeader } from '../components/layout/UserHeader.jsx';
import { Badge } from '../components/ui/Badge.jsx';
import { Button } from '../components/ui/Button.jsx';
import { Card } from '../components/ui/Card.jsx';
import { getStoredUser } from '../lib/authSession.js';
import { fetchDashboardFromApi } from '../lib/dashboardApi.js';
import { formatNaira } from '../lib/userDisplay.js';

const ORDER_STATUS_METRICS = [
  { key: 'pending', label: 'Pending', to: '/orders?status=pending' },
  { key: 'paid', label: 'Paid', to: '/orders?status=paid' },
  { key: 'assigned', label: 'Assigned', to: '/orders?status=assigned' },
  { key: 'completed', label: 'Completed', to: '/orders?status=completed' },
  { key: 'cancelled', label: 'Cancelled', to: '/orders?status=cancelled' },
];

function dashboardNumber(value) {
  if (value == null || value === '') return 0;
  const n = typeof value === 'number' ? value : Number(String(value).replace(/,/g, '').trim());
  return Number.isFinite(n) ? n : 0;
}

function formatCount(value) {
  return dashboardNumber(value).toLocaleString('en-NG', { maximumFractionDigits: 0 });
}

function formatOrderDate(value) {
  if (value == null || value === '') return '—';
  const d = new Date(String(value).replace(' ', 'T'));
  if (Number.isNaN(d.getTime())) return String(value);
  return d.toLocaleString('en-NG', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}

function formatOrderStatusLabel(status) {
  const s = String(status || '').trim();
  if (!s) return '—';
  return s
    .split(/[-_]/)
    .filter(Boolean)
    .map((w) => w.charAt(0).toUpperCase() + w.slice(1).toLowerCase())
    .join(' ');
}

function orderStatusBadgeVariant(status) {
  const s = String(status || '').toLowerCase();
  if (s === 'pending') return 'pending';
  if (s === 'completed') return 'delivered';
  if (s === 'paid' || s === 'assigned') return 'approved';
  if (s === 'cancelled') return 'rejected';
  return 'default';
}

function orderAmountDisplay(order) {
  const raw = order?.grand_total_naira ?? order?.total_amount_usd ?? order?.total_amount;
  return formatNaira(raw);
}

function customerLabel(order) {
  const user = order?.user;
  if (user?.name) return String(user.name);
  if (user?.email) return String(user.email);
  if (order?.user_id != null) return `User #${order.user_id}`;
  return '—';
}

export function AdminDashboardPage() {
  const navigate = useNavigate();
  const [dashboard, setDashboard] = useState(null);
  const [recentOrders, setRecentOrders] = useState([]);
  const [loading, setLoading] = useState(true);
  const [loadError, setLoadError] = useState('');

  const loadDashboard = useCallback(async () => {
    const u = getStoredUser();
    if (!u?.token || u.id == null) {
      navigate('/login', { replace: true });
      return;
    }

    setLoading(true);
    setLoadError('');
    const r = await fetchDashboardFromApi(u);
    setLoading(false);

    if (!r.ok) {
      setLoadError(
        typeof r.message === 'string' && r.message.length > 0
          ? r.message
          : 'Could not load dashboard data.'
      );
      setDashboard(null);
      setRecentOrders([]);
      return;
    }

    setDashboard(r.dashboard);
    setRecentOrders(r.recentOrders);
  }, [navigate]);

  useEffect(() => {
    loadDashboard();
  }, [loadDashboard]);

  const topMetrics = useMemo(
    () => [
      { label: 'Total orders', value: dashboard?.totalOrders, to: '/orders' },
      {
        label: 'Pending top-ups',
        value: dashboard?.pendingTopups,
        tone: 'orange',
        to: '/admin/topups',
      },
      {
        label: 'Wallet balance',
        value: dashboard?.walletBalance,
        tone: 'green',
        to: '/wallet',
      },
      {
        label: 'Order volume (₦)',
        value: dashboard?.totalAmount,
        to: '/orders',
      },
    ],
    [dashboard]
  );

  return (
    <>
      <UserHeader title="Dashboard" subtitle="Live platform activity and fulfillment queues" />
      <main className="flex-1 space-y-6 p-4 sm:p-6 lg:p-8">
        {loadError ? (
          <Card className="border border-red-200 bg-red-50">
            <p className="text-sm font-medium text-red-700">{loadError}</p>
            <Button type="button" variant="secondary" className="mt-3" onClick={loadDashboard}>
              Retry
            </Button>
          </Card>
        ) : null}

        <section className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4" aria-label="Dashboard totals">
          {topMetrics.map((metric) => (
            <Link
              key={metric.label}
              to={metric.to}
              className="block rounded-lg border border-gray-200 bg-gray-50 p-4 shadow-sm transition hover:border-orange-200 hover:bg-orange-50/40 hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-500"
              aria-label={`Open ${metric.label}`}
            >
              <p className="text-xs font-medium uppercase tracking-wide text-gray-500">{metric.label}</p>
              <p
                className={
                  metric.tone === 'orange'
                    ? 'mt-2 text-2xl font-semibold tabular-nums text-orange-600'
                    : metric.tone === 'green'
                      ? 'mt-2 text-2xl font-semibold tabular-nums text-green-700'
                      : 'mt-2 text-2xl font-semibold tabular-nums text-gray-900'
                }
              >
                {loading ? '—' : formatCount(metric.value)}
              </p>
              <span className="mt-2 inline-block text-xs font-medium text-orange-600">Open</span>
            </Link>
          ))}
        </section>

        <section className="rounded-2xl bg-white p-6 shadow-md" aria-labelledby="order-status-overview">
          <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <h2 id="order-status-overview" className="text-base font-semibold text-gray-900">
                Order status overview
              </h2>
              <p className="mt-1 text-sm text-gray-500">Counts from admin snappy orders.</p>
            </div>
            <Button as={Link} to="/orders" variant="secondary">
              View orders
            </Button>
          </div>
          <div className="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6">
            {ORDER_STATUS_METRICS.map((metric) => (
              <Link
                key={metric.key}
                to={metric.to}
                className="block rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 transition hover:border-orange-200 hover:bg-orange-50/40 focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-500"
                aria-label={`Open ${metric.label} orders`}
              >
                <p className="text-xs font-medium uppercase tracking-wide text-gray-500">{metric.label}</p>
                <p className="mt-1 text-xl font-semibold tabular-nums text-gray-900">
                  {loading ? '—' : formatCount(dashboard?.statusCounts?.[metric.key])}
                </p>
              </Link>
            ))}
          </div>
        </section>

        <Card className="overflow-hidden p-0">
          <div className="flex flex-col gap-3 border-b border-gray-100 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <h2 className="text-base font-semibold text-gray-900">Recent orders</h2>
              <p className="mt-1 text-sm text-gray-500">
                Latest orders returned by the dashboard endpoint.
              </p>
            </div>
            <Link to="/orders" className="text-sm font-medium text-orange-600 hover:text-orange-700">
              View all
            </Link>
          </div>
          {loading ? (
            <p className="px-6 py-5 text-sm text-gray-500" aria-busy="true">
              Loading dashboard…
            </p>
          ) : recentOrders.length === 0 ? (
            <p className="px-6 py-5 text-sm text-gray-500">No recent orders.</p>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full min-w-[48rem] border-collapse text-left text-sm">
                <thead>
                  <tr className="bg-gray-100 text-xs font-semibold uppercase tracking-wide text-gray-600">
                    <th className="px-4 py-3" scope="col">
                      Order
                    </th>
                    <th className="px-4 py-3" scope="col">
                      Customer
                    </th>
                    <th className="px-4 py-3" scope="col">
                      Status
                    </th>
                    <th className="px-4 py-3 text-right" scope="col">
                      Amount
                    </th>
                    <th className="px-4 py-3" scope="col">
                      Date
                    </th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                  {recentOrders.map((order) => {
                    const orderPath = `/orders/${order.id}`;
                    return (
                      <tr key={order.id} className="hover:bg-gray-50">
                        <td className="px-4 py-3">
                          <Link to={orderPath} className="font-medium text-blue-900 hover:underline">
                            #{order.id}
                          </Link>
                          {order.reference ? (
                            <p className="mt-0.5 max-w-[12rem] truncate font-mono text-xs text-gray-500" title={String(order.reference)}>
                              {String(order.reference)}
                            </p>
                          ) : null}
                        </td>
                        <td className="max-w-[14rem] truncate px-4 py-3 text-gray-700" title={customerLabel(order)}>
                          {customerLabel(order)}
                        </td>
                        <td className="px-4 py-3">
                          <Badge variant={orderStatusBadgeVariant(order.status)}>
                            {formatOrderStatusLabel(order.status)}
                          </Badge>
                        </td>
                        <td className="px-4 py-3 text-right tabular-nums font-medium text-gray-900">
                          {orderAmountDisplay(order)}
                        </td>
                        <td className="whitespace-nowrap px-4 py-3 text-gray-600">
                          {formatOrderDate(order.created_at)}
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>
          )}
        </Card>
      </main>
    </>
  );
}
