<?php

namespace Akhan619\LaravelSesTracking\Tests\Unit;

use Akhan619\LaravelSesTracking\App\Contracts\AwsCredentialsContract;
use Akhan619\LaravelSesTracking\App\Contracts\SesDataContract;
use Akhan619\LaravelSesTracking\App\Contracts\SnsDataContract;
use Akhan619\LaravelSesTracking\App\Contracts\SubscriptionContract;
use Akhan619\LaravelSesTracking\App\Contracts\WebhooksContract;
use Akhan619\LaravelSesTracking\LaravelSesTrackingServiceProvider;
use Akhan619\LaravelSesTracking\Tests\UnitTestCase;

class PackageServiceProviderTest extends UnitTestCase
{
    /**
     * @test
     */
    public function awsCredentialsContractIsBound()
    {
        $this->assertTrue(app()->bound(AwsCredentialsContract::class));
    }

    /**
     * @test
     */
    public function subscriptionContractIsBound()
    {
        $this->assertTrue(app()->bound(SubscriptionContract::class));
    }

    /**
     * @test
     */
    public function webhooksContractIsBound()
    {
        $this->assertTrue(app()->bound(WebhooksContract::class));
    }

    /**
     * @test
     */
    public function snsDataContractIsBound()
    {
        $this->assertTrue(app()->bound(SnsDataContract::class));
    }

    /**
     * @test
     */
    public function sesDataContractIsBound()
    {
        $this->assertTrue(app()->bound(SesDataContract::class));
    }

    /**
     * @test
     */
    public function configurationFileNameIsCorrectlySet()
    {
        $this->assertTrue(isset(LaravelSesTrackingServiceProvider::$configName));
        $this->assertTrue(!empty(LaravelSesTrackingServiceProvider::$configName));
        $this->assertTrue(file_exists(__DIR__.'/../../config/'.LaravelSesTrackingServiceProvider::$configName.'.php'));
    }
}
