<?php

namespace OhDear\LaravelWebhooks;

use Illuminate\Http\Request;

class OhDearWebhookCall
{
    public $payload = [];

    public static function createFromRequest(Request $request)
    {
        return new static($request->only([
            'type',
            'dateTime',
            'run',
            'site',
        ]));
    }

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function type(): string
    {
        return $this->payload['type'];
    }

    public function dateTime(): string
    {
        return $this->payload['dateTime'];
    }

    public function site(): array
    {
        return $this->payload['site'];
    }

    public function run(): array
    {
        return $this->payload['run'];
    }
}