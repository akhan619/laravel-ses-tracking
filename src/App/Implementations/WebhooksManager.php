<?php

declare(strict_types = 1);

namespace Akhan619\LaravelSesTracking\App\Implementations;

use Akhan619\LaravelSesTracking\App\Contracts\SubscriptionContract;
use Akhan619\LaravelSesTracking\App\Contracts\WebhooksContract;
use Akhan619\LaravelSesTracking\Console\Commands\SetupTrackingCommand;

class WebhooksManager implements WebhooksContract 
{
    protected string $domain;
    protected string $scheme;
    protected ?string $routePrefix;
    protected array $definedRoutes;
    protected array $routesToRegister;
    protected string $configName;
    protected bool $debug;
    protected SubscriptionContract $subscriptionManager;

    public function __construct(string $configName, SubscriptionContract $subscriptionManager) 
    {
        $this->configName = $configName;
        $this->subscriptionManager = $subscriptionManager;
        $this->debug = config($configName . '.debug');
    }

    /**
    *   Return the webhook data:
    *   domain
    *   scheme
    *   routePrefix
    *   definedRoutes
    *   routesToRegister
    *
    * @return array
    */
    public function getWebhookData() : array
    {
        // Read in the domain data from the config or parse the APP_URL
        $this->domain = config($this->configName. '.domain') ?? (parse_url(config('app.url'), PHP_URL_HOST) ?? '');

        // Read in the scheme data from the config or parse the APP_URL
        $this->scheme = strtolower(config($this->configName. '.scheme') ?? (parse_url(config('app.url'), PHP_URL_SCHEME) ?? ''));

        // Read in the route prefix data from the config
        $this->routePrefix = config($this->configName. '.route_prefix');

        // Read in the routes data from the config and filter the routes for active events.
        $activeRoutes = array_intersect_key(config($this->configName . '.routes'), $this->subscriptionManager->getEnabledEvents());
        $this->definedRoutes = array_filter($activeRoutes, 'strlen');

        // Compile the full urls for the routes.
        foreach($this->definedRoutes as $key => $partialRoute) {
            $this->routesToRegister[$key] = $this->getRouteString($partialRoute);
        }

        return [
            $this->domain,
            $this->scheme,
            $this->routePrefix,
            $this->definedRoutes,
            $this->routesToRegister
        ];
    }

    /**
    * Return the full route string.
    *
    * @return string
    */
    protected function getRouteString(string $route) : string
    {
        $prefix = empty($this->routePrefix) ? '/' : ('/' . $this->routePrefix . '/');
        return $this->scheme . '://' . $this->domain . $prefix . $route;
    }

    /**
    *   Validate the webhook data.
    *
    * @return bool
    */
    public function validateWebhookData() : bool
    {
        return $this->validateDomain() && $this->validateScheme() && $this->validateRoutePrefix() && $this->validateDefinedRoutes();
    }

    /**
    *   Validate the domain.
    *
    * @return bool
    */
    public function validateDomain() : bool
    {
        return is_string($this->domain) && !empty($this->domain);
    }

    /**
    *   Validate the scheme.
    *
    * @return bool
    */
    public function validateScheme() : bool
    {
        return in_array($this->scheme, ['http', 'https'], true) && ($this->subscriptionManager->getEnabledSubscriber() === $this->scheme);
    }

    /**
    *   Validate the route prefix.
    *
    * @return bool
    */
    public function validateRoutePrefix() : bool
    {
        return is_null($this->routePrefix) || (is_string($this->routePrefix) && !empty($this->routePrefix));
    }

    /**
    *   Validate the routes for the events.
    *
    * @return bool
    */
    public function validateDefinedRoutes() : bool
    {
        return !empty($this->definedRoutes) && (count($this->definedRoutes) === count($this->subscriptionManager->getEnabledEvents()));
    }

    /**
    *   Return the domain.
    *
    * @return string
    */
    public function getDomain() : string
    {
        return $this->domain;
    }

    /**
    *   Return the scheme.
    *
    * @return string
    */
    public function getScheme() : string
    {
        return $this->scheme;
    }

    /**
    *   Return the route prefix.
    *
    * @return string
    */
    public function getRoutePrefix() : string
    {
        return $this->routePrefix;
    }

    /**
    *   Return the routes for the events.
    *
    * @return array
    */
    public function getDefinedRoutes() : array
    {
        return $this->definedRoutes;
    }

    /**
    *   Return the full route string array that will be registered.
    *
    * @return array
    */
    public function getRoutesToRegister() : array
    {
        return $this->routesToRegister;
    }

    /**
    * Validate the subscription data for the CLI call.
    *
    * @return bool  
    */
    public function validateForCli(SetupTrackingCommand $console) : bool
    {          
        $checkPassed = tap($this->validateDomain(), function($condition) use ($console) {
            $this->validate('Domain info could not be resolved.', $condition, $console);
        });

        $checkPassed = tap($this->validateScheme(), function($condition) use ($console) {
            $this->validate("Scheme info could not be resolved or doesn't match the SNS protocol.", $condition, $console);
        }) && $checkPassed;
                  
        $checkPassed = tap($this->validateRoutePrefix(), function($condition) use ($console) {
            $this->validate('Route prefix info could not be resolved.', $condition, $console);
        }) && $checkPassed;
                  
        $checkPassed = tap($this->validateDefinedRoutes(), function($condition) use ($console) {
            $this->validate("Route info doesn't match the enabled events.", $condition, $console);
        }) && $checkPassed;

        if(!$checkPassed) {
            $console->io->error('Please specify the Webhook details in the ' . $this->configName . ' config file.');
        } else {
            $console->io->success('Webhooks Data Check');
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

    /**
    * Show the user the routes that will be registered. Confirm if they wish to proceed.
    *
    * @return bool
    */
    public function confirmRouteInfo(SetupTrackingCommand $console) : bool
    {
        $console->info('The following routes will be created and registered as endpoints for SNS subscription.');
        $console->newLine();

        $tableData = array_map(function($key, $value) {
            return [$key, $value];
        }, array_keys($this->routesToRegister), array_values($this->routesToRegister));

        $console->io->table(['Event', 'Route Name'], $tableData);
        return $this->debug ? true : $console->confirm('Do you wish to proceed?');
    }
}