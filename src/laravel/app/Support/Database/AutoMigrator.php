<?php

namespace App\Support\Database;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Throwable;

class AutoMigrator
{
    public function __construct(
        protected Application $app,
    ) {}

    /**
     * Check and run any pending migrations if enabled.
     */
    public function runIfPending(): bool
    {
        if (! config('database.auto_migrate', env('AUTO_MIGRATE', true))) {
            return false;
        }

        // Avoid running during standard unit/feature tests unless explicitly testing this behavior
        if ($this->app->runningUnitTests() && ! config('database.auto_migrate_in_tests', false)) {
            return false;
        }

        // When running in CLI, only run for long-running servers like 'serve' or octane
        if ($this->app->runningInConsole()) {
            $command = $_SERVER['argv'][1] ?? null;
            if (! in_array($command, ['serve', 'octane:start'], true)) {
                return false;
            }
        }

        return $this->executePendingMigrations();
    }

    /**
     * Determine if migrations are pending and run them.
     */
    public function executePendingMigrations(): bool
    {
        try {
            $migrator = $this->app->make('migrator');

            if (! $migrator->repositoryExists()) {
                Artisan::call('migrate', ['--force' => true]);

                return true;
            }

            $paths = array_unique(array_merge([$this->app->databasePath('migrations')], $migrator->paths()));
            $files = $migrator->getMigrationFiles($paths);
            $ran = $migrator->getRepository()->getRan();
            $pending = array_diff(array_keys($files), $ran);

            if (! empty($pending)) {
                Artisan::call('migrate', ['--force' => true]);

                return true;
            }

            return false;
        } catch (Throwable $e) {
            Log::warning('Automatic database migration check failed: '.$e->getMessage(), [
                'exception' => $e,
            ]);

            return false;
        }
    }
}
