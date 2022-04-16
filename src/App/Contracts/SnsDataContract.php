<?php

namespace Akhan619\LaravelSesTracking\App\Contracts;

interface SnsDataContract
{
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
    public function getSnsData(): array;

    /**
     *   Validate the sns data.
     *
     * @return bool
     */
    public function validateSnsData(): bool;

    /**
     *   Validate the topic configuration data.
     *
     * @return bool
     */
    public function validateTopicConfigurationData(): bool;

    /**
     *   Validate the subscription configuration data.
     *
     * @return bool
     */
    public function validateSubscriptionConfigurationData(): bool;

    /**
     *   Validate the topic name prefix.
     *
     * @return bool
     */
    public function validateTopicNamePrefix(): bool;

    /**
     *   Validate the topic names.
     *
     * @return bool
     */
    public function validateTopicNames(): bool;

    /**
     *   Validate the topic name suffix.
     *
     * @return bool
     */
    public function validateTopicNameSuffix(): bool;

    /**
     *   Validate the DeliveryPolicy for topic.
     *
     * @return bool
     */
    public function validateDeliveryPolicyTopic(): bool;

    /**
     *   Validate the Policy.
     *
     * @return bool
     */
    public function validatePolicy(): bool;

    /**
     *   Validate the KmsMasterKeyId.
     *
     * @return bool
     */
    public function validateKmsMasterKeyId(): bool;

    /**
     *   Validate the Tags.
     *
     * @return bool
     */
    public function validateTags(): bool;

    /**
     *   Validate the ReturnSubscriptionArn.
     *
     * @return bool
     */
    public function validateReturnSubscriptionArn(): bool;

    /**
     *   Validate the DeliveryPolicy for Subscription.
     *
     * @return bool
     */
    public function validateDeliveryPolicySubscription(): bool;

    /**
     *   Validate the FilterPolicy.
     *
     * @return bool
     */
    public function validateFilterPolicy(): bool;

    /**
     *   Validate the RawMessageDelivery.
     *
     * @return bool
     */
    public function validateRawMessageDelivery(): bool;

    /**
     *   Validate the RedrivePolicy.
     *
     * @return bool
     */
    public function validateRedrivePolicy(): bool;

    /**
     *   Return the topic configuration data.
     *
     * @return array
     */
    public function getTopicConfigurationData(): array;

    /**
     *   Return the subscription configuration data.
     *
     * @return array
     */
    public function getSubscriptionConfigurationData(): array;

    /**
     *   Return the topic name prefix.
     *
     * @return string|null
     */
    public function getTopicNamePrefix(): string|null;

    /**
     *   Return the topic names.
     *
     * @return array
     */
    public function getTopicNames(): array;

    /**
     *   Return the topic name suffix.
     *
     * @return string|null
     */
    public function getTopicNameSuffix(): string|null;

    /**
     *   Return the DeliveryPolicy for topic.
     *
     * @return array
     */
    public function getDeliveryPolicyTopic(): array;

    /**
     *   Return the Policy.
     *
     * @return array
     */
    public function getPolicy(): array;

    /**
     *   Return the KmsMasterKeyId.
     *
     * @return string|null
     */
    public function getKmsMasterKeyId(): string|null;

    /**
     *   Return the Tags.
     *
     * @return array
     */
    public function getTags(): array;

    /**
     *   Return the ReturnSubscriptionArn.
     *
     * @return bool
     */
    public function getReturnSubscriptionArn(): bool;

    /**
     *   Return the DeliveryPolicy for Subscription.
     *
     * @return array
     */
    public function getDeliveryPolicySubscription(): array;

    /**
     *   Return the FilterPolicy.
     *
     * @return array
     */
    public function getFilterPolicy(): array;

    /**
     *   Return the RawMessageDelivery.
     *
     * @return string
     */
    public function getRawMessageDelivery(): string;

    /**
     *   Return the RedrivePolicy.
     *
     * @return string|null
     */
    public function getRedrivePolicy(): string|null;
}
