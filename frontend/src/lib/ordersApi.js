import { apiData, apiMessage, apiUrl, jsonHeaders, readApiJson, userAuthHeaders } from './apiConfig.js';
import { endpoints } from './endpoints.js';
import { ORDER_STATUS_FILTERS } from './orderStatuses.js';

function validStatusQuery(status) {
  if (status == null || status === '') return '';
  const s = String(status).trim().toLowerCase();
  return ORDER_STATUS_FILTERS.includes(s) ? s : '';
}

function ordersFromPayload(data) {
  const nested = apiData(data);
  if (Array.isArray(nested?.orders)) return nested.orders;
  if (Array.isArray(data?.orders)) return data.orders;
  return [];
}

function orderFromPayload(data) {
  const nested = apiData(data);
  return nested?.order ?? data?.order ?? null;
}

function threadsFromPayload(data) {
  const nested = apiData(data);
  if (Array.isArray(nested?.threads)) return nested.threads;
  if (Array.isArray(nested?.order_threads)) return nested.order_threads;
  if (Array.isArray(data?.threads)) return data.threads;
  if (Array.isArray(data?.order_threads)) return data.order_threads;
  return [];
}

function listPathForRole(user) {
  const role = String(user?.role || '').toLowerCase();
  if (role === 'admin') return endpoints.snappyAdminOrders();
  if (role === 'agent') return endpoints.snappyAgentOrders();
  return endpoints.snappyMyOrders();
}

function summarizeOrders(orders) {
  const statusCounts = {
    pending: 0,
    paid: 0,
    placed: 0,
    'shipped-to-facility': 0,
    'arrived-at-facility': 0,
    'shipped-to-destination-country': 0,
    'arrived-at-destination-country': 0,
    'arrived-at-destination-facility': 0,
    'ready-for-pickup': 0,
    delivered: 0,
    cancelled: 0,
  };
  let totalAmount = 0;
  for (const o of orders) {
    const key = String(o?.status || '').toLowerCase();
    if (key in statusCounts) statusCounts[key] += 1;
    const n = Number(o?.grand_total_naira ?? o?.total_amount_usd ?? 0);
    if (Number.isFinite(n)) totalAmount += n;
  }
  return {
    totalOrders: orders.length,
    totalAmount,
    statusCounts,
  };
}

/**
 * List snappy orders for the current role.
 * Query: status, search; admin also user_id, agent_id, batch_id.
 */
