import { defineStore } from 'pinia'
import type { User, AuthResponse, LoginCredentials, ApiErrorResponse } from '~/types'

interface AuthState {
  user: User | null
  token: string | null
  isLoading: boolean
  error: string | null
}

export const useAuthStore = defineStore('auth', {
  state: (): AuthState => ({
    user: null,
    token: null,
    isLoading: false,
    error: null,
  }),

  getters: {
    isAuthenticated: (state): boolean => !!state.token && !!state.user,
    currentUser: (state): User | null => state.user,
    userRole: (state): string | null => state.user?.role ?? null,
    isAdmin: (state): boolean => state.user?.role === 'admin',
    isAccountant: (state): boolean => state.user?.role === 'accountant',
    canWrite: (state): boolean => {
      const role = state.user?.role
      return role === 'admin' || role === 'accountant'
    },
    canApprove: (state): boolean => state.user?.role === 'admin',
  },

  actions: {
    initialize() {
      if (import.meta.client) {
        const token = localStorage.getItem('auth_token')
        const userJson = localStorage.getItem('auth_user')

        if (token && userJson) {
          this.token = token
          try {
            this.user = JSON.parse(userJson)
          } catch {
            this.logout()
          }
        }
      }
    },

    async login(credentials: LoginCredentials): Promise<boolean> {
      this.isLoading = true
      this.error = null

      try {
        const api = useApi()
        const response = await api.post<AuthResponse>('/auth/login', credentials)

        this.token = response.token
        this.user = response.user

        if (import.meta.client) {
          localStorage.setItem('auth_token', response.token)
          localStorage.setItem('auth_user', JSON.stringify(response.user))
        }

        return true
      } catch (err) {
        const error = err as ApiErrorResponse
        this.error = error.message || 'Login failed'
        return false
      } finally {
        this.isLoading = false
      }
    },

    async logout(): Promise<void> {
      try {
        if (this.token) {
          const api = useApi()
          await api.post('/auth/logout')
        }
      } catch {
        // Ignore logout errors
      } finally {
        this.clearAuth()
        navigateTo('/login')
      }
    },

    clearAuth() {
      this.user = null
      this.token = null
      this.error = null

      if (import.meta.client) {
        localStorage.removeItem('auth_token')
        localStorage.removeItem('auth_user')
      }
    },

    async fetchUser(): Promise<void> {
      if (!this.token) return

      this.isLoading = true

      try {
        const api = useApi()
        const response = await api.get<{ data: User }>('/auth/user')
        this.user = response.data

        if (import.meta.client) {
          localStorage.setItem('auth_user', JSON.stringify(this.user))
        }
      } catch {
        this.clearAuth()
      } finally {
        this.isLoading = false
      }
    },
  },
})
