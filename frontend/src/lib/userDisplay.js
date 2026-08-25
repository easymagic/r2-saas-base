function toWalletNumber(amount) {
  if (amount == null || amount === '') return NaN;
  if (typeof amount === 'number') return amount;
  const s = String(amount).replace(/,/g, '').trim();
  return Number(s);
}

export function formatNaira(amount) {
  const n = toWalletNumber(amount);
  if (Number.isNaN(n)) return '—';
  return `₦${n.toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

export function formatDollar(amount) {
  const n = toWalletNumber(amount);
  if (Number.isNaN(n)) return '—';
  return `$${n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

export function initialsFromName(name) {
  if (!name || typeof name !== 'string') return '?';
  const parts = name.trim().split(/\s+/).filter(Boolean);
  if (parts.length === 0) return '?';
  if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
  return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
}
