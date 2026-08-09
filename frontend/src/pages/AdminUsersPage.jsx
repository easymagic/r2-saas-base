import { useCallback, useEffect, useState } from 'react';
import { Link, useNavigate, useSearchParams } from 'react-router-dom';
import { UserHeader } from '../components/layout/UserHeader.jsx';
import { Badge } from '../components/ui/Badge.jsx';
import { Button } from '../components/ui/Button.jsx';
import { Input } from '../components/ui/Input.jsx';
import { Modal } from '../components/ui/Modal.jsx';
import { useToast } from '../context/ToastContext.jsx';
import { getStoredUser } from '../lib/authSession.js';
import { cn } from '../lib/cn.js';
import { countryCodeOptions } from '../lib/countryCodes.js';
import { fetchAgentsFromApi, fetchInactiveAgentsFromApi } from '../lib/agentsApi.js';
import { createUserOnApi, fetchUserByIdFromApi, fetchUsersFromApi } from '../lib/userApi.js';
import { formatNaira } from '../lib/userDisplay.js';

const selectClassName = cn(
  'w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm',
  'focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500'
);

function initialCreateForm() {
  return {
    name: '',
    email: '',
    phone: '',
    country_code: '',
    role: 'customer',
    status: 'active',
    password: '',
  };
}

