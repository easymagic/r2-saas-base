import { cn } from '../../lib/cn.js';

export function Card({ className, children, ...props }) {
  return (
    <div
      className={cn('rounded-2xl bg-white p-6 shadow-md', className)}
      {...props}
    >
      {children}
    </div>
  );
}
