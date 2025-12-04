import { defineStore } from 'pinia'
import { useApi } from '~/composables/useApi'
import type { DashboardStats, ApiResponse, ApiErrorResponse } from '@billing/shared'

interface StatsState {
  stats: DashboardStats | null
  isLoading: boolean
  error: string | null
  lastFetched: number | null
}

// Cache duration in milliseconds (2 minutes, matching backend)
const CACHE_DURATION = 2 * 60 * 1000

export const useStatsStore = defineStore('stats', {
  state: (): StatsState => ({
    stats: null,
    isLoading: false,
    error: null,
    lastFetched: null,
  }),

  getters: {
    // Invoice getters
    totalInvoiceCount: (state): number => state.stats?.invoices.total_count ?? 0,
    totalInvoiceAmount: (state): number => state.stats?.invoices.total_amount ?? 0,
    pendingInvoiceCount: (state): number => state.stats?.invoices.pending_count ?? 0,
    approvedInvoiceCount: (state): number => state.stats?.invoices.approved_count ?? 0,
    paidInvoiceCount: (state): number => state.stats?.invoices.paid_count ?? 0,
    rejectedInvoiceCount: (state): number => state.stats?.invoices.rejected_count ?? 0,
    paidInvoiceAmount: (state): number => state.stats?.invoices.paid_amount ?? 0,

    // Vendor getters
    totalVendorCount: (state): number => state.stats?.vendors.total_count ?? 0,
    activeVendorCount: (state): number => state.stats?.vendors.active_count ?? 0,

    // Cache status
    isCacheValid: (state): boolean => {
      if (!state.lastFetched) return false
      return Date.now() - state.lastFetched < CACHE_DURATION
    },
  },

  actions: {
    async fetchStats(force = false): Promise<DashboardStats | null> {
      // Return cached stats if still valid and not forced
      if (!force && this.isCacheValid && this.stats) {
        return this.stats
      }

      this.isLoading = true
      this.error = null

      try {
        const api = useApi()
        const response = await api.get<ApiResponse<DashboardStats>>('/dashboard/stats')
        this.stats = response.data
        this.lastFetched = Date.now()
        return response.data
      } catch (err) {
        const error = err as ApiErrorResponse
        this.error = error.message || 'Failed to fetch stats'
        return null
      } finally {
        this.isLoading = false
      }
    },

    invalidateCache() {
      this.lastFetched = null
    },

    clearError() {
      this.error = null
    },
  },
})
