<?php

namespace OhDear\LaravelWebhooks\Tests\Middlewares;

use Illuminate\Support\Facades\Route;
use OhDear\LaravelWebhooks\Middlewares\VerifySignature;
use OhDear\LaravelWebhooks\Tests\TestCase;

class VerifySignatureTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        Route::post('ohdear-webhooks', function () {
            return 'ok';
        })->middleware(VerifySignature::class);
    }

    /** @test */
    public function it_will_succeed_when_the_request_has_a_valid_signature()
    {
        $payload = [
            'event' => 'uptimeCheckFailed',
        ];

        $response = $this->postJson(
            'ohdear-webhooks',
            $payload,
            ['OhDear-Signature' => $this->determineOhDearSignature($payload)]
        );

        $response
            ->assertStatus(200)
            ->assertSee('ok');
    }

    /** @test */
    public function it_will_fail_when_the_signature_header_is_not_set()
    {
        $payload = [
            'event' => 'uptimeCheckFailed',
        ];

        $response = $this->postJson(
            'ohdear-webhooks',
            $payload
        );

        $response
            ->assertStatus(400)
            ->assertJson([
                'error' => 'The request did not contain a header named `OhDear-Signature`.',
            ]);
    }

    /** @test */
    public function it_will_fail_when_the_signing_secret_is_not_set()
    {
        config(['ohdear-webhooks.signing_secret' => '']);

        $response = $this->postJson(
            'ohdear-webhooks',
            ['event' => 'uptimeCheckFailed'],
            ['OhDear-Signature' => 'abc']
        );

        $response
            ->assertStatus(400)
            ->assertSee('The OhDear webhook signing secret is not set');
    }

    /** @test */
    public function it_will_fail_when_the_signature_is_invalid()
    {
        $response = $this->postJson(
            'ohdear-webhooks',
            ['event' => 'source.chargeable'],
            ['OhDear-Signature' => 'abc']
        );

        $response
            ->assertStatus(400)
            ->assertSee('found in the header named `OhDear-Signature` is invalid');
    }
}
