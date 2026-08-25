import { apiData, apiMessage, apiUrl, jsonHeaders, readApiJson, userAuthHeaders } from './apiConfig.js';
import { endpoints } from './endpoints.js';
import { fetchAuthWalletBalanceFromApi } from './userApi.js';

/** Pull balance from common API response shapes (PHP / JSON). */
export function extractWalletBalanceFromPayload(data) {
  if (data == null || typeof data !== 'object') return null;

  const nested = apiData(data);
  const candidates = [
    nested?.balance,
    nested?.wallet_balance,
    data.wallet_balance,
    data.balance,
    data.available_balance,
    data.walletBalance,
    data.amount,
  ];
  for (const c of candidates) {
    if (c != null && c !== '') return c;
  }

  const w = nested?.wallet ?? data.wallet;
  if (w && typeof w === 'object') {
    const wb = w.wallet_balance ?? w.balance ?? w.available_balance;
    if (wb != null && wb !== '') return wb;
  }

  const user = nested?.user ?? data.user;
  if (user && typeof user === 'object' && user.wallet_balance != null && user.wallet_balance !== '') {
    return user.wallet_balance;
  }

  return null;
}

function walletsFromPayload(data) {
  const nested = apiData(data);
  if (Array.isArray(nested?.wallets)) return nested.wallets;
  if (Array.isArray(data?.wallets)) return data.wallets;
  if (Array.isArray(nested?.wallet_transactions)) return nested.wallet_transactions;
  if (Array.isArray(data?.wallet_transactions)) return data.wallet_transactions;
  if (Array.isArray(nested?.data)) return nested.data;
  if (Array.isArray(data?.data)) return data.data;
  return [];
}

function walletFromPayload(data) {
  const nested = apiData(data);
  return nested?.wallet ?? data?.wallet ?? nested?.data ?? null;
}

function sortWalletsNewestFirst(rows) {
  return [...rows].sort((a, b) => {
    const ta = new Date(String(a.created_at || a.action_at || '').replace(' ', 'T')).getTime();
    const tb = new Date(String(b.created_at || b.action_at || '').replace(' ', 'T')).getTime();
    return tb - ta;
  });
}

/**
 * POST /v2/wallet/top-up-online — JSON: `{ amount }`.
 * Success: `{ success, data: { wallet: { payment_url, ... }, message } }`.
 * Open `wallet.payment_url` (Paystack checkout) to complete payment.
 */
export async function postWalletTopUp(user, amount) {
  const headers = userAuthHeaders(user);
  if (!headers) return { ok: false, error: 'no_session' };

  const body = { amount: Number(String(amount).trim()) || String(amount).trim() };

  try {
    const res = await fetch(apiUrl(endpoints.walletTopUpOnline()), {
      method: 'POST',
      headers: jsonHeaders(headers),
      credentials: 'include',
      body: JSON.stringify(body),
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
        wallet: walletFromPayload(data),
        message: apiMessage(data, 'Top up online successful'),
        data,
      };
    }

    return { ok: false, message: apiMessage(data, 'Could not start top-up.'), data };
  } catch {
    return { ok: false, error: 'network' };
  }
}

/** POST /v2/wallet/top-up-manual — form-data: amount + proof screenshots. */
export async function postPendingManualTopUp(user, { amount, description, proofFile }) {
  const headers = userAuthHeaders(user);
  if (!headers) return { ok: false, error: 'no_session' };

  const body = new FormData();
  body.append('amount', String(amount).trim());
  if (description) body.append('description', String(description).trim());
  if (proofFile instanceof File) {
    body.append('proof_of_payment_screenshot1', proofFile);
  }

  try {
    const res = await fetch(apiUrl(endpoints.walletTopUpManual()), {
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

    if (data?.success) {
      return {
        ok: true,
        wallet: walletFromPayload(data),
        message: apiMessage(data, 'Manual top-up request submitted.'),
        data,
      };
    }

    return { ok: false, message: apiMessage(data, 'Could not submit manual top-up request.'), data };
  } catch {
    return { ok: false, error: 'network' };
  }
}

async function fetchWalletList(user, path) {
  const headers = userAuthHeaders(user);
  if (!headers) return { ok: false, error: 'no_session' };

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
      return { ok: false, message: apiMessage(data, 'Could not load wallet transactions.'), data };
    }

    return { ok: true, wallets: walletsFromPayload(data), data };
  } catch {
    return { ok: false, error: 'network' };
  }
}

/** Admin: pending manual top-ups. */
export async function fetchPendingManualTopupRequests(user) {
  const r = await fetchWalletList(user, endpoints.walletManualPending());
  if (!r.ok) return r;
  return { ok: true, requests: r.wallets, total: r.wallets.length, data: r.data };
}

