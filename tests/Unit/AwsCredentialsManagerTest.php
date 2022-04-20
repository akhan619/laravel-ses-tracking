<?php

namespace Akhan619\LaravelSesTracking\Tests\Unit;

use Akhan619\LaravelSesTracking\App\Implementations\AwsCredentialsManager;
use Akhan619\LaravelSesTracking\Tests\UnitTestCase;
use Akhan619\LaravelSesTracking\LaravelSesTrackingServiceProvider;

class AwsCredentialsManagerTest extends UnitTestCase
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
    public function awsCredentialsManagerCanGetCredentials()
    {
        $obj = new AwsCredentialsManager(LaravelSesTrackingServiceProvider::$configName);

        $this->assertCount(3, $obj->getAwsCredentials());
    }    

    protected function setCredentialsInConfig($app)
    {
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.ses.key', 'val1');
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.ses.secret', 'val2');
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.ses.region', 'val3');
    }

    /**
     * @test
     * @define-env setCredentialsInConfig
     */
    public function returnedCredentialsAreCorrect()
    {
        $obj = new AwsCredentialsManager(LaravelSesTrackingServiceProvider::$configName);
        $result = $obj->getAwsCredentials();

        $this->assertEquals(['val1', 'val2', 'val3'], $result);
    }    

    protected function setCorrectValuesForValidation($app)
    {
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.ses.key', 'val1');
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.ses.secret', 'val2');
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.ses.region', 'val3');
    }

    /**
     * @test
     * @define-env setCorrectValuesForValidation
     */
    public function validationsWorkCorrectlyWithTheRightData()
    {
        $obj = new AwsCredentialsManager(LaravelSesTrackingServiceProvider::$configName);
        $obj->getAwsCredentials();
        
        $this->assertTrue($obj->validateAwsAccessKeyId());
        $this->assertTrue($obj->validateAwsSecretAccessKey());
        $this->assertTrue($obj->validateAwsDefaultRegion());
    }      

    protected function setIncorrectValuesForValidation($app)
    {
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.ses.key', '');
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.ses.secret', '');
        $app['config']->set(LaravelSesTrackingServiceProvider::$configName . '.ses.region', null);
    }

    /**
     * @test
     * @define-env setIncorrectValuesForValidation
     */
    public function validationsWorkCorrectlyWithTheWrongData()
    {
        $obj = new AwsCredentialsManager(LaravelSesTrackingServiceProvider::$configName);
        $obj->getAwsCredentials();
        
        $this->assertFalse($obj->validateAwsAccessKeyId());
        $this->assertFalse($obj->validateAwsSecretAccessKey());
        $this->assertFalse($obj->validateAwsDefaultRegion());
    }  
}