<?php

namespace Akhan619\LaravelSesTracking\Tests\Unit;

use Akhan619\LaravelSesTracking\App\Implementations\SesDataManager;
use Akhan619\LaravelSesTracking\Tests\UnitTestCase;
use Akhan619\LaravelSesTracking\LaravelSesTrackingServiceProvider;

class SesDataManagerTest extends UnitTestCase
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

    /**
     * @test
     */
    public function sesDataManagerCanGetData()
    {
        $obj = new SesDataManager(LaravelSesTrackingServiceProvider::$configName);

        $this->assertCount(13, $obj->getSesData());
    }    

    protected function setDataInConfig($app)
    {
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.event_destination_prefix', 'val1');
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.destination_names', [
            'sends'              => 'sns',
            'rendering_failures' => 'sns',
            'rejects'            => 'sns',
            'deliveries'         => 'sns',
            'bounces'            => 'sns',
            'complaints'         => 'sns',
            'delivery_delays'    => 'sns',
            'subscriptions'      => 'sns',
            'opens'              => 'sns',
            'clicks'             => 'sns',
        ]);
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.event_destination_suffix', 'val2');
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.topic_name_as_suffix', true);
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.configuration_set.ConfigurationSetName', 'testing-101');
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.configuration_set.DeliveryOptions.SendingPoolName', null);
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.configuration_set.DeliveryOptions.TlsPolicy', 'REQUIRE');
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.configuration_set.ReputationOptions.LastFreshStart', null);
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.configuration_set.ReputationOptions.ReputationMetricsEnabled', false);
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.configuration_set.SendingOptions.SendingEnabled', true);
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.configuration_set.SuppressionOptions.SuppressedReasons', []);
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.configuration_set.Tags', []);
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.configuration_set.TrackingOptions.CustomRedirectDomain', null);
    }

    /**
     * @test
     * @define-env setDataInConfig
     */
    public function returnedDataIsCorrect()
    {
        $obj = new SesDataManager(LaravelSesTrackingServiceProvider::$configName);
        $result = $obj->getSesData();

        $this->assertEquals([
            'val1',
            [
                'sends'              => 'sns',
                'rendering_failures' => 'sns',
                'rejects'            => 'sns',
                'deliveries'         => 'sns',
                'bounces'            => 'sns',
                'complaints'         => 'sns',
                'delivery_delays'    => 'sns',
                'subscriptions'      => 'sns',
                'opens'              => 'sns',
                'clicks'             => 'sns',
            ],
            'val2',
            true,
            'testing-101',
            null,
            'REQUIRE',
            null,
            false,
            true,
            [],
            [],
            null,
        ], $result);
    }    

    protected function setCorrectValuesForValidation($app)
    {
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.event_destination_prefix', 'val1');
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.destination_names', [
            'sends'              => 'sns',
            'rendering_failures' => 'sns',
            'rejects'            => 'sns',
            'deliveries'         => 'sns',
            'bounces'            => 'sns',
            'complaints'         => 'sns',
            'delivery_delays'    => 'sns',
            'subscriptions'      => 'sns',
            'opens'              => 'sns',
            'clicks'             => 'sns',
        ]);
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.event_destination_suffix', 'val2');
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.topic_name_as_suffix', true);
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.configuration_set.ConfigurationSetName', 'testing-101');
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.configuration_set.DeliveryOptions.SendingPoolName', null);
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.configuration_set.DeliveryOptions.TlsPolicy', 'REQUIRE');
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.configuration_set.ReputationOptions.LastFreshStart', null);
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.configuration_set.ReputationOptions.ReputationMetricsEnabled', false);
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.configuration_set.SendingOptions.SendingEnabled', true);
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.configuration_set.SuppressionOptions.SuppressedReasons', []);
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.configuration_set.Tags', []);
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.configuration_set.TrackingOptions.CustomRedirectDomain', null);
    }

    /**
     * @test
     * @define-env setCorrectValuesForValidation
     */
    public function validationsWorkCorrectlyWithTheRightData()
    {
        $obj = new SesDataManager(LaravelSesTrackingServiceProvider::$configName);
        $obj->getSesData();
        
        $this->assertTrue($obj->validateEventDestinationPrefix());
        $this->assertTrue($obj->validateDestinationNames());
        $this->assertTrue($obj->validateEventDestinationSuffix());
        $this->assertTrue($obj->validateTopicNameAsSuffix());
        $this->assertTrue($obj->validateConfigurationSetName());
        $this->assertTrue($obj->validateSendingPoolName());
        $this->assertTrue($obj->validateTlsPolicy());
        $this->assertTrue($obj->validateLastFreshStart());
        $this->assertTrue($obj->validateReputationMetricsEnabled());
        $this->assertTrue($obj->validateSendingEnabled());
        $this->assertTrue($obj->validateSuppressedReasons());
        $this->assertTrue($obj->validateTags());
        $this->assertTrue($obj->validateCustomRedirectDomain());
    }      

    protected function setIncorrectValuesForValidation($app)
    {
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.event_destination_prefix', '');
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.destination_names', [
            'sends'              => 'sns',
            'rendering_failures' => 'sns',
            'rejects'            => 'sns',
            'deliveries'         => '',
            'bounces'            => 'sns',
            'complaints'         => '',
            'delivery_delays'    => 'sns',
            'subscriptions'      => 'sns',
            'opens'              => 'sns',
            'clicks'             => 'sns',
        ]);
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.event_destination_suffix', '');
        // $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.topic_name_as_suffix', true);
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.configuration_set.ConfigurationSetName', '');
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.configuration_set.DeliveryOptions.SendingPoolName', '');
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.configuration_set.DeliveryOptions.TlsPolicy', 'BATMAN');
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.configuration_set.ReputationOptions.LastFreshStart', '');
        // $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.configuration_set.ReputationOptions.ReputationMetricsEnabled', false);
        // $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.configuration_set.SendingOptions.SendingEnabled', true);
        // $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.configuration_set.SuppressionOptions.SuppressedReasons', []);
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.configuration_set.Tags', ['Random Value']);
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.configuration_set.TrackingOptions.CustomRedirectDomain', '');
    }

    /**
     * @test
     * @define-env setIncorrectValuesForValidation
     */
    public function validationsWorkCorrectlyWithTheWrongData()
    {
        $obj = new SesDataManager(LaravelSesTrackingServiceProvider::$configName);
        $obj->getSesData();
        
        $this->assertFalse($obj->validateEventDestinationPrefix());
        $this->assertFalse($obj->validateDestinationNames());
        $this->assertFalse($obj->validateEventDestinationSuffix());
        // $this->assertFalse($obj->validateTopicNameAsSuffix());
        $this->assertFalse($obj->validateConfigurationSetName());
        $this->assertFalse($obj->validateSendingPoolName());
        $this->assertFalse($obj->validateTlsPolicy());
        $this->assertFalse($obj->validateLastFreshStart());
        // $this->assertFalse($obj->validateReputationMetricsEnabled());
        // $this->assertFalse($obj->validateSendingEnabled());
        // $this->assertFalse($obj->validateSuppressedReasons());
        $this->assertFalse($obj->validateTags());
        $this->assertFalse($obj->validateCustomRedirectDomain());
    }  
}