import { useCallback, useEffect, useRef, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { UserHeader } from '../components/layout/UserHeader.jsx';
import { Badge } from '../components/ui/Badge.jsx';
import { Button } from '../components/ui/Button.jsx';
import { Input } from '../components/ui/Input.jsx';
import { useToast } from '../context/ToastContext.jsx';
import { useSyncedWalletBalance } from '../hooks/useSyncedWalletBalance.js';
import { apiMediaUrl } from '../lib/apiConfig.js';
import { getStoredUser } from '../lib/authSession.js';
import { ORDER_FULFILLMENT_STATUS_SEQUENCE } from '../lib/orderStatuses.js';
import {
  assignOrderToAgentFromApi,
  fetchOrderFromApi,
  fetchOrderThreadsFromApi,
  payOrderFromWalletFromApi,
  postChangeOrderStatus,
  postOrderThread,
} from '../lib/ordersApi.js';
import { formatNaira } from '../lib/userDisplay.js';
import { cn } from '../lib/cn.js';
import {
  hasInlineHtmlMessage,
  isFullHtmlDocumentMessage,
  sanitizeMessageHtml,
} from '../lib/htmlMessages.js';
import { initialsFromName } from '../lib/userDisplay.js';

function isOrderFulfillmentFinalized(status) {
  const s = String(status || '').toLowerCase();
  return s === 'completed' || s === 'cancelled';
}

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
  if (s === 'cancelled') return 'rejected';
  if (s === 'completed') return 'delivered';
  if (s === 'pending') return 'pending';
  if (s === 'paid' || s === 'assigned') return 'approved';
  return 'default';
}

function fulfillmentStatusSelectOptions(currentRaw) {
  const cur = String(currentRaw ?? '').trim();
  const lower = cur.toLowerCase();
  const base = ORDER_FULFILLMENT_STATUS_SEQUENCE.map((value) => ({
    value,
    label: formatOrderStatusLabel(value),
  }));
  if (cur && !ORDER_FULFILLMENT_STATUS_SEQUENCE.includes(lower)) {
    return [{ value: cur, label: `${formatOrderStatusLabel(cur)} (on record)` }, ...base];
  }
  return base;
}

function paymentStatusBadgeVariant(status) {
  const s = String(status || '').toLowerCase();
  if (s === 'paid' || s === 'successful') return 'approved';
  if (s === 'pending') return 'pending';
  if (s === 'failed') return 'rejected';
  return 'default';
}

function splitProductLinks(linkField) {
  if (linkField == null || typeof linkField !== 'string') return [];
  return linkField
    .split(/\r?\n/)
    .map((s) => s.trim())
    .filter(Boolean);
}

function orderLineTotal(o) {
  if (o?.grand_total_naira != null && o.grand_total_naira !== '') {
    return Number(o.grand_total_naira) || 0;
  }
  const sub = Number(o.total_amount_usd ?? o.total_amount) || 0;
  const svc = Number(o.service_charge_usd ?? o.service_charge) || 0;
  const ship = Number(o.shipping_cost_usd ?? o.shipping_cost) || 0;
  return sub + svc + ship;
}

function formatDollar(amount) {
  const n = Number(String(amount ?? '').replace(/,/g, '').trim());
  if (Number.isNaN(n)) return '$0.00';
  return `$${n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

function agentDisplayName(agent) {
  const name = typeof agent?.name === 'string' ? agent.name.trim() : '';
  const email = typeof agent?.email === 'string' ? agent.email.trim() : '';
  if (name && email) return `${name} (${email})`;
  if (name) return name;
  if (email) return email;
  return agent?.id != null ? `Agent #${agent.id}` : 'Unassigned';
}

function sortThreadsChronological(threads) {
  return [...threads].sort((a, b) => {
    const ta = new Date(String(a.created_at || '').replace(' ', 'T')).getTime();
    const tb = new Date(String(b.created_at || '').replace(' ', 'T')).getTime();
    return ta - tb;
  });
}

