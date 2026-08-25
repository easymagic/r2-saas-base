import { useMemo, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { UserHeader } from '../components/layout/UserHeader.jsx';
import { Badge } from '../components/ui/Badge.jsx';
import { Button } from '../components/ui/Button.jsx';
import { useToast } from '../context/ToastContext.jsx';
import { getStoredUser } from '../lib/authSession.js';
import {
  MIGRATION_JOBS,
  publishSnappyOrderSettings,
  runMigrationJob,
} from '../lib/migrationsApi.js';

function statusBadge(status) {
  if (status === 'ok') return { variant: 'approved', label: 'OK' };
  if (status === 'error') return { variant: 'rejected', label: 'Failed' };
  if (status === 'running') return { variant: 'pending', label: 'Running' };
  return { variant: 'default', label: 'Idle' };
}

export function AdminMigrationsPage() {
  const navigate = useNavigate();
  const { showToast } = useToast();
  const [runningId, setRunningId] = useState(null);
  const [runningAll, setRunningAll] = useState(false);
  const [publishing, setPublishing] = useState(false);
  const [results, setResults] = useState({});

  const busy = runningId != null || runningAll || publishing;

  const summary = useMemo(() => {
    const values = Object.values(results);
    const ok = values.filter((r) => r.status === 'ok').length;
    const failed = values.filter((r) => r.status === 'error').length;
    return { ok, failed, total: MIGRATION_JOBS.length };
  }, [results]);

  async function runOne(job) {
    setRunningId(job.id);
    setResults((prev) => ({
      ...prev,
      [job.id]: { status: 'running', message: 'Running…', path: job.path },
    }));

    const r = await runMigrationJob(job.path);
    setRunningId(null);
    setResults((prev) => ({
      ...prev,
      [job.id]: {
        status: r.ok ? 'ok' : 'error',
        message: r.message,
        path: job.path,
      },
    }));

    if (r.ok) showToast(`${job.label} migrated.`, 'success');
    else showToast(r.message || `${job.label} failed.`, 'error');
  }

  async function runAll() {
    setRunningAll(true);
    let failed = null;
    for (const job of MIGRATION_JOBS) {
      setRunningId(job.id);
      setResults((prev) => ({
        ...prev,
        [job.id]: { status: 'running', message: 'Running…', path: job.path },
      }));
      const r = await runMigrationJob(job.path);
      setResults((prev) => ({
        ...prev,
        [job.id]: {
          status: r.ok ? 'ok' : 'error',
          message: r.message,
          path: job.path,
        },
      }));
      if (!r.ok) {
        failed = r.message || `${job.label} failed.`;
        break;
      }
    }
    setRunningId(null);
    setRunningAll(false);
    if (failed) showToast(failed, 'error');
    else showToast('All migrations completed.', 'success');
  }

  async function handlePublishSettings() {
    const user = getStoredUser();
    if (!user?.token || user.id == null) {
      navigate('/login', { replace: true });
      return;
    }
    if (
      !window.confirm(
        'Publish SERVICE_CHARGE, SHIPPING_COST, and DOLLAR_TO_NAIRA_RATE into platform config?'
      )
    ) {
      return;
    }

    setPublishing(true);
    const r = await publishSnappyOrderSettings(user);
    setPublishing(false);

    if (!r.ok) {
      showToast(
        typeof r.message === 'string' && r.message.length > 0
          ? r.message
          : 'Could not publish settings.',
        'error'
      );
      return;
    }
    showToast(r.message || 'Settings published successfully.', 'success');
  }

  return (
    <>
      <UserHeader
        title="Migrations & tools"
        subtitle="Run schema migrations individually and publish snappy order settings"
      />
      <main className="flex-1 space-y-6 p-4 sm:p-6 lg:p-8">
        <section className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
          <div className="flex flex-wrap items-start justify-between gap-3">
            <div>
              <h2 className="text-sm font-semibold text-gray-900">Database migrations</h2>
              <p className="mt-1 text-xs text-gray-500">
                Uses <code className="rounded bg-slate-100 px-1">x-token</code> only (Postman Migrations
                folder). {summary.ok} ok · {summary.failed} failed · {summary.total} total
              </p>
            </div>
            <div className="flex flex-wrap gap-2">
              <Button type="button" variant="secondary" onClick={runAll} disabled={busy}>
                {runningAll ? 'Running all…' : 'Run all'}
              </Button>
            </div>
          </div>

          <div className="mt-4 overflow-hidden rounded-lg border border-slate-200">
            <div className="overflow-x-auto">
              <table className="w-full min-w-[40rem] border-collapse text-left text-sm">
                <thead>
                  <tr className="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-600">
                    <th className="px-4 py-3" scope="col">
                      Module
                    </th>
                    <th className="px-4 py-3" scope="col">
                      Path
                    </th>
                    <th className="px-4 py-3" scope="col">
                      Status
                    </th>
                    <th className="px-4 py-3" scope="col">
                      Result
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
                    const isRunning = runningId === job.id;
                    return (
                      <tr key={job.id} className="border-b border-slate-100 last:border-b-0">
                        <td className="px-4 py-3 font-medium text-gray-900">{job.label}</td>
                        <td className="px-4 py-3 font-mono text-xs text-slate-600">{job.path}</td>
                        <td className="px-4 py-3">
                          <Badge variant={badge.variant}>{badge.label}</Badge>
                        </td>
                        <td className="max-w-xs px-4 py-3 text-xs text-slate-600">
                          <span className="line-clamp-2 break-all">{result?.message || '—'}</span>
                        </td>
                        <td className="px-4 py-3 text-right">
                          <Button
                            type="button"
                            variant="secondary"
                            className="px-3 py-1.5 text-xs"
                            disabled={busy}
                            onClick={() => runOne(job)}
                          >
                            {isRunning ? 'Running…' : 'Run'}
                          </Button>
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>
          </div>
        </section>

        <section className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
          <div className="flex flex-wrap items-start justify-between gap-4">
            <div className="min-w-0 max-w-xl">
              <h2 className="text-sm font-semibold text-gray-900">Publish snappy order settings</h2>
              <p className="mt-1 text-xs leading-5 text-gray-500">
                Calls <code className="rounded bg-slate-100 px-1">POST /v2/snappy-orders/publish-settings</code>{' '}
                (<code className="rounded bg-slate-100 px-1">SnappyOrderController::publishSettings</code>
                ). Writes <code className="rounded bg-slate-100 px-1">SERVICE_CHARGE</code>,{' '}
                <code className="rounded bg-slate-100 px-1">SHIPPING_COST</code>, and{' '}
                <code className="rounded bg-slate-100 px-1">DOLLAR_TO_NAIRA_RATE</code> into platform
                config.
              </p>
            </div>
            <div className="flex flex-wrap gap-2">
              <Button as={Link} to="/admin/platform-config" variant="secondary">
                Open platform config
              </Button>
              <Button type="button" onClick={handlePublishSettings} disabled={busy}>
                {publishing ? 'Publishing…' : 'Publish settings'}
              </Button>
            </div>
          </div>
        </section>
      </main>
    </>
  );
}
