<?php

declare(strict_types=1);

namespace Akhan619\LaravelSesTracking\App\Implementations;

use Akhan619\LaravelSesTracking\App\Contracts\SnsDataContract;
use Akhan619\LaravelSesTracking\Console\Commands\SetupTrackingCommand;

class SnsDataManager implements SnsDataContract
{
    protected string $configName;

    protected ?string $topicNamePrefix;
    protected array $topicNames;
    protected ?string $topicNameSuffix;
    protected array $DeliveryPolicyTopic;
    protected array $Policy;
    protected ?string $KmsMasterKeyId;
    protected array $Tags;
    protected bool $ReturnSubscriptionArn;
    protected array $DeliveryPolicySubscription;
    protected array $FilterPolicy;
    protected string $RawMessageDelivery;
    protected ?string $RedrivePolicy;

    public function __construct(string $configName)
    {
        $this->configName = $configName;
    }

    /**
     *   Return the sns data:
     *   topicNamePrefix
     *   topicNames
     *   topicNameSuffix
     *   DeliveryPolicyTopic
     *   Policy
     *   KmsMasterKeyId
     *   Tags
     *   ReturnSubscriptionArn
     *   DeliveryPolicySubscription
     *   FilterPolicy
     *   RawMessageDelivery
     *   RedrivePolicy.
     *
     * @return array
     */
    public function getSnsData(): array
    {
        $this->topicNamePrefix = config($this->configName.'.topic_name_prefix');
        $this->topicNames = config($this->configName.'.topic_names') ?? [];
        $this->topicNameSuffix = config($this->configName.'.topic_name_suffix');

        $this->DeliveryPolicyTopic = config($this->configName.'.sns_topic_configuration_data.DeliveryPolicy') ?? [];
        $this->Policy = config($this->configName.'.sns_topic_configuration_data.Policy') ?? [];
        $this->KmsMasterKeyId = config($this->configName.'.sns_topic_configuration_data.KmsMasterKeyId');
        $this->Tags = config($this->configName.'.sns_topic_configuration_data.Tags') ?? [];
        $this->ReturnSubscriptionArn = config($this->configName.'.sns_subscription_configuration_data.ReturnSubscriptionArn');
        $this->DeliveryPolicySubscription = config($this->configName.'.sns_subscription_configuration_data.DeliveryPolicy') ?? [];
        $this->FilterPolicy = config($this->configName.'.sns_subscription_configuration_data.FilterPolicy') ?? [];
        $this->RawMessageDelivery = config($this->configName.'.sns_subscription_configuration_data.RawMessageDelivery') ?? '';
        $this->RedrivePolicy = config($this->configName.'.sns_subscription_configuration_data.RedrivePolicy');

        return [
            $this->topicNamePrefix,
            $this->topicNames,
            $this->topicNameSuffix,
            $this->DeliveryPolicyTopic,
            $this->Policy,
            $this->KmsMasterKeyId,
            $this->Tags,
            $this->ReturnSubscriptionArn,
            $this->DeliveryPolicySubscription,
            $this->FilterPolicy,
            $this->RawMessageDelivery,
            $this->RedrivePolicy,
        ];
    }

    /**
     *   Validate the sns data.
     *
     * @return bool
     */
    public function validateSnsData(): bool
    {
        return $this->validateTopicConfigurationData() &&
        $this->validateSubscriptionConfigurationData() &&
        $this->validateTopicNamePrefix() &&
        $this->validateTopicNames() &&
        $this->validateTopicNameSuffix();
    }

    /**
     *   Validate the topic configuration data.
     *
     * @return bool
     */
    public function validateTopicConfigurationData(): bool
    {
        return $this->validateDeliveryPolicyTopic() &&
        $this->validatePolicy() &&
        $this->validateKmsMasterKeyId() &&
        $this->validateTags();
    }

    /**
     *   Validate the subscription configuration data.
     *
     * @return bool
     */
    public function validateSubscriptionConfigurationData(): bool
    {
        return $this->validateReturnSubscriptionArn() &&
        $this->validateDeliveryPolicySubscription() &&
        $this->validateFilterPolicy() &&
        $this->validateRawMessageDelivery() &&
        $this->validateRedrivePolicy();
    }

    /**
     *   Validate the topic name prefix.
     *
     * @return bool
     */
    public function validateTopicNamePrefix(): bool
    {
        return is_null($this->topicNamePrefix) || (is_string($this->topicNamePrefix) && !empty($this->topicNamePrefix));
    }

