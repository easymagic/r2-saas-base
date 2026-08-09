import { defineConfig, loadEnv } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), 'VITE_');
  const hasBasePrefix = Object.prototype.hasOwnProperty.call(env, 'VITE_BASE_PREFIX');
  const segment = (env.VITE_BASE_PREFIX || '').trim().replace(/^\/+|\/+$/g, '');
  const outDir = env.VITE_BUILD_OUT_DIR || (mode === 'development' ? 'dist' : '../');
  const buildsToRepoRoot = outDir === '../' || outDir === '..';
  const defaultBase = buildsToRepoRoot ? '/' : '/demo/';
  const base = hasBasePrefix
    ? segment === ''
      ? '/'
      : `/${segment}/`
    : defaultBase;
  const disableHmr = env.VITE_DISABLE_HMR === '1';
  const hmrClientPort = env.VITE_HMR_CLIENT_PORT
    ? Number(env.VITE_HMR_CLIENT_PORT)
    : undefined;

  return {
    base,
    envPrefix: ['VITE_', 'API_', 'X_'],
    plugins: [react()],
    build: {
      outDir,
    },
    server: {
      host: '0.0.0.0',
      port: 5173,
      strictPort: true,
      watch: {
        usePolling: env.VITE_USE_POLLING === '1',
      },
      hmr: disableHmr
        ? false
        : hmrClientPort
          ? { clientPort: hmrClientPort }
          : true,
    },
  };
});
