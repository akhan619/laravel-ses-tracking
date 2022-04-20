<?php

namespace Akhan619\LaravelSesTracking\Tests\Unit;

use Akhan619\LaravelSesTracking\App\Implementations\SubscriptionManager;
use Akhan619\LaravelSesTracking\Console\Commands\SetupTrackingCommand;
use Akhan619\LaravelSesTracking\LaravelSesTrackingServiceProvider;
use Akhan619\LaravelSesTracking\Tests\UnitTestCase;
use \Mockery;

class SubscriptionManagerTest extends UnitTestCase
{
    protected const debug = true;

    protected function setUp(): void
    {
        parent::setUp();

        if (config(LaravelSesTrackingServiceProvider::$configName.'.debug') === false) {
            // Code should not reach this point in tests. If they do, something is wrong somewhere.
            $this->markTestSkipped('Skipping all tests as debug mode is disabled.');
        }
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function subscriptionManagerCanGetSubscriptionData()
    {
        $obj = new SubscriptionManager(LaravelSesTrackingServiceProvider::$configName);

        $this->assertCount(2, $obj->getSubscriptionData());
    }

    protected function setDataInConfig($app)
    {
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName.'.subscriber', [
            'http'  => true,
            'https' => false,
        ]);

        $app['config']->set(LaravelSesTrackingServiceProvider::$configName.'.active', [
            'sends'              => true,
            'rendering_failures' => false,
            'rejects'            => false,
            'deliveries'         => true,
            'bounces'            => true,
            'complaints'         => false,
            'delivery_delays'    => false,
            'subscriptions'      => true,
            'opens'              => false,
            'clicks'             => true,
        ]);
    }

    /**
     * @test
     * @define-env setDataInConfig
     */
    public function returnedDataIsCorrect()
    {
        $obj = new SubscriptionManager(LaravelSesTrackingServiceProvider::$configName);
        $result = $obj->getSubscriptionData();

        $this->assertEquals('http', $result[0]);
        $this->assertEquals([
            'sends'              => true,
            'deliveries'         => true,
            'bounces'            => true,
            'subscriptions'      => true,
            'clicks'             => true,
        ], $result[1]);
    }

    protected function setCorrectValuesForValidation($app)
    {
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName.'.subscriber', [
            'http'  => false,
            'https' => true,
        ]);

        $app['config']->set(LaravelSesTrackingServiceProvider::$configName.'.active', [
            'sends'              => true,
            'rendering_failures' => true,
            'rejects'            => true,
            'deliveries'         => true,
            'bounces'            => true,
            'complaints'         => false,
            'delivery_delays'    => false,
            'subscriptions'      => false,
            'opens'              => false,
            'clicks'             => false,
        ]);
    }

    /**
     * @test
     * @define-env setCorrectValuesForValidation
     */
    public function validationsWorkCorrectlyWithTheRightData()
    {
        $obj = new SubscriptionManager(LaravelSesTrackingServiceProvider::$configName);
        $obj->getSubscriptionData();

        $this->assertTrue($obj->validateEnabledEvents());
        $this->assertTrue($obj->validateEnabledSubscriber());

        $console = Mockery::mock(SetupTrackingCommand::class);
        $console->shouldReceive('getIo->success')->once();

        $obj->validateForCli($console);
    }

    protected function setIncorrectValuesForValidation($app)
    {
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName.'.subscriber', [
            'http'  => true,
            'https' => true,
        ]);

        $app['config']->set(LaravelSesTrackingServiceProvider::$configName.'.active', [
            'sends'              => false,
            'rendering_failures' => false,
            'rejects'            => false,
            'deliveries'         => false,
            'bounces'            => false,
            'complaints'         => false,
            'delivery_delays'    => false,
            'subscriptions'      => false,
            'opens'              => false,
            'clicks'             => false,
        ]);
    }

    /**
     * @test
     * @define-env setIncorrectValuesForValidation
     */
    public function validationsWorkCorrectlyWithTheWrongData()
    {
        $obj = new SubscriptionManager(LaravelSesTrackingServiceProvider::$configName);
        $obj->getSubscriptionData();

        $this->assertFalse($obj->validateEnabledEvents());
        $this->assertFalse($obj->validateEnabledSubscriber());

        $console = Mockery::mock(SetupTrackingCommand::class);
        $console->shouldReceive('getIo->error')->times(3);

        $obj->validateForCli($console);
    }
}
