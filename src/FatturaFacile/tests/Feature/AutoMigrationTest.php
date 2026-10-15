<?php

namespace Tests\Feature;

use App\Support\Database\AutoMigrator;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class AutoMigrationTest extends TestCase
{
    public function test_it_does_not_run_when_disabled_in_config(): void
    {
        Config::set('database.auto_migrate', false);

        $automigrator = new AutoMigrator($this->app);

        $this->assertFalse($automigrator->runIfPending());
    }

    public function test_it_does_not_run_during_tests_unless_flagged(): void
    {
        Config::set('database.auto_migrate', true);
        Config::set('database.auto_migrate_in_tests', false);

        $automigrator = new AutoMigrator($this->app);

        $this->assertFalse($automigrator->runIfPending());
    }

    public function test_it_skips_in_console_for_non_server_commands(): void
    {
        Config::set('database.auto_migrate', true);
        Config::set('database.auto_migrate_in_tests', true);

        $_SERVER['argv'] = ['artisan', 'route:list'];

        $automigrator = new AutoMigrator($this->app);

        $this->assertFalse($automigrator->runIfPending());
    }

    public function test_it_runs_when_server_command_is_invoked(): void
    {
        Config::set('database.auto_migrate', true);
        Config::set('database.auto_migrate_in_tests', true);

        $_SERVER['argv'] = ['artisan', 'serve'];

        $automigrator = new AutoMigrator($this->app);

        // In test database, migrations repository is created and migrations run, returning true
        $this->assertTrue($automigrator->runIfPending());
    }

    public function test_it_executes_pending_migrations_when_repository_missing(): void
    {
        $migratorMock = Mockery::mock(Migrator::class);
        $migratorMock->shouldReceive('repositoryExists')->once()->andReturn(false);

        $this->app->instance('migrator', $migratorMock);

        Artisan::shouldReceive('call')
            ->once()
            ->with('migrate', ['--force' => true])
            ->andReturn(0);

        $automigrator = new AutoMigrator($this->app);

        $this->assertTrue($automigrator->executePendingMigrations());
    }

    public function test_it_executes_pending_migrations_when_migrations_are_pending(): void
    {
        $repositoryMock = Mockery::mock();
        $repositoryMock->shouldReceive('getRan')->once()->andReturn(['2024_01_01_000000_create_test_table']);

        $migratorMock = Mockery::mock(Migrator::class);
        $migratorMock->shouldReceive('repositoryExists')->once()->andReturn(true);
        $migratorMock->shouldReceive('paths')->once()->andReturn([]);
        $migratorMock->shouldReceive('getMigrationFiles')->once()->andReturn([
            '2024_01_01_000000_create_test_table' => '/path/1',
            '2024_01_02_000000_create_pending_table' => '/path/2',
        ]);
        $migratorMock->shouldReceive('getRepository')->once()->andReturn($repositoryMock);

        $this->app->instance('migrator', $migratorMock);

        Artisan::shouldReceive('call')
            ->once()
            ->with('migrate', ['--force' => true])
            ->andReturn(0);

        $automigrator = new AutoMigrator($this->app);

        $this->assertTrue($automigrator->executePendingMigrations());
    }

    public function test_it_logs_and_returns_false_when_exception_occurs(): void
    {
        Log::shouldReceive('warning')->once();

        $migratorMock = Mockery::mock(Migrator::class);
        $migratorMock->shouldReceive('repositoryExists')->once()->andThrow(new \RuntimeException('Connection failed'));

        $this->app->instance('migrator', $migratorMock);

        $automigrator = new AutoMigrator($this->app);

        $this->assertFalse($automigrator->executePendingMigrations());
    }

    public function test_execute_pending_migrations_returns_false_when_all_ran(): void
    {
        $repositoryMock = Mockery::mock();
        $repositoryMock->shouldReceive('getRan')->once()->andReturn(['2024_01_01_000000_table']);

        $migratorMock = Mockery::mock(Migrator::class);
        $migratorMock->shouldReceive('repositoryExists')->once()->andReturn(true);
        $migratorMock->shouldReceive('paths')->once()->andReturn([]);
        $migratorMock->shouldReceive('getMigrationFiles')->once()->andReturn([
            '2024_01_01_000000_table' => '/path/1',
        ]);
        $migratorMock->shouldReceive('getRepository')->once()->andReturn($repositoryMock);

        $this->app->instance('migrator', $migratorMock);

        $automigrator = new AutoMigrator($this->app);

        $this->assertFalse($automigrator->executePendingMigrations());
    }
}
