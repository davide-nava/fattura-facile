# ADR 0001: Multi-Tenancy Isolation Strategy

## Status
Accepted

## Context
Fattura Facile is intended for SaaS deployment across multiple small and medium enterprises, as well as on-premise single-tenant or multi-subsidiary deployments. Requirements mandate strict data isolation so that no tenant can ever view, modify, or leak data belonging to another tenant across UI queries, APIs, background jobs, exports, or attachment downloads.

## Decision
We adopt a **shared-database, tenant-aware discriminant column (`tenant_id`) architecture** reinforced by defense-in-depth:
1. **Model Layer**: A `BelongsToTenant` trait attaches a global `TenantScope` applying `WHERE tenant_id = :current_tenant_id` on all queries, and automatically injects `tenant_id` from `TenantContext` upon creation.
2. **Context Resolution**: A centralized `TenantContext` service manages the active tenant. For HTTP requests, the tenant is resolved from the authenticated user's active membership (or validated subdomain/header for API calls). Client-supplied `tenant_id` parameters are never blindly trusted.
3. **Authorization Layer**: Laravel Policies check `user->hasAccessToTenant($tenantId)` and `model->tenant_id === tenantContext->getTenantId()`.
4. **Database Constraints**: Foreign keys and composite unique indexes (e.g. `(tenant_id, company_id, code)`) enforce multi-tenant consistency at the relational engine level.
5. **Background Jobs & Commands**: Tenant-aware jobs serialize their `tenant_id` and initialize the `TenantContext` upon execution.

## Consequences
- **Pros**: Cost-effective for SaaS, simplified database schema management (single migration run), high tenant scalability, identical codebase for cloud and on-premise.
- **Cons**: Requires continuous automated test verification to prevent global scope bypass (`withoutGlobalScopes()`) and leakages in raw queries or joins.
