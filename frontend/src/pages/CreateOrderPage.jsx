import { useEffect, useState } from 'react';
import { Link as RouterLink, useNavigate } from 'react-router-dom';
import { UserHeader } from '../components/layout/UserHeader.jsx';
import { apiData, apiMessage, apiUrl, readApiJson, userAuthHeaders } from '../lib/apiConfig.js';
import { Button } from '../components/ui/Button.jsx';
import { Input } from '../components/ui/Input.jsx';
import { Textarea } from '../components/ui/Textarea.jsx';
import { useToast } from '../context/ToastContext.jsx';
import { useSyncedWalletBalance } from '../hooks/useSyncedWalletBalance.js';
import { getStoredUser, mergeSessionTokenFromOrderUser } from '../lib/authSession.js';
import { initialsFromName } from '../lib/userDisplay.js';

export function CreateOrderPage() {
  const navigate = useNavigate();
  const { showToast } = useToast();
  const [balanceLabel] = useSyncedWalletBalance();
  const [user, setUser] = useState(() => getStoredUser());
  const [link, setLink] = useState('');
  const [description, setDescription] = useState('');
  const [totalAmountUsd, setTotalAmountUsd] = useState('');
  const [screenShot1, setScreenShot1] = useState(null);
  const [screenShot2, setScreenShot2] = useState(null);
  const [screenShot3, setScreenShot3] = useState(null);
  const [submitting, setSubmitting] = useState(false);
  const [formError, setFormError] = useState('');

  useEffect(() => {
    const u = getStoredUser();
    if (!u?.token || u.id == null) {
      navigate('/login', { replace: true });
      return;
    }
    setUser(u);
  }, [navigate]);

  async function handleSubmit(e) {
    e.preventDefault();
    setFormError('');
    const u = getStoredUser();
    const headers = userAuthHeaders(u);
    if (!headers) {
      navigate('/login', { replace: true });
      return;
    }

    const amountStr = totalAmountUsd.trim();
    if (!amountStr) {
      setFormError('Enter the amount in USD.');
      return;
    }

    if (!link.trim()) {
      setFormError('Add a product or reference link.');
      return;
    }

    if (!(screenShot1 instanceof File)) {
      setFormError('At least one screenshot is required.');
      return;
    }

    setSubmitting(true);
    const body = new FormData();
    body.append('link', link.trim());
    body.append('description', description.trim());
    body.append('total_amount_usd', amountStr);
    body.append('screen_shot1', screenShot1);
    if (screenShot2 instanceof File) body.append('screen_shot2', screenShot2);
    if (screenShot3 instanceof File) body.append('screen_shot3', screenShot3);

    try {
      const res = await fetch(apiUrl('/v2/snappy-orders'), {
        method: 'POST',
        headers,
        credentials: 'include',
        body,
      });

      let data = null;
      try {
        data = await readApiJson(res);
      } catch {
        setFormError('Unexpected response from server.');
        showToast('Could not create order. Try again.', 'error');
        return;
      }

      const nested = apiData(data);
      const order = nested?.order ?? data?.order;
      if (data?.success && order) {
        if (order.user) mergeSessionTokenFromOrderUser(order.user);
        showToast(apiMessage(data, 'Order created successfully'), 'success');
        const id = order.id;
        if (id != null) navigate(`/orders/${id}`, { replace: true });
        else navigate('/orders', { replace: true });
        return;
      }

      const msg = apiMessage(data, 'Could not create order.');
      setFormError(msg);
      showToast(msg, 'error');
    } catch {
      setFormError('Network error. Check that the API is running.');
      showToast('Network error. Check that the API is running.', 'error');
    } finally {
      setSubmitting(false);
    }
  }

  if (!user?.token) {
    return null;
  }

  return (
    <>
      <UserHeader
        title="Create order"
        subtitle="Product link, USD amount, and reference screenshots"
        right={
          <>
            <span className="hidden rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600 sm:inline">
              Balance: {balanceLabel}
            </span>
            <RouterLink
              to="/profile"
              className="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-900 text-xs font-semibold text-white ring-2 ring-transparent transition hover:ring-orange-400/50 focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-500"
              aria-label="Edit profile"
              title="My profile"
            >
              {initialsFromName(user.name)}
            </RouterLink>
          </>
        }
      />
      <main className="flex-1 p-4 sm:p-6 lg:p-8">
        <div className="w-full max-w-2xl">
          <section className="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-md shadow-slate-900/5 sm:p-8">
            <h2 className="text-base font-semibold text-gray-900">Product request</h2>
            <p className="mt-1 text-sm text-gray-500">
              Fields match <code className="rounded bg-slate-100 px-1 text-xs">POST /v2/snappy-orders</code>.
            </p>

            <form className="mt-6 space-y-6" onSubmit={handleSubmit} noValidate>
              {formError ? (
                <p className="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800" role="alert">
                  {formError}
                </p>
              ) : null}
              <Input
                id="link"
                name="link"
                type="text"
                label="Product link"
                required
                placeholder="https://..."
                value={link}
                onChange={(ev) => setLink(ev.target.value)}
                disabled={submitting}
              />
              <Textarea
                id="description"
                name="description"
                label="Description"
                rows={4}
                placeholder="Size, color, quantity…"
                value={description}
                onChange={(ev) => setDescription(ev.target.value)}
                disabled={submitting}
              />
              <div>
                <span className="block text-sm font-medium text-gray-700">Screenshots</span>
                <p className="mt-1 text-xs text-gray-500">
                  <code className="rounded bg-slate-100 px-1">screen_shot1</code> is required; 2 and 3 are optional.
                </p>
                <div className="mt-3 grid gap-3 sm:grid-cols-3">
                  {[
                    { id: 'screen_shot1', file: screenShot1, set: setScreenShot1, required: true },
                    { id: 'screen_shot2', file: screenShot2, set: setScreenShot2, required: false },
                    { id: 'screen_shot3', file: screenShot3, set: setScreenShot3, required: false },
                  ].map((slot) => (
                    <label
                      key={slot.id}
                      htmlFor={slot.id}
                      className="flex min-h-[7.5rem] cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 px-3 py-4 text-center transition hover:border-orange-400 hover:bg-orange-50/40"
                    >
                      <span className="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        {slot.id.replace('screen_shot', 'Shot ')}
                        {slot.required ? ' *' : ''}
                      </span>
                      <span className="mt-2 line-clamp-2 text-sm font-medium text-gray-700">
                        {slot.file ? slot.file.name : 'Browse image'}
                      </span>
                      <input
                        id={slot.id}
                        name={slot.id}
                        type="file"
                        accept="image/*"
                        className="sr-only"
                        required={slot.required}
                        onChange={(ev) => slot.set(ev.target.files?.[0] || null)}
                        disabled={submitting}
                      />
                    </label>
                  ))}
                </div>
              </div>
              <div>
                <label htmlFor="total_amount_usd" className="block text-sm font-medium text-gray-700">
                  Amount (USD)
                </label>
                <div className="relative mt-1">
                  <span className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm font-semibold text-gray-500">
                    $
                  </span>
                  <input
                    id="total_amount_usd"
                    name="total_amount_usd"
                    className="w-full rounded-lg border border-gray-300 px-3 py-2 pl-8 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required
                    placeholder="50"
                    inputMode="decimal"
                    value={totalAmountUsd}
                    onChange={(ev) => setTotalAmountUsd(ev.target.value)}
                    disabled={submitting}
                  />
                </div>
              </div>
              <div className="flex flex-wrap gap-3">
                <Button type="submit" disabled={submitting}>
                  {submitting ? 'Submitting…' : 'Submit order'}
                </Button>
                <Button as={RouterLink} to="/orders" variant="secondary">
                  Cancel
                </Button>
              </div>
            </form>
          </section>
        </div>
      </main>
    </>
  );
}
