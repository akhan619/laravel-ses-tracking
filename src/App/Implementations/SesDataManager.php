<?php

declare(strict_types=1);

namespace Akhan619\LaravelSesTracking\App\Implementations;

use Akhan619\LaravelSesTracking\App\Contracts\SesDataContract;
use Akhan619\LaravelSesTracking\Console\Commands\SetupTrackingCommand;

class SesDataManager implements SesDataContract
{
    protected string $configName;

    protected ?string $eventDestinationPrefix;
    protected array $destinationNames;
    protected ?string $eventDestinationSuffix;
    protected bool $topicNameAsSuffix;
    protected string $ConfigurationSetName;
    protected ?string $SendingPoolName;
    protected string $TlsPolicy;
    protected ?string $LastFreshStart;
    protected bool $ReputationMetricsEnabled;
    protected bool $SendingEnabled;
    protected array $SuppressedReasons;
    protected array $Tags;
    protected ?string $CustomRedirectDomain;

    public function __construct(string $configName)
    {
        $this->configName = $configName;
    }

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
     *   CustomRedirectDomain.
     *
     * @return array
     */
    public function getSesData(): array
    {
        $this->eventDestinationPrefix = config($this->configName.'.event_destination_prefix');
        $this->destinationNames = config($this->configName.'.destination_names') ?? [];
        $this->eventDestinationSuffix = config($this->configName.'.event_destination_suffix');
        $this->topicNameAsSuffix = config($this->configName.'.topic_name_as_suffix');
        $this->ConfigurationSetName = config($this->configName.'.configuration_set.ConfigurationSetName');
        $this->SendingPoolName = config($this->configName.'.configuration_set.DeliveryOptions.SendingPoolName');
        $this->TlsPolicy = config($this->configName.'.configuration_set.DeliveryOptions.TlsPolicy') ?? [];
        $this->LastFreshStart = config($this->configName.'.configuration_set.ReputationOptions.LastFreshStart');
        $this->ReputationMetricsEnabled = config($this->configName.'.configuration_set.ReputationOptions.ReputationMetricsEnabled');
        $this->SendingEnabled = config($this->configName.'.configuration_set.SendingOptions.SendingEnabled');
        $this->SuppressedReasons = config($this->configName.'.configuration_set.SuppressionOptions.SuppressedReasons') ?? [];
        $this->Tags = config($this->configName.'.configuration_set.Tags') ?? [];
        $this->CustomRedirectDomain = config($this->configName.'.configuration_set.TrackingOptions.CustomRedirectDomain');

        return [
            $this->eventDestinationPrefix,
            $this->destinationNames,
            $this->eventDestinationSuffix,
            $this->topicNameAsSuffix,
            $this->ConfigurationSetName,
            $this->SendingPoolName,
            $this->TlsPolicy,
            $this->LastFreshStart,
            $this->ReputationMetricsEnabled,
            $this->SendingEnabled,
            $this->SuppressedReasons,
            $this->Tags,
            $this->CustomRedirectDomain,
        ];
    }

    /**
     *   Validate the ses data.
     *
     * @return bool
     */
    public function validateSesData(): bool
    {
        return $this->validateConfigurationSet() &&
        $this->validateEventDestinationPrefix() &&
        $this->validateDestinationNames() &&
        $this->validateEventDestinationSuffix() &&
        $this->validateTopicNameAsSuffix();
    }

    /**
     *   Validate the configuration set.
     *
     * @return bool
     */
    public function validateConfigurationSet(): bool
    {
        return $this->validateConfigurationSetName() &&
        $this->validateSendingPoolName() &&
        $this->validateTlsPolicy() &&
        $this->validateLastFreshStart() &&
        $this->validateReputationMetricsEnabled() &&
        $this->validateSendingEnabled() &&
        $this->validateSuppressedReasons() &&
        $this->validateTags() &&
        $this->validateCustomRedirectDomain();
    }

    /**
     *   Validate the event destination prefix.
     *
     * @return bool
     */
    public function validateEventDestinationPrefix(): bool
    {
        return is_null($this->eventDestinationPrefix) || (is_string($this->eventDestinationPrefix) && !empty($this->eventDestinationPrefix));
    }

    /**
     *   Validate the destination names.
     *
     * @return bool
     */
    public function validateDestinationNames(): bool
    {
        if (empty($this->destinationNames) || !is_array($this->destinationNames)) {
            return false;
        }

        $checkPassed = true;
        foreach ($this->destinationNames as $name) {
            $checkPassed = $checkPassed && (is_null($name) || (is_string($name) && !empty($name)));
        }

        return $checkPassed;
    }

    /**
     *   Validate the event destination suffix.
     *
     * @return bool
     */
    public function validateEventDestinationSuffix(): bool
    {
        return is_null($this->eventDestinationSuffix) || (is_string($this->eventDestinationSuffix) && !empty($this->eventDestinationSuffix));
    }

    /**
     *   Validate the topic name as suffix.
     *
     * @return bool
     */
    public function validateTopicNameAsSuffix(): bool
    {
        return isset($this->topicNameAsSuffix) && is_bool($this->topicNameAsSuffix);
    }

