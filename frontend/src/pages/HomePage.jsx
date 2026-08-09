import { Link } from 'react-router-dom';
import { Button } from '../components/ui/Button.jsx';
import { getStoredUser } from '../lib/authSession.js';

const featureCards = [
  {
    title: 'Fast requests',
    body: 'Submit product links with screenshots and get fulfillment moving quickly.',
    icon: (
      <svg className="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path d="M13 2 4 14h7l-1 8 10-13h-7l1-7Z" fill="currentColor" />
      </svg>
    ),
  },
  {
    title: 'Secure wallet',
    body: 'Top up online or manually, then pay orders from your balance.',
    icon: (
      <svg className="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path
          d="M6 10V8a6 6 0 1 1 12 0v2h1a1 1 0 0 1 1 1v9a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-9a1 1 0 0 1 1-1h1Zm2 0h8V8a4 4 0 0 0-8 0v2Z"
          fill="currentColor"
        />
      </svg>
    ),
  },
  {
    title: 'Trackable orders',
    body: 'Follow pending → paid → placed → delivered with thread updates.',
    icon: (
      <svg className="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path
          d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7Zm0 9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5.5Z"
          fill="currentColor"
        />
      </svg>
    ),
  },
];

const steps = [
  'Create an account and verify your email.',
  'Paste the product link, USD amount, and screenshots.',
  'Fund your wallet and pay when the order is ready.',
];

export function HomePage() {
  const user = getStoredUser();
  const startTo = user?.token ? '/orders' : '/register';
  const signInTo = user?.token ? '/orders' : '/login';

  return (
    <main className="min-h-screen bg-slate-100 text-gray-900">
      <header className="home-hero sticky top-0 z-30 border-b border-white/10 shadow-lg shadow-slate-900/20">
        <div className="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
          <Link to="/" className="flex min-w-0 items-center gap-3 text-white" aria-label="BorderlessFetch home">
            <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-sm font-bold text-blue-900">
              BF
            </span>
            <span className="truncate text-xl font-semibold tracking-tight">BorderlessFetch</span>
          </Link>
          <nav className="hidden items-center gap-8 text-sm font-semibold text-white/90 md:flex" aria-label="Homepage">
            <a href="#features" className="transition hover:text-white">
              Features
            </a>
            <a href="#how-it-works" className="transition hover:text-white">
              How it works
            </a>
            <Link to={signInTo} className="transition hover:text-white">
              {user?.token ? 'Orders' : 'Sign in'}
            </Link>
          </nav>
          <Button as={Link} to={startTo} variant="ghost" className="bg-white text-blue-900 hover:bg-orange-50">
            Get started
          </Button>
        </div>
      </header>

      <section className="home-hero relative overflow-hidden">
        <div
          className="pointer-events-none absolute inset-0 opacity-30"
          style={{
            backgroundImage:
              'linear-gradient(rgba(255,255,255,0.06) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.06) 1px, transparent 1px)',
            backgroundSize: '48px 48px',
          }}
          aria-hidden="true"
        />
        <div className="relative mx-auto flex min-h-[calc(100vh-4rem)] max-w-7xl flex-col justify-center px-4 py-20 sm:px-6 lg:px-8">
          <div className="bf-animate-in mx-auto max-w-4xl text-center text-white">
            <p className="text-sm font-semibold uppercase tracking-[0.2em] text-orange-200">BorderlessFetch</p>
            <h1 className="mt-5 text-4xl font-bold tracking-tight sm:text-6xl lg:text-7xl">
              Fetch products globally.
              <span className="block text-orange-300">Deliver locally.</span>
            </h1>
            <p className="bf-animate-in-delay mx-auto mt-6 max-w-2xl text-lg leading-8 text-slate-200 sm:text-xl">
              Turn store links into snappy orders, fund your wallet, and follow every update from one clear workspace.
            </p>
            <div className="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
              <Button as={Link} to={startTo} variant="orange" className="min-h-12 min-w-48">
                Get started free
              </Button>
              <Button
                as="a"
                href="#how-it-works"
                variant="ghost"
                className="min-h-12 min-w-44 border-white/40 bg-white/10 text-white hover:bg-white/20"
              >
                Learn more
              </Button>
            </div>
          </div>
        </div>
      </section>

      <section
        id="features"
        className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8"
        aria-labelledby="features-heading"
      >
        <h2 id="features-heading" className="text-2xl font-semibold tracking-tight text-slate-900">
          Built for fulfillment
        </h2>
        <p className="mt-2 max-w-2xl text-sm text-slate-600">
          One clear path from product request to wallet payment and delivery updates.
        </p>
        <div className="mt-8 grid gap-6 lg:grid-cols-3">
          {featureCards.map((item) => (
            <article
              key={item.title}
              className="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-md shadow-slate-900/5 sm:p-7"
            >
              <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-900 text-orange-300">
                {item.icon}
              </div>
              <h3 className="mt-6 text-lg font-semibold text-gray-950">{item.title}</h3>
              <p className="mt-3 text-sm leading-6 text-gray-600">{item.body}</p>
            </article>
          ))}
        </div>
      </section>

      <section
        id="how-it-works"
        className="border-t border-slate-200/80 bg-white/70 py-16"
        aria-labelledby="how-heading"
      >
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <h2 id="how-heading" className="text-2xl font-semibold tracking-tight text-slate-900">
            How it works
          </h2>
          <div className="mt-8 grid gap-4 lg:grid-cols-3">
            {steps.map((step, index) => (
              <div
                key={step}
                className="flex gap-4 rounded-2xl border border-slate-200/80 bg-slate-50/80 p-5"
              >
                <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-900 text-sm font-bold text-white">
                  {index + 1}
                </span>
                <p className="pt-1 text-sm font-medium leading-6 text-gray-700">{step}</p>
              </div>
            ))}
          </div>
        </div>
      </section>
    </main>
  );
}
