# Architecture Documentation

## Overview

The Multitenant Billing System is a monorepo containing three main packages:

```
multitenant-billing-system/
├── backend/     # Laravel 11 API
├── frontend/    # Nuxt 4 Application
└── shared/      # TypeScript Types & Utilities
```

## Monorepo Structure Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                        Monorepo Root                             │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌──────────────┐    ┌──────────────┐    ┌──────────────┐       │
│  │   Frontend   │    │   Backend    │    │    Shared    │       │
│  │   (Nuxt 4)   │    │  (Laravel)   │    │ (TypeScript) │       │
│  └──────┬───────┘    └──────┬───────┘    └──────┬───────┘       │
│         │                   │                   │               │
│         │                   │                   │               │
│         └───────────────────┼───────────────────┘               │
│                             │                                    │
│                     imports types &                              │
│                     utilities from                               │
│                        @billing/shared                           │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                         MySQL 8.0                                │
│                    (Docker Container)                            │
└─────────────────────────────────────────────────────────────────┘
```

## Backend Architecture (Laravel)

### Directory Structure

```
backend/
├── app/
│   ├── Contracts/
│   │   └── Repositories/   # Repository interfaces
│   ├── Enums/              # PHP Enums (UserRole, InvoiceStatus)
│   ├── Http/
│   │   ├── Controllers/    # API Controllers (thin, HTTP concerns only)
│   │   ├── Middleware/     # Tenant middleware
│   │   ├── Requests/       # Form Request validation
│   │   └── Resources/      # API Resources (JSON transformers)
│   ├── Models/             # Eloquent models
│   │   ├── Concerns/       # Model traits
│   │   └── Scopes/         # Query scopes
│   ├── Providers/          # Service providers
│   ├── Repositories/       # Repository implementations
│   └── Services/           # Business logic services
├── database/
│   ├── migrations/         # Database migrations
│   └── seeders/            # Database seeders
├── routes/
│   └── api.php             # API routes
└── tests/
    ├── Feature/            # Feature/Integration tests
    └── Unit/
        ├── Repositories/   # Repository unit tests
        └── Services/       # Service unit tests
```

### Design Patterns

#### Service-Repository Pattern

The application uses a layered architecture with Services and Repositories:

```
┌──────────────┐    ┌─────────────┐    ┌─────────────────────┐    ┌───────────┐
│  Controller  │───▶│   Service   │───▶│ RepositoryInterface │───▶│  Eloquent │
│  (HTTP)      │    │  (Business) │    └─────────────────────┘    │   Model   │
└──────────────┘    └─────────────┘              │                └───────────┘
                                                 ▼
                                       ┌─────────────────────┐
                                       │     Repository      │
                                       │  (Implementation)   │
                                       └─────────────────────┘
```

**Layer Responsibilities:**

| Layer | Responsibility | Examples |
|-------|----------------|----------|
| Controller | HTTP concerns, request/response handling, validation | `InvoiceController`, `VendorController` |
| Service | Business logic orchestration, calculations, workflows | `InvoiceService`, `VendorService` |
| Repository | Data access, queries, filtering | `InvoiceRepository`, `VendorRepository` |
| Model | Entity definition, relationships, scopes | `Invoice`, `Vendor` |

**Available Services:**
- `InvoiceService` - Invoice CRUD, total calculations, status transitions
- `VendorService` - Vendor CRUD, deletion validation
- `OrganizationService` - Organization data with relationship counts
- `InvoiceNumberGenerator` - Unique invoice number generation
- `StatsService` - Dashboard statistics with caching

**Available Repositories:**
- `InvoiceRepository` - Invoice queries with filtering, sorting, pagination
- `VendorRepository` - Vendor queries with statistics
- `OrganizationRepository` - Organization queries with counts

**Bindings:**
- Services: Auto-resolved via constructor injection
- Repositories: Bound in `RepositoryServiceProvider` (interface → implementation)

**Benefits:**
- Controllers remain thin (HTTP concerns only)
- Business logic is testable in isolation
- Data access is abstracted and mockable
- Easy to swap implementations for testing

#### Multi-tenancy

```
Request → AuthMiddleware → TenantMiddleware → Controller
                               │
                               ▼
                    Sets organization_id in context
                               │
                               ▼
                    OrganizationScope applied to all queries
```

- All data models include `organization_id`
- `BelongsToOrganization` trait applies global scope
- Users can only access data within their organization

## Frontend Architecture (Nuxt 4)

### Directory Structure

```
frontend/
├── components/         # Reusable Vue components
├── composables/        # Vue composables (useApi, etc.)
├── layouts/            # Page layouts
├── middleware/         # Route middleware (auth)
├── pages/              # File-based routing
│   ├── dashboard.vue
│   ├── login.vue
│   ├── invoices/
│   └── vendors/
└── stores/             # Pinia state management
    ├── auth.ts
    ├── invoice.ts
    └── vendor.ts
