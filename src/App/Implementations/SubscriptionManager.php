<?php

declare(strict_types=1);

namespace Akhan619\LaravelSesTracking\App\Implementations;

use Akhan619\LaravelSesTracking\App\Contracts\SubscriptionContract;
use Akhan619\LaravelSesTracking\Console\Commands\SetupTrackingCommand;

class SubscriptionManager implements SubscriptionContract
{
    protected string $enabledSubscriber;
    protected array $enabledEvents;
    protected array $subscribers;
    protected string $configName;

    public function __construct(string $configName)
    {
        $this->configName = $configName;
    }

    /**
     *   Return the subscription data:
     *   enabledSubscriber
     *   enabledEvents.
     *
     * @return array
     */
    public function getSubscriptionData(): array
    {
        // Read in the subscriber data from the config.
        $this->subscribers = array_filter(config($this->configName.'.subscriber'), function ($value) {
            return $value;
        });
        $this->enabledSubscriber = key($this->subscribers) ?? '';

        // Read in the events data from the config and filter the active events.
        $this->enabledEvents = array_filter(config($this->configName.'.active'), function ($value) {
            return $value;
        });

        return [
            $this->enabledSubscriber,
            $this->enabledEvents,
        ];
    }

    /**
     *   Validate the subscription data.
     *
     * @return bool
     */
    public function validateSubscriptionData(): bool
    {
        return $this->validateEnabledEvents() && $this->validateEnabledSubscriber();
    }

    /**
     *   Validate the enabled events.
     *
     * @return bool
     */
    public function validateEnabledEvents(): bool
    {
        return isset($this->enabledEvents) && is_array($this->enabledEvents) && !empty($this->enabledEvents);
    }

    /**
     *   Validate the enabled subscription protocal.
     *
     * @return bool
     */
    public function validateEnabledSubscriber(): bool
    {
        return count($this->subscribers) === 1;
    }

    /**
     *   Return the enabled events.
     *
     * @return array
     */
    public function getEnabledEvents(): array
    {
        return $this->enabledEvents;
    }

    /**
     *   Return the enabled subscription protocal.
     *
     * @return string
     */
    public function getEnabledSubscriber(): string
    {
        return $this->enabledSubscriber;
    }

    /**
     * Validate the subscription data for the CLI call.
     *
     * @return bool
     */
    public function validateForCli(SetupTrackingCommand $console): bool
    {
        $checkPassed = tap($this->validateEnabledEvents(), function ($condition) use ($console) {
            $this->validate('Active Events data is missing. Make sure atleast 1 event is set to true.', $condition, $console);
        });

        $checkPassed = tap($this->validateEnabledSubscriber(), function ($condition) use ($console) {
            $this->validate('Zero or Multiple subscribers enabled. Please ensure only one subscriber is enabled.', $condition, $console);
        }) && $checkPassed;

        if (!$checkPassed) {
            $console->io->error('Please specify the Subscription details in the '.$this->configName.' config file.');
        } else {
            $console->io->success('Subscription Data Check');
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
