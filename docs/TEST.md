# Testing Guide

This document covers the testing infrastructure, conventions, and guidelines for the Multitenant Billing System.

## Overview

- **Framework**: PHPUnit 11.5 with Laravel Testing Traits
- **Database**: SQLite in-memory for fast test execution
- **Coverage Target**: 70%+ for Repository, Services, Controllers

## Quick Start

### Running Tests

```bash
# Run all tests (Docker)
make test

# Run all tests (Local)
make test-local

# Run with coverage report (requires xdebug)
make test-coverage

# Run specific test suites
make test-unit       # Unit tests only
make test-feature    # Feature tests only
```

### Composer Scripts

```bash
cd backend

# Run all tests
composer test

# Run with coverage
composer test:coverage

# Run specific suites
composer test:unit
composer test:feature
```

## Test Structure

```
backend/tests/
├── TestCase.php              # Base test class
├── Unit/
│   ├── Repositories/
│   │   ├── BaseRepositoryTest.php
│   │   ├── InvoiceRepositoryTest.php
│   │   └── VendorRepositoryTest.php
│   └── Services/
│       └── InvoiceNumberGeneratorTest.php
└── Feature/
    ├── AuthenticationTest.php
    ├── VendorApiTest.php
    ├── InvoiceApiTest.php
    ├── Invoice/
    │   └── InvoiceStatusTest.php
    └── TenantIsolation/
        └── TenantIsolationTest.php
```

## Model Factories

All models have factories with useful states for testing.

### OrganizationFactory

```php
// Basic organization
Organization::factory()->create();

// Inactive organization
Organization::factory()->inactive()->create();
```

### UserFactory

```php
// Basic user
User::factory()->create();

// Admin user
User::factory()->admin()->create();

// Accountant user
User::factory()->accountant()->create();

// Inactive user
User::factory()->inactive()->create();

// User for specific organization
User::factory()->forOrganization($organization)->create();

// Combining states
User::factory()
    ->forOrganization($organization)
    ->admin()
    ->create();
```

### VendorFactory

```php
// Basic vendor
Vendor::factory()->create();

// Inactive vendor
Vendor::factory()->inactive()->create();

// Vendor for specific organization
Vendor::factory()->forOrganization($organization)->create();
```

### InvoiceFactory

```php
// Basic pending invoice
Invoice::factory()->create();

// Invoice with specific status
Invoice::factory()->pending()->create();
Invoice::factory()->approved()->create();
Invoice::factory()->rejected()->create();
Invoice::factory()->paid()->create();

// Overdue invoice
Invoice::factory()->overdue()->create();

// Invoice for specific vendor (inherits organization)
Invoice::factory()->forVendor($vendor)->create();

// Invoice created by specific user
Invoice::factory()->createdBy($user)->create();

// Combining states
Invoice::factory()
    ->forVendor($vendor)
    ->approved()
    ->create();
```

## Writing Tests

### Feature Tests

Feature tests test the full HTTP request/response cycle.

```php
<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organization;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = Organization::factory()->create();
        $this->user = User::factory()
            ->forOrganization($this->organization)
            ->admin()
            ->create();
    }

    public function test_example(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/vendors');

        $response->assertOk()
            ->assertJsonStructure(['data']);
    }
}
```

### Unit Tests

Unit tests test individual classes in isolation.

```php
<?php

namespace Tests\Unit\Services;

use App\Models\Organization;
use App\Services\InvoiceNumberGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceNumberGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_unique_invoice_number(): void
    {
        $generator = new InvoiceNumberGenerator();
        $organization = Organization::factory()->create(['name' => 'Acme Corp']);

        $result = $generator->generate($organization);

        $this->assertStringStartsWith('ACM-', $result);
    }
}
```

### Tenant Isolation Tests

Always test that data is properly isolated between organizations.

```php
public function test_user_cannot_access_other_organization_data(): void
{
    // Create data in another organization
    $otherOrg = Organization::factory()->create();
    $otherVendor = Vendor::factory()->forOrganization($otherOrg)->create();

    // User from different organization should not see it
    $response = $this->actingAs($this->user)
        ->getJson("/api/vendors/{$otherVendor->id}");

    $response->assertNotFound();
}
```

## Code Coverage

### Generating Coverage Reports

```bash
# Generate HTML coverage report
make test-coverage

# Report will be in backend/coverage-report/index.html
```

### Coverage Configuration

Coverage is configured in `backend/phpunit.xml`:

```xml
<coverage>
    <report>
        <html outputDirectory="coverage-report"/>
        <text outputFile="php://stdout" showOnlySummary="true"/>
        <clover outputFile="coverage.xml"/>
    </report>
</coverage>
```

### Coverage Targets

| Component | Target |
|-----------|--------|
| Repositories | 80%+ |
| Services | 90%+ |
| Controllers | 70%+ |
| Models | 60%+ |
| **Overall** | **70%+** |

## Test Environment

### Configuration

Tests use `.env.testing` which configures:

- SQLite in-memory database
- Array cache driver
- Array session driver
- Sync queue driver
- Reduced bcrypt rounds (4) for speed

### Database

Tests use `RefreshDatabase` trait which:
- Runs migrations before each test
- Wraps each test in a database transaction
- Rolls back after each test

## Best Practices

1. **Use factories** - Never manually insert data with raw SQL
2. **Test tenant isolation** - Always verify cross-organization access is blocked
3. **One assertion focus** - Each test should focus on one behavior
4. **Descriptive names** - Test names should describe the expected behavior
5. **Setup in setUp()** - Common setup should be in the setUp method
6. **Use states** - Leverage factory states for common configurations

## Troubleshooting

### Tests fail with database errors

```bash
# Clear config cache
cd backend && php artisan config:clear

# Or run with explicit database
DB_DATABASE=:memory: php artisan test
```

### Factory not found errors

Ensure the factory class exists in `backend/database/factories/` and follows the naming convention `{Model}Factory.php`.

### Slow tests

- Use `RefreshDatabase` instead of `DatabaseMigrations`
- Use SQLite in-memory (`:memory:`)
- Reduce `BCRYPT_ROUNDS` in `.env.testing`
