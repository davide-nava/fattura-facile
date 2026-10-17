# Double-Entry Accounting Posting Rules

## 1. Accounting Invariants
Every journal entry created and posted in Fattura Facile must strictly respect:
1. **Mathematical Equilibrium**:
   $$\sum \text{Debits} = \sum \text{Credits}$$
   Both sides must balance to the exact cent without any floating-point approximation.
2. **Immutability**: Once an entry transitions to `is_posted = true`, neither header attributes nor line amounts can be altered or deleted. Corrections must be booked as reversal entries (`reversal_of_id`).
3. **Period Integrity**: Journal entries cannot be posted into closed accounting periods (`is_closed = true`).

## 2. Standard Business Transaction Mappings

### Sales Invoice Issuance
When a sales invoice is issued to a customer:
- **Debit**: Trade Receivables (Account `1100` / Asset) for Total Amount.
- **Credit**: Sales Revenue (Account `3200` / Revenue) for Net Subtotal.
- **Credit**: VAT Payable (Account `2200` / Liability) for Tax Amount.

### Customer Payment Received
When a customer settles an invoice via bank transfer:
- **Debit**: Bank Account CHF (Account `1020` / Asset) for Paid Amount.
- **Credit**: Trade Receivables (Account `1100` / Asset) for Paid Amount.

### Supplier Invoice Registration
When goods or services are received from a supplier:
- **Debit**: Cost of Goods Sold / Expense (Account `4200` / Expense) for Subtotal.
- **Credit**: Trade Payables (Account `2000` / Liability) for Total Amount.

### Physical Inventory Receipt
When goods are booked into the warehouse:
- **Debit**: Merchandise Inventory (Account `1200` / Asset).
- **Credit**: Inventory Offset / GR/IR Clearing Account.
