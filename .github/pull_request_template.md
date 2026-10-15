## Description
<!-- Provide a concise summary of the changes made and the business motivation behind them. -->

## Related Issues
<!-- Link related issues or discussions (e.g., Fixes #123, Closes #456) -->
- Fixes #

## Type of Change
<!-- Please check the options that apply: -->
- [ ] 🐛 Bug fix (non-breaking change which fixes an issue)
- [ ] ✨ New feature (non-breaking change which adds functionality)
- [ ] 💥 Breaking change (fix or feature that would cause existing functionality to change)
- [ ] ♻️ Code refactor or performance improvement (no functional logic changes)
- [ ] 📝 Documentation update
- [ ] 🔧 Infrastructure, CI/CD, or configuration update

## Modules Impacted
- [ ] Multi-Tenancy & Tenant Context
- [ ] Identity & Access Management (Roles/Permissions)
- [ ] Invoicing & Sales (Quotes, Orders, Invoices, Dompdf)
- [ ] Purchasing & Suppliers (POs, Goods Receipts, Supplier Invoices)
- [ ] Inventory & Warehouses (Stock Movements, Valuation)
- [ ] Accounting & Ledger (KMU Chart of Accounts, Journal Entries)
- [ ] Payments & Allocations
- [ ] CRM & Activities
- [ ] REST API v1
- [ ] Filament Admin Panel
- [ ] Database Schema & Migrations

## Core Invariants Verification
<!-- Every PR MUST respect the foundational ERP invariants: -->
- [ ] **Multi-Tenancy Isolation**: All tenant-scoped models implement `BelongsToTenant`. No unscoped raw SQL or context leaks.
- [ ] **Financial Precision**: All currency math uses `App\Shared\Money\MoneyValue` / BCMath string operations. Zero PHP `float` usage.
- [ ] **Double-Entry Equilibrium**: Any accounting postings strictly enforce `sum(Debits) == sum(Credits)`.
- [ ] **Ledger Immutability**: Posted journal entries and issued invoices are immutable (adjustments made via reversal entries / credit notes).
- [ ] **Append-Only Inventory**: Stock balance adjustments are backed by discrete, append-only `StockMovement` records.

## Testing & Quality Checklist
<!-- Before submitting your PR, verify the following: -->
- [ ] My code adheres to the project's coding standards ([PSR-12](https://www.php-fig.org/psr/psr-12/) via Laravel Pint).
- [ ] I have run `./vendor/bin/pint --test` (or `composer lint:check`) with zero style violations.
- [ ] I have written new automated Unit and/or Feature tests covering the changed functionality.
- [ ] All tests pass locally via `php artisan test` (or `vendor/bin/phpunit`).
- [ ] The **100% Code Coverage Gate** is preserved (`php vendor/bin/phpunit --coverage-text`).
- [ ] Database migrations (if any) are backwards-compatible and execute safely via AutoMigrator.
- [ ] Documentation has been added or updated in `README.md` or `docs/` where appropriate.

## Testing Steps & Screenshots
<!-- Describe step-by-step instructions for testing and verifying these changes: -->
1. Run migrations / seeds: `php artisan migrate --seed`
2. Navigate to: `...`
3. Execute action: `...`
4. Verify outcome: `...`

<!-- If UI changes were made in Filament, attach before/after screenshots here: -->
