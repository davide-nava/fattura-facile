# On-Premise Installation & Operations Guide

## Overview
Fattura Facile runs completely on-premise without requiring connections to proprietary third-party cloud services. The same single codebase operates both SaaS and single/multi-company on-premise enterprise environments.

## Minimum System Requirements
- 2 CPU Cores
- 4 GB RAM (8 GB recommended for heavy reporting)
- 20 GB SSD storage
- Docker Engine 24+ and Docker Compose v2+
- Linux (Ubuntu 22.04 LTS, Debian 12, RHEL 9) or Windows Server with WSL2/Docker

## Quickstart Deployment

1. Clone or unpack the release bundle on the host:
   ```bash
   git clone https://github.com/fatturafacile/fatturafacile.git /opt/fatturafacile
   cd /opt/fatturafacile
   ```

2. Copy and configure environment:
   ```bash
   cp .env.example .env
   # Edit .env to set DB_PASSWORD and local domain
   ```

3. Start services via Docker Compose:
   ```bash
   docker compose up -d
   ```

4. Initialize database schema and admin account:
   ```bash
   docker compose exec app php artisan key:generate --force
   docker compose exec app php artisan migrate --force
   docker compose exec app php artisan db:seed --force
   ```

5. Access the ERP interface:
   Open browser at `http://your-server-ip:8080/admin`
   Default credentials:
   - Email: `admin@fatturafacile.eu`
   - Password: `Password123!`
   *(Change the default password immediately upon first login)*
