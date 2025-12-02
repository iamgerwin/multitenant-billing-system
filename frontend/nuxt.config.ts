import { resolve } from 'path'

// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',

  devtools: { enabled: true },

  modules: [
    '@nuxtjs/tailwindcss',
    '@pinia/nuxt',
  ],

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
  alias: {
    '@billing/shared': resolve(__dirname, '../shared'),
  },

  // Ensure Vite can resolve the shared package
  vite: {
    resolve: {
      alias: {
        '@billing/shared': resolve(__dirname, '../shared'),
      },
    },
    optimizeDeps: {
      include: ['@billing/shared'],
    },
  },
})
