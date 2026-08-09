import { apiData, apiMessage, apiUrl, jsonHeaders, readApiJson, userAuthHeaders } from './apiConfig.js';

function userFromPayload(data) {
  const nested = apiData(data);
  return nested?.user ?? data?.user ?? null;
}

function notificationsFromPayload(data) {
  const nested = apiData(data);
  if (Array.isArray(nested?.notifications)) return nested.notifications;
  if (Array.isArray(data?.notifications)) return data.notifications;
  return [];
}

export async function fetchMeFromApi(user) {
  const headers = userAuthHeaders(user);
  if (!headers) return { ok: false, error: 'no_session' };

  try {
    const res = await fetch(apiUrl('/v2/auth/me'), {
      method: 'GET',
      headers,
      credentials: 'include',
    });
    let data = null;
    try {
      data = await readApiJson(res);
    } catch {
      return { ok: false, error: 'bad_json' };
    }

    const apiUser = userFromPayload(data);
    if (data?.success && apiUser) {
      return { ok: true, user: apiUser, data };
    }

    return { ok: false, message: apiMessage(data, 'Could not load profile.'), data };
  } catch {
    return { ok: false, error: 'network' };
  }
}

/** GET /v2/notifications/my-notifications */
export async function fetchNotificationsFromApi(user) {
  const headers = userAuthHeaders(user);
  if (!headers) return { ok: false, error: 'no_session' };

  try {
    const res = await fetch(apiUrl('/v2/notifications/my-notifications'), {
      method: 'GET',
      headers,
      credentials: 'include',
    });
    let data = null;
    try {
      data = await readApiJson(res);
    } catch {
      return { ok: false, error: 'bad_json' };
    }

    if (!data?.success) {
      return { ok: false, message: apiMessage(data, 'Could not load notifications.'), data };
    }

    return { ok: true, notifications: notificationsFromPayload(data), data };
  } catch {
    return { ok: false, error: 'network' };
  }
}

/** Unread = filter client-side (no dedicated unread route). */
export async function fetchUnreadNotificationsFromApi(user) {
  const r = await fetchNotificationsFromApi(user);
  if (!r.ok) return r;
  const notifications = r.notifications.filter((n) => {
    if (n?.is_read === true || n?.is_read === 1 || n?.is_read === '1') return false;
    if (n?.read_at) return false;
    return true;
  });
  return { ok: true, notifications, data: r.data };
}

export const NOTIFICATIONS_CHANGED_EVENT = 'r2-notifications-changed';

export function notifyNotificationsChanged() {
  if (typeof window !== 'undefined') {
    window.dispatchEvent(new Event(NOTIFICATIONS_CHANGED_EVENT));
  }
}

/** POST /v2/notifications/{id}/mark-as-read */
export async function markNotificationReadOnApi(user, notificationId) {
  const id = notificationId != null ? String(notificationId).trim() : '';
  if (!id) return { ok: false, error: 'bad_id' };

  const headers = userAuthHeaders(user);
  if (!headers) return { ok: false, error: 'no_session' };

  try {
    const res = await fetch(apiUrl(`/v2/notifications/${encodeURIComponent(id)}/mark-as-read`), {
      method: 'POST',
      headers,
      credentials: 'include',
    });
    let data = null;
    try {
      data = await readApiJson(res);
    } catch {
      return { ok: false, error: 'bad_json' };
    }

    if (data?.success) {
      return { ok: true, message: apiMessage(data, 'Marked as read.'), data };
    }

    return { ok: false, message: apiMessage(data, 'Could not mark notification as read.'), data };
  } catch {
    return { ok: false, error: 'network' };
  }
}

