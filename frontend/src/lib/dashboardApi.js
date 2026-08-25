import { fetchOrdersFromApi } from './ordersApi.js';
import { fetchAuthWalletBalanceFromApi } from './userApi.js';
import { fetchPendingManualTopupRequests } from './walletApi.js';

/** Compose admin dashboard from available v2 list endpoints. */
export async function fetchDashboardFromApi(user) {
  if (!user?.token) return { ok: false, error: 'no_session' };

  try {
    const [ordersRes, balanceRes, pendingTopupsRes] = await Promise.all([
      fetchOrdersFromApi(user),
      fetchAuthWalletBalanceFromApi(user),
      fetchPendingManualTopupRequests(user),
    ]);

    if (!ordersRes.ok) {
      return {
        ok: false,
        message: ordersRes.message || 'Could not load dashboard data.',
        data: ordersRes.data,
      };
    }

    const recentOrders = [...ordersRes.orders]
      .sort((a, b) => {
        const ta = new Date(String(a.created_at || '').replace(' ', 'T')).getTime();
        const tb = new Date(String(b.created_at || '').replace(' ', 'T')).getTime();
        return tb - ta;
      })
      .slice(0, 8);

    const dashboard = {
      success: true,
      totalOrders: ordersRes.total,
      totalAmount: ordersRes.totalAmount,
      statusCounts: ordersRes.statusCounts,
      walletBalance: balanceRes.ok ? balanceRes.balance : null,
      pendingTopups: pendingTopupsRes.ok ? pendingTopupsRes.total : null,
      recentOrders,
    };

    return {
      ok: true,
      dashboard,
      recentOrders,
      data: dashboard,
    };
  } catch {
    return { ok: false, error: 'network' };
  }
}
