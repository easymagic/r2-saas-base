import { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { Button } from '../components/ui/Button.jsx';
import { Input } from '../components/ui/Input.jsx';
import { useToast } from '../context/ToastContext.jsx';
import { postVerifyAccountOtp } from '../lib/accountApi.js';
import { apiMessage } from '../lib/apiConfig.js';
import { getStoredUser, resolvePostLoginPath } from '../lib/authSession.js';

const HINT_EMAIL_KEY = 'register_verify_hint_email';

export function VerifyOtpPage() {
  const navigate = useNavigate();
  const { showToast } = useToast();
  const [otp, setOtp] = useState('');
  const [email, setEmail] = useState(() =>
    typeof sessionStorage !== 'undefined' ? sessionStorage.getItem(HINT_EMAIL_KEY) || '' : ''
  );
  const [submitting, setSubmitting] = useState(false);
  const [formError, setFormError] = useState('');

  useEffect(() => {
    const u = getStoredUser();
    if (u?.token && String(u.token).length > 0) {
      navigate(resolvePostLoginPath(u, null), { replace: true });
    }
  }, [navigate]);

  async function handleSubmit(e) {
    e.preventDefault();
    setFormError('');
    const code = otp.trim();
    const mail = email.trim();
    if (!mail) {
      setFormError('Enter the email you registered with.');
      return;
    }
    if (!code) {
      setFormError('Enter the code from your email.');
      return;
    }
    setSubmitting(true);
    try {
      const r = await postVerifyAccountOtp({ email: mail, otp: code });
      if (!r.ok) {
        const msg =
          typeof r.message === 'string' && r.message.length > 0 ? r.message : 'Invalid or expired code.';
        setFormError(msg);
        showToast(msg, 'error');
        return;
      }

      try {
        sessionStorage.removeItem(HINT_EMAIL_KEY);
      } catch {
        /* ignore */
      }

      showToast(`${apiMessage(r.data, 'Account verified')}. You can sign in now.`, 'success');
      navigate('/login', { replace: true });
    } catch {
      const msg = 'Network error. Check that the API is running.';
      setFormError(msg);
      showToast(msg, 'error');
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
        <p className="mt-1 text-sm text-gray-500">Verify your email</p>
      </Link>

      <section
        className="w-full max-w-md rounded-2xl border border-white/70 bg-white/95 p-6 shadow-xl shadow-slate-900/10 backdrop-blur sm:p-8"
        aria-labelledby="otp-heading"
      >
        <h2 id="otp-heading" className="text-lg font-semibold text-gray-900">
          Enter verification code
        </h2>
        <p className="mt-1 text-sm text-gray-500">
          We sent a one-time code to your email. Enter email + OTP to finish registration.
        </p>

        <form className="mt-6 space-y-4" onSubmit={handleSubmit} noValidate>
          {formError ? (
            <p className="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800" role="alert">
              {formError}
            </p>
          ) : null}
          <Input
            id="verify-email"
            name="email"
            type="email"
            label="Email"
            autoComplete="email"
            required
            value={email}
            onChange={(ev) => setEmail(ev.target.value)}
            disabled={submitting}
          />
          <Input
            id="verify-otp"
            name="otp"
            type="text"
            label="One-time code"
            inputMode="numeric"
            autoComplete="one-time-code"
            required
            placeholder="123456"
            value={otp}
            onChange={(ev) => setOtp(ev.target.value)}
            disabled={submitting}
          />
          <Button type="submit" className="w-full" disabled={submitting}>
            {submitting ? 'Verifying…' : 'Verify account'}
          </Button>
        </form>

        <p className="mt-6 text-center text-sm text-gray-600">
          <Link to="/register" className="font-semibold text-orange-600 hover:text-orange-700">
            Back to registration
          </Link>
          {' · '}
          <Link to="/login" className="font-semibold text-orange-600 hover:text-orange-700">
            Sign in
          </Link>
          {' · '}
          <Link to="/" className="font-semibold text-blue-700 hover:text-blue-800">
            Home
          </Link>
        </p>
      </section>
    </main>
  );
}
