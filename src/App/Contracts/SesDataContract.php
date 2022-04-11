<?php

namespace Akhan619\LaravelSesTracking\App\Contracts;

interface SesDataContract {

    /**
    *   Return the ses data:
    *   eventDestinationPrefix
    *   destinationNames
    *   eventDestinationSuffix
    *   topicNameAsSuffix
    *   ConfigurationSetName
    *   SendingPoolName
    *   TlsPolicy
    *   LastFreshStart
    *   ReputationMetricsEnabled
    *   SendingEnabled
    *   SuppressedReasons
    *   Tags
    *   CustomRedirectDomain
    *
    * @return array
    */
    public function getSesData() : array;

    /**
    *   Validate the ses data.
    *
    * @return bool
    */
    public function validateSesData() : bool;

    /**
    *   Validate the configuration set.
    *
    * @return bool
    */
    public function validateConfigurationSet() : bool;

    /**
    *   Validate the event destination prefix.
    *
    * @return bool
    */
    public function validateEventDestinationPrefix() : bool;

    /**
    *   Validate the destination names.
    *
    * @return bool
    */
    public function validateDestinationNames() : bool;

    /**
    *   Validate the event destination suffix.
    *
    * @return bool
    */
    public function validateEventDestinationSuffix() : bool;

    /**
    *   Validate the topic name as suffix.
    *
    * @return bool
    */
    public function validateTopicNameAsSuffix() : bool;

    /**
    *   Validate the ConfigurationSetName.
    *
    * @return bool
    */
    public function validateConfigurationSetName() : bool;

    /**
    *   Validate the SendingPoolName.
    *
    * @return bool
    */
    public function validateSendingPoolName() : bool;

    /**
    *   Validate the TlsPolicy.
    *
    * @return bool
    */
    public function validateTlsPolicy() : bool;

    /**
    *   Validate the LastFreshStart.
    *
    * @return bool
    */
    public function validateLastFreshStart() : bool;

    /**
    *   Validate the ReputationMetricsEnabled.
    *
    * @return bool
    */
    public function validateReputationMetricsEnabled() : bool;

    /**
    *   Validate the SendingEnabled.
    *
    * @return bool
    */
    public function validateSendingEnabled() : bool;

    /**
    *   Validate the SuppressedReasons.
    *
    * @return bool
    */
    public function validateSuppressedReasons() : bool;

    /**
    *   Validate the Tags.
    *
    * @return bool
    */
    public function validateTags() : bool;

    /**
    *   Validate the CustomRedirectDomain.
    *
    * @return bool
    */
    public function validateCustomRedirectDomain() : bool;

    /**
    *   Return the configuration set.
    *
    * @return array
    */
    public function getConfigurationSet() : array;

    /**
    *   Return the event destination prefix.
    *
    * @return string|null
    */
    public function getEventDestinationPrefix() :  string|null;

    /**
    *   Return the destination names.
    *
    * @return array
    */
    public function getDestinationNames() : array;

    /**
    *   Return the event destination suffix.
    *
    * @return string|null
    */
    public function getEventDestinationSuffix() : string|null;

    /**
    *   Return the topic name as suffix.
    *
    * @return bool
    */
    public function getTopicNameAsSuffix() : bool;

    /**
    *   Return the ConfigurationSetName.
    *
    * @return string
    */
    public function getConfigurationSetName() : string;

    /**
    *   Return the SendingPoolName.
    *
    * @return string|null
    */
    public function getSendingPoolName() : string|null;

    /**
    *   Return the TlsPolicy.
    *
    * @return string
    */
    public function getTlsPolicy() : string;

    /**
    *   Return the LastFreshStart.
    *
    * @return string|null
    */
    public function getLastFreshStart() : string|null;

    /**
    *   Return the ReputationMetricsEnabled.
    *
    * @return bool
    */
    public function getReputationMetricsEnabled() : bool;

    /**
    *   Return the SendingEnabled.
    *
    * @return bool
    */
    public function getSendingEnabled() : bool;

    /**
    *   Return the SuppressedReasons.
    *
    * @return array
    */
    public function getSuppressedReasons() : array;

    /**
    *   Return the Tags.
    *
    * @return array
    */
    public function getTags() : array;

    /**
    *   Return the CustomRedirectDomain.
    *
    * @return string|null
    */
    public function getCustomRedirectDomain() : string|null;
}