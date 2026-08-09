import { useCallback, useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { UserHeader } from '../components/layout/UserHeader.jsx';
import { Button } from '../components/ui/Button.jsx';
import { Card } from '../components/ui/Card.jsx';
import { Input } from '../components/ui/Input.jsx';
import { useToast } from '../context/ToastContext.jsx';
import { getStoredUser } from '../lib/authSession.js';
import { fetchPlatformConfig, savePlatformConfig } from '../lib/platformApi.js';

function ConfigField({ helper, ...props }) {
  return (
    <div>
      <Input {...props} />
      {helper ? <p className="mt-1 text-xs text-gray-500">{helper}</p> : null}
    </div>
  );
}

export function AdminPlatformConfigPage() {
  const navigate = useNavigate();
  const { showToast } = useToast();
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [config, setConfig] = useState(null);
  const [saving, setSaving] = useState(false);
  const [formError, setFormError] = useState('');
  const [form, setForm] = useState({
    service_charge: '',
    dollar_to_naira_rate: '',
    shipping_cost: '',
    bank_name: '',
    account_number: '',
    account_name: '',
    account_type: '',
  });

  const load = useCallback(async () => {
    const user = getStoredUser();
    if (!user?.token || user.id == null) {
      navigate('/login', { replace: true });
      return;
    }
    setLoading(true);
    setError('');
    const r = await fetchPlatformConfig(user);
    setLoading(false);
    if (!r.ok) {
      setError(typeof r.message === 'string' && r.message.length > 0 ? r.message : 'Could not load configuration.');
      setConfig(null);
      return;
    }
    setConfig(r.config);
    setForm({
      service_charge: String(r.config.service_charge ?? ''),
      dollar_to_naira_rate: String(r.config.dollar_to_naira_rate ?? ''),
      shipping_cost: String(r.config.shipping_cost ?? ''),
      bank_name: String(r.config.bank_name ?? ''),
      account_number: String(r.config.account_number ?? ''),
      account_name: String(r.config.account_name ?? ''),
      account_type: String(r.config.account_type ?? ''),
    });
  }, [navigate]);

  useEffect(() => {
    load();
  }, [load]);

  function setField(name, value) {
    setForm((prev) => ({ ...prev, [name]: value }));
  }

  async function handleSave(e) {
    e.preventDefault();
    setFormError('');
    const user = getStoredUser();
    if (!user?.token || user.id == null) {
      navigate('/login', { replace: true });
      return;
    }
    const required = [
      'service_charge',
      'dollar_to_naira_rate',
      'shipping_cost',
      'bank_name',
      'account_number',
      'account_name',
      'account_type',
    ];
    for (const key of required) {
      if (!String(form[key] ?? '').trim()) {
        setFormError('All fields are required.');
        return;
      }
    }

    setSaving(true);
    try {
      const r = await savePlatformConfig(user, form);
      if (!r.ok) {
        const msg =
          typeof r.message === 'string' && r.message.length > 0 ? r.message : 'Could not save platform configuration.';
        setFormError(msg);
        showToast(msg, 'error');
        return;
      }
      setConfig(r.config);
      setForm({
        service_charge: String(r.config.service_charge ?? ''),
        dollar_to_naira_rate: String(r.config.dollar_to_naira_rate ?? ''),
        shipping_cost: String(r.config.shipping_cost ?? ''),
        bank_name: String(r.config.bank_name ?? ''),
        account_number: String(r.config.account_number ?? ''),
        account_name: String(r.config.account_name ?? ''),
        account_type: String(r.config.account_type ?? ''),
      });
      showToast(r.data?.message || 'Platform config updated successfully', 'success');
    } finally {
      setSaving(false);
    }
  }

  return (
    <>
      <UserHeader title="Platform settings" subtitle="Configure charges, exchange rates, shipping, and payout details" />
      <main className="flex-1 p-4 sm:p-6 lg:p-8">
        <div className="mx-auto flex max-w-7xl flex-col gap-6 xl:flex-row xl:items-start">
          <aside className="w-full shrink-0 xl:sticky xl:top-6 xl:w-72">
            <nav className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm" aria-label="Settings sections">
              <div className="border-b border-gray-100 px-4 py-3">
                <p className="text-xs font-semibold uppercase tracking-wide text-gray-500">Settings</p>
              </div>
              <a
                href="#rates"
                className="flex items-start gap-3 border-b border-gray-100 px-4 py-4 text-sm hover:bg-gray-50"
              >
                <span className="mt-1 h-2 w-2 rounded-full bg-blue-900" aria-hidden="true" />
                <span>
                  <span className="block font-semibold text-gray-900">Rates and fees</span>
                  <span className="mt-1 block text-xs leading-5 text-gray-500">Service charge, FX rate, and shipping.</span>
                </span>
              </a>
              <a href="#payout" className="flex items-start gap-3 px-4 py-4 text-sm hover:bg-gray-50">
                <span className="mt-1 h-2 w-2 rounded-full bg-orange-500" aria-hidden="true" />
                <span>
                  <span className="block font-semibold text-gray-900">Payout account</span>
                  <span className="mt-1 block text-xs leading-5 text-gray-500">Bank details shown for manual top-ups.</span>
                </span>
              </a>
            </nav>

            <div className="mt-4 rounded-xl border border-gray-200 bg-white p-4 text-sm shadow-sm">
              <p className="font-semibold text-gray-900">Configuration record</p>
              <dl className="mt-3 space-y-2 text-xs">
                <div className="flex justify-between gap-3">
                  <dt className="text-gray-500">Config ID</dt>
                  <dd className="font-medium text-gray-900">{config?.id ?? '-'}</dd>
                </div>
                <div className="flex justify-between gap-3">
                  <dt className="text-gray-500">Brand ID</dt>
                  <dd className="font-medium text-gray-900">{config?.brand_id ?? '-'}</dd>
                </div>
                <div>
                  <dt className="text-gray-500">Last updated</dt>
                  <dd className="mt-1 break-words font-medium text-gray-900">{config?.updated_at || '-'}</dd>
                </div>
              </dl>
            </div>
          </aside>

          <section className="min-w-0 flex-1 space-y-5">
            <div className="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-gray-200 bg-white px-5 py-4 shadow-sm">
              <div>
                <p className="text-sm font-semibold text-gray-900">Platform configuration</p>
                {loading ? (
                  <p className="mt-1 text-xs text-gray-500" aria-busy="true">
                    Loading configuration...
                  </p>
                ) : error ? (
                  <p className="mt-1 text-sm text-red-600">{error}</p>
                ) : (
                  <p className="mt-1 text-xs text-gray-500">Changes affect future orders and customer payment instructions.</p>
                )}
              </div>
              <Button type="button" variant="secondary" onClick={load} disabled={loading || saving}>
                Refresh
              </Button>
            </div>

          {!loading && !error ? (
            <form className="space-y-5" onSubmit={handleSave} noValidate>
              {formError ? (
                <p className="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700" role="alert">
                  {formError}
                </p>
              ) : null}

              <Card id="rates" className="rounded-xl p-0 shadow-sm">
                <div className="border-b border-gray-100 px-5 py-4">
                  <h2 className="text-base font-semibold text-gray-900">Rates and fees</h2>
                  <p className="mt-1 text-sm text-gray-500">Set the values used when calculating order totals.</p>
                </div>
                <div className="grid gap-5 px-5 py-5 lg:grid-cols-3">
                  <ConfigField
                    id="service-charge"
                    label="Service charge"
                    inputMode="decimal"
                    value={form.service_charge}
                    onChange={(e) => setField('service_charge', e.target.value)}
                    disabled={saving}
                    helper="Flat service fee added before conversion."
                    required
                  />
                  <ConfigField
                    id="dollar-rate"
                    label="Dollar to naira rate"
                    inputMode="decimal"
                    value={form.dollar_to_naira_rate}
                    onChange={(e) => setField('dollar_to_naira_rate', e.target.value)}
                    disabled={saving}
                    helper="Exchange rate applied to order totals."
                    required
                  />
                  <ConfigField
                    id="shipping-cost"
                    label="Shipping cost"
                    inputMode="decimal"
                    value={form.shipping_cost}
                    onChange={(e) => setField('shipping_cost', e.target.value)}
                    disabled={saving}
                    helper="Default shipping amount added to orders."
                    required
                  />
                </div>
              </Card>

              <Card id="payout" className="rounded-xl p-0 shadow-sm">
                <div className="border-b border-gray-100 px-5 py-4">
                  <h2 className="text-base font-semibold text-gray-900">Payout account</h2>
                  <p className="mt-1 text-sm text-gray-500">Maintain the bank details customers use for manual top-ups.</p>
                </div>
                <div className="grid gap-5 px-5 py-5 lg:grid-cols-2">
                  <ConfigField
                    id="bank-name"
                    label="Bank name"
                    value={form.bank_name}
                    onChange={(e) => setField('bank_name', e.target.value)}
                    disabled={saving}
                    helper="Receiving bank for bank transfers."
                    required
                  />
                  <ConfigField
                    id="account-number"
                    label="Account number"
                    value={form.account_number}
                    onChange={(e) => setField('account_number', e.target.value)}
                    disabled={saving}
                    helper="Use digits only where possible."
                    required
                  />
                  <ConfigField
                    id="account-name"
                    label="Account name"
                    value={form.account_name}
                    onChange={(e) => setField('account_name', e.target.value)}
                    disabled={saving}
                    helper="Legal or trading name on the account."
                    required
                  />
                  <ConfigField
                    id="account-type"
                    label="Account type"
                    value={form.account_type}
                    onChange={(e) => setField('account_type', e.target.value)}
                    disabled={saving}
                    helper="Example: Business Account."
                    required
                  />
                </div>
              </Card>

              <div className="sticky bottom-0 z-10 -mx-4 border-t border-gray-200 bg-gray-50/95 px-4 py-4 backdrop-blur sm:mx-0 sm:rounded-xl sm:border sm:bg-white sm:shadow-sm">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                  <p className="text-xs text-gray-500">Review changes before saving. Reset reloads the last server values.</p>
                  <div className="flex flex-col gap-3 sm:flex-row">
                    <Button type="button" variant="secondary" onClick={load} disabled={saving || loading}>
                      Reset
                    </Button>
                    <Button type="submit" disabled={saving}>
                      {saving ? 'Saving...' : 'Save changes'}
                    </Button>
                  </div>
                </div>
              </div>
            </form>
          ) : !loading && !error ? (
            <Card className="rounded-xl text-sm text-gray-500 shadow-sm">No configuration data.</Card>
          ) : null}
          </section>
        </div>
      </main>
    </>
  );
}
