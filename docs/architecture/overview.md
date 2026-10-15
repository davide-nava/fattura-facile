# FatturaFacile — Architecture Overview

## 1. System Vision
**FatturaFacile** (https://fatturafacile.eu/) is a modern, modular, multi-tenant ERP platform designed for small and medium-sized enterprises (SMEs). It offers comprehensive business administration spanning sales, purchasing, inventory, invoicing, double-entry accounting, customer relations (CRM), and Swiss localization foundations.

The system is built as a **modular monolith** in PHP 8.5+ and Laravel 13, utilizing Filament v5 for administration, Livewire for dynamic interactive components, PostgreSQL 16+ for relational persistence with strict ACID transactions, and Docker for uniform cloud SaaS and on-premise deployments.

## 2. High-Level Modular Monolith Design
The application code is partitioned into domain-oriented modules located in `app/Modules/` and shared kernel primitives in `app/Shared/`:

```
app/
├── Modules/
│   ├── Identity/        # Users, roles, permissions, invitations, authentication
│   ├── Tenancy/         # Tenant models, TenantContext, TenantScope, domain resolution
│   ├── Companies/       # Legal entities, branches, fiscal years, document numbering sequences
│   ├── Customers/       # Customer directories, contacts, addresses, credit limits, pricing
│   ├── Suppliers/       # Supplier directories, contacts, purchase terms
│   ├── Products/        # Products, services, categories, units of measure, tax classifications
│   ├── Sales/           # Quotes, Sales Orders, Delivery Notes, lifecycle transitions
│   ├── Purchasing/      # Purchase Orders, Goods Receipts, Supplier Invoices
│   ├── Inventory/       # Warehouses, Stock movements (ledger), balances, adjustments, valuation
│   ├── Invoicing/       # Invoices, Credit notes, tax calculations, PDF generation
│   ├── Accounting/      # Chart of accounts, journal entries, ledger, trial balance, period closing
│   ├── Payments/        # Payment recording, payment methods, partial payment allocation
│   ├── CRM/             # Leads, opportunities, sales pipeline, activities
│   ├── Documents/       # Secure file attachments, private storage, access authorization
│   ├── Reporting/       # Tenant-aware metrics, revenue, inventory valuation, financial reports
│   ├── Notifications/   # In-app and queued email notifications
│   └── Integrations/    # Versioned REST APIs, webhooks, export adapters
└── Shared/              # Money/Decimal math, Base models, Domain contracts, Enums
```

## 3. Multi-Tenancy Strategy
FatturaFacile employs a **shared-database, tenant-partitioned** model with defense-in-depth isolation:
1. **Tenant (Organization)**: Highest-level isolation boundary. Every business entity explicitly references `tenant_id`.
2. **Company (Legal Entity)**: Distinct legal company belonging to a tenant. References `tenant_id` and `id`.
3. **Branch**: Operational unit or facility belonging to a company. References `tenant_id`, `company_id`.
4. **User & Membership**: Users have accounts and are linked to tenants via `tenant_memberships` carrying roles and status (`active`, `suspended`, `invited`).
5. **Runtime Isolation**:
   - `TenantContext`: Centralized singleton resolving the current active tenant from session or API token.
   - `TenantAwareScope`: Global Eloquent scope automatically applying `WHERE tenant_id = ?` to all tenant-scoped entities.
   - Model `creating` hook automatically stamps `tenant_id` from the resolved `TenantContext`.
   - Server-side authorization policies verify tenant, company, and permission ownership before any mutation.

## 4. Financial Integrity Invariants
1. **No Floating-Point Math**: All monetary amounts and quantities are handled using PostgreSQL `DECIMAL(15, 4)` and `DECIMAL(15, 2)` types with exact string/BCMath rounding.
2. **Double-Entry Equilibrium**: Every posted journal entry enforces `Total Debits == Total Credits`. Unbalanced journal entries are rejected at transaction commit.
3. **Immutability of Finalized Documents**: Finalized invoices and posted journal entries cannot be deleted or mutated. Corrections must be performed through Credit Notes or Reversal Journal Entries.
4. **Consecutive Document Numbering**: Sequential numbering (e.g. `INV-2026-00001`) is maintained per company and fiscal year using database locking (`SELECT ... FOR UPDATE`) to prevent gaps and race conditions.

## 5. Swiss Localization Foundations
- Default currency: Swiss Franc (`CHF`) with 5-cent rounding support.
- Swiss VAT rates:
  - Standard rate: 8.1%
  - Reduced rate: 2.6% (Food, books, medicines)
  - Special accommodation rate: 3.8% (Lodging)
  - Exempt: 0.0%
- Swiss Business Identifiers: Enterprise Identification Number (UID) format `CHE-xxx.xxx.xxx`.
- Swiss address formats (Canton, NPA 4 digits).
