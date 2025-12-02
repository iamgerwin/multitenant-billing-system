/**
 * Shared Utility Functions
 *
 * Helper functions for common operations across frontend and backend.
 */

import { InvoiceStatus, InvoiceStatusTransitions } from '../enums'

// ============================================================================
// Invoice Status Utilities
// ============================================================================

/**
 * Get the allowed next statuses for a given invoice status
 */
export function getNextStatuses(currentStatus: InvoiceStatus): InvoiceStatus[] {
  return InvoiceStatusTransitions[currentStatus] || []
}

/**
 * Check if a status transition is valid
 */
export function isValidTransition(from: InvoiceStatus, to: InvoiceStatus): boolean {
  return getNextStatuses(from).includes(to)
}

// ============================================================================
// Currency Formatting
// ============================================================================

export interface CurrencyFormatOptions {
  currency?: string
  locale?: string
  minimumFractionDigits?: number
  maximumFractionDigits?: number
}

/**
 * Format a number as currency
 */
export function formatCurrency(
  amount: number,
  options: CurrencyFormatOptions = {}
): string {
  const {
    currency = 'USD',
    locale = 'en-US',
    minimumFractionDigits = 2,
    maximumFractionDigits = 2,
  } = options

  return new Intl.NumberFormat(locale, {
    style: 'currency',
    currency,
    minimumFractionDigits,
    maximumFractionDigits,
  }).format(amount)
}

/**
 * Format a number as a compact currency (e.g., $1.2K, $3.4M)
 */
export function formatCompactCurrency(
  amount: number,
  options: Omit<CurrencyFormatOptions, 'minimumFractionDigits' | 'maximumFractionDigits'> = {}
): string {
  const { currency = 'USD', locale = 'en-US' } = options

  return new Intl.NumberFormat(locale, {
    style: 'currency',
    currency,
    notation: 'compact',
    maximumFractionDigits: 1,
  }).format(amount)
}

// ============================================================================
// Date Formatting
// ============================================================================

export interface DateFormatOptions {
  locale?: string
  format?: 'short' | 'medium' | 'long' | 'full'
  includeTime?: boolean
}

/**
 * Format a date string for display
 */
export function formatDate(
  dateString: string | null | undefined,
  options: DateFormatOptions = {}
): string {
  if (!dateString) return '-'

  const { locale = 'en-US', format = 'medium', includeTime = false } = options

  try {
    const date = new Date(dateString)

    if (isNaN(date.getTime())) return '-'

    const dateOptions: Intl.DateTimeFormatOptions = {
      dateStyle: format,
    }

    if (includeTime) {
      dateOptions.timeStyle = 'short'
    }

    return new Intl.DateTimeFormat(locale, dateOptions).format(date)
  } catch {
    return '-'
  }
}

/**
 * Format a date as relative time (e.g., "2 days ago", "in 3 hours")
 */
export function formatRelativeDate(
  dateString: string | null | undefined,
  locale = 'en-US'
): string {
  if (!dateString) return '-'

  try {
    const date = new Date(dateString)
    if (isNaN(date.getTime())) return '-'

    const now = new Date()
    const diffInSeconds = Math.floor((date.getTime() - now.getTime()) / 1000)
    const diffInMinutes = Math.floor(diffInSeconds / 60)
    const diffInHours = Math.floor(diffInMinutes / 60)
    const diffInDays = Math.floor(diffInHours / 24)

    const rtf = new Intl.RelativeTimeFormat(locale, { numeric: 'auto' })

    if (Math.abs(diffInDays) >= 1) {
      return rtf.format(diffInDays, 'day')
    } else if (Math.abs(diffInHours) >= 1) {
      return rtf.format(diffInHours, 'hour')
    } else if (Math.abs(diffInMinutes) >= 1) {
      return rtf.format(diffInMinutes, 'minute')
    } else {
      return rtf.format(diffInSeconds, 'second')
    }
  } catch {
    return '-'
  }
}

/**
 * Check if a date is in the past
 */
export function isDatePast(dateString: string | null | undefined): boolean {
  if (!dateString) return false

  try {
    const date = new Date(dateString)
    return !isNaN(date.getTime()) && date < new Date()
  } catch {
    return false
  }
}

/**
 * Check if a date is today
 */
export function isDateToday(dateString: string | null | undefined): boolean {
  if (!dateString) return false

  try {
    const date = new Date(dateString)
    const today = new Date()

    return (
      !isNaN(date.getTime()) &&
      date.getFullYear() === today.getFullYear() &&
      date.getMonth() === today.getMonth() &&
      date.getDate() === today.getDate()
    )
  } catch {
    return false
  }
}

// ============================================================================
// Number Formatting
// ============================================================================

/**
 * Format a number with thousands separators
 */
export function formatNumber(
  value: number,
  locale = 'en-US',
  options: Intl.NumberFormatOptions = {}
): string {
  return new Intl.NumberFormat(locale, options).format(value)
}

/**
 * Format a number as a percentage
 */
export function formatPercentage(
  value: number,
  locale = 'en-US',
  decimals = 1
): string {
  return new Intl.NumberFormat(locale, {
    style: 'percent',
    minimumFractionDigits: decimals,
    maximumFractionDigits: decimals,
  }).format(value / 100)
}
