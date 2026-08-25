import { cn } from '../../lib/cn.js';

const variants = {
  primary:
    'bg-blue-900 text-white shadow-md hover:bg-blue-950 focus:ring-blue-500',
  secondary:
    'border border-gray-300 bg-white text-gray-700 shadow-sm hover:bg-gray-50 focus:ring-blue-500',
  danger: 'bg-red-500 text-white shadow-md hover:bg-red-600 focus:ring-red-400',
  success:
    'bg-green-600 text-white shadow-md hover:bg-green-700 focus:ring-green-500',
  orange:
    'bg-orange-500 text-white shadow-md hover:bg-orange-600 focus:ring-orange-400',
  ghost:
    'border border-white/30 bg-white/10 text-white backdrop-blur hover:bg-white/20 focus:ring-white/40',
};

export function Button({
  className,
  variant = 'primary',
  type = 'button',
  as: Comp = 'button',
  ...props
}) {
  return (
    <Comp
      type={Comp === 'button' ? type : undefined}
      className={cn(
        'inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50',
        variant !== 'ghost' && 'focus:ring-offset-2',
        variant === 'ghost' && 'focus:ring-offset-blue-900',
        variants[variant],
        className
      )}
      {...props}
    />
  );
}
