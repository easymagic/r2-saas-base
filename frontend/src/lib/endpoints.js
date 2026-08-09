/**
 * API paths from `api/postman/R2-SaaS-Base-API.postman_collection.json`.
 * Prefer these helpers over hard-coded strings in fetch calls.
 */

export const endpoints = {
  // Auth (Public)
  authLogin: () => '/v2/auth/login',
  authRegister: () => '/v2/auth/register',
  authForgotPassword: () => '/v2/auth/user/forgot-password',
  authResetPassword: () => '/v2/auth/user/reset-password',
  authVerifyEmail: () => '/v2/auth/user/verify-email',

  // Auth (Authenticated)
  authCreate: () => '/v2/auth/create',
  authUser: (userId) => `/v2/auth/user/${encodeURIComponent(userId)}`,
  authMe: () => '/v2/auth/me',
  authChangePassword: () => '/v2/auth/me/change-password',
  authWalletBalance: () => '/v2/auth/me/wallet-balance',
  authLogout: () => '/v2/auth/login',

  // Auth (Admin)
  authUsers: () => '/v2/auth/users',

  // Wallet
  walletTopUpOnline: () => '/v2/wallet/top-up-online',
  walletTopUpManual: () => '/v2/wallet/top-up-manual',
  walletApproveManual: (walletId) =>
    `/v2/wallet/${encodeURIComponent(walletId)}/approve-manual-top-up`,
  walletRejectManual: (walletId) =>
    `/v2/wallet/${encodeURIComponent(walletId)}/reject-manual-top-up`,
  walletMyPending: () => '/v2/wallet/my-pending-wallet-transactions',
  walletMyApproved: () => '/v2/wallet/my-approved-wallet-transactions',
  walletManualPending: () => '/v2/wallet/manual-pending-wallet-transactions',
  walletManualApproved: () => '/v2/wallet/manual-approved-wallet-transactions',
  walletManualRejected: () => '/v2/wallet/manual-rejected-wallet-transactions',

  // Notifications
  notificationsMine: () => '/v2/notifications/my-notifications',
  notificationMarkRead: (id) =>
    `/v2/notifications/${encodeURIComponent(id)}/mark-as-read`,
  notificationMarkUnread: (id) =>
    `/v2/notifications/${encodeURIComponent(id)}/mark-as-unread`,
  notificationDelete: (id) =>
    `/v2/notifications/${encodeURIComponent(id)}/delete`,

  // Platform config
  platformConfigs: () => '/v2/platform-configs',
  platformConfigsUpdate: () => '/v2/platform-configs/update',
  platformConfigDelete: (id) =>
    `/v2/platform-configs/${encodeURIComponent(id)}/delete`,

  // Batches
  batches: () => '/v2/batches',
  batch: (batchId) => `/v2/batches/${encodeURIComponent(batchId)}`,

  // Threads
  threads: () => '/v2/threads',
  threadsForOrder: (orderId) => `/v2/threads/${encodeURIComponent(orderId)}`,

  // Snappy orders
  snappyOrders: () => '/v2/snappy-orders',
  snappyMyOrders: () => '/v2/snappy-orders/my-orders',
  snappyAgentOrders: () => '/v2/snappy-orders/agent-orders',
  snappyAdminOrders: () => '/v2/snappy-orders/admin-orders',
  snappyPayFromWallet: (orderId) =>
    `/v2/snappy-orders/${encodeURIComponent(orderId)}/pay-from-wallet`,
  snappyChangeStatus: (orderId) =>
    `/v2/snappy-orders/${encodeURIComponent(orderId)}/change-status`,
  snappyAssignAgent: (orderId) =>
    `/v2/snappy-orders/${encodeURIComponent(orderId)}/assign-to-agent`,
  snappyAssignBatch: (orderId) =>
    `/v2/snappy-orders/${encodeURIComponent(orderId)}/assign-to-batch`,
  snappyUnassignBatch: (orderId) =>
    `/v2/snappy-orders/${encodeURIComponent(orderId)}/unassign-from-batch`,
  snappyPublishSettings: () => '/v2/snappy-orders/publish-settings',

  // Migrations
  migrateUsers: () => '/v2/migrate',
  migrateWallet: () => '/v2/wallet/migrate',
  migrateNotifications: () => '/v2/notifications/migrate',
  migratePlatformConfigs: () => '/v2/platform-configs/migrate',
  migrateSnappyOrders: () => '/v2/snappy-orders/migrate',
  migrateBatches: () => '/v2/batches/migrate',
  migrateThreads: () => '/v2/threads/migrate',
  migrateProxyOrderChangeLogs: () => '/v2/proxy-order-change-logs/migrate',
  migrateLogs: () => '/v2/logs/migrate',
  logs: () => '/v2/logs',
};

export function withQuery(path, params = {}) {
  const qs = new URLSearchParams();
  for (const [key, value] of Object.entries(params || {})) {
    if (value == null || value === '') continue;
    qs.set(key, String(value));
  }
  const q = qs.toString();
  return q ? `${path}?${q}` : path;
}
