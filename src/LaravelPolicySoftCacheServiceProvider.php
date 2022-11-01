<?php

namespace Innoge\LaravelPolicySoftCache;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
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
            ->hasConfigFile();
    }

    public function boot()
    {
        ray()->clearScreen();
        Gate::before(function (Model $user, string $ability, array $args) {
            return $this->app->make(LaravelPolicySoftCache::class)
                ->handleGateCall($user, $ability, $args);
        });
    }
}
