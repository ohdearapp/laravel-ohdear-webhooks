<?php

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use OhDear\LaravelWebhooks\OhDearWebhookCall;
use OhDear\LaravelWebhooks\Tests\DummyJob;

beforeEach(function () {
    Event::fake();

    Bus::fake();

    Route::ohDearWebhooks('ohdear-webhooks');

    config(['ohdear-webhooks.jobs' => ['uptimeCheckFailed' => DummyJob::class]]);
});

test('it can handle a valid request', function () {
    $this->withoutExceptionHandling();

    $payload = getTestPayload();

    $headers = ['OhDear-Signature' => $this->determineOhDearSignature($payload)];

    $this
        ->postJson('ohdear-webhooks', $payload, $headers)
        ->assertSuccessful();

    Event::assertDispatched('ohdear-webhooks::uptimeCheckFailed', function ($event, $eventPayload) {
        if (! $eventPayload instanceof OhDearWebhookCall) {
            return false;
        }

        if ($eventPayload->type() != 'uptimeCheckFailed') {
            return false;
        }

        if ($eventPayload->dateTime() != '20160101120000') {
            return false;
        }

        if ($eventPayload->monitor()['id'] != 'monitor1') {
            return false;
        }

        if ($eventPayload->run()['id'] != 'run1') {
            return false;
        }

        return true;
    });

    Bus::assertDispatched(DummyJob::class, function (DummyJob $job) {
        return $job->ohDearWebhookCall->type() === 'uptimeCheckFailed';
    });
});

test('no jobs or events will be fired if a request has an invalid signature', function () {
    $payload = getTestPayload();

    $headers = ['Stripe-Signature' => 'invalid_signature'];

    $this
        ->postJson('ohdear-webhooks', $payload, $headers)
        ->assertStatus(400);

    Event::assertNotDispatched('stripe-webhooks::my.type');

    Bus::assertNotDispatched(DummyJob::class);
});

function getTestPayload(): array
{
    $payload = [
        'type' => 'uptimeCheckFailed',
        'dateTime' => '20160101120000',
        'monitor' => ['id' => 'monitor1'],
        'run' => ['id' => 'run1'],
    ];

    return $payload;
}
