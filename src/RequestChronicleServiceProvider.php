<?php

namespace Plakhin\RequestChronicle;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class RequestChronicleServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-request-chronicle')
            ->hasConfigFile()
            ->hasMigration('create_laravel_request_chronicle_table');
    }
}
