/**
 * Frontend TypeScript Types
 *
 * Note: This is the INITIAL approach where types are duplicated in the frontend.
 * This will be refactored in Phase 17-18 to use a shared package.
 */

// ============================================================================
// Enums
// ============================================================================

/**
 * User roles within the system
 */
export enum UserRole {
  Admin = 'admin',
  Accountant = 'accountant',
  User = 'user',
}

/**
 * Human-readable labels for user roles
 */
export const UserRoleLabels: Record<UserRole, string> = {
  [UserRole.Admin]: 'Administrator',
  [UserRole.Accountant]: 'Accountant',
  [UserRole.User]: 'User',
}

/**
 * Invoice status values
 */
export enum InvoiceStatus {
  Pending = 'pending',
  Approved = 'approved',
  Rejected = 'rejected',
  Paid = 'paid',
}

/**
 * Human-readable labels for invoice statuses
 */
export const InvoiceStatusLabels: Record<InvoiceStatus, string> = {
  [InvoiceStatus.Pending]: 'Pending',
  [InvoiceStatus.Approved]: 'Approved',
  [InvoiceStatus.Rejected]: 'Rejected',
  [InvoiceStatus.Paid]: 'Paid',
}

/**
 * Color codes for invoice statuses (for UI display)
 */
export const InvoiceStatusColors: Record<InvoiceStatus, string> = {
  [InvoiceStatus.Pending]: 'warning',
  [InvoiceStatus.Approved]: 'success',
  [InvoiceStatus.Rejected]: 'danger',
  [InvoiceStatus.Paid]: 'info',
}

// ============================================================================
// Entity Interfaces
// ============================================================================

/**
 * Base interface for entities with timestamps
 */
export interface BaseEntity {
  id: number
  created_at: string | null
  updated_at: string | null
}

/**
 * Organization entity
 */
export interface Organization extends BaseEntity {
  name: string
  slug: string
  email: string | null
  phone: string | null
  address: string | null
  city: string | null
  state: string | null
  postal_code: string | null
  country: string | null
  full_address: string
  tax_id: string | null
  currency: string
  is_active: boolean
  users_count?: number
  vendors_count?: number
  invoices_count?: number
}

/**
 * User entity
 */
export interface User extends BaseEntity {
  organization_id: number
  name: string
  email: string
  role: UserRole
  role_label: string
  is_active: boolean
  email_verified_at: string | null
  organization?: Organization
}

/**
 * Vendor entity
 */
export interface Vendor extends BaseEntity {
  organization_id: number
  name: string
  code: string | null
  email: string | null
  phone: string | null
  address: string | null
  city: string | null
  state: string | null
  postal_code: string | null
  country: string | null
  full_address: string
  tax_id: string | null
  payment_terms: string | null
  notes: string | null
  is_active: boolean
  invoices_count?: number
  total_invoice_amount?: number
  pending_invoices_count?: number
}

/**
 * Invoice entity
 */
export interface Invoice extends BaseEntity {
  organization_id: number
  vendor_id: number
  invoice_number: string
  invoice_date: string | null
  due_date: string | null
  status: InvoiceStatus
  status_label: string
  status_color: string
  subtotal: number
  tax_amount: number
  discount_amount: number
  total_amount: number
  currency: string
  description: string | null
  notes: string | null
  paid_date: string | null
  payment_method: string | null
  payment_reference: string | null
  is_overdue: boolean
  can_edit: boolean
  can_delete: boolean
  allowed_transitions: InvoiceStatus[]
  created_by: number | null
  approved_by: number | null
  approved_at: string | null
  vendor?: Vendor
  creator?: User
  approver?: User
}

// ============================================================================
// API Response Types
// ============================================================================

/**
 * Standard API response wrapper
 */
export interface ApiResponse<T> {
  data: T
  message?: string
}

/**
 * Paginated API response
 */
export interface PaginatedResponse<T> {
  data: T[]
  links: {
    first: string | null
    last: string | null
    prev: string | null
    next: string | null
  }
  meta: {
    current_page: number
    from: number | null
    last_page: number
    path: string
    per_page: number
    to: number | null
    total: number
    links: Array<{
      url: string | null
      label: string
      active: boolean
    }>
  }
}

/**
 * API error response
 */
export interface ApiErrorResponse {
  message: string
  errors?: Record<string, string[]>
}

/**
 * Authentication response
 */
export interface AuthResponse {
  user: User
  token: string
  token_type: string
  expires_at: string
}

/**
 * Login credentials
 */
export interface LoginCredentials {
  email: string
  password: string
}

// ============================================================================
// Form/Input Types
// ============================================================================

/**
 * Vendor creation/update payload
 */
export interface VendorPayload {
  name: string
  code?: string | null
  email?: string | null
  phone?: string | null
  address?: string | null
  city?: string | null
  state?: string | null
  postal_code?: string | null
  country?: string | null
  tax_id?: string | null
  payment_terms?: string | null
  notes?: string | null
  is_active?: boolean
}

/**
 * Invoice creation payload
 */
export interface InvoiceCreatePayload {
  vendor_id: number
  invoice_number: string
  invoice_date: string
  due_date?: string | null
  subtotal: number
  tax_amount?: number
  discount_amount?: number
  currency?: string
  description?: string | null
  notes?: string | null
}

/**
 * Invoice update payload
 */
export interface InvoiceUpdatePayload {
  vendor_id?: number
  invoice_number?: string
  invoice_date?: string
  due_date?: string | null
  subtotal?: number
  tax_amount?: number
  discount_amount?: number
  currency?: string
  description?: string | null
  notes?: string | null
}

/**
 * Invoice status update payload
 */
export interface InvoiceStatusPayload {
  status: InvoiceStatus
  payment_method?: string | null
  payment_reference?: string | null
}
