<?php

namespace Innoge\LaravelPolicySoftCache;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelPolicySoftCacheServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-policy-soft-cache')
            ->hasConfigFile('policy-soft-cache');
    }

    public function boot(): void
    {
        $this->app->singleton(LaravelPolicySoftCache::class, function () {
            return new LaravelPolicySoftCache();
        });

        $this->publishes([
            __DIR__.'/../config/policy-soft-cache.php' => config_path('policy-soft-cache.php'),
        ]);

        /*
         *  Flush Cache on every application boot
         */
        LaravelPolicySoftCache::flushCache();

        Gate::before(function (Model $user, string $ability, array $args) {
            return $this->app->make(LaravelPolicySoftCache::class)
                ->handleGateCall($user, $ability, $args);
        });
    }
}
