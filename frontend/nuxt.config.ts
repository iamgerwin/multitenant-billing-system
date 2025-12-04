import { resolve } from 'path'

// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',

  devtools: { enabled: false },

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
