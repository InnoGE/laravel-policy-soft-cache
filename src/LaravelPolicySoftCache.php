<?php

namespace Innoge\LaravelPolicySoftCache;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Innoge\LaravelPolicySoftCache\Contracts\SoftCacheable;

class LaravelPolicySoftCache
{
    /**
     * @var array<string,mixed>
     */
    protected array $cache = [];

    /**
     * @throws BindingResolutionException
     */
    public static function flushCache(): void
    {
        app()->make(static::class)->cache = [];
    }

    public function handleGateCall(mixed $user, string $ability, mixed $args): mixed
    {
        if (! is_array($args)) {
            $args = [$args];
        }

        $model = $args[0] ?? null;

        $policy = Gate::getPolicyFor($model);

        if ($model && $this->shouldCache($policy)) {
            return $this->callPolicyMethod($user, $model, $policy, $ability, $args);
        }

        return null;
    }

    protected function shouldCache(?object $policy): bool
    {
        return $policy && ($policy instanceof SoftCacheable || config('policy-soft-cache.cache_all_policies', false) === true);
    }

    protected function callPolicyMethod(mixed $user, Model $model, object $policy, string $ability, array $args): mixed
    {
        $cacheKey = $this->getCacheKey($model, $ability);

        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $result = $policy->{$ability}(...array_merge([$user], $args));

        $this->cache[$cacheKey] = $result;

        return $result;
    }

    protected function getCacheKey(Model $model, string $ability): string
    {
        return $model::class.'_'.$ability;
    }
}
