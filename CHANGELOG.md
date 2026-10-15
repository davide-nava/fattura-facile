# Changelog

All notable changes to **FatturaFacile** will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- **Standardized GitHub Issue Forms**: Replaced legacy markdown templates with interactive YAML issue forms for Bug Reports (`bug_report.yml`) and Feature Requests (`feature_request.yml`), complete with module selectors, invariant checklists, and environment dropdowns.
- **Issue Template Configuration**: Added `.github/ISSUE_TEMPLATE/config.yml` disabling blank issues and linking to security disclosure and developer documentation.
- **Standardized Pull Request Template**: Created `.github/pull_request_template.md` with comprehensive invariant verification (multi-tenancy, zero-float math, double-entry equilibrium, append-only stock), testing checklists, and review steps.
- **Dedicated AI Guidelines**: Added `AI_INSTRUCTIONS.md` and enriched `AGENTS.md` with deep architectural context, domain invariant rules, and modular monolith reference for AI coding assistants.
- **Comprehensive Contribution Guide**: Expanded `CONTRIBUTING.md` with branching strategy (`main`, `develop`, `feature/*`, `fix/*`), PSR-12 and Pint standards, Conventional Commits specifications, and setup instructions.
- **Cleaned Funding Configuration**: Standardized `.github/FUNDING.yml` for GitHub Sponsors and Ko-fi.

### Fixed
- **Release CI Workflow**: Corrected syntax errors, updated to PHP 8.5, and added production asset compilation and tarball/zip packaging in `.github/workflows/release.yml`.
- **SonarCloud Configuration**: Updated `.sonarcloud.properties` and `.github/workflows/sonarcloud.yml` to reflect `src/FatturaFacile` directory layout and PHP 8.5.

## [1.1.0] - 2026-10-15

### Added
- **Automatic Database Migrations**: Implemented `App\Support\Database\AutoMigrator` to detect and run pending database migrations automatically during application bootstrap in `AppServiceProvider::boot()`, with CLI safety guards and `AUTO_MIGRATE` configuration.
- **100% Automated Code Coverage**: Achieved complete 100% code coverage across all statements, methods, and elements with 161 tests and 597 assertions covering multi-tenancy isolation, models, relations, Livewire components, API endpoints, Filament resources, Fortify auth, and financial math.
- **PhpStorm Run/Debug Configurations**: Added pre-configured `.run/` configurations for PHPUnit with coverage, Unit tests, Feature tests, Artisan Serve debug, Queue Worker debug, and Pint code styling.
- **Optimized GitHub Actions CI/CD Pipeline**: Upgraded `.github/workflows/ci.yml` with working-directory support for `src/FatturaFacile`, PostgreSQL 16 migration testing, automated security audits, a strict 100% code coverage quality gate, and production Docker build automation.
- **English Documentation Standard**: Standardized repository markdown files, technical architecture ADRs, and code comments into clear, professional English.

## [1.0.0] - 2026-10-14

### Added
- **Multi-Tenant Core Architecture**: Shared database with strict `tenant_id` partitioning, `TenantContext` singleton, and `BelongsToTenant` scope.
- **Identity & Access Management**: Multi-tenancy memberships, roles (`superadmin`, `tenant_admin`, `accountant`, `sales_manager`, `warehouse_manager`, `staff`), granular permissions, and audit logging.
- **Companies & Branches**: Multiple legal entities per tenant, branch facilities, fiscal years, and atomic document sequence numbering.
- **Customer & Supplier Directories**: Contact information, Swiss UID numbers, payment terms, currency preferences, and credit limits.
- **Product & Service Catalog**: Categories, physical products and services, units of measure, tax classifications, and sales/purchase pricing.
- **Inventory & Warehouses**: Multi-warehouse management, append-only `StockMovement` ledger, materialized `StockBalance`, Weighted Average Costing (WAC), inter-warehouse transfers, and overselling guards.
- **Sales Lifecycle**: Quotes, Sales Orders, and Delivery Notes with status progression and document conversions.
- **Invoicing & Credit Notes**: Automatic document numbering, server-side line recalculations, Swiss 5-cent rounding for CHF, PDF invoice rendering with Dompdf, and credit note issuance.
- **Double-Entry Accounting Foundation**: Swiss standard chart of accounts (KMU), accounting periods, draft and posted journal entries, immutable posting invariants (`Debit == Credit`), reversal entries, and Trial Balance generation.
- **Payment Management**: Payment recording with payment methods, multi-invoice allocation, partial settlement, and supplier invoice settlements.
- **CRM Module**: Leads, Opportunities with pipeline stages and win probabilities, and CRM activity tracking.
- **Document Management**: Private attachment uploads and authorized streaming.
- **Executive Reporting & Dashboard**: Live KPIs for revenue, receivables, overdue aging, payables, stock valuation, and CRM pipeline.
- **REST API v1**: Versioned API for health, customers, products, and invoices with tenant header validation.
- **Filament v5 Administration**: Interactive panel resources with table filtering, modals, search, and dashboard widgets.
- **Docker & Infrastructure**: Multi-stage production Dockerfile, Docker Compose stack (App, Nginx, PostgreSQL 16, Redis 7), and CI/CD quality gate workflow.

[Unreleased]: https://github.com/fatturafacile/fatturafacile/compare/v1.1.0...HEAD
[1.1.0]: https://github.com/fatturafacile/fatturafacile/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/fatturafacile/fatturafacile/releases/tag/v1.0.0
