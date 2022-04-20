<?php

namespace Akhan619\LaravelSesTracking\App;

use Akhan619\LaravelSesTracking\App\Contracts\AwsCredentialsContract;
use Akhan619\LaravelSesTracking\App\Contracts\SesDataContract;
use Akhan619\LaravelSesTracking\App\Contracts\SnsDataContract;
use Akhan619\LaravelSesTracking\App\Contracts\SubscriptionContract;
use Akhan619\LaravelSesTracking\App\Contracts\WebhooksContract;
use Akhan619\LaravelSesTracking\Console\Commands\SetupTrackingCommand;
use Aws\Exception\AwsException;
use Illuminate\Support\Facades\App;

class SetupManager
{
    protected SetupTrackingCommand $console;
    protected bool $debug;

    protected int $stepCounter = 0;

    protected SesManager $ses;
    protected SnsManager $sns;
    protected AwsCredentialsContract $awsCredentialsManager;
    protected SubscriptionContract $subscriptionManager;
    protected WebhooksContract $webhooksManager;
    protected SnsDataContract $snsDataManager;
    protected SesDataContract $sesDataManager;

    protected static string $configName = 'laravel-ses-tracking';

    public function __construct(SetupTrackingCommand $console)
    {
        $this->debug = config(self::$configName.'.debug');
        $this->console = $console;

        $this->console->info(str_repeat('-', 36));
        $this->console->info(' Laravel SES Tracking Setup Started ');
        $this->console->info(str_repeat('-', 36));

        try {
            // Make sure we have the AWS credentials for the API calls.
            $this->prettyPrintStep('Checking... AWS credentials are properly configured?');
            $this->awsCredentialsManager = App::make(AwsCredentialsContract::class);
            $this->awsCredentialsManager->getAwsCredentials();
            if (!$this->awsCredentialsManager->validateForCli($console)) {
                return;
            }

            // Make sure we have the Subscription data for the API calls.
            $this->prettyPrintStep('Checking... Subscription details are properly configured?');
            $this->subscriptionManager = App::make(SubscriptionContract::class);
            $this->subscriptionManager->getSubscriptionData();
            if (!$this->subscriptionManager->validateForCli($console)) {
                return;
            }

            if (in_array($this->subscriptionManager->getEnabledSubscriber(), ['http', 'https'], true)) {
                // Make sure the http/s configuration details are properly set and there are no conflicting settings.
                $this->prettyPrintStep('Checking... Webhook details are properly configured?');
                $this->webhooksManager = App::make(WebhooksContract::class);
                $this->webhooksManager->getWebhookData();
                if (!$this->webhooksManager->validateForCli($console)) {
                    return;
                }

                // If we are using the http/s protocols for SNS then get a confirmation from the user
                // for the routes that will be registered.
                $this->prettyPrintStep('Route Confirmation');

                $response = $this->webhooksManager->confirmRouteInfo($console);
                if (!$response) {
                    return;
                }
            }

            // Make sure we have the SNS data for the API calls.
            $this->prettyPrintStep('Checking... SNS data is properly configured?');
            $this->snsDataManager = App::make(SnsDataContract::class);
            $this->snsDataManager->getSnsData();
            if (!$this->snsDataManager->validateForCli($this->console)) {
                return;
            }

            // Make sure we have the SES data for the API calls.
            $this->prettyPrintStep('Checking... SES data is properly configured?');
            $this->sesDataManager = App::make(SesDataContract::class);
            $this->sesDataManager->getSesData();
            if (!$this->sesDataManager->validateForCli($this->console)) {
                return;
            }
        } catch (\Throwable $th) {
            $this->error($th);

            return;
        }

        // Start the AWS API calls process.
        $this->runSetup();
    }

