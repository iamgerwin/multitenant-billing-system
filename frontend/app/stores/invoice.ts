import { defineStore } from 'pinia'
import { useApi } from '~/composables/useApi'
import {
  InvoiceStatus,
  type Invoice,
  type InvoiceCreatePayload,
  type InvoiceUpdatePayload,
  type InvoiceStatusPayload,
  type ApiResponse,
  type PaginatedResponse,
  type ApiErrorResponse,
} from '@billing/shared'

interface InvoiceFilters {
  status?: InvoiceStatus
  vendor_id?: number
  search?: string
  sort?: string // e.g., '-created_at', 'invoice_date', '-total_amount'
}

interface InvoiceState {
  invoices: Invoice[]
  currentInvoice: Invoice | null
  filters: InvoiceFilters
  pagination: {
    currentPage: number
    lastPage: number
    perPage: number
    total: number
  }
  isLoading: boolean
  error: string | null
}

export const useInvoiceStore = defineStore('invoice', {
  state: (): InvoiceState => ({
    invoices: [],
    currentInvoice: null,
    filters: {},
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
    pendingInvoices: (state): Invoice[] =>
      state.invoices.filter((i) => i.status === InvoiceStatus.Pending),
    approvedInvoices: (state): Invoice[] =>
      state.invoices.filter((i) => i.status === InvoiceStatus.Approved),
    overdueInvoices: (state): Invoice[] =>
      state.invoices.filter((i) => i.is_overdue),
    getInvoiceById: (state) => (id: number): Invoice | undefined => {
      return state.invoices.find((i) => i.id === id)
    },
    totalAmount: (state): number =>
      state.invoices.reduce((sum, i) => sum + i.total_amount, 0),
  },

  actions: {
    async fetchInvoices(page = 1, perPage = 15): Promise<void> {
      this.isLoading = true
      this.error = null

      try {
        const api = useApi()
        const params: Record<string, string | number | boolean | undefined> = {
          page,
          per_page: perPage,
          ...this.filters,
        }

        const response = await api.get<PaginatedResponse<Invoice>>('/invoices', { params })

        this.invoices = response.data
        this.pagination = {
          currentPage: response.meta.current_page,
          lastPage: response.meta.last_page,
          perPage: response.meta.per_page,
          total: response.meta.total,
        }
      } catch (err) {
        const error = err as ApiErrorResponse
        this.error = error.message || 'Failed to fetch invoices'
      } finally {
        this.isLoading = false
      }
    },

    async fetchInvoice(id: number): Promise<Invoice | null> {
      this.isLoading = true
      this.error = null

      try {
        const api = useApi()
        const response = await api.get<ApiResponse<Invoice>>(`/invoices/${id}`)
        this.currentInvoice = response.data
        return response.data
      } catch (err) {
        const error = err as ApiErrorResponse
        this.error = error.message || 'Failed to fetch invoice'
        return null
      } finally {
        this.isLoading = false
      }
    },

    async createInvoice(payload: InvoiceCreatePayload): Promise<Invoice | null> {
      this.isLoading = true
      this.error = null

      try {
        const api = useApi()
        const response = await api.post<ApiResponse<Invoice>>('/invoices', payload)
        this.invoices.unshift(response.data)
        return response.data
      } catch (err) {
        const error = err as ApiErrorResponse
        this.error = error.message || 'Failed to create invoice'
        throw error
      } finally {
        this.isLoading = false
      }
    },

    async updateInvoice(id: number, payload: InvoiceUpdatePayload): Promise<Invoice | null> {
      this.isLoading = true
      this.error = null

      try {
        const api = useApi()
        const response = await api.put<ApiResponse<Invoice>>(`/invoices/${id}`, payload)

        const index = this.invoices.findIndex((i) => i.id === id)
        if (index !== -1) {
          this.invoices[index] = response.data
        }

        if (this.currentInvoice?.id === id) {
          this.currentInvoice = response.data
        }

        return response.data
      } catch (err) {
        const error = err as ApiErrorResponse
        this.error = error.message || 'Failed to update invoice'
        throw error
      } finally {
        this.isLoading = false
      }
    },

    async updateInvoiceStatus(id: number, payload: InvoiceStatusPayload): Promise<Invoice | null> {
      this.isLoading = true
      this.error = null

      try {
        const api = useApi()
        const response = await api.patch<ApiResponse<Invoice>>(`/invoices/${id}/status`, payload)

        const index = this.invoices.findIndex((i) => i.id === id)
        if (index !== -1) {
          this.invoices[index] = response.data
        }

        if (this.currentInvoice?.id === id) {
          this.currentInvoice = response.data
        }

        return response.data
      } catch (err) {
        const error = err as ApiErrorResponse
        this.error = error.message || 'Failed to update invoice status'
        throw error
      } finally {
        this.isLoading = false
      }
    },

    async deleteInvoice(id: number): Promise<boolean> {
      this.isLoading = true
      this.error = null

      try {
        const api = useApi()
        await api.del(`/invoices/${id}`)

        this.invoices = this.invoices.filter((i) => i.id !== id)

        if (this.currentInvoice?.id === id) {
          this.currentInvoice = null
        }

        return true
      } catch (err) {
        const error = err as ApiErrorResponse
        this.error = error.message || 'Failed to delete invoice'
        return false
      } finally {
        this.isLoading = false
      }
    },

    setFilters(filters: InvoiceFilters) {
      this.filters = filters
    },

    clearFilters() {
      this.filters = {}
    },

    clearCurrentInvoice() {
      this.currentInvoice = null
    },

    clearError() {
      this.error = null
    },

    async generateInvoiceNumber(): Promise<string | null> {
      try {
        const api = useApi()
        const response = await api.get<{ invoice_number: string }>('/invoices/generate-number')
        return response.invoice_number
      } catch (err) {
        const error = err as ApiErrorResponse
        this.error = error.message || 'Failed to generate invoice number'
        return null
      }
    },
  },
})
