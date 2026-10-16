# Contributing to FatturaFacile

Thank you for your interest in contributing to **FatturaFacile**! We welcome bug fixes, documentation improvements, Swiss & EU localization enhancements, performance optimizations, and new ERP module capabilities.

Please take a few moments to review this guide to ensure a smooth, efficient collaboration process.

---

## Code of Conduct

By participating in this project, you agree to abide by our [Code of Conduct](CODE_OF_CONDUCT.md). We expect all contributors to treat everyone with respect, courtesy, and professionalism.

---

## Branching Strategy & Git Workflow

We follow a structured Git branching strategy to ensure stability:

| Branch / Pattern | Description | Base Branch | Target PR Branch |
| :--- | :--- | :--- | :--- |
| `main` | Production-ready stable releases. Directly tagged for releases (`v*`). | - | - |
| `develop` | Active integration branch for upcoming releases. | `main` | `main` |
| `feature/<name>` | New features, module enhancements, or UI additions. | `develop` | `develop` |
| `fix/<issue-name>` | Bug fixes and defect resolutions. | `develop` | `develop` |
| `hotfix/<name>` | Critical production bug fixes requiring immediate release. | `main` | `main` and `develop` |
| `docs/<topic>` | Documentation improvements and ADR additions. | `develop` | `develop` |

### Step-by-Step Contribution Flow

1. **Fork & Clone**:
   ```bash
   git clone https://github.com/<your-username>/fatturafacile.git
   cd fatturafacile
   ```

2. **Create a Topic Branch**:
   ```bash
   git checkout -b feature/qr-bill-generator develop
   ```

