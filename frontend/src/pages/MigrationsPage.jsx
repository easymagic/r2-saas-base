import { useState } from 'react';
import { Link } from 'react-router-dom';
import { Badge } from '../components/ui/Badge.jsx';
import { Button } from '../components/ui/Button.jsx';
import { useToast } from '../context/ToastContext.jsx';
import { MIGRATION_JOBS, runMigrationJob } from '../lib/migrationsApi.js';

function statusBadge(status) {
  if (status === 'ok') return { variant: 'approved', label: 'OK' };
  if (status === 'error') return { variant: 'rejected', label: 'Failed' };
  if (status === 'running') return { variant: 'pending', label: 'Running' };
  return { variant: 'default', label: 'Idle' };
}

export function MigrationsPage() {
  const { showToast } = useToast();
  const [runningId, setRunningId] = useState(null);
  const [runningAll, setRunningAll] = useState(false);
  const [results, setResults] = useState({});

  const busy = runningId != null || runningAll;

  async function runOne(job) {
    setRunningId(job.id);
    setResults((prev) => ({
      ...prev,
      [job.id]: { status: 'running', message: 'Running…' },
    }));
    const r = await runMigrationJob(job.path);
    setRunningId(null);
    setResults((prev) => ({
      ...prev,
      [job.id]: { status: r.ok ? 'ok' : 'error', message: r.message },
    }));
    if (r.ok) showToast(`${job.label} migrated.`, 'success');
    else showToast(r.message || 'Migration failed.', 'error');
  }

  async function handleInstallAll() {
    setRunningAll(true);
    let failed = null;
    for (const job of MIGRATION_JOBS) {
      setRunningId(job.id);
      setResults((prev) => ({
        ...prev,
        [job.id]: { status: 'running', message: 'Running…' },
      }));
      const r = await runMigrationJob(job.path);
      setResults((prev) => ({
        ...prev,
        [job.id]: { status: r.ok ? 'ok' : 'error', message: r.message },
      }));
      if (!r.ok) {
        failed = r.message || `${job.label} failed.`;
        break;
      }
    }
    setRunningId(null);
    setRunningAll(false);
    if (failed) showToast(failed, 'error');
    else showToast('Migration completed.', 'success');
  }

  return (
    <main className="flex min-h-screen flex-col items-center bg-gray-50 p-4 sm:p-6">
      <Link
        to="/"
        className="mb-8 rounded-2xl text-center focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-500 focus-visible:ring-offset-4"
        aria-label="Go to homepage"
      >
        <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-900 text-lg font-bold text-white">
          BF
        </div>
        <h1 className="mt-4 text-xl font-semibold text-gray-900">BorderlessFetch</h1>
        <p className="mt-1 text-sm text-gray-500">Social commerce fulfillment</p>
      </Link>

      <section
        className="w-full max-w-3xl rounded-2xl bg-white p-6 shadow-lg sm:p-8"
        aria-labelledby="migrations-heading"
      >
        <div className="flex flex-wrap items-start justify-between gap-3">
          <div>
            <h2 id="migrations-heading" className="text-lg font-semibold text-gray-900">
              Migrations
            </h2>
            <p className="mt-1 text-sm text-gray-500">
              Run each table migration individually, or install all. Uses{' '}
              <code className="rounded bg-slate-100 px-1 text-xs">x-token</code> only.
            </p>
          </div>
          <Button type="button" onClick={handleInstallAll} disabled={busy}>
            {runningAll ? 'Installing…' : 'Install all'}
          </Button>
        </div>

        <div className="mt-6 overflow-hidden rounded-xl border border-slate-200">
          <table className="w-full border-collapse text-left text-sm">
            <thead>
              <tr className="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-600">
                <th className="px-4 py-3" scope="col">
                  Module
                </th>
                <th className="hidden px-4 py-3 sm:table-cell" scope="col">
                  Path
                </th>
                <th className="px-4 py-3" scope="col">
                  Status
                </th>
                <th className="px-4 py-3 text-right" scope="col">
                  Action
                </th>
              </tr>
            </thead>
            <tbody>
              {MIGRATION_JOBS.map((job) => {
                const result = results[job.id];
                const badge = statusBadge(result?.status);
                return (
                  <tr key={job.id} className="border-b border-slate-100 last:border-b-0">
                    <td className="px-4 py-3 font-medium text-gray-900">{job.label}</td>
                    <td className="hidden px-4 py-3 font-mono text-xs text-slate-500 sm:table-cell">
                      {job.path}
                    </td>
                    <td className="px-4 py-3">
                      <Badge variant={badge.variant}>{badge.label}</Badge>
                      {result?.message && result.status !== 'running' ? (
                        <p className="mt-1 max-w-[12rem] truncate text-[11px] text-slate-500" title={result.message}>
                          {result.message}
                        </p>
                      ) : null}
                    </td>
                    <td className="px-4 py-3 text-right">
                      <Button
                        type="button"
                        variant="secondary"
                        className="px-3 py-1.5 text-xs"
                        disabled={busy}
                        onClick={() => runOne(job)}
                      >
                        {runningId === job.id ? 'Running…' : 'Run'}
                      </Button>
                    </td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        </div>

        <p className="mt-6 text-center text-sm text-gray-600">
          <Link to="/" className="font-semibold text-blue-700 hover:text-blue-800">
            Home
          </Link>
          <span className="mx-2 text-gray-300">|</span>
          <Link to="/login" className="font-semibold text-orange-600 hover:text-orange-700">
            Sign in
          </Link>
          <span className="mx-2 text-gray-300">|</span>
          <Link to="/admin/migrations" className="font-semibold text-slate-700 hover:text-slate-900">
            Admin tools
          </Link>
        </p>
      </section>
    </main>
  );
}
