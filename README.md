# Construction Change Order Engine

A DDD-structured Laravel API for managing construction change orders with event-driven budget recalculation, built to demonstrate engineering standards.

---

## Tech Stack

- **PHP 8.3** — strict types, readonly properties, enums, match expressions
- **Laravel 11** — framework for routing, auth, queues, broadcasting
- **PostgreSQL** — primary database with JSONB for audit metadata
- **Laravel Sanctum** — token-based API authentication
- **Laravel Horizon** — queue management and monitoring
- **Laravel Reverb** — WebSocket server for real-time updates
- **PHPUnit** — feature and unit test suite
- **PHPStan Level 8** — strictest static analysis
- **PHPCS PSR-12** — code style enforcement

---

## Architecture Overview

This project follows **Domain-Driven Design (DDD)** with a modular monolith structure. The key principle is that business logic is completely decoupled from the framework.

```
app/
├── Domain/          # Pure PHP — zero Laravel imports
│   ├── ChangeOrder/
│   │   ├── Contracts/   # Repository interfaces
│   │   ├── Enums/       # State machine (ChangeOrderState)
│   │   ├── Events/      # Domain events (plain PHP value objects)
│   │   ├── Exceptions/  # Domain exceptions
│   │   ├── Models/      # Eloquent models (framework-dependent, kept close to domain)
│   │   └── Services/    # Business logic (ChangeOrderService)
│   ├── ProjectBudget/
│   │   ├── Contracts/   # Repository interfaces
│   │   ├── Models/      # Project, BudgetLineItem
│   │   └── Services/    # BudgetRecalculationService
│   ├── AuditLog/
│   │   └── Models/      # AuditLog (immutable, no updated_at)
│   └── Shared/
│       └── Enums/       # UserRole (shared across domains)
├── Infrastructure/  # Laravel-specific implementations
│   ├── Events/      # Broadcast events (ShouldBroadcast)
│   ├── Listeners/   # Queued event listeners
│   ├── Providers/   # DomainServiceProvider (binds interfaces)
│   └── Repositories/ # Eloquent repository implementations
└── Application/     # HTTP layer
    └── Http/
        ├── Controllers/Api/
        ├── Requests/
        ├── Resources/
        └── Policies/
```

## Setup

### Prerequisites

- PHP 8.3
- Composer
- PostgreSQL 17
- Redis (for queues)
- Node.js (for Reverb, optional for backend-only testing)

### Installation

```bash
git clone https://github.com/Fajendagba/Construction-Change-Order-Engine.git
cd change-order-backend
composer install
cp .env.example .env
php artisan key:generate
```

### Configure Environment

Update `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=ingenious_change_order
DB_USERNAME=postgres
DB_PASSWORD=your_password

BROADCAST_CONNECTION=reverb
QUEUE_CONNECTION=redis

REVERB_APP_ID=ingenious-local
REVERB_APP_KEY=local-key
REVERB_APP_SECRET=local-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
```

### Database Setup

```bash
createdb ingenious_change_order
php artisan migrate
php artisan db:seed
```

### Running the Application

You need three terminal tabs running simultaneously:

```bash
php artisan serve

php artisan queue:work

php artisan reverb:start
```

---

## Running Tests

```bash
# Full test suite
php artisan test

# Feature tests only
php artisan test --filter "Feature"

# Unit tests only
php artisan test --filter "Unit"
```

---

## Static Analysis

```bash
# PHPStan Level 8
./vendor/bin/phpstan analyse

# PHPCS PSR-12
./vendor/bin/phpcs
```

---

## API Endpoints

| Method | Endpoint | Description | Required Role |
|--------|----------|-------------|---------------|
| POST | `/api/login` | Authenticate and receive token | Public |
| GET | `/api/me` | Get authenticated user | Any |
| GET | `/api/projects` | List all projects | Any |
| GET | `/api/projects/{id}` | Get project with budget | Any |
| GET | `/api/projects/{id}/change-orders` | List change orders | Any |
| POST | `/api/projects/{id}/change-orders` | Create change order | Contractor |
| GET | `/api/projects/{id}/change-orders/{id}` | Get change order with audit logs | Any |
| PATCH | `/api/projects/{id}/change-orders/{id}/transition` | Transition state | Role-dependent |
| GET | `/api/projects/{id}/change-orders/{id}/audit-logs` | Get audit trail | Any |

### State Transition Rules

| From State | To State | Required Role |
|------------|----------|---------------|
| Draft | Submitted | Contractor |
| Submitted | Under Review | Owner |
| Under Review | Approved | Owner |
| Under Review | Rejected | Owner |
| Rejected | Draft | Contractor |

---

## Demo Credentials

After running `php artisan db:seed`:

| Name | Email | Password | Role |
|------|-------|----------|------|
| Sarah Mitchell | owner@ingenious.build | password | Owner |
| Mike Rodriguez | contractor@ingenious.build | password | Contractor |
| Emily Chen | architect@ingenious.build | password | Architect |

---

## Queue Priority

Background jobs are processed in priority order:

1. `budget-updates` — financial data, highest priority
2. `audit` — audit log writes, medium priority
3. `notifications` — WebSocket broadcasts, lowest priority
