import { useCallback, useEffect, useMemo, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { UserHeader } from '../components/layout/UserHeader.jsx';
import { Button } from '../components/ui/Button.jsx';
import { Card } from '../components/ui/Card.jsx';
import { Textarea } from '../components/ui/Textarea.jsx';
import { useToast } from '../context/ToastContext.jsx';
import { getStoredUser } from '../lib/authSession.js';
import { fetchSettingsFromApi, saveSettings, saveSettingsOnApi } from '../lib/settingsApi.js';

function GearIcon() {
  return (
    <svg viewBox="0 0 24 24" aria-hidden="true" className="h-4 w-4">
      <path
        d="M12 15.5A3.5 3.5 0 1 0 12 8a3.5 3.5 0 0 0 0 7.5Z"
        fill="none"
        stroke="currentColor"
        strokeWidth="1.8"
      />
      <path
        d="M19.4 15a8.1 8.1 0 0 0 .1-1l2-1.5-2-3.5-2.4 1a8 8 0 0 0-1.7-1l-.3-2.6H11l-.3 2.6a8 8 0 0 0-1.7 1L6.6 9l-2 3.5 2 1.5a8.1 8.1 0 0 0 0 2l-2 1.5 2 3.5 2.4-1a8 8 0 0 0 1.7 1l.3 2.6h4.1l.3-2.6a8 8 0 0 0 1.7-1l2.4 1 2-3.5-2.1-1.5Z"
        fill="none"
        stroke="currentColor"
        strokeLinecap="round"
        strokeLinejoin="round"
        strokeWidth="1.8"
      />
    </svg>
  );
}

function makeRow(setting = {}) {
  const key = typeof setting.key === 'string' ? setting.key : '';
  return {
    rowId: `${key || 'new'}-${Date.now()}-${Math.random().toString(16).slice(2)}`,
    id: setting.id,
    key,
    value: setting.value == null ? '' : String(setting.value),
  };
}

export function AdminSettingsPage() {
  const navigate = useNavigate();
  const { showToast } = useToast();
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');
  const [formError, setFormError] = useState('');
  const [rows, setRows] = useState([]);

  const settingsCount = useMemo(() => rows.filter((row) => row.key.trim()).length, [rows]);

  const load = useCallback(async () => {
    const user = getStoredUser();
    if (!user?.token || user.id == null) {
      navigate('/login', { replace: true });
      return;
    }

    setLoading(true);
    setError('');
    const result = await fetchSettingsFromApi(getStoredUser());
    setLoading(false);

    if (!result.ok) {
      const msg =
        typeof result.message === 'string' && result.message.length > 0 ? result.message : 'Could not load settings.';
      setError(msg);
      setRows([]);
      return;
    }

    saveSettings(result.settings.list);
    setRows(result.settings.list.map(makeRow));
  }, [navigate]);

  useEffect(() => {
    load();
  }, [load]);

  function updateRow(rowId, patch) {
    setRows((prev) => prev.map((row) => (row.rowId === rowId ? { ...row, ...patch } : row)));
  }

  async function handleSave(e) {
    e.preventDefault();
    setFormError('');

    const user = getStoredUser();
    if (!user?.token || user.id == null) {
      navigate('/login', { replace: true });
      return;
    }

    const values = {};
    for (const row of rows) {
      const key = row.key.trim();
      if (!key) {
        setFormError('A setting is missing its key. Refresh and try again.');
        return;
      }
      if (values[key] !== undefined) {
        setFormError(`Duplicate setting key: ${key}`);
        return;
      }
      values[key] = row.value;
    }

    setSaving(true);
    try {
      const result = await saveSettingsOnApi(user, values);
      if (!result.ok) {
        const msg =
          typeof result.message === 'string' && result.message.length > 0 ? result.message : 'Could not save settings.';
        setFormError(msg);
        showToast(msg, 'error');
        return;
      }

      const refreshed = await fetchSettingsFromApi(user);
      if (refreshed.ok) {
        saveSettings(refreshed.settings.list);
        setRows(refreshed.settings.list.map(makeRow));
      }
      showToast(result.data?.message || 'Settings saved successfully', 'success');
    } finally {
      setSaving(false);
    }
  }

  return (
    <>
      <UserHeader title="Settings" subtitle="Manage framework app settings and deployment controls" />
      <main className="flex-1 p-4 sm:p-6 lg:p-8">
        <div className="mx-auto max-w-6xl space-y-5">
          <div className="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-gray-200 bg-white px-5 py-4 shadow-sm">
            <div>
              <p className="text-sm font-semibold text-gray-900">App settings</p>
              {loading ? (
                <p className="mt-1 text-xs text-gray-500" aria-busy="true">
                  Loading settings...
                </p>
              ) : error ? (
                <p className="mt-1 text-sm text-red-600">{error}</p>
              ) : (
                <p className="mt-1 text-xs text-gray-500">{settingsCount} settings loaded.</p>
              )}
            </div>
            <div className="flex gap-2">
              <Button type="button" variant="secondary" onClick={load} disabled={loading || saving}>
                Refresh
              </Button>
            </div>
          </div>

          {!loading && !error ? (
            <form className="space-y-5" onSubmit={handleSave} noValidate>
              {formError ? (
                <p className="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700" role="alert">
                  {formError}
                </p>
              ) : null}

              <Card className="rounded-xl p-0 shadow-sm">
                <div className="overflow-x-auto">
                  <table className="min-w-[760px] table-fixed divide-y divide-gray-100">
                    <colgroup>
                      <col className="w-16" />
                      <col className="w-[32%]" />
                      <col />
                      <col className="w-32" />
                    </colgroup>
                    <thead className="bg-gray-50">
                      <tr>
                        <th scope="col" className="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                          Type
                        </th>
                        <th scope="col" className="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                          Key
                        </th>
                        <th scope="col" className="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                          Value
                        </th>
                        <th scope="col" className="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                          Status
                        </th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100 bg-white">
                      {rows.length === 0 ? (
                        <tr>
                          <td colSpan={4} className="px-5 py-8 text-center text-sm text-gray-500">
                            No settings yet.
                          </td>
                        </tr>
                      ) : (
                        rows.map((row) => (
                          <tr key={row.rowId} className="align-top">
                            <td className="px-5 py-4">
                              <span
                                className="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 text-blue-900"
                                title="Setting"
                              >
                                <GearIcon />
                              </span>
                            </td>
                            <td className="px-5 py-4">
                              <div className="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 font-mono text-sm font-semibold text-gray-800">
                                {row.key}
                              </div>
                            </td>
                            <td className="px-5 py-4">
                              <Textarea
                                id={`setting-value-${row.rowId}`}
                                aria-label={`Value for ${row.key || 'new setting'}`}
                                rows={2}
                                value={row.value}
                                onChange={(e) => updateRow(row.rowId, { value: e.target.value })}
                                disabled={saving}
                                className="min-h-[76px]"
                              />
                            </td>
                            <td className="px-5 py-4">
                              <span className="inline-flex rounded-full border border-gray-200 px-2.5 py-1 text-xs font-semibold text-gray-600">
                                Read-only key
                              </span>
                            </td>
                          </tr>
                        ))
                      )}
                    </tbody>
                  </table>
                </div>
              </Card>

              <div className="flex justify-end">
                <Button type="submit" disabled={saving || loading}>
                  {saving ? 'Saving...' : 'Save settings'}
                </Button>
              </div>
            </form>
          ) : null}
        </div>
      </main>
    </>
  );
}
