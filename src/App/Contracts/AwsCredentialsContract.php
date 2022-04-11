<?php

namespace Akhan619\LaravelSesTracking\App\Contracts;

interface AwsCredentialsContract {
    
    /**
    *   Return the AWS credentials consisting of:
    *   AWS_ACCESS_KEY_ID
    *   AWS_SECRET_ACCESS_KEY
    *   AWS_DEFAULT_REGION
    *
    * @return array
    */
    public function getAwsCredentials() : array;

    /**
    *   Validate the AWS credentials.
    *
    * @return bool
    */
    public function validateAwsCredentials() : bool;

    /**
    *   Validate the AWS_ACCESS_KEY_ID.
    *
    * @return bool
    */
    public function validateAwsAccessKeyId() : bool;

    /**
    *   Validate the AWS_SECRET_ACCESS_KEY.
    *
    * @return bool
    */
    public function validateAwsSecretAccessKey() : bool;

    /**
    *   Validate the AWS_DEFAULT_REGION.
    *
    * @return bool
    */
    public function validateAwsDefaultRegion() : bool;

    /**
    *   Return the AWS_ACCESS_KEY_ID
    *
    * @return string
    */
    public function getAwsAccessKeyId() : string;

    /**
    *   Return the AWS_SECRET_ACCESS_KEY
    *
    * @return string
    */
    public function getAwsSecretAccessKey() : string;

    /**
    *   Return the AWS_DEFAULT_REGION
    *
    * @return string
    */
    public function getAwsDefaultRegion() : string;
}