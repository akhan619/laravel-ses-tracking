<?php

namespace Akhan619\LaravelSesTracking\Tests\Unit;

use Akhan619\LaravelSesTracking\App\Implementations\SubscriptionManager;
use Akhan619\LaravelSesTracking\App\Implementations\WebhooksManager;
use Akhan619\LaravelSesTracking\Tests\UnitTestCase;
use Akhan619\LaravelSesTracking\LaravelSesTrackingServiceProvider;
use \Mockery;

class WebhooksManagerTest extends UnitTestCase
{
    protected const debug = true;

    protected function setUp(): void
    {
        parent::setUp();

        if(config(LaravelSesTrackingServiceProvider::$configName . '.debug') === false) {
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
    public function webhooksManagerCanGetData()
    {
        $subscriptionMgr = Mockery::mock(SubscriptionManager::class);
        $subscriptionMgr->shouldReceive('getEnabledEvents')
        ->once()
        ->andReturn([
            'sends'              => true,
            'rendering_failures' => true,
            'rejects'            => true,
            'deliveries'         => true,
        ]);

        $obj = new WebhooksManager(LaravelSesTrackingServiceProvider::$configName, $subscriptionMgr);

        $this->assertCount(5, $obj->getWebhookData());
    }    

    protected function setDataInConfig($app)
    {
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.domain', 'example.com');
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.scheme', 'https');
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.route_prefix', 'notifications');
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.routes', [
            'sends'              => 'send-101',
            'rendering_failures' => 'rendering-failures-101',
            'rejects'            => 'rejects-101',
            'deliveries'         => 'deliveries-101',
        ]);
    }

    /**
     * @test
     * @define-env setDataInConfig
     */
    public function returnedDataIsCorrect()
    {
        $subscriptionMgr = Mockery::mock(SubscriptionManager::class);
        $subscriptionMgr->shouldReceive('getEnabledEvents')
        ->once()
        ->andReturn([
            'sends'              => true,
            'rendering_failures' => true,
        ]);

        $obj = new WebhooksManager(LaravelSesTrackingServiceProvider::$configName, $subscriptionMgr);
        $result = $obj->getWebhookData();

        $this->assertEquals([
            'example.com',
            'https',
            'notifications',
            [
                'sends'              => 'send-101',
                'rendering_failures' => 'rendering-failures-101',
            ],
            [
                'sends'                 =>  'https://example.com/notifications/send-101',
                'rendering_failures'    =>  'https://example.com/notifications/rendering-failures-101',
            ]
        ], $result);
    }    

    protected function setCorrectValuesForValidation($app)
    {
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.domain', 'example.com');
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.scheme', 'https');
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.route_prefix', 'notifications');
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.routes', [
            'sends'              => 'send-101',
            'rendering_failures' => 'rendering-failures-101',
            'rejects'            => 'rejects-101',
            'deliveries'         => 'deliveries-101',
        ]);
    }

    /**
     * @test
     * @define-env setCorrectValuesForValidation
     */
    public function validationsWorkCorrectlyWithTheRightData()
    {
        $subscriptionMgr = Mockery::mock(SubscriptionManager::class);
        $subscriptionMgr->shouldReceive('getEnabledEvents')
        ->twice()
        ->andReturn([
            'sends'              => true,
            'rendering_failures' => true,
        ])
        ->shouldReceive('getEnabledSubscriber')
        ->once()
        ->andReturn('https');

        $obj = new WebhooksManager(LaravelSesTrackingServiceProvider::$configName, $subscriptionMgr);
        $obj->getWebhookData();
        
        $this->assertTrue($obj->validateDomain());
        $this->assertTrue($obj->validateScheme());
        $this->assertTrue($obj->validateRoutePrefix());
        $this->assertTrue($obj->validateDefinedRoutes());
    }      

    protected function setIncorrectValuesForValidation($app)
    {
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.domain', '');
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.scheme', 'https');
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.route_prefix', '');
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.routes', [
            'sends'              => 'send-101',
            'rendering_failures' => 'rendering-failures-101',
            'rejects'            => 'rejects-101',
            'deliveries'         => 'deliveries-101',
        ]);
    }

    /**
     * @test
     * @define-env setIncorrectValuesForValidation
     */
    public function validationsWorkCorrectlyWithTheWrongData()
    {
        $subscriptionMgr = Mockery::mock(SubscriptionManager::class);
        $subscriptionMgr->shouldReceive('getEnabledEvents')
        ->twice()
        ->andReturn([
            'sends'              => true,
            'rendering_failures' => true,
        ],
        [
            'sends'                 => true,
            'rendering_failures'    => true,
            'deliveries'            => true,
        ])
        ->shouldReceive('getEnabledSubscriber')
        ->once()
        ->andReturn('http');

        $obj = new WebhooksManager(LaravelSesTrackingServiceProvider::$configName, $subscriptionMgr);
        $obj->getWebhookData();
        
        $this->assertFalse($obj->validateDomain());
        $this->assertFalse($obj->validateScheme());
        $this->assertFalse($obj->validateRoutePrefix());
        $this->assertFalse($obj->validateDefinedRoutes());
    }  
}