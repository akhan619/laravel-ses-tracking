<?php

declare(strict_types = 1);

namespace Akhan619\LaravelSesTracking\App\Implementations;

use Akhan619\LaravelSesTracking\App\Contracts\AwsCredentialsContract;
use Akhan619\LaravelSesTracking\Console\Commands\SetupTrackingCommand;

class AwsCredentialsManager implements AwsCredentialsContract 
{
    protected string $awsKey;
    protected string $awsSecret;
    protected string $awsRegion;
    protected string $configName;

    public function __construct(string $configName) 
    {
        $this->configName = $configName;
    }

    /**
    *   Return the AWS credentials consisting of:
    *   AWS_ACCESS_KEY_ID
    *   AWS_SECRET_ACCESS_KEY
    *   AWS_DEFAULT_REGION
    *
    * @return array
    */
    public function getAwsCredentials() : array
    {
        $this->awsKey = config($this->configName. '.ses.key') ?? '';
        $this->awsSecret = config($this->configName . '.ses.secret') ?? '';
        $this->awsRegion = config($this->configName . '.ses.region') ?? '';

        return [
            $this->awsKey,
            $this->awsSecret,
            $this->awsRegion
        ];
    }

    /**
    *   Validate the AWS credentials.
    *
    * @return bool
    */
    public function validateAwsCredentials() : bool
    {
        return $this->validateAwsAccessKeyId() && $this->validateAwsSecretAccessKey() && $this->validateAwsDefaultRegion();
    }

    /**
    *   Validate the AWS_ACCESS_KEY_ID.
    *
    * @return bool
    */
    public function validateAwsAccessKeyId() : bool
    {
        return !empty($this->awsKey) && is_string($this->awsKey);
    }

    /**
    *   Validate the AWS_SECRET_ACCESS_KEY.
    *
    * @return bool
    */
    public function validateAwsSecretAccessKey() : bool
    {
        return !empty($this->awsSecret) && is_string($this->awsSecret);
    }

    /**
    *   Validate the AWS_DEFAULT_REGION.
    *
    * @return bool
    */
    public function validateAwsDefaultRegion() : bool
    {
        return !empty($this->awsRegion) && is_string($this->awsRegion);
    }

    /**
    *   Return the AWS_ACCESS_KEY_ID
    *
    * @return string
    */
    public function getAwsAccessKeyId() : string
    {
        return $this->awsKey;
    }

    /**
    *   Return the AWS_SECRET_ACCESS_KEY
    *
    * @return string
    */
    public function getAwsSecretAccessKey() : string
    {
        return $this->awsSecret;
    }

    /**
    *   Return the AWS_DEFAULT_REGION
    *
    * @return string
    */
    public function getAwsDefaultRegion() : string
    {
        return $this->awsRegion;
    }

    /**
    * Validate the AWS credentials for the CLI call.
    *
    * @return bool  
    */
    public function validateForCli(SetupTrackingCommand $console) : bool
    { 
        $checkPassed = tap($this->validateAwsAccessKeyId(), function($condition) use ($console) {
            $this->validate('AWS_ACCESS_KEY_ID is missing.', $condition, $console);
        });

        $checkPassed = tap($this->validateAwsSecretAccessKey(), function($condition) use ($console) {
            $this->validate('AWS_SECRET_ACCESS_KEY is missing.', $condition, $console);
        }) && $checkPassed;

        $checkPassed = tap($this->validateAwsDefaultRegion(), function($condition) use ($console) {
            $this->validate('AWS_DEFAULT_REGION is missing.', $condition, $console);
        }) && $checkPassed;

        if(!$checkPassed) {
            $console->io->error('Please specify the AWS details in the ' . $this->configName . ' config file or in the environment file.');
        } else {
            $console->io->success('AWS Credentials Check');
        }

        return $checkPassed;
    }    

    /**
    * Validate and print any errors for the passed in condition.
    *
    * @return void
    */
    protected function validate(string $errorMsg, bool $condition, SetupTrackingCommand $console) : void
    {
        if(!$condition) {
            $console->io->error($errorMsg);
        }
    }
}