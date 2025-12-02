/**
 * @billing/shared
 *
 * Shared types and enums for the multitenant billing system.
 * This package is the single source of truth for data structures
 * used by both frontend and backend.
 */

// Export all enums and enum utilities
export {
  // Enums
  UserRole,
  InvoiceStatus,
  // Labels
  UserRoleLabels,
  InvoiceStatusLabels,
  InvoiceStatusColors,
  // Permissions and transitions
  UserRolePermissions,
  InvoiceStatusTransitions,
  // Utility functions
  canTransitionTo,
  isFinalStatus,
  canEditInvoice,
  canDeleteInvoice,
} from './enums'

// Export all types
export type {
  // Base types
  BaseEntity,
  // Entity types
  Organization,
  User,
  Vendor,
  Invoice,
  // API types
  ApiResponse,
  PaginatedResponse,
  ApiErrorResponse,
  AuthResponse,
  LoginCredentials,
  // Form types
  VendorPayload,
  InvoiceCreatePayload,
  InvoiceUpdatePayload,
  InvoiceStatusPayload,
} from './types'

// Export type guards
export {
  // Enum validators
  isValidInvoiceStatus,
  isValidUserRole,
  // Entity type guards
  isUser,
  isInvoice,
  isVendor,
  isOrganization,
  // Array type guards
  isUserArray,
  isInvoiceArray,
  isVendorArray,
} from './types/guards'

// Export utility functions
export {
  // Invoice status utilities
  getNextStatuses,
  isValidTransition,
  // Currency formatting
  formatCurrency,
  formatCompactCurrency,
  // Date formatting
  formatDate,
  formatRelativeDate,
  isDatePast,
  isDateToday,
  // Number formatting
  formatNumber,
  formatPercentage,
} from './utils'

// Export utility types
export type {
  CurrencyFormatOptions,
  DateFormatOptions,
} from './utils'
