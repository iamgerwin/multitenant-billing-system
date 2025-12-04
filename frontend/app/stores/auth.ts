import { defineStore } from 'pinia'
import { useApi } from '~/composables/useApi'
import { useInvoiceStore } from './invoice'
import { useVendorStore } from './vendor'
import { useStatsStore } from './stats'
import {
  UserRole,
  UserRolePermissions,
  type User,
  type AuthResponse,
  type LoginCredentials,
  type ApiErrorResponse,
} from '@billing/shared'

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
    userRole: (state): UserRole | null => state.user?.role ?? null,
    isAdmin: (state): boolean => state.user?.role === UserRole.Admin,
    isAccountant: (state): boolean => state.user?.role === UserRole.Accountant,
    canWrite: (state): boolean => {
      const role = state.user?.role
      return role ? UserRolePermissions[role].canWrite : false
    },
    canApprove: (state): boolean => {
      const role = state.user?.role
      return role ? UserRolePermissions[role].canApprove : false
    },
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
        this.clearAllStores()
        navigateTo('/login')
      }
    },

    clearAllStores() {
      // Reset all stores to their initial state on logout
      // Critical: This ensures tenant isolation - stats from one org
      // are never visible to users from another org
      const invoiceStore = useInvoiceStore()
      const vendorStore = useVendorStore()
      const statsStore = useStatsStore()

      invoiceStore.$reset()
      vendorStore.$reset()
      statsStore.$reset()
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
