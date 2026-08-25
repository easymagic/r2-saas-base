import { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { Button } from '../components/ui/Button.jsx';
import { Input } from '../components/ui/Input.jsx';
import { useToast } from '../context/ToastContext.jsx';
import { postRegisterAccount } from '../lib/accountApi.js';
import { apiMessage } from '../lib/apiConfig.js';
import { getStoredUser, resolvePostLoginPath } from '../lib/authSession.js';
import { countryCodeOptions } from '../lib/countryCodes.js';

const selectClassName =
  'w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500';

export function RegisterPage() {
  const navigate = useNavigate();
  const { showToast } = useToast();
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');
  const [phone, setPhone] = useState('');
  const [deliveryAddress, setDeliveryAddress] = useState('');
  const [countryCode, setCountryCode] = useState('');
  const [submitting, setSubmitting] = useState(false);
  const [formError, setFormError] = useState('');

  useEffect(() => {
    const u = getStoredUser();
    if (!u?.token) return;
    navigate(resolvePostLoginPath(u, null), { replace: true });
  }, [navigate]);

  async function handleSubmit(e) {
    e.preventDefault();
    setFormError('');
    if (!countryCode.trim()) {
      setFormError('Country code is required!');
      return;
    }
    if (password !== passwordConfirmation) {
      setFormError('Passwords do not match.');
      return;
    }
    if (!password || password.length < 6) {
      setFormError('Use a password of at least 6 characters.');
      return;
    }
    setSubmitting(true);
    try {
      const r = await postRegisterAccount({
        name,
        email,
        password,
        phone,
        delivery_address: deliveryAddress,
        country_code: countryCode,
        social_security_number: '',
      });
      if (!r.ok) {
        const msg =
          typeof r.message === 'string' && r.message.length > 0 ? r.message : 'Could not create account.';
        setFormError(msg);
        showToast(msg, 'error');
        return;
      }

      try {
        sessionStorage.setItem('register_verify_hint_email', email.trim());
      } catch {
        /* ignore */
      }
      showToast(apiMessage(r.data, 'Check your email for a verification code.'), 'success');
      navigate('/register/verify-otp', { replace: true });
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
        <p className="mt-1 text-sm text-gray-500">Create your account</p>
      </Link>

      <section
        className="w-full max-w-md rounded-2xl border border-white/70 bg-white/95 p-6 shadow-xl shadow-slate-900/10 backdrop-blur sm:p-8"
        aria-labelledby="register-heading"
      >
        <h2 id="register-heading" className="text-lg font-semibold text-gray-900">
          Register
        </h2>
        <p className="mt-1 text-sm text-gray-500">Customer accounts verify by email OTP after signup.</p>

        <form className="mt-6 space-y-4" onSubmit={handleSubmit} noValidate>
          {formError ? (
            <p className="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800" role="alert">
              {formError}
            </p>
          ) : null}

          <Input
            id="reg-name"
            name="name"
            type="text"
            label="Full name"
            autoComplete="name"
            required
            value={name}
            onChange={(ev) => setName(ev.target.value)}
            disabled={submitting}
          />
          <Input
            id="reg-email"
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
            id="reg-phone"
            name="phone"
            type="tel"
            label="Phone"
            autoComplete="tel"
            required
            value={phone}
            onChange={(ev) => setPhone(ev.target.value)}
            disabled={submitting}
          />
          <Input
            id="reg-delivery-address"
            name="delivery_address"
            type="text"
            label="Delivery address"
            autoComplete="street-address"
            value={deliveryAddress}
            onChange={(ev) => setDeliveryAddress(ev.target.value)}
            disabled={submitting}
          />
          <div>
            <label htmlFor="reg-country-code" className="block text-sm font-medium text-gray-700">
              Country code
            </label>
            <div className="mt-1">
              <select
                id="reg-country-code"
                name="country_code"
                className={selectClassName}
                value={countryCode}
                onChange={(ev) => setCountryCode(ev.target.value)}
                disabled={submitting}
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
          <Input
            id="reg-password"
            name="password"
            type="password"
            label="Password"
            autoComplete="new-password"
            required
            value={password}
            onChange={(ev) => setPassword(ev.target.value)}
            disabled={submitting}
          />
          <Input
            id="reg-password-confirm"
            name="password_confirmation"
            type="password"
            label="Confirm password"
            autoComplete="new-password"
            required
            value={passwordConfirmation}
            onChange={(ev) => setPasswordConfirmation(ev.target.value)}
            disabled={submitting}
          />

          <Button type="submit" className="w-full" disabled={submitting}>
            {submitting ? 'Creating account…' : 'Create account'}
          </Button>
        </form>

        <p className="mt-6 text-center text-sm text-gray-600">
          Already have an account?{' '}
          <Link to="/login" className="font-semibold text-orange-600 hover:text-orange-700">
            Sign in
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
