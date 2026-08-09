import { useEffect, useState } from 'react';
import { Link, useLocation, useNavigate } from 'react-router-dom';
import { Button } from '../components/ui/Button.jsx';
import { Input } from '../components/ui/Input.jsx';
import { useToast } from '../context/ToastContext.jsx';
import { apiData, apiMessage, apiUrl, jsonHeaders, readApiJson, xTokenHeader } from '../lib/apiConfig.js';
import { getStoredUser, resolvePostLoginPath, saveAuthUser } from '../lib/authSession.js';
import { endpoints } from '../lib/endpoints.js';
import { syncSettingsFromApi } from '../lib/settingsApi.js';
import { fetchMeFromApi } from '../lib/userApi.js';

export function LoginPage() {
  const navigate = useNavigate();
  const location = useLocation();
  const { showToast } = useToast();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [submitting, setSubmitting] = useState(false);
  const [formError, setFormError] = useState('');

  useEffect(() => {
    const u = getStoredUser();
    if (!u) return;
    navigate(resolvePostLoginPath(u, location.state?.from?.pathname), { replace: true });
  }, [navigate, location.state]);

  async function handleSubmit(e) {
    e.preventDefault();
    setFormError('');
    setSubmitting(true);

    try {
      const res = await fetch(apiUrl(endpoints.authLogin()), {
        method: 'POST',
        headers: jsonHeaders({ 'x-token': xTokenHeader() }),
        credentials: 'include',
        body: JSON.stringify({
          email: email.trim(),
          password,
        }),
      });

      let data = null;
      try {
        data = await readApiJson(res);
      } catch {
        setFormError('Unexpected response from server.');
        showToast('Could not sign in. Try again.', 'error');
        return;
      }

      const nested = apiData(data);
      const apiUser = nested?.user ?? data?.user ?? null;
      if (data?.success && apiUser) {
        saveAuthUser(apiUser);
        const stored = getStoredUser();
        if (stored) {
          const me = await fetchMeFromApi(stored);
          if (me?.ok && me.user) saveAuthUser(me.user);
        }
        void syncSettingsFromApi(getStoredUser());
        showToast(apiMessage(data, 'Login successful'), 'success');
        const from = location.state?.from?.pathname;
        navigate(resolvePostLoginPath(getStoredUser() || apiUser, from), { replace: true });
        return;
      }

      const msg = apiMessage(data, 'Invalid credentials!');
      setFormError(msg);
      showToast(msg, 'error');
    } catch {
      setFormError('Network error. Check that the API is running.');
      showToast('Network error. Check that the API is running.', 'error');
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <main className="auth-shell flex min-h-screen flex-col items-center justify-center p-4 sm:p-6">
      <Link
        to="/"
        className="mb-8 rounded-2xl text-center focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-500 focus-visible:ring-offset-4"
        aria-label="Go to homepage"
      >
        <div className="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-900 text-lg font-bold text-white shadow-lg shadow-blue-900/30">
          BF
        </div>
        <h1 className="mt-4 text-2xl font-semibold tracking-tight text-gray-900">BorderlessFetch</h1>
        <p className="mt-1 text-sm text-gray-500">Social commerce fulfillment</p>
      </Link>

      <section className="w-full max-w-md rounded-2xl border border-white/70 bg-white/95 p-6 shadow-xl shadow-slate-900/10 backdrop-blur sm:p-8" aria-labelledby="signin-heading">
        <h2 id="signin-heading" className="text-lg font-semibold text-gray-900">
          Sign in
        </h2>
        <p className="mt-1 text-sm text-gray-500">Use your platform email and password.</p>

        <form className="mt-6 space-y-4" onSubmit={handleSubmit} noValidate>
          {formError ? (
            <p className="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800" role="alert">
              {formError}
            </p>
          ) : null}
          <Input
            id="email"
            name="email"
            type="email"
            label="Email"
            autoComplete="email"
            required
            placeholder="admin@platform.com"
            value={email}
            onChange={(ev) => setEmail(ev.target.value)}
            disabled={submitting}
          />
          <Input
            id="password"
            name="password"
            type="password"
            label="Password"
            autoComplete="current-password"
            required
            value={password}
            onChange={(ev) => setPassword(ev.target.value)}
            disabled={submitting}
          />
          <Button type="submit" className="w-full" disabled={submitting}>
            {submitting ? 'Signing in…' : 'Sign in'}
          </Button>
        </form>

        <p className="mt-6 text-center text-sm text-gray-600">
          New here?{' '}
          <Link to="/register" className="font-semibold text-orange-600 hover:text-orange-700">
            Create an account
          </Link>
          <span className="mx-2 text-gray-300">|</span>
          <Link to="/" className="font-semibold text-blue-700 hover:text-blue-800">
            Home
          </Link>
        </p>
      </section>
    </main>
  );
}
