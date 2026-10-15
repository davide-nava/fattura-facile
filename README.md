# FatturaFacile

> **Multi-Tenant ERP Platform for Small & Medium Enterprises**  
> Official Website: [https://fatturafacile.eu/](https://fatturafacile.eu/)  
> Built with Laravel 13, Filament v5, Livewire 4, Tailwind CSS v4, PostgreSQL 16/17, and Docker.

[![CI Quality Gate](https://github.com/fatturafacile/fatturafacile/actions/workflows/ci.yml/badge.svg)](https://github.com/fatturafacile/fatturafacile/actions/workflows/ci.yml)
[![Coverage: 100%](https://img.shields.io/badge/Coverage-100%25-brightgreen.svg)](#8-testing--100-code-coverage)
[![PHP: 8.5](https://img.shields.io/badge/PHP-8.5-blue.svg)](https://php.net)
[![Laravel: 13.x](https://img.shields.io/badge/Laravel-13.x-red.svg)](https://laravel.com)
[![Filament: v5](https://img.shields.io/badge/Filament-v5-orange.svg)](https://filamentphp.com)
[![PostgreSQL: 16%2F17](https://img.shields.io/badge/PostgreSQL-16%2F17-336791.svg)](https://postgresql.org)
[![License: MIT](https://img.shields.io/badge/License-MIT-emerald.svg)](LICENSE)

---

## 1. Project Overview

**FatturaFacile** is an enterprise-grade, modular, multi-tenant ERP platform architected specifically for small and medium-sized businesses (SMEs) with Swiss localization foundations and international readiness.

It is designed to serve both **Cloud SaaS multi-tenant distribution** and **Customer-managed on-premise single/multi-company installations** using an identical application codebase.

All business operations, financial calculations, inventory movements, and double-entry postings are backed by real database persistence and strict transactional consistency.

---

## 2. Core Modules & Capabilities

The platform is structured into 17 domain-driven modules inside `app/Modules/`:

| Module | Key Capabilities |
| :--- | :--- |
| **Tenancy** | Organization boundaries (`tenant_id`), runtime resolution (`TenantContext`), and automatic query scoping (`TenantScope`). |
| **Identity** | Multi-tenant user mapping (`TenantMembership`), roles (`superadmin`, `tenant_admin`, `accountant`, `sales_manager`, `warehouse_manager`, `staff`), and granular permissions. |
| **Companies** | Multiple legal entities per tenant (`Company`), operational locations (`Branch`), `FiscalYear`, and atomic numbering (`DocumentSequence`). |
| **Customers** | Customer directory (`Customer`), Swiss UID numbers (`CHE-xxx.xxx.xxx`), payment terms, and credit limits. |
| **Suppliers** | Vendor directory (`Supplier`), purchase terms, and banking details. |
| **Products** | Categories, physical products and services, units of measure, sales/purchase pricing, and Swiss VAT classifications (`TaxRate`). |
| **Sales** | Commercial workflow: Quotes, Sales Orders, and Delivery Notes with status progression and document conversions. |
| **Purchasing** | Procurement lifecycle: Purchase Orders, Goods Receipts, and Supplier Invoices. |
| **Inventory** | Multi-warehouse management, append-only `StockMovement` ledger, materialized `StockBalance`, Weighted Average Costing (WAC), inter-warehouse transfers, and overselling guards. |
| **Invoicing** | Invoices, Credit Notes, automated line recalculations, Swiss 5-cent rounding for CHF, and PDF invoice rendering via Dompdf. |
| **Accounting** | Double-entry ledger, Swiss standard chart of accounts (KMU), accounting periods, immutable draft/posted journal entries (`Debit == Credit`), reversals, and Trial Balance generation. |
| **Payments** | Payment recording with methods, multi-invoice allocation (`PaymentAllocation`), and partial settlement tracking. |
| **CRM** | Customer relationship tracking: Leads, Opportunities with pipeline stages and win probabilities, and CRM activity tracking. |
| **Documents** | Private attachment storage with authenticated, authorized streaming (`Attachment`). |
| **Reporting** | Tenant-aware financial dashboards, live KPIs (revenue, overdue aging, payables, stock valuation, CRM pipeline). |
| **Integrations** | Versioned REST API v1 (`/api/v1/...`) with tenant context headers. |
| **Audit** | Tenant-scoped audit logging for security and compliance tracking. |

---

## 3. Technology Stack

- **Backend Framework**: PHP 8.5+, Laravel 13, Composer 2.x
- **Administration UI**: Filament v5, Livewire 4, Alpine.js, Tailwind CSS v4
- **Relational Database**: PostgreSQL 16+ / 17 (production), SQLite (in-memory test suite)
- **Caching & Queues**: Redis 7+ (with local sync/database fallback)
- **PDF Generation**: Dompdf (`barryvdh/laravel-dompdf`)
- **Static Analysis & Quality**: PHPStan / Larastan (Level 7), Laravel Pint (PSR-12)
- **Automated Testing**: PHPUnit 12 & Pest 4 (100% Code Coverage Quality Gate)
- **Containerization**: Docker, Docker Compose, Nginx 1.27

---

## 4. Repository Layout & Architecture

```
FatturaFacile/
├── .github/                 # Workflows (ci.yml, release.yml, sonarcloud.yml), issue templates, PR template
├── .run/                    # PhpStorm Run/Debug configurations
├── dockers/                 # Container infrastructure (PostgreSQL 16/17, pgAdmin)
├── docs/                    # Architecture ADRs, deployment guides, double-entry posting rules
│   ├── accounting/          # Double-entry posting invariants
│   ├── adr/                 # Architecture Decision Records (ADRs 0001 - 0005)
│   ├── architecture/        # Overview & module diagrams
│   ├── deployment/          # Cloud SaaS and On-Premise guides
│   └── tenancy/             # Tenant lifecycle and isolation
└── src/FatturaFacile/       # Laravel Application Root
    ├── app/
    │   ├── Modules/         # 17 Domain-Driven ERP Modules
    │   ├── Shared/          # MoneyValue (BCMath), TenantAware traits, Enums
    │   ├── Support/         # AutoMigrator (Automatic pending migrations)
    │   └── Filament/        # Filament v5 resources, dashboard widgets, and panels
    ├── config/              # Application & database configurations
    ├── database/migrations/ # Timestamped relational schema
    ├── routes/              # api.php, web.php, settings.php, console.php
    └── tests/               # Unit and Feature test suites (100% Code Coverage)
```

---

## 5. Prerequisites

Before installing FatturaFacile locally, ensure you have:

- **PHP 8.5+** with the following extensions enabled:
  `bcmath`, `intl`, `pdo_pgsql`, `pgsql`, `pdo_sqlite`, `sqlite3`, `zip`, `gd`
- **Composer 2.7+**
- **Node.js 20+ & npm**
- **Docker Engine & Docker Compose**

---

## 6. Installation & Quickstart

### Option A: Local Development Setup

1. **Clone the repository**:
   ```bash
   git clone https://github.com/fatturafacile/fatturafacile.git
   cd fatturafacile
   ```

2. **Navigate to the Laravel application directory**:
   ```bash
   cd src/FatturaFacile
   ```

3. **Install Dependencies**:
   ```bash
   composer install
   npm install
   npm run build
   ```

4. **Configure Environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Start PostgreSQL & Redis via Docker**:
   ```bash
   # From root directory:
   docker compose -f ../../dockers/docker-compose.yml up -d postgres
   ```

6. **Run Migrations & Seed Demo Tenant**:
   ```bash
   php artisan migrate --seed
   ```

7. **Start the Application**:
   ```bash
   php artisan serve
   ```
   Access the administration panel at [http://localhost:8000/admin](http://localhost:8000/admin):
   - **Default Admin**: `admin@fatturafacile.eu`
   - **Password**: `Password123!`

---

### Option B: Docker Compose Full Stack

To spin up the entire containerized stack (App, Nginx web server, PostgreSQL 16, Redis 7, Queue Worker, and Scheduler):

```bash
# Navigate to the Laravel application root
cd src/FatturaFacile

# Start all containers in background
docker compose -f compose.yaml up -d

# Execute database migrations and seed data
docker compose -f compose.yaml exec app php artisan migrate --seed --force
```

Access the panel at: [http://localhost:8080/admin](http://localhost:8080/admin)

---

## 7. Environment Configuration Guide (`.env.example`)

The application is configured using standard environment variables defined in `src/FatturaFacile/.env.example`:

| Variable | Default Value | Description |
| :--- | :--- | :--- |
| `APP_NAME` | `FatturaFacile` | Application name displayed in emails and Filament UI. |
| `APP_ENV` | `local` | Environment mode (`local`, `production`, `testing`). |
| `APP_KEY` | *(generated)* | 32-character AES encryption key (`php artisan key:generate`). |
| `APP_DEBUG` | `true` | Detailed debug error reporting (must be `false` in production). |
| `APP_URL` | `http://localhost:8000` | Base URL of the application. |
| `AUTO_MIGRATE` | `true` | Automatically checks and runs pending database migrations on boot. |
| `DB_CONNECTION` | `pgsql` / `sqlite` | Database driver (`pgsql` for production/dev, `sqlite` for tests). |
| `DB_HOST` | `127.0.0.1` | PostgreSQL server hostname or container name (`postgres`). |
| `DB_PORT` | `5432` | PostgreSQL port. |
| `DB_DATABASE` | `fatturafacile` | Database name. |
| `DB_USERNAME` | `fatturafacile` | Database user. |
| `DB_PASSWORD` | `secret` | Database password. |
| `CACHE_STORE` | `database` / `redis` | Cache driver (`redis` recommended for production). |
| `QUEUE_CONNECTION` | `database` / `redis` | Queue driver for asynchronous jobs and notifications. |
| `SESSION_DRIVER` | `database` / `redis` | User session storage driver. |
| `REDIS_HOST` | `127.0.0.1` | Redis server hostname. |
| `REDIS_PORT` | `6379` | Redis port. |
| `MAIL_MAILER` | `log` | Mail transport (`smtp`, `log`, `ses`, `resend`). |

---

## 8. Testing & 100% Code Coverage

FatturaFacile enforces **100% code coverage** across all statements, methods, and domain logic. Every PR must satisfy this gate in CI.

From the `src/FatturaFacile` directory:

1. **Run Full Test Suite**:
   ```bash
   php artisan test
   ```

2. **Execute Full Suite with Clover XML & Text Coverage**:
   ```bash
   php vendor/bin/phpunit --coverage-clover coverage.xml --coverage-text
   ```

3. **Run Code Formatting Check (Laravel Pint)**:
   ```bash
   ./vendor/bin/pint --test
   ```

4. **Run Static Analysis (Larastan)**:
   ```bash
   composer types:check
   ```

---

## 9. Core Architectural Invariants

- **Multi-Tenancy Isolation**:
  - `tenant_id` scopes all organization entities via `BelongsToTenant`.
  - `TenantScope` injects `WHERE tenant_id = ?` into queries. Missing context fails safely (`1 = 0`).
- **Zero Floating-Point Financial Math**:
  - Financial calculations **never** use PHP `float`. All currency math uses `App\Shared\Money\MoneyValue` or string-based BCMath operations.
  - Swiss 5-cent rounding for CHF invoice totals: `round(amount * 20) / 20`.
- **Double-Entry Equilibrium**:
  - Posted journal entries strictly enforce $\sum \text{Debits} == \sum \text{Credits}$. Unbalanced entries are rejected.
  - Posted journal entries and issued invoices are immutable.
- **Append-Only Inventory Ledger**:
  - Stock levels are never mutated directly. All changes create discrete `StockMovement` records evaluated under Weighted Average Costing (WAC).
- **AutoMigrator**:
  - Pending database migrations run safely on bootstrap via `App\Support\Database\AutoMigrator` in system context.

---

## 10. REST API v1

Versioned REST endpoints are available under `/api/v1` with tenant context validation:

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/api/v1/health` | Service health status and platform version. |
| `GET` | `/api/v1/customers` | List tenant customers (supports filtering & pagination). |
| `POST` | `/api/v1/customers` | Create a new customer record. |
| `GET` | `/api/v1/customers/{id}` | Retrieve a specific customer by ID. |
| `GET` | `/api/v1/products` | List catalog products and services. |
| `GET` | `/api/v1/products/{id}` | Retrieve a specific product by ID. |
| `GET` | `/api/v1/invoices` | List tenant invoices. |
| `POST` | `/api/v1/invoices` | Create a draft invoice with line items. |
| `GET` | `/api/v1/invoices/{id}` | Retrieve an invoice by ID. |
| `POST` | `/api/v1/invoices/{id}/issue` | Issue an invoice (locks lines and generates PDF). |
| `GET` | `/api/v1/invoices/{id}/pdf` | Download rendered Dompdf invoice PDF. |

*Header requirements: Provide `X-Tenant-ID: <id>` or authenticate via Sanctum token.*

---

## 11. PhpStorm Run/Debug Configurations

Pre-configured run and debug configurations are located in `.run/`:

| Configuration | Type | Purpose |
| :--- | :--- | :--- |
| **All Tests (PHPUnit with Coverage)** | PHPUnit | Executes complete test suite with Xdebug code coverage generation |
| **Unit Tests** | PHPUnit | Runs all unit tests targeting `src/FatturaFacile/tests/Unit` |
| **Feature Tests** | PHPUnit | Runs all feature tests targeting `src/FatturaFacile/tests/Feature` |
| **Serve (Debug)** | PHP Script | Launches `artisan serve --port=8000` with Xdebug listener |
| **Queue Worker (Debug)** | PHP Script | Starts background queue worker `artisan queue:listen --tries=1` |
| **Pint Code Style** | PHP Script | Runs `vendor/bin/pint` to enforce code style |

---

## 12. GitHub Actions CI/CD Pipeline

The `.github/workflows/ci.yml` pipeline automates continuous integration:

1. **Code Quality & Security Gate**:
   - Validates `composer.json` strict compliance (`composer validate --strict`).
   - Checks code styling adherence using Laravel Pint (`pint --test`).
   - Performs automated security vulnerability auditing (`composer audit`).
2. **Automated Tests & 100% Coverage Verification**:
   - Launches a real PostgreSQL 16 Alpine service container.
   - Verifies schema migrations against PostgreSQL (`php artisan migrate --force`).
   - Executes the complete PHPUnit suite with Xdebug coverage.
   - Enforces a strict **100% Code Coverage Gate** before allowing builds to pass.
   - Archives the Clover XML coverage report as an artifact.
3. **Continuous Deployment**:
   - Automatically builds the production-ready Docker container on pushes to `main`.

---

## 13. Documentation & Architecture ADRs

- [Architecture Overview](docs/architecture/overview.md)
- [ADR 0001: Multi-Tenancy Isolation Strategy](docs/adr/0001-multi-tenancy-isolation-strategy.md)
- [ADR 0002: Modular Monolith Structure](docs/adr/0002-modular-monolith-structure.md)
- [ADR 0003: Financial Integrity & Double-Entry Accounting](docs/adr/0003-financial-integrity-and-double-entry-accounting.md)
- [ADR 0004: Inventory Movement Ledger](docs/adr/0004-inventory-movement-ledger.md)
- [ADR 0005: Swiss Localization Foundations](docs/adr/0005-swiss-localization-foundations.md)
- [Double-Entry Accounting Posting Rules](docs/accounting/double-entry-posting-rules.md)
- [Multi-Tenant Lifecycle and Security](docs/tenancy/tenant-lifecycle.md)
- [Cloud SaaS Deployment Architecture](docs/deployment/cloud.md)
- [On-Premise Enterprise Installation](docs/deployment/on-premise.md)
- [Backup and Disaster Recovery](docs/deployment/backup-and-recovery.md)
- [AI Coding Assistant Instructions](AI_INSTRUCTIONS.md)

---

## 14. Contributing & Security

- **Contributing**: Please review [CONTRIBUTING.md](CONTRIBUTING.md) for code style, branching strategy, and PR submission rules.
- **Security Disclosures**: Report confidential security or multi-tenant isolation vulnerabilities to **security@fatturafacile.eu** or via our [Security Policy](SECURITY.md).

---

## 15. License

FatturaFacile is open-source software licensed under the [MIT License](LICENSE).  
Official project website: [https://fatturafacile.eu/](https://fatturafacile.eu/)
