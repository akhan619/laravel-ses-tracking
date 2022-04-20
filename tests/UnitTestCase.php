<?php

namespace Akhan619\LaravelSesTracking\Tests;

use Akhan619\LaravelSesTracking\LaravelSesTrackingServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class UnitTestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        // Code before application created.

        parent::setUp();

        // Code after application created.
        $this->withoutExceptionHandling();
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelSesTrackingServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName.'.debug', true);
    }
}
