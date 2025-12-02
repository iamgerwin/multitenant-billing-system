# Phase 11: Frontend Types (Initial - Duplicated)

## Title
feat: add frontend TypeScript types

## PR Links
- **Develop Branch PR**: https://github.com/iamgerwin/multitenant-billing-system/pull/13

## PR Status
Code Review

## Description
Added frontend TypeScript type definitions that mirror the backend Laravel models and enums. This establishes type safety for the frontend application when consuming API responses.

## ClickUp Ticket
https://app.clickup.com/t/86d14yd2j

## Files Changed
| File | Change Type | Description |
|------|-------------|-------------|
| `frontend/types/index.ts` | Added | TypeScript type definitions |

## Technical Details

### Enums Created
- **UserRole**: `admin`, `accountant`, `user` - with labels mapping
- **InvoiceStatus**: `pending`, `approved`, `rejected`, `paid` - with labels and color mappings

### Entity Interfaces
- **BaseEntity**: Common fields (id, created_at, updated_at)
- **User**: User entity with organization relationship
- **Organization**: Organization/tenant entity
- **Vendor**: Vendor entity with invoice aggregates
- **Invoice**: Invoice entity with status transitions and relationships

### API Response Types
- **ApiResponse<T>**: Standard wrapper for single resource responses
- **PaginatedResponse<T>**: Laravel pagination structure
- **ApiErrorResponse**: Error response with validation errors
- **AuthResponse**: Authentication response with token

### Form Payload Types
- **LoginCredentials**: Login form data
- **VendorPayload**: Vendor create/update data
- **InvoiceCreatePayload**: Invoice creation data
- **InvoiceUpdatePayload**: Invoice update data
- **InvoiceStatusPayload**: Status transition data

### Notes
This is the initial approach where types are duplicated in the frontend. This violates DRY principle and will be refactored in Phase 17-18 to use a shared TypeScript package between frontend and backend.

## Reviewers
- To Review: @iamgerwin
