/**
 * Frontend TypeScript Types
 *
 * Re-exports all types from @billing/shared package.
 * This file maintains backwards compatibility with existing imports.
 */

// Re-export everything from the shared package
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
} from '@billing/shared'

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
} from '@billing/shared'
