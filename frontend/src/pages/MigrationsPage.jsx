import { useState } from 'react';
import { Link } from 'react-router-dom';
import { Button } from '../components/ui/Button.jsx';
import { useToast } from '../context/ToastContext.jsx';
import { apiUrl, xTokenHeader } from '../lib/apiConfig.js';

function formatMigrationResponse(text) {
  if (!text) return 'Migration request completed.';
  return text
    .replace(/<br\s*\/?>/gi, '\n')
    .replace(/<[^>]*>/g, '')
    .trim();
}

export function MigrationsPage() {
  const { showToast } = useToast();
  const [installing, setInstalling] = useState(false);
  const [result, setResult] = useState('');
  const [error, setError] = useState('');

  async function handleInstall() {
    setInstalling(true);
    setResult('');
    setError('');

    const migratePaths = [
      '/v2/migrate',
      '/v2/wallet/migrate',
      '/v2/notifications/migrate',
      '/v2/platform-configs/migrate',
      '/v2/snappy-orders/migrate',
      '/v2/batches/migrate',
      '/v2/threads/migrate',
    ];

    try {
      const lines = [];
      for (const path of migratePaths) {
        const res = await fetch(apiUrl(path), {
          method: 'GET',
          headers: {
            'x-token': xTokenHeader(),
          },
          credentials: 'include',
        });
        const text = await res.text();
        const message = formatMigrationResponse(text);
        lines.push(`${path}: ${res.ok ? message || 'ok' : message || `failed (${res.status})`}`);
        if (!res.ok) {
          setError(lines.join('\n'));
          showToast('Migration failed.', 'error');
          return;
        }
      }

      setResult(lines.join('\n'));
      showToast('Migration completed.', 'success');
    } catch {
      setError('Network error. Check that the API is running.');
      showToast('Network error. Check that the API is running.', 'error');
    } finally {
      setInstalling(false);
    }
  }

  return (
    <main className="flex min-h-screen flex-col items-center justify-center bg-gray-50 p-4 sm:p-6">
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

      <section className="w-full max-w-md rounded-2xl bg-white p-6 shadow-lg sm:p-8" aria-labelledby="migrations-heading">
        <h2 id="migrations-heading" className="text-lg font-semibold text-gray-900">
          Migrations
        </h2>
        <p className="mt-1 text-sm text-gray-500">Install database tables and platform settings.</p>

        <div className="mt-6 space-y-4">
          {error ? (
            <p className="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800" role="alert">
              {error}
            </p>
          ) : null}

          {result ? (
            <pre className="max-h-48 overflow-auto whitespace-pre-wrap rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-900">
              {result}
            </pre>
          ) : null}

          <Button type="button" className="w-full" onClick={handleInstall} disabled={installing}>
            {installing ? 'Installing...' : 'Install'}
          </Button>
        </div>

        <p className="mt-6 text-center text-sm text-gray-600">
          <Link to="/" className="font-semibold text-blue-700 hover:text-blue-800">
            Home
          </Link>
          <span className="mx-2 text-gray-300">|</span>
          <Link to="/login" className="font-semibold text-orange-600 hover:text-orange-700">
            Sign in
          </Link>
        </p>
      </section>
    </main>
  );
}
