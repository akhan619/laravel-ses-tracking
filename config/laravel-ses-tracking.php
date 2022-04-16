<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Set whether to debug the package.
    |--------------------------------------------------------------------------
    |
    |	When debug is enabled no actual calls are made to AWS. The settings and
    |   values that will be used in the call will be displayed. So you can check
    |   whether everything is in order.
    |	Type: bool
    |
    */
    'debug' => true,

    /*
    |--------------------------------------------------------------------------
    | Define the AWS credentials to use.
    |--------------------------------------------------------------------------
    |
    |   For added security you may define a different IAM user's credentials here
    |   with limited access. Please refer to the AWS docs for all permissions required
    |   to work with SES, SNS event subscription.
    |	Type: array
    |
    */
    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION'),
    ],

    /*
    |--------------------------------------------------------------------------
    | The SNS protocol to use.
    |--------------------------------------------------------------------------
    |
    |   Currently, only http and https are available.
    |	Type: array
    |
    */
    'subscriber' => [
        'http'  => false,
        'https' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Domain
    |--------------------------------------------------------------------------
    |
    |   Define the domain to use for the SNS notifications to be routed to.
    |   If null the domain will be pulled from APP_URL.
    |   Do not include the SCHEME like http/s, e.g. example.com
    |	Type: string
    |
    */
    'domain' => null,

    /*
    |--------------------------------------------------------------------------
    | Scheme
    |--------------------------------------------------------------------------
    |
    |   Define the scheme http/https to use for the SNS notifications to be routed to.
    |   If null the scheme will be pulled from APP_URL.
    |   MUST MATCH THE SNS PROTOCAL HTTP/HTTPS TYPES USED ABOVE, IF ONE OF THEM IS ENABLED.
    |	Type: string
    |
    */
    //
    'scheme' => null,

    /*
    |--------------------------------------------------------------------------
    | Events
    |--------------------------------------------------------------------------
    |
    |   Enable the SES events you wish to subscribe to. A corresponding route must be
    |   present in the routes sections below.
    |	Type: array
    |
    */
    'active' => [
        'sends'              => true,
        'rendering_failures' => false,
        'rejects'            => false,
        'deliveries'         => true,
        'bounces'            => true,
        'complaints'         => false,
        'delivery_delays'    => false,
        'subscriptions'      => false,
        'opens'              => false,
        'clicks'             => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Route prefix
    |--------------------------------------------------------------------------
    |
    |   Define the route prefix for the http/s endpoint to use. A leading or trailing '/' is not required.
    |   Set to null if no prefix is required.
    |	Type: string|null
    |
    */
    'route_prefix' => 'email/notification',

    /*
    |--------------------------------------------------------------------------
    | Route names
    |--------------------------------------------------------------------------
    |
    |   Define the route names to use for the SNS notifications to be routed to. These will be
    |   automatically setup for use. The general syntax is:
    |       scheme://domain/prefix/route
    |   Example based on defaults:
    |       http://localhost/email/notification/sends
    |
    |   Note: The routes will be registered only if they are enabled under the 'active' key.
    |	Type: array
    |
    */
    'routes' => [
        'sends'              => 'sends',
        'rendering_failures' => 'rendering-failures',
        'rejects'            => 'rejects',
        'deliveries'         => 'deliveries',
        'bounces'            => 'hard-bounces',
        'complaints'         => 'complaints',
        'delivery_delays'    => 'delivery-delays',
        'subscriptions'      => 'subscriptions',
        'opens'              => 'opens',
        'clicks'             => 'clicks',
    ],

    /*
    |--------------------------------------------------------------------------
    | Event Destination Prefix
    |--------------------------------------------------------------------------
    |
    |	Define the prefix to use for each event destination.
    |   Set to null if no prefix is required.
    |	Type: string|null
    |
    */
    'event_destination_prefix' => 'destination',

    /*
    |--------------------------------------------------------------------------
    | Event Destination - Topic Name Suffix
    |--------------------------------------------------------------------------
    |
    |	Whether the topic name should be used as a suffix.
    |	Type: bool
    |
    */
    'topic_name_as_suffix' => true,

    /*
    |--------------------------------------------------------------------------
    | Destination Names
    |--------------------------------------------------------------------------
    |
    |	The name to use as destination name for the given event.
    |   Set an individual entry to null if name is to be left empty for that event.
    |   Note: The names are used only if the corresponding event is enabled under the 'active' key.
    |	Type: array
    |
    */
    'destination_names' => [
        'sends'              => 'sns',
        'rendering_failures' => 'sns',
        'rejects'            => 'sns',
        'deliveries'         => 'sns',
        'bounces'            => 'sns',
        'complaints'         => 'sns',
        'delivery_delays'    => 'sns',
        'subscriptions'      => 'sns',
        'opens'              => 'sns',
        'clicks'             => 'sns',
    ],

    /*
    |--------------------------------------------------------------------------
    | Event Destination Suffix
    |--------------------------------------------------------------------------
    |
    |	The suffix to use for the destination name.
    |   Set to null if no suffix is required.
    |   Note: Suffix is enabled only if 'topic_name_as_suffix' is FALSE. So only one of them can use used at a time.
    |   But both can be disabled by setting 'topic_name_as_suffix' to FALSE and 'event_destination_suffix' to null.
    |	Type: string|null
    |
    */
    'event_destination_suffix' => 'us-east-1',

    /*
    |--------------------------------------------------------------------------
    | Configuration Set
    |--------------------------------------------------------------------------
    |
    | The configuration set data to use when calling the SES API to create a configuration set.
    | Modify the settings here as per your requirements.
    |
    */
    'configuration_set' => [
        /*
        |--------------------------------------------------------------------------
        | ConfigurationSetName
        |--------------------------------------------------------------------------
        |
        |   The name of the configuration set.
        |   The name can contain up to 64 alphanumeric characters, including letters, numbers, hyphens (-) and underscores (_) only.
        |	Type: string
        |   REQUIRED: YES
        |
        */
        'ConfigurationSetName' => 'ses-event',

        /*
        |--------------------------------------------------------------------------
        | DeliveryOptions
        |--------------------------------------------------------------------------
        |
        |	Used to associate a configuration set with a dedicated IP pool.
        |   REQUIRED: NO
        |
        */
        'DeliveryOptions' => [
            /*
            |--------------------------------------------------------------------------
            | SendingPoolName
            |--------------------------------------------------------------------------
            |
            |	The name of the dedicated IP pool to associate with the configuration set.
            |   Type: string|null
            |   REQUIRED: NO <Set to null to remove from the method calls.>
            |
            */
            'SendingPoolName' => null,

            /*
            |--------------------------------------------------------------------------
            | TlsPolicy
            |--------------------------------------------------------------------------
            |
            |	Specifies whether messages that use the configuration set are required to use Transport Layer Security (TLS).
            |   Type: string <REQUIRE | OPTIONAL>
            |
            */
            'TlsPolicy' => 'REQUIRE',
        ],

        /*
        |--------------------------------------------------------------------------
        | ReputationOptions
        |--------------------------------------------------------------------------
        |
        |	Enable or disable collection of reputation metrics for emails that you send using this configuration set in the current Amazon Web Services Region.
        |
        */
        'ReputationOptions' => [
            /*
            |--------------------------------------------------------------------------
            | LastFreshStart
            |--------------------------------------------------------------------------
            |
            |	The date and time (in Unix time) when the reputation metrics were last given a fresh start.
            |   Type: timestamp (string|DateTime or anything parsable by strtotime)|null
            |   REQUIRED: NO <Set to null to remove from the method calls.>
            |
            */
            'LastFreshStart' => null,

            /*
            |--------------------------------------------------------------------------
            | ReputationMetricsEnabled
            |--------------------------------------------------------------------------
            |
            |	If true, tracking of reputation metrics is enabled for the configuration set.
            |   Type: boolean
            |
            */
            'ReputationMetricsEnabled' => false,
        ],

        /*
        |--------------------------------------------------------------------------
        | SendingOptions
        |--------------------------------------------------------------------------
        |
        |	An object that defines whether or not Amazon SES can send email that you send using the configuration set.
        |
        */
        'SendingOptions' => [
            /*
            |--------------------------------------------------------------------------
            | SendingEnabled
            |--------------------------------------------------------------------------
            |
            |	If true, email sending is enabled for the configuration set. If false, email sending is disabled for the configuration set.
            |   Type: boolean
            |
            */
            'SendingEnabled' => true,
        ],

        /*
        |--------------------------------------------------------------------------
        | SuppressionOptions
        |--------------------------------------------------------------------------
        |
        |	An object that contains information about the suppression list preferences for your account.
        |
        */
        'SuppressionOptions' => [
            /*
            |--------------------------------------------------------------------------
            | SuppressedReasons
            |--------------------------------------------------------------------------
            |
            |	A list that contains the reasons that email addresses are automatically added to the suppression list for your account.
            |   Type: Array of strings <COMPLAINT, BOUNCE>
            |   Examples: ['COMPLAINT'] | ['BOUNCE'] | ['COMPLAINT' , 'BOUNCE']
            |   REQUIRED: NO <Set to empty array to remove from the method calls.>
            |
            */
            'SuppressedReasons' => [],
        ],

        /*
        |--------------------------------------------------------------------------
        | Tags
        |--------------------------------------------------------------------------
        |
        |	An array of objects that define the tags (keys and values) to associate with the configuration set.
        |   Type: Array of Tag structures
        |   REQUIRED: NO <Set to empty array to remove from the method calls.>
        |
        */
        'Tags' => [],

        /*
        |--------------------------------------------------------------------------
        | TrackingOptions
        |--------------------------------------------------------------------------
        |
        |	An object that defines the open and click tracking options for emails that you send using the configuration set.
        |
        */
        'TrackingOptions' => [
            /*
            |--------------------------------------------------------------------------
            | CustomRedirectDomain
            |--------------------------------------------------------------------------
            |
            |	The domain to use for tracking open and click events.
            |   Type: string|null
            |   REQUIRED: NO <Set to null to remove from the method calls.>
            |
            */
            'CustomRedirectDomain' => null,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Topic Name Prefix
    |--------------------------------------------------------------------------
    |
    |	The prefix to use for the topic name.
    |   Set to null if no prefix is required.
    |	Type: string|null
    |
    */
    'topic_name_prefix' => env('APP_NAME', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Topic Name
    |--------------------------------------------------------------------------
    |
    |   The name to use for the sns topic for a given event.
    |   Set an individual entry to null if name is to be left empty for that event.
    |   Note: The names are used only if the corresponding event is enabled under the 'active' key.
    |	Type: array
    |
    */
    'topic_names' => [
        'sends'              => 'sends',
        'rendering_failures' => 'rendering-failures',
        'rejects'            => 'rejects',
        'deliveries'         => 'deliveries',
        'bounces'            => 'hard-bounces',
        'complaints'         => 'complaints',
        'delivery_delays'    => 'delivery-delays',
        'subscriptions'      => 'subscriptions',
        'opens'              => 'opens',
        'clicks'             => 'clicks',
    ],

    /*
    |--------------------------------------------------------------------------
    | Topic Name Suffix
    |--------------------------------------------------------------------------
    |
    |	The suffix to use for the topic name.
    |   Set to null if no suffix is required.
    |	Type: string|null
    |
    */
    'topic_name_suffix' => 'us-east-1',

    /*
    |--------------------------------------------------------------------------
    | SNS Topic Configuration Data
    |--------------------------------------------------------------------------
    |
    | The configuration data to use when calling the SNS API to create a topic.
    | Modify the settings here as per your requirements.
    |
    */
    'sns_topic_configuration_data' => [
        /*
        |--------------------------------------------------------------------------
        | DeliveryPolicy
        |--------------------------------------------------------------------------
        |
        |	The policy that defines how Amazon SNS retries failed deliveries to HTTP/S endpoints.
        |   Refer to the AWS docs for more details on the structure.
        |   Type: Array (Internally converted to json string)
        |   REQUIRED: NO <Set to empty array to remove from the method calls.>
        |
        */
        'DeliveryPolicy' => [],

        /*
        |--------------------------------------------------------------------------
        | Policy
        |--------------------------------------------------------------------------
        |
        |	The policy that defines who can access your topic.
        |   Refer to the AWS docs for more details on the structure.
        |   Type: Array (Internally converted to json string)
        |   REQUIRED: NO <Set to empty array to remove from the method calls.>
        |
        */
        'Policy' => [],

        /*
        |--------------------------------------------------------------------------
        | KmsMasterKeyId
        |--------------------------------------------------------------------------
        |
        |	The ID of an Amazon Web Services managed customer master key (CMK) for Amazon SNS or a custom CMK.
        |   Set this key to enable Server-Side-Encryption at rest.
        |   Type: string|null
        |   REQUIRED: NO <Set to null to remove from the method calls.>
        |
        */
        'KmsMasterKeyId' => null,

        /*
        |--------------------------------------------------------------------------
        | Tags
        |--------------------------------------------------------------------------
        |
        |	An array of objects that define the tags (keys and values) to associate with the Topic.
        |   Type: Array of Tag structures
        |   REQUIRED: NO <Set to empty array to remove from the method calls.>
        |
        */
        'Tags' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | SNS Subscription Configuration Data
    |--------------------------------------------------------------------------
    |
    |	The configuration data to use when calling the SNS API to subscribe an endpoint to a topic.
    |   Modify the settings here as per your requirements.
    |
    */
    'sns_subscription_configuration_data' => [
        /*
        |--------------------------------------------------------------------------
        | ReturnSubscriptionArn
        |--------------------------------------------------------------------------
        |
        |	Sets whether the response from the Subscribe request includes the subscription ARN, even if the subscription is not yet confirmed.
        |   Type: boolean
        |
        */
        'ReturnSubscriptionArn' => false,

        /*
        |--------------------------------------------------------------------------
        | DeliveryPolicy
        |--------------------------------------------------------------------------
        |
        |	The policy that defines how Amazon SNS retries failed deliveries to HTTP/S endpoints.
        |   Refer to the AWS docs for more details on the structure.
        |   Type: Array (Internally converted to json string)
        |   REQUIRED: NO <Set to empty array to remove from the method calls.>
        |
        */
        'DeliveryPolicy' => [],

        /*
        |--------------------------------------------------------------------------
        | FilterPolicy
        |--------------------------------------------------------------------------
        |
        |	The simple JSON object that lets your subscriber receive only a subset of messages,
        |   rather than receiving every message published to the topic.
        |   Refer to the AWS docs for more details on the structure.
        |   Type: Array (Internally converted to json string)
        |   REQUIRED: NO <Set to empty array to remove from the method calls.>
        |
        */
        'FilterPolicy ' => [],

        /*
        |--------------------------------------------------------------------------
        | RawMessageDelivery
        |--------------------------------------------------------------------------
        |
        |	Sets whether the response from the Subscribe request includes the subscription ARN, even if the subscription is not yet confirmed.
        |   Type: string <true | false>
        |
        */
        'RawMessageDelivery' => 'false',

        /*
        |--------------------------------------------------------------------------
        | RedrivePolicy
        |--------------------------------------------------------------------------
        |
        |	When specified, sends undeliverable messages to the specified Amazon SQS dead-letter queue.
        |   Type: string <ARN>|null
        |   REQUIRED: NO <Set to null to remove from the method calls.>
        |
        */
        'RedrivePolicy' => null,
    ],
];
