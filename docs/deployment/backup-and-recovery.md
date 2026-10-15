# Backup and Disaster Recovery Procedures

## Database Backup (PostgreSQL)

### 1. Automated Daily Logical Dump
Create a cron job on the host or inside a maintenance container:

```bash
docker exec -t fatturafacile-postgres pg_dump -U fatturafacile -d fatturafacile -F c -b -v -f /var/lib/postgresql/data/backup_$(date +%Y%m%d_%H%M%S).dump
```

### 2. Offsite Replication
Sync database dumps and persistent file storage (`storage/app/private`) to external backup storage using encrypted protocols:

```bash
rsync -avz -e ssh /opt/fatturafacile/storage/ backup-server:/backups/fatturafacile/storage/
```

## Database Restoration Procedure

1. Place the target backup dump file into the host directory:
   ```bash
   cp backup_20261014.dump /opt/fatturafacile/
   ```

2. Terminate active application connections:
   ```bash
   docker compose stop app queue scheduler web
   ```

3. Restore database schema and data:
   ```bash
   docker exec -i fatturafacile-postgres dropdb -U fatturafacile --if-exists fatturafacile
   docker exec -i fatturafacile-postgres createdb -U fatturafacile fatturafacile
   docker exec -i fatturafacile-postgres pg_restore -U fatturafacile -d fatturafacile -v < backup_20261014.dump
   ```

4. Run migrations if upgrading across versions:
   ```bash
   docker compose run --rm app php artisan migrate --force
   ```

5. Restart application services:
   ```bash
   docker compose up -d
   ```
