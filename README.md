# Handle Oh Dear! webhooks in a Laravel application

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ohdearapp/laravel-ohdear-webhooks.svg?style=flat-square)](https://packagist.org/packages/ohdearapp/laravel-ohdear-webhooks)
[![Total Downloads](https://img.shields.io/packagist/dt/ohdearapp/laravel-ohdear-webhooks.svg?style=flat-square)](https://packagist.org/packages/ohdearapp/laravel-ohdear-webhooks)

[Oh Dear](https://ohdear.app) can notify your application of events using webhooks. This package can help you handle those webhooks. Out of the box it will verify the Oh Dear signature of all incoming requests. You can easily define jobs or events that should be dispatched when specific events hit your app.

This package will not handle what should be done after the webhook request has been validated and the right job or event is called.

## Installation

```
$ composer require ohdearapp/laravel-ohdear-webhooks
```

The service provider will automatically register itself.

You must publish the config file with:

```
$ php artisan vendor:publish --provider="OhDear\LaravelWebhooks\OhDearWebhooksServiceProvider" --tag="config"
```

This is the contents of the config file that will be published at `config/ohdear-webhooks.php`:

```php
return [

    /*
     * Oh dear will sign webhooks using a secret. You can find the secret used at the webhook
     * configuration settings: <?php echo config('app.url'); ?>/team-settings/notifications#webhooks
     */
    'signing_secret' => env('OH_DEAR_SIGNING_SECRET'),

    /*
     * Here you can define the job that should be run when a certain webhook hits your .
     * application.
     *
     * You can find a list of Oh dear webhook types here:
     * https://ohdear.app/docs/integrations/webhooks#webhook-events
     */
    'jobs' => [
        // 'uptimeCheckFailed' => \App\Jobs\LaravelWebhooks\HandleFailedUptimeCheck::class,
        // 'uptimeCheckRecovered' => \App\Jobs\LaravelWebhooks\HandleRecoveredUptimeCheck::class,
        // ...
    ],
];
```

In the `signing_secret` key of the config file you specify your signing secret. You can find the correct at the team webhooks settings on [the notification settings screen](/team-settings/notifications).

Finally, take care of the routing: At the Oh Dear notification settings you must configure at what url Oh Dear webhooks should hit your app. In the routes file of your app you must pass that route to `Route::ohDearWebhooks`:

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

Oh Dear will send out webhooks for several event types. You can find the full list of events types in the [Oh Dear documentation](events).

Oh Dear will sign all requests hitting the webhook url of your app. This package will automatically verify if the signature is valid. If it is not, the request was probably not sent by Oh Dear.

Unless something wrong, this package will respond with a `200` to webhook requests. Sending a `200` will prevent Oh Dear from resending the same event again.

If the signature is not valid a `OhDear\OhDearWebhooks\WebhookFailed` exception will be thrown.

There are two ways this package enables you to handle webhook requests: you can opt to queue a job or listen to the events the package will fire.

### Handling webhook requests using jobs

If you want to do something when a specific event type comes in you can define a job that does the work. Here's an example of such a job:

```php
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

        // you can access the payload of the webhook call with $this->webhookCall->payload
    }
}
```

We highly recommend that you make this job queueable, because this will minimize the response time of the webhook requests. This allows you to handle more oh dear webhook requests and avoid timeouts.

After having created your job you must register it at the `jobs` array in the `ohdear-webhooks.php` config file. The key should be the name of the [oh dear event type](events). The value should be the fully qualified classname.

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

The above example is only one way to handle events in Laravel. To learn the other options, [read the Laravel documentation on handling events](https://laravel.com/docs/5.5/events).

### Using the OhDearWebhookCall

Like mentioned above your events or jobs will receive an instance of `OhDear\LaravelWebhooks\OhDearWebhookCall`.

You can access the raw payload by calling:

```php
$ohDearWebhookCall->payload; // returns an array;
```

Or you can opt to get more specific information:

```php
$ohDearWebhookCall->type(); // returns the type of the webhook (eg: 'uptimeCheckFailed');
$ohDearWebhookCall->monitor(); // returns an array with all the attribute of the monitor;
$ohDearWebhookCall->run(); // returns an array with all the attribute of the run;
$ohDearWebhookCall->dateTime(); // returns an string with a dateTime (Ymdhis) when Oh Dear generated this webhook call;
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email support@ohdear.app instead of using the issue tracker.

## Credits

- [Freek Van der Herten](https://github.com/freekmurze)
- [Mattias Geniar](https://github.com/mattiasgeniar)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
