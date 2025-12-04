# API Documentation

Base URL: `http://localhost:8888/api`

## Authentication

All protected endpoints require a Bearer token in the Authorization header:

```
Authorization: Bearer <token>
```

---

## Auth Endpoints

### Login

Authenticate a user and receive an access token.

```
POST /api/auth/login
```

**Request Body:**

| Field    | Type   | Required | Description       |
|----------|--------|----------|-------------------|
| email    | string | Yes      | User email        |
| password | string | Yes      | User password     |

**Example Request:**

```json
{
  "email": "admin@acme.test",
  "password": "password"
}
```

**Response (200 OK):**

```json
{
  "data": {
    "token": "1|abc123...",
    "user": {
      "id": 1,
      "organization_id": 1,
      "name": "Admin User",
      "email": "admin@acme.test",
      "role": "admin",
      "role_label": "Admin",
      "is_active": true,
      "email_verified_at": "2024-01-01T00:00:00.000000Z",
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z",
      "organization": {
        "id": 1,
        "name": "Acme Corporation",
        "slug": "acme-corporation",
        "email": "contact@acme.test",
        "currency": "USD",
        "is_active": true
      }
    }
  },
  "message": "Login successful"
}
```

**Error Response (401 Unauthorized):**

```json
{
  "message": "Invalid credentials"
}
```

---

### Get Current User

Retrieve the authenticated user's information.

```
GET /api/auth/user
```

**Headers:** `Authorization: Bearer <token>`

**Response (200 OK):**

```json
{
  "data": {
    "id": 1,
    "organization_id": 1,
    "name": "Admin User",
    "email": "admin@acme.test",
    "role": "admin",
    "role_label": "Admin",
    "is_active": true,
    "email_verified_at": "2024-01-01T00:00:00.000000Z",
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z",
    "organization": {
      "id": 1,
      "name": "Acme Corporation",
      "slug": "acme-corporation",
      "email": "contact@acme.test",
      "currency": "USD",
      "is_active": true
    }
  }
}
```

---

### Logout

Revoke the current access token.

```
POST /api/auth/logout
```

**Headers:** `Authorization: Bearer <token>`

**Response (200 OK):**

```json
{
  "message": "Logged out successfully"
}
```

---

### Logout All Sessions

Revoke all access tokens for the authenticated user.

```
POST /api/auth/logout-all
```

**Headers:** `Authorization: Bearer <token>`

**Response (200 OK):**

```json
{
  "message": "All sessions logged out successfully"
}
```

---

## Organization Endpoints

### Get Organization

Retrieve the current user's organization with aggregate counts.

```
GET /api/organization
```

**Headers:** `Authorization: Bearer <token>`

**Response (200 OK):**

