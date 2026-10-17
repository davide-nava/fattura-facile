# Tenant Lifecycle & Security Architecture

## 1. Domain Separation
Fattura Facile enforces a hierarchy of multi-tenant ownership:
```
Tenant (Customer Organization / Isolated Boundary)
  └── Companies (Legal Entities e.g. AG, Sàrl, GmbH)
        ├── Branches (Physical Facilities / Stores)
        ├── Warehouses (Inventory Stock Locations)
        ├── Fiscal Years & Accounting Periods
        └── Document Numbering Sequences
```

## 2. Membership & Access Control
- A `User` represents a single individual identity across the system.
- Users gain access to a tenant through a `TenantMembership` record.
- Roles available:
  - `superadmin`: Global platform administrator across tenants (requires explicit system context).
  - `tenant_admin`: Complete administrative control within their tenant.
  - `accountant`: Access to Invoicing, Chart of Accounts, Journal Entries, and Reports.
  - `sales_manager`: Access to Customers, Quotes, Sales Orders, and Invoices.
  - `warehouse_manager`: Access to Warehouses, Stock movements, Goods receipts, and Delivery notes.
  - `staff`: Standard read/create capabilities.

## 3. Defense-in-Depth Tenant Isolation
1. **Global Scope (`TenantScope`)**: Automatically attached to every tenant-aware model.
2. **Context Resolution (`TenantContext`)**: Resolved on every request via `X-Tenant-ID` header, session, or verified user association.
3. **Automatic Stamping (`creating` hook)**: Automatically injects `tenant_id` from the resolved context.
4. **Data Leakage Guards**: Requests without a valid tenant context fail safely (`WHERE 1 = 0`) unless explicitly wrapped in `TenantContext::executeAsSystem()`.