export async function fetchOnePendingManualTopupRequest(user, requestId) {
  const r = await fetchPendingManualTopupRequests(user);
  if (!r.ok) return r;
  const id = String(requestId ?? '').trim();
  const request = r.requests.find((w) => String(w?.id) === id);
  if (!request) return { ok: false, message: 'Could not load top-up request.', data: r.data };
  return { ok: true, request, data: r.data };
}

/** POST /v2/wallet/{walletId}/approve-manual-top-up */
export async function approvePendingManualTopupRequest(user, requestId) {
  const headers = userAuthHeaders(user);
  if (!headers) return { ok: false, error: 'no_session' };

  const id = String(requestId ?? '').trim();
  if (!id) return { ok: false, error: 'invalid_id' };

  try {
    const res = await fetch(apiUrl(endpoints.walletApproveManual(id)), {
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
      return { ok: true, wallet: walletFromPayload(data), data };
    }

    return { ok: false, message: apiMessage(data, 'Could not approve top-up request.'), data };
  } catch {
    return { ok: false, error: 'network' };
  }
}

/** POST /v2/wallet/{walletId}/reject-manual-top-up — JSON: reason */
export async function rejectPendingManualTopupRequest(user, requestId, reason) {
  const headers = userAuthHeaders(user);
  if (!headers) return { ok: false, error: 'no_session' };

  const id = String(requestId ?? '').trim();
  if (!id) return { ok: false, error: 'invalid_id' };
  const reasonText = String(reason ?? '').trim();
  if (!reasonText) return { ok: false, error: 'invalid_reason', message: 'Rejection reason is required.' };

  try {
    const res = await fetch(apiUrl(endpoints.walletRejectManual(id)), {
      method: 'POST',
      headers: jsonHeaders(headers),
      credentials: 'include',
      body: JSON.stringify({ reason: reasonText }),
    });
    let data = null;
    try {
      data = await readApiJson(res);
    } catch {
      return { ok: false, error: 'bad_json' };
    }

    if (data?.success) {
      return { ok: true, wallet: walletFromPayload(data), data };
    }

    return { ok: false, message: apiMessage(data, 'Could not reject top-up request.'), data };
  } catch {
    return { ok: false, error: 'network' };
  }
}

/**
 * Combine my pending + approved wallet rows; balance from /v2/auth/me/wallet-balance.
 */
export async function fetchWalletFromApi(user, page = 1) {
  const headers = userAuthHeaders(user);
  if (!headers) return { ok: false, error: 'no_session' };

  const pageNum = Math.max(1, parseInt(String(page), 10) || 1);
  const perPage = 20;

  try {
    const [pending, approved, balanceRes] = await Promise.all([
      fetchWalletList(user, endpoints.walletMyPending()),
      fetchWalletList(user, endpoints.walletMyApproved()),
      fetchAuthWalletBalanceFromApi(user),
    ]);

    if (!pending.ok && !approved.ok) {
      return {
        ok: false,
        message: pending.message || approved.message || 'Could not load wallet.',
        data: pending.data || approved.data,
      };
    }

    const all = sortWalletsNewestFirst([
      ...(pending.ok ? pending.wallets : []),
      ...(approved.ok ? approved.wallets : []),
    ]);
    const total = all.length;
    const lastPage = total === 0 ? 1 : Math.max(1, Math.ceil(total / perPage));
    const start = (pageNum - 1) * perPage;
    const transactions = all.slice(start, start + perPage);

    let balance = balanceRes.ok ? balanceRes.balance : null;
    if (balance == null && transactions.length > 0 && transactions[0].balance != null) {
      balance = transactions[0].balance;
    }

    return {
      ok: true,
      balance,
      transactions,
      total,
      page: pageNum,
      perPage,
      lastPage,
      hasNext: pageNum < lastPage,
      hasPrev: pageNum > 1,
      data: { pending: pending.data, approved: approved.data },
    };
  } catch {
    return { ok: false, error: 'network' };
  }
}

export async function fetchApprovedTopupRequests(user) {
  const r = await fetchWalletList(user, endpoints.walletManualApproved());
  if (!r.ok) return r;
  return { ok: true, requests: r.wallets, total: r.wallets.length, data: r.data };
}

export async function fetchRejectedTopupRequests(user) {
  const r = await fetchWalletList(user, endpoints.walletManualRejected());
  if (!r.ok) return r;
  return { ok: true, requests: r.wallets, total: r.wallets.length, data: r.data };
}

export async function fetchWalletBalanceFromApi(user) {
  const r = await fetchAuthWalletBalanceFromApi(user);
  if (r.ok) return r;
  const list = await fetchWalletFromApi(user);
  if (!list.ok) {
    return { ok: false, error: list.error, message: list.message, data: list.data };
  }
  if (list.balance == null) return { ok: false, data: list.data, error: 'no_balance_field' };
  return { ok: true, balance: list.balance };
}