function formatCreatedDate(value) {
  if (value == null || value === '') return '—';
  const d = new Date(String(value).replace(' ', 'T'));
  if (Number.isNaN(d.getTime())) return String(value);
  return d.toLocaleDateString('en-NG', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
}

function statusBadge(status) {
  const s = String(status || '').toLowerCase();
  if (s === 'active') return { variant: 'approved', label: 'Active' };
  if (s === 'inactive') return { variant: 'rejected', label: 'Inactive' };
  if (s === 'pending') return { variant: 'pending', label: 'Pending' };
  return { variant: 'default', label: s || '—' };
}

function parsePageParam(value) {
  const n = parseInt(String(value ?? ''), 10);
  return Number.isFinite(n) && n > 0 ? n : 1;
}

function paginationPages(current, last) {
  if (last <= 1) return [];
  const width = 5;
  const start = Math.max(1, Math.min(current - 2, last - width + 1));
  const end = Math.min(last, start + width - 1);
  return Array.from({ length: end - start + 1 }, (_, i) => start + i);
}

export function AdminUsersPage() {
  const navigate = useNavigate();
  const [searchParams, setSearchParams] = useSearchParams();
  const page = parsePageParam(searchParams.get('page'));
  const { showToast } = useToast();
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [users, setUsers] = useState([]);
  const [total, setTotal] = useState(0);
  const [lastPage, setLastPage] = useState(1);
  const [hasNext, setHasNext] = useState(false);
  const [hasPrev, setHasPrev] = useState(false);
  const [listRefresh, setListRefresh] = useState(0);
  const [createOpen, setCreateOpen] = useState(false);
  const [createForm, setCreateForm] = useState(initialCreateForm);
  const [createSaving, setCreateSaving] = useState(false);
  const [createError, setCreateError] = useState('');
  const [showCreatePassword, setShowCreatePassword] = useState(false);
  const [inactiveAgents, setInactiveAgents] = useState([]);
  const [inactiveLoading, setInactiveLoading] = useState(true);
  const [inactiveError, setInactiveError] = useState('');
  const [allAgents, setAllAgents] = useState([]);
  const [allAgentsLoading, setAllAgentsLoading] = useState(true);
  const [allAgentsError, setAllAgentsError] = useState('');
  const [lookupId, setLookupId] = useState('');
  const [lookupLoading, setLookupLoading] = useState(false);

  const loadInactiveAgents = useCallback(async () => {
    const u = getStoredUser();
    if (!u?.token || u.id == null) {
      navigate('/login', { replace: true });
      return;
    }
    setInactiveLoading(true);
    setInactiveError('');
    const r = await fetchInactiveAgentsFromApi(u);
    setInactiveLoading(false);
    if (!r.ok) {
      setInactiveError(
        typeof r.message === 'string' && r.message.length > 0 ? r.message : 'Could not load inactive agents.'
      );
      setInactiveAgents([]);
      return;
    }
    const sorted = [...r.agents].sort((a, b) => Number(a.id || 0) - Number(b.id || 0));
    setInactiveAgents(sorted);
  }, [navigate]);

  const loadAllAgents = useCallback(async () => {
    const u = getStoredUser();
    if (!u?.token || u.id == null) {
      navigate('/login', { replace: true });
      return;
    }
    setAllAgentsLoading(true);
    setAllAgentsError('');
    const r = await fetchAgentsFromApi(u);
    setAllAgentsLoading(false);
    if (!r.ok) {
      setAllAgentsError(
        typeof r.message === 'string' && r.message.length > 0 ? r.message : 'Could not load agents.'
      );
      setAllAgents([]);
      return;
    }
    const sorted = [...r.agents].sort((a, b) => Number(a.id || 0) - Number(b.id || 0));
    setAllAgents(sorted);
  }, [navigate]);

  useEffect(() => {
    loadInactiveAgents();
    loadAllAgents();
  }, [loadInactiveAgents, loadAllAgents]);

  const loadUsers = useCallback(async () => {
    const u = getStoredUser();
    if (!u?.token || u.id == null) {
      navigate('/login', { replace: true });
      return;
    }
    setLoading(true);
    setError('');
    const r = await fetchUsersFromApi(u, page);
    setLoading(false);
    if (!r.ok) {
      setError(typeof r.message === 'string' && r.message.length > 0 ? r.message : 'Could not load users.');
      setUsers([]);
      setTotal(0);
      setLastPage(1);
      setHasNext(false);
      setHasPrev(false);
      return;
    }
    const sorted = [...r.users].sort((a, b) => Number(a.id || 0) - Number(b.id || 0));
    setUsers(sorted);
    setTotal(r.total);
    const lp = typeof r.lastPage === 'number' ? r.lastPage : 1;
    setLastPage(lp);
    setHasNext(!!r.hasNext);
    setHasPrev(!!r.hasPrev);
    if (r.lookupOnly) {
      setError('User list is not on v2 — look up a user by id, or create one below.');
    }
  }, [navigate, page, listRefresh]);

  async function handleLookupUser(e) {
    e.preventDefault();
    const id = lookupId.trim();
    if (!id) return;
    const u = getStoredUser();
    if (!u?.token) {
      navigate('/login', { replace: true });
      return;
    }
    setLookupLoading(true);
    const r = await fetchUserByIdFromApi(u, id);
    setLookupLoading(false);
    if (!r.ok || !r.user) {
      showToast(r.message || 'User not found.', 'error');
      return;
    }
    navigate(`/admin/users/${r.user.id}`);
  }

  useEffect(() => {
    loadUsers();
  }, [loadUsers]);

  function setCreateField(name, value) {
    setCreateForm((prev) => ({ ...prev, [name]: value }));
  }

  function goToPage(nextPage) {
    const safePage = Math.max(1, Math.min(Number(nextPage) || 1, Math.max(1, lastPage)));
    const next = new URLSearchParams(searchParams);
    next.set('page', String(safePage));
    setSearchParams(next, { replace: false });
  }

  function closeCreateModal() {
    setCreateOpen(false);
    setCreateForm(initialCreateForm());
    setCreateError('');
    setShowCreatePassword(false);
  }

  async function handleCreateUser(e) {
    e.preventDefault();
    setCreateError('');
    const session = getStoredUser();
    if (!session?.token || session.id == null) {
      navigate('/login', { replace: true });
      return;
    }
    if (!String(createForm.name ?? '').trim()) {
      setCreateError('Name is required.');
      return;
    }
    if (!String(createForm.email ?? '').trim()) {
      setCreateError('Email is required.');
      return;
    }
    if (!String(createForm.password ?? '').trim()) {
      setCreateError('Password is required.');
      return;
    }
    if (!String(createForm.country_code ?? '').trim()) {
      setCreateError('Country code is required.');
      return;
    }

    setCreateSaving(true);
    try {
      const r = await createUserOnApi(session, createForm);
      if (!r.ok) {
        const msg = typeof r.message === 'string' && r.message.length > 0 ? r.message : 'Could not create user.';
        setCreateError(msg);
        showToast(msg, 'error');
        return;
      }
      showToast(r.data?.message || 'User created successfully', 'success');
      closeCreateModal();
      goToPage(1);
      setListRefresh((t) => t + 1);
      loadInactiveAgents();
    } finally {
      setCreateSaving(false);
    }
  }

  return (
    <>
      <UserHeader title="Users" subtitle="Manage platform accounts and access" />
      <main className="flex-1 p-4 sm:p-6 lg:p-8">
        <section className="rounded-2xl bg-white p-6 shadow-md">
          <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
            {loading ? (
              <span
                className="inline-flex h-9 items-center rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-500"
                aria-busy="true"
              >
                Loading users...
              </span>
            ) : error ? (
              <span className="text-sm text-red-600">{error}</span>
            ) : (
              <span className="text-xs text-gray-500">
                Page {page}
                {lastPage > 1 ? ` of ${lastPage}` : ''}
                {` · ${users.length} on this page`}
                {total !== users.length || lastPage > 1 ? ` · ${total} total` : ''}
              </span>
            )}
            <form className="flex flex-wrap items-end gap-2" onSubmit={handleLookupUser}>
              <div>
                <label htmlFor="user-lookup-id" className="sr-only">
                  Look up user id
                </label>
                <input
                  id="user-lookup-id"
                  type="text"
                  inputMode="numeric"
                  placeholder="User id"
                  value={lookupId}
                  onChange={(ev) => setLookupId(ev.target.value)}
                  className="w-28 rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <Button type="submit" variant="secondary" disabled={lookupLoading || !lookupId.trim()}>
                {lookupLoading ? 'Looking up…' : 'Open by id'}
              </Button>
              <Button type="button" variant="orange" onClick={() => setCreateOpen(true)} disabled={loading}>
                Create user
              </Button>
              <Button
                type="button"
                variant="secondary"
                disabled={loading || !!error || !hasPrev}
                onClick={() => goToPage(page - 1)}
                aria-label="Previous page"
              >
                Previous
              </Button>
              <Button
                type="button"
                variant="secondary"
                disabled={loading || !!error || !hasNext}
                onClick={() => goToPage(page + 1)}
                aria-label="Next page"
              >
                Next
              </Button>
              <Button type="button" variant="secondary" onClick={loadUsers} disabled={loading}>
                Refresh
              </Button>
            </form>
          </div>
          <div className="overflow-x-auto">
            <table className="w-full min-w-[60rem] border-collapse text-left text-sm">
              <thead>
                <tr className="border-b border-gray-200 bg-gray-100 text-xs font-semibold uppercase text-gray-600">
                  <th className="px-4 py-3" scope="col">
                    ID
                  </th>
                  <th className="px-4 py-3" scope="col">
                    Name
                  </th>
                  <th className="px-4 py-3" scope="col">
                    Email
                  </th>
                  <th className="px-4 py-3" scope="col">
                    Role
                  </th>
                  <th className="px-4 py-3" scope="col">
                    Status
                  </th>
                  <th className="px-4 py-3 text-right" scope="col">
                    Wallet
                  </th>
                  <th className="px-4 py-3" scope="col">
                    Phone
                  </th>
                  <th className="px-4 py-3" scope="col">
                    Joined
                  </th>
                  <th className="px-4 py-3 text-right" scope="col">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100">
                {users.map((u) => {
                  const badge = statusBadge(u.status);
                  const userPath = `/admin/users/${u.id}`;
                  return (
                  <tr
                    key={u.id}
                    role="link"
                    tabIndex={0}
                    className="cursor-pointer hover:bg-gray-50 focus:outline-none focus-visible:bg-gray-50 focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-blue-500"
                    onClick={() => navigate(userPath)}
                    onKeyDown={(e) => {
                      if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        navigate(userPath);
                      }
                    }}
                    aria-label={`View user ${u.name || u.id}`}
                  >
                    <td className="px-4 py-3 font-mono text-xs">{u.id}</td>
                    <td className="px-4 py-3 font-medium text-gray-900">
                      <Link
                        to={userPath}
                        className="text-blue-900 hover:underline"
                        onClick={(e) => e.stopPropagation()}
                      >
                        {u.name || '—'}
                      </Link>
                    </td>
                    <td className="px-4 py-3 text-gray-600">{u.email || '—'}</td>
                    <td className="px-4 py-3 text-gray-700">{u.role || '—'}</td>
                    <td className="px-4 py-3">
                      <Badge variant={badge.variant}>{badge.label}</Badge>
                    </td>
                    <td className="px-4 py-3 text-right tabular-nums text-gray-700">{formatNaira(u.wallet_balance)}</td>
                    <td className="px-4 py-3 text-gray-700">
                      {[u.country_code, u.phone].filter(Boolean).join(' ') || '—'}
                    </td>
                    <td className="whitespace-nowrap px-4 py-3 text-gray-600">{formatCreatedDate(u.created_at)}</td>
                    <td className="px-4 py-3 text-right">
                      <Button
                        as={Link}
                        to={userPath}
                        variant="secondary"
                        className="px-3 py-1.5 text-xs"
                        onClick={(e) => e.stopPropagation()}
                      >
                        View
                      </Button>
                    </td>
                  </tr>
                );})}
                {!loading && !error && users.length === 0 ? (
                  <tr>
                    <td className="px-4 py-6 text-sm text-gray-500" colSpan={9}>
                      No users found.
                    </td>
                  </tr>
                ) : null}
              </tbody>
            </table>
          </div>
          {!loading && !error ? (
            <div className="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 pt-4">
              <span className="text-xs text-gray-500">
                Page {page} of {lastPage}
              </span>
              <div className="flex flex-wrap gap-2">
                <Button
                  type="button"
                  variant="secondary"
                  disabled={loading || !!error || !hasPrev}
                  onClick={() => goToPage(page - 1)}
                  aria-label="Previous page"
                >
                  Previous
                </Button>
                {paginationPages(page, lastPage).map((p) => (
                  <Button
                    key={p}
                    type="button"
                    variant={p === page ? 'primary' : 'secondary'}
                    disabled={loading || !!error || p === page}
                    onClick={() => goToPage(p)}
                    aria-label={`Page ${p}`}
                    aria-current={p === page ? 'page' : undefined}
                    className="min-w-10 px-3"
                  >
                    {p}
                  </Button>
                ))}
                <Button
                  type="button"
                  variant="secondary"
                  disabled={loading || !!error || !hasNext}
                  onClick={() => goToPage(page + 1)}
                  aria-label="Next page"
                >
                  Next
                </Button>
              </div>
            </div>
          ) : null}
        </section>

        <section className="mt-8 rounded-2xl bg-white p-6 shadow-md" aria-labelledby="inactive-agents-heading">
          <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
              <h2 id="inactive-agents-heading" className="text-base font-semibold text-gray-900">
                Inactive agents
              </h2>
              <p className="mt-1 text-xs text-gray-500">
                Agent accounts that are not yet activated (or were deactivated).
              </p>
            </div>
            <Button type="button" variant="secondary" onClick={loadInactiveAgents} disabled={inactiveLoading}>
              {inactiveLoading ? 'Refreshing…' : 'Refresh'}
            </Button>
          </div>
          <div className="overflow-x-auto">
            <table className="w-full min-w-[48rem] border-collapse text-left text-sm">
              <thead>
                <tr className="border-b border-gray-200 bg-gray-100 text-xs font-semibold uppercase text-gray-600">
                  <th className="px-4 py-3" scope="col">
                    ID
                  </th>
                  <th className="px-4 py-3" scope="col">
                    Name
                  </th>
                  <th className="px-4 py-3" scope="col">
                    Email
                  </th>
                  <th className="px-4 py-3" scope="col">
                    Account status
                  </th>
                  <th className="px-4 py-3" scope="col">
                    Agent status
                  </th>
                  <th className="px-4 py-3 text-right" scope="col">
                    Wallet
                  </th>
                  <th className="px-4 py-3" scope="col">
                    Phone
                  </th>
                  <th className="px-4 py-3" scope="col">
                    Joined
                  </th>
                  <th className="px-4 py-3 text-right" scope="col">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100">
                {inactiveLoading ? (
                  <tr>
                    <td className="px-4 py-6 text-sm text-gray-500" colSpan={9}>
                      Loading inactive agents…
                    </td>
                  </tr>
                ) : inactiveError ? (
                  <tr>
                    <td className="px-4 py-6 text-sm text-red-600" colSpan={9} role="alert">
                      {inactiveError}
                    </td>
                  </tr>
                ) : inactiveAgents.length === 0 ? (
                  <tr>
                    <td className="px-4 py-6 text-sm text-gray-500" colSpan={9}>
                      No inactive agents.
                    </td>
                  </tr>
                ) : (
                  inactiveAgents.map((a) => {
                    const acctBadge = statusBadge(a.status);
                    const agBadge = statusBadge(a.agent_status);
                    const userPath = `/admin/users/${a.id}`;
                    return (
                      <tr
                        key={a.id}
                        role="link"
                        tabIndex={0}
                        className="cursor-pointer hover:bg-gray-50 focus:outline-none focus-visible:bg-gray-50 focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-blue-500"
                        onClick={() => navigate(userPath)}
                        onKeyDown={(e) => {
                          if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            navigate(userPath);
                          }
                        }}
                        aria-label={`View user ${a.name || a.id}`}
                      >
                        <td className="px-4 py-3 font-mono text-xs">{a.id}</td>
                        <td className="px-4 py-3 font-medium text-gray-900">
                          <Link
                            to={userPath}
                            className="text-blue-900 hover:underline"
                            onClick={(e) => e.stopPropagation()}
                          >
                            {a.name || '—'}
                          </Link>
                        </td>
                        <td className="px-4 py-3 text-gray-600">{a.email || '—'}</td>
                        <td className="px-4 py-3">
                          <Badge variant={acctBadge.variant}>{acctBadge.label}</Badge>
                        </td>
                        <td className="px-4 py-3">
                          <Badge variant={agBadge.variant}>{agBadge.label}</Badge>
                        </td>
                        <td className="px-4 py-3 text-right tabular-nums text-gray-700">
                          {formatNaira(a.wallet_balance)}
                        </td>
                        <td className="px-4 py-3 text-gray-700">
                          {[a.country_code, a.phone].filter(Boolean).join(' ') || '—'}
                        </td>
                        <td className="whitespace-nowrap px-4 py-3 text-gray-600">
                          {formatCreatedDate(a.created_at)}
                        </td>
                        <td className="px-4 py-3 text-right">
                          <Button
                            as={Link}
                            to={userPath}
                            variant="secondary"
                            className="px-3 py-1.5 text-xs"
                            onClick={(e) => e.stopPropagation()}
                          >
                            View
                          </Button>
                        </td>
                      </tr>
                    );
                  })
                )}
              </tbody>
            </table>
          </div>
        </section>

        <section className="mt-8 rounded-2xl bg-white p-6 shadow-md" aria-labelledby="all-agents-heading">
          <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
              <h2 id="all-agents-heading" className="text-base font-semibold text-gray-900">
                All agents
              </h2>
              <p className="mt-1 text-xs text-gray-500">
                All agent accounts regardless of activation status.
              </p>
            </div>
            <Button type="button" variant="secondary" onClick={loadAllAgents} disabled={allAgentsLoading}>
              {allAgentsLoading ? 'Refreshing…' : 'Refresh'}
            </Button>
          </div>
          <div className="overflow-x-auto">
            <table className="w-full min-w-[52rem] border-collapse text-left text-sm">
              <thead>
                <tr className="border-b border-gray-200 bg-gray-100 text-xs font-semibold uppercase text-gray-600">
                  <th className="px-4 py-3" scope="col">ID</th>
                  <th className="px-4 py-3" scope="col">Name</th>
                  <th className="px-4 py-3" scope="col">Email</th>
                  <th className="px-4 py-3" scope="col">Account status</th>
                  <th className="px-4 py-3" scope="col">Agent status</th>
                  <th className="px-4 py-3 text-right" scope="col">Wallet</th>
                  <th className="px-4 py-3" scope="col">Phone</th>
                  <th className="px-4 py-3" scope="col">Joined</th>
                  <th className="px-4 py-3 text-right" scope="col">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100">
                {allAgentsLoading ? (
                  <tr>
                    <td className="px-4 py-6 text-sm text-gray-500" colSpan={9}>Loading agents…</td>
                  </tr>
                ) : allAgentsError ? (
                  <tr>
                    <td className="px-4 py-6 text-sm text-red-600" colSpan={9} role="alert">{allAgentsError}</td>
                  </tr>
                ) : allAgents.length === 0 ? (
                  <tr>
                    <td className="px-4 py-6 text-sm text-gray-500" colSpan={9}>No agents found.</td>
                  </tr>
                ) : (
                  allAgents.map((a) => {
                    const acctBadge = statusBadge(a.status);
                    const agBadge = statusBadge(a.agent_status);
                    const userPath = `/admin/users/${a.id}`;
                    return (
                      <tr
                        key={a.id}
                        role="link"
                        tabIndex={0}
                        className="cursor-pointer hover:bg-gray-50 focus:outline-none focus-visible:bg-gray-50 focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-blue-500"
                        onClick={() => navigate(userPath)}
                        onKeyDown={(e) => {
                          if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            navigate(userPath);
                          }
                        }}
                        aria-label={`View user ${a.name || a.id}`}
                      >
                        <td className="px-4 py-3 font-mono text-xs">{a.id}</td>
                        <td className="px-4 py-3 font-medium text-gray-900">
                          <Link
                            to={userPath}
                            className="text-blue-900 hover:underline"
                            onClick={(e) => e.stopPropagation()}
                          >
                            {a.name || '—'}
                          </Link>
                        </td>
                        <td className="px-4 py-3 text-gray-600">{a.email || '—'}</td>
                        <td className="px-4 py-3">
                          <Badge variant={acctBadge.variant}>{acctBadge.label}</Badge>
                        </td>
                        <td className="px-4 py-3">
                          <Badge variant={agBadge.variant}>{agBadge.label}</Badge>
                        </td>
                        <td className="px-4 py-3 text-right tabular-nums text-gray-700">
                          {formatNaira(a.wallet_balance)}
                        </td>
                        <td className="px-4 py-3 text-gray-700">
                          {[a.country_code, a.phone].filter(Boolean).join(' ') || '—'}
                        </td>
                        <td className="whitespace-nowrap px-4 py-3 text-gray-600">
                          {formatCreatedDate(a.created_at)}
                        </td>
                        <td className="px-4 py-3 text-right">
                          <Button
                            as={Link}
                            to={userPath}
                            variant="secondary"
                            className="px-3 py-1.5 text-xs"
                            onClick={(e) => e.stopPropagation()}
                          >
                            View
                          </Button>
                        </td>
                      </tr>
                    );
                  })
                )}
              </tbody>
            </table>
          </div>
        </section>
      </main>

      <Modal open={createOpen} onClose={() => !createSaving && closeCreateModal()} title="Create user">
        <form className="space-y-4" onSubmit={handleCreateUser} noValidate>
          {createError ? (
            <p className="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700" role="alert">
              {createError}
            </p>
          ) : null}
          <div className="grid gap-4 sm:grid-cols-2">
            <Input
              id="create-name"
              name="name"
              label="Name"
              value={createForm.name}
              onChange={(e) => setCreateField('name', e.target.value)}
              disabled={createSaving}
              required
            />
            <Input
              id="create-email"
              name="email"
              type="email"
              label="Email"
              value={createForm.email}
              onChange={(e) => setCreateField('email', e.target.value)}
              disabled={createSaving}
              required
            />
            <Input
              id="create-phone"
              name="phone"
              label="Phone"
              value={createForm.phone}
              onChange={(e) => setCreateField('phone', e.target.value)}
              disabled={createSaving}
            />
            <div>
              <label htmlFor="create-country-code" className="block text-sm font-medium text-gray-700">
                Country code
              </label>
              <div className="mt-1">
                <select
                  id="create-country-code"
                  name="country_code"
                  className={selectClassName}
                  value={createForm.country_code}
                  onChange={(e) => setCreateField('country_code', e.target.value)}
                  disabled={createSaving}
                  required
                >
                  <option value="">Select country</option>
                  {countryCodeOptions.map(({ country, code }) => (
                    <option key={`${country}-${code}`} value={code}>
                      {country} ({code})
                    </option>
                  ))}
                </select>
              </div>
            </div>
            <div>
              <label htmlFor="create-role" className="block text-sm font-medium text-gray-700">
                Role
              </label>
              <div className="mt-1">
                <select
                  id="create-role"
                  name="role"
                  className={selectClassName}
                  value={createForm.role}
                  onChange={(e) => setCreateField('role', e.target.value)}
                  disabled={createSaving}
                >
                  <option value="customer">customer</option>
                  <option value="agent">agent</option>
                  <option value="admin">admin</option>
                </select>
              </div>
            </div>
            <div>
              <label htmlFor="create-status" className="block text-sm font-medium text-gray-700">
                Status
              </label>
              <div className="mt-1">
                <select
                  id="create-status"
                  name="status"
                  className={selectClassName}
                  value={createForm.status}
                  onChange={(e) => setCreateField('status', e.target.value)}
                  disabled={createSaving}
                >
                  <option value="active">active</option>
                  <option value="inactive">inactive</option>
                  <option value="pending">pending</option>
                </select>
              </div>
            </div>
            <Input
              id="create-password"
              name="password"
              type={showCreatePassword ? 'text' : 'password'}
              label="Password"
              value={createForm.password}
              onChange={(e) => setCreateField('password', e.target.value)}
              disabled={createSaving}
              required
            />
          </div>
          <div className="flex flex-wrap gap-3 pt-2">
            <Button type="submit" disabled={createSaving}>
              {createSaving ? 'Creating…' : 'Create user'}
            </Button>
            <Button type="button" variant="secondary" disabled={createSaving} onClick={() => setShowCreatePassword((v) => !v)}>
              {showCreatePassword ? 'Hide password' : 'Show password'}
            </Button>
            <Button type="button" variant="secondary" disabled={createSaving} onClick={closeCreateModal}>
              Cancel
            </Button>
          </div>
        </form>
      </Modal>
    </>
  );
}
