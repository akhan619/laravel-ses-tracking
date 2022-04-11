<?php

namespace Akhan619\LaravelSesTracking;

use Akhan619\LaravelSesTracking\App\Contracts\AwsCredentialsContract;
use Akhan619\LaravelSesTracking\App\Contracts\SesDataContract;
use Akhan619\LaravelSesTracking\App\Contracts\SnsDataContract;
use Akhan619\LaravelSesTracking\App\Contracts\SubscriptionContract;
use Akhan619\LaravelSesTracking\App\Contracts\WebhooksContract;
use Akhan619\LaravelSesTracking\App\Implementations\AwsCredentialsManager;
use Akhan619\LaravelSesTracking\App\Implementations\SesDataManager;
use Akhan619\LaravelSesTracking\App\Implementations\SnsDataManager;
use Akhan619\LaravelSesTracking\App\Implementations\SubscriptionManager;
use Akhan619\LaravelSesTracking\App\Implementations\WebhooksManager;
use Akhan619\LaravelSesTracking\Console\Commands\SetupTrackingCommand;
use Illuminate\Support\ServiceProvider;

class LaravelSesTrackingServiceProvider extends ServiceProvider
{
    protected static string $configName = 'laravel-ses-tracking';

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-ses-tracking.php', 'laravel-ses-tracking');

        if ($this->app->runningInConsole()) {
            $this->app->singleton(AwsCredentialsContract::class, function ($app) {
                return new AwsCredentialsManager(self::$configName);
            });
    
            $this->app->singleton(SubscriptionContract::class, function ($app) {
                return new SubscriptionManager(self::$configName);
            });
    
            $this->app->singleton(WebhooksContract::class, function ($app) {
                return new WebhooksManager(self::$configName, $app->make(SubscriptionContract::class));
            });
    
            $this->app->singleton(SnsDataContract::class, function ($app) {
                return new SnsDataManager(self::$configName);
            });
    
            $this->app->singleton(SesDataContract::class, function ($app) {
                return new SesDataManager(self::$configName);
            });
        }
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole(): void
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/laravel-ses-tracking.php' => config_path('laravel-ses-tracking.php'),
        ], 'config');

        // Registering package commands.
        $this->commands([
            SetupTrackingCommand::class,
        ]);
    }
}