/** GET /v2/auth/user/{id} — single-user lookup (no list route in v2). */
export async function fetchUserByIdFromApi(user, userId) {
  const headers = userAuthHeaders(user);
  if (!headers) return { ok: false, error: 'no_session' };

  const id = String(userId ?? '').trim();
  if (!id) return { ok: false, error: 'invalid_id' };

  try {
    const res = await fetch(apiUrl(`/v2/auth/user/${encodeURIComponent(id)}`), {
      method: 'GET',
      headers,
      credentials: 'include',
    });
    let data = null;
    try {
      data = await readApiJson(res);
    } catch {
      return { ok: false, error: 'bad_json' };
    }

    const apiUser = userFromPayload(data);
    if (data?.success && apiUser) {
      return { ok: true, user: apiUser, data };
    }

    return { ok: false, message: apiMessage(data, 'Could not load user.'), data };
  } catch {
    return { ok: false, error: 'network' };
  }
}

/**
 * List users is not exposed on v2; keep a stub that returns empty so pages can fall back to lookup-by-id.
 */
export async function fetchUsersFromApi(user, page = 1) {
  const headers = userAuthHeaders(user);
  if (!headers) return { ok: false, error: 'no_session' };
  return {
    ok: true,
    users: [],
    total: 0,
    page: Math.max(1, parseInt(String(page), 10) || 1),
    perPage: 20,
    lastPage: 1,
    hasNext: false,
    hasPrev: false,
    data: { success: true, data: { users: [], count: 0 } },
    lookupOnly: true,
  };
}

/** POST /v2/auth/create — admin create user (JSON). */
export async function createUserOnApi(user, payload) {
  const headers = userAuthHeaders(user);
  if (!headers) return { ok: false, error: 'no_session' };

  try {
    const res = await fetch(apiUrl('/v2/auth/create'), {
      method: 'POST',
      headers: jsonHeaders(headers),
      credentials: 'include',
      body: JSON.stringify({
        name: payload.name != null ? String(payload.name).trim() : '',
        email: payload.email != null ? String(payload.email).trim() : '',
        phone: payload.phone != null ? String(payload.phone).trim() : '',
        country_code: payload.country_code != null ? String(payload.country_code).trim() : '',
        role: payload.role != null ? String(payload.role).trim() : 'customer',
        status: payload.status != null ? String(payload.status).trim() : 'active',
        password: payload.password != null ? String(payload.password) : '',
        delivery_address: payload.delivery_address != null ? String(payload.delivery_address).trim() : '',
        social_security_number:
          payload.social_security_number != null ? String(payload.social_security_number).trim() : '',
      }),
    });
    let data = null;
    try {
      data = await readApiJson(res);
    } catch {
      return { ok: false, error: 'bad_json' };
    }

    const apiUser = userFromPayload(data);
    if (data?.success && apiUser) {
      return { ok: true, user: apiUser, data };
    }

    return { ok: false, message: apiMessage(data, 'Could not create user.'), data };
  } catch {
    return { ok: false, error: 'network' };
  }
}

/** POST /v2/auth/user/{id} — admin update user (JSON). */
export async function updateUserOnApi(user, userId, payload) {
  const headers = userAuthHeaders(user);
  if (!headers) return { ok: false, error: 'no_session' };

  const id = String(userId ?? '').trim();
  if (!id) return { ok: false, error: 'invalid_id' };

  try {
    const res = await fetch(apiUrl(`/v2/auth/user/${encodeURIComponent(id)}`), {
      method: 'POST',
      headers: jsonHeaders(headers),
      credentials: 'include',
      body: JSON.stringify({
        name: payload.name != null ? String(payload.name).trim() : '',
        phone: payload.phone != null ? String(payload.phone).trim() : '',
        role: payload.role != null ? String(payload.role).trim() : '',
        status: payload.status != null ? String(payload.status).trim() : '',
        delivery_address: payload.delivery_address != null ? String(payload.delivery_address).trim() : '',
        social_security_number:
          payload.social_security_number != null ? String(payload.social_security_number).trim() : '',
        country_code: payload.country_code != null ? String(payload.country_code).trim() : '',
      }),
    });
    let data = null;
    try {
      data = await readApiJson(res);
    } catch {
      return { ok: false, error: 'bad_json' };
    }

    const apiUser = userFromPayload(data);
    if (data?.success && apiUser) {
      return { ok: true, user: apiUser, data };
    }

    return { ok: false, message: apiMessage(data, 'Could not update user.'), data };
  } catch {
    return { ok: false, error: 'network' };
  }
}

