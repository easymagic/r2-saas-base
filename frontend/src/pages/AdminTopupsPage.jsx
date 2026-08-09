import { useCallback, useEffect, useState } from 'react';
import { UserHeader } from '../components/layout/UserHeader.jsx';
import { Badge } from '../components/ui/Badge.jsx';
import { Button } from '../components/ui/Button.jsx';
import { Modal } from '../components/ui/Modal.jsx';
import { useToast } from '../context/ToastContext.jsx';
import { getStoredUser } from '../lib/authSession.js';
import { apiMediaUrl } from '../lib/apiConfig.js';
import { formatNaira } from '../lib/userDisplay.js';
import {
  approvePendingManualTopupRequest,
  fetchApprovedTopupRequests,
  fetchOnePendingManualTopupRequest,
  fetchPendingManualTopupRequests,
  rejectPendingManualTopupRequest,
} from '../lib/walletApi.js';

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

function statusBadgeForTopup(row) {
  const approval = String(row?.approval_status || '').toLowerCase();
  const status = String(row?.status || '').toLowerCase();
  if (approval === 'rejected' || status === 'failed' || status === 'cancelled') {
    return { variant: 'rejected', label: approval === 'rejected' ? 'Rejected' : 'Failed' };
  }
  if (approval === 'approved') return { variant: 'approved', label: 'Approved' };
  if (approval === 'pending' || status === 'pending') return { variant: 'pending', label: 'Pending' };
  return { variant: 'default', label: approval || status || '—' };
}

