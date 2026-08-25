import { apiData, apiMessage, apiUrl, jsonHeaders, readApiJson, userAuthHeaders } from './apiConfig.js';
import { endpoints, withQuery } from './endpoints.js';

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
    const res = await fetch(apiUrl(endpoints.authMe()), {
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
    const res = await fetch(apiUrl(endpoints.notificationsMine()), {
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

/** Unread = filter client-side from my-notifications. */
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
    const res = await fetch(apiUrl(endpoints.notificationMarkRead(id)), {
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

/** POST /v2/notifications/{id}/mark-as-unread */
export async function markNotificationUnreadOnApi(user, notificationId) {
  const id = notificationId != null ? String(notificationId).trim() : '';
  if (!id) return { ok: false, error: 'bad_id' };

  const headers = userAuthHeaders(user);
  if (!headers) return { ok: false, error: 'no_session' };

  try {
    const res = await fetch(apiUrl(endpoints.notificationMarkUnread(id)), {
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
      return { ok: true, message: apiMessage(data, 'Marked as unread.'), data };
    }

    return { ok: false, message: apiMessage(data, 'Could not mark notification as unread.'), data };
  } catch {
    return { ok: false, error: 'network' };
  }
}

/** DELETE /v2/notifications/{id}/delete */
export async function deleteNotificationOnApi(user, notificationId) {
  const id = notificationId != null ? String(notificationId).trim() : '';
  if (!id) return { ok: false, error: 'bad_id' };

  const headers = userAuthHeaders(user);
  if (!headers) return { ok: false, error: 'no_session' };

  try {
    const res = await fetch(apiUrl(endpoints.notificationDelete(id)), {
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
      return { ok: true, message: apiMessage(data, 'Notification deleted.'), data };
    }

    return { ok: false, message: apiMessage(data, 'Could not delete notification.'), data };
  } catch {
    return { ok: false, error: 'network' };
  }
}

/** GET /v2/auth/user/{id} */
export async function fetchUserByIdFromApi(user, userId) {
  const headers = userAuthHeaders(user);
  if (!headers) return { ok: false, error: 'no_session' };

  const id = String(userId ?? '').trim();
  if (!id) return { ok: false, error: 'invalid_id' };

  try {
    const res = await fetch(apiUrl(endpoints.authUser(id)), {
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
 * GET /v2/auth/users — admin list.
 * Postman filters: `search`, `email`.
 */
export async function fetchUsersFromApi(user, page = 1, filters = {}) {
  const headers = userAuthHeaders(user);
  if (!headers) return { ok: false, error: 'no_session' };

  const pageNum = Math.max(1, parseInt(String(page), 10) || 1);
  const query = {
    search: filters.search,
    email: filters.email,
  };

  try {
    const res = await fetch(apiUrl(withQuery(endpoints.authUsers(), query)), {
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
      return { ok: false, message: apiMessage(data, 'Could not load users.'), data };
    }

    const nested = apiData(data);
    const users = Array.isArray(nested?.users)
      ? nested.users
      : Array.isArray(data?.users)
        ? data.users
        : [];
    const total =
      typeof nested?.count === 'number'
        ? nested.count
        : typeof data?.count === 'number'
          ? data.count
          : users.length;
    const perPage = 10;
    const lastPage = total === 0 ? 1 : Math.max(1, Math.ceil(total / perPage));

    return {
      ok: true,
      users,
      total,
      page: pageNum,
      perPage,
      lastPage,
      hasNext: pageNum < lastPage,
      hasPrev: pageNum > 1,
      data,
    };
  } catch {
    return { ok: false, error: 'network' };
  }
}

/** POST /v2/auth/create */
export async function createUserOnApi(user, payload) {
  const headers = userAuthHeaders(user);
  if (!headers) return { ok: false, error: 'no_session' };

  try {
    const res = await fetch(apiUrl(endpoints.authCreate()), {
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

/** POST /v2/auth/user/{id} */
export async function updateUserOnApi(user, userId, payload) {
  const headers = userAuthHeaders(user);
  if (!headers) return { ok: false, error: 'no_session' };

  const id = String(userId ?? '').trim();
  if (!id) return { ok: false, error: 'invalid_id' };

  try {
    const res = await fetch(apiUrl(endpoints.authUser(id)), {
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

/** DELETE /v2/auth/user/{id} */
export async function deleteUserOnApi(user, userId) {
  const headers = userAuthHeaders(user);
  if (!headers) return { ok: false, error: 'no_session' };

  const id = String(userId ?? '').trim();
  if (!id) return { ok: false, error: 'invalid_id' };

  try {
    const res = await fetch(apiUrl(endpoints.authUser(id)), {
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
      return { ok: true, user: userFromPayload(data), message: apiMessage(data, 'User deleted.'), data };
    }

    return { ok: false, message: apiMessage(data, 'Could not delete user.'), data };
  } catch {
    return { ok: false, error: 'network' };
  }
}

/** No dedicated admin password-reset route; use forgot/reset password. */
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
    const res = await fetch(apiUrl(endpoints.authLogout()), {
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

/** POST /v2/auth/me/change-password */
export async function changeMyPasswordOnApi(user, { currentPassword, newPassword, confirmPassword }) {
  const headers = userAuthHeaders(user);
  if (!headers) return { ok: false, error: 'no_session' };

  try {
    const res = await fetch(apiUrl(endpoints.authChangePassword()), {
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

/** POST /v2/auth/me */
export async function postMeOnApi(user, payload) {
  const baseHeaders = userAuthHeaders(user);
  if (!baseHeaders) return { ok: false, error: 'no_session' };

  try {
    const res = await fetch(apiUrl(endpoints.authMe()), {
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
    const res = await fetch(apiUrl(endpoints.authWalletBalance()), {
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