    /**
     *   Validate the topic names.
     *
     * @return bool
     */
    public function validateTopicNames(): bool
    {
        if (empty($this->topicNames) || !is_array($this->topicNames)) {
            return false;
        }

        $checkPassed = true;
        foreach ($this->topicNames as $name) {
            $checkPassed = $checkPassed && (is_null($name) || (is_string($name) && !empty($name)));
        }

        return $checkPassed;
    }

    /**
     *   Validate the topic name suffix.
     *
     * @return bool
     */
    public function validateTopicNameSuffix(): bool
    {
        return is_null($this->topicNameSuffix) || (is_string($this->topicNameSuffix) && !empty($this->topicNameSuffix));
    }

    /**
     *   Validate the DeliveryPolicy for topic.
     *
     * @return bool
     */
    public function validateDeliveryPolicyTopic(): bool
    {
        return is_array($this->DeliveryPolicyTopic);
    }

    /**
     *   Validate the Policy.
     *
     * @return bool
     */
    public function validatePolicy(): bool
    {
        return is_array($this->Policy);
    }

    /**
     *   Validate the KmsMasterKeyId.
     *
     * @return bool
     */
    public function validateKmsMasterKeyId(): bool
    {
        return is_null($this->KmsMasterKeyId) || (is_string($this->KmsMasterKeyId) && !empty($this->KmsMasterKeyId));
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
     *   Validate the ReturnSubscriptionArn.
     *
     * @return bool
     */
    public function validateReturnSubscriptionArn(): bool
    {
        return isset($this->ReturnSubscriptionArn) && is_bool($this->ReturnSubscriptionArn);
    }

    /**
     *   Validate the DeliveryPolicy for Subscription.
     *
     * @return bool
     */
    public function validateDeliveryPolicySubscription(): bool
    {
        return is_array($this->DeliveryPolicySubscription);
    }

    /**
     *   Validate the FilterPolicy.
     *
     * @return bool
     */
    public function validateFilterPolicy(): bool
    {
        return is_array($this->FilterPolicy);
    }

    /**
     *   Validate the RawMessageDelivery.
     *
     * @return bool
     */
    public function validateRawMessageDelivery(): bool
    {
        return is_string($this->RawMessageDelivery) && in_array($this->RawMessageDelivery, ['true', 'false']);
    }

    /**
     *   Validate the RedrivePolicy.
     *
     * @return bool
     */
    public function validateRedrivePolicy(): bool
    {
        return is_null($this->RedrivePolicy) || (is_string($this->RedrivePolicy) && !empty($this->RedrivePolicy));
    }

    /**
     *   Return the topic configuration data.
     *
     * @return array
     */
    public function getTopicConfigurationData(): array
    {
        $configData = [
            'Attributes' => [],
        ];

        if (!empty($this->getDeliveryPolicyTopic())) {
            $configData['Attributes']['DeliveryPolicy'] = json_encode($this->getDeliveryPolicyTopic());
        }

        if (!empty($this->getPolicy())) {
            $configData['Attributes']['Policy'] = json_encode($this->getPolicy());
        }

        if (!is_null($this->getKmsMasterKeyId())) {
            $configData['Attributes']['KmsMasterKeyId'] = $this->getKmsMasterKeyId();
        }

        if (!empty($this->getTags())) {
            $configData['Attributes']['Tags'] = $this->getTags();
        }

        return $configData;
    }

    /**
     *   Return the subscription configuration data.
     *
     * @return array
     */
    public function getSubscriptionConfigurationData(): array
    {
        $configData = [
            'Attributes' => [],
        ];

        if (!empty($this->getDeliveryPolicySubscription())) {
            $configData['Attributes']['DeliveryPolicy'] = json_encode($this->getDeliveryPolicySubscription());
        }

        if (!empty($this->getFilterPolicy())) {
            $configData['Attributes']['FilterPolicy'] = json_encode($this->getFilterPolicy());
        }

        $configData['Attributes']['RawMessageDelivery'] = $this->getRawMessageDelivery();

        if (!is_null($this->getRedrivePolicy())) {
            $configData['Attributes']['RedrivePolicy'] = $this->getRedrivePolicy();
        }

        $configData['ReturnSubscriptionArn'] = $this->getReturnSubscriptionArn();

        return $configData;
    }

    /**
     *   Return the topic name prefix.
     *
     * @return string|null
     */
    public function getTopicNamePrefix(): string|null
    {
        return $this->topicNamePrefix;
    }

    /**
     *   Return the topic names.
     *
     * @return array
     */
    public function getTopicNames(): array
    {
        return $this->topicNames;
    }

    /**
     *   Return the topic name suffix.
     *
     * @return string|null
     */
    public function getTopicNameSuffix(): string|null
    {
        return $this->topicNameSuffix;
    }

    /**
     *   Return the DeliveryPolicy for topic.
     *
     * @return array
     */
    public function getDeliveryPolicyTopic(): array
    {
        return $this->DeliveryPolicyTopic;
    }

    /**
     *   Return the Policy.
     *
     * @return array
     */
    public function getPolicy(): array
    {
        return $this->Policy;
    }

    /**
     *   Return the KmsMasterKeyId.
     *
     * @return string|null
     */
    public function getKmsMasterKeyId(): string|null
    {
        return $this->KmsMasterKeyId;
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
     *   Return the ReturnSubscriptionArn.
     *
     * @return bool
     */
    public function getReturnSubscriptionArn(): bool
    {
        return $this->ReturnSubscriptionArn;
    }

    /**
     *   Return the DeliveryPolicy for Subscription.
     *
     * @return array
     */
    public function getDeliveryPolicySubscription(): array
    {
        return $this->DeliveryPolicySubscription;
    }

    /**
     *   Return the FilterPolicy.
     *
     * @return array
     */
    public function getFilterPolicy(): array
    {
        return $this->FilterPolicy;
    }

    /**
     *   Return the RawMessageDelivery.
     *
     * @return string
     */
    public function getRawMessageDelivery(): string
    {
        return $this->RawMessageDelivery;
    }

    /**
     *   Return the RedrivePolicy.
     *
     * @return string|null
     */
    public function getRedrivePolicy(): string|null
    {
        return $this->RedrivePolicy;
    }

    /**
     * Validate the SNS data for the CLI call.
     *
     * @return bool
     */
    public function validateForCli(SetupTrackingCommand $console): bool
    {
        $checkPassed = tap($this->validateTopicNamePrefix(), function ($condition) use ($console) {
            $this->validate('Topic name prefix does not match requirements.', $condition, $console);
        });

        $checkPassed = tap($this->validateTopicNames(), function ($condition) use ($console) {
            $this->validate('Topic names do not match requirements.', $condition, $console);
        }) && $checkPassed;

        $checkPassed = tap($this->validateTopicNameSuffix(), function ($condition) use ($console) {
            $this->validate('Topic name suffix does not match requirements.', $condition, $console);
        }) && $checkPassed;

        $checkPassed = tap($this->validateDeliveryPolicyTopic(), function ($condition) use ($console) {
            $this->validate('DeliveryPolicy for Topic does not match requirements.', $condition, $console);
        }) && $checkPassed;

        $checkPassed = tap($this->validatePolicy(), function ($condition) use ($console) {
            $this->validate('Policy does not match requirements.', $condition, $console);
        }) && $checkPassed;

        $checkPassed = tap($this->validateKmsMasterKeyId(), function ($condition) use ($console) {
            $this->validate('KmsMasterKeyId does not match requirements.', $condition, $console);
        }) && $checkPassed;

        $checkPassed = tap($this->validateTags(), function ($condition) use ($console) {
            $this->validate('Tags do not match requirements.', $condition, $console);
        }) && $checkPassed;

        $checkPassed = tap($this->validateReturnSubscriptionArn(), function ($condition) use ($console) {
            $this->validate('ReturnSubscriptionArn does not match requirements.', $condition, $console);
        }) && $checkPassed;

        $checkPassed = tap($this->validateDeliveryPolicySubscription(), function ($condition) use ($console) {
            $this->validate('DeliveryPolicy for Subscription does not match requirements.', $condition, $console);
        }) && $checkPassed;

        $checkPassed = tap($this->validateFilterPolicy(), function ($condition) use ($console) {
            $this->validate('FilterPolicy does not match requirements.', $condition, $console);
        }) && $checkPassed;

        $checkPassed = tap($this->validateRawMessageDelivery(), function ($condition) use ($console) {
            $this->validate('RawMessageDelivery does not match requirements.', $condition, $console);
        }) && $checkPassed;

        $checkPassed = tap($this->validateRedrivePolicy(), function ($condition) use ($console) {
            $this->validate('RedrivePolicy does not match requirements.', $condition, $console);
        }) && $checkPassed;

        if (!$checkPassed) {
            $console->getIo()->error('Please specify the SNS data in the '.$this->configName.' config file.');
        } else {
            $console->getIo()->success('SNS Data Check');
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
            $console->getIo()->error($errorMsg);
        }
    }
}
