import { apiMessage, apiUrl, readApiJson, userAuthHeaders, xTokenHeader } from './apiConfig.js';
import { endpoints } from './endpoints.js';

/** Migrate endpoints from Postman (x-token only). */
export const MIGRATION_JOBS = [
  { id: 'users', label: 'Users', path: endpoints.migrateUsers() },
  { id: 'wallet', label: 'Wallet', path: endpoints.migrateWallet() },
  { id: 'notifications', label: 'Notifications', path: endpoints.migrateNotifications() },
  { id: 'platform-configs', label: 'Platform configs', path: endpoints.migratePlatformConfigs() },
  { id: 'snappy-orders', label: 'Snappy orders', path: endpoints.migrateSnappyOrders() },
  { id: 'batches', label: 'Batches', path: endpoints.migrateBatches() },
  { id: 'threads', label: 'Threads', path: endpoints.migrateThreads() },
  {
    id: 'proxy-order-change-logs',
    label: 'Proxy order change logs',
    path: endpoints.migrateProxyOrderChangeLogs(),
  },
  { id: 'logs', label: 'Logs', path: endpoints.migrateLogs() },
  { id: 'categories', label: 'Categories', path: endpoints.migrateCategories() },
  { id: 'products', label: 'Products', path: endpoints.migrateProducts() },
  { id: 'user-kycs', label: 'User KYCs', path: endpoints.migrateUserKycs() },
];

function formatMigrationBody(text) {
  if (!text) return 'ok';
  return String(text)
    .replace(/<br\s*\/?>/gi, '\n')
    .replace(/<[^>]*>/g, '')
    .trim();
}

/** GET migrate path with x-token only. */
export async function runMigrationJob(path) {
  try {
    const res = await fetch(apiUrl(path), {
      method: 'GET',
      headers: {
        'x-token': xTokenHeader(),
      },
      credentials: 'include',
    });

    const text = await res.text();
    let message = formatMigrationBody(text);
    try {
      const json = JSON.parse(text);
      message = apiMessage(json, message || 'ok');
    } catch {
      /* plain text / HTML body */
    }

    if (!res.ok) {
      return { ok: false, path, message: message || `failed (${res.status})`, status: res.status };
    }

    return { ok: true, path, message: message || 'ok', status: res.status };
  } catch {
    return { ok: false, path, message: 'Network error. Check that the API is running.', error: 'network' };
  }
}

/** POST /v2/snappy-orders/publish-settings (admin + user token). */
export async function publishSnappyOrderSettings(user) {
  const headers = userAuthHeaders(user);
  if (!headers) return { ok: false, error: 'no_session' };

  try {
    const res = await fetch(apiUrl(endpoints.snappyPublishSettings()), {
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
      return { ok: true, message: apiMessage(data, 'Settings published successfully'), data };
    }

    return { ok: false, message: apiMessage(data, 'Could not publish settings.'), data };
  } catch {
    return { ok: false, error: 'network' };
  }
}
