<?php

namespace Akhan619\LaravelSesTracking\App\Contracts;

interface WebhooksContract
{
    /**
     *   Return the webhook data:
     *   domain
     *   scheme
     *   routePrefix
     *   definedRoutes
     *   routesToRegister.
     *
     * @return array
     */
    public function getWebhookData(): array;

    /**
     *   Validate the webhook data.
     *
     * @return bool
     */
    public function validateWebhookData(): bool;

    /**
     *   Validate the domain.
     *
     * @return bool
     */
    public function validateDomain(): bool;

    /**
     *   Validate the route prefix.
     *
     * @return bool
     */
    public function validateRoutePrefix(): bool;

    /**
     *   Validate the route prefix.
     *
     * @return bool
     */
    public function validateScheme(): bool;

    /**
     *   Validate the routes for the events.
     *
     * @return bool
     */
    public function validateDefinedRoutes(): bool;

    /**
     *   Return the domain.
     *
     * @return string
     */
    public function getDomain(): string;

    /**
     *   Return the scheme.
     *
     * @return string
     */
    public function getScheme(): string;

    /**
     *   Return the route prefix.
     *
     * @return string
     */
    public function getRoutePrefix(): string;

    /**
     *   Return the routes for the events.
     *
     * @return array
     */
    public function getDefinedRoutes(): array;

    /**
     *   Return the full route string array that will be registered.
     *
     * @return array
     */
    public function getRoutesToRegister(): array;
}