3. **Develop & Test Locally** (see [Local Development Setup](#local-development-setup)).

4. **Format & Verify**:
   Run Laravel Pint and the test suite before committing.

5. **Commit Changes** using [Conventional Commits](#commit-message-conventions).

6. **Submit a Pull Request** targeting the `develop` branch (or `main` for critical hotfixes).

---

## Local Development Setup

The Laravel application root is located in `src/laravel/`.

### Prerequisites
- **PHP**: 8.5+ with extensions `bcmath`, `intl`, `pdo_pgsql`, `pgsql`, `pdo_sqlite`, `sqlite3`, `zip`, `gd`
- **Composer**: 2.7+
- **Node.js**: 20+ & npm
- **Docker & Docker Compose**: For PostgreSQL 16+ and Redis 7+

### Step-by-Step Setup

1. **Navigate to the application directory**:
   ```bash
   cd src/laravel
   ```

2. **Install Composer Dependencies**:
   ```bash
   composer install
   ```

3. **Install Frontend Dependencies**:
   ```bash
   npm install
   npm run build
   ```

4. **Environment Configuration**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Start Infrastructure Containers (PostgreSQL & Redis)**:
   From the repository root:
   ```bash
   # From root directory:
   docker compose -f dockers/docker-compose.yml up -d postgres
   ```
   Or use the full stack defined in `src/laravel/compose.yaml`.

6. **Run Database Migrations & Seed Demo Data**:
   ```bash
   php artisan migrate --seed
   ```
   *Note: If `AUTO_MIGRATE=true` is set, pending migrations run automatically upon application startup.*

7. **Start the Development Server**:
   ```bash
   php artisan serve
   ```
   Access the Filament administration panel at [http://localhost:8000/admin](http://localhost:8000/admin).
   - **Default Admin**: `admin@fatturafacile.eu`
   - **Password**: `Password123!`

---

## Coding Standards & Conventions

### 1. PHP & PSR Standards
- All code must strictly conform to **[PSR-12](https://www.php-fig.org/psr/psr-12/)** extended coding standards.
- Enforce strict typing and return type hints on all methods:
  ```php
  public function calculateTotal(Invoice $invoice): MoneyValue
  {
      // ...
  }
  ```
- Use PHP 8.5 constructor property promotion:
  ```php
  public function __construct(
      protected readonly JournalEntryService $journalEntryService,
  ) {}
  ```
- Prefer TitleCase for PHP Enum cases.

### 2. Automated Code Formatting (Laravel Pint)
Run Laravel Pint before committing any PHP files:
```bash
# Check code formatting without modifying files
./vendor/bin/pint --test

# Automatically fix code formatting
./vendor/bin/pint
```

### 3. Static Analysis
The codebase adheres to **PHPStan / Larastan Level 7**:
```bash
composer types:check
# Or directly:
./vendor/bin/phpstan analyse --memory-limit=1G
```

---

## Core ERP Invariants (Strictly Enforced)

Any Pull Request that breaks any of the following core invariants will be automatically rejected:

1. **Multi-Tenancy Isolation**:
   - Every tenant-owned model must include `App\Shared\Traits\BelongsToTenant`.
   - Never bypass `TenantScope` in business logic.
   - Missing tenant context must fail safely (`WHERE 1 = 0`).

2. **Zero Floating-Point Financial Math**:
   - **Never** use PHP `float` or primitive arithmetic (`+`, `-`, `*`, `/`) for money, amounts, discounts, or tax rates.
   - Always use `App\Shared\Money\MoneyValue` or string-based BCMath functions (`bcadd`, `bcsub`, etc.).
   - CHF invoice totals must round to 0.05 Swiss five cents (`MoneyValue::roundToSwissFiveCents()`).

3. **Double-Entry Equilibrium**:
   - Every posted journal entry must satisfy $\sum \text{Debits} == \sum \text{Credits}$.
   - Posted entries are immutable. Reversals must be recorded as separate reversing entries.

4. **Append-Only Inventory Ledger**:
   - Stock balances cannot be updated via raw SQL increments.
   - All physical inventory movements must be backed by immutable `StockMovement` entries.

---

## Testing & 100% Code Coverage Quality Gate

FatturaFacile enforces a **strict 100% Code Coverage Gate** across all statements, methods, and paths in core domain modules.

### Running Tests
Execute the test suite from `src/laravel`:

```bash
# Run tests using Pest / Artisan test runner
php artisan test

# Run tests with detailed PHPUnit output and Clover coverage
php vendor/bin/phpunit --coverage-clover coverage.xml --coverage-text

# Run a specific test class
php artisan test --filter=InvoiceWorkflowTest
```

### Writing Tests
- All new features and bug fixes must include corresponding tests in `tests/Unit/` or `tests/Feature/`.
- Test nominal flows, edge cases, permission denials, and multi-tenant isolation boundaries.
- Ensure total code coverage remains at **100%**.

---

## Commit Message Conventions

We follow [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/):

```
<type>(<scope>): <short description>

[optional body]

[optional footer(s)]
```

### Types
- `feat`: A new feature or module addition
- `fix`: A bug fix
- `docs`: Documentation updates
- `style`: Formatting, missing semi-colons, Pint cleanup (no code logic changes)
- `refactor`: Code change that neither fixes a bug nor adds a feature
- `perf`: Code change that improves execution performance
- `test`: Adding missing tests or correcting existing tests
- `chore`: Build process, dependencies, or auxiliary tool updates

### Examples
- `feat(accounting): add trial balance export to CSV format`
- `fix(invoicing): correct Swiss 5-cent rounding on multi-line credit notes`
- `test(tenancy): add isolation boundary test for customer directory queries`

---

## Pull Request Submission Checklist

Before opening your Pull Request, verify:
- [ ] You have branched from `develop` (or `main` for hotfixes).
- [ ] All code passes `./vendor/bin/pint --test` cleanly.
- [ ] All unit and feature tests pass (`php artisan test`).
- [ ] 100% Code Coverage is preserved.
- [ ] No tenant leaks or floating-point calculations exist.
- [ ] The PR title follows Conventional Commits.
- [ ] You have filled out all sections of the [Pull Request Template](.github/pull_request_template.md).

Thank you for helping make **FatturaFacile** the most reliable, modern open-source ERP for SMEs!
