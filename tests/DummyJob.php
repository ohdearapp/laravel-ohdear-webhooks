<?php

namespace OhDear\LaravelWebhooks\Tests;

use OhDear\LaravelWebhooks\OhDearWebhookCall;

class DummyJob
{
    /** @var \OhDear\LaravelWebhooks\OhDearWebhookCall */
    public $ohDearWebhookCall;

    public function __construct(OhDearWebhookCall $ohDearWebhookCall)
    {
        $this->ohDearWebhookCall = $ohDearWebhookCall;
    }

    public function handle()
    {
    }
}
