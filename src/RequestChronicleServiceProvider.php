<?php

namespace Plakhin\RequestChronicle;

use Plakhin\RequestChronicle\Http\Middleware\SaveRequest;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class RequestChronicleServiceProvider extends PackageServiceProvider
{
    public function register(): void
    {
        parent::register();
        $this->registerMiddleware();
    }

    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-request-chronicle')
            ->hasConfigFile()
            ->hasMigration('create_request_chronicle_table');
    }

    private function registerMiddleware(): void
    {
        $this->app->singleton(SaveRequest::class);
    }
}
