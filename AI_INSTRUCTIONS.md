# AI Coding Assistant Instructions & Architectural Guidelines

> **Project**: FatturaFacile  
> **Platform**: Multi-Tenant ERP Platform for SMEs  
> **Tech Stack**: Laravel 13, Filament v5, Livewire 4, Alpine.js, Tailwind CSS v4, PostgreSQL 16/17, Redis 7, PHP 8.5, Docker.

This document serves as the primary guidance file for AI coding assistants (such as Antigravity, GitHub Copilot, Claude, Cursor, ChatGPT, and others) operating within the FatturaFacile repository.

---

## 1. Repository Layout & Working Directory

The repository separates container infrastructure, JetBrains `.run` configurations, ADR documentation, and the Laravel source:

```
FatturaFacile/
├── .github/                 # Workflows (ci.yml, release.yml, sonarcloud.yml), issue templates, PR template
├── .run/                    # PhpStorm Run/Debug configurations
├── dockers/                 # PostgreSQL 16/17 and pgAdmin orchestration
├── docs/                    # Architecture ADRs, deployment guides, accounting rules
└── src/FatturaFacile/       # Laravel Application Root
    ├── app/
    │   ├── Modules/         # 17 Domain-Driven ERP Modules
    │   ├── Shared/          # BCMath MoneyValue, TenantAware traits, Enums
    │   ├── Support/         # AutoMigrator
    │   └── Filament/        # Admin resources, widgets, pages
    ├── database/migrations/ # Timestamped relational schema
    ├── routes/              # api.php, web.php, settings.php, console.php
    └── tests/               # Unit and Feature tests (100% Code Coverage)
```

> [!IMPORTANT]
> **Working Directory**: The Laravel application resides in `src/FatturaFacile/`. All PHP, Composer, Artisan, Pint, PHPUnit, and NPM commands **MUST** be executed inside `src/FatturaFacile/`.

---

## 2. Core Architecture: Modular Monolith (`app/Modules/`)

The application avoids monolithic clutter by encapsulating domain entities, actions, policies, and queries within dedicated modules:

| Module | Namespace | Purpose & Core Entities |
| :--- | :--- | :--- |
| **Accounting** | `App\Modules\Accounting` | Swiss KMU Chart of Accounts (`Account`), `AccountingPeriod`, double-entry `JournalEntry`, `JournalEntryLine`, `TrialBalanceService`. |
| **Audit** | `App\Modules\Audit` | Tenant-partitioned `AuditLog` tracking entity lifecycle, actors, and IP addresses. |
| **Companies** | `App\Modules\Companies` | Legal entities (`Company`), operational sites (`Branch`), `FiscalYear`, atomic numbering (`DocumentSequence`). |
| **CRM** | `App\Modules\CRM` | `Lead`, `Opportunity` (pipeline stages & probability), `CrmActivity` tracking. |
| **Customers** | `App\Modules\Customers` | Customer master directory (`Customer`), Swiss UID (`CHE-xxx.xxx.xxx`), payment terms, credit limits. |
| **Documents** | `App\Modules\Documents` | Private file attachments (`Attachment`) with signed streaming and authorization gates. |
| **Identity** | `App\Modules\Identity` | Multi-tenant user mapping (`TenantMembership`), roles, and granular permissions. |
| **Integrations** | `App\Modules\Integrations` | Versioned REST API v1 (`CustomerApiController`, `InvoiceApiController`, `ProductApiController`, Health Check). |
| **Inventory** | `App\Modules\Inventory` | Warehouses (`Warehouse`), append-only ledger (`StockMovement`), materialized `StockBalance`, WAC valuation. |
| **Invoicing** | `App\Modules\Invoicing` | `Invoice`, `CreditNote`, Swiss 5-cent rounding, automated line recalculations, Dompdf rendering. |
| **Payments** | `App\Modules\Payments` | Inbound payments (`Payment`), invoice allocation (`PaymentAllocation`), settlement tracking. |
| **Products** | `App\Modules\Products` | Catalog items (`Product`), categories (`ProductCategory`), Swiss VAT rates (`TaxRate`). |
| **Purchasing** | `App\Modules\Purchasing` | Vendor lifecycle: `PurchaseOrder`, `GoodsReceipt`, `SupplierInvoice`. |
| **Reporting** | `App\Modules\Reporting` | Financial dashboards, KPI calculation, overdue aging analysis, revenue metrics. |
| **Sales** | `App\Modules\Sales` | Sales pipeline: `Quote`, `SalesOrder`, `DeliveryNote` with progression and conversion. |
| **Suppliers** | `App\Modules\Suppliers` | Supplier directory (`Supplier`), payment terms, banking details. |
| **Tenancy** | `App\Modules\Tenancy` | Multi-tenant context (`TenantContext`), query scoping (`TenantScope`), `BelongsToTenant` trait. |

