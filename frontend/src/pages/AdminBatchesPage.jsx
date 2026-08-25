import { useCallback, useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { UserHeader } from '../components/layout/UserHeader.jsx';
import { Button } from '../components/ui/Button.jsx';
import { Card } from '../components/ui/Card.jsx';
import { Input } from '../components/ui/Input.jsx';
import { Modal } from '../components/ui/Modal.jsx';
import { Textarea } from '../components/ui/Textarea.jsx';
import { useToast } from '../context/ToastContext.jsx';
import { getStoredUser } from '../lib/authSession.js';
import { batchOrdersTableCell, batchSelectOptionLabel } from '../lib/batchDisplay.js';
import { createBatchFromApi, deleteBatchFromApi, fetchBatchesFromApi, updateBatchFromApi } from '../lib/batchesApi.js';
import { assignOrderToBatchFromApi, fetchOrdersFromApi } from '../lib/ordersApi.js';
import { formatNaira } from '../lib/userDisplay.js';

function formatBatchCreated(value) {
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

function orderListLabel(order) {
  const id = order?.id != null ? String(order.id) : '—';
  const ref = order?.reference != null && String(order.reference).trim() !== '' ? String(order.reference) : null;
  const amt = order?.grand_total_amount ?? order?.total_amount;
  const parts = [`#${id}`];
  if (ref) parts.push(ref);
  parts.push(formatNaira(amt));
  return parts.join(' · ');
}

function orderAlreadyInBatch(order, batchIdStr) {
  if (!batchIdStr || order == null) return false;
  const bid = order.batch_id;
  if (bid == null || bid === '' || Number(bid) === 0) return false;
  return Number(bid) === Number(batchIdStr);
}

export function AdminBatchesPage() {
  const navigate = useNavigate();
  const [selected, setSelected] = useState(() => new Set());
  const [batches, setBatches] = useState([]);
  const [loading, setLoading] = useState(true);
  const [loadError, setLoadError] = useState('');
  const [targetBatchId, setTargetBatchId] = useState('');
  const [deletingId, setDeletingId] = useState(null);
  const [orders, setOrders] = useState([]);
  const [ordersLoading, setOrdersLoading] = useState(true);
  const [ordersError, setOrdersError] = useState('');
  const [assigning, setAssigning] = useState(false);
  const [createSaving, setCreateSaving] = useState(false);
  const [editBatch, setEditBatch] = useState(null);
  const [editName, setEditName] = useState('');
  const [editDescription, setEditDescription] = useState('');
  const [editSaving, setEditSaving] = useState(false);
  const [editError, setEditError] = useState('');
  const { showToast } = useToast();

  const loadBatches = useCallback(async () => {
    const u = getStoredUser();
    if (!u?.token || u.id == null) {
      navigate('/login', { replace: true });
      return;
    }
    setLoading(true);
    setLoadError('');
    const r = await fetchBatchesFromApi(u);
    setLoading(false);
    if (!r.ok) {
      setLoadError(
        typeof r.message === 'string' && r.message.length > 0 ? r.message : 'Could not load batches.'
      );
      setBatches([]);
      return;
    }
    const sorted = [...r.batches].sort((a, b) => {
      const ta = new Date(String(a.created_at || '').replace(' ', 'T')).getTime();
      const tb = new Date(String(b.created_at || '').replace(' ', 'T')).getTime();
      return tb - ta;
    });
    setBatches(sorted);
  }, [navigate]);

  const loadOrders = useCallback(async () => {
    const u = getStoredUser();
    if (!u?.token || u.id == null) {
      navigate('/login', { replace: true });
      return;
    }
    setOrdersLoading(true);
    setOrdersError('');
    const r = await fetchOrdersFromApi(u);
    setOrdersLoading(false);
    if (!r.ok) {
      setOrdersError(
        typeof r.message === 'string' && r.message.length > 0 ? r.message : 'Could not load orders.'
      );
      setOrders([]);
      return;
    }
    const sorted = [...r.orders].sort((a, b) => {
      const ta = new Date(String(a.created_at || '').replace(' ', 'T')).getTime();
      const tb = new Date(String(b.created_at || '').replace(' ', 'T')).getTime();
      return tb - ta;
    });
    setOrders(sorted);
  }, [navigate]);

  useEffect(() => {
    loadBatches();
    loadOrders();
  }, [loadBatches, loadOrders]);

  useEffect(() => {
    if (batches.length === 0) {
      setTargetBatchId('');
      return;
    }
    setTargetBatchId((prev) => {
      const ids = new Set(batches.map((b) => String(b.id)));
      if (prev && ids.has(prev)) return prev;
      return String(batches[0].id);
    });
  }, [batches]);

  useEffect(() => {
    setSelected(new Set());
  }, [targetBatchId]);

  function toggle(id) {
    setSelected((prev) => {
      const next = new Set(prev);
      if (next.has(id)) next.delete(id);
      else next.add(id);
      return next;
    });
  }

  async function handleDeleteBatch(batch) {
    const u = getStoredUser();
    if (!u?.token || u.id == null) {
      navigate('/login', { replace: true });
      return;
    }
    const name = batch?.name != null ? String(batch.name) : `Batch ${batch?.id}`;
    if (!window.confirm(`Delete batch “${name}”? This cannot be undone.`)) return;

    const id = batch?.id;
    setDeletingId(id);
    const r = await deleteBatchFromApi(u, id);
    setDeletingId(null);

    if (!r.ok) {
      const msg =
        typeof r.message === 'string' && r.message.length > 0 ? r.message : 'Could not delete batch.';
      showToast(msg, 'error');
      return;
    }
    showToast('Batch deleted.', 'success');
    await loadBatches();
  }

  async function handleAssignSelected() {
    const u = getStoredUser();
    if (!u?.token || u.id == null) {
      navigate('/login', { replace: true });
      return;
    }
    if (!targetBatchId) {
      showToast('Choose a target batch.', 'error');
      return;
    }
    const ids = [...selected];
    if (ids.length === 0) {
      showToast('Select at least one order.', 'error');
      return;
    }

    setAssigning(true);
    let okCount = 0;
    const failures = [];
    for (const oid of ids) {
      const r = await assignOrderToBatchFromApi(u, oid, targetBatchId);
      if (r.ok) okCount += 1;
      else
        failures.push({
          id: oid,
          message:
            typeof r.message === 'string' && r.message.length > 0 ? r.message : 'Could not assign order.',
        });
    }
    setAssigning(false);

    if (failures.length === 0) {
      showToast(
        okCount === 1 ? 'Order assigned to batch.' : `${okCount} orders assigned to batch.`,
        'success'
      );
      setSelected(new Set());
      await loadBatches();
      await loadOrders();
      return;
    }

    if (okCount > 0) {
      showToast(
        `Assigned ${okCount}; ${failures.length} failed (${failures[0].message})`,
        'error'
      );
      setSelected(new Set());
      await loadBatches();
      await loadOrders();
      return;
    }

    showToast(failures[0].message, 'error');
  }

  function openEditBatch(batch) {
    setEditBatch(batch);
    setEditName(typeof batch.name === 'string' ? batch.name : '');
    setEditDescription(typeof batch.description === 'string' ? batch.description : '');
    setEditError('');
  }

  function closeEditBatch() {
    setEditBatch(null);
    setEditName('');
    setEditDescription('');
    setEditError('');
  }

  async function handleEditBatchSubmit(e) {
    e.preventDefault();
    const u = getStoredUser();
    if (!u?.token || u.id == null) {
      navigate('/login', { replace: true });
      return;
    }
    if (!editName.trim()) {
      setEditError('Name is required.');
      return;
    }
    setEditSaving(true);
    const r = await updateBatchFromApi(u, editBatch.id, { name: editName, description: editDescription });
    setEditSaving(false);
    if (!r.ok) {
      const msg = typeof r.message === 'string' && r.message.length > 0 ? r.message : 'Could not update batch.';
      setEditError(msg);
      showToast(msg, 'error');
      return;
    }
    showToast(typeof r.message === 'string' && r.message.length > 0 ? r.message : 'Batch updated.', 'success');
    closeEditBatch();
    await loadBatches();
  }

  async function handleCreateBatchSubmit(e) {
    e.preventDefault();
    const u = getStoredUser();
    if (!u?.token || u.id == null) {
      navigate('/login', { replace: true });
      return;
    }
    const form = e.currentTarget;
    const fd = new FormData(form);
    const name = String(fd.get('name') ?? '').trim();
    const description = String(fd.get('description') ?? '');
    if (!name) {
      showToast('Name is required.', 'error');
      return;
    }

    setCreateSaving(true);
    const r = await createBatchFromApi(u, { name, description });
    setCreateSaving(false);

    if (!r.ok) {
      const msg =
        typeof r.message === 'string' && r.message.length > 0 ? r.message : 'Could not create batch.';
      showToast(msg, 'error');
      return;
    }
    showToast(typeof r.message === 'string' && r.message.length > 0 ? r.message : 'Batch created.', 'success');
    form.reset();
    await loadBatches();
  }

  return (
    <>
      <UserHeader title="Batches" subtitle="Group orders into batches for easier fulfillment" />
      <main className="flex-1 space-y-6 p-4 sm:p-6 lg:p-8">
        <div className="grid gap-6 lg:grid-cols-2">
          <Card>
            <h2 className="text-base font-semibold text-gray-900">Create batch</h2>
            <p className="mt-1 text-sm text-gray-500">Give the batch a name and optional notes.</p>
            <form className="mt-4 space-y-4" onSubmit={handleCreateBatchSubmit}>
              <Input id="batch-name" name="name" label="Name" placeholder="batch1-101" required disabled={createSaving} />
              <Textarea
                id="batch-desc"
                name="description"
                label="Description"
                rows={3}
                disabled={createSaving}
              />
              <Button type="submit" disabled={createSaving}>
                {createSaving ? 'Creating…' : 'Create batch'}
              </Button>
            </form>
          </Card>

          <Card>
            <h2 className="text-base font-semibold text-gray-900">Assign orders</h2>
            <p className="mt-1 text-sm text-gray-500">Choose a batch, tick the orders to include, then confirm.</p>
            <div className="mt-4">
              <label htmlFor="target-batch" className="block text-sm font-medium text-gray-700">
                Target batch
              </label>
              <select
                id="target-batch"
                className="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                value={targetBatchId}
                onChange={(e) => setTargetBatchId(e.target.value)}
                disabled={loading || !!loadError || batches.length === 0}
              >
                {batches.length === 0 ? (
                  <option value="">No batches</option>
                ) : (
                  batches.map((b) => (
                    <option key={b.id} value={String(b.id)}>
                      {batchSelectOptionLabel(b)}
                    </option>
                  ))
                )}
              </select>
            </div>
            <fieldset className="mt-4">
              <legend className="text-sm font-medium text-gray-700">Orders</legend>
              {ordersError ? (
                <p className="mt-2 text-sm text-red-600">{ordersError}</p>
              ) : null}
              <ul className="mt-2 max-h-48 space-y-2 overflow-y-auto rounded-xl border border-gray-200 p-3">
                {ordersLoading ? (
                  <li className="px-2 py-2 text-sm text-gray-500">Loading orders…</li>
                ) : orders.length === 0 ? (
                  <li className="px-2 py-2 text-sm text-gray-500">No orders to show.</li>
                ) : (
                  orders.map((o) => {
                    const oid = String(o.id);
                    const inBatch = orderAlreadyInBatch(o, targetBatchId);
                    return (
                      <li key={oid}>
                        <label
                          className={`flex cursor-pointer items-center gap-3 rounded-lg px-2 py-2 hover:bg-gray-50 ${inBatch ? 'opacity-60' : ''}`}
                        >
                          <input
                            type="checkbox"
                            checked={selected.has(oid)}
                            disabled={inBatch || assigning || ordersLoading || !!ordersError}
                            onChange={() => toggle(oid)}
                            className="h-4 w-4 rounded border-gray-300 text-blue-900 focus:ring-blue-500 disabled:cursor-not-allowed"
                          />
                          <span className="text-sm text-gray-800">
                            {orderListLabel(o)}
                            {inBatch ? (
                              <span className="ml-2 text-xs font-normal text-gray-500">(in this batch)</span>
                            ) : null}
                          </span>
                        </label>
                      </li>
                    );
                  })
                )}
              </ul>
            </fieldset>
            <Button
              type="button"
              variant="orange"
              className="mt-4"
              disabled={
                assigning ||
                ordersLoading ||
                !!ordersError ||
                !targetBatchId ||
                batches.length === 0 ||
                selected.size === 0
              }
              onClick={handleAssignSelected}
            >
              {assigning ? 'Assigning…' : 'Assign selected'}
            </Button>
          </Card>
        </div>

        <Card className="overflow-hidden p-0">
          <div className="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-6 py-4">
            <h2 className="text-base font-semibold text-gray-900">All batches</h2>
            <Button type="button" variant="secondary" onClick={loadBatches} disabled={loading}>
              {loading ? 'Refreshing…' : 'Refresh'}
            </Button>
          </div>
          {loadError ? (
            <p className="px-6 py-4 text-sm text-red-600">{loadError}</p>
          ) : null}
          <div className="overflow-x-auto">
            <table className="w-full border-collapse text-left text-sm">
              <thead>
                <tr className="bg-gray-100 text-xs font-semibold uppercase text-gray-600">
                  <th className="px-4 py-3" scope="col">
                    ID
                  </th>
                  <th className="px-4 py-3" scope="col">
                    Name
                  </th>
                  <th className="px-4 py-3" scope="col">
                    Description
                  </th>
                  <th className="px-4 py-3" scope="col">
                    Created
                  </th>
                  <th className="px-4 py-3 text-right" scope="col">
                    Orders
                  </th>
                  <th className="px-4 py-3 text-right" scope="col">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100">
                {loading ? (
                  <tr>
                    <td className="px-4 py-8 text-center text-gray-500" colSpan={6}>
                      Loading batches…
                    </td>
                  </tr>
                ) : loadError ? null : batches.length === 0 ? (
                  <tr>
                    <td className="px-4 py-8 text-center text-gray-500" colSpan={6}>
                      No batches yet.
                    </td>
                  </tr>
                ) : (
                  batches.map((b) => (
                      <tr key={b.id} className="hover:bg-gray-50">
                        <td className="px-4 py-3 font-mono text-xs">{b.id}</td>
                        <td className="px-4 py-3 font-medium text-gray-900">{b.name}</td>
                        <td className="max-w-xs truncate px-4 py-3 text-gray-600" title={b.description || ''}>
                          {b.description != null && String(b.description).trim() !== '' ? b.description : '—'}
                        </td>
                        <td className="whitespace-nowrap px-4 py-3 text-gray-600">{formatBatchCreated(b.created_at)}</td>
                        <td className="px-4 py-3 text-right">{batchOrdersTableCell(b)}</td>
                        <td className="px-4 py-3 text-right">
                          <div className="flex justify-end gap-3">
                            <button
                              type="button"
                              className="text-sm font-medium text-blue-700 hover:text-blue-900 disabled:cursor-not-allowed disabled:opacity-50"
                              disabled={deletingId != null || loading}
                              onClick={() => openEditBatch(b)}
                            >
                              Edit
                            </button>
                            <button
                              type="button"
                              className="text-sm font-medium text-red-600 hover:text-red-700 disabled:cursor-not-allowed disabled:opacity-50"
                              disabled={deletingId != null || loading}
                              onClick={() => handleDeleteBatch(b)}
                            >
                              {deletingId === b.id ? 'Deleting…' : 'Delete'}
                            </button>
                          </div>
                        </td>
                      </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>
        </Card>
      </main>
      <Modal open={!!editBatch} onClose={() => !editSaving && closeEditBatch()} title="Edit batch">
        <form className="space-y-4" onSubmit={handleEditBatchSubmit} noValidate>
          {editError ? (
            <p className="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700" role="alert">
              {editError}
            </p>
          ) : null}
          <Input
            id="edit-batch-name"
            name="name"
            label="Name"
            value={editName}
            onChange={(e) => setEditName(e.target.value)}
            disabled={editSaving}
            required
          />
          <Textarea
            id="edit-batch-desc"
            name="description"
            label="Description"
            rows={3}
            value={editDescription}
            onChange={(e) => setEditDescription(e.target.value)}
            disabled={editSaving}
          />
          <div className="flex flex-wrap gap-3 pt-2">
            <Button type="submit" disabled={editSaving}>
              {editSaving ? 'Saving…' : 'Save changes'}
            </Button>
            <Button type="button" variant="secondary" disabled={editSaving} onClick={closeEditBatch}>
              Cancel
            </Button>
          </div>
        </form>
      </Modal>
    </>
  );
}
