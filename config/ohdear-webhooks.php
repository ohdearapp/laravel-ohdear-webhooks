<?php

return [

    /*
     * Oh dear will sign webhooks using a secret. You can find the secret used at the webhook
     * configuration settings: https://ohdear.app/team-settings/notifications#webhooks
     */
    'signing_secret' => env('OH_DEAR_SIGNING_SECRET'),

    /*
     * Here you can define the job that should be run when a certain webhook hits your
     * application.
     *
     * You can find a list of Oh dear webhook types here:
     * https://ohdear.app/docs/webhooks/events
     */
    'jobs' => [
        // 'uptimeCheckFailedNotification' => \App\Jobs\LaravelWebhooks\HandleFailedUptimeCheck::class,
        // 'uptimeCheckRecoveredNotification' => \App\Jobs\LaravelWebhooks\HandleRecoveredUptimeCheck::class,
        // ...
    ],
];
