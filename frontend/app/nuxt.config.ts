// https://nuxt.com/docs/api/configuration/nuxt-config

import tailwindcss from "@tailwindcss/vite";

export default defineNuxtConfig({
  compatibilityDate: '2024-11-01',
  devtools: { enabled: true },
  modules: ['@nuxt/ui', '@pinia/nuxt', 'nuxt-auth-sanctum'],
  css: ['~/assets/css/main.css'],
  vite: {
    plugins: [
      tailwindcss(),
    ],
  },
  runtimeConfig: {
    public: {
      sanctum: {
        mode: 'cookie',
        redirectIfAuthenticated: true,
        redirectIfUnauthenticated: true,
        endpoints: {
          csrf: '/sanctum/csrf-cookie',
          login: '/api/login',
          logout: '/api/logout',
          user: '/api/user',
        },
        redirect: {
          onLogin: '/positions',
          onLogout: '/login',
        },
        globalMiddleware: {
          enabled: true,
        },
        baseUrl: 'https://api-pea.dev-fullstack.net'
      }

    }
  },
});