---

## 3. Non-Negotiable Domain Invariants

When generating or refactoring code, AI assistants **must strictly enforce** the following invariants:

### A. Multi-Tenant Data Isolation
- Every domain model belonging to an organization must implement `App\Shared\Traits\BelongsToTenant`.
- `TenantScope` automatically injects `WHERE tenant_id = ?` into all Eloquent queries.
- Unscoped queries or missing tenant context will fail safely (`1 = 0`).
- To run administrative/system tasks across tenants, always use:
  ```php
  TenantContext::executeAsSystem(function () {
      // System logic
  });
  ```

### B. Financial Calculations (Zero Float Invariant)
- **NEVER use PHP floating-point numbers (`float`)** or standard operators (`+`, `-`, `*`, `/`) for monetary amounts or tax calculations due to IEEE 754 precision issues.
- Always use `App\Shared\Money\MoneyValue` or string-based BCMath functions (`bcadd`, `bcsub`, `bcmul`, `bcdiv`, `bccomp`).
- **Swiss 5-Cent Rounding**: CHF invoice totals must round to the nearest 0.05 (`round(amount * 20) / 20` via `MoneyValue::roundToSwissFiveCents()`).
- Database columns for monetary amounts must use `DECIMAL(15, 2)` or `DECIMAL(15, 4)`.

### C. Double-Entry Accounting Invariant
- Every posted journal entry must enforce debit-credit equilibrium:
  $$\sum \text{Debits} == \sum \text{Credits}$$
- Unbalanced postings must throw an exception and rollback the active database transaction.
- Posted entries are **immutable**: once posted (`is_posted = true`), rows cannot be updated or deleted. Corrections require reversing journal entries (`reversed_entry_id`) or credit notes.

### D. Inventory Movement Ledger
- Warehouse inventory is **never** mutated by directly altering stock totals.
- Every stock change must be recorded via an append-only `StockMovement` (types: `in`, `out`, `transfer`, `adjustment`).
- Inventory cost valuation uses the **Weighted Average Cost (WAC)** algorithm.

### E. Automatic Database Migrations (AutoMigrator)
- `App\Support\Database\AutoMigrator` checks and executes pending migrations on application boot.
- Controlled by `AUTO_MIGRATE=true` in `.env`.
- Executes inside system context to bypass tenant filters during schema creation.

---

## 4. Coding Standards & Conventions

- **PHP 8.5 Features**:
  - Always declare parameter and return types: `function calculateTotal(Order $order): MoneyValue`.
  - Use constructor property promotion: `public function __construct(public JournalEntryService $service) {}`.
  - Use PHP 8.1+ Enums with TitleCase cases.
- **Code Formatting**:
  - Follow PSR-12 and Laravel conventions formatted via **Laravel Pint**.
  - Run Pint before finalizing changes:
    ```bash
    vendor/bin/pint --format agent
    ```
- **Livewire & Filament**:
  - Filament v5 panel resources reside in `app/Filament/Resources/`.
  - Maintain server-side state in Livewire components and validate inputs in actions.

---

## 5. Testing & 100% Code Coverage Rule

The FatturaFacile CI/CD pipeline enforces a strict **100% Code Coverage Quality Gate**.

- **Test Framework**: PHPUnit 12 & Pest 4.
- **Rules for New Code**:
  - Every new method, model relation, API endpoint, or service MUST include unit or feature tests.
  - Test files must reside in `src/FatturaFacile/tests/Unit/` or `src/FatturaFacile/tests/Feature/`.
  - Run the test suite:
    ```bash
    # Run full test suite
    php artisan test
    
    # Run PHPUnit with Clover coverage report
    php vendor/bin/phpunit --coverage-clover coverage.xml --coverage-text
    ```
- Never submit changes that lower code coverage below 100%.
