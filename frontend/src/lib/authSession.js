const STORAGE_KEY = 'r2_auth_user';
const SETTINGS_STORAGE_KEY = 'r2_app_settings';

function sanitizeUser(raw) {
  if (!raw || typeof raw !== 'object') return null;
  return {
    id: raw.id,
    name: raw.name,
    email: raw.email,
    phone: raw.phone,
    wallet_balance: raw.wallet_balance,
    country_code: raw.country_code,
    social_security_number: raw.social_security_number,
    delivery_address: raw.delivery_address,
    role: raw.role,
    status: raw.status,
    agent_status: raw.agent_status,
    created_at: raw.created_at,
    token: raw.token,
  };
}

export function getStoredUser() {
  try {
    const s = localStorage.getItem(STORAGE_KEY);
    if (!s) return null;
    const parsed = JSON.parse(s);
    return sanitizeUser(parsed);
  } catch {
    return null;
  }
}

export function saveAuthUser(apiUser) {
  const user = sanitizeUser(apiUser);
  if (user) localStorage.setItem(STORAGE_KEY, JSON.stringify(user));
  return user;
}

/**
 * Refresh session token after order create. Do not replace the whole user from `order.user`:
 * some APIs return a mismatched `wallet_balance` there while login / wallet endpoints are correct.
 */
export function mergeSessionTokenFromOrderUser(orderUser) {
  const prev = getStoredUser();
  if (!prev || typeof orderUser?.token !== 'string' || !orderUser.token.length) return;
  saveAuthUser({ ...prev, token: orderUser.token });
}

/** Merge a new wallet balance into the saved session without dropping other fields. */
export function patchStoredUserWalletBalance(walletBalance) {
  const prev = getStoredUser();
  if (!prev || walletBalance === undefined) return;
  saveAuthUser({ ...prev, wallet_balance: walletBalance });
}

export function clearAuthUser() {
  localStorage.removeItem(STORAGE_KEY);
  localStorage.removeItem(SETTINGS_STORAGE_KEY);
}

/** After login, send admins to /admin (or prior admin URL); others to their limited order/profile/wallet area. */
export function resolvePostLoginPath(user, fromPathname) {
  const role = String(user?.role || '').toLowerCase();
  const isAdmin = role === 'admin';
  const from =
    typeof fromPathname === 'string' &&
    fromPathname.startsWith('/') &&
    fromPathname !== '/login'
      ? fromPathname
      : null;
  if (isAdmin) {
    if (from && from.startsWith('/admin')) return from;
    return '/admin';
  }
  if (from === '/profile' || from === '/create-order' || from === '/wallet' || from?.startsWith('/orders')) {
    return from;
  }
  return '/orders';
}
