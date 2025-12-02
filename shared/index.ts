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
