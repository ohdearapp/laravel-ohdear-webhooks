# Handle Oh Dear! webhooks in a Laravel application

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ohdearapp/laravel-ohdear-webhooks.svg?style=flat-square)](https://packagist.org/packages/ohdearapp/laravel-ohdear-webhooks)
![GitHub Workflow Status](https://img.shields.io/github/workflow/status/ohdearapp/laravel-ohdear-webhooks/run-tests?label=tests)
[![StyleCI](https://styleci.io/repos/109316815/shield?branch=master)](https://styleci.io/repos/109316815)
[![Quality Score](https://img.shields.io/scrutinizer/g/ohdearapp/laravel-ohdear-webhooks.svg?style=flat-square)](https://scrutinizer-ci.com/g/ohdearapp/laravel-ohdear-webhooks)
[![Total Downloads](https://img.shields.io/packagist/dt/ohdearapp/laravel-ohdear-webhooks.svg?style=flat-square)](https://packagist.org/packages/ohdearapp/laravel-ohdear-webhooks)

[Oh Dear](https://ohdear.app) can notify your application of events using webhooks. This package can help you handle those webhooks. Out of the box it will verify the Oh Dear signature of all incoming requests. You can easily define jobs or events that should be dispatched when specific events hit your app.

This package will not handle what should be done after the webhook request has been validated and the right job or event is called.

Before using this package we highly recommend reading [the entire documentation on webhooks over at Oh Dear](https://ohdear.app/docs/webhooks/introduction).

## Documentation

All package documentation can be found on the [Oh Dear website](https://ohdear.app/docs/webhooks/introduction).

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
