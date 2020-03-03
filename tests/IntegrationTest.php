<?php

namespace OhDear\LaravelWebhooks\Tests;

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use OhDear\LaravelWebhooks\OhDearWebhookCall;

class IntegrationTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Event::fake();

        Bus::fake();

        Route::ohDearWebhooks('ohdear-webhooks');

        config(['ohdear-webhooks.jobs' => ['uptimeCheckFailed' => DummyJob::class]]);
    }

    /** @test */
    public function it_can_handle_a_valid_request()
    {
        $this->withoutExceptionHandling();

        $payload = $this->getTestPayload();

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

            if ($eventPayload->site()['id'] != 'site1') {
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
    }

    /** @test */
    public function no_jobs_or_events_will_be_fired_if_a_request_has_an_invalid_signature()
    {
        $payload = $this->getTestPayload();

        $headers = ['Stripe-Signature' => 'invalid_signature'];

        $this
            ->postJson('ohdear-webhooks', $payload, $headers)
            ->assertStatus(400);

        Event::assertNotDispatched('stripe-webhooks::my.type');

        Bus::assertNotDispatched(DummyJob::class);
    }

    /**
     * @return array
     */
    public function getTestPayload(): array
    {
        $payload = [
            'type' => 'uptimeCheckFailed',
            'dateTime' => '20160101120000',
            'site' => ['id' => 'site1'],
            'run' => ['id' => 'run1'],
        ];

        return $payload;
    }
}
