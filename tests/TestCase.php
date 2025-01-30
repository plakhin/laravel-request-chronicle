<?php

namespace Plakhin\RequestChronicle\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Plakhin\RequestChronicle\RequestChronicleServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            RequestChronicleServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        foreach (\Illuminate\Support\Facades\File::allFiles(__DIR__.'/../database/migrations') as $migration) {
            (include $migration->getRealPath())->up();
        }
    }
}
