<?php

namespace Akhan619\LaravelSesTracking\App;

use Akhan619\LaravelSesTracking\App\Contracts\AwsCredentialsContract;
use Akhan619\LaravelSesTracking\App\Contracts\SesDataContract;
use Akhan619\LaravelSesTracking\Console\Commands\SetupTrackingCommand;
use Akhan619\LaravelSesTracking\Traits\PrintsToConsole;
use Aws\SesV2\SesV2Client;
use Exception;
use Illuminate\Support\Str;

class SesManager
{
    use PrintsToConsole;

    protected SetupTrackingCommand $console;
    protected bool $debug;

    protected SesV2Client $ses;
    protected SesDataContract $sesDataManager;
    protected array $enabledDestinationNames;
    protected bool $configurationSetExists = false;

    public function __construct(AwsCredentialsContract $credentialsManager, SesDataContract $sesDataManager, bool $debug, SetupTrackingCommand $console)
    {
        $this->debug = $debug;
        $this->console = $console;

        $this->ses = new SesV2Client([
            'credentials' => [
                'key'    => $credentialsManager->getAwsAccessKeyId(),
                'secret' => $credentialsManager->getAwsSecretAccessKey(),
            ],
            'region'  => $credentialsManager->getAwsDefaultRegion(),
            'version' => '2019-09-27',
        ]);

        $this->sesDataManager = $sesDataManager;
    }

    /**
     * Process and parse the configuration for a valid configuration set. Then create the configuration set on AWS.
     *
     * @return void
     */
    public function createConfigurationSet(): void
    {
        if ($this->debug) {
            $this->prettyPrintArray($this->sesDataManager->getConfigurationSet(), 'Configuration Set Data');
        } else {
            $this->sendCreationRequest();
            $this->console->io->success('Configuration Set Creation');
        }
    }

    /**
     * Create the configuration set on AWS.
     *
     * @return void
     */
    protected function sendCreationRequest(): void
    {
        $this->ses->createConfigurationSet($this->sesDataManager->getConfigurationSet());
        $this->configurationSetExists = true;
    }

    /**
     * Show the user the names that will be used as SES Configuration Set Event Destination names. Confirm if they wish to proceed.
     *
     * @return mixed
     */
    public function confirmNamingConvention(array $enabledEvents, array $snsTopicNames): mixed
    {
        $this->console->info('The following SES event destination names will be created.');
        $this->console->newLine();

        $enabledDestinations = array_intersect_key($this->sesDataManager->getDestinationNames(), $enabledEvents);
        foreach ($enabledDestinations as $eventKey => $eventName) {
            $this->enabledDestinationNames[$eventKey] = $this->getSesEventDestinationName($eventName, $snsTopicNames[$eventKey]);
        }

        $tableData = array_map(function ($key, $value) {
            return [$key, $value];
        }, array_keys($this->enabledDestinationNames), array_values($this->enabledDestinationNames));

        $this->console->io->table(['Event', 'Event Destination Name'], $tableData);
        $response = $this->debug ? true : $this->console->confirm('Do you wish to proceed?');

        return $response ? $this->enabledDestinationNames : false;
    }

    /**
     * Return the ses event destination name string.
     *
     * @return string
     */
    protected function getSesEventDestinationName(?string $event, string $topicName): string
    {
        $suffix = $this->sesDataManager->getTopicNameAsSuffix() ? $topicName : $this->sesDataManager->getEventDestinationSuffix();

        if ($this->sesDataManager->getTopicNameAsSuffix()) {
            $allNull = is_null($this->sesDataManager->getEventDestinationPrefix()) && is_null($topicName) && is_null($event);

            if ($allNull) {
                throw new Exception('Event destination name empty. Please check the configuration for the values of event_destination_prefix/topic_name_as_suffix/destination_names.');
            }
        } else {
            $allNull = is_null($this->sesDataManager->getEventDestinationPrefix()) && is_null($this->sesDataManager->getEventDestinationSuffix()) && is_null($event);

            if ($allNull) {
                throw new Exception('Event destination name empty. Please check the configuration and make sure atleast one of event_destination_prefix/event_destination_suffix/destination_names is not null.');
            }
        }

        if (!is_null($event)) {
            return ($this->sesDataManager->getEventDestinationPrefix() ? $this->sesDataManager->getEventDestinationPrefix().'-' : '').
            $event.
            ($suffix ? '-'.$suffix : '');
        } else {
            if (is_null($this->sesDataManager->getEventDestinationPrefix())) {
                return $suffix;
            }

            if (is_null($suffix)) {
                return $this->sesDataManager->getEventDestinationPrefix();
            }

            return $this->sesDataManager->getEventDestinationPrefix().'-'.$suffix;
        }
    }

    /**
     * Create the event destination for our configuration set.
     *
     * @return void
     */
    public function createSesEventDestinations(SnsManager $snsManager): void
    {
        if (!$this->debug) {
            $bar = $this->console->getOutput()->createProgressBar(count($this->enabledDestinationNames));
            $bar->start();
        }

        try {
            foreach ($this->enabledDestinationNames as $eventDestinationKey => $eventDestinationName) {
                $config = [
                    'ConfigurationSetName' => $this->sesDataManager->getConfigurationSetName(),
                    'EventDestination'     => [
                        'Enabled'            => true,
                        'MatchingEventTypes' => [strtoupper(Str::singular($eventDestinationKey))],
                        'SnsDestination'     => [
                            'TopicArn' => $this->debug ? 'Not Available in Debug Mode' : $snsManager->getTopicArnByEventKey($eventDestinationKey),
                        ],
                    ],
                    'EventDestinationName' => $eventDestinationName,
                ];

                if (!$this->debug) {
                    $this->ses->createConfigurationSetEventDestination($config);
                    $bar->advance();
                } else {
                    $this->prettyPrintArray($config, 'SES Event Destination Configuration Data');
                }
            }
        } catch (\Throwable $th) {
            throw $th;
        } finally {
            if (!$this->debug) {
                $bar->finish();
                $this->console->newLine(2);
            }
        }
    }

    /**
     * Remove the current configuration set.
     *
     * @return void
     */
    public function deleteCurrentConfigurationSet(): void
    {
        if ($this->configurationSetExists && !$this->debug) {
            $this->ses->deleteConfigurationSet([
                'ConfigurationSetName' => $this->sesDataManager->getConfigurationSetName(),
            ]);
            $this->configurationSetExists = false;
        }
    }
}
