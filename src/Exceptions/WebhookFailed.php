<?php

namespace OhDear\LaravelWebhooks\Exceptions;

use Exception;
use Illuminate\Http\Request;

class WebhookFailed extends Exception
{
    public static function missingSignature()
    {
        return new static('The request did not contain a header named `OhDear-Signature`.');
    }

    public static function invalidSignature($signature)
    {
        return new static("The signature `{$signature}` found in the header named `OhDear-Signature` is invalid. Make sure that the `ohdear-webhooks.signing_secret` config key is set to the value you found on the OhDear dashboard. If you are caching your config try running `php artisan config:clear` to resolve the problem.");
    }

    public static function signingSecretNotSet()
    {
        return new static('The OhDear webhook signing secret is not set. Make sure that the `ohdear-webhooks.signing_secret` config key is set to the value you found on the OhDear dashboard.');
    }

    public static function missingType(Request $request)
    {
        return new static('The webhook call did not contain a type. Valid OhDear webhook calls should always contain a type.');
    }

    public static function jobClassDoesNotExist($jobClass, $ohDearWebhookCall)
    {
        return new static('The '.$jobClass.' class does not exist.');
    }

    public function render($request)
    {
        return response(['error' => $this->getMessage()], 400);
    }
}