function OrderThreadEntry({ thread, currentUserId, onDelete, deleting }) {
  const senderId = thread.sender_id != null ? Number(thread.sender_id) : NaN;
  const isSelf = Number(currentUserId) === senderId && !Number.isNaN(senderId);
  const senderName =
    typeof thread.user?.name === 'string' && thread.user.name.trim()
      ? thread.user.name.trim()
      : isSelf
        ? 'You'
        : 'Sender';
  const label = isSelf ? 'You' : senderName;
  const attachment =
    thread.attachment_url && String(thread.attachment_url).trim()
      ? apiMediaUrl(String(thread.attachment_url).trim())
      : '';
  const fullHtml = isFullHtmlDocumentMessage(thread.message);
  const inlineHtml = !fullHtml && hasInlineHtmlMessage(thread.message);
  const isImage =
    attachment && /\.(jpe?g|png|gif|webp)(\?|$)/i.test(attachment);

  return (
    <article
      className={`rounded-2xl border px-4 py-3 ${
        isSelf ? 'ml-8 border-blue-200 bg-blue-50' : 'mr-8 border-gray-200 bg-gray-50'
      }`}
    >
      <p className="text-xs font-semibold text-gray-600">{label}</p>
      {fullHtml ? (
        <iframe
          title={`Order message ${thread.id}`}
          sandbox="allow-popups allow-top-navigation-by-user-activation"
          className="mt-2 w-full min-h-[20rem] rounded-lg border border-gray-200 bg-white"
          srcDoc={thread.message}
        />
      ) : inlineHtml ? (
        <div
          className="mt-2 whitespace-pre-wrap break-words text-sm text-gray-900 [&_a]:font-medium [&_a]:text-orange-600 [&_a]:underline [&_a:hover]:text-orange-700 [&_blockquote]:border-l-2 [&_blockquote]:border-gray-300 [&_blockquote]:pl-3 [&_code]:rounded [&_code]:bg-white [&_code]:px-1 [&_code]:py-0.5 [&_pre]:overflow-x-auto [&_table]:w-full [&_table]:border-collapse [&_td]:border [&_td]:border-gray-200 [&_td]:px-2 [&_td]:py-1 [&_th]:border [&_th]:border-gray-200 [&_th]:px-2 [&_th]:py-1 [&_th]:text-left"
          dangerouslySetInnerHTML={{ __html: sanitizeMessageHtml(thread.message) }}
        />
      ) : thread.message && String(thread.message).trim() ? (
        <p className="mt-2 whitespace-pre-wrap text-sm text-gray-900">{thread.message}</p>
      ) : null}
      {attachment ? (
        <div className="mt-3">
          {isImage ? (
            <a href={attachment} target="_blank" rel="noopener noreferrer" className="block">
              <img src={attachment} alt="Attachment" className="max-h-64 rounded-lg border border-gray-200 object-contain" />
            </a>
          ) : (
            <a
              href={attachment}
              target="_blank"
              rel="noopener noreferrer"
              className="text-sm font-medium text-orange-600 hover:text-orange-700"
            >
              Download attachment →
            </a>
          )}
        </div>
      ) : null}
      <div className="mt-2 flex items-center justify-between gap-2">
        <p className="text-xs text-gray-500">{formatOrderDate(thread.created_at)}</p>
        {onDelete ? (
          <button
            type="button"
            disabled={deleting}
            onClick={() => onDelete(thread.id)}
            className="text-xs font-medium text-red-500 hover:text-red-700 disabled:opacity-50"
          >
            {deleting ? 'Deleting…' : 'Delete'}
          </button>
        ) : null}
      </div>
    </article>
  );
}

