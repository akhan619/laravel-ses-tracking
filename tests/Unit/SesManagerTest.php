<?php

namespace Akhan619\LaravelSesTracking\Tests\Unit;

use Akhan619\LaravelSesTracking\App\Contracts\AwsCredentialsContract;
use Akhan619\LaravelSesTracking\App\Contracts\SesDataContract;
use Akhan619\LaravelSesTracking\App\SesManager;
use Akhan619\LaravelSesTracking\App\SnsManager;
use Akhan619\LaravelSesTracking\Console\Commands\SetupTrackingCommand;
use Akhan619\LaravelSesTracking\LaravelSesTrackingServiceProvider;
use Akhan619\LaravelSesTracking\Tests\UnitTestCase;
use Mockery;

class SesManagerTest extends UnitTestCase
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
    public function sesManagerCanBeInitialized()
    {
        $aws = Mockery::mock(AwsCredentialsContract::class);
        $aws->shouldReceive([
            'getAwsAccessKeyId'     => 'someId',
            'getAwsSecretAccessKey' => 'someKey',
            'getAwsDefaultRegion'   => 'us-east-1',
        ])
        ->once();

        $dataMgr = Mockery::mock(SesDataContract::class);
        $console = Mockery::mock(SetupTrackingCommand::class);

        $sesMgr = new SesManager($aws, $dataMgr, self::debug, $console);
        $this->assertTrue($sesMgr instanceof SesManager);
    }

    /**
     * @test
     */
    public function sesManagerCreateConfigurationSetMethodPrintsCorrectlyInDebugMode()
    {
        $aws = Mockery::mock(AwsCredentialsContract::class);
        $aws->shouldReceive([
            'getAwsAccessKeyId'     => 'someId',
            'getAwsSecretAccessKey' => 'someKey',
            'getAwsDefaultRegion'   => 'us-east-1',
        ])
        ->once();

        $dataMgr = Mockery::mock(SesDataContract::class);
        $dataMgr->shouldReceive('getConfigurationSet')
        ->once();

        $console = Mockery::mock(SetupTrackingCommand::class);

        $sesMgr = Mockery::mock(SesManager::class, [$aws, $dataMgr, true, $console])->makePartial()->shouldAllowMockingProtectedMethods();
        $sesMgr->shouldReceive('prettyPrintArray')
        ->once();

        $sesMgr->createConfigurationSet();

        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function sesManagerCreateConfigurationSetMethodWorksCorrectlyInLiveMode()
    {
        $aws = Mockery::mock(AwsCredentialsContract::class);
        $aws->shouldReceive([
            'getAwsAccessKeyId'     => 'someId',
            'getAwsSecretAccessKey' => 'someKey',
            'getAwsDefaultRegion'   => 'us-east-1',
        ])
        ->once();

        $dataMgr = Mockery::mock(SesDataContract::class);

        $console = Mockery::mock(SetupTrackingCommand::class);
        $console->shouldReceive('getIo->success')
        ->once();

        $sesMgr = Mockery::mock(SesManager::class, [$aws, $dataMgr, false, $console])->makePartial()->shouldAllowMockingProtectedMethods();
        $sesMgr->shouldReceive('sendCreationRequest')
        ->once();

        $sesMgr->createConfigurationSet();

        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function sesManagerConfirmNamingConventionPrintsCorrectly()
    {
        $aws = Mockery::mock(AwsCredentialsContract::class);
        $aws->shouldReceive([
            'getAwsAccessKeyId'     => 'someId',
            'getAwsSecretAccessKey' => 'someKey',
            'getAwsDefaultRegion'   => 'us-east-1',
        ])
        ->once();

        $dataMgr = Mockery::mock(SesDataContract::class);
        $dataMgr->shouldReceive('getDestinationNames')
        ->once()
        ->andReturn(['sends'    =>  'sns']);

        $dataMgr->shouldReceive('getEventDestinationSuffix')
        ->andReturn('us-east-1');

        $dataMgr->shouldReceive('getTopicNameAsSuffix')
        ->andReturn(true);

        $dataMgr->shouldReceive('getEventDestinationPrefix')
        ->andReturn('destination');

        $console = Mockery::mock(SetupTrackingCommand::class);
        $console->shouldReceive('getIo->table')
        ->once()
        ->with(['Event', 'Event Destination Name'], [
            ['sends', 'destination-sns-sends'],
        ]);

        $console->shouldReceive('info')
        ->once();

        $console->shouldReceive('newLine')
        ->once();

        $sesMgr = new SesManager($aws, $dataMgr, true, $console);
        $sesMgr->confirmNamingConvention(['sends'   => true], ['sends'  => 'sends']);

        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function sesManagerConfirmNamingConventionThrowsExceptionOnEmptyName()
    {
        $aws = Mockery::mock(AwsCredentialsContract::class);
        $aws->shouldReceive([
            'getAwsAccessKeyId'     => 'someId',
            'getAwsSecretAccessKey' => 'someKey',
            'getAwsDefaultRegion'   => 'us-east-1',
        ])
        ->once();

        $dataMgr = Mockery::mock(SesDataContract::class);
        $dataMgr->shouldReceive('getDestinationNames')
        ->once()
        ->andReturn(['sends'    =>  null]);

        $dataMgr->shouldReceive('getEventDestinationSuffix')
        ->andReturn(null);

        $dataMgr->shouldReceive('getTopicNameAsSuffix')
        ->andReturn(false);

        $dataMgr->shouldReceive('getEventDestinationPrefix')
        ->andReturn(null);

        $console = Mockery::mock(SetupTrackingCommand::class);
        $console->shouldNotReceive('getIo->table');

        $console->shouldReceive('info')
        ->once();

        $console->shouldReceive('newLine')
        ->once();

        $sesMgr = new SesManager($aws, $dataMgr, true, $console);

        $hasThrown = false;

        try {
            $sesMgr->confirmNamingConvention(['sends'   => true], ['sends'  => 'sends']);
        } catch (\Throwable $th) {
            $hasThrown = true;
        }

        $this->assertTrue($hasThrown);
    }

    /**
     * @test
     */
    public function sesManagerCreateEventDestinationPrintsCorrectlyInDebugMode()
    {
        $aws = Mockery::mock(AwsCredentialsContract::class);
        $aws->shouldReceive([
            'getAwsAccessKeyId'     => 'someId',
            'getAwsSecretAccessKey' => 'someKey',
            'getAwsDefaultRegion'   => 'us-east-1',
        ])
        ->once();

        $dataMgr = Mockery::mock(SesDataContract::class);
        $dataMgr->shouldReceive('getConfigurationSetName')
        ->once()
        ->andReturn('Test-Set-1');

        $dataMgr->shouldReceive('getDestinationNames')
        ->once()
        ->andReturn(['sends'    =>  'sns']);

        $dataMgr->shouldReceive('getEventDestinationSuffix')
        ->andReturn('us-east-1');

        $dataMgr->shouldReceive('getTopicNameAsSuffix')
        ->andReturn(true);

        $dataMgr->shouldReceive('getEventDestinationPrefix')
        ->andReturn('destination');

        $snsMgr = Mockery::mock(SnsManager::class);

        $console = Mockery::mock(SetupTrackingCommand::class);
        $console->shouldReceive('getIo->table')
        ->once()
        ->with(['Event', 'Event Destination Name'], [
            ['sends', 'destination-sns-sends'],
        ]);

        $console->shouldReceive('info')
        ->once();

        $console->shouldReceive('newLine')
        ->once();

        $sesMgr = Mockery::mock(SesManager::class, [$aws, $dataMgr, true, $console])
        ->makePartial()
        ->shouldAllowMockingProtectedMethods();

        $sesMgr->shouldReceive('prettyPrintArray')
        ->with([
            'ConfigurationSetName' => 'Test-Set-1',
            'EventDestination'     => [
                'Enabled'            => true,
                'MatchingEventTypes' => ['SEND'],
                'SnsDestination'     => [
                    'TopicArn' => 'Not Available in Debug Mode',
                ],
            ],
            'EventDestinationName' => 'destination-sns-sends',
        ], 'SES Event Destination Configuration Data')
        ->once();

        $sesMgr->confirmNamingConvention(['sends'   => true], ['sends'  => 'sends']);
        $sesMgr->createSesEventDestinations($snsMgr);

        $this->assertTrue(true);
    }
}
