<?php

namespace App\Providers;

use App\Modules\Audit\Services\AuditService;
use App\Modules\Reporting\Services\ReportManager;
use App\Modules\Tenancy\Services\TenantContext;
use App\Services\Localization\Audit\Contracts\TranslationAuditorInterface;
use App\Services\Localization\Audit\Contracts\TranslationScannerInterface;
use App\Services\Localization\Audit\Services\CodebaseTranslationScanner;
use App\Services\Localization\Audit\Services\TranslationAuditor;
use App\Services\Localization\Contracts\LocaleManagerInterface;
use App\Services\Localization\LocaleManager;
use App\Services\Navigation\Contracts\NavigationManagerInterface;
use App\Services\Navigation\NavigationManager;
use App\Services\Pdf\Contracts\PdfGeneratorInterface;
use App\Services\Pdf\PdfManager;
use App\Support\Database\AutoMigrator;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Blade;
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

        $this->app->singleton(
            LocaleManagerInterface::class,
            LocaleManager::class
        );

        $this->app->singleton(
            TranslationScannerInterface::class,
            CodebaseTranslationScanner::class
        );

        $this->app->singleton(
            TranslationAuditorInterface::class,
            TranslationAuditor::class
        );

        $this->app->singleton(
            NavigationManagerInterface::class,
            NavigationManager::class
        );

        $this->app->singleton(PdfManager::class, function ($app) {
            return new PdfManager($app);
        });

        $this->app->singleton(
            PdfGeneratorInterface::class,
            PdfManager::class
        );

        $this->app->singleton(ReportManager::class, function ($app) {
            return new ReportManager(
                $app,
                $app->make(PdfGeneratorInterface::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::anonymousComponentPath(resource_path('views/layouts'), 'layouts');
        Blade::anonymousComponentPath(resource_path('views/components/print'), 'print');

        Factory::guessFactoryNamesUsing(function (string $modelName) {
            return 'Database\\Factories\\'.class_basename($modelName).'Factory';
        });

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
