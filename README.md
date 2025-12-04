# Multitenant Billing System

A full-stack multitenant billing system with vendor management, invoice tracking, and role-based access control.

## Tech Stack

- **Backend**: Laravel 11 (PHP 8.2+), MySQL 8.0, Laravel Sanctum
- **Frontend**: Nuxt 4, Vue 3, Pinia, TailwindCSS
- **Shared**: TypeScript package with types, enums, and utilities
- **Infrastructure**: Docker Compose

## Monorepo Structure

```
multitenant-billing-system/
├── backend/          # Laravel API server
├── frontend/         # Nuxt 4 application
├── shared/           # TypeScript shared package
├── docker/           # Docker configuration
├── docs/             # Documentation
├── docker-compose.yml
├── Makefile
└── .env.example
```

## Quick Start

### Prerequisites

- Docker & Docker Compose
- Git

### Setup

1. Clone the repository:
   ```bash
   git clone https://github.com/iamgerwin/multitenant-billing-system.git
   cd multitenant-billing-system
   ```

2. Copy environment file:
   ```bash
   cp .env.example .env
   ```

3. Start the services:
   ```bash
   make up
   ```

4. Run database migrations and seeders:
   ```bash
   make fresh
   ```

5. (Optional) Import sample database dump:
   ```bash
   # Extract and import the sample database
   unzip docs/billing_system.sql.zip -d docs/
   docker compose exec -T billing_mysql mysql -uroot -proot billing < docs/billing_system.sql
   ```

6. Access the application:
   - Frontend: http://localhost:3333
   - Backend API: http://localhost:8888

### Default Credentials

After running seeders:
- **Admin**: admin@acme.test / password
- **Accountant**: accountant@acme.test / password
- **User**: user@acme.test / password

## Available Commands

```bash
make up              # Start all services
make down            # Stop all services
make build           # Rebuild containers
make logs            # View container logs
make fresh           # Reset database with seeders
make test            # Run backend tests (Docker)
make test-local      # Run backend tests locally
make test-coverage   # Run tests with coverage report
make test-unit       # Run unit tests only
make test-feature    # Run feature tests only
```

## Using the Shared Package

The shared package (`@billing/shared`) provides TypeScript types, enums, and utilities used by both backend responses and frontend code.

### Installation

The package is linked via npm workspaces. In the frontend:

```typescript
import {
  UserRole,
  InvoiceStatus,
  formatCurrency,
  formatDate,
  isValidTransition,
  type User,
  type Invoice,
  type Vendor
} from '@billing/shared'
```

### Available Exports

- **Enums**: `UserRole`, `InvoiceStatus`
- **Types**: `User`, `Invoice`, `Vendor`, `Organization`, `ApiResponse`, `PaginatedResponse`
- **Type Guards**: `isUser()`, `isInvoice()`, `isVendor()`, `isValidInvoiceStatus()`
- **Utilities**: `formatCurrency()`, `formatDate()`, `isValidTransition()`, `getNextStatuses()`

## Documentation

- [Architecture](docs/ARCHITECTURE.md) - System design and patterns
- [API Reference](docs/API.md) - Endpoint documentation
- [ERD](docs/ERD.md) - Database schema and relationships
- [Testing](docs/TEST.md) - Testing infrastructure and guidelines
- [Sample Database](docs/billing_system.sql.zip) - Database dump with sample data (100+ invoices, vendors)

## Features

- **Multi-tenancy**: Organization-scoped data isolation
- **Role-based Access**: Admin, Accountant, and User roles with different permissions
- **Vendor Management**: Create, update, and track vendors
- **Invoice Workflow**: Create invoices with status transitions (Pending → Approved → Paid)
- **Type Safety**: Shared TypeScript types across the stack

## License

MIT
