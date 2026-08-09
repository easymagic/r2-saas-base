import { useCallback, useEffect, useMemo, useState } from 'react';
import { Link, useNavigate, useSearchParams } from 'react-router-dom';
import { UserHeader } from '../components/layout/UserHeader.jsx';
import { Badge } from '../components/ui/Badge.jsx';
import { Button } from '../components/ui/Button.jsx';
import { useToast } from '../context/ToastContext.jsx';
import { useSyncedWalletBalance } from '../hooks/useSyncedWalletBalance.js';
import { getStoredUser } from '../lib/authSession.js';
import { batchSelectOptionLabel } from '../lib/batchDisplay.js';
import { fetchBatchesFromApi } from '../lib/batchesApi.js';
import {
  assignOrderToBatchFromApi,
  fetchOrdersFromApi,
  unassignOrderFromBatchFromApi,
} from '../lib/ordersApi.js';
import { ORDER_STATUS_FILTERS } from '../lib/orderStatuses.js';
import { formatDollar, formatNaira, initialsFromName } from '../lib/userDisplay.js';

function formatOrderDate(value) {
  if (value == null || value === '') return '—';
  const d = new Date(String(value).replace(' ', 'T'));
  if (Number.isNaN(d.getTime())) return String(value);
  return d.toLocaleString('en-NG', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}

function formatOrderStatusLabel(status) {
  const s = String(status || '').trim();
  if (!s) return '—';
  return s
    .split(/[-_]/)
    .filter(Boolean)
    .map((w) => w.charAt(0).toUpperCase() + w.slice(1).toLowerCase())
    .join(' ');
}

function orderStatusBadgeVariant(status) {
  const s = String(status || '').toLowerCase();
  if (s === 'pending') return 'pending';
  if (s === 'delivered' || s === 'ready-for-pickup') return 'delivered';
  if (s === 'paid' || s === 'placed') return 'approved';
  if (s === 'cancelled') return 'rejected';
  return 'default';
}

const ORDER_SUMMARY_METRICS = [
  { key: 'pending', label: 'Pending' },
  { key: 'paid', label: 'Paid' },
  { key: 'placed', label: 'Placed' },
  { key: 'ready-for-pickup', label: 'Ready for pickup' },
  { key: 'delivered', label: 'Delivered' },
  { key: 'cancelled', label: 'Cancelled' },
];

function formatMetricNumber(value) {
  if (value == null || value === '') return '—';
  const n = typeof value === 'number' ? value : Number(value);
  if (!Number.isFinite(n)) return '—';
  return n.toLocaleString('en-NG', { maximumFractionDigits: 0 });
}

function hasOrderSummary(summary) {
  if (!summary) return false;
  if (summary.totalOrders != null || summary.totalAmount != null) return true;
  return ORDER_SUMMARY_METRICS.some(({ key }) => summary.statusCounts?.[key] != null);
}

function orderTypeLabel(type) {
  const t = String(type || '').toLowerCase();
  if (t === 'online') return 'Online';
  if (t === 'manual') return 'Manual';
  return t ? t.charAt(0).toUpperCase() + t.slice(1) : '—';
}

function orderAmountDisplay(order) {
  if (order?.grand_total_naira != null && order.grand_total_naira !== '') {
    return formatNaira(order.grand_total_naira);
  }
  return formatDollar(order?.total_amount_usd ?? order?.total_amount);
}

function orderMatchesQuery(order, q) {
  if (!q.trim()) return true;
  const needle = q.trim().toLowerCase();
  const hay = [
    order.id,
    order.reference,
    order.link,
    order.description,
    order.type,
    order.status,
    order.payment_status,
    order.batch?.name,
  ]
    .filter((x) => x != null && x !== '')
    .join(' ')
    .toLowerCase();
  return hay.includes(needle);
}

function orderMatchesStatus(order, status) {
  if (!status) return true;
  return String(order?.status || '').toLowerCase() === status;
}

function orderAlreadyInBatch(order, batchIdStr) {
  if (!batchIdStr || order == null) return false;
  const bid = order.batch_id;
  if (bid == null || bid === '' || Number(bid) === 0) return false;
  return Number(bid) === Number(batchIdStr);
}

function batchCellLabel(order) {
  if (order?.batch?.name) return String(order.batch.name);
  const bid = order?.batch_id;
  if (bid != null && bid !== '' && Number(bid) > 0) return `Batch #${bid}`;
  return '—';
}

function orderHasBatch(order) {
  const bid = order?.batch_id;
  if (bid != null && bid !== '' && Number(bid) > 0) return true;
  return Boolean(order?.batch?.name);
}

/** URL ?batch_id= — empty string means no filter */
function parseBatchIdFromSearchParam(value) {
  if (value == null || value === '') return '';
  const s = String(value).trim();
  if (!/^\d+$/.test(s)) return '';
  if (Number(s) <= 0) return '';
  return s;
}

function parseStatusFromSearchParam(value) {
  const s = String(value || '').trim().toLowerCase();
  return ORDER_STATUS_FILTERS.includes(s) ? s : '';
}

export function OrdersPage() {
  const navigate = useNavigate();
  const [searchParams, setSearchParams] = useSearchParams();
  const filterBatchId = parseBatchIdFromSearchParam(searchParams.get('batch_id'));
  const filterStatus = parseStatusFromSearchParam(searchParams.get('status'));
  const user = getStoredUser();
  const [balanceLabel] = useSyncedWalletBalance();
  const [search, setSearch] = useState('');
  const [loading, setLoading] = useState(true);
  const [loadError, setLoadError] = useState('');
  const [orders, setOrders] = useState([]);
  const [apiTotal, setApiTotal] = useState(null);
  const [orderSummary, setOrderSummary] = useState(null);
  const [batches, setBatches] = useState([]);
  const [batchesLoading, setBatchesLoading] = useState(false);
  const [batchesError, setBatchesError] = useState('');
  const [selectedIds, setSelectedIds] = useState(() => new Set());
  const [targetBatchId, setTargetBatchId] = useState('');
  const [assigning, setAssigning] = useState(false);
  const [unassigningId, setUnassigningId] = useState(null);
  const { showToast } = useToast();

  const isAdmin = String(user?.role || '').toLowerCase() === 'admin';

  const loadOrders = useCallback(async () => {
    const u = getStoredUser();
    if (!u?.token || u.id == null) {
      navigate('/login', { replace: true });
      return;
    }
    setLoading(true);
    setLoadError('');
    setOrderSummary(null);
    const r = await fetchOrdersFromApi(u, {
      ...(filterBatchId ? { batchId: filterBatchId } : {}),
      ...(filterStatus ? { status: filterStatus } : {}),
    });
    setLoading(false);
    if (!r.ok) {
      setLoadError(typeof r.message === 'string' && r.message.length > 0 ? r.message : 'Could not load orders.');
      setOrders([]);
      setApiTotal(null);
      setOrderSummary(null);
      return;
    }
    const sorted = [...r.orders].sort((a, b) => {
      const ta = new Date(String(a.created_at || '').replace(' ', 'T')).getTime();
      const tb = new Date(String(b.created_at || '').replace(' ', 'T')).getTime();
      return tb - ta;
    });
    setOrders(sorted);
    setApiTotal(typeof r.total === 'number' ? r.total : sorted.length);
    setOrderSummary(r.summary ?? null);
  }, [navigate, filterBatchId, filterStatus]);

  const loadBatchesList = useCallback(async () => {
    if (!isAdmin) return;
    const u = getStoredUser();
    if (!u?.token || u.id == null) {
      navigate('/login', { replace: true });
      return;
    }
    setBatchesLoading(true);
    setBatchesError('');
    const r = await fetchBatchesFromApi(u);
    setBatchesLoading(false);
    if (!r.ok) {
      setBatchesError(
        typeof r.message === 'string' && r.message.length > 0 ? r.message : 'Could not load batches.'
      );
      setBatches([]);
      return;
    }
    const sorted = [...r.batches].sort((a, b) => {
      const ta = new Date(String(a.created_at || '').replace(' ', 'T')).getTime();
      const tb = new Date(String(b.created_at || '').replace(' ', 'T')).getTime();
      return tb - ta;
    });
    setBatches(sorted);
  }, [isAdmin, navigate]);

  useEffect(() => {
    loadOrders();
  }, [loadOrders]);

  useEffect(() => {
    if (!isAdmin) {
      setBatches([]);
      setBatchesError('');
      setSelectedIds(new Set());
      setTargetBatchId('');
      return;
    }
    loadBatchesList();
  }, [isAdmin, loadBatchesList]);

  useEffect(() => {
    if (!isAdmin || batches.length === 0) {
      setTargetBatchId('');
      return;
    }
    setTargetBatchId((prev) => {
      const ids = new Set(batches.map((b) => String(b.id)));
      if (prev && ids.has(prev)) return prev;
      return String(batches[0].id);
    });
  }, [isAdmin, batches]);

  useEffect(() => {
    setSelectedIds(new Set());
  }, [targetBatchId, filterBatchId, filterStatus]);

  const ordersForRole = useMemo(() => {
    const uid = user?.id;
    const role = String(user?.role || '').toLowerCase();
    if (isAdmin || role === 'agent' || uid == null) return orders;
    return orders.filter((o) => Number(o.user_id) === Number(uid));
  }, [orders, isAdmin, user?.id, user?.role]);

  const visibleOrders = useMemo(
    () => ordersForRole.filter((o) => orderMatchesQuery(o, search) && orderMatchesStatus(o, filterStatus)),
    [ordersForRole, search, filterStatus]
  );

  const selectableVisible = useMemo(
    () =>
      isAdmin
        ? visibleOrders.filter((o) => !orderAlreadyInBatch(o, targetBatchId))
        : [],
    [isAdmin, visibleOrders, targetBatchId]
  );

  const allSelectableChecked =
    selectableVisible.length > 0 && selectableVisible.every((o) => selectedIds.has(String(o.id)));

  function toggleOrderSelected(orderIdStr) {
    setSelectedIds((prev) => {
      const next = new Set(prev);
      if (next.has(orderIdStr)) next.delete(orderIdStr);
      else next.add(orderIdStr);
      return next;
    });
  }

  function selectAllVisibleSelectable() {
    setSelectedIds(new Set(selectableVisible.map((o) => String(o.id))));
  }

  function clearOrderSelection() {
    setSelectedIds(new Set());
  }

  async function handleAssignToBatch() {
    const u = getStoredUser();
    if (!u?.token || u.id == null) {
      navigate('/login', { replace: true });
      return;
    }
    if (!targetBatchId) {
      showToast('Choose a batch.', 'error');
      return;
    }
    const ids = [...selectedIds];
    if (ids.length === 0) {
      showToast('Select at least one order.', 'error');
      return;
    }

    setAssigning(true);
    let okCount = 0;
    const failures = [];
    for (const oid of ids) {
      const r = await assignOrderToBatchFromApi(u, oid, targetBatchId);
      if (r.ok) okCount += 1;
      else
        failures.push({
          id: oid,
          message:
            typeof r.message === 'string' && r.message.length > 0 ? r.message : 'Could not assign order.',
        });
    }
    setAssigning(false);

    if (failures.length === 0) {
      showToast(
        okCount === 1 ? 'Order assigned to batch.' : `${okCount} orders assigned to batch.`,
        'success'
      );
      setSelectedIds(new Set());
      await loadOrders();
      await loadBatchesList();
      return;
    }

    if (okCount > 0) {
      showToast(
        `Assigned ${okCount}; ${failures.length} failed (${failures[0].message})`,
        'error'
      );
      setSelectedIds(new Set());
      await loadOrders();
      await loadBatchesList();
      return;
    }

    showToast(failures[0].message, 'error');
  }

  async function handleUnassignFromBatch(order) {
    const u = getStoredUser();
    if (!u?.token || u.id == null) {
      navigate('/login', { replace: true });
      return;
    }
    const id = order?.id;
    if (id == null || !orderHasBatch(order)) return;

    const batchLabel = batchCellLabel(order);
    if (
      !window.confirm(
        `Remove order #${id} from ${batchLabel === '—' ? 'its batch' : `“${batchLabel}”`}?`
      )
    ) {
      return;
    }

    setUnassigningId(id);
    const r = await unassignOrderFromBatchFromApi(u, id);
    setUnassigningId(null);

    if (!r.ok) {
      const msg =
        typeof r.message === 'string' && r.message.length > 0
          ? r.message
          : 'Could not remove order from batch.';
      showToast(msg, 'error');
      return;
    }
    showToast(
      typeof r.message === 'string' && r.message.length > 0 ? r.message : 'Order removed from batch.',
      'success'
    );
    await loadOrders();
    await loadBatchesList();
  }

  const batchBusy = assigning || unassigningId != null;

  return (
    <>
      <UserHeader
        title="Orders"
        subtitle={isAdmin ? 'Search, assign batches, and open any order' : 'Everything you’ve ordered'}
        right={isAdmin ? (
          <>
            <span className="hidden rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600 sm:inline">
              Balance: {balanceLabel}
            </span>
            <Link
              to="/profile"
              className="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-900 text-xs font-semibold text-white ring-2 ring-transparent transition hover:ring-orange-400/50 focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-500"
              aria-label="Edit profile"
              title="My profile"
            >
              {initialsFromName(user?.name)}
            </Link>
          </>
        ) : null}
      />
      <main className="flex-1 p-4 sm:p-6 lg:p-8">
        <section className="rounded-2xl bg-white p-6 shadow-md">
          <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div className="flex min-w-0 flex-1 flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end">
              <div className="relative min-w-0 flex-1 sm:max-w-md">
                <label htmlFor="search" className="sr-only">
                  Search orders
                </label>
                <input
                  id="search"
                  type="search"
                  value={search}
                  onChange={(e) => setSearch(e.target.value)}
                  placeholder="Search by ID, reference, or notes…"
                  className="w-full rounded-lg border border-gray-300 py-2 pl-10 pr-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <svg
                  className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                  aria-hidden
                >
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
              </div>
              {isAdmin ? (
                <div className="w-full min-w-[12rem] sm:w-auto sm:max-w-[14rem]">
                  <label htmlFor="orders-filter-batch" className="block text-sm font-medium text-gray-700">
                    Filter by batch
                  </label>
                  <select
                    id="orders-filter-batch"
                    className="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    value={filterBatchId}
                    disabled={batchesLoading || !!batchesError}
                    onChange={(e) => {
                      const v = e.target.value;
                      const next = new URLSearchParams(searchParams);
                      if (v) next.set('batch_id', v);
                      else next.delete('batch_id');
                      setSearchParams(next, { replace: true });
                    }}
                  >
                    <option value="">All batches</option>
                    {filterBatchId && !batches.some((b) => String(b.id) === filterBatchId) ? (
                      <option value={filterBatchId}>Batch #{filterBatchId}</option>
                    ) : null}
                    {batches.map((b) => (
                      <option key={b.id} value={String(b.id)}>
                        {batchSelectOptionLabel(b)}
                      </option>
                    ))}
                  </select>
                </div>
              ) : null}
              <div className="w-full min-w-[12rem] sm:w-auto sm:max-w-[16rem]">
                <label htmlFor="orders-filter-status" className="block text-sm font-medium text-gray-700">
                  Filter by status
                </label>
                <select
                  id="orders-filter-status"
                  className="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  value={filterStatus}
                  onChange={(e) => {
                    const v = e.target.value;
                    const next = new URLSearchParams(searchParams);
                    if (v) next.set('status', v);
                    else next.delete('status');
                    setSearchParams(next, { replace: true });
                  }}
                >
                  <option value="">All statuses</option>
                  {ORDER_STATUS_FILTERS.map((status) => (
                    <option key={status} value={status}>
                      {formatOrderStatusLabel(status)}
                    </option>
                  ))}
                </select>
              </div>
            </div>
            <div className="flex flex-col items-start gap-2 sm:flex-row sm:items-center sm:gap-3">
              {filterBatchId || filterStatus ? (
                <button
                  type="button"
                  className="text-xs font-medium text-orange-600 hover:text-orange-700"
                  onClick={() => {
                    const next = new URLSearchParams(searchParams);
                    next.delete('batch_id');
                    next.delete('status');
                    setSearchParams(next, { replace: true });
                  }}
                >
                  Clear filters
                </button>
              ) : null}
              <div className="text-xs text-gray-500">
                {loading ? (
                  <span aria-busy="true">Loading…</span>
                ) : loadError ? (
                  <span className="text-red-600">{loadError}</span>
                ) : (
                  <span>
                    {search.trim()
                      ? `${visibleOrders.length} ${visibleOrders.length === 1 ? 'match' : 'matches'}`
                      : `${visibleOrders.length} ${visibleOrders.length === 1 ? 'order' : 'orders'}`}
                    {filterBatchId && !search.trim() ? ' in this batch' : null}
                    {filterStatus && !search.trim() ? ` · ${formatOrderStatusLabel(filterStatus)}` : null}
                    {!search.trim() && isAdmin && apiTotal != null && apiTotal !== visibleOrders.length
                      ? ` · ${apiTotal} from server`
                      : null}
                  </span>
                )}
              </div>
            </div>
          </div>

          {!loading && !loadError && hasOrderSummary(orderSummary) ? (
            <div className="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6">
              <div className="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                <p className="text-xs font-medium uppercase tracking-wide text-gray-500">Total orders</p>
                <p className="mt-1 text-xl font-semibold tabular-nums text-gray-900">
                  {formatMetricNumber(orderSummary?.totalOrders)}
                </p>
              </div>
              <div className="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                <p className="text-xs font-medium uppercase tracking-wide text-gray-500">Total amount</p>
                <p className="mt-1 text-xl font-semibold tabular-nums text-gray-900">
                  {formatDollar(orderSummary?.totalAmount)}
                </p>
              </div>
              {ORDER_SUMMARY_METRICS.map(({ key, label }) =>
                orderSummary?.statusCounts?.[key] == null ? null : (
                  <div key={key} className="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                    <p className="text-xs font-medium uppercase tracking-wide text-gray-500">{label}</p>
                    <p className="mt-1 text-xl font-semibold tabular-nums text-gray-900">
                      {formatMetricNumber(orderSummary.statusCounts[key])}
                    </p>
                  </div>
                )
              )}
            </div>
          ) : null}

          {isAdmin ? (
            <div className="mt-4 rounded-xl border border-gray-200 bg-gray-50 p-4">
              <p className="text-sm font-medium text-gray-900">Assign to batch</p>
              <p className="mt-1 text-xs text-gray-500">
                Tick orders in the table, pick a batch, then assign. Orders already in that batch cannot be selected.
              </p>
              <div className="mt-3 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end">
                <div className="min-w-[12rem] flex-1">
                  <label htmlFor="orders-assign-batch" className="block text-sm font-medium text-gray-700">
                    Batch
                  </label>
                  <select
                    id="orders-assign-batch"
                    className="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    value={targetBatchId}
                    onChange={(e) => setTargetBatchId(e.target.value)}
                    disabled={batchesLoading || !!batchesError || batches.length === 0 || batchBusy}
                  >
                    {batches.length === 0 ? (
                      <option value="">No batches</option>
                    ) : (
                      batches.map((b) => (
                        <option key={b.id} value={String(b.id)}>
                          {batchSelectOptionLabel(b)}
                        </option>
                      ))
                    )}
                  </select>
                </div>
                <div className="flex flex-wrap gap-2">
                  <Button
                    type="button"
                    variant="orange"
                    disabled={
                      batchBusy ||
                      batchesLoading ||
                      !!batchesError ||
                      !targetBatchId ||
                      batches.length === 0 ||
                      selectedIds.size === 0
                    }
                    onClick={handleAssignToBatch}
                  >
                    {assigning ? 'Assigning…' : 'Assign selected'}
                  </Button>
                  <Button
                    type="button"
                    variant="secondary"
                    disabled={batchBusy || selectableVisible.length === 0}
                    onClick={selectAllVisibleSelectable}
                  >
                    Select all in list
                  </Button>
                  <Button type="button" variant="secondary" disabled={batchBusy || selectedIds.size === 0} onClick={clearOrderSelection}>
                    Clear selection
                  </Button>
                </div>
              </div>
              {batchesError ? <p className="mt-2 text-xs text-red-600">{batchesError}</p> : null}
              {batchesLoading ? <p className="mt-2 text-xs text-gray-500">Loading batches…</p> : null}
              {!batchesLoading && !batchesError && batches.length === 0 ? (
                <p className="mt-2 text-xs text-gray-600">
                  No batches yet.{' '}
                  <Link to="/admin/batches" className="font-medium text-orange-600 hover:text-orange-700">
                    Create one in Batches
                  </Link>
                </p>
              ) : null}
            </div>
          ) : null}

          {!loading && !loadError && visibleOrders.length === 0 ? (
            <p className="mt-6 text-sm text-gray-500">
              {ordersForRole.length === 0
                ? 'No orders yet. Create one from the dashboard to get started.'
                : 'No orders match your search.'}
            </p>
          ) : null}

          {visibleOrders.length > 0 ? (
            <div className="mt-6 overflow-x-auto">
              <table className={`w-full border-collapse text-left text-sm ${isAdmin ? 'min-w-[52rem]' : 'min-w-[44rem]'}`}>
                <thead>
                  <tr className="border-b border-gray-200 bg-gray-100 text-xs font-semibold uppercase tracking-wide text-gray-600">
                    {isAdmin ? (
                      <th className="w-10 px-2 py-3 text-center" scope="col">
                        <span className="sr-only">Select for batch</span>
                        <input
                          type="checkbox"
                          className="h-4 w-4 rounded border-gray-300 text-blue-900 focus:ring-blue-500"
                          checked={allSelectableChecked}
                          disabled={batchBusy || selectableVisible.length === 0}
                          onChange={() => {
                            if (allSelectableChecked) clearOrderSelection();
                            else selectAllVisibleSelectable();
                          }}
                          aria-label="Select all orders in this list that can be assigned to the chosen batch"
                        />
                      </th>
                    ) : null}
                    <th className="px-4 py-3" scope="col">
                      Order
                    </th>
                    <th className="px-4 py-3" scope="col">
                      Type
                    </th>
                    <th className="px-4 py-3" scope="col">
                      Status
                    </th>
                    {isAdmin ? (
                      <th className="px-4 py-3" scope="col">
                        Batch
                      </th>
                    ) : null}
                    <th className="px-4 py-3 text-right" scope="col">
                      Amount
                    </th>
                    <th className="px-4 py-3" scope="col">
                      Date
                    </th>
                    <th className="px-4 py-3 text-right" scope="col">
                      Action
                    </th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                  {visibleOrders.map((o) => {
                    const id = o.id;
                    const idStr = String(id);
                    const statusLabel = formatOrderStatusLabel(o.status);
                    const inTargetBatch = isAdmin && orderAlreadyInBatch(o, targetBatchId);
                    const orderPath = `/orders/${id}`;
                    return (
                      <tr
                        key={id}
                        role="link"
                        tabIndex={0}
                        className="cursor-pointer hover:bg-gray-50 focus:outline-none focus-visible:bg-gray-50 focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-blue-500"
                        onClick={() => navigate(orderPath)}
                        onKeyDown={(e) => {
                          if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            navigate(orderPath);
                          }
                        }}
                        aria-label={`Open order ${idStr}`}
                      >
                        {isAdmin ? (
                          <td className="px-2 py-3 text-center" onClick={(e) => e.stopPropagation()}>
                            <input
                              type="checkbox"
                              className="h-4 w-4 rounded border-gray-300 text-blue-900 focus:ring-blue-500 disabled:cursor-not-allowed"
                              checked={selectedIds.has(idStr)}
                              disabled={batchBusy || inTargetBatch}
                              onChange={() => toggleOrderSelected(idStr)}
                              onKeyDown={(e) => e.stopPropagation()}
                              aria-label={inTargetBatch ? `Order ${idStr} is already in this batch` : `Select order ${idStr} for batch assign`}
                            />
                          </td>
                        ) : null}
                        <td className="px-4 py-3">
                          <Link
                            className="font-medium text-blue-900 hover:underline"
                            to={orderPath}
                            onClick={(e) => e.stopPropagation()}
                          >
                            #{id}
                          </Link>
                          {o.reference ? (
                            <p className="mt-0.5 max-w-[12rem] truncate font-mono text-xs text-gray-500" title={String(o.reference)}>
                              {String(o.reference)}
                            </p>
                          ) : null}
                        </td>
                        <td className="px-4 py-3 text-gray-700">{orderTypeLabel(o.type)}</td>
                        <td className="px-4 py-3">
                          <Badge variant={orderStatusBadgeVariant(o.status)}>{statusLabel}</Badge>
                        </td>
                        {isAdmin ? (
                          <td className="max-w-[10rem] truncate px-4 py-3 text-gray-700" title={batchCellLabel(o)}>
                            {batchCellLabel(o)}
                            {inTargetBatch ? (
                              <span className="mt-0.5 block text-xs font-normal text-gray-500">Current batch</span>
                            ) : null}
                          </td>
                        ) : null}
                        <td className="px-4 py-3 text-right tabular-nums font-medium text-gray-900">{orderAmountDisplay(o)}</td>
                        <td className="px-4 py-3 whitespace-nowrap text-gray-600">{formatOrderDate(o.created_at)}</td>
                        <td className="px-4 py-3 text-right">
                          <div className="flex flex-col items-end gap-1 sm:flex-row sm:items-center sm:justify-end sm:gap-3">
                            <Link
                              className="text-sm font-medium text-orange-600 hover:text-orange-700"
                              to={orderPath}
                              onClick={(e) => e.stopPropagation()}
                            >
                              Open
                            </Link>
                            {isAdmin && orderHasBatch(o) ? (
                              <button
                                type="button"
                                className="text-sm font-medium text-gray-600 hover:text-gray-900 disabled:cursor-not-allowed disabled:opacity-50"
                                disabled={batchBusy}
                                onClick={(e) => {
                                  e.stopPropagation();
                                  handleUnassignFromBatch(o);
                                }}
                              >
                                {unassigningId != null && Number(unassigningId) === Number(id)
                                  ? 'Removing…'
                                  : 'Remove from batch'}
                              </button>
                            ) : null}
                          </div>
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>
          ) : null}
        </section>
      </main>
    </>
  );
}