export async function fetchOrdersFromApi(user, { batchId, status, search, userId, agentId } = {}) {
  const headers = userAuthHeaders(user);
  if (!headers) return { ok: false, error: 'no_session' };

  const params = new URLSearchParams();
  const orderStatus = validStatusQuery(status);
  if (orderStatus) params.set('status', orderStatus);
  if (search != null && String(search).trim()) params.set('search', String(search).trim());
  if (batchId != null && String(batchId).trim()) params.set('batch_id', String(batchId).trim());
  if (userId != null && String(userId).trim()) params.set('user_id', String(userId).trim());
  if (agentId != null && String(agentId).trim()) params.set('agent_id', String(agentId).trim());

  const base = listPathForRole(user);
  const qs = params.toString();
  const path = qs ? `${base}?${qs}` : base;

  try {
    const res = await fetch(apiUrl(path), {
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
      return { ok: false, message: apiMessage(data, 'Could not load orders.'), data };
    }

    const orders = ordersFromPayload(data);
    const nested = apiData(data);
    const total =
      typeof nested?.count === 'number'
        ? nested.count
        : typeof data?.count === 'number'
          ? data.count
          : orders.length;
    const summary = summarizeOrders(orders);

    return {
      ok: true,
      orders,
      total,
      totalAmount: summary.totalAmount,
      statusCounts: summary.statusCounts,
      summary,
      data,
    };
  } catch {
    return { ok: false, error: 'network' };
  }
}

/** GET /v2/snappy-orders/{orderId} — `SnappyOrderService::getById`. */
export async function fetchOrderFromApi(user, orderId) {
  const id = orderId != null ? String(orderId).trim() : '';
  if (!id) return { ok: false, error: 'bad_id' };

  const headers = userAuthHeaders(user);
  if (!headers) return { ok: false, error: 'no_session' };

  try {
    const res = await fetch(apiUrl(endpoints.snappyOrder(id)), {
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
      return { ok: false, message: apiMessage(data, 'Could not load this order.'), data };
    }

    const order = orderFromPayload(data);
    if (!order) {
      return { ok: false, message: 'Could not load this order.', data };
    }

    return {
      ok: true,
      order,
      message: apiMessage(data, 'Order fetched successfully'),
      data,
    };
  } catch {
    return { ok: false, error: 'network' };
  }
}

/** POST /v2/snappy-orders/{orderId}/assign-to-batch — JSON: batch_id (admin). */
export async function assignOrderToBatchFromApi(user, orderId, batchId) {
  const oid = orderId != null ? String(orderId).trim() : '';
  const bid = batchId != null ? String(batchId).trim() : '';
  if (!oid || !bid) return { ok: false, error: 'bad_id' };

  const headers = userAuthHeaders(user);
  if (!headers) return { ok: false, error: 'no_session' };

  try {
    const res = await fetch(apiUrl(endpoints.snappyAssignBatch(oid)), {
      method: 'POST',
      headers: jsonHeaders(headers),
      credentials: 'include',
      body: JSON.stringify({ batch_id: Number(bid) || bid }),
    });
    let data = null;
    try {
      data = await readApiJson(res);
    } catch {
      return { ok: false, error: 'bad_json' };
    }

    if (data?.success) {
      return { ok: true, order: orderFromPayload(data), data };
    }

    return { ok: false, message: apiMessage(data, 'Could not assign order to batch.'), data };
  } catch {
    return { ok: false, error: 'network' };
  }
}

/** POST /v2/snappy-orders/{orderId}/unassign-from-batch (admin). */
export async function unassignOrderFromBatchFromApi(user, orderId) {
  const oid = orderId != null ? String(orderId).trim() : '';
  if (!oid) return { ok: false, error: 'bad_id' };

  const headers = userAuthHeaders(user);
  if (!headers) return { ok: false, error: 'no_session' };

  try {
    const res = await fetch(apiUrl(endpoints.snappyUnassignBatch(oid)), {
      method: 'POST',
      headers: jsonHeaders(headers),
      credentials: 'include',
      body: JSON.stringify({}),
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
        order: orderFromPayload(data),
        message: apiMessage(data, 'Order unassigned from batch successfully'),
        data,
      };
    }

    return { ok: false, message: apiMessage(data, 'Could not remove order from batch.'), data };
  } catch {
    return { ok: false, error: 'network' };
  }
}

/** POST /v2/snappy-orders/{orderId}/assign-to-agent — JSON: agent_id (admin). */
export async function assignOrderToAgentFromApi(user, orderId, agentId) {
  const oid = orderId != null ? String(orderId).trim() : '';
  const aid = agentId != null ? String(agentId).trim() : '';
  if (!oid || !aid) return { ok: false, error: 'bad_id' };

  const headers = userAuthHeaders(user);
  if (!headers) return { ok: false, error: 'no_session' };

  try {
    const res = await fetch(apiUrl(endpoints.snappyAssignAgent(oid)), {
      method: 'POST',
      headers: jsonHeaders(headers),
      credentials: 'include',
      body: JSON.stringify({ agent_id: Number(aid) || aid }),
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
        order: orderFromPayload(data),
        message: apiMessage(data, 'Order assigned to agent.'),
        data,
      };
    }

    return { ok: false, message: apiMessage(data, 'Could not assign agent.'), data };
  } catch {
    return { ok: false, error: 'network' };
  }
}

export async function unassignOrderAgentFromApi() {
  return { ok: false, message: 'Unassign agent is not available on this API.' };
}

/** GET /v2/threads/{orderId} */
export async function fetchOrderThreadsFromApi(user, orderId) {
  const id = orderId != null ? String(orderId).trim() : '';
  if (!id) return { ok: false, error: 'bad_id' };

  const headers = userAuthHeaders(user);
  if (!headers) return { ok: false, error: 'no_session' };

  try {
    const res = await fetch(apiUrl(endpoints.threadsForOrder(id)), {
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
      return { ok: false, message: apiMessage(data, 'Could not load messages.'), data };
    }

    return { ok: true, threads: threadsFromPayload(data), data };
  } catch {
    return { ok: false, error: 'network' };
  }
}

/** POST /v2/threads — form-data: order_id, message, optional attachment_url. */
export async function postOrderThread(user, orderId, { message = '', file = null } = {}) {
  const id = orderId != null ? String(orderId).trim() : '';
  if (!id) return { ok: false, error: 'bad_id' };

  const headers = userAuthHeaders(user);
  if (!headers) return { ok: false, error: 'no_session' };

  const body = new FormData();
  body.append('order_id', id);
  body.append('message', typeof message === 'string' ? message : '');
  if (file instanceof File) {
    body.append('attachment_url', file);
  }

  try {
    const res = await fetch(apiUrl(endpoints.threads()), {
      method: 'POST',
      headers,
      credentials: 'include',
      body,
    });
    let data = null;
    try {
      data = await readApiJson(res);
    } catch {
      return { ok: false, error: 'bad_json' };
    }

    const nested = apiData(data);
    const thread = nested?.thread ?? nested?.order_thread ?? data?.thread ?? data?.order_thread;
    if (data?.success && thread) {
      return { ok: true, thread, data };
    }

    return { ok: false, message: apiMessage(data, 'Could not send message.'), data };
  } catch {
    return { ok: false, error: 'network' };
  }
}

export async function deleteOrderThread() {
  return { ok: false, message: 'Deleting thread messages is not available on this API.' };
}

/** POST /v2/snappy-orders/{orderId}/change-price — JSON: price (admin). */
export async function postAdjustOrderPrice(user, orderId, price) {
  const id = orderId != null ? String(orderId).trim() : '';
  if (!id) return { ok: false, error: 'bad_id' };

  const amount = Number(price);
  if (!Number.isFinite(amount) || amount <= 0) {
    return { ok: false, message: 'Enter a valid price greater than zero.' };
  }

  const headers = userAuthHeaders(user);
  if (!headers) return { ok: false, error: 'no_session' };

  try {
    const res = await fetch(apiUrl(endpoints.snappyChangePrice(id)), {
      method: 'POST',
      headers: jsonHeaders(headers),
      credentials: 'include',
      body: JSON.stringify({ price: amount }),
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
        order: orderFromPayload(data),
        message: apiMessage(data, 'Order price updated successfully'),
        data,
      };
    }

    return { ok: false, message: apiMessage(data, 'Could not update order price.'), data };
  } catch {
    return { ok: false, error: 'network' };
  }
}

/** POST /v2/snappy-orders/{id}/change-status — JSON: status (admin). */
export async function postChangeOrderStatus(user, orderId, { status } = {}) {
  const id = orderId != null ? String(orderId).trim() : '';
  if (!id) return { ok: false, error: 'bad_id' };

  const headers = userAuthHeaders(user);
  if (!headers) return { ok: false, error: 'no_session' };

  try {
    const res = await fetch(apiUrl(endpoints.snappyChangeStatus(id)), {
      method: 'POST',
      headers: jsonHeaders(headers),
      credentials: 'include',
      body: JSON.stringify({ status: String(status || '').trim() }),
    });
    let data = null;
    try {
      data = await readApiJson(res);
    } catch {
      return { ok: false, error: 'bad_json' };
    }

    if (data?.success) {
      return { ok: true, order: orderFromPayload(data), data };
    }

    return { ok: false, message: apiMessage(data, 'Could not change status.'), data };
  } catch {
    return { ok: false, error: 'network' };
  }
}

/** POST /v2/snappy-orders/{id}/pay-from-wallet */
export async function payOrderFromWalletFromApi(user, orderId) {
  const id = orderId != null ? String(orderId).trim() : '';
  if (!id) return { ok: false, error: 'bad_id' };

  const headers = userAuthHeaders(user);
  if (!headers) return { ok: false, error: 'no_session' };

  try {
    const res = await fetch(apiUrl(endpoints.snappyPayFromWallet(id)), {
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
      return {
        ok: true,
        order: orderFromPayload(data),
        message: apiMessage(data, 'Payment successful.'),
        data,
      };
    }

    return { ok: false, message: apiMessage(data, 'Could not pay from wallet.'), data };
  } catch {
    return { ok: false, error: 'network' };
  }
}

/** POST /v2/snappy-orders/publish-settings (admin). */
export async function publishOrderSettingsFromApi(user) {
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
      return { ok: true, message: apiMessage(data, 'Settings published.'), data };
    }

    return { ok: false, message: apiMessage(data, 'Could not publish settings.'), data };
  } catch {
    return { ok: false, error: 'network' };
  }
}
