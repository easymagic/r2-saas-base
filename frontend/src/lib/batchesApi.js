import { apiData, apiMessage, apiUrl, jsonHeaders, readApiJson, userAuthHeaders } from './apiConfig.js';

function batchesFromPayload(data) {
  const nested = apiData(data);
  if (Array.isArray(nested?.batches)) return nested.batches;
  if (Array.isArray(data?.batches)) return data.batches;
  return [];
}

function batchFromPayload(data) {
  const nested = apiData(data);
  return nested?.batch ?? data?.batch ?? null;
}

/** GET /v2/batches */
export async function fetchBatchesFromApi(user) {
  const headers = userAuthHeaders(user);
  if (!headers) return { ok: false, error: 'no_session' };

  try {
    const res = await fetch(apiUrl('/v2/batches'), {
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
      return { ok: false, message: apiMessage(data, 'Could not load batches.'), data };
    }

    return { ok: true, batches: batchesFromPayload(data), data };
  } catch {
    return { ok: false, error: 'network' };
  }
}

/** POST /v2/batches — JSON: name, description */
export async function createBatchFromApi(user, { name, description = '' } = {}) {
  const headers = userAuthHeaders(user);
  if (!headers) return { ok: false, error: 'no_session' };

  const n = String(name ?? '').trim();
  if (!n) {
    return { ok: false, error: 'invalid_name', message: 'Name is required.' };
  }

  try {
    const res = await fetch(apiUrl('/v2/batches'), {
      method: 'POST',
      headers: jsonHeaders(headers),
      credentials: 'include',
      body: JSON.stringify({
        name: n,
        description: String(description ?? '').trim(),
      }),
    });
    let data = null;
    try {
      data = await readApiJson(res);
    } catch {
      return { ok: false, error: 'bad_json' };
    }

    if (data?.success) {
      return {
        ok: true,
        batch: batchFromPayload(data),
        message: apiMessage(data, 'Batch created.'),
        data,
      };
    }

    return { ok: false, message: apiMessage(data, 'Could not create batch.'), data };
  } catch {
    return { ok: false, error: 'network' };
  }
}

/** Resolve one batch from list (no GET-by-id in v2). */
export async function fetchBatchByIdFromApi(user, batchId) {
  const id = batchId != null ? String(batchId).trim() : '';
  if (!id) return { ok: false, error: 'invalid_id' };

  const r = await fetchBatchesFromApi(user);
  if (!r.ok) return r;
  const batch = r.batches.find((b) => String(b?.id) === id);
  if (!batch) return { ok: false, message: 'Could not load batch.', data: r.data };
  return { ok: true, batch, data: r.data };
}

/** Update is not in v2; keep a clear error for the admin UI. */
export async function updateBatchFromApi() {
  return { ok: false, message: 'Updating batches is not available on this API.' };
}

/** DELETE /v2/batches/{id} */
export async function deleteBatchFromApi(user, batchId) {
  const headers = userAuthHeaders(user);
  if (!headers) return { ok: false, error: 'no_session' };

  const id = batchId != null ? String(batchId).trim() : '';
  if (!id) return { ok: false, error: 'invalid_id' };

  try {
    const res = await fetch(apiUrl(`/v2/batches/${encodeURIComponent(id)}`), {
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
      return { ok: true, data };
    }

    return { ok: false, message: apiMessage(data, 'Could not delete batch.'), data };
  } catch {
    return { ok: false, error: 'network' };
  }
}
