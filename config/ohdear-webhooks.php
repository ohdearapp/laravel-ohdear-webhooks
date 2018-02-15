<?php

return [

    /*
     * Oh dear will sign webhooks using a secret. You can find the secret used at the webhook
     * configuration settings: https://ohdearapp.com/team-settings/notifications
     */
    'signing_secret' => env('OH_DEAR_SIGNING_SECRET'),

    /*
     * Here you can define the job that should be run when a certain webhook hits your .
     * application.
     *
     * You can find a list of Oh dear webhook types here:
     * https://ohdearapp.com/docs/webhooks/events
     */
    'jobs' => [
        // 'uptimeCheckFailed' => \App\Jobs\LaravelWebhooks\HandleFailedUptimeCheck::class,
        // 'uptimeCheckRecovered' => \App\Jobs\LaravelWebhooks\HandleRecoveredUptimeCheck::class,
        // ...
    ],
];