/** No dedicated admin password-reset route; update status/fields only. */
export async function changeUserPasswordOnApi() {
  return {
    ok: false,
    message: 'Admin password reset is not available on this API. Ask the user to use forgot-password.',
  };
}

/** DELETE /v2/auth/login */
export async function logoutFromApi(user) {
  const headers = userAuthHeaders(user);
  if (!headers) return { ok: false, error: 'no_session' };

  try {
    const res = await fetch(apiUrl('/v2/auth/login'), {
      method: 'DELETE',
      headers,
      credentials: 'include',
    });
    let data = null;
    try {
      data = await readApiJson(res);
    } catch {
      return { ok: false, error: 'bad_json' };
    }

    if (data?.success) {
      return { ok: true, message: apiMessage(data, 'Logged out.'), data };
    }

    return { ok: false, message: apiMessage(data, 'Could not log out.'), data };
  } catch {
    return { ok: false, error: 'network' };
  }
}

/** POST /v2/auth/me/change-password — JSON: old_password, new_password, confirm_password. */
export async function changeMyPasswordOnApi(user, { currentPassword, newPassword, confirmPassword }) {
  const headers = userAuthHeaders(user);
  if (!headers) return { ok: false, error: 'no_session' };

  try {
    const res = await fetch(apiUrl('/v2/auth/me/change-password'), {
      method: 'POST',
      headers: jsonHeaders(headers),
      credentials: 'include',
      body: JSON.stringify({
        old_password: String(currentPassword ?? ''),
        new_password: String(newPassword ?? ''),
        confirm_password: String(confirmPassword ?? ''),
      }),
    });
    let data = null;
    try {
      data = await readApiJson(res);
    } catch {
      return { ok: false, error: 'bad_json' };
    }

    if (data?.success) {
      return { ok: true, message: apiMessage(data, 'Password changed.'), data };
    }

    return { ok: false, message: apiMessage(data, 'Could not change password.'), data };
  } catch {
    return { ok: false, error: 'network' };
  }
}

/** POST /v2/auth/me — JSON: name, phone, delivery_address. */
export async function postMeOnApi(user, payload) {
  const baseHeaders = userAuthHeaders(user);
  if (!baseHeaders) return { ok: false, error: 'no_session' };

  try {
    const res = await fetch(apiUrl('/v2/auth/me'), {
      method: 'POST',
      headers: jsonHeaders(baseHeaders),
      credentials: 'include',
      body: JSON.stringify({
        name: payload.name != null ? String(payload.name) : '',
        phone: payload.phone != null ? String(payload.phone) : '',
        delivery_address: payload.delivery_address != null ? String(payload.delivery_address) : '',
      }),
    });
    let data = null;
    try {
      data = await readApiJson(res);
    } catch {
      return { ok: false, error: 'bad_json' };
    }

    const apiUser = userFromPayload(data);
    if (data?.success && apiUser) {
      return { ok: true, user: apiUser, data };
    }

    return { ok: false, message: apiMessage(data, 'Could not save profile.'), data };
  } catch {
    return { ok: false, error: 'network' };
  }
}

/** POST /v2/auth/me/wallet-balance */
export async function fetchAuthWalletBalanceFromApi(user) {
  const headers = userAuthHeaders(user);
  if (!headers) return { ok: false, error: 'no_session' };

  try {
    const res = await fetch(apiUrl('/v2/auth/me/wallet-balance'), {
      method: 'POST',
      headers,
      credentials: 'include',
    });
    let data = null;
    try {
      data = await readApiJson(res);
    } catch {
      return { ok: false, error: 'bad_json' };
    }

    const nested = apiData(data);
    const balance = nested?.balance ?? data?.balance;
    if (data?.success && balance != null && balance !== '') {
      return { ok: true, balance, data };
    }

    return { ok: false, message: apiMessage(data, 'Could not load wallet balance.'), data };
  } catch {
    return { ok: false, error: 'network' };
  }
}