export function OrderDetailPage() {
  const { orderId } = useParams();
  const navigate = useNavigate();
  const { showToast } = useToast();
  const user = getStoredUser();
  const [balanceLabel] = useSyncedWalletBalance();
  const [loading, setLoading] = useState(true);
  const [loadError, setLoadError] = useState('');
  const [order, setOrder] = useState(null);
  const [threads, setThreads] = useState([]);
  const [threadsLoading, setThreadsLoading] = useState(false);
  const [threadsError, setThreadsError] = useState('');
  const [composeMessage, setComposeMessage] = useState('');
  const [composeSubmitting, setComposeSubmitting] = useState(false);
  const [composeError, setComposeError] = useState('');
  const composeFileRef = useRef(null);
  const [adjustAmount, setAdjustAmount] = useState('');
  const [adjustSubmitting, setAdjustSubmitting] = useState(false);
  const [adjustError, setAdjustError] = useState('');
  const [statusSelectValue, setStatusSelectValue] = useState('');
  const [statusCodeValue, setStatusCodeValue] = useState('');
  const [statusChangeSubmitting, setStatusChangeSubmitting] = useState(false);
  const [statusChangeError, setStatusChangeError] = useState('');
  const [agents, setAgents] = useState([]);
  const [agentsLoading, setAgentsLoading] = useState(false);
  const [agentsError, setAgentsError] = useState('');
  const [selectedAgentId, setSelectedAgentId] = useState('');
  const [agentAssignSubmitting, setAgentAssignSubmitting] = useState(false);
  const [agentAssignError, setAgentAssignError] = useState('');
  const [deletingThreadId, setDeletingThreadId] = useState(null);

  const isAdmin = String(user?.role || '').toLowerCase() === 'admin';

  const loadOrder = useCallback(async () => {
    const u = getStoredUser();
    if (!u?.token || u.id == null) {
      navigate('/login', { replace: true });
      return;
    }
    setLoading(true);
    setLoadError('');
    const r = await fetchOrderFromApi(u, orderId);
    setLoading(false);
    if (!r.ok) {
      setLoadError(typeof r.message === 'string' && r.message.length > 0 ? r.message : 'Could not load this order.');
      setOrder(null);
      return;
    }
    const o = r.order;
    const role = String(u.role || '').toLowerCase();
    const isOwner = Number(o.user_id) === Number(u.id);
    const isAssignedAgent = role === 'agent' && Number(o.agent_id) === Number(u.id);
    if (!isAdmin && !isOwner && !isAssignedAgent) {
      setLoadError('You can only open orders assigned to you.');
      setOrder(null);
      return;
    }
    setOrder(o);
    setLoadError('');
  }, [orderId, navigate, isAdmin]);

  useEffect(() => {
    loadOrder();
  }, [loadOrder]);

  useEffect(() => {
    if (!isAdmin) {
      setAgents([]);
      setAgentsError('');
      setAgentsLoading(false);
      return;
    }
    setAgents([]);
    setAgentsLoading(false);
    setAgentsError('Enter an agent user id (no agents list endpoint on v2).');
  }, [isAdmin]);

  useEffect(() => {
    if (!order?.id) {
      setThreads([]);
      setThreadsError('');
      return;
    }
    let cancelled = false;
    const oid = order.id;
    setThreadsLoading(true);
    setThreadsError('');
    fetchOrderThreadsFromApi(getStoredUser(), oid).then((r) => {
      if (cancelled) return;
      setThreadsLoading(false);
      if (!r.ok) {
        setThreadsError(typeof r.message === 'string' && r.message.length > 0 ? r.message : 'Could not load messages.');
        setThreads([]);
        return;
      }
      setThreads(sortThreadsChronological(r.threads));
    });
    return () => {
      cancelled = true;
    };
  }, [order?.id]);

  useEffect(() => {
    if (!order) return;
    const v = order.total_amount_usd ?? order.total_amount;
    setAdjustAmount(v != null && v !== '' ? String(v) : '');
    setAdjustError('');
  }, [order?.id, order?.total_amount_usd, order?.total_amount]);

  useEffect(() => {
    if (!order) return;
    const st = String(order.status || '').trim();
    setStatusSelectValue(st || 'pending');
    setStatusCodeValue(order.pickup_otp_code != null ? String(order.pickup_otp_code) : '');
    setStatusChangeError('');
  }, [order?.id, order?.status, order?.pickup_otp_code]);

  useEffect(() => {
    if (!order) return;
    const assignedId = Number(order.agent_id) > 0 ? String(order.agent_id) : '';
    setSelectedAgentId(assignedId);
    setAgentAssignError('');
  }, [order?.id, order?.agent_id]);

  async function handleChangeOrderStatus(e) {
    e.preventDefault();
    if (order && isOrderFulfillmentFinalized(order.status)) return;
    setStatusChangeError('');
    const status = statusSelectValue.trim();
    if (!status) {
      setStatusChangeError('Choose a status.');
      return;
    }
    const u = getStoredUser();
    if (!u?.token || order?.id == null) {
      navigate('/login', { replace: true });
      return;
    }
    setStatusChangeSubmitting(true);
    try {
      const r = await postChangeOrderStatus(u, order.id, {
        status,
        code: statusCodeValue,
      });
      if (!r.ok) {
        const m =
          typeof r.message === 'string' && r.message.length > 0 ? r.message : 'Could not change status.';
        setStatusChangeError(m);
        showToast(m, 'error');
        return;
      }
      if (r.order) {
        setOrder(r.order);
      } else {
        const fr = await fetchOrderFromApi(u, order.id);
        if (fr.ok) setOrder(fr.order);
      }
      showToast(
        typeof r.data?.message === 'string' && r.data.message.length > 0 ? r.data.message : 'Status updated',
        'success'
      );
    } catch {
      const m = 'Network error. Check that the API is running.';
      setStatusChangeError(m);
      showToast(m, 'error');
    } finally {
      setStatusChangeSubmitting(false);
    }
  }

  async function handleAdjustPrice(e) {
    e.preventDefault();
    setAdjustError('Price adjustment is not available on this API yet.');
    showToast('Price adjustment is not available on this API yet.', 'error');
  }

  async function handlePayFromWallet() {
    const u = getStoredUser();
    if (!u?.token || order?.id == null) {
      navigate('/login', { replace: true });
      return;
    }
    setAdjustSubmitting(true);
    try {
      const r = await payOrderFromWalletFromApi(u, order.id);
      if (!r.ok) {
        const m = typeof r.message === 'string' && r.message.length > 0 ? r.message : 'Could not pay from wallet.';
        showToast(m, 'error');
        return;
      }
      if (r.order) setOrder(r.order);
      else {
        const fr = await fetchOrderFromApi(u, order.id);
        if (fr.ok) setOrder(fr.order);
      }
      showToast(r.message || 'Paid from wallet.', 'success');
    } catch {
      showToast('Network error. Check that the API is running.', 'error');
    } finally {
      setAdjustSubmitting(false);
    }
  }

  async function refreshOrderAfterAgentChange(u, fallbackOrder) {
    if (fallbackOrder) {
      setOrder(fallbackOrder);
      return;
    }
    const fr = await fetchOrderFromApi(u, order.id);
    if (fr.ok) setOrder(fr.order);
  }

  async function handleAssignAgent(e) {
    e.preventDefault();
    setAgentAssignError('');
    const agentId = selectedAgentId.trim();
    if (!agentId) {
      setAgentAssignError('Choose an agent.');
      return;
    }
    const u = getStoredUser();
    if (!u?.token || order?.id == null) {
      navigate('/login', { replace: true });
      return;
    }
    setAgentAssignSubmitting(true);
    try {
      const r = await assignOrderToAgentFromApi(u, order.id, agentId);
      if (!r.ok) {
        const m = typeof r.message === 'string' && r.message.length > 0 ? r.message : 'Could not assign agent.';
        setAgentAssignError(m);
        showToast(m, 'error');
        return;
      }
      await refreshOrderAfterAgentChange(u, r.order);
      showToast(r.message || 'Agent assigned.', 'success');
    } catch {
      const m = 'Network error. Check that the API is running.';
      setAgentAssignError(m);
      showToast(m, 'error');
    } finally {
      setAgentAssignSubmitting(false);
    }
  }

  async function handleUnassignAgent() {
    setAgentAssignError('Unassign agent is not available on this API.');
    showToast('Unassign agent is not available on this API.', 'error');
  }

  async function handleDeleteThread() {
    showToast('Deleting thread messages is not available on this API.', 'error');
  }

  async function handlePostThread(e) {
    e.preventDefault();
    setComposeError('');
    const msg = composeMessage.trim();
    const file = composeFileRef.current?.files?.[0] ?? null;
    if (!msg && !file) {
      setComposeError('Add a message or attach a file.');
      return;
    }
    const u = getStoredUser();
    if (!u?.token || order?.id == null) {
      navigate('/login', { replace: true });
      return;
    }
    setComposeSubmitting(true);
    try {
      const r = await postOrderThread(u, order.id, { message: msg, file });
      if (!r.ok) {
        const m = typeof r.message === 'string' && r.message.length > 0 ? r.message : 'Could not send message.';
        setComposeError(m);
        showToast(m, 'error');
        return;
      }
      setThreads((prev) => sortThreadsChronological([...prev, r.thread]));
      setComposeMessage('');
      if (composeFileRef.current) composeFileRef.current.value = '';
      showToast(
        typeof r.data?.message === 'string' && r.data.message.length > 0 ? r.data.message : 'Message sent',
        'success'
      );
    } catch {
      const m = 'Network error. Check that the API is running.';
      setComposeError(m);
      showToast(m, 'error');
    } finally {
      setComposeSubmitting(false);
    }
  }

  const statusLabel = order ? formatOrderStatusLabel(order.status) : '';
  const payLabel = order
    ? String(order.status || '').toLowerCase() === 'paid' || String(order.status || '').toLowerCase() === 'completed'
      ? 'Paid / settled'
      : String(order.status || '').toLowerCase() === 'pending'
        ? 'Awaiting payment'
        : formatOrderStatusLabel(order.status)
    : '';
  const orderFulfillmentLocked = order != null && isOrderFulfillmentFinalized(order.status);
  const assignedAgent =
    order?.agent && typeof order.agent === 'object'
      ? order.agent
      : agents.find((agent) => String(agent.id) === String(order?.agent_id));
  const hasAssignedAgent = Number(order?.agent_id) > 0;
  const links = order ? splitProductLinks(order.link) : [];
  const shotUrl =
    order && order.screen_shot1 && String(order.screen_shot1).trim()
      ? apiMediaUrl(String(order.screen_shot1).trim())
      : '';

  return (
    <>
      <UserHeader
        title={loading ? `Order #${orderId}` : order ? `Order #${order.id}` : `Order #${orderId}`}
        subtitle={
          loading
            ? 'Loading details…'
            : order
              ? `${formatOrderStatusLabel(order.status)} · Placed ${formatOrderDate(order.created_at)}`
              : 'Details, status, and updates'
        }
        backTo="/orders"
        backLabel="Back to orders"
        right={
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
        }
      />
      <main className="flex-1 space-y-6 p-4 sm:p-6 lg:p-8">
        {loading ? (
          <p className="text-sm text-gray-500">Loading order…</p>
        ) : loadError ? (
          <section className="rounded-2xl border border-red-100 bg-red-50 p-6 text-sm text-red-800">
            <p>{loadError}</p>
            <Link to="/orders" className="mt-3 inline-block font-medium text-orange-700 hover:text-orange-800">
              Back to orders
            </Link>
          </section>
        ) : order ? (
          <>
            <section className="grid gap-6 lg:grid-cols-3">
              <div className="flex min-w-0 flex-col gap-6 lg:col-span-2">
                <article className="rounded-2xl bg-white p-6 shadow-md">
                <h2 className="text-base font-semibold text-gray-900">Product</h2>
                <p className="mt-2 text-xs font-medium uppercase tracking-wide text-gray-400">Reference</p>
                <p className="mt-1 font-mono text-sm text-gray-800">{order.reference || '—'}</p>

                {order.type === 'online' && links.length > 0 ? (
                  <>
                    <p className="mt-4 text-sm text-gray-600">Product link{links.length > 1 ? 's' : ''}</p>
                    <ul className="mt-2 space-y-2">
                      {links.map((href) => (
                        <li key={href.slice(0, 80)}>
                          <a
                            href={href}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="break-all text-sm font-medium text-orange-600 hover:text-orange-700"
                          >
                            {href.length > 96 ? `${href.slice(0, 94)}…` : href}
                          </a>
                        </li>
                      ))}
                    </ul>
                  </>
                ) : null}

                {order.description ? (
                  <>
                    <p className="mt-4 text-sm text-gray-600">Your notes</p>
                    <p className="mt-1 whitespace-pre-wrap text-sm text-gray-900">{order.description}</p>
                  </>
                ) : null}

                {shotUrl ? (
                  <div className="mt-6">
                    <p className="text-sm font-medium text-gray-700">Screenshot</p>
                    <div className="mt-2 overflow-hidden rounded-2xl border border-gray-200 bg-gray-100 shadow-inner">
                      <img src={shotUrl} alt="Order reference screenshot" className="max-h-96 w-full object-contain" />
                    </div>
                  </div>
                ) : null}
                </article>

                <section className="rounded-2xl bg-white p-6 shadow-md" aria-label="Order status">
                <h2 className="text-base font-semibold text-gray-900">Status</h2>
                <p className="mt-1 text-sm text-gray-500">Fulfillment and payment for this order.</p>
                <div className="mt-4 flex flex-wrap items-center gap-2">
                  <Badge variant={orderStatusBadgeVariant(order.status)}>{statusLabel}</Badge>
                  <Badge variant={paymentStatusBadgeVariant(order.status)}>
                    {payLabel}
                  </Badge>
                </div>
                <dl className="mt-4 grid gap-2 text-sm sm:grid-cols-2">
                  <div>
                    <dt className="text-gray-500">Placed</dt>
                    <dd className="text-gray-900">{formatOrderDate(order.created_at)}</dd>
                  </div>
                  {order.pickup_otp_code || order.code ? (
                    <div>
                      <dt className="text-gray-500">Pickup code</dt>
                      <dd className="font-mono text-gray-900">{order.pickup_otp_code || order.code}</dd>
                    </div>
                  ) : null}
                </dl>
                {!isAdmin &&
                String(order.status || '').toLowerCase() === 'pending' &&
                Number(order.price_adjustment_sent) === 1 ? (
                  <div className="mt-4">
                    <Button type="button" variant="orange" disabled={adjustSubmitting} onClick={handlePayFromWallet}>
                      {adjustSubmitting ? 'Paying…' : 'Pay from wallet'}
                    </Button>
                  </div>
                ) : null}
                </section>

                <section className="rounded-2xl bg-white p-6 shadow-md" aria-labelledby="updates-heading">
                <div className="flex flex-col gap-1 sm:flex-row sm:items-baseline sm:justify-between">
                  <h2 id="updates-heading" className="text-base font-semibold text-gray-900">
                    Order thread
                  </h2>
                  {threadsLoading ? (
                    <span className="text-xs text-gray-500" aria-busy="true">
                      Loading messages…
                    </span>
                  ) : threads.length > 0 ? (
                    <span className="text-xs text-gray-500">
                      {threads.length} {threads.length === 1 ? 'message' : 'messages'}
                    </span>
                  ) : null}
                </div>
                <p className="mt-1 text-sm text-gray-500">
                  Updates from the team and payment notices appear below. Links open in a new tab when you choose them.
                </p>
                {threadsError ? (
                  <p className="mt-4 text-sm text-red-600" role="alert">
                    {threadsError}
                  </p>
                ) : null}
                {!threadsLoading && !threadsError && threads.length === 0 ? (
                  <p className="mt-4 text-sm text-gray-500">No messages on this order yet.</p>
                ) : null}
                {threads.length > 0 ? (
                  <div className="mt-6 flex max-h-[min(70vh,40rem)] flex-col gap-4 overflow-y-auto pr-1">
                    {threads.map((t) => (
                      <OrderThreadEntry
                        key={t.id}
                        thread={t}
                        currentUserId={user?.id}
                        onDelete={isAdmin ? handleDeleteThread : null}
                        deleting={deletingThreadId === t.id}
                      />
                    ))}
                  </div>
                ) : null}

                <div className="mt-6 border-t border-gray-100 pt-6">
                  <h3 className="text-sm font-semibold text-gray-900">Add a message</h3>
                  <p className="mt-1 text-xs text-gray-500">Optional image attachment (JPG, PNG, etc.).</p>
                  <form className="mt-3 flex flex-col gap-3" onSubmit={handlePostThread} noValidate>
                    {composeError ? (
                      <p className="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800" role="alert">
                        {composeError}
                      </p>
                    ) : null}
                    <label htmlFor="order-thread-message" className="sr-only">
                      Message
                    </label>
                    <textarea
                      id="order-thread-message"
                      name="message"
                      rows={3}
                      value={composeMessage}
                      onChange={(ev) => setComposeMessage(ev.target.value)}
                      disabled={composeSubmitting}
                      className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:bg-gray-50"
                      placeholder="Type a message…"
                    />
                    <div className="flex flex-wrap items-center gap-3">
                      <label className="inline-flex cursor-pointer items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 disabled:opacity-50">
                        <span>Attach file</span>
                        <input
                          ref={composeFileRef}
                          type="file"
                          name="attachment_url"
                          accept="image/*"
                          className="sr-only"
                          disabled={composeSubmitting}
                        />
                      </label>
                      <Button type="submit" disabled={composeSubmitting}>
                        {composeSubmitting ? 'Sending…' : 'Send'}
                      </Button>
                    </div>
                  </form>
                </div>
                </section>
              </div>

              <article className="rounded-2xl bg-white p-6 shadow-md">
                <h2 className="text-base font-semibold text-gray-900">Price breakdown</h2>
                <dl className="mt-4 space-y-3 text-sm">
                  <div className="flex justify-between gap-4">
                    <dt className="text-gray-500">Item (USD)</dt>
                    <dd className="tabular-nums text-gray-900">
                      {formatDollar(order.total_amount_usd ?? order.total_amount)}
                    </dd>
                  </div>
                  <div className="flex justify-between gap-4">
                    <dt className="text-gray-500">Service charge (USD)</dt>
                    <dd className="tabular-nums text-gray-900">
                      {formatDollar(order.service_charge_usd ?? order.service_charge)}
                    </dd>
                  </div>
                  <div className="flex justify-between gap-4">
                    <dt className="text-gray-500">Shipping (USD)</dt>
                    <dd className="tabular-nums text-gray-900">
                      {formatDollar(order.shipping_cost_usd ?? order.shipping_cost)}
                    </dd>
                  </div>
                  {order.dollar_to_naira_rate != null && order.dollar_to_naira_rate !== '' ? (
                    <div className="flex justify-between gap-4">
                      <dt className="text-gray-500">FX rate</dt>
                      <dd className="tabular-nums text-gray-900">{order.dollar_to_naira_rate}</dd>
                    </div>
                  ) : null}
                  <div className="flex justify-between gap-4 border-t border-gray-100 pt-3 font-semibold">
                    <dt className="text-gray-900">Grand total (₦)</dt>
                    <dd className="tabular-nums text-blue-900">
                      {order.grand_total_naira != null && order.grand_total_naira !== ''
                        ? formatNaira(order.grand_total_naira)
                        : formatDollar(orderLineTotal(order))}
                    </dd>
                  </div>
                </dl>

                {typeof order.payment_link === 'string' && order.payment_link.startsWith('http') ? (
                  <div className="mt-4">
                    <a
                      href={order.payment_link}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="inline-flex text-sm font-medium text-orange-600 hover:text-orange-700"
                    >
                      Open payment link →
                    </a>
                  </div>
                ) : null}

                {isAdmin ? (
                  <>
                  <div
                    className={cn(
                      'mt-6 border-t border-gray-200 pt-5 transition-opacity',
                      orderFulfillmentLocked && 'pointer-events-none opacity-50'
                    )}
                  >
                    <h3 className="text-sm font-semibold text-gray-900">Price tools</h3>
                    <p className="mt-1 text-xs text-gray-500">
                      Adjust-price is not exposed on v2 yet. Amounts shown above come from the snappy order record.
                    </p>
                    {adjustError ? (
                      <p className="mt-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-900">
                        {adjustError}
                      </p>
                    ) : null}
                  </div>

                  <div className="mt-6 border-t border-gray-200 pt-5">
                    <h3 className="text-sm font-semibold text-gray-900">Assign agent</h3>
                    <p className="mt-1 text-xs text-gray-500">
                      POST /v2/snappy-orders/&#123;id&#125;/assign-to-agent with an agent user id.
                    </p>
                    <dl className="mt-3 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm">
                      <div className="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                        <dt className="text-gray-500">Current agent id</dt>
                        <dd className="font-medium text-gray-900">
                          {hasAssignedAgent ? `#${order.agent_id}` : 'Unassigned'}
                        </dd>
                      </div>
                    </dl>
                    <form className="mt-3 space-y-3" onSubmit={handleAssignAgent} noValidate>
                      {agentAssignError ? (
                        <p
                          className="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800"
                          role="alert"
                        >
                          {agentAssignError}
                        </p>
                      ) : null}
                      {agentsError ? (
                        <p className="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-900">
                          {agentsError}
                        </p>
                      ) : null}
                      <Input
                        id="assign-order-agent"
                        name="agent_id"
                        label="Agent user id"
                        type="text"
                        inputMode="numeric"
                        value={selectedAgentId}
                        onChange={(ev) => setSelectedAgentId(ev.target.value)}
                        disabled={agentAssignSubmitting}
                        placeholder="e.g. 12"
                      />
                      <Button
                        type="submit"
                        variant="orange"
                        disabled={agentAssignSubmitting || !selectedAgentId}
                        className="w-full sm:w-auto"
                      >
                        {agentAssignSubmitting ? 'Saving…' : hasAssignedAgent ? 'Update agent' : 'Assign agent'}
                      </Button>
                    </form>
                  </div>

                  <div
                    className={cn(
                      'mt-6 flex justify-end border-t border-gray-200 pt-5 transition-opacity',
                      orderFulfillmentLocked && 'pointer-events-none opacity-50'
                    )}
                  >
                    <div className="w-full max-w-sm space-y-3">
                      <h3 className="text-sm font-semibold text-gray-900">Change fulfillment status</h3>
                      <p className="text-xs text-gray-500">
                        Valid statuses: pending, paid, assigned, completed, cancelled. The server rejects invalid
                        transitions.
                      </p>
                      {orderFulfillmentLocked ? (
                        <p className="text-xs font-medium text-gray-500">
                          Status is final for this order ({formatOrderStatusLabel(order.status)}).
                        </p>
                      ) : null}
                      <form className="space-y-3" onSubmit={handleChangeOrderStatus} noValidate>
                        {statusChangeError ? (
                          <p
                            className="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800"
                            role="alert"
                          >
                            {statusChangeError}
                          </p>
                        ) : null}
                        <div>
                          <label htmlFor="change-order-status" className="block text-sm font-medium text-gray-700">
                            New status
                          </label>
                          <select
                            id="change-order-status"
                            name="status"
                            value={statusSelectValue}
                            onChange={(ev) => setStatusSelectValue(ev.target.value)}
                            disabled={statusChangeSubmitting || orderFulfillmentLocked}
                            className="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:bg-gray-100 disabled:text-gray-500"
                          >
                            {fulfillmentStatusSelectOptions(order.status).map((opt) => (
                              <option key={opt.value} value={opt.value}>
                                {opt.label}
                              </option>
                            ))}
                          </select>
                        </div>
                        <Input
                          id="change-order-code"
                          name="code"
                          label="Pickup code (optional)"
                          type="text"
                          value={statusCodeValue}
                          onChange={(ev) => setStatusCodeValue(ev.target.value)}
                          disabled={statusChangeSubmitting || orderFulfillmentLocked}
                          placeholder="957800"
                        />
                        <Button
                          type="submit"
                          variant="orange"
                          disabled={statusChangeSubmitting || orderFulfillmentLocked}
                          className="w-full sm:w-auto"
                        >
                          {statusChangeSubmitting ? 'Updating…' : 'Update status'}
                        </Button>
                      </form>
                    </div>
                  </div>
                  </>
                ) : null}
              </article>
            </section>
          </>
        ) : null}
      </main>
    </>
  );
}