    /**
     *   Validate the ConfigurationSetName.
     *
     * @return bool
     */
    public function validateConfigurationSetName(): bool
    {
        return is_string($this->ConfigurationSetName) && !empty($this->ConfigurationSetName);
    }

    /**
     *   Validate the SendingPoolName.
     *
     * @return bool
     */
    public function validateSendingPoolName(): bool
    {
        return is_null($this->SendingPoolName) || (is_string($this->SendingPoolName) && !empty($this->SendingPoolName));
    }

    /**
     *   Validate the TlsPolicy.
     *
     * @return bool
     */
    public function validateTlsPolicy(): bool
    {
        return is_string($this->TlsPolicy) && in_array($this->TlsPolicy, ['REQUIRE', 'OPTIONAL']);
    }

    /**
     *   Validate the LastFreshStart.
     *
     * @return bool
     */
    public function validateLastFreshStart(): bool
    {
        return is_null($this->LastFreshStart) || (is_string($this->LastFreshStart) && strtotime($this->LastFreshStart));
    }

    /**
     *   Validate the ReputationMetricsEnabled.
     *
     * @return bool
     */
    public function validateReputationMetricsEnabled(): bool
    {
        return isset($this->ReputationMetricsEnabled) && is_bool($this->ReputationMetricsEnabled);
    }

    /**
     *   Validate the SendingEnabled.
     *
     * @return bool
     */
    public function validateSendingEnabled(): bool
    {
        return isset($this->SendingEnabled) && is_bool($this->SendingEnabled);
    }

    /**
     *   Validate the SuppressedReasons.
     *
     * @return bool
     */
    public function validateSuppressedReasons(): bool
    {
        if (!is_array($this->SuppressedReasons)) {
            return false;
        }

        if (empty($this->SuppressedReasons)) {
            return true;
        }

        $checkPassed = count($this->SuppressedReasons) === 1 || count($this->SuppressedReasons) === 2;
        $checkPassed = $checkPassed && (count($this->SuppressedReasons) === count(array_unique($this->SuppressedReasons)));
        foreach ($this->SuppressedReasons as $reason) {
            $checkPassed = $checkPassed && in_array($reason, ['COMPLAINT', 'BOUNCE']);
        }

        return $checkPassed;
    }

    /**
     *   Validate the Tags.
     *
     * @return bool
     */
    public function validateTags(): bool
    {
        $check = is_array($this->Tags);
        if ($check) {
            foreach ($this->Tags as $tag) {
                $check = $check && is_array($tag) && !empty($tag) && array_key_exists('Key', $tag) && array_key_exists('Value', $tag) && is_string($tag['Key']) && is_string($tag['Value']) && !empty($tag['Key']);
            }
        }

        return $check;
    }

    /**
     *   Validate the CustomRedirectDomain.
     *
     * @return bool
     */
    public function validateCustomRedirectDomain(): bool
    {
        return is_null($this->CustomRedirectDomain) || (is_string($this->CustomRedirectDomain) && !empty($this->CustomRedirectDomain));
    }

    /**
     *   Return the configuration set.
     *
     * @return array
     */
    public function getConfigurationSet(): array
    {
        $configSet = [];

        if (!empty($this->ConfigurationSetName)) {
            $configSet['ConfigurationSetName'] = $this->ConfigurationSetName;
        }

        if (!empty($this->SendingPoolName)) {
            $configSet['DeliveryOptions']['SendingPoolName'] = $this->SendingPoolName;
        }

        if (!empty($this->TlsPolicy)) {
            $configSet['DeliveryOptions']['TlsPolicy'] = $this->TlsPolicy;
        }

        if (!empty($this->LastFreshStart)) {
            $configSet['ReputationOptions']['LastFreshStart'] = $this->LastFreshStart;
        }

        if (!empty($this->ReputationMetricsEnabled)) {
            $configSet['ReputationOptions']['ReputationMetricsEnabled'] = $this->ReputationMetricsEnabled;
        }

        if (!empty($this->SendingEnabled)) {
            $configSet['SendingOptions']['SendingEnabled'] = $this->SendingEnabled;
        }

        if (!empty($this->SuppressedReasons)) {
            $configSet['SuppressionOptions']['SuppressedReasons'] = $this->SuppressedReasons;
        }

        if (!empty($this->Tags)) {
            $configSet['Tags'] = $this->Tags;
        }

        if (!empty($this->CustomRedirectDomain)) {
            $configSet['TrackingOptions']['CustomRedirectDomain'] = $this->CustomRedirectDomain;
        }

        return $configSet;
    }

    /**
     *   Return the event destination prefix.
     *
     * @return string|null
     */
    public function getEventDestinationPrefix(): string|null
    {
        return $this->eventDestinationPrefix;
    }

    /**
     *   Return the destination names.
     *
     * @return array
     */
    public function getDestinationNames(): array
    {
        return $this->destinationNames;
    }

    /**
     *   Return the event destination suffix.
     *
     * @return string|null
     */
    public function getEventDestinationSuffix(): string|null
    {
        return $this->eventDestinationSuffix;
    }

