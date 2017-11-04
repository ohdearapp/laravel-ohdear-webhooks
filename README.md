> Package in development, do not use yet

# Handle Oh Dear webhooks in a Laravel application

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ohdearapp/laravel-ohdear-webhooks.svg?style=flat-square)](https://packagist.org/packages/ohdearapp/laravel-ohdear-webhooks)
[![Build Status](https://img.shields.io/travis/ohdearapp/laravel-ohdear-webhooks/master.svg?style=flat-square)](https://travis-ci.org/ohdearapp/laravel-ohdear-webhooks)
[![StyleCI](https://styleci.io/repos/109316815/shield?branch=master)](https://styleci.io/repos/109316815)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/30a27173-07a3-4752-9239-ae050924f158.svg?style=flat-square)](https://insight.sensiolabs.com/projects/30a27173-07a3-4752-9239-ae050924f158)
[![Quality Score](https://img.shields.io/scrutinizer/g/ohdearapp/laravel-ohdear-webhooks.svg?style=flat-square)](https://scrutinizer-ci.com/g/ohdearapp/laravel-ohdear-webhooks)
[![Total Downloads](https://img.shields.io/packagist/dt/ohdearapp/laravel-ohdear-webhooks.svg?style=flat-square)](https://packagist.org/packages/ohdearapp/laravel-ohdear-webhooks)

[Oh Dear](https://ohdearapp.com) can notify your application of events using webhooks. This package can help you handle those webhooks. Out of the box it will verify the Oh Dear signature of all incoming requests. You can easily define jobs or events that should be dispatched when specific events hit your app.

This package will not handle what should be done after the webhook request has been validated and the right job or event is called. 

Before using this package we highly recommend reading [the entire documentation on webhooks over at Oh Dear](https://ohdearapp.com/xxxxxx).

## Installation

You can install the package via composer:

```bash
composer require ohdearapp/laravel-ohdear-webhooks
```

The service provider will automatically register itself.

You must publish the config file with:
```bash
php artisan vendor:publish --provider="OhDear\LaravelWebhooks\OhDearWebhooksServiceProvider" --tag="config"
```

This is the contents of the config file that will be published at `config/ohdear-webhooks.php`:

```php
return [

    /*
     * Oh dear will sign webhooks using a secret. You can find the secret used at the webhook
     * configuration settings: https://ohdearapp.com/xxxxxx
     */
    'signing_secret' => '',

    /*
     * Here you can define the job that should be run when a certain webhook hits your .
     * application.
     *
     * You can find a list of Oh dear webhook types here:
     * https://ohdearapp.com/xxxxxx
     */
    'jobs' => [
        // 'uptimeCheckFailed' => \App\Jobs\LaravelWebhooks\HandleFailedUptimeCheck::class,
        // 'uptimeCheckRecovered' => \App\Jobs\LaravelWebhooks\HandleRecoveredUptimeCheck::class,
        // ...
    ],
];
```

In the `signing_secret` key of the config file you should add a valid webhook secret. You can find the secret used at [the webhook configuration settings on the Oh Dear notification settings](https://ohdearapp.com/xxxxx).


Finally, take care of the routing: At [the Oh Dear notification settings ](https://ohdearapp.com/xxxxx) you must configure at what url Oh Dear webhooks should hit your app. In the routes file of your app you must pass that route to `Route::ohDearWebhooks`:

```php
Route::ohDearWebhooks('webhook-route-configured-at-the-ohdear-dashboard'); 
```

Behind the scenes this will register a `POST` route to a controller provided by this package. Because Oh Dear has no way of getting a csrf-token, you must add that route to the `except` array of the `VerifyCsrfToken` middleware:

```php
protected $except = [
    'webhook-route-configured-at-the-ohdear-dashboard',
];
```

## Usage

Oh Dear will send out webhooks for several event types. You can find the [full list of events types](https://ohdearapp.com/xxxxx) in the Oh Dear documentation.

Oh Dear will sign all requests hitting the webhook url of your app. This package will automatically verify if the signature is valid. If it is not, the request was probably not sent by Oh Dear.
 
Unless something  wrong, this package will respond with a `200` to webhook requests. Sending a `200` will prevent Oh Dear from resending the same event again.

If the signature is not valid a `OhDear\OhDearWebhooks\WebhookFailed` exception will be thrown.
 
There are two ways this package enables you to handle webhook requests: you can opt to queue a job or listen to the events the package will fire.
 
### Handling webhook requests using jobs 
If you want to do something when a specific event type comes in you can define a job that does the work. Here's an example of such a job:

```php
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use OhDear\LaravelWebhooks\OhDearWebhookCall;

class HandleFailedUptimeCheck implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;
    
    /** @var \OhDear\LaravelWebhooks\OhDearWebhookCall */
    public $webhookCall;

    public function __construct(OhDearWebhookCall $webhookCall)
    {
        $this->webhookCall = $webhookCall;
    }

    public function handle()
    {
        // do your work here
        
        // you can access the payload of the webhook call with `$this->webhookCall->payload`
    }
}
```

We highly recommend that you make this job queueable, because this will minimize the response time of the webhook requests. This allows you to handle more oh dear webhook requests and avoid timeouts.

After having created your job you must register it at the `jobs` array in the `ohdear-webhooks.php` config file. The key should be the name of [the oh dear event type](https://ohdearapp.com/xxxx). The value should be the fully qualified classname.

```php
// config/ohdear-webhooks.php

'jobs' => [
    'uptimeCheckFailed' => \App\Jobs\ohdearWebhooks\HandleFailedUptimeCheck::class,
    'uptimeCheckRecovered' => \App\Jobs\ohdearWebhooks\HandleRecoveredUptimeCheck::class,
],
```

### Handling webhook requests using events

Instead of queueing jobs to perform some work when a webhook request comes in, you can opt to listen to the events this package will fire. Whenever a valid request hits your app, the package will fire a `ohdear-webhooks::<name-of-the-event>` event.

The payload of the events will be the instance of `OhDearWebhookCall` that was created for the incoming request. 

Let's take a look at how you can listen for such an event. In the `EventServiceProvider` you can register listeners.

```php
/**
 * The event listener mappings for the application.
 *
 * @var array
 */
protected $listen = [
    'ohdear-webhooks::uptimeCheckFailed' => [
        App\Listeners\MailOperators::class,
    ],
];
```

Here's an example of such a listener:

```php
<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use OhDear\LaravelWebhooks\OhDearWebhookCall;

class MailOperators implements ShouldQueue
{
    public function handle(OhDearWebhookCall $webhookCall)
    {
        // do your work here

        // you can access the payload of the webhook call with `$webhookCall->payload`
    }   
}
```

We highly recommend that you make the event listener queueable, as this will minimize the response time of the webhook requests. This allows you to handle more Oh Dear webhook requests and avoid timeouts.

The above example is only one way to handle events in Laravel. To learn the other options, read [the Laravel documentation on handling events](https://laravel.com/docs/5.5/events).

## Using the OhDearWebhookCall

Like mentioned above your events or jobs will receive an instance of `OhDear\LaravelWebhooks\OhDearWebhookCall`.

You can access the raw payload by calling:

```
$ohDearWebhookCall->payload; // returns an array;
```

Or you can opt to get more specific information:

```
$ohDearWebhookCall->type(); // returns the type of the webhook (eg: 'uptimeCheckFailed');
$ohDearWebhookCall->site(); // returns an array with all the attribute of the site;
$ohDearWebhookCall->run(); // returns an array with all the attribute of the run;
$ohDearWebhookCall->dateTime(); // returns an string with a dateTime (Ymdhis) when Oh Dear generated this webhook call;
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information about what has changed recently.

## Testing

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email support@ohdearapp.com instead of using the issue tracker.

## Credits

- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
