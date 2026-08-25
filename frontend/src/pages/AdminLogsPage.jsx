import { useCallback, useEffect, useState } from 'react';
import { UserHeader } from '../components/layout/UserHeader.jsx';
import { Badge } from '../components/ui/Badge.jsx';
import { Button } from '../components/ui/Button.jsx';
import { Input } from '../components/ui/Input.jsx';
import { getStoredUser } from '../lib/authSession.js';
import { fetchLogsFromApi } from '../lib/logsApi.js';

function formatLogDate(value) {
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

function typeBadge(type) {
  const t = String(type || '').toLowerCase();
  if (t === 'success') return { variant: 'approved', label: 'Success' };
  if (t === 'error') return { variant: 'rejected', label: 'Error' };
  return { variant: 'default', label: type || '—' };
}

function previewText(value, max = 80) {
  const s = String(value ?? '').trim();
  if (!s) return '—';
  return s.length > max ? `${s.slice(0, max)}…` : s;
}

export function AdminLogsPage() {
  const [type, setType] = useState('');
  const [search, setSearch] = useState('');
  const [applied, setApplied] = useState({ type: '', search: '' });
  const [loading, setLoading] = useState(true);
  const [loadError, setLoadError] = useState('');
  const [logs, setLogs] = useState([]);
  const [count, setCount] = useState(0);
  const [expandedId, setExpandedId] = useState(null);

  const loadLogs = useCallback(async () => {
    const user = getStoredUser();
    if (!user?.token || user.id == null) {
      setLoadError('No active session. Sign in again.');
      setLogs([]);
      setCount(0);
      setLoading(false);
      return;
    }

    setLoading(true);
    setLoadError('');
    const r = await fetchLogsFromApi(user, applied);
    setLoading(false);

    if (!r.ok) {
      setLoadError(
        typeof r.message === 'string' && r.message.length > 0 ? r.message : 'Could not load logs.'
      );
      setLogs([]);
      setCount(0);
      return;
    }

    setLogs(r.logs);
    setCount(r.count);
  }, [applied]);

  useEffect(() => {
    loadLogs();
  }, [loadLogs]);

  function handleApplyFilters(e) {
    e.preventDefault();
    setExpandedId(null);
    setApplied({
      type: String(type || '').trim(),
      search: String(search || '').trim(),
    });
  }

  function handleClearFilters() {
    setType('');
    setSearch('');
    setExpandedId(null);
    setApplied({ type: '', search: '' });
  }

  return (
    <>
      <UserHeader title="Logs" subtitle="System and payment verification logs" />
      <main className="flex-1 space-y-6 p-4 sm:p-6 lg:p-8">
        <form
          className="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-end"
          onSubmit={handleApplyFilters}
        >
          <label className="block min-w-[10rem] flex-1 text-sm">
            <span className="mb-1 block font-medium text-gray-700">Type</span>
            <select
              className="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
              value={type}
              onChange={(e) => setType(e.target.value)}
            >
              <option value="">All</option>
              <option value="success">Success</option>
              <option value="error">Error</option>
            </select>
          </label>
          <div className="min-w-0 flex-[2]">
            <Input
              id="logs-search"
              label="Search"
              type="search"
              placeholder="Title, payload, or response…"
              value={search}
              onChange={(e) => setSearch(e.target.value)}
            />
          </div>
          <div className="flex gap-2">
            <Button type="submit" variant="primary">
              Apply
            </Button>
            <Button type="button" variant="secondary" onClick={handleClearFilters}>
              Clear
            </Button>
          </div>
        </form>

        <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
          <div className="flex items-center justify-between border-b border-slate-100 px-4 py-3">
            <p className="text-sm text-gray-600">
              {loading ? 'Loading…' : `${count} log${count === 1 ? '' : 's'}`}
            </p>
            <Button type="button" variant="secondary" onClick={loadLogs} disabled={loading}>
              Refresh
            </Button>
          </div>

          {loadError ? (
            <p className="px-4 py-6 text-sm font-medium text-red-700">{loadError}</p>
          ) : null}

          {!loadError && !loading && logs.length === 0 ? (
            <p className="px-4 py-8 text-sm text-gray-500">No logs match these filters.</p>
          ) : null}

          {!loadError && logs.length > 0 ? (
            <div className="overflow-x-auto">
              <table className="w-full min-w-[48rem] border-collapse text-left text-sm">
                <thead>
                  <tr className="border-b border-gray-200 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-gray-600">
                    <th className="px-4 py-3" scope="col">
                      ID
                    </th>
                    <th className="px-4 py-3" scope="col">
                      Title
                    </th>
                    <th className="px-4 py-3" scope="col">
                      Type
                    </th>
                    <th className="px-4 py-3" scope="col">
                      Preview
                    </th>
                    <th className="px-4 py-3" scope="col">
                      Created
                    </th>
                    <th className="px-4 py-3 text-right" scope="col">
                      Detail
                    </th>
                  </tr>
                </thead>
                <tbody>
                  {logs.map((log) => {
                    const badge = typeBadge(log.type);
                    const open = expandedId === log.id;
                    return (
                      <tr key={log.id} className="border-b border-gray-100 align-top">
                        <td className="px-4 py-3 font-mono text-xs text-gray-700">{log.id}</td>
                        <td className="px-4 py-3 font-medium text-gray-900">{log.title || '—'}</td>
                        <td className="px-4 py-3">
                          <Badge variant={badge.variant}>{badge.label}</Badge>
                        </td>
                        <td className="px-4 py-3 text-gray-600">
                          {open ? (
                            <div className="space-y-2">
                              <div>
                                <p className="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                  Payload
                                </p>
                                <pre className="mt-1 max-h-48 overflow-auto whitespace-pre-wrap break-all rounded-lg bg-slate-50 p-2 text-xs text-gray-800">
                                  {log.payload || '—'}
                                </pre>
                              </div>
                              <div>
                                <p className="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                  Response
                                </p>
                                <pre className="mt-1 max-h-48 overflow-auto whitespace-pre-wrap break-all rounded-lg bg-slate-50 p-2 text-xs text-gray-800">
                                  {log.response || '—'}
                                </pre>
                              </div>
                            </div>
                          ) : (
                            previewText(log.response || log.payload)
                          )}
                        </td>
                        <td className="px-4 py-3 whitespace-nowrap text-gray-600">
                          {formatLogDate(log.created_at)}
                        </td>
                        <td className="px-4 py-3 text-right">
                          <Button
                            type="button"
                            variant="ghost"
                            onClick={() => setExpandedId(open ? null : log.id)}
                          >
                            {open ? 'Hide' : 'View'}
                          </Button>
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>
          ) : null}
        </div>
      </main>
    </>
  );
}
