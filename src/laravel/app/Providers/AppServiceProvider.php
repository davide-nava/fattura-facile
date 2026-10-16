<?php

namespace App\Providers;

use App\Modules\Audit\Services\AuditService;
use App\Modules\Tenancy\Services\TenantContext;
use App\Support\Database\AutoMigrator;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TenantContext::class, function () {
            return new TenantContext;
        });

        $this->app->singleton(AuditService::class, function () {
            return new AuditService;
        });

        $this->app->singleton(AutoMigrator::class, function () {
            return new AutoMigrator($this->app);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->autoRunPendingMigrations();
    }

    /**
     * Automatically execute pending database migrations during application bootstrap.
     */
    protected function autoRunPendingMigrations(): void
    {
        $this->app->make(AutoMigrator::class)->runIfPending();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
