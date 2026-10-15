# ADR 0004: Inventory Movement Ledger

## Status
Accepted

## Context
Directly editing a stock quantity field on a product table causes concurrency errors, lost history, inability to reconcile inventory with accounting, and no audit trail.

## Decision
1. **Append-Only Movement Ledger**: Every change in physical stock is recorded as a `StockMovement` row with type (`receipt`, `delivery`, `adjustment_in`, `adjustment_out`, `transfer_in`, `transfer_out`), quantity, unit cost, and reference document.
2. **Materialized Stock Balances**: A `StockBalance` table maintains the aggregated quantity per warehouse and product, protected by optimistic or pessimistic row locking (`lockForUpdate()`) inside transactions during movement execution.
3. **Valuation**: Weighted Average Costing (WAC) is calculated on incoming receipts to ensure accurate cost of goods sold (COGS).
