# Cloud SaaS Deployment Guide

## Architecture
In cloud multi-tenant deployment, FatturaFacile runs as containerized application services behind a load balancer with managed PostgreSQL and Redis clusters:

```
[ Internet ]
      │
[ Cloud Load Balancer / Reverse Proxy (TLS Termination) ]
      │
      ├──> [ Nginx Web Container (Port 80) ]
      │         │
      │         ▼
      └──> [ PHP-FPM Application Pods (Port 9000) ]
                │
                ├──> [ Managed PostgreSQL 16 Cluster ]
                ├──> [ Managed Redis Cluster (Cache, Session, Queue) ]
                └──> [ S3-Compatible Object Storage (Private Documents) ]
```

## Production Configuration Checklist
1. **Application Key**: `APP_KEY` must be generated and stored securely in cloud secrets manager (e.g. AWS Secrets Manager, Vault).
2. **Environment**: `APP_ENV=production`, `APP_DEBUG=false`.
3. **Database**: Use PostgreSQL connection pooling (e.g. PgBouncer or AWS RDS Proxy).
4. **Queue Workers**: Scale dedicated worker instances running `php artisan queue:work --tries=3 --timeout=90`.
5. **Scheduler**: Ensure a single worker process or cron task triggers `php artisan schedule:run` every minute.
6. **HTTPS & Domain**: Forward `X-Forwarded-Proto` and `X-Forwarded-Host` headers from the ingress proxy.
7. **Asset Optimization**: Run `php artisan optimize` during image build / deployment.
