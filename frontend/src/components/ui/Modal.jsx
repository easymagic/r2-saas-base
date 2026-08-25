import { useEffect, useRef } from 'react';
import { cn } from '../../lib/cn.js';
import { Button } from './Button.jsx';

export function Modal({ open, onClose, title, children, className }) {
  const ref = useRef(null);

  useEffect(() => {
    if (!open) return;
    const prev = document.body.style.overflow;
    document.body.style.overflow = 'hidden';
    ref.current?.focus?.();
    return () => {
      document.body.style.overflow = prev;
    };
  }, [open]);

  if (!open) return null;

  return (
    <div className="fixed inset-0 z-[80] flex items-end justify-center p-4 sm:items-center" role="presentation">
      <button
        type="button"
        className="absolute inset-0 bg-blue-900/40 backdrop-blur-sm"
        aria-label="Close overlay"
        onClick={onClose}
      />
      <div
        ref={ref}
        role="dialog"
        aria-modal="true"
        aria-labelledby={title ? 'modal-title' : undefined}
        tabIndex={-1}
        className={cn(
          'relative z-10 w-full max-w-lg rounded-2xl border border-gray-200 bg-white shadow-2xl',
          className
        )}
      >
        {title && (
          <div className="flex items-center justify-between border-b border-gray-100 px-6 py-4">
            <h2 id="modal-title" className="text-lg font-semibold text-gray-900">
              {title}
            </h2>
            <Button variant="secondary" type="button" className="px-2 py-1 text-gray-500" onClick={onClose}>
              ✕
            </Button>
          </div>
        )}
        <div className={title ? 'px-6 py-4' : 'p-6'}>{children}</div>
      </div>
    </div>
  );
}
