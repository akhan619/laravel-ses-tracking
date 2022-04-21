<?php

namespace Akhan619\LaravelSesTracking\Tests\Unit;

use Akhan619\LaravelSesTracking\App\Implementations\SnsDataManager;
use Akhan619\LaravelSesTracking\Console\Commands\SetupTrackingCommand;
use Akhan619\LaravelSesTracking\LaravelSesTrackingServiceProvider;
use Akhan619\LaravelSesTracking\Tests\UnitTestCase;
use Mockery;

class SnsDataManagerTest extends UnitTestCase
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
    public function snsDataManagerCanGetData()
    {
        $obj = new SnsDataManager(LaravelSesTrackingServiceProvider::$configName);

        $this->assertCount(12, $obj->getSnsData());
    }

    protected function setDataInConfig($app)
    {
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName.'.topic_name_prefix', 'val1');
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName.'.topic_names', [
            'sends'              => 'sends',
            'rendering_failures' => 'rendering-failures',
            'rejects'            => 'rejects',
            'deliveries'         => 'deliveries',
            'bounces'            => 'bounces',
            'complaints'         => 'complaints',
            'delivery_delays'    => 'delivery-delays',
            'subscriptions'      => 'subscriptions',
            'opens'              => 'opens',
            'clicks'             => 'clicks',
        ]);
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName.'.topic_name_suffix', 'val2');
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName.'.sns_topic_configuration_data.DeliveryPolicy', []);
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName.'.sns_topic_configuration_data.Policy', []);
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName.'.sns_topic_configuration_data.KmsMasterKeyId', null);
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName.'.sns_topic_configuration_data.Tags', []);
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName.'.sns_subscription_configuration_data.ReturnSubscriptionArn', false);
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName.'.sns_subscription_configuration_data.DeliveryPolicy', []);
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName.'.sns_subscription_configuration_data.FilterPolicy', []);
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName.'.sns_subscription_configuration_data.RawMessageDelivery', 'false');
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName.'.sns_subscription_configuration_data.RedrivePolicy', null);
    }

    /**
     * @test
     * @define-env setDataInConfig
     */
    public function returnedDataIsCorrect()
    {
        $obj = new SnsDataManager(LaravelSesTrackingServiceProvider::$configName);
        $result = $obj->getSnsData();

        $this->assertEquals([
            'val1',
            [
                'sends'              => 'sends',
                'rendering_failures' => 'rendering-failures',
                'rejects'            => 'rejects',
                'deliveries'         => 'deliveries',
                'bounces'            => 'bounces',
                'complaints'         => 'complaints',
                'delivery_delays'    => 'delivery-delays',
                'subscriptions'      => 'subscriptions',
                'opens'              => 'opens',
                'clicks'             => 'clicks',
            ],
            'val2',
            [],
            [],
            null,
            [],
            false,
            [],
            [],
            'false',
            null,
        ], $result);
    }

    protected function setCorrectValuesForValidation($app)
    {
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName.'.topic_name_prefix', 'val1');
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName.'.topic_names', [
            'sends'              => 'sends',
            'rendering_failures' => 'rendering-failures',
            'rejects'            => 'rejects',
            'deliveries'         => 'deliveries',
            'bounces'            => 'bounces',
            'complaints'         => 'complaints',
            'delivery_delays'    => 'delivery-delays',
            'subscriptions'      => 'subscriptions',
            'opens'              => 'opens',
            'clicks'             => 'clicks',
        ]);
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName.'.topic_name_suffix', 'val2');
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName.'.sns_topic_configuration_data.DeliveryPolicy', []);
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName.'.sns_topic_configuration_data.Policy', []);
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName.'.sns_topic_configuration_data.KmsMasterKeyId', null);
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName.'.sns_topic_configuration_data.Tags', []);
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName.'.sns_subscription_configuration_data.ReturnSubscriptionArn', false);
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName.'.sns_subscription_configuration_data.DeliveryPolicy', []);
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName.'.sns_subscription_configuration_data.FilterPolicy', []);
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName.'.sns_subscription_configuration_data.RawMessageDelivery', 'false');
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName.'.sns_subscription_configuration_data.RedrivePolicy', null);
    }

    /**
     * @test
     * @define-env setCorrectValuesForValidation
     */
    public function validationsWorkCorrectlyWithTheRightData()
    {
        $obj = new SnsDataManager(LaravelSesTrackingServiceProvider::$configName);
        $obj->getSnsData();

        $this->assertTrue($obj->validateTopicNamePrefix());
        $this->assertTrue($obj->validateTopicNames());
        $this->assertTrue($obj->validateTopicNameSuffix());
        $this->assertTrue($obj->validateDeliveryPolicyTopic());
        $this->assertTrue($obj->validatePolicy());
        $this->assertTrue($obj->validateKmsMasterKeyId());
        $this->assertTrue($obj->validateTags());
        $this->assertTrue($obj->validateReturnSubscriptionArn());
        $this->assertTrue($obj->validateDeliveryPolicySubscription());
        $this->assertTrue($obj->validateFilterPolicy());
        $this->assertTrue($obj->validateRawMessageDelivery());
        $this->assertTrue($obj->validateRedrivePolicy());

        $console = Mockery::mock(SetupTrackingCommand::class);
        $console->shouldReceive('getIo->success')->once();

        $obj->validateForCli($console);
    }

    protected function setIncorrectValuesForValidation($app)
    {
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName.'.topic_name_prefix', '');
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName.'.topic_names', [
            'sends'              => '',
            'rendering_failures' => 'rendering-failures',
            'rejects'            => '',
            'deliveries'         => 'deliveries',
            'bounces'            => 'bounces',
            'complaints'         => 'complaints',
            'delivery_delays'    => 'delivery-delays',
            'subscriptions'      => '',
            'opens'              => 'opens',
            'clicks'             => 'clicks',
        ]);
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName.'.topic_name_suffix', '');
        // $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.sns_topic_configuration_data.DeliveryPolicy', []);
        // $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.sns_topic_configuration_data.Policy', []);
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName.'.sns_topic_configuration_data.KmsMasterKeyId', '');
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName.'.sns_topic_configuration_data.Tags', ['Random Value']);
        // $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.sns_subscription_configuration_data.ReturnSubscriptionArn', false);
        // $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.sns_subscription_configuration_data.DeliveryPolicy', []);
        // $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.sns_subscription_configuration_data.FilterPolicy', []);
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName.'.sns_subscription_configuration_data.RawMessageDelivery', 'JOKER');
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName.'.sns_subscription_configuration_data.RedrivePolicy', '');
    }

    /**
     * @test
     * @define-env setIncorrectValuesForValidation
     */
    public function validationsWorkCorrectlyWithTheWrongData()
    {
        $obj = new SnsDataManager(LaravelSesTrackingServiceProvider::$configName);
        $obj->getSnsData();

        $this->assertFalse($obj->validateTopicNamePrefix());
        $this->assertFalse($obj->validateTopicNames());
        $this->assertFalse($obj->validateTopicNameSuffix());
        // $this->assertFalse($obj->validateDeliveryPolicyTopic());
        // $this->assertFalse($obj->validatePolicy());
        $this->assertFalse($obj->validateKmsMasterKeyId());
        $this->assertFalse($obj->validateTags());
        // $this->assertFalse($obj->validateReturnSubscriptionArn());
        // $this->assertFalse($obj->validateDeliveryPolicySubscription());
        // $this->assertFalse($obj->validateFilterPolicy());
        $this->assertFalse($obj->validateRawMessageDelivery());
        $this->assertFalse($obj->validateRedrivePolicy());

        $console = Mockery::mock(SetupTrackingCommand::class);
        $console->shouldReceive('getIo->error')->times(8);

        $obj->validateForCli($console);
    }

    protected function setValuesForTopicConfigurationDataTest($app)
    {
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName.'.sns_topic_configuration_data.DeliveryPolicy', ['TWO-FACE']);
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName.'.sns_topic_configuration_data.Policy', ['ROBIN']);
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName.'.sns_topic_configuration_data.KmsMasterKeyId', 'BANE');
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName.'.sns_topic_configuration_data.Tags', [['Key' => 'Key1', 'Value' => 'Value1']]);
    }

    /**
     * @test
     * @define-env setValuesForTopicConfigurationDataTest
     */
    public function topicConfigurationDataIsSetCorrectly()
    {
        $obj = new SnsDataManager(LaravelSesTrackingServiceProvider::$configName);
        $obj->getSnsData();
        $topicData = $obj->getTopicConfigurationData();

        $this->assertEquals($topicData, [
            'Attributes'    => [
                'DeliveryPolicy'    => json_encode(['TWO-FACE']),
                'Policy'            => json_encode(['ROBIN']),
                'KmsMasterKeyId'    => 'BANE',
                'Tags'              => [['Key' => 'Key1', 'Value' => 'Value1']],
            ],
        ]);
    }

    protected function setValuesForSubscriptionConfigurationDataTest($app)
    {
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName.'.sns_subscription_configuration_data.ReturnSubscriptionArn', true);
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName.'.sns_subscription_configuration_data.DeliveryPolicy', ['DRMANHATTAN']);
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName.'.sns_subscription_configuration_data.FilterPolicy', ['OZZYMANDIAS']);
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName.'.sns_subscription_configuration_data.RawMessageDelivery', 'true');
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName.'.sns_subscription_configuration_data.RedrivePolicy', 'COMEDIAN');
    }

    /**
     * @test
     * @define-env setValuesForSubscriptionConfigurationDataTest
     */
    public function subscriptionConfigurationDataIsSetCorrectly()
    {
        $obj = new SnsDataManager(LaravelSesTrackingServiceProvider::$configName);
        $obj->getSnsData();
        $subsData = $obj->getSubscriptionConfigurationData();

        $this->assertEquals($subsData, [
            'Attributes'    => [
                'DeliveryPolicy'        => json_encode(['DRMANHATTAN']),
                'FilterPolicy'          => json_encode(['OZZYMANDIAS']),
                'RawMessageDelivery'    => 'true',
                'RedrivePolicy'         => 'COMEDIAN',
            ],
            'ReturnSubscriptionArn'     => true,
        ]);
    }
}