```json
{
  "data": {
    "id": 1,
    "name": "Acme Corporation",
    "slug": "acme-corporation",
    "email": "contact@acme.test",
    "phone": "+1-555-0100",
    "address": "123 Business St",
    "city": "San Francisco",
    "state": "CA",
    "postal_code": "94105",
    "country": "USA",
    "full_address": "123 Business St, San Francisco, CA 94105, USA",
    "tax_id": "12-3456789",
    "currency": "USD",
    "is_active": true,
    "users_count": 3,
    "vendors_count": 5,
    "invoices_count": 12,
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

---

## Vendor Endpoints

### List Vendors

Retrieve a paginated list of vendors.

```
GET /api/vendors
```

**Headers:** `Authorization: Bearer <token>`

**Query Parameters:**

| Parameter | Type    | Default | Description                |
|-----------|---------|---------|----------------------------|
| per_page  | integer | 15      | Items per page (max 100)   |
| page      | integer | 1       | Page number                |

**Response (200 OK):**

```json
{
  "data": [
    {
      "id": 1,
      "organization_id": 1,
      "name": "Tech Supplies Inc",
      "code": "TECH-001",
      "email": "sales@techsupplies.test",
      "phone": "+1-555-0101",
      "address": "456 Tech Ave",
      "city": "San Jose",
      "state": "CA",
      "postal_code": "95110",
      "country": "USA",
      "full_address": "456 Tech Ave, San Jose, CA 95110, USA",
      "tax_id": "98-7654321",
      "payment_terms": 30,
      "notes": null,
      "is_active": true,
      "invoices_count": 3,
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z"
    }
  ],
  "links": {
    "first": "http://localhost:8888/api/vendors?page=1",
    "last": "http://localhost:8888/api/vendors?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "path": "http://localhost:8888/api/vendors",
    "per_page": 15,
    "to": 1,
    "total": 1
  }
}
```

---

### Create Vendor

Create a new vendor. Requires Admin role (Accountant is read-only).

```
POST /api/vendors
```

**Headers:** `Authorization: Bearer <token>`

**Request Body:**

| Field         | Type    | Required | Description                  |
|---------------|---------|----------|------------------------------|
| name          | string  | Yes      | Vendor name (max 255)        |
| code          | string  | Yes      | Unique vendor code (max 50)  |
| email         | string  | No       | Contact email                |
| phone         | string  | No       | Phone number (max 50)        |
| address       | string  | No       | Street address (max 500)     |
| city          | string  | No       | City (max 100)               |
| state         | string  | No       | State/Province (max 100)     |
| postal_code   | string  | No       | Postal/ZIP code (max 20)     |
| country       | string  | No       | Country (max 100)            |
| tax_id        | string  | No       | Tax ID (max 50)              |
| payment_terms | integer | No       | Payment terms in days (0-365)|
| notes         | string  | No       | Internal notes (max 1000)    |
| is_active     | boolean | No       | Active status (default: true)|

**Example Request:**

```json
{
  "name": "Office Supplies Co",
  "code": "OFF-001",
  "email": "orders@officesupplies.test",
  "phone": "+1-555-0102",
  "payment_terms": 15,
  "is_active": true
}
```

**Response (201 Created):**

```json
{
  "data": {
    "id": 2,
    "organization_id": 1,
    "name": "Office Supplies Co",
    "code": "OFF-001",
    "email": "orders@officesupplies.test",
    "phone": "+1-555-0102",
    "address": null,
    "city": null,
    "state": null,
    "postal_code": null,
    "country": null,
    "full_address": "",
    "tax_id": null,
    "payment_terms": 15,
    "notes": null,
    "is_active": true,
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  },
  "message": "Vendor created successfully"
}
```

---

### Get Vendor

Retrieve a single vendor by ID.

```
GET /api/vendors/{id}
```

**Headers:** `Authorization: Bearer <token>`

**Response (200 OK):**

```json
{
  "data": {
    "id": 1,
    "organization_id": 1,
    "name": "Tech Supplies Inc",
    "code": "TECH-001",
    "email": "sales@techsupplies.test",
    "phone": "+1-555-0101",
    "address": "456 Tech Ave",
    "city": "San Jose",
    "state": "CA",
    "postal_code": "95110",
    "country": "USA",
    "full_address": "456 Tech Ave, San Jose, CA 95110, USA",
    "tax_id": "98-7654321",
    "payment_terms": 30,
    "notes": null,
    "is_active": true,
    "invoices_count": 3,
    "total_invoice_amount": 15000.00,
    "pending_invoices_count": 1,
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

---

### Update Vendor

Update an existing vendor. Requires Admin role.

```
PATCH /api/vendors/{id}
```

**Headers:** `Authorization: Bearer <token>`

**Request Body:** Same fields as Create Vendor (all optional)

**Response (200 OK):**

```json
{
  "data": { ... },
  "message": "Vendor updated successfully"
}
```

---

### Delete Vendor

Delete a vendor. Only allowed if vendor has no invoices. Requires Admin role.

```
DELETE /api/vendors/{id}
```

**Headers:** `Authorization: Bearer <token>`

**Response (200 OK):**

```json
{
  "message": "Vendor deleted successfully"
}
```

**Error Response (422 Unprocessable):**

```json
{
  "message": "Cannot delete vendor with existing invoices"
}
```

---

## Invoice Endpoints

### List Invoices

Retrieve a paginated list of invoices.

```
GET /api/invoices
```

**Headers:** `Authorization: Bearer <token>`

**Query Parameters:**

| Parameter | Type    | Default | Description                |
|-----------|---------|---------|----------------------------|
| per_page  | integer | 15      | Items per page (max 100)   |
| page      | integer | 1       | Page number                |

**Response (200 OK):**

```json
{
  "data": [
    {
      "id": 1,
      "organization_id": 1,
      "vendor_id": 1,
      "invoice_number": "INV-001",
      "invoice_date": "2024-01-15",
      "due_date": "2024-02-15",
      "status": "pending",
      "status_label": "Pending",
      "status_color": "warning",
      "is_overdue": false,
      "subtotal": "5000.00",
      "tax_amount": "500.00",
      "discount_amount": "0.00",
      "total_amount": "5500.00",
      "currency": "USD",
      "description": "Monthly supplies order",
      "notes": null,
      "paid_date": null,
      "payment_method": null,
      "payment_reference": null,
      "can_edit": true,
      "can_delete": true,
      "allowed_transitions": ["approved", "rejected"],
      "created_by": 1,
      "approved_by": null,
      "approved_at": null,
      "created_at": "2024-01-15T00:00:00.000000Z",
      "updated_at": "2024-01-15T00:00:00.000000Z",
      "vendor": {
        "id": 1,
        "name": "Tech Supplies Inc",
        "code": "TECH-001"
      },
      "creator": {
        "id": 1,
        "name": "Admin User"
      }
    }
  ],
  "links": { ... },
  "meta": { ... }
}
```

---

### Create Invoice

Create a new invoice. Requires Admin role. Status is automatically set to "pending".

```
POST /api/invoices
```

**Headers:** `Authorization: Bearer <token>`

**Request Body:**

| Field           | Type    | Required | Description                        |
|-----------------|---------|----------|------------------------------------|
| vendor_id       | integer | Yes      | Vendor ID (must belong to org)     |
| invoice_number  | string  | Yes      | Unique invoice number (max 50)     |
| invoice_date    | date    | Yes      | Invoice date (YYYY-MM-DD)          |
| due_date        | date    | No       | Due date (must be >= invoice_date) |
| subtotal        | decimal | Yes      | Subtotal amount (0-999999999.99)   |
| tax_amount      | decimal | No       | Tax amount (default: 0)            |
| discount_amount | decimal | No       | Discount amount (default: 0)       |
| currency        | string  | No       | 3-letter currency code (e.g. USD)  |
| description     | string  | No       | Invoice description (max 1000)     |
| notes           | string  | No       | Internal notes (max 1000)          |

**Example Request:**

```json
{
  "vendor_id": 1,
  "invoice_number": "INV-002",
  "invoice_date": "2024-01-20",
  "due_date": "2024-02-20",
  "subtotal": 2500.00,
  "tax_amount": 250.00,
  "discount_amount": 100.00,
  "currency": "USD",
  "description": "Office equipment purchase"
}
```

**Response (201 Created):**

```json
{
  "data": {
    "id": 2,
    "organization_id": 1,
    "vendor_id": 1,
    "invoice_number": "INV-002",
    "invoice_date": "2024-01-20",
    "due_date": "2024-02-20",
    "status": "pending",
    "status_label": "Pending",
    "status_color": "warning",
    "is_overdue": false,
    "subtotal": "2500.00",
    "tax_amount": "250.00",
    "discount_amount": "100.00",
    "total_amount": "2650.00",
    "currency": "USD",
    "description": "Office equipment purchase",
    "notes": null,
    "can_edit": true,
    "can_delete": true,
    "allowed_transitions": ["approved", "rejected"],
    "created_by": 1,
    "approved_by": null,
    "approved_at": null,
    "created_at": "2024-01-20T00:00:00.000000Z",
    "updated_at": "2024-01-20T00:00:00.000000Z"
  },
  "message": "Invoice created successfully"
}
```

---

### Get Invoice

Retrieve a single invoice by ID with related data.

```
GET /api/invoices/{id}
```

**Headers:** `Authorization: Bearer <token>`

**Response (200 OK):**

```json
{
  "data": {
    "id": 1,
    "organization_id": 1,
    "vendor_id": 1,
    "invoice_number": "INV-001",
    "invoice_date": "2024-01-15",
    "due_date": "2024-02-15",
    "status": "approved",
    "status_label": "Approved",
    "status_color": "success",
    "is_overdue": false,
    "subtotal": "5000.00",
    "tax_amount": "500.00",
    "discount_amount": "0.00",
    "total_amount": "5500.00",
    "currency": "USD",
    "description": "Monthly supplies order",
    "notes": null,
    "paid_date": null,
    "payment_method": null,
    "payment_reference": null,
    "can_edit": false,
    "can_delete": false,
    "allowed_transitions": ["paid", "rejected"],
    "created_by": 1,
    "approved_by": 1,
    "approved_at": "2024-01-16T10:00:00.000000Z",
    "created_at": "2024-01-15T00:00:00.000000Z",
    "updated_at": "2024-01-16T10:00:00.000000Z",
    "vendor": {
      "id": 1,
      "name": "Tech Supplies Inc",
      "code": "TECH-001",
      "email": "sales@techsupplies.test"
    },
    "creator": {
      "id": 1,
      "name": "Admin User",
      "email": "admin@acme.test"
    },
    "approver": {
      "id": 1,
      "name": "Admin User",
      "email": "admin@acme.test"
    }
  }
}
```

---

### Update Invoice

Update an existing invoice. Only allowed for invoices with "pending" or "rejected" status. Requires Admin role.

```
PATCH /api/invoices/{id}
```

**Headers:** `Authorization: Bearer <token>`

**Request Body:** Same fields as Create Invoice (all optional except vendor_id and invoice_number which cannot be changed)

**Response (200 OK):**

```json
{
  "data": { ... },
  "message": "Invoice updated successfully"
}
```

---

### Update Invoice Status

Transition an invoice to a new status.

```
PATCH /api/invoices/{id}/status
```

**Headers:** `Authorization: Bearer <token>`

**Request Body:**

| Field             | Type   | Required | Description                              |
|-------------------|--------|----------|------------------------------------------|
| status            | string | Yes      | New status (pending/approved/rejected/paid) |
| payment_method    | string | Conditional | Required when status is "paid"       |
| payment_reference | string | No       | Payment reference number                 |

**Allowed Transitions:**

| From     | To                    | Permission Required |
|----------|-----------------------|---------------------|
| pending  | approved, rejected    | Admin only          |
| approved | paid, rejected        | Admin only          |
| rejected | pending               | Admin only          |
| paid     | (no transitions)      | N/A                 |

**Example Request (Approve):**

```json
{
  "status": "approved"
}
```

**Example Request (Mark as Paid):**

```json
{
  "status": "paid",
  "payment_method": "bank_transfer",
  "payment_reference": "TXN-123456"
}
```

**Response (200 OK):**

```json
{
  "data": {
    "id": 1,
    "status": "approved",
    "status_label": "Approved",
    "status_color": "success",
    "can_edit": false,
    "can_delete": false,
    "allowed_transitions": ["paid", "rejected"],
    "approved_by": 1,
    "approved_at": "2024-01-16T10:00:00.000000Z",
    ...
  },
  "message": "Invoice approved successfully"
}
```

---

### Delete Invoice

Delete an invoice. Only allowed for invoices with "pending" or "rejected" status. Requires Admin role.

```
DELETE /api/invoices/{id}
```

**Headers:** `Authorization: Bearer <token>`

**Response (200 OK):**

```json
{
  "message": "Invoice deleted successfully"
}
```

**Error Response (422 Unprocessable):**

```json
{
  "message": "Cannot delete invoice with status: approved"
}
```

---

## Error Responses

### 401 Unauthorized

```json
{
  "message": "Unauthenticated."
}
```

### 403 Forbidden

```json
{
  "message": "This action is unauthorized."
}
```

### 404 Not Found

```json
{
  "message": "Resource not found."
}
```

### 422 Validation Error

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field_name": [
      "The field name is required."
    ]
  }
}
```

### 500 Server Error

```json
{
  "message": "Server Error"
}
```

---

## Status Codes Summary

| Code | Description              |
|------|--------------------------|
| 200  | Success                  |
| 201  | Created                  |
| 401  | Unauthorized             |
| 403  | Forbidden                |
| 404  | Not Found                |
| 422  | Validation Error         |
| 500  | Server Error             |
