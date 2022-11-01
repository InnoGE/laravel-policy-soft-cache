<?php

namespace Innoge\LaravelPolicySoftCache;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Innoge\LaravelPolicySoftCache\Contracts\SoftCacheable;

class LaravelPolicySoftCache
{
    protected static array $cache = [];

    public static function flushCache(): void
    {
        static::$cache = [];
    }

    public function handleGateCall(Model $user, string $ability, array $args)
    {
        $model = $args[0] ?? null;

        $policy = Gate::getPolicyFor($model);

        if ($model && $this->shouldCache($model, $policy, $ability)) {
            ray(true);
            return $this->callPolicyMethod($user, $model, $policy, $ability, $args);
        }
        return null;
    }

    protected function shouldCache(Model $model, mixed $policy, string $ability): bool
    {
        return $policy && ($policy instanceof SoftCacheable || config('policy-soft-cache.cache_all_policies', false) === true);
    }

    protected function callPolicyMethod(Model $user, Model $model, object $policy, string $ability, array $args)
    {
        $cacheKey = $this->getCacheKey($model, $ability);
        if (isset(static::$cache[$cacheKey])) {
            return static::$cache[$cacheKey];
        }

        $result = $policy->{$ability}(...array_merge([$user], $args));

        static::$cache[$cacheKey] = $result;

        return $result;
    }

    protected function getCacheKey(Model $model, string $ability): string
    {
        return config('policy-soft-cache.cache_prefix', 'soft_cache_') . $model::class . '_' . $ability;
    }
}
