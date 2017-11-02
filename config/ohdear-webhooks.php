<?php

return [

    /*
     * Oh dear will sign webhooks using a secret. You can find the secret used at the webhook
     * configuration settings: https://ohdearapp.com/xxxxxx
     */
    'signing_secret' => '',

    /*
     * Here you can define the job that should be run when a certain webhook hits your .
     * application. The key is name of stripe event type with the `.` replace by `.`
     *
     * You can find a list of Oh dear webhook type here:
     * https://ohdearapp.com/xxxxxx
     */
    'jobs' => [
        // 'source_chargeable' => \App\Jobs\LaravelWebhooks\HandleChargeableSource::class,
        // 'charge_failed' => \App\Jobs\LaravelWebhooks\HandleFailedCharge::class,
    ],
];
