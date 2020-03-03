<?php

namespace OhDear\LaravelWebhooks\Tests;

use Exception;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use OhDear\LaravelWebhooks\OhDearWebhooksServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    /**
     * Set up the environment.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        config(['ohdear-webhooks.signing_secret' => 'test_signing_secret']);
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            OhDearWebhooksServiceProvider::class,
        ];
    }

    protected function determineOhDearSignature(array $payload): string
    {
        $secret = config('ohdear-webhooks.signing_secret');

        return hash_hmac('sha256', json_encode($payload), $secret);
    }
}
