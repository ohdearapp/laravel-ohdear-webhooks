<?php

namespace OhDear\LaravelWebhooks\Middlewares;

use Closure;
use OhDear\LaravelWebhooks\Exceptions\WebhookFailed;

class VerifySignature
{
    public function handle($request, Closure $next)
    {
        $signature = $request->header('OhDear-Signature');

        if (! $signature) {
            throw WebhookFailed::missingSignature();
        }

        if (! $this->isValid($signature, $request->getContent())) {
            throw WebhookFailed::invalidSignature($signature);
        }

        return $next($request);
    }

    protected function isValid(string $signature, string $payload): bool
    {
        $secret = config('ohdear-webhooks.signing_secret');

        if (empty($secret)) {
            throw WebhookFailed::signingSecretNotSet();
        }

        $computedSignature = hash_hmac('sha256', $payload, $secret);

        return hash_equals($signature, $computedSignature);
    }
}
