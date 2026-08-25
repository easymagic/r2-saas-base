/**
 * Order count when the API includes `batch.orders`; `null` if omitted (list batches often has no `orders`).
 */
export function batchOrdersCountIfPresent(batch) {
  if (!batch || !Array.isArray(batch.orders)) return null;
  return batch.orders.length;
}

/** Dropdown label: name only, or "name (N orders)" when counts are present. */
export function batchSelectOptionLabel(batch) {
  const name = batch?.name != null ? String(batch.name) : '';
  const n = batchOrdersCountIfPresent(batch);
  if (n == null) return name;
  return `${name} (${n} ${n === 1 ? 'order' : 'orders'})`;
}

/** Table cell: em dash when counts are not in the payload. */
export function batchOrdersTableCell(batch) {
  const n = batchOrdersCountIfPresent(batch);
  if (n == null) return '—';
  return n;
}
