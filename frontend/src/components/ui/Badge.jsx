import { cn } from '../../lib/cn.js';

const styles = {
  pending: 'bg-yellow-100 text-yellow-700',
  approved: 'bg-green-100 text-green-700',
  rejected: 'bg-red-100 text-red-700',
  delivered: 'bg-green-100 text-green-700',
  default: 'bg-gray-100 text-gray-700',
};

export function Badge({ variant = 'default', className, children }) {
  return (
    <span
      className={cn(
        'inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium',
        styles[variant] || styles.default,
        className
      )}
    >
      {children}
    </span>
  );
}
