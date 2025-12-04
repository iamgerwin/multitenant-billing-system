import { defineStore } from 'pinia'
import { useApi } from '~/composables/useApi'
import type {
  Vendor,
  VendorPayload,
  ApiResponse,
  PaginatedResponse,
  ApiErrorResponse,
} from '@billing/shared'

interface VendorState {
  vendors: Vendor[]
  currentVendor: Vendor | null
  pagination: {
    currentPage: number
    lastPage: number
    perPage: number
    total: number
  }
  isLoading: boolean
  error: string | null
}

export const useVendorStore = defineStore('vendor', {
  state: (): VendorState => ({
    vendors: [],
    currentVendor: null,
    pagination: {
      currentPage: 1,
      lastPage: 1,
      perPage: 15,
      total: 0,
    },
    isLoading: false,
    error: null,
  }),

  getters: {
    activeVendors: (state): Vendor[] => state.vendors.filter((v) => v.is_active),
    getVendorById: (state) => (id: number): Vendor | undefined => {
      return state.vendors.find((v) => v.id === id)
    },
  },

  actions: {
    async fetchVendors(page = 1, perPage = 15): Promise<void> {
      this.isLoading = true
      this.error = null

      try {
        const api = useApi()
        const response = await api.get<PaginatedResponse<Vendor>>('/vendors', {
          params: { page, per_page: perPage },
        })

        this.vendors = response.data
        this.pagination = {
          currentPage: response.meta.current_page,
          lastPage: response.meta.last_page,
          perPage: response.meta.per_page,
          total: response.meta.total,
        }
      } catch (err) {
        const error = err as ApiErrorResponse
        this.error = error.message || 'Failed to fetch vendors'
      } finally {
        this.isLoading = false
      }
    },

    async fetchVendor(id: number): Promise<Vendor | null> {
      this.isLoading = true
      this.error = null

      try {
        const api = useApi()
        const response = await api.get<ApiResponse<Vendor>>(`/vendors/${id}`)
        this.currentVendor = response.data
        return response.data
      } catch (err) {
        const error = err as ApiErrorResponse
        this.error = error.message || 'Failed to fetch vendor'
        return null
      } finally {
        this.isLoading = false
      }
    },

    async createVendor(payload: VendorPayload): Promise<Vendor | null> {
      this.isLoading = true
      this.error = null

      try {
        const api = useApi()
        const response = await api.post<ApiResponse<Vendor>>('/vendors', payload)
        this.vendors.unshift(response.data)
        return response.data
      } catch (err) {
        const error = err as ApiErrorResponse
        this.error = error.message || 'Failed to create vendor'
        throw error
      } finally {
        this.isLoading = false
      }
    },

    async updateVendor(id: number, payload: Partial<VendorPayload>): Promise<Vendor | null> {
      this.isLoading = true
      this.error = null

      try {
        const api = useApi()
        const response = await api.put<ApiResponse<Vendor>>(`/vendors/${id}`, payload)

        const index = this.vendors.findIndex((v) => v.id === id)
        if (index !== -1) {
          this.vendors[index] = response.data
        }

        if (this.currentVendor?.id === id) {
          this.currentVendor = response.data
        }

        return response.data
      } catch (err) {
        const error = err as ApiErrorResponse
        this.error = error.message || 'Failed to update vendor'
        throw error
      } finally {
        this.isLoading = false
      }
    },

    async deleteVendor(id: number): Promise<boolean> {
      this.isLoading = true
      this.error = null

      try {
        const api = useApi()
        await api.del(`/vendors/${id}`)

        this.vendors = this.vendors.filter((v) => v.id !== id)

        if (this.currentVendor?.id === id) {
          this.currentVendor = null
        }

        return true
      } catch (err) {
        const error = err as ApiErrorResponse
        this.error = error.message || 'Failed to delete vendor'
        return false
      } finally {
        this.isLoading = false
      }
    },

    clearCurrentVendor() {
      this.currentVendor = null
    },

    clearError() {
      this.error = null
    },
  },
})
