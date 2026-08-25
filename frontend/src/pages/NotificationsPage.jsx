import { useCallback, useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { UserHeader } from '../components/layout/UserHeader.jsx';
import { Badge } from '../components/ui/Badge.jsx';
import { Button } from '../components/ui/Button.jsx';
import { useToast } from '../context/ToastContext.jsx';
import { useSyncedWalletBalance } from '../hooks/useSyncedWalletBalance.js';
import { getStoredUser } from '../lib/authSession.js';
import {
  deleteNotificationOnApi,
  fetchNotificationsFromApi,
  markNotificationReadOnApi,
  notifyNotificationsChanged,
} from '../lib/userApi.js';
import {
  hasInlineHtmlMessage,
  isFullHtmlDocumentMessage,
  sanitizeMessageHtml,
} from '../lib/htmlMessages.js';
import { initialsFromName } from '../lib/userDisplay.js';

function formatNotificationDate(value) {
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

function isNotificationUnread(n) {
  if (n?.is_read === true || n?.is_read === 1 || n?.is_read === '1') return false;
  if (n?.read_at) return false;
  const s = String(n?.read_status || '').toLowerCase();
  if (s === 'read') return false;
  return true;
}

function readStatusBadge(n) {
  if (isNotificationUnread(n)) return { variant: 'pending', label: 'Unread' };
  return { variant: 'default', label: 'Read' };
}

function NotificationMessage({ id, message }) {
  if (!message || !String(message).trim()) return null;

  if (isFullHtmlDocumentMessage(message)) {
    return (
      <iframe
        title={`Notification message ${id}`}
        sandbox="allow-popups allow-top-navigation-by-user-activation"
        className="mt-2 w-full min-h-[16rem] rounded-lg border border-gray-200 bg-white"
        srcDoc={message}
      />
    );
  }

  if (hasInlineHtmlMessage(message)) {
    return (
      <div
        className="mt-1 whitespace-pre-wrap break-words text-sm text-gray-600 [&_a]:font-medium [&_a]:text-orange-600 [&_a]:underline [&_a:hover]:text-orange-700 [&_blockquote]:border-l-2 [&_blockquote]:border-gray-300 [&_blockquote]:pl-3 [&_code]:rounded [&_code]:bg-gray-100 [&_code]:px-1 [&_code]:py-0.5 [&_pre]:overflow-x-auto [&_table]:w-full [&_table]:border-collapse [&_td]:border [&_td]:border-gray-200 [&_td]:px-2 [&_td]:py-1 [&_th]:border [&_th]:border-gray-200 [&_th]:px-2 [&_th]:py-1 [&_th]:text-left"
        dangerouslySetInnerHTML={{ __html: sanitizeMessageHtml(message) }}
      />
    );
  }

  return <p className="mt-1 text-sm text-gray-600">{message}</p>;
}

export function NotificationsPage() {
  const navigate = useNavigate();
  const user = getStoredUser();
  const { showToast } = useToast();
  const [balanceLabel] = useSyncedWalletBalance();
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [items, setItems] = useState([]);
  const [markingReadId, setMarkingReadId] = useState(null);
  const [deletingId, setDeletingId] = useState(null);

  const load = useCallback(async () => {
    const u = getStoredUser();
    if (!u?.token || u.id == null) {
      navigate('/login', { replace: true });
      return;
    }
    setLoading(true);
    setError('');
    const r = await fetchNotificationsFromApi(u);
    setLoading(false);
    if (!r.ok) {
      setError(typeof r.message === 'string' && r.message.length > 0 ? r.message : 'Could not load notifications.');
      setItems([]);
      return;
    }
    const sorted = [...r.notifications].sort((a, b) => {
      const ta = new Date(String(a.created_at || '').replace(' ', 'T')).getTime();
      const tb = new Date(String(b.created_at || '').replace(' ', 'T')).getTime();
      return tb - ta;
    });
    setItems(sorted);
  }, [navigate]);

  useEffect(() => {
    load();
  }, [load]);

  async function handleMarkRead(notificationId) {
    const u = getStoredUser();
    if (!u?.token || u.id == null) {
      navigate('/login', { replace: true });
      return;
    }
    setMarkingReadId(notificationId);
    const r = await markNotificationReadOnApi(u, notificationId);
    setMarkingReadId(null);
    if (!r.ok) {
      const msg =
        typeof r.message === 'string' && r.message.length > 0 ? r.message : 'Could not mark as read.';
      showToast(msg, 'error');
      return;
    }
    setItems((prev) =>
      prev.map((n) =>
        Number(n.id) === Number(notificationId)
          ? { ...n, is_read: 1, read_status: 'read', read_at: n.read_at || new Date().toISOString() }
          : n
      )
    );
    notifyNotificationsChanged();
    showToast(
      typeof r.message === 'string' && r.message.length > 0 ? r.message : 'Marked as read.',
      'success'
    );
  }

  async function handleDelete(notificationId) {
    const u = getStoredUser();
    if (!u?.token || u.id == null) {
      navigate('/login', { replace: true });
      return;
    }
    if (!window.confirm('Delete this notification?')) return;

    setDeletingId(notificationId);
    const r = await deleteNotificationOnApi(u, notificationId);
    setDeletingId(null);

    if (!r.ok) {
      const msg =
        typeof r.message === 'string' && r.message.length > 0 ? r.message : 'Could not delete notification.';
      showToast(msg, 'error');
      return;
    }

    setItems((prev) => prev.filter((n) => Number(n.id) !== Number(notificationId)));
    notifyNotificationsChanged();
    showToast(
      typeof r.message === 'string' && r.message.length > 0 ? r.message : 'Notification deleted.',
      'success'
    );
  }

  const unreadCount = items.filter((n) => isNotificationUnread(n)).length;
  const actionBusy = markingReadId != null || deletingId != null;

  return (
    <>
      <UserHeader
        title="Notifications"
        subtitle={unreadCount > 0 ? `${unreadCount} unread` : 'Updates about your account and orders'}
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
      <main className="flex-1 p-4 sm:p-6 lg:p-8">
        <section className="rounded-2xl bg-white p-6 shadow-md">
          <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
            {loading ? (
              <span className="text-xs text-gray-500" aria-busy="true">
                Loading…
              </span>
            ) : error ? (
              <span className="text-sm text-red-600">{error}</span>
            ) : (
              <span className="text-xs text-gray-500">
                {items.length} {items.length === 1 ? 'notification' : 'notifications'}
              </span>
            )}
            <Button type="button" variant="secondary" onClick={load} disabled={loading}>
              {loading ? 'Refreshing…' : 'Refresh'}
            </Button>
          </div>

          {!loading && !error && items.length === 0 ? (
            <p className="text-sm text-gray-500">No notifications yet.</p>
          ) : null}

          {items.length > 0 ? (
            <ul className="divide-y divide-gray-100 border-t border-gray-100">
              {items.map((n) => {
                const rb = readStatusBadge(n);
                const isUnread = isNotificationUnread(n);
                return (
                  <li
                    key={n.id}
                    className={`py-4 first:pt-0 ${isUnread ? 'border-l-4 border-l-blue-500 pl-3' : ''}`}
                  >
                    <div className="flex flex-wrap items-start justify-between gap-3">
                      <div className="min-w-0 flex-1">
                        <p className="font-medium text-gray-900">{n.title || 'Notification'}</p>
                        <NotificationMessage id={n.id} message={n.message} />
                        <div className="mt-2 flex flex-wrap items-center gap-2 text-xs text-gray-500">
                          <Badge variant={rb.variant}>{rb.label}</Badge>
                          {n.type ? (
                            <span className="rounded-full bg-gray-100 px-2 py-0.5 font-medium text-gray-600">
                              {String(n.type)}
                            </span>
                          ) : null}
                          {n.intent ? (
                            <span className="rounded-full bg-gray-100 px-2 py-0.5 font-medium text-gray-600">
                              {String(n.intent)}
                            </span>
                          ) : null}
                          <span className="tabular-nums">{formatNotificationDate(n.created_at)}</span>
                        </div>
                      </div>
                      <div className="flex shrink-0 flex-col items-stretch gap-2 sm:flex-row sm:items-center">
                        {isUnread ? (
                          <Button
                            type="button"
                            variant="secondary"
                            className="px-3 py-1.5 text-xs"
                            disabled={actionBusy}
                            onClick={() => handleMarkRead(n.id)}
                          >
                            {markingReadId != null && Number(markingReadId) === Number(n.id)
                              ? 'Marking…'
                              : 'Mark as read'}
                          </Button>
                        ) : null}
                        <Button
                          type="button"
                          variant="secondary"
                          className="border-red-200 px-3 py-1.5 text-xs text-red-600 hover:border-red-300 hover:bg-red-50 hover:text-red-700"
                          disabled={actionBusy}
                          onClick={() => handleDelete(n.id)}
                        >
                          {deletingId != null && Number(deletingId) === Number(n.id)
                            ? 'Deleting…'
                            : 'Delete'}
                        </Button>
                      </div>
                    </div>
                  </li>
                );
              })}
            </ul>
          ) : null}
        </section>
      </main>
    </>
  );
}
