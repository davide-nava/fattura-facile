# ADR 0003: Financial Integrity and Double-Entry Accounting

## Status
Accepted

## Context
An ERP must guarantee absolute accuracy in financial reporting. Floating-point precision issues, silent mutations of historic ledgers, or out-of-balance entries destroy auditability and legal compliance.

## Decision
1. **Decimal Precision**: All currency amounts are stored as `DECIMAL(15, 2)` or `DECIMAL(15, 4)` and calculated using exact string arithmetic via BCMath / `App\Shared\Money\MoneyValue`.
2. **Double-Entry Principle**: Journal entries consist of 2 or more lines where `sum(debit) == sum(credit)`. Unbalanced entries cannot be posted.
3. **Immutability**: Once an entry is marked `posted`, its debit/credit lines cannot be updated or deleted. Corrections require an explicit reversing journal entry (`reversal_of_id`).
4. **Integration**: Invoices post to Accounts Receivable and Sales Revenue. Supplier Invoices post to Expense/Asset and Accounts Payable. Payments clear AR/AP balances against Bank/Cash accounts.
