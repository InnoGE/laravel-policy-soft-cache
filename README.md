# Laravel Policy Soft Cache Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/innoge/laravel-policy-soft-cache.svg?style=flat-square)](https://packagist.org/packages/innoge/laravel-policy-soft-cache)
[![Tests](https://github.com/InnoGE/laravel-policy-soft-cache/actions/workflows/run-tests.yml/badge.svg)](https://github.com/InnoGE/laravel-policy-soft-cache/actions/workflows/run-tests.yml)
[![Fix PHP code style issues](https://github.com/InnoGE/laravel-policy-soft-cache/actions/workflows/fix-php-code-style-issues.yml/badge.svg)](https://github.com/InnoGE/laravel-policy-soft-cache/actions/workflows/fix-php-code-style-issues.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/innoge/laravel-policy-soft-cache.svg?style=flat-square)](https://packagist.org/packages/innoge/laravel-policy-soft-cache)

Optimize your Laravel application's performance with soft caching for policy checks. This package caches policy invocations to prevent redundant checks within the same request lifecycle, enhancing your application's response times.

## Requirements

This package is compatible with ```Laravel 9 & 10```, and PHP 8.1, 8.2 & 8.3.
## Installation

You can install the package via composer:

```bash
composer require innoge/laravel-policy-soft-cache
```

You can publish the config file with:

```bash
php artisan vendor:publish --provider="Innoge\LaravelPolicySoftCache\LaravelPolicySoftCacheServiceProvider"
```

This is the contents of the published config file:

```php
return [
    /*
     * When enabled, the package will cache the results of all Policies in your Laravel application
     */
    'cache_all_policies' => env('CACHE_ALL_POLICIES', true),
];
```

You can also use `CACHE_ALL_POLICIES` in your `.env` file to change it.
```.dotenv
CACHE_ALL_POLICIES=false
```

## Usage

By default, this package caches all policy calls of your entire application. You can disable this behavior by setting the ```cache_all_policies```configuration to false. Now you can specify which Policy classes should be soft cached and which not. If you want your policy to be cached, add the ```Innoge\LaravelPolicySoftCache\Contracts\SoftCacheable``` interface.

For Example:

```
use Innoge\LaravelPolicySoftCache\Contracts\SoftCacheable;

class UserPolicy implements SoftCacheable
{
    ...
}
```

## Clearing the cache
Sometimes you want to clear the policy cache after model changes. You can call the ```Innoge\LaravelPolicySoftCache::flushCache();``` method.

## Known Issues
### Gate::before and Service Provider Load Order

When the `innoge/laravel-policy-soft-cache` package is installed in an application that utilizes `Gate::before`, typically defined in the `AuthServiceProvider`, a conflict may arise due to the order in which service providers are loaded.

#### Resolution Steps
To resolve this issue, follow these steps:

1. **Manual Service Provider Registration**: Add `\Innoge\LaravelPolicySoftCache\LaravelPolicySoftCacheServiceProvider::class` to the end of the `providers` array in your `config/app.php`. This manual registration ensures that the `LaravelPolicySoftCacheServiceProvider` loads after all other service providers, including `AuthServiceProvider`.

    ```php
    'providers' => [
        // Other Service Providers

        \Innoge\LaravelPolicySoftCache\LaravelPolicySoftCacheServiceProvider::class,
    ],
    ```

2. **Disable Auto-Discovery for the Package**: To prevent Laravel's auto-discovery mechanism from automatically loading the service provider, add `innoge/laravel-policy-soft-cache` to the `dont-discover` array in your `composer.json`. This step is crucial for maintaining the manual load order.

    ```json
    "extra": {
        "laravel": {
            "dont-discover": ["innoge/laravel-policy-soft-cache"]
        }
    },
    ```

3. **Reinstall Dependencies**: After updating your `composer.json`, run `composer install` to apply the changes. This step is necessary for the changes to take effect.

    ```bash
    composer install
    ```


## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Tim Geisendörfer](https://github.com/geisi)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
