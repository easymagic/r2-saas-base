import { apiData, apiMessage, apiUrl, readApiJson, userAuthHeaders } from './apiConfig.js';
import { endpoints, withQuery } from './endpoints.js';

/**
 * GET /v2/logs — admin list.
 * Filters: `type` (success|error), `search`, `id`.
 */
export async function fetchLogsFromApi(user, filters = {}) {
  const headers = userAuthHeaders(user);
  if (!headers) return { ok: false, error: 'no_session' };

  const query = {
    type: filters.type,
    search: filters.search,
    id: filters.id,
  };

  try {
    const res = await fetch(apiUrl(withQuery(endpoints.logs(), query)), {
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
      return { ok: false, message: apiMessage(data, 'Could not load logs.'), data };
    }

    const nested = apiData(data);
    const logs = Array.isArray(nested?.logs)
      ? nested.logs
      : Array.isArray(data?.logs)
        ? data.logs
        : [];
    const count =
      typeof nested?.count === 'number'
        ? nested.count
        : typeof data?.count === 'number'
          ? data.count
          : logs.length;

    return {
      ok: true,
      logs,
      count,
      message: apiMessage(data, 'Logs fetched successfully'),
      data,
    };
  } catch {
    return { ok: false, error: 'network' };
  }
}
