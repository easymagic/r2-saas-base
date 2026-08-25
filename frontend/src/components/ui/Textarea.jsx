import { cn } from '../../lib/cn.js';

export function Textarea({ className, id, label, ...props }) {
  const ta = (
    <textarea
      id={id}
      className={cn(
        'w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500',
        className
      )}
      {...props}
    />
  );
  if (!label) return ta;
  return (
    <div>
      <label htmlFor={id} className="block text-sm font-medium text-gray-700">
        {label}
      </label>
      <div className="mt-1">{ta}</div>
    </div>
  );
}
