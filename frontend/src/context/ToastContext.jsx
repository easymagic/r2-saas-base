import React, { createContext, useCallback, useContext, useState } from 'react';
import { cn } from '../lib/cn.js';

const ToastContext = createContext(null);

export function ToastProvider({ children }) {
  const [toasts, setToasts] = useState([]);

  const dismiss = useCallback((id) => {
    setToasts((t) => t.filter((x) => x.id !== id));
  }, []);

  const showToast = useCallback((message, variant = 'success') => {
    const id = crypto.randomUUID();
    setToasts((t) => [...t, { id, message, variant }]);
    window.setTimeout(() => dismiss(id), 4800);
  }, [dismiss]);

  return (
    <ToastContext.Provider value={{ showToast }}>
      {children}
      <div
        className="pointer-events-none fixed bottom-4 right-4 z-[100] flex w-full max-w-sm flex-col gap-2 p-4 sm:bottom-6 sm:right-6"
        aria-live="polite"
      >
        {toasts.map((t) => (
          <div
            key={t.id}
            className={cn(
              'pointer-events-auto rounded-2xl border p-4 shadow-lg',
              t.variant === 'error' && 'border-red-200 bg-white',
              t.variant === 'success' && 'border-green-200 bg-white',
              t.variant === 'info' && 'border-gray-200 bg-white'
            )}
          >
            <p className="text-sm font-semibold text-gray-900">{t.message}</p>
          </div>
        ))}
      </div>
    </ToastContext.Provider>
  );
}

export function useToast() {
  const ctx = useContext(ToastContext);
  if (!ctx) throw new Error('useToast must be used within ToastProvider');
  return ctx;
}
