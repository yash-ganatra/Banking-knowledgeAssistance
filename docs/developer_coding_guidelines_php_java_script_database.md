# Developer Coding Guidelines

**Scope**: PHP, JavaScript, REST APIs, and Relational Databases

**Objective**: Provide clear, practical conventions that cover ~80% of daily development needs while keeping code readable, maintainable, and safe.

---

## 1. Variable Naming Conventions (PHP, JavaScript, Database)

### 1.1 General Principles
- Be **descriptive, not verbose**.
- Names should answer: *what does this represent?*
- Avoid abbreviations unless they are **widely understood** (e.g., `id`, `url`, `api`).
- Prefer **consistency over personal preference**.

---

### 1.2 PHP Variables
**Convention**: `camelCase`

**Good Examples**
```php
$userId
$totalAmount
$isActive
$createdAt
```

**Avoid**
```php
$uid        // unclear
$TotalAmt  // inconsistent casing
$data1     // meaningless
```

**Booleans**
- Prefix with `is`, `has`, `can`, `should`
```php
$isValid
$hasAccess
```

---

### 1.3 JavaScript Variables
**Convention**: `camelCase`

```js
let orderCount;
const maxRetryLimit = 3;
let isLoggedIn;
```

**Constants**
```js
const MAX_UPLOAD_SIZE_MB = 10;
```

---

### 1.4 Database Tables & Fields

#### Tables
- Use **snake_case**
- Prefer **plural nouns**

```sql
users
order_items
payment_transactions
```

#### Columns
- Use **snake_case**
- Be explicit

```sql
user_id
created_at
updated_at
is_active
```

**Primary & Foreign Keys**
```sql
id           -- primary key
user_id      -- foreign key
```

---

## 2. Function Naming Conventions (PHP & JavaScript)

### 2.1 General Rules
- Function names should **start with a verb**.
- One function = **one responsibility**.

---

### 2.2 PHP Functions / Methods
**Convention**: `camelCase`

```php
function calculateTax()
function getUserById($userId)
function saveOrder()
```

**Boolean-returning Functions**
```php
function isUserActive()
function hasSufficientBalance()
```

---

### 2.3 JavaScript Functions

```js
function fetchUserProfile() {}
function validateForm() {}
function formatCurrency(amount) {}
```

**Async Functions**
- Use verb that implies async behavior

```js
async function loadDashboardData() {}
```

---

## 3. API Coding Best Practices

### 3.1 Input Validation
- **Never trust input** (request body, query params, headers)

```php
if (!isset($request['user_id'])) {
    throw new InvalidArgumentException('user_id is required');
}
```

---

### 3.2 Error Handling (Try–Catch)

```php
try {
    $result = $service->processPayment($data);
} catch (Exception $e) {
    logError($e->getMessage());
    return apiError('Payment failed');
}
```

**Rules**
- Catch **specific exceptions** where possible
- Do not expose internal errors to API consumers

---

### 3.3 API Responses

**Always return structured responses**

```json
{
  "success": true,
  "data": {},
  "message": ""
}
```

```json
{
  "success": false,
  "error_code": "INVALID_INPUT",
  "message": "User ID is missing"
}
```

---

### 3.4 HTTP Status Codes

- `200` – Success
- `201` – Resource created
- `400` – Validation error
- `401` – Unauthorized
- `403` – Forbidden
- `404` – Not found
- `500` – Internal error

---

### 3.5 Defensive Coding

**PHP**
```php
if (isset($data['amount']) && is_numeric($data['amount'])) {
    $amount = (float) $data['amount'];
}
```

**JavaScript**
```js
if (response?.data?.user) {
    renderUser(response.data.user);
}
```

---

## 4. Best Practices for Choosing Data Types (Database)

### 4.1 General Principles
- Choose the **smallest data type** that meets the requirement
- Think about **future growth**, not just current data

---

### 4.2 Common Data Type Guidelines

| Business Data | Recommended Type | Notes |
|--------------|------------------|------|
| IDs | INT / BIGINT | Use BIGINT if high volume expected |
| Monetary values | DECIMAL(15,2) | Never use FLOAT |
| Boolean flags | TINYINT(1) | Use `0` or `1` |
| Dates only | DATE | No time component |
| Date + time | DATETIME / TIMESTAMP | Prefer UTC |
| Short text | VARCHAR(255) | Index-friendly |
| Long text | TEXT | Avoid indexing |

---

### 4.3 Avoid These Common Mistakes
- ❌ Using `VARCHAR` for numeric values
- ❌ Storing multiple values in one column (CSV)
- ❌ Using `TEXT` when `VARCHAR` is sufficient

---

## 5. General Code Hygiene Rules

- Keep functions **< 30–40 lines** where possible
- Avoid deeply nested `if` blocks
- Add comments **only where intent is not obvious**
- Remove dead code before committing
- Follow existing project conventions first

---

## 6. Final Rule of Thumb

> **Write code for the next developer — who might be you in 6 months.**

This guideline intentionally favors clarity and consistency over cleverness.

---

**Version**: 1.0
**Audience**: Backend & Full‑Stack Developers