    /**
     *   Return the topic name as suffix.
     *
     * @return bool
     */
    public function getTopicNameAsSuffix(): bool
    {
        return $this->topicNameAsSuffix;
    }

    /**
     *   Return the ConfigurationSetName.
     *
     * @return string
     */
    public function getConfigurationSetName(): string
    {
        return $this->ConfigurationSetName;
    }

    /**
     *   Return the SendingPoolName.
     *
     * @return string|null
     */
    public function getSendingPoolName(): string|null
    {
        return $this->SendingPoolName;
    }

    /**
     *   Return the TlsPolicy.
     *
     * @return string
     */
    public function getTlsPolicy(): string
    {
        return $this->TlsPolicy;
    }

    /**
     *   Return the LastFreshStart.
     *
     * @return string|null
     */
    public function getLastFreshStart(): string|null
    {
        return $this->LastFreshStart;
    }

    /**
     *   Return the ReputationMetricsEnabled.
     *
     * @return bool
     */
    public function getReputationMetricsEnabled(): bool
    {
        return $this->ReputationMetricsEnabled;
    }

    /**
     *   Return the SendingEnabled.
     *
     * @return bool
     */
    public function getSendingEnabled(): bool
    {
        return $this->SendingEnabled;
    }

    /**
     *   Return the SuppressedReasons.
     *
     * @return array
     */
    public function getSuppressedReasons(): array
    {
        return $this->SuppressedReasons;
    }

    /**
     *   Return the Tags.
     *
     * @return array
     */
    public function getTags(): array
    {
        return $this->Tags;
    }

    /**
     *   Return the CustomRedirectDomain.
     *
     * @return string|null
     */
    public function getCustomRedirectDomain(): string|null
    {
        return $this->CustomRedirectDomain;
    }

    /**
     * Validate the SES data for the CLI call.
     *
     * @return bool
     */
    public function validateForCli(SetupTrackingCommand $console): bool
    {
        $checkPassed = tap($this->validateEventDestinationPrefix(), function ($condition) use ($console) {
            $this->validate('Event destination prefix does not match requirements.', $condition, $console);
        });

        $checkPassed = tap($this->validateDestinationNames(), function ($condition) use ($console) {
            $this->validate('Destination names do not match requirements.', $condition, $console);
        }) && $checkPassed;

        $checkPassed = tap($this->validateEventDestinationSuffix(), function ($condition) use ($console) {
            $this->validate('Event destination suffix does not match requirements.', $condition, $console);
        }) && $checkPassed;

        $checkPassed = tap($this->validateTopicNameAsSuffix(), function ($condition) use ($console) {
            $this->validate('Topic name as suffix does not match requirements.', $condition, $console);
        }) && $checkPassed;

        $checkPassed = tap($this->validateConfigurationSetName(), function ($condition) use ($console) {
            $this->validate('ConfigurationSetName does not match requirements.', $condition, $console);
        }) && $checkPassed;

        $checkPassed = tap($this->validateSendingPoolName(), function ($condition) use ($console) {
            $this->validate('SendingPoolName does not match requirements.', $condition, $console);
        }) && $checkPassed;

        $checkPassed = tap($this->validateTlsPolicy(), function ($condition) use ($console) {
            $this->validate('TlsPolicy does not match requirements.', $condition, $console);
        }) && $checkPassed;

        $checkPassed = tap($this->validateLastFreshStart(), function ($condition) use ($console) {
            $this->validate('LastFreshStart does not match requirements.', $condition, $console);
        }) && $checkPassed;

        $checkPassed = tap($this->validateReputationMetricsEnabled(), function ($condition) use ($console) {
            $this->validate('ReputationMetricsEnabled for Subscription does not match requirements.', $condition, $console);
        }) && $checkPassed;

        $checkPassed = tap($this->validateSendingEnabled(), function ($condition) use ($console) {
            $this->validate('SendingEnabled does not match requirements.', $condition, $console);
        }) && $checkPassed;

        $checkPassed = tap($this->validateSuppressedReasons(), function ($condition) use ($console) {
            $this->validate('SuppressedReasons does not match requirements.', $condition, $console);
        }) && $checkPassed;

        $checkPassed = tap($this->validateTags(), function ($condition) use ($console) {
            $this->validate('Tags do not match requirements.', $condition, $console);
        }) && $checkPassed;

        $checkPassed = tap($this->validateCustomRedirectDomain(), function ($condition) use ($console) {
            $this->validate('CustomRedirectDomain does not match requirements.', $condition, $console);
        }) && $checkPassed;

        if (!$checkPassed) {
            $console->io->error('Please specify the SES data in the '.$this->configName.' config file.');
        } else {
            $console->io->success('SES Data Check');
        }

        return $checkPassed;
    }

    /**
     * Validate and print any errors for the passed in condition.
     *
     * @return void
     */
    protected function validate(string $errorMsg, bool $condition, SetupTrackingCommand $console): void
    {
        if (!$condition) {
            $console->io->error($errorMsg);
        }
    }
}
