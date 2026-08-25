import { clearAuthUser } from './authSession.js';

function normalizeBaseUrl(url) {
  if (typeof url !== 'string') return '';
  return url.trim().replace(/\/+$/, '');
}

/** API origin from API_BASE_URL (preferred) or VITE_API_BASE_URL. Empty = same-origin. */
const apiBaseUrl = normalizeBaseUrl(
  import.meta.env.API_BASE_URL || import.meta.env.VITE_API_BASE_URL || ''
);
let unauthorizedRedirectStarted = false;

export function apiUrl(path) {
  const p = path.startsWith('/') ? path : `/${path}`;
  return apiBaseUrl ? `${apiBaseUrl}${p}` : p;
}

export async function readApiJson(res) {
  const text = await res.text();
  if (res?.status === 401) {
    resetSessionAndRedirectToLogin();
  }
  try {
    return JSON.parse(text);
  } catch {
    const start = text.indexOf('{');
    const end = text.lastIndexOf('}');
    if (start >= 0 && end > start) {
      return JSON.parse(text.slice(start, end + 1));
    }
    throw new Error('bad_json');
  }
}

/** Nested success payload: `{ success, data: { … } }` with legacy flat fallback. */
export function apiData(payload) {
  if (payload == null || typeof payload !== 'object') return null;
  if (payload.data != null && typeof payload.data === 'object' && !Array.isArray(payload.data)) {
    return payload.data;
  }
  return payload;
}

export function apiMessage(payload, fallback = 'Request failed.') {
  const nested = apiData(payload);
  const msg = nested?.message ?? payload?.message;
  return typeof msg === 'string' && msg.length > 0 ? msg : fallback;
}

export function resetSessionAndRedirectToLogin() {
  clearAuthUser();

  if (typeof document !== 'undefined') {
    const base = import.meta.env.BASE_URL || '/';
    const basePath = base.endsWith('/') ? base.slice(0, -1) : base;
    const cookieOptions = [
      'PHPSESSID=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/',
      'PHPSESSID=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/; SameSite=Lax',
      basePath ? `PHPSESSID=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=${basePath}` : '',
    ];
    for (const cookie of cookieOptions) {
      if (cookie) document.cookie = cookie;
    }
  }

  if (typeof window !== 'undefined' && !unauthorizedRedirectStarted) {
    unauthorizedRedirectStarted = true;
    const base = import.meta.env.BASE_URL || '/';
    const basePath = base.endsWith('/') ? base.slice(0, -1) : base;
    const loginPath = `${basePath}/login`.replace(/\/{2,}/g, '/');
    const currentPath = `${window.location.pathname}${window.location.search}`;
    if (currentPath !== loginPath) {
      window.location.assign(loginPath);
    }
  }
}

/**
 * URLs for uploaded files on the API host: {API_BASE_URL}/uploads/…
 */
export function apiMediaUrl(relativePath) {
  if (relativePath == null || typeof relativePath !== 'string') return '';
  let path = relativePath.trim().replace(/^\/+/, '');
  if (!path) return '';
  if (path.startsWith('uploads/')) path = path.slice('uploads/'.length);
  if (apiBaseUrl) return `${apiBaseUrl}/uploads/${path}`;
  return `/uploads/${path}`;
}

export function xTokenHeader() {
  const t =
    import.meta.env.X_TOKEN ||
    import.meta.env.VITE_API_X_TOKEN ||
    import.meta.env.API_X_TOKEN;
  return typeof t === 'string' && t.trim().length > 0 ? t.trim() : '1234567890';
}

/** Headers for authenticated user requests (orders, wallet, etc.). */
export function userAuthHeaders(user) {
  if (!user || user.id == null || !user.token) return null;
  return {
    'x-token': xTokenHeader(),
    'x-user-token': user.token,
    'x-user-id': String(user.id),
  };
}

export function jsonHeaders(extra = {}) {
  return {
    'Content-Type': 'application/json',
    ...extra,
  };
}
