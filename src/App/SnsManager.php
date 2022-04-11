<?php

namespace Akhan619\LaravelSesTracking\App;

use Akhan619\LaravelSesTracking\App\Contracts\AwsCredentialsContract;
use Akhan619\LaravelSesTracking\App\Contracts\SnsDataContract;
use Akhan619\LaravelSesTracking\Console\Commands\SetupTrackingCommand;
use Akhan619\LaravelSesTracking\Traits\PrintsToConsole;
use Illuminate\Support\Arr;
use Aws\Sns\SnsClient;
use Exception;

class SnsManager {

    use PrintsToConsole;

    protected SetupTrackingCommand $console;
    protected bool $debug;
    
    protected SnsClient $sns;
    protected SnsDataContract $snsDataManager;
    protected array $topicArns = [];
    protected array $enabledTopicNames;

    public function __construct(AwsCredentialsContract $credentialsManager, SnsDataContract $snsDataManager, bool $debug, SetupTrackingCommand $console) 
    {
        $this->debug = $debug;
        $this->console = $console;

        $this->sns = new SnsClient([
            'credentials' => [
                'key' => $credentialsManager->getAwsAccessKeyId(),
                'secret' => $credentialsManager->getAwsSecretAccessKey(),
            ],
            'region' => $credentialsManager->getAwsDefaultRegion(),
            'version' => '2010-03-31'
        ]);

        $this->snsDataManager = $snsDataManager;
    }

    /**
    * Create the SNS topics
    *
    * @return void
    */
    public function createSnsTopics() : void
    {
        if (!$this->debug) {
            $this->console->info("Ensure topic names don't already exist.");
            $matches = $this->ensureTopicsDontExist($this->enabledTopicNames);
            $this->console->newLine();

            if(!empty($matches)) {
                $this->console->io->listing($matches);
                throw new Exception('Duplicate topic names. The above topics already exist.');
            }

            $this->console->info("Creating topics...");
            $this->console->newLine();
            $bar = $this->console->getOutput()->createProgressBar(count($this->enabledTopicNames));
            $bar->start();
        }         

        try {
            foreach($this->enabledTopicNames as $eventNameKey => $topicName) {
                $payload = $this->snsDataManager->getTopicConfigurationData();
                if(empty($payload['Attributes'])) {
                    unset($payload['Attributes']);
                }
                $payload['Name'] = $topicName;

                if (!$this->debug) {
                    $this->topicArns[$eventNameKey] = $this->createTopic($payload);
                    $bar->advance();
                } else {
                    $this->prettyPrintArray($payload, 'SNS Topic Configuration Data');
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
    * Create the SNS topic
    *
    * @return string
    */
    protected function createTopic(array $payload) : string
    {
        $result = $this->sns->createTopic($payload);
        return $result->get('TopicArn');
    }

    /**
    * Ensure topics to be created don't already exist.
    *
    * @return array
    */
    protected function ensureTopicsDontExist(array $topicsToCheck) : array
    {
        $token = '';
        $matches = [];

        do {
            $response = empty($token) ? $this->sns->listTopics() : $this->sns->listTopics(['NextToken' => $token]);
            $token = $response->hasKey('NextToken') ? $response->get('NextToken') : '';
            
            $topics = array_map(function($topicArn) {
                $chunks = explode(':', $topicArn);
                return end($chunks);
            }, Arr::flatten($response->get('Topics')));
            
            $matches = array_merge($matches, array_intersect($topicsToCheck, $topics));

        } while (!empty($token));

        return array_unique($matches);
    }

    /**
    * Show the user the names that will be used as SNS topic name. Confirm if they wish to proceed.
    *
    * @return mixed
    */
    public function confirmNamingConvention(array $enabledEvents) : mixed
    {
        $this->console->info('The following SNS topic names will be created.');
        $this->console->newLine();

        $this->enabledTopicNames = array_map(function($event) {
            return $this->getSnsTopicName($event);
        }, array_intersect_key($this->snsDataManager->getTopicNames(), $enabledEvents));

        $tableData = array_map(function($key, $value) {
            return [$key, $value];
        }, array_keys($this->enabledTopicNames), array_values($this->enabledTopicNames));

        $this->console->io->table(['Event', 'Topic Name'], $tableData);
        $response = $this->debug ? true : $this->console->confirm('Do you wish to proceed?');

        return $response ? $this->enabledTopicNames : false;
    }

    /**
    * Return the sns topic name string.
    *
    * @return string
    */
    protected function getSnsTopicName(?string $event) : string
    {
        $allNull = is_null($this->snsDataManager->getTopicNamePrefix()) && is_null($this->snsDataManager->getTopicNameSuffix()) && is_null($event);

        if($allNull) {
            throw new Exception('Topic name empty. Please check the configuration and make sure atleast one of topic_name_prefix/topic_name_suffix/topic_name is not null.');
        }

        if(!is_null($event)) {
            return ($this->snsDataManager->getTopicNamePrefix() ? $this->snsDataManager->getTopicNamePrefix() . '-' : '' ) . 
            $event . 
            ($this->snsDataManager->getTopicNameSuffix() ? '-' . $this->snsDataManager->getTopicNameSuffix() : '');
        } else {
            if(is_null($this->snsDataManager->getTopicNamePrefix())) {
                return $this->snsDataManager->getTopicNameSuffix();
            }
            
            if(is_null($this->snsDataManager->getTopicNameSuffix())) {
                return $this->snsDataManager->getTopicNamePrefix();
            }

            return $this->snsDataManager->getTopicNamePrefix() . '-' . $this->snsDataManager->getTopicNameSuffix();
        }        
    }

    /**
    * Return the Topic ARN for the given event key
    *
    * @return string
    */
    public function getTopicArnByEventKey(string $eventKey) : string
    {
        return $this->topicArns[$eventKey];
    }

    /**
    * Subscribe the endpoints to the SNS topic
    *
    * @return void
    */
    public function subscribeEndpoints(string $protocol, array $endpoints) : void
    {
        if(!$this->debug) {
            $bar = $this->console->getOutput()->createProgressBar(count($endpoints));
            $bar->start();
        }        

        $subscriptionArns = [];

        try {
            foreach($endpoints as $eventKey => $routeUrl) {
                $config = $this->snsDataManager->getSubscriptionConfigurationData();
                if(empty($config['Attributes'])) {
                    unset($config['Attributes']);
                }

                $config['Endpoint'] = $routeUrl;
                $config['Protocol'] = $protocol;
                $config['TopicArn'] = $this->debug ? 'Not Available in Debug Mode' : $this->getTopicArnByEventKey($eventKey);
    
                if(!$this->debug) {
                    array_push($subscriptionArns, [$eventKey, $this->sns->subscribe($config)->get('SubscriptionArn')]);
                    $bar->advance();
                } else {
                    $this->prettyPrintArray($config, 'Subscription Endpoint Configuration Data');
                }
            }
        } catch (\Throwable $th) {
            throw $th;
        } finally {
            if(!$this->debug) {
                $bar->finish();
                $this->console->newLine(2);
            }            
        }  

        if(!$this->debug) {
            $this->console->newLine();
            $this->console->io->table(['Event', 'SubscriptionArn'], $subscriptionArns);
            $this->console->newLine();
        }
    }

    /**
    * Removes all the topics that have been created.
    *
    * @return void
    */
    public function deleteCurrentTopics() : void
    {
        if (!empty($this->topicArns) && !$this->debug) {
            array_walk($this->topicArns, function($value, $key){
                $this->sns->deleteTopic([
                    'TopicArn' => $value
                ]);
            });
            $this->topicArns = [];
        }
    }
}