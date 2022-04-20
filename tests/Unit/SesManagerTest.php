<?php

namespace Akhan619\LaravelSesTracking\Tests\Unit;

use Akhan619\LaravelSesTracking\App\Contracts\AwsCredentialsContract;
use Akhan619\LaravelSesTracking\App\Contracts\SesDataContract;
use Akhan619\LaravelSesTracking\App\SesManager;
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
}
