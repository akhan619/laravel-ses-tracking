<?php

namespace Akhan619\LaravelSesTracking\App\Contracts;

interface SubscriptionContract {
    
    /**
    *   Return the subscription data:
    *   enabledSubscriber
    *   enabledEvents
    *
    * @return array
    */
    public function getSubscriptionData() : array;

    /**
    *   Validate the subscription data.
    *
    * @return bool
    */
    public function validateSubscriptionData() : bool;

    /**
    *   Validate the enabled events.
    *
    * @return bool
    */
    public function validateEnabledEvents() : bool;

    /**
    *   Validate the enabled subscription protocal.
    *
    * @return bool
    */
    public function validateEnabledSubscriber() : bool;

    /**
    *   Return the enabled events.
    *
    * @return array
    */
    public function getEnabledEvents() : array;

    /**
    *   Return the enabled subscription protocal.
    *
    * @return string
    */
    public function getEnabledSubscriber() : string;
    
}