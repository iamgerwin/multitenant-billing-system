/**
 * Type Guards
 *
 * Runtime type checking functions for validating data structures.
 * Useful for API response validation and type narrowing.
 */

import { InvoiceStatus, UserRole } from '../enums'
import type { User, Invoice, Vendor, Organization } from './index'

// ============================================================================
// Enum Validators
// ============================================================================

/**
 * Check if a string is a valid InvoiceStatus
 */
export function isValidInvoiceStatus(status: unknown): status is InvoiceStatus {
  return (
    typeof status === 'string' &&
    Object.values(InvoiceStatus).includes(status as InvoiceStatus)
  )
}

/**
 * Check if a string is a valid UserRole
 */
export function isValidUserRole(role: unknown): role is UserRole {
  return (
    typeof role === 'string' &&
    Object.values(UserRole).includes(role as UserRole)
  )
}

// ============================================================================
// Entity Type Guards
// ============================================================================

/**
 * Check if an object has the required BaseEntity properties
 */
function hasBaseEntityProperties(obj: unknown): boolean {
  if (typeof obj !== 'object' || obj === null) return false
  const record = obj as Record<string, unknown>
  return typeof record.id === 'number'
}

/**
 * Type guard for User entity
 */
export function isUser(obj: unknown): obj is User {
  if (!hasBaseEntityProperties(obj)) return false

  const record = obj as Record<string, unknown>
  return (
    typeof record.organization_id === 'number' &&
    typeof record.name === 'string' &&
    typeof record.email === 'string' &&
    isValidUserRole(record.role) &&
    typeof record.is_active === 'boolean'
  )
}

/**
 * Type guard for Invoice entity
 */
export function isInvoice(obj: unknown): obj is Invoice {
  if (!hasBaseEntityProperties(obj)) return false

  const record = obj as Record<string, unknown>
  return (
    typeof record.organization_id === 'number' &&
    typeof record.vendor_id === 'number' &&
    typeof record.invoice_number === 'string' &&
    isValidInvoiceStatus(record.status) &&
    typeof record.subtotal === 'number' &&
    typeof record.total_amount === 'number'
  )
}

/**
 * Type guard for Vendor entity
 */
export function isVendor(obj: unknown): obj is Vendor {
  if (!hasBaseEntityProperties(obj)) return false

  const record = obj as Record<string, unknown>
  return (
    typeof record.organization_id === 'number' &&
    typeof record.name === 'string' &&
    typeof record.is_active === 'boolean'
  )
}

/**
 * Type guard for Organization entity
 */
export function isOrganization(obj: unknown): obj is Organization {
  if (!hasBaseEntityProperties(obj)) return false

  const record = obj as Record<string, unknown>
  return (
    typeof record.name === 'string' &&
    typeof record.slug === 'string' &&
    typeof record.currency === 'string' &&
    typeof record.is_active === 'boolean'
  )
}

// ============================================================================
// Array Type Guards
// ============================================================================

/**
 * Type guard for array of Users
 */
export function isUserArray(arr: unknown): arr is User[] {
  return Array.isArray(arr) && arr.every(isUser)
}

/**
 * Type guard for array of Invoices
 */
export function isInvoiceArray(arr: unknown): arr is Invoice[] {
  return Array.isArray(arr) && arr.every(isInvoice)
}

/**
 * Type guard for array of Vendors
 */
export function isVendorArray(arr: unknown): arr is Vendor[] {
  return Array.isArray(arr) && arr.every(isVendor)
}
