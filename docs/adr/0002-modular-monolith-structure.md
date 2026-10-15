# ADR 0002: Modular Monolith Architecture

## Status
Accepted

## Context
A business ERP covers diverse domains: Sales, Purchasing, Inventory, Accounting, CRM, Documents, etc. Distributed microservices introduce high operational overhead, distributed transaction failures, network latency, and coordination complexity that exceeds the needs of SME ERPs.

## Decision
We implement a **Modular Monolith** structure in `app/Modules/`. Each module encapsulates its domain models, services/actions, policies, database migrations, and Filament UI components. Modules interact through defined public service contracts and domain events rather than direct cross-module table mutations.

## Consequences
- Single deployable Docker artifact.
- Strong boundaries prevent spaghetti code while enabling in-process database transactions across related business operations (e.g. Invoicing posting to Accounting and Inventory).
