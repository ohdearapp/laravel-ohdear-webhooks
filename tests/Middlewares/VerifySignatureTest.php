<?php

use Illuminate\Support\Facades\Route;
use OhDear\LaravelWebhooks\Middlewares\VerifySignature;

beforeEach(function () {
    Route::post('ohdear-webhooks', function () {
        return 'ok';
    })->middleware(VerifySignature::class);
});

test('it will succeed when the request has a valid signature', function () {
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
});

test('it will fail when the signature header is not set', function () {
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
});

test('it will fail when the signing secret is not set', function () {
    config(['ohdear-webhooks.signing_secret' => '']);

    $response = $this->postJson(
        'ohdear-webhooks',
        ['event' => 'uptimeCheckFailed'],
        ['OhDear-Signature' => 'abc']
    );

    $response
        ->assertStatus(400)
        ->assertSee('The OhDear webhook signing secret is not set');
});

test('it will fail when the signature is invalid', function () {
    $response = $this->postJson(
        'ohdear-webhooks',
        ['event' => 'source.chargeable'],
        ['OhDear-Signature' => 'abc']
    );

    $response
        ->assertStatus(400)
        ->assertSee('found in the header named `OhDear-Signature` is invalid');
});