/**
 * Shared Enums
 *
 * These enums are the single source of truth for both frontend and backend.
 * Values must match the PHP enums in backend/app/Enums/
 */

// ============================================================================
// User Role
// ============================================================================

export enum UserRole {
  Admin = 'admin',
  Accountant = 'accountant',
  User = 'user',
}

export const UserRoleLabels: Record<UserRole, string> = {
  [UserRole.Admin]: 'Administrator',
  [UserRole.Accountant]: 'Accountant',
  [UserRole.User]: 'User',
}

export const UserRolePermissions: Record<UserRole, {
  canWrite: boolean
  canApprove: boolean
  canManageUsers: boolean
}> = {
  [UserRole.Admin]: {
    canWrite: true,
    canApprove: true,
    canManageUsers: true,
  },
  [UserRole.Accountant]: {
    canWrite: true,
    canApprove: false,
    canManageUsers: false,
  },
  [UserRole.User]: {
    canWrite: false,
    canApprove: false,
    canManageUsers: false,
  },
}

// ============================================================================
// Invoice Status
// ============================================================================

export enum InvoiceStatus {
  Pending = 'pending',
  Approved = 'approved',
  Rejected = 'rejected',
  Paid = 'paid',
}

export const InvoiceStatusLabels: Record<InvoiceStatus, string> = {
  [InvoiceStatus.Pending]: 'Pending',
  [InvoiceStatus.Approved]: 'Approved',
  [InvoiceStatus.Rejected]: 'Rejected',
  [InvoiceStatus.Paid]: 'Paid',
}

export const InvoiceStatusColors: Record<InvoiceStatus, string> = {
  [InvoiceStatus.Pending]: 'warning',
  [InvoiceStatus.Approved]: 'success',
  [InvoiceStatus.Rejected]: 'danger',
  [InvoiceStatus.Paid]: 'info',
}

/**
 * Allowed status transitions
 * Mirrors the PHP InvoiceStatus::allowedTransitions() method
 */
export const InvoiceStatusTransitions: Record<InvoiceStatus, InvoiceStatus[]> = {
  [InvoiceStatus.Pending]: [InvoiceStatus.Approved, InvoiceStatus.Rejected],
  [InvoiceStatus.Approved]: [InvoiceStatus.Paid],
  [InvoiceStatus.Rejected]: [],
  [InvoiceStatus.Paid]: [],
}

/**
 * Check if a status can transition to another status
 */
export function canTransitionTo(from: InvoiceStatus, to: InvoiceStatus): boolean {
  return InvoiceStatusTransitions[from].includes(to)
}

/**
 * Check if invoice status is final (no more transitions allowed)
 */
export function isFinalStatus(status: InvoiceStatus): boolean {
  return status === InvoiceStatus.Paid
}

/**
 * Check if invoice can be edited based on status
 */
export function canEditInvoice(status: InvoiceStatus): boolean {
  return status === InvoiceStatus.Pending || status === InvoiceStatus.Rejected
}

/**
 * Check if invoice can be deleted based on status
 */
export function canDeleteInvoice(status: InvoiceStatus): boolean {
  return status === InvoiceStatus.Pending || status === InvoiceStatus.Rejected
}