export function AdminTopupsPage() {
  const [activeTab, setActiveTab] = useState('pending');
  const [preview, setPreview] = useState(null);
  const [loading, setLoading] = useState(true);
  const [loadError, setLoadError] = useState('');
  const [requests, setRequests] = useState([]);
  const [total, setTotal] = useState(0);
  const [approvedLoading, setApprovedLoading] = useState(false);
  const [approvedError, setApprovedError] = useState('');
  const [approvedRequests, setApprovedRequests] = useState([]);
  const [approvedTotal, setApprovedTotal] = useState(0);
  const [approvingId, setApprovingId] = useState(null);
  const [rejectingId, setRejectingId] = useState(null);
  const [previewLoading, setPreviewLoading] = useState(false);
  const [previewError, setPreviewError] = useState('');
  const { showToast } = useToast();

  const loadPendingRequests = useCallback(async () => {
    const user = getStoredUser();
    if (!user?.token || user.id == null) {
      setLoadError('No active session. Sign in again.');
      setRequests([]);
      setTotal(0);
      setLoading(false);
      return;
    }
    setLoading(true);
    setLoadError('');
    const r = await fetchPendingManualTopupRequests(user);
    setLoading(false);
    if (!r.ok) {
      setLoadError(typeof r.message === 'string' && r.message.length > 0 ? r.message : 'Could not load pending requests.');
      setRequests([]);
      setTotal(0);
      return;
    }
    setRequests(r.requests);
    setTotal(r.total);
  }, []);

  const loadApprovedRequests = useCallback(async () => {
    const user = getStoredUser();
    if (!user?.token || user.id == null) {
      setApprovedError('No active session. Sign in again.');
      setApprovedRequests([]);
      setApprovedTotal(0);
      return;
    }
    setApprovedLoading(true);
    setApprovedError('');
    const r = await fetchApprovedTopupRequests(user);
    setApprovedLoading(false);
    if (!r.ok) {
      setApprovedError(typeof r.message === 'string' && r.message.length > 0 ? r.message : 'Could not load approved requests.');
      setApprovedRequests([]);
      setApprovedTotal(0);
      return;
    }
    setApprovedRequests(r.requests);
    setApprovedTotal(r.total);
  }, []);

  useEffect(() => {
    loadPendingRequests();
    loadApprovedRequests();
  }, [loadPendingRequests, loadApprovedRequests]);

  async function handleApprove(requestId) {
    if (approvingId != null || rejectingId != null) return;
    const user = getStoredUser();
    if (!user?.token || user.id == null) {
      showToast('No active session. Sign in again.', 'error');
      return;
    }

    setApprovingId(requestId);
    try {
      const r = await approvePendingManualTopupRequest(user, requestId);
      if (!r.ok) {
        const msg = typeof r.message === 'string' && r.message.length > 0 ? r.message : 'Could not approve request.';
        showToast(msg, 'error');
        return;
      }
      showToast(r.data?.message || 'Manual wallet topup request approved successfully', 'success');
      await loadPendingRequests();
      if (preview?.id === requestId) setPreview(null);
    } finally {
      setApprovingId(null);
    }
  }

  async function handleReject(requestId) {
    if (approvingId != null || rejectingId != null) return;
    const user = getStoredUser();
    if (!user?.token || user.id == null) {
      showToast('No active session. Sign in again.', 'error');
      return;
    }
    const reason = window.prompt('Reason for rejection:');
    if (reason == null) return;
    if (!String(reason).trim()) {
      showToast('Enter a rejection reason.', 'error');
      return;
    }

    setRejectingId(requestId);
    try {
      const r = await rejectPendingManualTopupRequest(user, requestId, reason);
      if (!r.ok) {
        const msg = typeof r.message === 'string' && r.message.length > 0 ? r.message : 'Could not reject request.';
        showToast(msg, 'error');
        return;
      }
      showToast(r.data?.message || 'Manual wallet topup request rejected successfully', 'success');
      await loadPendingRequests();
      if (preview?.id === requestId) setPreview(null);
    } finally {
      setRejectingId(null);
    }
  }

  async function handleOpenPreview(requestRow) {
    setPreview(requestRow);
    setPreviewError('');
    const user = getStoredUser();
    if (!user?.token || user.id == null) {
      setPreviewError('No active session. Sign in again.');
      return;
    }
    setPreviewLoading(true);
    try {
      const r = await fetchOnePendingManualTopupRequest(user, requestRow.id);
      if (!r.ok) {
        const msg =
          typeof r.message === 'string' && r.message.length > 0 ? r.message : 'Could not load top-up request details.';
        setPreviewError(msg);
        return;
      }
      setPreview(r.request);
    } finally {
      setPreviewLoading(false);
    }
  }

  return (
    <>
      <UserHeader title="Top-up approvals" subtitle="Review payment proof and approve or reject requests" />
      <main className="flex-1 p-4 sm:p-6 lg:p-8">
        <section className="rounded-2xl bg-white p-6 shadow-md">
          <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div className="flex gap-1 rounded-lg border border-gray-200 bg-gray-100 p-1">
              <button
                type="button"
                onClick={() => setActiveTab('pending')}
                className={`rounded-md px-4 py-1.5 text-sm font-medium transition ${activeTab === 'pending' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'}`}
              >
                Pending
              </button>
              <button
                type="button"
                onClick={() => setActiveTab('approved')}
                className={`rounded-md px-4 py-1.5 text-sm font-medium transition ${activeTab === 'approved' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'}`}
              >
                Approved
              </button>
            </div>
            <Button
              type="button"
              variant="secondary"
              onClick={activeTab === 'pending' ? loadPendingRequests : loadApprovedRequests}
              disabled={activeTab === 'pending' ? (loading || approvingId != null || rejectingId != null) : approvedLoading}
            >
              Refresh
            </Button>
          </div>

          {activeTab === 'pending' ? (
            <>
              <div className="mb-3">
                {loading ? (
                  <span className="inline-flex h-9 items-center rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-500" aria-busy="true">
                    Loading pending requests...
                  </span>
                ) : loadError ? (
                  <span className="text-xs text-red-600">{loadError}</span>
                ) : (
                  <span className="text-xs text-gray-500">
                    {requests.length} shown{total !== requests.length ? ` · ${total} total` : ''}
                  </span>
                )}
              </div>
              <div className="overflow-x-auto">
                <table className="w-full min-w-[56rem] border-collapse text-left text-sm">
                  <thead>
                    <tr className="border-b border-gray-200 bg-gray-100 text-xs font-semibold uppercase text-gray-600">
                      <th className="px-4 py-3" scope="col">ID</th>
                      <th className="px-4 py-3" scope="col">User</th>
                      <th className="px-4 py-3 text-right" scope="col">Amount</th>
                      <th className="px-4 py-3" scope="col">Description</th>
                      <th className="px-4 py-3" scope="col">Date</th>
                      <th className="px-4 py-3" scope="col">Proof</th>
                      <th className="px-4 py-3" scope="col">Status</th>
                      <th className="px-4 py-3 text-right" scope="col">Actions</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-gray-100">
                    {requests.map((r) => (
                      <tr key={r.id} className="hover:bg-gray-50">
                        <td className="px-4 py-3 font-mono text-xs">{r.id}</td>
                        <td className="px-4 py-3 text-gray-700">
                          <span className="block font-medium text-gray-800">{r.user?.name || `User #${r.user_id}`}</span>
                          <span className="block text-xs text-gray-500">{r.user?.email || '—'}</span>
                        </td>
                        <td className="px-4 py-3 text-right font-medium tabular-nums">{formatNaira(r.amount)}</td>
                        <td className="px-4 py-3 text-gray-700">{r.description || '—'}</td>
                        <td className="whitespace-nowrap px-4 py-3 text-gray-600">{formatWalletRowDate(r.created_at)}</td>
                        <td className="px-4 py-3">
                          <button
                            type="button"
                            onClick={() => handleOpenPreview(r)}
                            disabled={!r.proof_of_payment_screenshot1}
                            className="text-sm font-medium text-orange-600 hover:text-orange-700"
                          >
                            {r.proof_of_payment_screenshot1 ? 'View image' : 'No proof'}
                          </button>
                        </td>
                        <td className="px-4 py-3">
                          {(() => {
                            const badge = statusBadgeForTopup(r);
                            return <Badge variant={badge.variant}>{badge.label}</Badge>;
                          })()}
                        </td>
                        <td className="px-4 py-3 text-right">
                          <div className="flex justify-end gap-2">
                            <Button
                              type="button"
                              variant="success"
                              onClick={() => handleApprove(r.id)}
                              disabled={approvingId != null || rejectingId != null}
                            >
                              {approvingId === r.id ? 'Approving...' : 'Approve'}
                            </Button>
                            <Button
                              type="button"
                              variant="danger"
                              onClick={() => handleReject(r.id)}
                              disabled={approvingId != null || rejectingId != null}
                            >
                              {rejectingId === r.id ? 'Rejecting...' : 'Reject'}
                            </Button>
                          </div>
                        </td>
                      </tr>
                    ))}
                    {!loading && !loadError && requests.length === 0 ? (
                      <tr>
                        <td className="px-4 py-6 text-sm text-gray-500" colSpan={8}>
                          No pending manual top-up requests.
                        </td>
                      </tr>
                    ) : null}
                  </tbody>
                </table>
              </div>
            </>
          ) : (
            <>
              <div className="mb-3">
                {approvedLoading ? (
                  <span className="inline-flex h-9 items-center rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-500" aria-busy="true">
                    Loading approved requests...
                  </span>
                ) : approvedError ? (
                  <span className="text-xs text-red-600">{approvedError}</span>
                ) : (
                  <span className="text-xs text-gray-500">
                    {approvedRequests.length} shown{approvedTotal !== approvedRequests.length ? ` · ${approvedTotal} total` : ''}
                  </span>
                )}
              </div>
              <div className="overflow-x-auto">
                <table className="w-full min-w-[48rem] border-collapse text-left text-sm">
                  <thead>
                    <tr className="border-b border-gray-200 bg-gray-100 text-xs font-semibold uppercase text-gray-600">
                      <th className="px-4 py-3" scope="col">ID</th>
                      <th className="px-4 py-3" scope="col">User</th>
                      <th className="px-4 py-3 text-right" scope="col">Amount</th>
                      <th className="px-4 py-3" scope="col">Description</th>
                      <th className="px-4 py-3" scope="col">Date</th>
                      <th className="px-4 py-3" scope="col">Proof</th>
                      <th className="px-4 py-3" scope="col">Status</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-gray-100">
                    {approvedRequests.map((r) => (
                      <tr key={r.id} className="hover:bg-gray-50">
                        <td className="px-4 py-3 font-mono text-xs">{r.id}</td>
                        <td className="px-4 py-3 text-gray-700">
                          <span className="block font-medium text-gray-800">{r.user?.name || `User #${r.user_id}`}</span>
                          <span className="block text-xs text-gray-500">{r.user?.email || '—'}</span>
                        </td>
                        <td className="px-4 py-3 text-right font-medium tabular-nums">{formatNaira(r.amount)}</td>
                        <td className="px-4 py-3 text-gray-700">{r.description || '—'}</td>
                        <td className="whitespace-nowrap px-4 py-3 text-gray-600">{formatWalletRowDate(r.created_at)}</td>
                        <td className="px-4 py-3">
                          <button
                            type="button"
                            onClick={() => handleOpenPreview(r)}
                            disabled={!r.proof_of_payment_screenshot1}
                            className="text-sm font-medium text-orange-600 hover:text-orange-700"
                          >
                            {r.proof_of_payment_screenshot1 ? 'View image' : 'No proof'}
                          </button>
                        </td>
                        <td className="px-4 py-3">
                          {(() => {
                            const badge = statusBadgeForTopup(r);
                            return <Badge variant={badge.variant}>{badge.label}</Badge>;
                          })()}
                        </td>
                      </tr>
                    ))}
                    {!approvedLoading && !approvedError && approvedRequests.length === 0 ? (
                      <tr>
                        <td className="px-4 py-6 text-sm text-gray-500" colSpan={7}>
                          No approved top-up requests.
                        </td>
                      </tr>
                    ) : null}
                  </tbody>
                </table>
              </div>
            </>
          )}
        </section>
      </main>

      <Modal open={!!preview} onClose={() => setPreview(null)} title="Proof of payment">
        {previewLoading ? (
          <div className="flex aspect-video items-center justify-center rounded-xl bg-gray-100 text-sm text-gray-500">
            Loading request details...
          </div>
        ) : previewError ? (
          <div className="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{previewError}</div>
        ) : preview?.proof_of_payment_screenshot1 ? (
          <img
            src={apiMediaUrl(preview.proof_of_payment_screenshot1)}
            alt={`Proof of payment for request #${preview.id}`}
            className="max-h-[70vh] w-full rounded-xl border border-gray-200 object-contain"
          />
        ) : (
          <div className="flex aspect-video items-center justify-center rounded-xl bg-gray-100 text-sm text-gray-500">
            No proof image attached.
          </div>
        )}
      </Modal>
    </>
  );
}
