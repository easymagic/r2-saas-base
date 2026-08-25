import { useCallback, useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { UserHeader } from '../components/layout/UserHeader.jsx';
import { Badge } from '../components/ui/Badge.jsx';
import { Button } from '../components/ui/Button.jsx';
import { Card } from '../components/ui/Card.jsx';
import { Input } from '../components/ui/Input.jsx';
import { useToast } from '../context/ToastContext.jsx';
import { getStoredUser, saveAuthUser } from '../lib/authSession.js';
import { cn } from '../lib/cn.js';
import { activateAgentFromApi, deactivateAgentFromApi } from '../lib/agentsApi.js';
import { changeUserPasswordOnApi, fetchUserByIdFromApi, updateUserOnApi } from '../lib/userApi.js';
import { formatNaira } from '../lib/userDisplay.js';

const selectClassName = cn(
  'w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm',
  'focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500'
);

function formatCreatedDate(value) {
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

function statusBadge(status) {
  const s = String(status || '').toLowerCase();
  if (s === 'active') return { variant: 'approved', label: 'Active' };
  if (s === 'inactive') return { variant: 'rejected', label: 'Inactive' };
  if (s === 'pending') return { variant: 'pending', label: 'Pending' };
  return { variant: 'default', label: s || '—' };
}

function sessionTokenLabel(token) {
  if (token == null || token === '') return 'None';
  return typeof token === 'string' && token.length > 0 ? 'Active' : 'None';
}

function userToForm(u) {
  return {
    name: String(u.name ?? ''),
    phone: String(u.phone ?? ''),
    role: String(u.role ?? 'customer'),
    status: String(u.status ?? 'active'),
  };
}

export function AdminUserDetailPage() {
  const { userId } = useParams();
  const navigate = useNavigate();
  const { showToast } = useToast();
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [user, setUser] = useState(null);
  const [form, setForm] = useState(userToForm({}));
  const [saving, setSaving] = useState(false);
  const [formError, setFormError] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [showNewPassword, setShowNewPassword] = useState(false);
  const [passwordSaving, setPasswordSaving] = useState(false);
  const [passwordError, setPasswordError] = useState('');
  const [agentActionLoading, setAgentActionLoading] = useState(false);

  const load = useCallback(async () => {
    const u = getStoredUser();
    if (!u?.token || u.id == null) {
      navigate('/login', { replace: true });
      return;
    }
    setLoading(true);
    setError('');
    const r = await fetchUserByIdFromApi(u, userId);
    setLoading(false);
    if (!r.ok) {
      setError(typeof r.message === 'string' && r.message.length > 0 ? r.message : 'Could not load user.');
      setUser(null);
      return;
    }
    setUser(r.user);
    setForm(userToForm(r.user));
  }, [navigate, userId]);

  useEffect(() => {
    load();
  }, [load]);

  function setField(name, value) {
    setForm((prev) => ({ ...prev, [name]: value }));
  }

  async function handleSubmit(e) {
    e.preventDefault();
    setFormError('');
    const session = getStoredUser();
    if (!session?.token || session.id == null) {
      navigate('/login', { replace: true });
      return;
    }
    if (!String(form.name ?? '').trim()) {
      setFormError('Name is required.');
      return;
    }

    setSaving(true);
    try {
      const r = await updateUserOnApi(session, userId, form);
      if (!r.ok) {
        const msg = typeof r.message === 'string' && r.message.length > 0 ? r.message : 'Could not update user.';
        setFormError(msg);
        showToast(msg, 'error');
        return;
      }
      setUser(r.user);
      setForm(userToForm(r.user));
      if (Number(r.user.id) === Number(session.id)) {
        saveAuthUser(r.user);
      }
      showToast(r.data?.message || 'User updated successfully', 'success');
    } finally {
      setSaving(false);
    }
  }

  async function handleChangePassword(e) {
    e.preventDefault();
    setPasswordError('');
    const session = getStoredUser();
    if (!session?.token || session.id == null) {
      navigate('/login', { replace: true });
      return;
    }
    if (!String(newPassword).trim()) {
      setPasswordError('New password is required.');
      return;
    }

    setPasswordSaving(true);
    try {
      const r = await changeUserPasswordOnApi(session, userId, newPassword);
      if (!r.ok) {
        const msg =
          typeof r.message === 'string' && r.message.length > 0 ? r.message : 'Could not change user password.';
        setPasswordError(msg);
        showToast(msg, 'error');
        return;
      }
      setUser(r.user);
      if (Number(r.user.id) === Number(session.id)) {
        saveAuthUser(r.user);
      }
      setNewPassword('');
      showToast(r.data?.message || 'User password changed successfully', 'success');
    } finally {
      setPasswordSaving(false);
    }
  }

  async function handleActivateAgent() {
    const session = getStoredUser();
    if (!session?.token || session.id == null) {
      navigate('/login', { replace: true });
      return;
    }
    setAgentActionLoading(true);
    try {
      const r = await activateAgentFromApi(session, userId);
      if (!r.ok) {
        const msg =
          typeof r.message === 'string' && r.message.length > 0
            ? r.message
            : 'Could not activate agent account.';
        showToast(msg, 'error');
        return;
      }
      showToast(
        typeof r.message === 'string' && r.message.length > 0 ? r.message : 'Agent activated.',
        'success'
      );
      await load();
    } finally {
      setAgentActionLoading(false);
    }
  }

  async function handleDeactivateAgent() {
    const session = getStoredUser();
    if (!session?.token || session.id == null) {
      navigate('/login', { replace: true });
      return;
    }
    if (!window.confirm('Deactivate this user’s agent account? They will need to be activated again to use agent features.')) {
      return;
    }
    setAgentActionLoading(true);
    try {
      const r = await deactivateAgentFromApi(session, userId);
      if (!r.ok) {
        const msg =
          typeof r.message === 'string' && r.message.length > 0
            ? r.message
            : 'Could not deactivate agent account.';
        showToast(msg, 'error');
        return;
      }
      showToast(
        typeof r.message === 'string' && r.message.length > 0 ? r.message : 'Agent deactivated.',
        'success'
      );
      await load();
    } finally {
      setAgentActionLoading(false);
    }
  }

  const agentStatusRaw = user?.agent_status != null ? String(user.agent_status).trim() : '';
  const agentBadge = agentStatusRaw ? statusBadge(agentStatusRaw) : null;
  const isAgentRole = String(form.role || '').toLowerCase() === 'agent';
  const agentAccountActive = String(agentStatusRaw || '').toLowerCase() === 'active';

  const summaryRows = user
    ? [
        { label: 'User ID', value: String(user.id ?? '—') },
        { label: 'Email', value: user.email || '—' },
        { label: 'Wallet balance', value: formatNaira(user.wallet_balance) },
        {
          label: 'Agent status',
          value: agentBadge ? <Badge variant={agentBadge.variant}>{agentBadge.label}</Badge> : '—',
        },
        { label: 'Joined', value: formatCreatedDate(user.created_at) },
        { label: 'Session', value: sessionTokenLabel(user.token) },
      ]
    : [];

  return (
    <>
      <UserHeader
        title={user?.name ? user.name : 'User'}
        subtitle={user?.email || 'User details'}
        backTo="/admin/users"
        backLabel="Back to users"
      />
      <main className="flex-1 space-y-6 p-4 sm:p-6 lg:p-8">
        {loading ? (
          <p className="text-sm text-gray-500">Loading user…</p>
        ) : error ? (
          <p className="text-sm text-red-600">{error}</p>
        ) : user ? (
          <>
            <Card className="overflow-hidden p-0">
              <div className="border-b border-gray-100 px-6 py-4">
                <h2 className="text-base font-semibold text-gray-900">Edit user</h2>
              </div>
              <form className="space-y-4 px-6 py-5" onSubmit={handleSubmit} noValidate>
                {formError ? (
                  <p className="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700" role="alert">
                    {formError}
                  </p>
                ) : null}
                <div className="grid gap-4 md:grid-cols-2">
                  <Input
                    id="user-name"
                    name="name"
                    label="Name"
                    value={form.name}
                    onChange={(e) => setField('name', e.target.value)}
                    disabled={saving}
                    required
                  />
                  <Input
                    id="user-phone"
                    name="phone"
                    label="Phone"
                    value={form.phone}
                    onChange={(e) => setField('phone', e.target.value)}
                    disabled={saving}
                  />
                  <div>
                    <label htmlFor="user-role" className="block text-sm font-medium text-gray-700">
                      Role
                    </label>
                    <div className="mt-1">
                      <select
                        id="user-role"
                        name="role"
                        className={selectClassName}
                        value={form.role}
                        onChange={(e) => setField('role', e.target.value)}
                        disabled={saving}
                      >
                        <option value="customer">customer</option>
                        <option value="agent">agent</option>
                        <option value="admin">admin</option>
                      </select>
                    </div>
                  </div>
                  <div>
                    <label htmlFor="user-status" className="block text-sm font-medium text-gray-700">
                      Status
                    </label>
                    <div className="mt-1">
                      <select
                        id="user-status"
                        name="status"
                        className={selectClassName}
                        value={form.status}
                        onChange={(e) => setField('status', e.target.value)}
                        disabled={saving}
                      >
                        <option value="active">active</option>
                        <option value="inactive">inactive</option>
                        <option value="pending">pending</option>
                      </select>
                    </div>
                  </div>
                </div>
                {isAgentRole ? (
                  <div className="rounded-xl border border-orange-200 bg-orange-50/60 p-4">
                    <h3 className="text-sm font-semibold text-gray-900">Agent account</h3>
                    <p className="mt-1 text-xs text-gray-600">
                      Activate or deactivate this user’s agent access (separate from account status above).
                    </p>
                    <p className="mt-3 text-sm text-gray-800">
                      Agent status:{' '}
                      {agentBadge ? (
                        <Badge variant={agentBadge.variant}>{agentBadge.label}</Badge>
                      ) : (
                        <span className="text-gray-500">—</span>
                      )}
                    </p>
                    {agentAccountActive ? (
                      <div className="mt-3 space-y-2">
                        <p className="text-sm text-gray-600">This user’s agent account is active.</p>
                        <Button
                          type="button"
                          variant="danger"
                          disabled={saving || agentActionLoading}
                          onClick={handleDeactivateAgent}
                        >
                          {agentActionLoading ? 'Deactivating…' : 'Deactivate agent account'}
                        </Button>
                      </div>
                    ) : (
                      <div className="mt-3">
                        <Button
                          type="button"
                          variant="orange"
                          disabled={saving || agentActionLoading}
                          onClick={handleActivateAgent}
                        >
                          {agentActionLoading ? 'Activating…' : 'Activate agent account'}
                        </Button>
                      </div>
                    )}
                  </div>
                ) : null}
                <div className="flex flex-wrap gap-3 pt-2">
                  <Button type="submit" disabled={saving}>
                    {saving ? 'Saving…' : 'Save changes'}
                  </Button>
                  <Button
                    type="button"
                    variant="secondary"
                    disabled={saving}
                    onClick={() => {
                      if (user) setForm(userToForm(user));
                      setFormError('');
                    }}
                  >
                    Reset
                  </Button>
                </div>
              </form>
            </Card>

            <Card className="overflow-hidden p-0">
              <div className="border-b border-gray-100 px-6 py-4">
                <h2 className="text-base font-semibold text-gray-900">Account summary</h2>
              </div>
              <dl className="divide-y divide-gray-100">
                {summaryRows.map((row) => (
                  <div
                    key={row.label}
                    className="grid gap-1 px-6 py-4 sm:grid-cols-[minmax(0,12rem)_1fr] sm:items-baseline"
                  >
                    <dt className="text-sm font-medium text-gray-500">{row.label}</dt>
                    <dd className="text-sm text-gray-900">{row.value}</dd>
                  </div>
                ))}
              </dl>
            </Card>

            <Card className="overflow-hidden p-0">
              <div className="border-b border-gray-100 px-6 py-4">
                <h2 className="text-base font-semibold text-gray-900">Change password</h2>
              </div>
              <form className="space-y-4 px-6 py-5" onSubmit={handleChangePassword} noValidate>
                {passwordError ? (
                  <p className="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700" role="alert">
                    {passwordError}
                  </p>
                ) : null}
                <Input
                  id="user-new-password"
                  name="new_password"
                  type={showNewPassword ? 'text' : 'password'}
                  label="New password"
                  value={newPassword}
                  onChange={(e) => setNewPassword(e.target.value)}
                  disabled={passwordSaving}
                  required
                />
                <div className="flex flex-wrap gap-3">
                  <Button
                    type="button"
                    variant="secondary"
                    disabled={passwordSaving}
                    onClick={() => setShowNewPassword((v) => !v)}
                  >
                    {showNewPassword ? 'Hide password' : 'Show password'}
                  </Button>
                  <Button type="submit" disabled={passwordSaving}>
                    {passwordSaving ? 'Changing…' : 'Change password'}
                  </Button>
                </div>
              </form>
            </Card>
          </>
        ) : null}
      </main>
    </>
  );
}
