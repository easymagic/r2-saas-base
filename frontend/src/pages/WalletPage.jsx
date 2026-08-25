import { useCallback, useEffect, useState } from 'react';
import { Link, useNavigate, useSearchParams } from 'react-router-dom';
import { UserHeader } from '../components/layout/UserHeader.jsx';
import { Badge } from '../components/ui/Badge.jsx';
import { Button } from '../components/ui/Button.jsx';
import { Input } from '../components/ui/Input.jsx';
import { Modal } from '../components/ui/Modal.jsx';
import { useToast } from '../context/ToastContext.jsx';
import { useSyncedWalletBalance } from '../hooks/useSyncedWalletBalance.js';
import { getStoredUser, saveAuthUser } from '../lib/authSession.js';
import { cn } from '../lib/cn.js';
import { formatNaira, initialsFromName } from '../lib/userDisplay.js';
import { fetchWalletFromApi, postPendingManualTopUp, postWalletTopUp } from '../lib/walletApi.js';

function formatWalletRowDate(value) {
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

function transactionKindLabel(t) {
  const type = String(t.type || '').toLowerCase();
  if (type === 'online') return 'Online top-up';
  if (type === 'manual') return 'Manual top-up';
  if (type === 'credit') return 'Credit';
  if (type === 'debit') return 'Payment or withdrawal';
  return type ? type.charAt(0).toUpperCase() + type.slice(1) : 'Activity';
}

function statusBadgeForTransaction(t) {
  const status = String(t.status || '').toLowerCase();
  if (status === 'rejected' || status === 'failed' || status === 'cancelled') {
    return { variant: 'rejected', label: 'Rejected' };
  }
  if (status === 'approved' || status === 'successful') {
    return { variant: 'approved', label: 'Approved' };
  }
  if (status === 'pending') {
    return { variant: 'pending', label: 'Pending' };
  }
  if (status) return { variant: 'default', label: status };
  return { variant: 'default', label: '—' };
}

function shortReference(ref) {
  if (ref == null || ref === '') return '—';
  const s = String(ref);
  return s.length > 22 ? `${s.slice(0, 20)}…` : s;
}

function transactionDescriptionCell(t) {
  const kind = transactionKindLabel(t);
  const desc = String(t.description || '').trim();
  if (!desc) {
    return kind;
  }
  return (
    <>
      <span className="block">{kind}</span>
      <span className="mt-0.5 block text-xs font-normal text-gray-500">{desc}</span>
    </>
  );
}

function parsePageParam(value) {
  const n = parseInt(String(value ?? ''), 10);
  return Number.isFinite(n) && n > 0 ? n : 1;
}

function paginationPages(current, last) {
  if (last <= 1) return [];
  const width = 5;
  const start = Math.max(1, Math.min(current - 2, last - width + 1));
  const end = Math.min(last, start + width - 1);
  return Array.from({ length: end - start + 1 }, (_, i) => start + i);
}

export function WalletPage() {
  const navigate = useNavigate();
  const [searchParams, setSearchParams] = useSearchParams();
  const page = parsePageParam(searchParams.get('page'));
  const [open, setOpen] = useState(false);
  const [topupMode, setTopupMode] = useState('online');
  const [amount, setAmount] = useState('');
  const [manualDescription, setManualDescription] = useState('');
  const [proofFile, setProofFile] = useState(null);
  const [proofInputKey, setProofInputKey] = useState(0);
  const [submitting, setSubmitting] = useState(false);
  const [formError, setFormError] = useState('');
  const { showToast } = useToast();
  const [balanceLabel, refreshWalletBalance] = useSyncedWalletBalance();
  const sessionUser = getStoredUser();
  const [txLoading, setTxLoading] = useState(true);
  const [txError, setTxError] = useState('');
  const [transactions, setTransactions] = useState([]);
  const [txTotal, setTxTotal] = useState(null);
  const [txLastPage, setTxLastPage] = useState(1);
  const [txHasNext, setTxHasNext] = useState(false);
  const [txHasPrev, setTxHasPrev] = useState(false);

  const loadWalletTransactions = useCallback(async () => {
    const u = getStoredUser();
    if (!u?.token || u.id == null) {
      navigate('/login', { replace: true });
      return;
    }
    setTxLoading(true);
    setTxError('');
    const r = await fetchWalletFromApi(u, page);
    setTxLoading(false);
    if (!r.ok) {
      setTxError(typeof r.message === 'string' && r.message.length > 0 ? r.message : 'Could not load transactions.');
      setTransactions([]);
      setTxTotal(null);
      setTxLastPage(1);
      setTxHasNext(false);
      setTxHasPrev(false);
      return;
    }
    const sorted = [...r.transactions].sort((a, b) => {
      const ta = new Date(String(a.created_at || a.action_at || '').replace(' ', 'T')).getTime();
      const tb = new Date(String(b.created_at || b.action_at || '').replace(' ', 'T')).getTime();
      return tb - ta;
    });
    setTransactions(sorted);
    setTxTotal(typeof r.total === 'number' ? r.total : sorted.length);
    setTxLastPage(typeof r.lastPage === 'number' ? r.lastPage : 1);
    setTxHasNext(!!r.hasNext);
    setTxHasPrev(!!r.hasPrev);
  }, [navigate, page]);

  useEffect(() => {
    loadWalletTransactions();
  }, [loadWalletTransactions]);

  function goToPage(nextPage) {
    const safePage = Math.max(1, Math.min(Number(nextPage) || 1, Math.max(1, txLastPage)));
    const next = new URLSearchParams(searchParams);
    if (safePage === 1) next.delete('page');
    else next.set('page', String(safePage));
    setSearchParams(next, { replace: false });
  }

  function resetWalletModalState() {
    setOpen(false);
    setTopupMode('online');
    setAmount('');
    setManualDescription('');
    setProofFile(null);
    setProofInputKey((k) => k + 1);
    setFormError('');
  }

  function closeWalletModal() {
    if (submitting) return;
    resetWalletModalState();
  }

  async function handleManualSubmit(e) {
    e.preventDefault();
    setFormError('');
    const u = getStoredUser();
    if (!u?.token || u.id == null) {
      navigate('/login', { replace: true });
      return;
    }

    const amountStr = amount.trim();
    if (!amountStr || Number(amountStr) <= 0) {
      setFormError('Enter a valid amount.');
      return;
    }
    if (!proofFile) {
      setFormError('Attach an image as proof of payment.');
      return;
    }

    setSubmitting(true);
    try {
      const result = await postPendingManualTopUp(u, {
        amount: amountStr,
        description: manualDescription,
        proofFile,
      });
      if (!result.ok) {
        const msg =
          typeof result.message === 'string' && result.message.length > 0
            ? result.message
            : 'Could not submit manual top-up request.';
        setFormError(msg);
        showToast(msg, 'error');
        return;
      }

      if (result.wallet?.user) saveAuthUser(result.wallet.user);
      await refreshWalletBalance();
      await loadWalletTransactions();
      showToast(result.message || 'Manual top-up request submitted.', 'success');
      resetWalletModalState();
    } catch {
      setFormError('Network error. Check that the API is running.');
      showToast('Network error. Check that the API is running.', 'error');
    } finally {
      setSubmitting(false);
    }
  }

  async function handleTopUpSubmit(e) {
    e.preventDefault();
    setFormError('');
    const u = getStoredUser();
    const headersUser = u;
    if (!headersUser?.token || headersUser.id == null) {
      navigate('/login', { replace: true });
      return;
    }

    const amountStr = amount.trim();
    if (!amountStr || Number(amountStr) <= 0) {
      setFormError('Enter a valid amount.');
      return;
    }

    // Open a tab synchronously (same user gesture) so the browser allows it; navigate after POST returns.
    const paymentTab = window.open('about:blank', '_blank');
    if (paymentTab) {
      try {
        paymentTab.opener = null;
      } catch {
        /* ignore */
      }
    }

    setSubmitting(true);
    try {
      const result = await postWalletTopUp(headersUser, amountStr);
      if (!result.ok) {
        paymentTab?.close();
        const msg =
          typeof result.message === 'string' && result.message.length > 0
            ? result.message
            : 'Could not start top-up.';
        setFormError(msg);
        showToast(msg, 'error');
        return;
      }

      const wallet = result.wallet;
      if (wallet?.user) saveAuthUser(wallet.user);
      await refreshWalletBalance();
      await loadWalletTransactions();

      const payUrl = typeof wallet?.payment_url === 'string' ? wallet.payment_url.trim() : '';
      if (payUrl.startsWith('http')) {
        if (paymentTab && !paymentTab.closed) {
          paymentTab.location.replace(payUrl);
        } else {
          const fallback = window.open(payUrl, '_blank', 'noopener,noreferrer');
          if (!fallback) {
            showToast('Allow pop-ups for this site to open checkout in a new tab.', 'info');
            window.location.assign(payUrl);
          }
        }
        showToast('Continue in the payment window to finish your top-up.', 'success');
      } else {
        paymentTab?.close();
        showToast('Top-up created, but no payment URL was returned.', 'error');
      }

      resetWalletModalState();
    } catch {
      paymentTab?.close();
      setFormError('Network error. Check that the API is running.');
      showToast('Network error. Check that the API is running.', 'error');
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <>
      <UserHeader
        title="Wallet"
        subtitle="Your balance and activity"
        right={
          <>
            <span className="hidden rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600 sm:inline">
              Balance: {balanceLabel}
            </span>
            <Link
              to="/profile"
              className="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-900 text-xs font-semibold text-white ring-2 ring-transparent transition hover:ring-orange-400/50 focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-500"
              aria-label="Edit profile"
              title="My profile"
            >
              {initialsFromName(sessionUser?.name)}
            </Link>
            <Button variant="orange" type="button" onClick={() => setOpen(true)}>
              Top up wallet
            </Button>
          </>
        }
      />
      <main className="flex-1 space-y-6 p-4 sm:p-6 lg:p-8">
        <section className="rounded-2xl bg-white p-6 shadow-md sm:flex sm:items-center sm:justify-between" aria-labelledby="balance-heading">
          <div>
            <h2 id="balance-heading" className="text-sm font-medium text-gray-500">
              Available balance
            </h2>
            <p className="mt-2 text-3xl font-semibold tracking-tight text-gray-900">{balanceLabel}</p>
            <p className="mt-2 text-sm text-gray-500">
              We keep this up to date when you visit this screen or after you sign in.
            </p>
          </div>
          <span className="mt-4 inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-800 sm:mt-0">
            Verified account
          </span>
        </section>

        <section className="rounded-2xl bg-white p-6 shadow-md">
          <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <h2 className="text-base font-semibold text-gray-900">Transactions</h2>
              {!txLoading && !txError ? (
                <p className="mt-1 text-xs text-gray-500">
                  Page {page}
                  {txLastPage > 1 ? ` of ${txLastPage}` : ''}
                  {txTotal != null && txTotal !== transactions.length
                    ? ` · ${transactions.length} shown · ${txTotal} total`
                    : ` · ${transactions.length} ${transactions.length === 1 ? 'transaction' : 'transactions'}`}
                </p>
              ) : null}
            </div>
            {txLoading ? (
              <span
                className="inline-flex h-9 items-center rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-500"
                aria-busy="true"
              >
                Loading…
              </span>
            ) : txError ? (
              <span className="text-xs text-red-600">{txError}</span>
            ) : (
              <div className="flex flex-wrap gap-2">
                <Button
                  type="button"
                  variant="secondary"
                  disabled={txLoading || !!txError || !txHasPrev}
                  onClick={() => goToPage(page - 1)}
                  aria-label="Previous page"
                >
                  Previous
                </Button>
                <Button
                  type="button"
                  variant="secondary"
                  disabled={txLoading || !!txError || !txHasNext}
                  onClick={() => goToPage(page + 1)}
                  aria-label="Next page"
                >
                  Next
                </Button>
                <Button type="button" variant="secondary" onClick={loadWalletTransactions} disabled={txLoading}>
                  Refresh
                </Button>
              </div>
            )}
          </div>
          {!txLoading && !txError && transactions.length === 0 ? (
            <p className="mt-4 text-sm text-gray-500">No wallet activity yet. Top up to see credits here.</p>
          ) : null}
          {transactions.length > 0 ? (
            <div className="mt-4 overflow-x-auto">
              <table className="w-full min-w-[40rem] border-collapse text-left text-sm">
                <thead>
                  <tr className="border-b border-gray-200 bg-gray-100 text-xs font-semibold uppercase tracking-wide text-gray-600">
                    <th className="px-4 py-3" scope="col">
                      Date
                    </th>
                    <th className="px-4 py-3" scope="col">
                      Reference
                    </th>
                    <th className="px-4 py-3" scope="col">
                      Description
                    </th>
                    <th className="px-4 py-3 text-right" scope="col">
                      Amount
                    </th>
                    <th className="px-4 py-3 text-right" scope="col">
                      Balance after
                    </th>
                    <th className="px-4 py-3" scope="col">
                      Status
                    </th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                  {transactions.map((t) => {
                    const type = String(t.type || '').toLowerCase();
                    const isCredit = type === 'credit';
                    const { variant, label } = statusBadgeForTransaction(t);
                    return (
                      <tr key={t.id} className="hover:bg-gray-50">
                        <td className="whitespace-nowrap px-4 py-3 text-gray-600">
                          {formatWalletRowDate(t.created_at)}
                        </td>
                        <td className="max-w-[10rem] truncate px-4 py-3 font-mono text-xs text-gray-800" title={String(t.reference || '')}>
                          {shortReference(t.reference)}
                        </td>
                        <td className="px-4 py-3 text-gray-700">{transactionDescriptionCell(t)}</td>
                        <td
                          className={`px-4 py-3 text-right font-medium tabular-nums ${
                            isCredit ? 'text-green-700' : 'text-red-600'
                          }`}
                        >
                          {isCredit ? '+' : '−'}
                          {formatNaira(t.amount)}
                        </td>
                        <td className="px-4 py-3 text-right tabular-nums text-gray-700">{formatNaira(t.balance)}</td>
                        <td className="px-4 py-3">
                          <Badge variant={variant}>{label}</Badge>
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>
          ) : null}
          {!txLoading && !txError && txLastPage > 1 ? (
            <div className="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 pt-4">
              <span className="text-xs text-gray-500">
                Page {page} of {txLastPage}
              </span>
              <div className="flex flex-wrap gap-2">
                <Button
                  type="button"
                  variant="secondary"
                  disabled={!txHasPrev}
                  onClick={() => goToPage(page - 1)}
                  aria-label="Previous page"
                >
                  Previous
                </Button>
                {paginationPages(page, txLastPage).map((p) => (
                  <Button
                    key={p}
                    type="button"
                    variant={p === page ? 'primary' : 'secondary'}
                    disabled={p === page}
                    onClick={() => goToPage(p)}
                    aria-label={`Page ${p}`}
                    aria-current={p === page ? 'page' : undefined}
                    className="min-w-10 px-3"
                  >
                    {p}
                  </Button>
                ))}
                <Button
                  type="button"
                  variant="secondary"
                  disabled={!txHasNext}
                  onClick={() => goToPage(page + 1)}
                  aria-label="Next page"
                >
                  Next
                </Button>
              </div>
            </div>
          ) : null}
        </section>
      </main>

      <Modal
        open={open}
        onClose={() => !submitting && closeWalletModal()}
        title={topupMode === 'manual' ? 'Request manual top-up' : 'Top up wallet'}
      >
        <div className="flex rounded-lg border border-gray-200 p-0.5">
          <button
            type="button"
            className={cn(
              'flex-1 rounded-md px-3 py-2 text-sm font-medium transition',
              topupMode === 'online'
                ? 'bg-orange-500 text-white'
                : 'text-gray-600 hover:bg-gray-50'
            )}
            disabled={submitting}
            onClick={() => {
              setTopupMode('online');
              setFormError('');
            }}
          >
            Pay online
          </button>
          <button
            type="button"
            className={cn(
              'flex-1 rounded-md px-3 py-2 text-sm font-medium transition',
              topupMode === 'manual'
                ? 'bg-orange-500 text-white'
                : 'text-gray-600 hover:bg-gray-50'
            )}
            disabled={submitting}
            onClick={() => {
              setTopupMode('manual');
              setFormError('');
            }}
          >
            Manual top-up
          </button>
        </div>
        <p className="mt-4 text-sm text-gray-600">
          {topupMode === 'online'
            ? 'Enter the amount you want to add. We’ll open a secure checkout so you can pay with card or bank.'
            : 'Submit amount, payment channel or note, and a screenshot. An admin will review and approve your wallet credit.'}
        </p>
        <form
          className="mt-4 space-y-4"
          onSubmit={topupMode === 'online' ? handleTopUpSubmit : handleManualSubmit}
          noValidate
        >
          {formError ? (
            <p className="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800" role="alert">
              {formError}
            </p>
          ) : null}
          <Input
            id="topup-amount"
            name="amount"
            label="Amount"
            required
            placeholder="500"
            inputMode="decimal"
            value={amount}
            onChange={(ev) => setAmount(ev.target.value)}
            disabled={submitting}
          />
          {topupMode === 'manual' ? (
            <>
              <Input
                id="manual-description"
                name="description"
                label="Payment channel / note"
                placeholder="e.g. PayPal, bank transfer reference"
                value={manualDescription}
                onChange={(ev) => setManualDescription(ev.target.value)}
                disabled={submitting}
              />
              <Input
                key={proofInputKey}
                id="manual-proof"
                name="proof_of_payment_screenshot1"
                type="file"
                accept="image/*"
                label="Proof of payment"
                required
                disabled={submitting}
                onChange={(ev) => setProofFile(ev.target.files?.[0] ?? null)}
              />
              {proofFile ? (
                <p className="text-xs text-gray-500">
                  Selected: <span className="font-medium text-gray-700">{proofFile.name}</span>
                </p>
              ) : null}
            </>
          ) : null}
          <div className="flex flex-wrap gap-3 pt-2">
            <Button type="submit" disabled={submitting}>
              {submitting
                ? topupMode === 'manual'
                  ? 'Submitting…'
                  : 'Starting…'
                : topupMode === 'manual'
                  ? 'Submit request'
                  : 'Continue to payment'}
            </Button>
            <Button type="button" variant="secondary" disabled={submitting} onClick={closeWalletModal}>
              Close
            </Button>
          </div>
        </form>
      </Modal>
    </>
  );
}
