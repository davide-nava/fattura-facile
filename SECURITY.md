# Security Policy

## Supported Versions

Only the latest release branch of FatturaFacile receives active security patches.

| Version | Supported          |
| ------- | ------------------ |
| 1.0.x   | :white_check_mark: |
| < 1.0   | :x:                |

## Reporting a Vulnerability

Security is paramount in FatturaFacile, especially regarding multi-tenant data isolation and financial accuracy.

If you discover a security vulnerability, please do NOT disclose it publicly via GitHub issues or discussions.

Instead, please email our security team directly:
**security@fatturafacile.eu**

Please include:
1. Type of vulnerability (e.g. Cross-Tenant Data Access, Authentication Bypass, SQL Injection, Privilege Escalation).
2. Step-by-step instructions or proof-of-concept script to reproduce the issue.
3. Impact assessment.

We commit to acknowledging your report within 48 hours and providing an estimated timeline for remediation.

## Multi-Tenancy Security Invariants
FatturaFacile maintains absolute tenant isolation as a core security requirement:
- Direct object reference (IDOR) attacks across tenants must always return `404 Not Found`.
- System operations requiring cross-tenant execution must use `TenantContext::executeAsSystem()`.
- Unscoped queries on tenant-aware entities are blocked by default.