```

### State Management (Pinia)

```
┌─────────────────────────────────────────────────────────────┐
│                         Pinia Stores                         │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌──────────┐    ┌──────────────┐    ┌──────────────┐       │
│  │   Auth   │    │   Invoice    │    │    Vendor    │       │
│  │  Store   │    │    Store     │    │    Store     │       │
│  └────┬─────┘    └──────┬───────┘    └──────┬───────┘       │
│       │                 │                   │               │
│  - user            - invoices          - vendors            │
│  - token           - pagination        - pagination         │
│  - organization    - filters           - selectedVendor     │
│                                                              │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
                    ┌──────────────────┐
                    │    useApi()      │
                    │   composable     │
                    └──────────────────┘
                              │
                              ▼
                    ┌──────────────────┐
                    │   Backend API    │
                    └──────────────────┘
```

## Shared Package Structure

### Directory Layout

```
shared/
├── enums/
│   └── index.ts        # UserRole, InvoiceStatus + metadata
├── types/
│   ├── index.ts        # Entity interfaces & API types
│   └── guards.ts       # Type guard functions
├── utils/
│   └── index.ts        # Formatting & utility functions
└── index.ts            # Package exports
```

### Exports

```typescript
// Enums
export { UserRole, InvoiceStatus } from './enums'
export { UserRoleLabels, UserRolePermissions } from './enums'
export { InvoiceStatusLabels, InvoiceStatusColors, InvoiceStatusTransitions } from './enums'

// Types
export type { User, Invoice, Vendor, Organization } from './types'
export type { ApiResponse, PaginatedResponse, PaginationMeta } from './types'
export type { VendorPayload, InvoiceCreatePayload, InvoiceStatusPayload } from './types'

// Type Guards
export { isUser, isInvoice, isVendor, isOrganization } from './types/guards'
export { isValidInvoiceStatus, isValidUserRole } from './types/guards'

// Utilities
export { formatCurrency, formatCompactCurrency } from './utils'
export { formatDate, formatRelativeDate, isDatePast } from './utils'
export { getNextStatuses, isValidTransition } from './utils'
```

## Keeping PHP and TypeScript Enums in Sync

### UserRole Enum

**PHP** (`backend/app/Enums/UserRole.php`):
```php
enum UserRole: string
{
    case Admin = 'admin';
    case Accountant = 'accountant';
}
```

**TypeScript** (`shared/enums/index.ts`):
```typescript
export enum UserRole {
  Admin = 'admin',
  Accountant = 'accountant',
}
```

### InvoiceStatus Enum

**PHP** (`backend/app/Enums/InvoiceStatus.php`):
```php
enum InvoiceStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Paid = 'paid';
}
```

**TypeScript** (`shared/enums/index.ts`):
```typescript
export enum InvoiceStatus {
  Pending = 'pending',
  Approved = 'approved',
  Rejected = 'rejected',
  Paid = 'paid',
}
```

### Synchronization Rules

1. **Values must match exactly** - String values are serialized in API responses
2. **Add to both simultaneously** - When adding new enum values, update both PHP and TypeScript
3. **Test serialization** - Ensure API responses deserialize correctly on the frontend
4. **Use metadata** - Both sides define labels, colors, and transition rules

### Status Transition Matrix

| From      | To (Allowed)           |
|-----------|------------------------|
| Pending   | Approved, Rejected     |
| Approved  | Paid, Rejected         |
| Rejected  | Pending                |
| Paid      | (terminal - no transitions) |

## Authentication Flow

```
┌──────────┐     ┌─────────────┐     ┌──────────────┐
│  Client  │────▶│ POST /login │────▶│ AuthController│
└──────────┘     └─────────────┘     └──────┬───────┘
                                            │
                                      Validate credentials
                                            │
                                            ▼
                                     ┌──────────────┐
                                     │ Create Token │
                                     │  (Sanctum)   │
                                     └──────┬───────┘
                                            │
                                            ▼
                              ┌──────────────────────────┐
                              │  Return token + user     │
                              │  with organization       │
                              └──────────────────────────┘
```

## Role-Based Permissions

| Role       | canWrite | canApprove | canManageUsers | Description |
|------------|----------|------------|----------------|-------------|
| Admin      | Yes      | Yes        | Yes            | Full org-level access |
| Accountant | No       | No         | No             | Read-only access |

## Docker Infrastructure

```
┌─────────────────────────────────────────────────────────────┐
│                    Docker Compose                            │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐       │
│  │    MySQL     │  │   Backend    │  │   Frontend   │       │
│  │   :3366      │  │   :8888      │  │   :3333      │       │
│  └──────────────┘  └──────────────┘  └──────────────┘       │
│         │                 │                 │               │
│         └────────┬────────┘                 │               │
│                  │                          │               │
│           billing_network                   │               │
│                  │                          │               │
│                  └──────────────────────────┘               │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

### Services

| Service   | Container        | Port  | Description            |
|-----------|------------------|-------|------------------------|
| mysql     | billing_mysql    | 3366  | MySQL 8.0 database     |
| backend   | billing_backend  | 8888  | Laravel API server     |
| frontend  | billing_frontend | 3333  | Nuxt development server|