    /**
     * Start the Setup process.
     *
     * @return void
     */
    protected function runSetup(): void
    {
        try {
            // Create the SES and SNS client manager instantances.
            $this->ses = new SesManager($this->awsCredentialsManager, $this->sesDataManager, $this->debug, $this->console);
            $this->sns = new SnsManager($this->awsCredentialsManager, $this->snsDataManager, $this->debug, $this->console);

            // Confirm the topic names from the user.
            $this->prettyPrintStep('Topic Names Confirmation');
            $snsResponse = $this->sns->confirmNamingConvention($this->subscriptionManager->getEnabledEvents());
            if (!$snsResponse) {
                return;
            }

            // Confirm the event destination names from the user.
            $this->prettyPrint('Event Destination Names Confirmation');
            $sesResponse = $this->ses->confirmNamingConvention($this->subscriptionManager->getEnabledEvents(), $snsResponse);
            if (!$sesResponse) {
                return;
            }
        } catch (\Throwable $th) {
            $this->error($th);

            return;
        }

        // BEGINNING OF SIDE-EFFECTS. THE PROCESS OF CREATING THE EMAIL NOTIFICATION EVENTS IS A MULTI-STEP PROCESS.
        // IF ONE OF THE STEPS FAILS, THEN WE WILL BE LEFT WILL INITIALIZED RESOURCES ON AWS WHICH WONT BE USED.
        // E.G. IF THE SNS TOPIC NAME CREATION FAILS, THEN WE ARE LEFT WITH A UNUSED CONFIGURATION SET.
        // EVERY EFFORT IS MADE TO LIMIT THIS POSSIBILITY BY REVERTING THE AWS INFRASTRUCTURE USED BACK TO THE STATE AT THE START.
        // HOWEVER, THIS MAY NOT ALWAYS WORK AS WE ARE DEALING WITH APIS WHICH MAY FAIL AGAIN WHEN REVERTING.
        // CURRENTLY, IF THE AUTOMATIC REVERTION FAILS THE ONLY RECOURSE IS TO MANUALLY CLEAN THE AWS ACCOUNT.

        try {
            $this->prettyPrintStep('Creating...Configuration Set.');
            $this->ses->createConfigurationSet();

            $this->prettyPrintStep('Creating...SNS Topics.');
            $this->sns->createSnsTopics();

            $this->prettyPrintStep('Creating...SES Event Destinations.');
            $this->ses->createSesEventDestinations($this->sns);

            if (in_array($this->subscriptionManager->getEnabledSubscriber(), ['http', 'https'])) {
                $this->prettyPrintStep('Subscribing...Http/s Endpoints.');
                $this->sns->subscribeEndpoints($this->subscriptionManager->getEnabledSubscriber(), $this->webhooksManager->getRoutesToRegister());
            }
        } catch (AwsException $e) {
            $this->awsError($e);
            $this->revertToCleanState();

            return;
        } catch (\Exception $e) {
            $this->error($e);
            $this->revertToCleanState();

            return;
        }

        if (!$this->debug) {
            $this->console->getIo()->success('Successfully completed Event subscription process.');
        }
    }

    /**
     * In case of errors, revert the state of AWS back to the start so we don't have to worry about deleting
     * things manually.
     *
     * @return
     */
    protected function revertToCleanState()
    {
        $this->stepCounter++;
        $this->prettyPrint('('.$this->stepCounter.') Reverting...Clean State.');

        $this->ses->deleteCurrentConfigurationSet();
        $this->sns->deleteCurrentTopics();

        $this->console->getIo()->success('Environment Clean State');
    }

    /**
     * Pretty print to the console.
     *
     * @return void
     */
    protected function prettyPrint(string $msg): void
    {
        $this->console->getIo()->title($msg);
    }

    /**
     * Pretty print step to the console.
     *
     * @return void
     */
    protected function prettyPrintStep(string $msg): void
    {
        $this->stepCounter++;
        $this->prettyPrint("($this->stepCounter) $msg");
    }

    /**
     * Output AWS errors to console.
     *
     * @return void
     */
    protected function awsError(AwsException $e): void
    {
        $this->console->getIo()->error(
            $e->getAwsErrorCode().' - '.$e->getAwsErrorType().' - '.$e->getAwsErrorMessage()
        );
    }

    /**
     * Output errors to console.
     *
     * @return void
     */
    protected function error(\Throwable $th): void
    {
        $this->console->getIo()->error(
            get_class($th).' - '.$th->getCode().' - '.$th->getMessage()
        );
    }

    /**
     * Start the creation process.
     *
     * @param $console
     *
     * @return SesSetup
     */
    public static function create($console): SetupManager
    {
        return new self($console);
    }
}
