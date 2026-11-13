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

    $headers = ['OhDear-Signature' => 'invalid_signature'];

    $this
        ->postJson('ohdear-webhooks', $payload, $headers)
        ->assertStatus(400)
        ->assertSee('found in the header named `OhDear-Signature` is invalid');

    Event::assertNotDispatched('ohdear-webhooks::uptimeCheckFailed');

    Bus::assertNotDispatched(DummyJob::class);
});

test('it will fail when the payload does not contain a type', function () {
    $payload = ['dateTime' => '20160101120000'];

    $headers = ['OhDear-Signature' => $this->determineOhDearSignature($payload)];

    $this
        ->postJson('ohdear-webhooks', $payload, $headers)
        ->assertStatus(400)
        ->assertJson([
            'error' => 'The webhook call did not contain a type. Valid OhDear webhook calls should always contain a type.',
        ]);

    Bus::assertNothingDispatched();
});

test('it will fire an event without dispatching a job when no job is configured for the type', function () {
    config(['ohdear-webhooks.jobs' => []]);

    $payload = getTestPayload();

    $headers = ['OhDear-Signature' => $this->determineOhDearSignature($payload)];

    $this
        ->postJson('ohdear-webhooks', $payload, $headers)
        ->assertSuccessful();

    Event::assertDispatched('ohdear-webhooks::uptimeCheckFailed');

    Bus::assertNothingDispatched();
});

test('it will fail when the configured job class does not exist', function () {
    config(['ohdear-webhooks.jobs' => ['uptimeCheckFailed' => 'App\\Jobs\\NonExistingJob']]);

    $payload = getTestPayload();

    $headers = ['OhDear-Signature' => $this->determineOhDearSignature($payload)];

    $this
        ->postJson('ohdear-webhooks', $payload, $headers)
        ->assertStatus(400)
        ->assertJson([
            'error' => 'The App\\Jobs\\NonExistingJob class does not exist.',
        ]);

    Bus::assertNothingDispatched();
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
