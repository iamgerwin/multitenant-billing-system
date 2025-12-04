import { resolve } from 'path'

// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',

  devtools: { enabled: true },

  modules: [
    '@nuxtjs/tailwindcss',
    '@pinia/nuxt',
  ],

  app: {
    head: {
      title: 'Billing System',
      titleTemplate: '%s | Billing System',
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        { name: 'description', content: 'Multi-tenant billing and invoice management system' },
      ],
      link: [
        { rel: 'icon', type: 'image/svg+xml', href: '/favicon.svg' },
        { rel: 'icon', type: 'image/x-icon', href: '/favicon.ico' },
      ],
      // Critical CSS to prevent FOUC - hides content until CSS loads
      style: [
        {
          children: `
            .app-content { opacity: 0; visibility: hidden; }
            .app-content.app-ready { opacity: 1; visibility: visible; transition: opacity 0.2s ease; }
            .app-wrapper { min-height: 100vh; background-color: #f9fafb; }
            .loading-overlay { position: fixed; inset: 0; z-index: 9999; display: flex; align-items: center; justify-content: center; background-color: #f9fafb; }
            .loading-spinner { display: flex; flex-direction: column; align-items: center; gap: 1rem; }
            .loading-icon-wrapper { width: 3rem; height: 3rem; }
            .loading-icon { width: 100%; height: 100%; color: #6366f1; animation: spin 1s linear infinite; }
            .loading-circle { stroke-dasharray: 60; stroke-dashoffset: 45; stroke-linecap: round; }
            .loading-text { font-size: 0.875rem; font-weight: 500; color: #6b7280; }
            @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
          `,
        },
      ],
    },
  },

  runtimeConfig: {
    public: {
      apiBaseUrl: process.env.NUXT_PUBLIC_API_BASE_URL || 'http://localhost:8000/api',
    },
  },

  typescript: {
    strict: true,
    // Disable type checking in dev mode for faster startup (run separately with `npm run typecheck`)
    typeCheck: process.env.NODE_ENV === 'production',
  },

  tailwindcss: {
    cssPath: '~/assets/css/tailwind.css',
    configPath: 'tailwind.config.ts',
  },

  pinia: {
    storesDirs: ['./stores'],
  },

  imports: {
    dirs: ['stores'],
  },

  // Vite aliases for shared package resolution
  // Use /shared in Docker, ../shared for local dev
  alias: {
    '@billing/shared': process.env.DOCKER_ENV ? '/shared' : resolve(__dirname, '../shared'),
  },

  // Ensure Vite can resolve the shared package
  vite: {
    resolve: {
      alias: {
        '@billing/shared': process.env.DOCKER_ENV ? '/shared' : resolve(__dirname, '../shared'),
      },
    },
    optimizeDeps: {
      include: ['@billing/shared'],
    },
  },
})
