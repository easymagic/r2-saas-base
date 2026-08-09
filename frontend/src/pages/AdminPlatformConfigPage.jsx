import { useCallback, useEffect, useMemo, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { UserHeader } from '../components/layout/UserHeader.jsx';
import { Button } from '../components/ui/Button.jsx';
import { Input } from '../components/ui/Input.jsx';
import { useToast } from '../context/ToastContext.jsx';
import { getStoredUser } from '../lib/authSession.js';
import {
  deletePlatformConfig,
  fetchPlatformConfig,
  updatePlatformConfigSetting,
} from '../lib/platformApi.js';

const KNOWN_LABELS = {
  SERVICE_CHARGE: 'Service charge',
  DOLLAR_TO_NAIRA_RATE: 'Dollar → naira rate',
  SHIPPING_COST: 'Shipping cost',
  BANK_NAME: 'Bank name',
  ACCOUNT_NUMBER: 'Account number',
  ACCOUNT_NAME: 'Account name',
  ACCOUNT_TYPE: 'Account type',
};

function formatConfigDate(value) {
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

function normalizeRow(row) {
  const key = String(row?.setting_key ?? row?.setting_name ?? row?.key ?? '').trim();
  return {
    id: row?.id ?? null,
    setting_key: key,
    setting_value: row?.setting_value ?? row?.value ?? '',
    created_at: row?.created_at ?? '',
    updated_at: row?.updated_at ?? '',
    draftValue: String(row?.setting_value ?? row?.value ?? ''),
    dirty: false,
    saving: false,
  };
}

function labelForKey(key) {
  const upper = String(key || '').toUpperCase();
  return KNOWN_LABELS[upper] || null;
}

export function AdminPlatformConfigPage() {
  const navigate = useNavigate();
  const { showToast } = useToast();
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [rows, setRows] = useState([]);
  const [search, setSearch] = useState('');
  const [deletingId, setDeletingId] = useState(null);
  const [newKey, setNewKey] = useState('');
  const [newValue, setNewValue] = useState('');
  const [adding, setAdding] = useState(false);

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
      setError(
        typeof r.message === 'string' && r.message.length > 0
          ? r.message
          : 'Could not load platform configuration.'
      );
      setRows([]);
      return;
    }
    const sorted = [...(r.list || [])]
      .map(normalizeRow)
      .filter((row) => row.setting_key)
      .sort((a, b) => a.setting_key.localeCompare(b.setting_key));
    setRows(sorted);
  }, [navigate]);

  useEffect(() => {
    load();
  }, [load]);

  const filteredRows = useMemo(() => {
    const q = search.trim().toLowerCase();
    if (!q) return rows;
    return rows.filter((row) => {
      const label = labelForKey(row.setting_key) || '';
      return (
        row.setting_key.toLowerCase().includes(q) ||
        String(row.draftValue).toLowerCase().includes(q) ||
        label.toLowerCase().includes(q)
      );
    });
  }, [rows, search]);

  const dirtyCount = useMemo(() => rows.filter((row) => row.dirty).length, [rows]);

  function setDraftValue(idOrKey, value) {
    setRows((prev) =>
      prev.map((row) => {
        const match = row.id != null ? Number(row.id) === Number(idOrKey) : row.setting_key === idOrKey;
        if (!match) return row;
        const next = String(value);
        return {
          ...row,
          draftValue: next,
          dirty: next !== String(row.setting_value ?? ''),
        };
      })
    );
  }

  async function handleSaveRow(row) {
    const user = getStoredUser();
    if (!user?.token || user.id == null) {
      navigate('/login', { replace: true });
      return;
    }
    const key = row.setting_key;
    const value = String(row.draftValue ?? '').trim();
    if (!key) return;

    setRows((prev) =>
      prev.map((r) => (r.setting_key === key ? { ...r, saving: true } : r))
    );

    const result = await updatePlatformConfigSetting(user, key, value);
    if (!result.ok) {
      setRows((prev) =>
        prev.map((r) => (r.setting_key === key ? { ...r, saving: false } : r))
      );
      showToast(
        typeof result.message === 'string' && result.message.length > 0
          ? result.message
          : 'Could not save setting.',
        'error'
      );
      return;
    }

    showToast('Setting saved.', 'success');
    await load();
  }

  async function handleDeleteRow(row) {
    const user = getStoredUser();
    if (!user?.token || user.id == null) {
      navigate('/login', { replace: true });
      return;
    }
    if (row.id == null) {
      showToast('This row has no id and cannot be deleted.', 'error');
      return;
    }
    if (!window.confirm(`Delete setting “${row.setting_key}”?`)) return;

    setDeletingId(row.id);
    const result = await deletePlatformConfig(user, row.id);
    setDeletingId(null);

    if (!result.ok) {
      showToast(
        typeof result.message === 'string' && result.message.length > 0
          ? result.message
          : 'Could not delete setting.',
        'error'
      );
      return;
    }
    showToast('Setting deleted.', 'success');
    await load();
  }

  async function handleAddSetting(e) {
    e.preventDefault();
    const user = getStoredUser();
    if (!user?.token || user.id == null) {
      navigate('/login', { replace: true });
      return;
    }
    const key = newKey.trim().toUpperCase().replace(/\s+/g, '_');
    const value = newValue.trim();
    if (!key) {
      showToast('Setting key is required.', 'error');
      return;
    }
    if (rows.some((row) => row.setting_key.toUpperCase() === key)) {
      showToast(`Setting “${key}” already exists. Edit it in the table.`, 'error');
      return;
    }

    setAdding(true);
    const result = await updatePlatformConfigSetting(user, key, value);
    setAdding(false);
    if (!result.ok) {
      showToast(
        typeof result.message === 'string' && result.message.length > 0
          ? result.message
          : 'Could not add setting.',
        'error'
      );
      return;
    }
    setNewKey('');
    setNewValue('');
    showToast('Setting added.', 'success');
    await load();
  }

  return (
    <>
      <UserHeader
        title="Platform config"
        subtitle="Key / value settings from GET /v2/platform-configs"
      />
      <main className="flex-1 p-4 sm:p-6 lg:p-8">
        <div className="mx-auto max-w-6xl space-y-5">
          <div className="flex flex-wrap items-end justify-between gap-3 rounded-xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
            <div className="min-w-0 flex-1">
              <p className="text-sm font-semibold text-gray-900">Configuration registry</p>
              {loading ? (
                <p className="mt-1 text-xs text-gray-500" aria-busy="true">
                  Loading platform configs…
                </p>
              ) : error ? (
                <p className="mt-1 text-sm text-red-600">{error}</p>
              ) : (
                <p className="mt-1 text-xs text-gray-500">
                  {rows.length} setting{rows.length === 1 ? '' : 's'}
                  {dirtyCount > 0 ? ` · ${dirtyCount} unsaved` : ''}
                </p>
              )}
            </div>
            <div className="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-center">
              <Input
                id="platform-config-search"
                type="search"
                placeholder="Filter by key or value…"
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                className="sm:w-64"
              />
              <Button type="button" variant="secondary" onClick={load} disabled={loading}>
                Refresh
              </Button>
            </div>
          </div>

          <form
            onSubmit={handleAddSetting}
            className="grid gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-[1fr_1.4fr_auto] sm:items-end"
          >
            <Input
              id="new-setting-key"
              label="New setting key"
              placeholder="e.g. SERVICE_CHARGE"
              value={newKey}
              onChange={(e) => setNewKey(e.target.value)}
              disabled={adding}
            />
            <Input
              id="new-setting-value"
              label="Value"
              placeholder="Setting value"
              value={newValue}
              onChange={(e) => setNewValue(e.target.value)}
              disabled={adding}
            />
            <Button type="submit" disabled={adding || loading}>
              {adding ? 'Adding…' : 'Add setting'}
            </Button>
          </form>

          <section className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div className="overflow-x-auto">
              <table className="w-full min-w-[52rem] border-collapse text-left text-sm">
                <thead>
                  <tr className="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-600">
                    <th className="px-4 py-3" scope="col">
                      Key
                    </th>
                    <th className="px-4 py-3" scope="col">
                      Value
                    </th>
                    <th className="px-4 py-3 whitespace-nowrap" scope="col">
                      Updated
                    </th>
                    <th className="px-4 py-3 text-right" scope="col">
                      Actions
                    </th>
                  </tr>
                </thead>
                <tbody>
                  {loading ? (
                    <tr>
                      <td colSpan={4} className="px-4 py-10 text-center text-sm text-gray-500">
                        Loading…
                      </td>
                    </tr>
                  ) : null}

                  {!loading && error ? (
                    <tr>
                      <td colSpan={4} className="px-4 py-10 text-center text-sm text-red-600">
                        {error}
                      </td>
                    </tr>
                  ) : null}

                  {!loading && !error && filteredRows.length === 0 ? (
                    <tr>
                      <td colSpan={4} className="px-4 py-10 text-center text-sm text-gray-500">
                        {search.trim()
                          ? 'No settings match this filter.'
                          : 'No platform configs yet. Add a setting above or run migrate.'}
                      </td>
                    </tr>
                  ) : null}

                  {!loading &&
                    !error &&
                    filteredRows.map((row) => {
                      const label = labelForKey(row.setting_key);
                      const busy = row.saving || deletingId != null;
                      return (
                        <tr
                          key={row.id ?? row.setting_key}
                          className="border-b border-slate-100 align-top last:border-b-0"
                        >
                          <td className="px-4 py-3">
                            <div className="font-mono text-xs font-semibold tracking-wide text-slate-900">
                              {row.setting_key}
                            </div>
                            {label ? (
                              <p className="mt-1 text-xs text-slate-500">{label}</p>
                            ) : null}
                            {row.id != null ? (
                              <p className="mt-1 font-mono text-[11px] text-slate-400">id {row.id}</p>
                            ) : null}
                          </td>
                          <td className="px-4 py-3">
                            <input
                              type="text"
                              aria-label={`Value for ${row.setting_key}`}
                              value={row.draftValue}
                              disabled={busy}
                              onChange={(e) =>
                                setDraftValue(row.id ?? row.setting_key, e.target.value)
                              }
                              className="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 font-mono text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:bg-slate-50"
                            />
                            {row.dirty ? (
                              <p className="mt-1 text-xs font-medium text-orange-600">Unsaved change</p>
                            ) : null}
                          </td>
                          <td className="px-4 py-3 whitespace-nowrap text-xs text-slate-600">
                            {formatConfigDate(row.updated_at || row.created_at)}
                          </td>
                          <td className="px-4 py-3">
                            <div className="flex flex-col items-stretch justify-end gap-2 sm:flex-row sm:items-center">
                              <Button
                                type="button"
                                variant="primary"
                                className="px-3 py-1.5 text-xs"
                                disabled={busy || !row.dirty}
                                onClick={() => handleSaveRow(row)}
                              >
                                {row.saving ? 'Saving…' : 'Save'}
                              </Button>
                              <Button
                                type="button"
                                variant="secondary"
                                className="border-red-200 px-3 py-1.5 text-xs text-red-600 hover:border-red-300 hover:bg-red-50"
                                disabled={busy || row.id == null}
                                onClick={() => handleDeleteRow(row)}
                              >
                                {deletingId != null && Number(deletingId) === Number(row.id)
                                  ? 'Deleting…'
                                  : 'Delete'}
                              </Button>
                            </div>
                          </td>
                        </tr>
                      );
                    })}
                </tbody>
              </table>
            </div>
            <div className="border-t border-slate-100 bg-slate-50/80 px-4 py-3 text-xs text-slate-500">
              Updates use <code className="rounded bg-white px-1">POST /v2/platform-configs/update</code>{' '}
              with <code className="rounded bg-white px-1">setting_name</code> /{' '}
              <code className="rounded bg-white px-1">setting_value</code>. Delete uses{' '}
              <code className="rounded bg-white px-1">DELETE /v2/platform-configs/{'{id}'}/delete</code>.
            </div>
          </section>
        </div>
      </main>
    </>
  );
}
