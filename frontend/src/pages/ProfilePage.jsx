import { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { UserHeader } from '../components/layout/UserHeader.jsx';
import { Badge } from '../components/ui/Badge.jsx';
import { Button } from '../components/ui/Button.jsx';
import { Input } from '../components/ui/Input.jsx';
import { useToast } from '../context/ToastContext.jsx';
import { getStoredUser, saveAuthUser } from '../lib/authSession.js';
import { activateAgentFromApi, deactivateAgentFromApi } from '../lib/agentsApi.js';
import { changeMyPasswordOnApi, fetchMeFromApi, postMeOnApi } from '../lib/userApi.js';
import { formatNaira, initialsFromName } from '../lib/userDisplay.js';

function agentStatusBadge(status) {
  const s = String(status || '').toLowerCase();
  if (s === 'active') return { variant: 'approved', label: 'Active' };
  if (s === 'inactive') return { variant: 'rejected', label: 'Inactive' };
  if (s === 'pending') return { variant: 'pending', label: 'Pending' };
  return { variant: 'default', label: String(status || '').trim() || '—' };
}

export function ProfilePage() {
  const navigate = useNavigate();
  const { showToast } = useToast();
  const [balanceLabel, setBalanceLabel] = useState(() => formatNaira(getStoredUser()?.wallet_balance));
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [loadError, setLoadError] = useState('');
  const [formError, setFormError] = useState('');
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [phone, setPhone] = useState('');
  const [agentStatus, setAgentStatus] = useState('');
  const [agentActionLoading, setAgentActionLoading] = useState(false);
  const [currentPassword, setCurrentPassword] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [changePwSaving, setChangePwSaving] = useState(false);
  const [changePwError, setChangePwError] = useState('');

  const sessionUser = getStoredUser();

  useEffect(() => {
    const u = getStoredUser();
    if (!u?.token || u.id == null) {
      navigate('/login', { replace: true });
      return;
    }

    let cancelled = false;
    setLoading(true);
    setLoadError('');
    fetchMeFromApi(u).then((r) => {
      if (cancelled) return;
      if (!r.ok || !r.user) {
        setLoadError(typeof r.message === 'string' ? r.message : 'Could not load your profile.');
        setLoading(false);
        return;
      }
      const usr = r.user;
      setName(typeof usr.name === 'string' ? usr.name : '');
      setEmail(typeof usr.email === 'string' ? usr.email : '');
      setPhone(typeof usr.phone === 'string' ? usr.phone : '');
      setAgentStatus(usr.agent_status != null ? String(usr.agent_status).trim() : '');
      saveAuthUser(usr);
      setBalanceLabel(formatNaira(usr.wallet_balance));
      setLoading(false);
    });

    return () => {
      cancelled = true;
    };
  }, [navigate]);

  async function handleSubmit(e) {
    e.preventDefault();
    setFormError('');
    const u = getStoredUser();
    if (!u?.token) {
      navigate('/login', { replace: true });
      return;
    }

    setSaving(true);
    try {
      const result = await postMeOnApi(u, {
        name: name.trim(),
        phone: phone.trim(),
        delivery_address: u.delivery_address != null ? String(u.delivery_address) : '',
      });
      if (!result.ok) {
        const msg =
          typeof result.message === 'string' && result.message.length > 0
            ? result.message
            : 'Could not save profile.';
        setFormError(msg);
        showToast(msg, 'error');
        return;
      }
      const saved = result.user;
      saveAuthUser(saved);
      if (saved.agent_status != null) setAgentStatus(String(saved.agent_status).trim());
      setBalanceLabel(formatNaira(saved.wallet_balance));
      if (typeof saved.name === 'string') setName(saved.name);
      if (typeof saved.phone === 'string') setPhone(saved.phone);
      showToast('Profile saved', 'success');
    } catch {
      setFormError('Network error. Check that the API is running.');
      showToast('Network error. Check that the API is running.', 'error');
    } finally {
      setSaving(false);
    }
  }

  async function handleActivateAgent() {
    const u = getStoredUser();
    if (!u?.token || u.id == null) {
      navigate('/login', { replace: true });
      return;
    }
    setAgentActionLoading(true);
    const r = await activateAgentFromApi(u, u.id);
    setAgentActionLoading(false);
    if (!r.ok) {
      const msg =
        typeof r.message === 'string' && r.message.length > 0
          ? r.message
          : 'Could not activate your agent account.';
      showToast(msg, 'error');
      return;
    }
    const prev = getStoredUser();
    const ag = r.agent;
    if (ag && typeof ag === 'object') {
      const keepToken =
        prev?.token && String(prev.token).length > 0
          ? prev.token
          : typeof ag.token === 'string' && ag.token.length > 0
            ? ag.token
            : '';
      saveAuthUser({ ...prev, ...ag, token: keepToken });
      if (ag.agent_status != null) setAgentStatus(String(ag.agent_status).trim());
    }
    showToast(
      typeof r.message === 'string' && r.message.length > 0 ? r.message : 'Agent activated.',
      'success'
    );
  }

  async function handleDeactivateAgent() {
    const u = getStoredUser();
    if (!u?.token || u.id == null) {
      navigate('/login', { replace: true });
      return;
    }
    if (
      !window.confirm(
        'Deactivate your agent account? You will need to activate again to use agent features.'
      )
    ) {
      return;
    }
    setAgentActionLoading(true);
    const r = await deactivateAgentFromApi(u, u.id);
    setAgentActionLoading(false);
    if (!r.ok) {
      const msg =
        typeof r.message === 'string' && r.message.length > 0
          ? r.message
          : 'Could not deactivate your agent account.';
      showToast(msg, 'error');
      return;
    }
    const prev = getStoredUser();
    const ag = r.agent;
    if (ag && typeof ag === 'object') {
      const keepToken =
        prev?.token && String(prev.token).length > 0
          ? prev.token
          : typeof ag.token === 'string' && ag.token.length > 0
            ? ag.token
            : '';
      saveAuthUser({ ...prev, ...ag, token: keepToken });
      if (ag.agent_status != null) setAgentStatus(String(ag.agent_status).trim());
    }
    showToast(
      typeof r.message === 'string' && r.message.length > 0 ? r.message : 'Agent deactivated.',
      'success'
    );
  }

  async function handleChangePassword(e) {
    e.preventDefault();
    setChangePwError('');
    if (!newPassword.trim()) {
      setChangePwError('New password is required.');
      return;
    }
    if (newPassword !== confirmPassword) {
      setChangePwError('New passwords do not match.');
      return;
    }
    const u = getStoredUser();
    if (!u?.token) {
      navigate('/login', { replace: true });
      return;
    }
    setChangePwSaving(true);
    try {
      const result = await changeMyPasswordOnApi(u, {
        currentPassword: currentPassword.trim(),
        newPassword: newPassword.trim(),
        confirmPassword: confirmPassword.trim(),
      });
      if (!result.ok) {
        const msg =
          typeof result.message === 'string' && result.message.length > 0
            ? result.message
            : 'Could not change password.';
        setChangePwError(msg);
        showToast(msg, 'error');
        return;
      }
      showToast(result.message || 'Password changed.', 'success');
      setCurrentPassword('');
      setNewPassword('');
      setConfirmPassword('');
    } catch {
      const msg = 'Network error. Check that the API is running.';
      setChangePwError(msg);
      showToast(msg, 'error');
    } finally {
      setChangePwSaving(false);
    }
  }

  const roleLower = String(sessionUser?.role || '').toLowerCase();
  const isAgent = roleLower === 'agent';
  const agentStatusLower = String(agentStatus || '').toLowerCase();
  const agentNeedsActivation = isAgent && agentStatusLower !== 'active';

  const headerRight = (
    <>
      <span className="hidden rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600 sm:inline">
        Balance: {balanceLabel}
      </span>
      <span
        className="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-900 text-xs font-semibold text-white"
        aria-hidden
      >
        {initialsFromName(sessionUser?.name)}
      </span>
    </>
  );

  const isAdmin = String(getStoredUser()?.role || '').toLowerCase() === 'admin';

  return (
    <>
      <UserHeader
        title="My profile"
        subtitle="Update how we reach you and how your name appears"
        right={headerRight}
      />
      <main className="flex-1 p-4 sm:p-6 lg:p-8">
        <div className="w-full max-w-lg space-y-6">
          <section className="rounded-2xl bg-white p-6 shadow-md sm:p-8">
            {loading ? (
              <p className="text-sm text-gray-500">Loading your profile…</p>
            ) : loadError ? (
              <div className="space-y-4">
                <p className="text-sm text-red-700" role="alert">
                  {loadError}
                </p>
                <Button
                  type="button"
                  variant="secondary"
                  onClick={() => {
                    const u = getStoredUser();
                    if (!u) return;
                    setLoading(true);
                    setLoadError('');
                    fetchMeFromApi(u).then((r) => {
                      if (!r.ok || !r.user) {
                        setLoadError(typeof r.message === 'string' ? r.message : 'Could not load your profile.');
                        setLoading(false);
                        return;
                      }
                      const usr = r.user;
                      setName(typeof usr.name === 'string' ? usr.name : '');
                      setEmail(typeof usr.email === 'string' ? usr.email : '');
                      setPhone(typeof usr.phone === 'string' ? usr.phone : '');
                      setAgentStatus(usr.agent_status != null ? String(usr.agent_status).trim() : '');
                      saveAuthUser(usr);
                      setBalanceLabel(formatNaira(usr.wallet_balance));
                      setLoading(false);
                    });
                  }}
                >
                  Try again
                </Button>
              </div>
            ) : (
              <form className="space-y-4" onSubmit={handleSubmit} noValidate>
                {isAgent ? (
                  <div className="rounded-xl border border-gray-200 bg-gray-50 p-4">
                    <h2 className="text-base font-semibold text-gray-900">Agent account</h2>
                    <p className="mt-1 text-sm text-gray-600">
                      Agent status:{' '}
                      {(() => {
                        const b = agentStatusBadge(agentStatus);
                        return <Badge variant={b.variant}>{b.label}</Badge>;
                      })()}
                    </p>
                    {agentNeedsActivation ? (
                      <div className="mt-3 space-y-2">
                        <p className="text-sm text-gray-600">
                          Activate your agent account to use agent features on the platform.
                        </p>
                        <Button type="button" variant="orange" disabled={agentActionLoading} onClick={handleActivateAgent}>
                          {agentActionLoading ? 'Activating…' : 'Activate my agent account'}
                        </Button>
                      </div>
                    ) : (
                      <div className="mt-3 space-y-2">
                        <p className="text-sm text-gray-600">Your agent account is active.</p>
                        <Button
                          type="button"
                          variant="danger"
                          disabled={agentActionLoading}
                          onClick={handleDeactivateAgent}
                        >
                          {agentActionLoading ? 'Deactivating…' : 'Deactivate my agent account'}
                        </Button>
                      </div>
                    )}
                  </div>
                ) : null}
                <h2 className="text-base font-semibold text-gray-900">Account details</h2>
                <p className="text-sm text-gray-500">
                  Changes are sent to your account on our servers. Wallet and orders are unchanged.
                </p>
                {formError ? (
                  <p className="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800" role="alert">
                    {formError}
                  </p>
                ) : null}
                <Input
                  id="profile-name"
                  name="name"
                  label="Full name"
                  value={name}
                  onChange={(ev) => setName(ev.target.value)}
                  disabled={saving}
                  autoComplete="name"
                />
                <div>
                  <p className="block text-sm font-medium text-gray-700">Email</p>
                  <p className="mt-1 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                    {email || '—'}
                  </p>
                  <p className="mt-1 text-xs text-gray-500">Email isn’t changed from this form.</p>
                </div>
                <Input
                  id="profile-phone"
                  name="phone"
                  type="tel"
                  label="Phone"
                  value={phone}
                  onChange={(ev) => setPhone(ev.target.value)}
                  disabled={saving}
                  autoComplete="tel"
                />
                <div className="flex flex-wrap gap-3 pt-2">
                  <Button type="submit" disabled={saving}>
                    {saving ? 'Saving…' : 'Save changes'}
                  </Button>
                  <Button as={Link} to={isAdmin ? '/admin' : '/'} variant="secondary">
                    {isAdmin ? 'Back to admin' : 'Back to dashboard'}
                  </Button>
                </div>
              </form>
            )}
          </section>
          <section className="rounded-2xl bg-white p-6 shadow-md sm:p-8">
            <h2 className="text-base font-semibold text-gray-900">Change password</h2>
            <p className="mt-1 text-sm text-gray-500">Update your login password.</p>
            <form className="mt-4 space-y-4" onSubmit={handleChangePassword} noValidate>
              {changePwError ? (
                <p className="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800" role="alert">
                  {changePwError}
                </p>
              ) : null}
              <Input
                id="current-password"
                name="current_password"
                type="password"
                label="Current password"
                value={currentPassword}
                onChange={(ev) => setCurrentPassword(ev.target.value)}
                disabled={changePwSaving}
                autoComplete="current-password"
              />
              <Input
                id="new-password"
                name="new_password"
                type="password"
                label="New password"
                value={newPassword}
                onChange={(ev) => setNewPassword(ev.target.value)}
                disabled={changePwSaving}
                autoComplete="new-password"
              />
              <Input
                id="confirm-password"
                name="new_password_confirmation"
                type="password"
                label="Confirm new password"
                value={confirmPassword}
                onChange={(ev) => setConfirmPassword(ev.target.value)}
                disabled={changePwSaving}
                autoComplete="new-password"
              />
              <Button type="submit" variant="orange" disabled={changePwSaving}>
                {changePwSaving ? 'Changing…' : 'Change password'}
              </Button>
            </form>
          </section>
        </div>
      </main>
    </>
  );
}
